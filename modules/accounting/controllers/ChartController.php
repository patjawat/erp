<?php

namespace app\modules\accounting\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\modules\accounting\models\AccountingChartImportForm;
use app\modules\accounting\models\AccountingChartVersion;
use app\modules\accounting\services\AccountingChartImportService;

class ChartController extends Controller
{
    private const IMPORT_SESSION = 'accounting_chart_import_preview';
    private const PREVIEW_TTL = 7200;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['accountingView']],
                ['allow' => true, 'actions' => ['import', 'confirm-import', 'delete-import-preview', 'activate'], 'roles' => ['accountingChartManage']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'confirm-import' => ['POST'], 'delete-import-preview' => ['POST'], 'activate' => ['POST'],
            ]],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', ['dataProvider' => new ActiveDataProvider([
            'query' => AccountingChartVersion::find()->orderBy(['fiscal_year' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ])]);
    }

    public function actionView($id)
    {
        $model = $this->findVersion($id);
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => new ActiveDataProvider([
                'query' => $model->getAccounts(),
                'pagination' => ['pageSize' => 100],
            ]),
        ]);
    }

    public function actionImport()
    {
        $this->purgeStalePreviews();
        $model = new AccountingChartImportForm();
        $preview = $this->loadPreview();
        if (Yii::$app->request->isPost) {
            $this->clearPreview();
            $preview = null;
            $model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                $path = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'accounting-chart-' . Yii::$app->security->generateRandomString(12) . '.' . $model->file->extension;
                if (!$model->file->saveAs($path)) {
                    Yii::$app->session->setFlash('error', 'บันทึกไฟล์ชั่วคราวไม่สำเร็จ กรุณาลองอีกครั้ง');
                } else {
                    try {
                        $preview = (new AccountingChartImportService())->preview(
                            $path,
                            $model->file->name,
                            (int) $model->fiscal_year,
                            (string) $model->version_code,
                            (string) $model->title,
                            $model->sheet
                        );
                        $preview = $this->storePreview($preview);
                    } catch (\Throwable $e) {
                        Yii::error($e, __METHOD__);
                        Yii::$app->session->setFlash('error', $e instanceof \RuntimeException ? $e->getMessage() : 'อ่านไฟล์ไม่สำเร็จ กรุณาตรวจรูปแบบแล้วลองอีกครั้ง');
                    } finally {
                        @unlink($path);
                    }
                }
            }
        } elseif ($preview) {
            $model->setAttributes([
                'fiscal_year' => $preview['fiscal_year'],
                'version_code' => $preview['version_code'],
                'title' => $preview['title'],
                'sheet' => $preview['sheet'],
            ], false);
        }
        return $this->render('import', compact('model', 'preview'));
    }

    public function actionConfirmImport()
    {
        $preview = $this->loadPreview();
        $token = (string) Yii::$app->request->post('preview_token', '');
        if (!$preview || $token === '' || !hash_equals((string) $preview['token'], $token)) {
            Yii::$app->session->setFlash('error', 'ไม่พบผลตรวจสอบ กรุณาเลือกไฟล์ใหม่');
            return $this->redirect(['import']);
        }
        try {
            $version = (new AccountingChartImportService())->save($preview);
            $this->clearPreview();
            Yii::$app->session->setFlash('success', 'นำเข้าผังบัญชีเป็นฉบับรอตรวจสอบแล้ว ' . number_format($version->account_count) . ' รหัส');
            return $this->redirect(['view', 'id' => $version->id]);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'นำเข้าไม่สำเร็จ ระบบยังไม่ได้บันทึกข้อมูล — ' . $e->getMessage());
            return $this->redirect(['import']);
        }
    }

    public function actionDeleteImportPreview()
    {
        $this->clearPreview();
        return $this->redirect(['import']);
    }

    public function actionActivate($id)
    {
        $model = $this->findVersion($id);
        try {
            (new AccountingChartImportService())->activate($model);
            Yii::$app->session->setFlash('success', 'เปิดใช้ผังบัญชี “' . $model->title . '” เป็นมาตรฐานอ้างอิงแล้ว โดยยังไม่เชื่อมทะเบียนเจ้าหนี้');
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'เปิดใช้ผังบัญชีไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    private function findVersion($id): AccountingChartVersion
    {
        if (!$model = AccountingChartVersion::findOne($id)) {
            throw new NotFoundHttpException('ไม่พบผังบัญชี');
        }
        return $model;
    }

    private function storePreview(array $preview): array
    {
        $token = Yii::$app->security->generateRandomString(32);
        $dir = $this->previewDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($dir . DIRECTORY_SEPARATOR . $token . '.json', json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX);
        Yii::$app->session->set(self::IMPORT_SESSION, $token);
        $preview['token'] = $token;
        return $preview;
    }

    private function loadPreview(): ?array
    {
        $token = (string) Yii::$app->session->get(self::IMPORT_SESSION, '');
        $path = $this->previewPath($token);
        if (!$path || !is_file($path) || filemtime($path) < time() - self::PREVIEW_TTL) {
            $this->clearPreview();
            return null;
        }
        $preview = json_decode((string) file_get_contents($path), true);
        if (!is_array($preview) || !isset($preview['rows'])) {
            $this->clearPreview();
            return null;
        }
        $preview['token'] = $token;
        return $preview;
    }

    private function clearPreview(): void
    {
        $token = (string) Yii::$app->session->get(self::IMPORT_SESSION, '');
        if ($path = $this->previewPath($token)) {
            @unlink($path);
        }
        Yii::$app->session->remove(self::IMPORT_SESSION);
    }

    private function purgeStalePreviews(): void
    {
        foreach (glob($this->previewDir() . DIRECTORY_SEPARATOR . '*.json') ?: [] as $file) {
            if (@filemtime($file) < time() - self::PREVIEW_TTL) {
                @unlink($file);
            }
        }
    }

    private function previewDir(): string
    {
        return Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'accounting-chart-import';
    }

    private function previewPath(string $token): ?string
    {
        return preg_match('/^[A-Za-z0-9_-]{32}$/', $token)
            ? $this->previewDir() . DIRECTORY_SEPARATOR . $token . '.json'
            : null;
    }
}
