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
use app\modules\accounting\models\AccountingChartAccount;
use app\modules\accounting\models\AccountingChartMapping;
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
                ['allow' => true, 'actions' => ['index', 'view', 'mappings'], 'roles' => ['accountingView']],
                ['allow' => true, 'actions' => ['import', 'confirm-import', 'delete-import-preview', 'activate', 'confirm-mapping', 'reject-mapping', 'map-manually', 'mark-hospital-only', 'reset-mapping'], 'roles' => ['accountingChartManage']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'confirm-import' => ['POST'], 'delete-import-preview' => ['POST'], 'activate' => ['POST'],
                'confirm-mapping' => ['POST'], 'reject-mapping' => ['POST'],
                'map-manually' => ['POST'], 'mark-hospital-only' => ['POST'], 'reset-mapping' => ['POST'],
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
        $mappingReadiness = $model->scope === AccountingChartVersion::SCOPE_HOSPITAL
            ? (new AccountingChartImportService())->mappingReadiness($model)
            : null;
        return $this->render('view', [
            'model' => $model,
            'mappingReadiness' => $mappingReadiness,
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
                            $model->sheet,
                            (string) $model->scope
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
                'scope' => $preview['scope'],
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
            Yii::$app->session->setFlash('error', $e instanceof \DomainException ? $e->getMessage() : 'เปิดใช้ผังบัญชีไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionMappings($id)
    {
        $model = $this->findVersion($id);
        if ($model->scope !== AccountingChartVersion::SCOPE_HOSPITAL) {
            Yii::$app->session->setFlash('warning', 'การจับคู่ใช้สำหรับผังโรงพยาบาลเท่านั้น');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $mappings = AccountingChartMapping::find()
            ->where(['hospital_version_id' => $model->id])
            ->with(['hospitalAccount', 'standardAccount'])
            ->orderBy(['status' => SORT_DESC, 'match_type' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        $mappedIds = array_map(static fn(AccountingChartMapping $mapping) => $mapping->hospital_account_id, $mappings);
        $unmapped = $model->getAccounts()
            ->andWhere(['category' => ['4', '5']])
            ->andFilterWhere(['not in', 'id', $mappedIds])
            ->all();
        $standardVersion = $this->findStandardVersion($model);
        $standardAccounts = $standardVersion
            ? AccountingChartAccount::find()->where(['version_id' => $standardVersion->id, 'category' => ['4', '5']])->orderBy('code')->all()
            : [];
        $readiness = (new AccountingChartImportService())->mappingReadiness($model);
        return $this->render('mappings', compact('model', 'mappings', 'unmapped', 'standardVersion', 'standardAccounts', 'readiness'));
    }

    public function actionConfirmMapping($id)
    {
        return $this->updateMappingStatus($id, AccountingChartMapping::STATUS_CONFIRMED, 'ยืนยันการจับคู่แล้ว');
    }

    public function actionRejectMapping($id)
    {
        return $this->updateMappingStatus($id, AccountingChartMapping::STATUS_REJECTED, 'ปฏิเสธคำแนะนำการจับคู่แล้ว');
    }

    public function actionMapManually($id)
    {
        $hospitalVersion = $this->findVersion($id);
        $standardVersion = $this->findStandardVersion($hospitalVersion);
        $hospitalAccount = AccountingChartAccount::findOne((int) Yii::$app->request->post('hospital_account_id'));
        $standardAccount = AccountingChartAccount::findOne((int) Yii::$app->request->post('standard_account_id'));
        if (!$standardVersion || !$hospitalAccount || !$standardAccount
            || (int) $hospitalAccount->version_id !== (int) $hospitalVersion->id
            || (int) $standardAccount->version_id !== (int) $standardVersion->id
            || !in_array($hospitalAccount->category, ['4', '5'], true)
            || !in_array($standardAccount->category, ['4', '5'], true)) {
            Yii::$app->session->setFlash('error', 'ข้อมูลบัญชีที่เลือกไม่อยู่ในผังปีเดียวกัน กรุณาเลือกใหม่');
            return $this->redirect(['mappings', 'id' => $hospitalVersion->id]);
        }
        $mapping = AccountingChartMapping::findOne(['hospital_version_id' => $hospitalVersion->id, 'hospital_account_id' => $hospitalAccount->id])
            ?: new AccountingChartMapping(['hospital_version_id' => $hospitalVersion->id, 'hospital_account_id' => $hospitalAccount->id]);
        $mapping->setAttributes([
            'fiscal_year' => $hospitalVersion->fiscal_year,
            'standard_version_id' => $standardVersion->id,
            'standard_account_id' => $standardAccount->id,
            'match_type' => AccountingChartMapping::TYPE_MANUAL,
            'status' => AccountingChartMapping::STATUS_CONFIRMED,
            'note' => trim((string) Yii::$app->request->post('note')) ?: null,
            'decided_at' => date('Y-m-d H:i:s'),
            'decided_by' => Yii::$app->user->id,
        ]);
        Yii::$app->session->setFlash($mapping->save() ? 'success' : 'error', $mapping->hasErrors() ? 'บันทึกการจับคู่ไม่สำเร็จ' : 'บันทึกการจับคู่ด้วยตนเองแล้ว');
        return $this->redirect(['mappings', 'id' => $hospitalVersion->id]);
    }

    public function actionMarkHospitalOnly($id)
    {
        $hospitalVersion = $this->findVersion($id);
        $standardVersion = $this->findStandardVersion($hospitalVersion);
        $hospitalAccount = AccountingChartAccount::findOne((int) Yii::$app->request->post('hospital_account_id'));
        if (!$standardVersion || !$hospitalAccount || (int) $hospitalAccount->version_id !== (int) $hospitalVersion->id || !in_array($hospitalAccount->category, ['4', '5'], true)) {
            Yii::$app->session->setFlash('error', 'ไม่พบบัญชีโรงพยาบาลที่เลือก');
            return $this->redirect(['mappings', 'id' => $hospitalVersion->id]);
        }
        $mapping = AccountingChartMapping::findOne(['hospital_version_id' => $hospitalVersion->id, 'hospital_account_id' => $hospitalAccount->id])
            ?: new AccountingChartMapping(['hospital_version_id' => $hospitalVersion->id, 'hospital_account_id' => $hospitalAccount->id]);
        $mapping->setAttributes([
            'fiscal_year' => $hospitalVersion->fiscal_year,
            'standard_version_id' => $standardVersion->id,
            'standard_account_id' => null,
            'match_type' => AccountingChartMapping::TYPE_HOSPITAL_ONLY,
            'status' => AccountingChartMapping::STATUS_CONFIRMED,
            'note' => trim((string) Yii::$app->request->post('note')) ?: 'บัญชีเฉพาะโรงพยาบาล ไม่มีคู่ในผังมาตรฐาน',
            'decided_at' => date('Y-m-d H:i:s'),
            'decided_by' => Yii::$app->user->id,
        ]);
        Yii::$app->session->setFlash($mapping->save() ? 'success' : 'error', $mapping->hasErrors() ? 'บันทึกผลไม่สำเร็จ' : 'ระบุเป็นบัญชีเฉพาะโรงพยาบาลแล้ว');
        return $this->redirect(['mappings', 'id' => $hospitalVersion->id]);
    }

    public function actionResetMapping($id)
    {
        $mapping = AccountingChartMapping::findOne($id);
        if (!$mapping) throw new NotFoundHttpException('ไม่พบรายการจับคู่');
        $versionId = $mapping->hospital_version_id;
        $deleted = $mapping->delete() !== false;
        Yii::$app->session->setFlash($deleted ? 'success' : 'error', $deleted ? 'นำผลตัดสินออกแล้ว สามารถเลือกใหม่ได้' : 'นำผลตัดสินออกไม่สำเร็จ');
        return $this->redirect(['mappings', 'id' => $versionId]);
    }

    private function findVersion($id): AccountingChartVersion
    {
        if (!$model = AccountingChartVersion::findOne($id)) {
            throw new NotFoundHttpException('ไม่พบผังบัญชี');
        }
        return $model;
    }

    private function updateMappingStatus($id, string $status, string $message)
    {
        $mapping = AccountingChartMapping::findOne($id);
        if (!$mapping) throw new NotFoundHttpException('ไม่พบรายการจับคู่');
        if ($mapping->match_type !== AccountingChartMapping::TYPE_PARENT || $mapping->status !== AccountingChartMapping::STATUS_SUGGESTED) {
            Yii::$app->session->setFlash('error', 'รายการนี้ไม่ใช่คำแนะนำที่รอตรวจ กรุณาเลือกบัญชีใหม่ด้วยตนเอง');
            return $this->redirect(['mappings', 'id' => $mapping->hospital_version_id]);
        }
        $mapping->status = $status;
        $mapping->decided_at = date('Y-m-d H:i:s');
        $mapping->decided_by = Yii::$app->user->id;
        if ($mapping->save()) {
            Yii::$app->session->setFlash('success', $message);
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกผลการจับคู่ไม่สำเร็จ');
        }
        return $this->redirect(['mappings', 'id' => $mapping->hospital_version_id]);
    }

    private function findStandardVersion(AccountingChartVersion $hospitalVersion): ?AccountingChartVersion
    {
        if ($hospitalVersion->scope !== AccountingChartVersion::SCOPE_HOSPITAL) return null;
        $existing = AccountingChartMapping::find()->where(['hospital_version_id' => $hospitalVersion->id])->one();
        if ($existing) return AccountingChartVersion::findOne($existing->standard_version_id);
        return AccountingChartVersion::find()
            ->where(['fiscal_year' => $hospitalVersion->fiscal_year, 'scope' => AccountingChartVersion::SCOPE_STANDARD, 'status' => [AccountingChartVersion::STATUS_ACTIVE, AccountingChartVersion::STATUS_DRAFT]])
            ->orderBy(new \yii\db\Expression("CASE status WHEN 'active' THEN 0 WHEN 'draft' THEN 1 ELSE 2 END, id DESC"))
            ->one();
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
