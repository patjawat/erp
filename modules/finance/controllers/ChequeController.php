<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\components\AppHelper;
use app\modules\finance\models\FinanceCheque;
use app\modules\finance\models\FinanceChequeTemplate;
use app\modules\finance\models\FinanceCashAccount;
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
                ['allow' => true, 'actions' => ['index', 'view', 'template', 'preview', 'test-print', 'print', 'next-cheque-no'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['create', 'calibrate', 'create-template', 'upload-background', 'status', 'void'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'create' => ['GET', 'POST'], 'calibrate' => ['GET', 'POST'], 'create-template' => ['GET', 'POST'],
                'upload-background' => ['POST'], 'status' => ['POST'], 'void' => ['POST'],
            ]],
        ]);
    }

    public function getViewPath()
    {
        return Yii::getAlias('@app/modules/finance/views/cheque');
    }

    /** ทะเบียนคุมเช็ค + ตัวกรอง + สรุปยอด */
    public function actionIndex()
    {
        $req = Yii::$app->request;
        $q = trim((string) $req->get('q', ''));
        $status = (string) $req->get('status', '');

        $query = FinanceCheque::find()->orderBy(['id' => SORT_DESC]);
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'cheque_no', $q], ['like', 'payee_name', $q], ['like', 'cheque_book_no', $q]]);
        }
        if (isset(FinanceCheque::statusOptions()[$status])) {
            $query->andWhere(['status' => $status]);
        }

        // สรุปจำนวน+ยอด แยกตามสถานะ (นับทั้งทะเบียน ไม่อิงตัวกรอง)
        $summary = [];
        $rows = FinanceCheque::find()->select(['status', 'c' => 'COUNT(*)', 's' => 'SUM(amount)'])
            ->groupBy('status')->asArray()->all();
        foreach ($rows as $r) {
            $summary[$r['status']] = ['count' => (int) $r['c'], 'sum' => (float) $r['s']];
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]),
            'q' => $q,
            'status' => $status,
            'summary' => $summary,
        ]);
    }

    /** ออกเช็คใหม่เอง (ไม่ผ่านจ่ายเจ้าหนี้) — คีย์ข้อมูล → บันทึกทะเบียน → พิมพ์ */
    public function actionCreate()
    {
        $cheque = new FinanceCheque(['status' => FinanceCheque::STATUS_DRAFT, 'is_ac_payee' => 1]);
        $req = Yii::$app->request;

        if ($req->isPost) {
            $cheque->cash_account_id = (int) $req->post('cash_account_id', 0) ?: null;
            $cheque->template_id = (int) $req->post('template_id', 0) ?: null;
            $cheque->cheque_no = trim((string) $req->post('cheque_no', ''));
            $cheque->cheque_book_no = trim((string) $req->post('cheque_book_no', '')) ?: null;
            $cheque->cheque_date = AppHelper::normalizeDateToDb((string) $req->post('cheque_date', '')) ?: null;
            $cheque->payee_name = trim((string) $req->post('payee_name', ''));
            $cheque->amount = (float) str_replace([',', ' '], '', (string) $req->post('amount', '0'));
            $cheque->is_ac_payee = $req->post('is_ac_payee') ? 1 : 0;

            if ($cheque->validate()) {
                // กันเลขเช็คซ้ำต่อบัญชี (unique index) ให้ error สวย ๆ แทน exception
                $dup = $cheque->cash_account_id && FinanceCheque::find()
                    ->where(['cash_account_id' => $cheque->cash_account_id, 'cheque_no' => $cheque->cheque_no])->exists();
                if ($dup) {
                    $cheque->addError('cheque_no', 'เลขที่เช็คนี้มีในบัญชีจ่ายนี้แล้ว');
                } elseif ($cheque->save(false)) {
                    Yii::$app->session->setFlash('success', 'บันทึกเช็คเข้าทะเบียนแล้ว — กดพิมพ์เพื่อออกเช็ค');
                    return $this->redirect(['view', 'id' => $cheque->id]);
                }
            }
        }

        $accounts = FinanceCashAccount::find()->where(['is_active' => 1])
            ->andWhere(['in', 'account_type', [FinanceCashAccount::TYPE_BANK, FinanceCashAccount::TYPE_TREASURY]])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();

        return $this->render('create', [
            'cheque' => $cheque,
            'accounts' => \yii\helpers\ArrayHelper::map($accounts, 'id', fn($a) => $a->label()),
            'templates' => FinanceChequeTemplate::activeList(),
        ]);
    }

    /** คืนเลขที่เช็คถัดไปของบัญชีจ่าย (AJAX) */
    public function actionNextChequeNo($account_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['next' => FinanceCheque::nextChequeNo((int) $account_id)];
    }

    /** รายละเอียดเช็ค + เดินสถานะ */
    public function actionView($id)
    {
        return $this->render('view', ['cheque' => $this->findCheque($id)]);
    }

    /** เดินสถานะเช็ค (พิมพ์แล้ว/ส่งมอบ/ขึ้นเงิน/เด้ง) */
    public function actionStatus($id)
    {
        $cheque = $this->findCheque($id);
        $to = (string) Yii::$app->request->post('to', '');
        if ($cheque->moveTo($to)) {
            Yii::$app->session->setFlash('success', 'เปลี่ยนสถานะเป็น "' . $cheque->statusLabel() . '" แล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'เปลี่ยนสถานะไม่ได้ (ขั้นตอนไม่ถูกต้อง หรือเช็คถูกยกเลิกแล้ว)');
        }
        return $this->redirect(['view', 'id' => $cheque->id]);
    }

    /** ยกเลิกเช็ค (เช็คเสีย/พิมพ์ผิด) โดยคงเลขไว้ในทะเบียน */
    public function actionVoid($id)
    {
        $cheque = $this->findCheque($id);
        $reason = trim((string) Yii::$app->request->post('reason', ''));
        if ($reason === '') {
            Yii::$app->session->setFlash('error', 'กรุณาระบุเหตุผลการยกเลิก');
        } elseif ($cheque->isVoid()) {
            Yii::$app->session->setFlash('info', 'เช็คนี้ถูกยกเลิกแล้ว');
        } elseif ($cheque->void($reason)) {
            Yii::$app->session->setFlash('success', 'ยกเลิกเช็คเลขที่ ' . $cheque->cheque_no . ' แล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ยกเลิกไม่สำเร็จ');
        }
        return $this->redirect(['view', 'id' => $cheque->id]);
    }

    /** รายการแม่แบบเช็ค */
    public function actionTemplate()
    {
        return $this->render('template', [
            'templates' => FinanceChequeTemplate::find()->orderBy(['bank_name' => SORT_ASC, 'name' => SORT_ASC])->all(),
        ]);
    }

    /** สร้างแม่แบบเช็คธนาคารใหม่ */
    public function actionCreateTemplate()
    {
        $tpl = new FinanceChequeTemplate([
            'page_width_mm' => 178,
            'page_height_mm' => 82,
            'is_active' => 1,
        ]);
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $tpl->bank_name = trim((string) ($post['bank_name'] ?? ''));
            $tpl->bank_code = trim((string) ($post['bank_code'] ?? '')) ?: null;
            $tpl->name = trim((string) ($post['name'] ?? ''));
            $tpl->page_width_mm = (float) ($post['page_width_mm'] ?? 178);
            $tpl->page_height_mm = (float) ($post['page_height_mm'] ?? 82);
            $tpl->layout_json = \yii\helpers\Json::encode(FinanceChequeTemplate::defaultLayout());
            if ($tpl->validate() && $tpl->save()) {
                Yii::$app->session->setFlash('success', 'สร้างแม่แบบแล้ว — ปรับตำแหน่งให้ตรงเช็คจริงได้เลย');
                return $this->redirect(['calibrate', 'id' => $tpl->id]);
            }
        }
        return $this->render('create-template', ['tpl' => $tpl]);
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
                    'pitch' => (float) ($f['pitch'] ?? 0),
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

    /** พรีวิว PDF (มีพื้นหลังสแกน) สำหรับ iframe บนหน้า calibrate/ออกเช็ค */
    public function actionPreview($id = 0)
    {
        $tpl = FinanceChequeTemplate::findOne($id)
            ?: FinanceChequeTemplate::find()->where(['is_active' => 1])->orderBy(['id' => SORT_ASC])->one();
        if (!$tpl) {
            throw new NotFoundHttpException('ยังไม่มีแม่แบบเช็ค');
        }
        return $this->outputPdf($tpl, $this->sampleData(), true);
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
        // พิมพ์จากทะเบียนครั้งแรก → เดินสถานะ "พิมพ์แล้ว" อัตโนมัติ
        if ($cheque->status === FinanceCheque::STATUS_DRAFT) {
            $cheque->moveTo(FinanceCheque::STATUS_PRINTED);
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
                'pitch' => $f['pitch'] ?? 0,
                'enabled' => !empty($f['enabled']),
            ];
        }
        return $out;
    }

    private function findCheque($id): FinanceCheque
    {
        $cheque = FinanceCheque::findOne($id);
        if (!$cheque) {
            throw new NotFoundHttpException('ไม่พบเช็ค');
        }
        return $cheque;
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
