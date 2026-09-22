<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\modules\finance\models\FinanceCheque;
use app\modules\finance\models\FinanceChequeTemplate;
use app\modules\finance\services\ChequePrintService;

/**
 * โปรแกรมพิมพ์เช็ค — จัดการแม่แบบ/ปรับตำแหน่ง/พิมพ์ + ทะเบียนคุมเช็ค
 */
class ChequeController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'template', 'preview', 'test-print', 'print'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['calibrate', 'upload-background', 'void'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'calibrate' => ['GET', 'POST'], 'upload-background' => ['POST'], 'void' => ['POST'],
            ]],
        ]);
    }

    public function getViewPath()
    {
        return Yii::getAlias('@app/modules/finance/views/cheque');
    }

    /** ทะเบียนคุมเช็ค */
    public function actionIndex()
    {
        $query = FinanceCheque::find()->orderBy(['id' => SORT_DESC]);
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]),
        ]);
    }

    /** รายการแม่แบบเช็ค */
    public function actionTemplate()
    {
        return $this->render('template', [
            'templates' => FinanceChequeTemplate::find()->orderBy(['bank_name' => SORT_ASC, 'name' => SORT_ASC])->all(),
        ]);
    }

    /** ปรับตำแหน่งช่องพิมพ์ของแม่แบบ (calibrate) */
    public function actionCalibrate($id)
    {
        $tpl = $this->findTemplate($id);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $tpl->page_width_mm = (float) ($post['page_width_mm'] ?? $tpl->page_width_mm);
            $tpl->page_height_mm = (float) ($post['page_height_mm'] ?? $tpl->page_height_mm);
            $tpl->calibrate_offset_x = (float) ($post['calibrate_offset_x'] ?? 0);
            $tpl->calibrate_offset_y = (float) ($post['calibrate_offset_y'] ?? 0);

            $layout = [];
            foreach ((array) ($post['field'] ?? []) as $key => $f) {
                $layout[] = [
                    'key' => $key,
                    'x' => (float) ($f['x'] ?? 0),
                    'y' => (float) ($f['y'] ?? 0),
                    'font_size' => (float) ($f['font_size'] ?? 16),
                    'align' => in_array($f['align'] ?? 'L', ['L', 'C', 'R'], true) ? $f['align'] : 'L',
                    'bold' => !empty($f['bold']) ? 1 : 0,
                    'enabled' => !empty($f['enabled']) ? 1 : 0,
                ];
            }
            $tpl->layout_json = \yii\helpers\Json::encode($layout);
            if ($tpl->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกตำแหน่งแม่แบบแล้ว');
            } else {
                Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(', ', $tpl->getFirstErrors()));
            }
            return $this->redirect(['calibrate', 'id' => $tpl->id]);
        }

        return $this->render('calibrate', [
            'tpl' => $tpl,
            'fields' => $this->mergedFields($tpl),
        ]);
    }

    /** พรีวิว PDF (มีพื้นหลังสแกน) สำหรับ iframe บนหน้า calibrate */
    public function actionPreview($id)
    {
        return $this->outputPdf($this->findTemplate($id), $this->sampleData(), true);
    }

    /** พิมพ์จริง (ไม่มีพื้นหลัง) — ทดสอบวางทาบเช็คจริง */
    public function actionTestPrint($id)
    {
        return $this->outputPdf($this->findTemplate($id), $this->sampleData(), false);
    }

    /** พิมพ์เช็คจากทะเบียน */
    public function actionPrint($id)
    {
        $cheque = FinanceCheque::findOne($id);
        if (!$cheque) {
            throw new NotFoundHttpException('ไม่พบเช็ค');
        }
        $tpl = $cheque->template_id ? FinanceChequeTemplate::findOne($cheque->template_id) : null;
        if (!$tpl) {
            $tpl = FinanceChequeTemplate::find()->where(['is_active' => 1])->one();
        }
        if (!$tpl) {
            throw new NotFoundHttpException('ยังไม่มีแม่แบบเช็ค');
        }
        return $this->outputPdf($tpl, [
            'cheque_date' => $cheque->cheque_date,
            'payee' => $cheque->payee_name,
            'amount' => (float) $cheque->amount,
            'print_ac_payee' => (int) $cheque->is_ac_payee === 1,
        ], false);
    }

    /** อัปโหลดรูปสแกนเช็คเปล่าเป็นพื้นหลังพรีวิว */
    public function actionUploadBackground($id)
    {
        $tpl = $this->findTemplate($id);
        $file = UploadedFile::getInstanceByName('background');
        if ($file) {
            $dir = Yii::getAlias('@webroot') . '/uploads/cheque';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $ext = strtolower($file->extension ?: 'jpg');
            $name = 'tpl' . $tpl->id . '_' . time() . '.' . $ext;
            if ($file->saveAs($dir . '/' . $name)) {
                $tpl->background_path = 'uploads/cheque/' . $name;
                $tpl->save(false, ['background_path', 'updated_at', 'updated_by']);
                Yii::$app->session->setFlash('success', 'อัปโหลดรูปพื้นหลังแล้ว');
            } else {
                Yii::$app->session->setFlash('error', 'บันทึกไฟล์ไม่สำเร็จ');
            }
        }
        return $this->redirect(['calibrate', 'id' => $tpl->id]);
    }

    // ---- helpers ----

    private function outputPdf(FinanceChequeTemplate $tpl, array $data, bool $preview)
    {
        $pdf = (new ChequePrintService())->renderPdf($tpl, $data, $preview);
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="cheque.pdf"');
        $response->content = $pdf;
        return $response;
    }

    private function sampleData(): array
    {
        $req = Yii::$app->request;
        return [
            'cheque_date' => $req->get('d', date('Y-m-d')),
            'payee' => $req->get('p', 'ตัวอย่าง ผู้รับเงิน จำกัด'),
            'amount' => (float) $req->get('a', 53460),
            'print_ac_payee' => $req->get('ac', '1') === '1',
        ];
    }

    /** รวมฟิลด์มาตรฐานกับพิกัดที่บันทึกไว้ ให้ view แสดงครบทุกฟิลด์ */
    private function mergedFields(FinanceChequeTemplate $tpl): array
    {
        $saved = [];
        foreach ($tpl->layout() as $f) {
            if (!empty($f['key'])) {
                $saved[$f['key']] = $f;
            }
        }
        $out = [];
        foreach (FinanceChequeTemplate::FIELDS as $key => $label) {
            $f = $saved[$key] ?? [];
            $out[] = [
                'key' => $key,
                'label' => $label,
                'x' => $f['x'] ?? 0,
                'y' => $f['y'] ?? 0,
                'font_size' => $f['font_size'] ?? 16,
                'align' => $f['align'] ?? 'L',
                'bold' => !empty($f['bold']),
                'enabled' => !empty($f['enabled']),
            ];
        }
        return $out;
    }

    private function findTemplate($id): FinanceChequeTemplate
    {
        $tpl = FinanceChequeTemplate::findOne($id);
        if (!$tpl) {
            throw new NotFoundHttpException('ไม่พบแม่แบบเช็ค');
        }
        return $tpl;
    }
}
