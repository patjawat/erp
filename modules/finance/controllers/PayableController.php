<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayablePayment;
use app\modules\finance\models\FinanceCheque;
use app\modules\finance\models\FinanceChequeTemplate;
use app\modules\finance\models\FinanceCashAccount;
use app\modules\finance\services\FinancePayableDraftService;
use app\modules\finance\services\FinancePayablePaymentService;

class PayableController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view', 'aging', 'letter', 'payments'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['cancel-payment'], 'roles' => ['financeOperate']],
                ['allow' => true, 'actions' => ['approve-payment', 'reject-payment'], 'roles' => ['financeApprove']],
                ['allow' => true, 'actions' => ['create', 'update', 'send-accounting', 'send-accounting-bulk', 'pay', 'billing'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'update' => ['GET', 'POST'], 'pay' => ['GET', 'POST'], 'billing' => ['GET', 'POST'], 'cancel-payment' => ['POST'], 'approve-payment' => ['POST'], 'reject-payment' => ['POST'], 'send-accounting' => ['POST'], 'send-accounting-bulk' => ['POST'],
            ]],
        ]);
    }

    public function getViewPath()
    {
        return Yii::getAlias('@app/modules/finance/views/payable');
    }

    public function actionIndex()
    {
        $req = Yii::$app->request;
        $q = trim((string) $req->get('q', ''));
        $sent = (string) $req->get('sent', '');
        $payment = (string) $req->get('payment', '');
        $billing = (string) $req->get('billing', '');

        $query = FinancePayable::find()->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'vendor_name_snapshot', $q],
                ['like', 'invoice_no', $q],
                ['like', 'payable_no', $q],
            ]);
        }
        if ($sent === 'yes') {
            $query->andWhere(['not', ['sent_accounting_at' => null]]);
        } elseif ($sent === 'no') {
            $query->andWhere(['sent_accounting_at' => null]);
        }
        if ($billing === 'unbilled') {
            $query->andWhere(['status' => FinancePayable::STATUS_APPROVED, 'billing_id' => null]);
        } elseif ($billing === 'billed') {
            $query->andWhere(['not', ['billing_id' => null]]);
        }
        // กรองตามสถานะการจ่าย (คำนวณจากยอดตัดหนี้)
        if (in_array($payment, ['unpaid', 'partial', 'paid'], true)) {
            $paid = '(SELECT COALESCE(SUM(fps.amount),0) FROM finance_payable_settlement fps WHERE fps.payable_id = finance_payable.id)';
            if ($payment === 'unpaid') {
                $query->andWhere($paid . ' <= 0.005');
            } elseif ($payment === 'partial') {
                $query->andWhere($paid . ' > 0.005 AND ' . $paid . ' + 0.005 < finance_payable.net_amount');
            } else {
                $query->andWhere($paid . ' + 0.005 >= finance_payable.net_amount');
            }
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]),
            'q' => $q,
            'sent' => $sent,
            'payment' => $payment,
            'billing' => $billing,
        ]);
    }

    /** รายงานเจ้าหนี้ค้างชำระ (aging) — หนี้ที่อนุมัติแล้วและยังคงค้าง จัด bucket ตามวันครบกำหนด */
    public function actionAging()
    {
        $sql = "
            SELECT p.id, p.payable_no, p.vendor_name_snapshot, p.invoice_no, p.due_date,
                   p.net_amount, COALESCE(s.paid, 0) AS paid,
                   (p.net_amount - COALESCE(s.paid, 0)) AS outstanding
            FROM {{%finance_payable}} p
            LEFT JOIN (
                SELECT payable_id, SUM(amount) AS paid
                FROM {{%finance_payable_settlement}} GROUP BY payable_id
            ) s ON s.payable_id = p.id
            WHERE p.status = :approved AND (p.net_amount - COALESCE(s.paid, 0)) > 0.005
            ORDER BY p.due_date ASC, p.id ASC
        ";
        $rows = Yii::$app->db->createCommand($sql, [':approved' => FinancePayable::STATUS_APPROVED])->queryAll();

        $today = new \DateTimeImmutable('today');
        $monthEnd = new \DateTimeImmutable('last day of this month');
        $buckets = [
            'not_due' => ['label' => 'ยังไม่ถึงกำหนด', 'total' => 0.0, 'count' => 0],
            'd30' => ['label' => 'เกิน 1–30 วัน', 'total' => 0.0, 'count' => 0],
            'd60' => ['label' => 'เกิน 31–60 วัน', 'total' => 0.0, 'count' => 0],
            'd90' => ['label' => 'เกิน 61–90 วัน', 'total' => 0.0, 'count' => 0],
            'd90p' => ['label' => 'เกิน 90 วัน', 'total' => 0.0, 'count' => 0],
        ];
        $sumOutstanding = 0.0;
        $sumOverdue = 0.0;
        $sumDueThisMonth = 0.0;

        foreach ($rows as &$r) {
            $out = (float) $r['outstanding'];
            $sumOutstanding += $out;
            $due = $r['due_date'] ? new \DateTimeImmutable($r['due_date']) : null;
            $overdueDays = $due ? (int) $today->diff($due)->format('%r%a') : 0; // ลบ = เกินกำหนด
            $overdueDays = -$overdueDays; // จำนวนวันที่เกินกำหนด (บวก = เลยมาแล้ว)
            if (!$due || $overdueDays <= 0) {
                $key = 'not_due';
                if ($due && $due <= $monthEnd) {
                    $sumDueThisMonth += $out;
                }
            } else {
                $sumOverdue += $out;
                $key = $overdueDays <= 30 ? 'd30' : ($overdueDays <= 60 ? 'd60' : ($overdueDays <= 90 ? 'd90' : 'd90p'));
            }
            $buckets[$key]['total'] += $out;
            $buckets[$key]['count']++;
            $r['bucket'] = $key;
            $r['overdue_days'] = $overdueDays;
        }
        unset($r);

        return $this->render('aging', [
            'rows' => $rows,
            'buckets' => $buckets,
            'sumOutstanding' => $sumOutstanding,
            'sumOverdue' => $sumOverdue,
            'sumDueThisMonth' => $sumDueThisMonth,
        ]);
    }

    /** จ่ายชำระรายเจ้าหนี้: เลือกบริษัท → ติ๊กบิลที่จ่าย → บันทึก batch (1 เช็ค + 1 หนังสือนำส่ง) */
    public function actionPay()
    {
        $req = Yii::$app->request;

        if ($req->isPost) {
            return $this->processPayment($req);
        }

        $vendor = trim((string) $req->get('vendor', ''));
        if ($vendor === '') {
            return $this->render('pay', ['mode' => 'vendors', 'vendors' => $this->outstandingVendors()]);
        }
        // บัญชีจ่าย (ธนาคาร/เงินฝากคลัง) + meta สำหรับ autofill ธนาคาร/สาขา
        $accounts = FinanceCashAccount::find()->where(['is_active' => 1])
            ->andWhere(['in', 'account_type', [FinanceCashAccount::TYPE_BANK, FinanceCashAccount::TYPE_TREASURY]])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        $accountMeta = [];
        foreach ($accounts as $a) {
            $accountMeta[$a->id] = ['bank' => (string) $a->bank_name, 'branch' => (string) $a->branch];
        }

        return $this->render('pay', [
            'mode' => 'bills',
            'vendor' => $vendor,
            'rows' => $this->outstandingBills($vendor),
            'locked' => FinancePayablePaymentService::pendingPayableIds(),
            'today' => date('d/m/') . ((int) date('Y') + 543),
            'accounts' => ArrayHelper::map($accounts, 'id', fn(FinanceCashAccount $a) => $a->label()),
            'accountMeta' => $accountMeta,
            'templates' => FinanceChequeTemplate::activeList(),
            'categoryOptions' => FinancePayablePaymentService::categoryOptions(),
        ]);
    }

    /** ย้ายไปทะเบียนรับวางบิล (/finance/billing) */
    public function actionBilling()
    {
        return $this->redirect(['/finance/billing/index']);
    }

    /** รายการรอบจ่ายเจ้าหนี้ (ล่าสุดก่อน) — ดูหนังสือนำส่ง/ใบสำคัญ/เช็ค และยกเลิกรอบจ่าย */
    public function actionPayments()
    {
        $q = trim((string) Yii::$app->request->get('q', ''));
        $query = FinancePayablePayment::find()->orderBy(['pay_date' => SORT_DESC, 'id' => SORT_DESC]);
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'vendor_name_snapshot', $q], ['like', 'cheque_no', $q], ['like', 'doc_no', $q]]);
        }
        return $this->render('payments', [
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]),
            'q' => $q,
        ]);
    }

    /** อนุมัติรอบจ่าย → ตัดหนี้ + ใบสำคัญจ่าย + เช็ค */
    public function actionApprovePayment($id)
    {
        $pay = $this->findPayment($id);
        $tx = Yii::$app->db->beginTransaction();
        try {
            (new FinancePayablePaymentService())->approve($pay);
            $tx->commit();
            Yii::$app->session->setFlash('success', 'อนุมัติรอบจ่าย #' . $pay->id . ' แล้ว — ตัดหนี้และออกใบสำคัญจ่าย/เช็คเรียบร้อย');
        } catch (\DomainException $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'อนุมัติรอบจ่ายไม่สำเร็จ');
        }
        return $this->redirect(['payments']);
    }

    public function actionRejectPayment($id)
    {
        $pay = $this->findPayment($id);
        try {
            (new FinancePayablePaymentService())->reject($pay, (string) Yii::$app->request->post('reason', ''));
            Yii::$app->session->setFlash('success', 'ไม่อนุมัติรอบจ่าย #' . $pay->id . ' — บิลกลับไปเลือกจ่ายรอบใหม่ได้');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['payments']);
    }

    private function findPayment($id): FinancePayablePayment
    {
        $pay = FinancePayablePayment::findOne($id);
        if (!$pay) {
            throw new NotFoundHttpException('ไม่พบรอบจ่ายเจ้าหนี้');
        }
        return $pay;
    }

    /** ยกเลิกรอบจ่าย: คืนยอดคงค้าง + ลบใบสำคัญจ่าย + ยกเลิกเช็ค */
    public function actionCancelPayment($id)
    {
        $pay = FinancePayablePayment::findOne($id);
        if (!$pay) {
            throw new NotFoundHttpException('ไม่พบรอบจ่ายเจ้าหนี้');
        }
        $tx = Yii::$app->db->beginTransaction();
        try {
            (new FinancePayablePaymentService())->cancel($pay, (string) Yii::$app->request->post('reason', ''));
            $tx->commit();
            Yii::$app->session->setFlash('success', 'ยกเลิกรอบจ่าย #' . $pay->id . ' แล้ว — ยอดหนี้กลับมาค้าง ใบสำคัญจ่ายถูกลบ และเช็คถูกยกเลิก');
        } catch (\DomainException $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'ยกเลิกรอบจ่ายไม่สำเร็จ');
        }
        return $this->redirect(['payments']);
    }

    /** หนังสือนำส่งชำระเงิน (พิมพ์) จากรอบจ่าย */
    public function actionLetter($id)
    {
        $pay = FinancePayablePayment::findOne($id);
        if (!$pay) {
            throw new NotFoundHttpException('ไม่พบรอบจ่ายเจ้าหนี้');
        }
        if ($pay->status !== FinancePayablePaymentService::STATUS_PAID) {
            Yii::$app->session->setFlash('error', 'รอบจ่าย #' . $pay->id . ' ยังไม่ได้อนุมัติ — พิมพ์หนังสือนำส่งได้หลังอนุมัติ');
            return $this->redirect(['payments']);
        }
        $this->layout = false; // หน้าเอกสารพิมพ์ standalone
        return $this->render('letter', [
            'pay' => $pay,
            'lines' => $pay->paidLines(),
            'site' => SiteHelper::getInfo(),
            'cheque' => FinanceCheque::find()->where(['payment_id' => $pay->id])->one(),
        ]);
    }

    /** เจ้าหนี้ที่มีบิลค้าง (สรุปรายบริษัท) */
    private function outstandingVendors(): array
    {
        $sql = "
            SELECT p.vendor_name_snapshot AS vendor, COUNT(*) AS bills,
                   SUM(p.net_amount - COALESCE(s.paid, 0)) AS outstanding, MIN(p.due_date) AS earliest_due
            FROM {{%finance_payable}} p
            LEFT JOIN (SELECT payable_id, SUM(amount) paid FROM {{%finance_payable_settlement}} GROUP BY payable_id) s
              ON s.payable_id = p.id
            WHERE p.status = :a AND (p.net_amount - COALESCE(s.paid, 0)) > 0.005
            GROUP BY p.vendor_name_snapshot
            ORDER BY earliest_due ASC
        ";
        return Yii::$app->db->createCommand($sql, [':a' => FinancePayable::STATUS_APPROVED])->queryAll();
    }

    /** บิลค้างของเจ้าหนี้รายหนึ่ง */
    private function outstandingBills(string $vendor): array
    {
        $sql = "
            SELECT p.id, p.vendor_id, p.payable_no, p.invoice_no, p.due_date, p.gross_amount,
                   p.withholding_tax_amount, p.net_amount, COALESCE(s.paid, 0) AS paid,
                   (p.net_amount - COALESCE(s.paid, 0)) AS outstanding
            FROM {{%finance_payable}} p
            LEFT JOIN (SELECT payable_id, SUM(amount) paid FROM {{%finance_payable_settlement}} GROUP BY payable_id) s
              ON s.payable_id = p.id
            WHERE p.status = :a AND p.vendor_name_snapshot = :v AND (p.net_amount - COALESCE(s.paid, 0)) > 0.005
            ORDER BY p.due_date ASC, p.id ASC
        ";
        return Yii::$app->db->createCommand($sql, [':a' => FinancePayable::STATUS_APPROVED, ':v' => $vendor])->queryAll();
    }

    /** บันทึกรอบจ่าย: ตัดหนี้ + ใบสำคัญจ่ายเงินบำรุง + เช็ค (ทรานแซกชันเดียว) */
    private function processPayment($req)
    {
        $vendor = trim((string) $req->post('vendor', ''));
        $categories = (array) $req->post('category', []);
        $lines = [];
        foreach ((array) $req->post('pay', []) as $pid => $amt) {
            $lines[(int) $pid] = [
                'amount' => (float) str_replace([',', ' '], '', (string) $amt),
                'category_id' => (int) ($categories[$pid] ?? 0),
            ];
        }
        $head = [
            'vendor' => $vendor,
            'pay_date' => AppHelper::normalizeDateToDb((string) $req->post('pay_date')) ?: date('Y-m-d'),
            'cash_account_id' => (int) $req->post('cash_account_id', 0),
            'pay_method' => (string) $req->post('pay_method', 'cheque'),
            'cheque_no' => $req->post('cheque_no'),
            'bank_name' => $req->post('bank_name'),
            'bank_branch' => $req->post('bank_branch'),
            'doc_no' => $req->post('doc_no'),
            'subject' => $req->post('subject'),
            'note' => $req->post('note'),
            'template_id' => $req->post('template_id'),
            'cheque_book_no' => $req->post('cheque_book_no'),
            'is_ac_payee' => (bool) $req->post('is_ac_payee'),
        ];

        $tx = Yii::$app->db->beginTransaction();
        try {
            $pay = (new FinancePayablePaymentService())->request($lines, $head);
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกรอบจ่าย #' . $pay->id . ' รวม ' . number_format((float) $pay->net_total, 2)
                . ' บาท แล้ว — รอผู้อนุมัติกดอนุมัติ จึงจะตัดหนี้และออกใบสำคัญจ่าย/เช็ค');
            return $this->redirect(['payments']);
        } catch (\DomainException $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกจ่ายชำระไม่สำเร็จ');
        }
        return $this->redirect(['pay', 'vendor' => $vendor]);
    }

    /** การเงินส่งบิลให้บัญชีลงบันทึก */
    public function actionSendAccounting($id)
    {
        $model = $this->findPayable($id);
        if ($model->isSentAccounting()) {
            Yii::$app->session->setFlash('info', 'บิลนี้ส่งบัญชีไปแล้ว');
        } else {
            $model->sent_accounting_at = date('Y-m-d H:i:s');
            $model->sent_accounting_by = Yii::$app->user->id;
            $model->save(false, ['sent_accounting_at', 'sent_accounting_by']);
            Yii::$app->session->setFlash('success', 'ส่ง ' . $model->payable_no . ' ให้บัญชีแล้ว');
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['view', 'id' => $model->id]);
    }

    /** ส่งบัญชีทีละหลายรายการ (เลือกจากทะเบียนเจ้าหนี้) */
    public function actionSendAccountingBulk()
    {
        $ids = array_values(array_filter(array_map('intval', (array) Yii::$app->request->post('ids', []))));
        if (!$ids) {
            Yii::$app->session->setFlash('warning', 'ยังไม่ได้เลือกรายการที่จะส่งบัญชี');
            return $this->redirect(['index']);
        }
        $done = FinancePayable::updateAll(
            ['sent_accounting_at' => date('Y-m-d H:i:s'), 'sent_accounting_by' => Yii::$app->user->id],
            ['id' => $ids, 'status' => FinancePayable::STATUS_APPROVED, 'sent_accounting_at' => null]
        );
        Yii::$app->session->setFlash($done ? 'success' : 'warning', "ส่งบัญชีแล้ว {$done} รายการ");
        return $this->redirect(['index']);
    }

    /** ตั้งเจ้าหนี้ทำที่กล่องรอรับ (ปุ่ม "รับ") แล้ว — ลิงก์เดิมพาไปหน้ารายการในกล่อง */
    public function actionCreate($inbox_id)
    {
        return $this->redirect(['/finance/inbox/view', 'id' => (int) $inbox_id]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findPayable($id)]);
    }

    /** แก้ข้อมูลบิล: เลขใบแจ้งหนี้ / วันที่ใบแจ้งหนี้ / เครดิต / ภาษีหัก ณ ที่จ่าย / หมายเหตุ */
    public function actionUpdate($id)
    {
        $model = $this->findPayable($id);
        if ($reason = $this->lockReason($model)) {
            Yii::$app->session->setFlash('warning', $reason);
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $req = Yii::$app->request;
        if ($req->isPost) {
            $post = (array) $req->post('FinancePayable', []);
            $invoiceNo = FinancePayableDraftService::normalizeInvoiceNo((string) ($post['invoice_no'] ?? ''));
            $model->invoice_no = $invoiceNo !== '' ? $invoiceNo : null;
            $model->invoice_date = AppHelper::normalizeDateToDb($post['invoice_date'] ?? null) ?: $model->invoice_date;
            $model->credit_days = max(0, (int) ($post['credit_days'] ?? $model->credit_days));
            $model->withholding_tax_amount = round((float) str_replace(',', '', (string) ($post['withholding_tax_amount'] ?? 0)), 2);
            $model->note = trim((string) ($post['note'] ?? '')) ?: null;
            $model->net_amount = round((float) $model->gross_amount - (float) $model->withholding_tax_amount, 2);
            $model->due_date = FinancePayableDraftService::calculateDueDate((string) $model->billing_date, (int) $model->credit_days);

            if ($model->invoice_no && FinancePayable::find()->where(['vendor_id' => $model->vendor_id, 'invoice_no' => $model->invoice_no])
                    ->andWhere(['<>', 'id', $model->id])->exists()) {
                $model->addError('invoice_no', 'เลขใบแจ้งหนี้นี้ซ้ำกับบิลอื่นของบริษัทเดียวกัน');
            }
            if ((float) $model->withholding_tax_amount < 0 || (float) $model->withholding_tax_amount > (float) $model->gross_amount) {
                $model->addError('withholding_tax_amount', 'ภาษีหัก ณ ที่จ่ายต้องไม่ติดลบและไม่เกินยอดหนี้');
            }
            if (!$model->hasErrors()) {
                $model->save(false);
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลบิลแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', ['model' => $model]);
    }

    /** เหตุผลที่แก้ข้อมูลบิลไม่ได้ (null = แก้ได้) */
    private function lockReason(FinancePayable $model): ?string
    {
        if ($model->isSentAccounting()) {
            return 'บิลนี้ส่งบัญชีแล้ว แก้ข้อมูลไม่ได้';
        }
        if ($model->getPaidAmount() > 0.005 || isset(FinancePayablePaymentService::pendingPayableIds()[$model->id])) {
            return 'บิลนี้มีการจ่ายหรืออยู่ในรอบจ่ายแล้ว แก้ข้อมูลไม่ได้';
        }
        return null;
    }

    private function findPayable($id): FinancePayable
    {
        $model = FinancePayable::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบบิลในทะเบียนเจ้าหนี้');
        }
        return $model;
    }
}
