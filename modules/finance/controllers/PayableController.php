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
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinanceInboxReview;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayableReview;
use app\modules\finance\services\FinanceInboxReviewService;
use app\modules\finance\models\FinancePayableSettlement;
use app\modules\finance\models\FinancePayablePayment;
use app\modules\finance\models\FinanceCheque;
use app\modules\finance\models\FinanceChequeTemplate;
use app\modules\finance\models\FinanceCashAccount;
use app\modules\finance\services\FinancePayableDraftService;
use app\modules\finance\services\FinancePayableApprovalService;
use app\modules\finance\services\FinancePayablePaymentService;
use app\modules\accounting\models\AccountingChartAccount;
use app\modules\sm\models\Vendor;

class PayableController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view', 'aging', 'letter', 'payments'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['cancel-payment'], 'roles' => ['financeOperate']],
                ['allow' => true, 'actions' => ['create', 'update', 'submit', 'send-accounting', 'send-accounting-bulk'], 'roles' => ['financeOperate']],
                ['allow' => true, 'actions' => ['review'], 'roles' => ['financeApprove', 'financeOperate']],
                ['allow' => true, 'actions' => ['pay', 'billing'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'create' => ['GET', 'POST'], 'update' => ['GET', 'POST'], 'submit' => ['POST'], 'review' => ['POST'],
                'pay' => ['GET', 'POST'], 'billing' => ['GET', 'POST'], 'cancel-payment' => ['POST'], 'send-accounting' => ['POST'], 'send-accounting-bulk' => ['POST'],
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
        $status = (string) $req->get('status', '');
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
        if ($status !== '' && isset(FinancePayable::statusOptions()[$status])) {
            $query->andWhere(['status' => $status]);
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
            'status' => $status,
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
            $pay = (new FinancePayablePaymentService())->pay($lines, $head);
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกจ่ายชำระ ' . count($pay->settlements) . ' บิล รวม '
                . number_format((float) $pay->net_total, 2) . ' บาท และออกใบสำคัญจ่ายเงินบำรุงแล้ว');
            return $this->redirect(['letter', 'id' => $pay->id]);
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

    /** การเงินส่งเจ้าหนี้ (อนุมัติแล้ว) ให้บัญชีลงบันทึก */
    public function actionSendAccounting($id)
    {
        $model = $this->findPayable($id);
        if ($model->status !== FinancePayable::STATUS_APPROVED) {
            Yii::$app->session->setFlash('warning', 'ส่งบัญชีได้เฉพาะรายการที่อนุมัติเข้าทะเบียนแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ($model->isSentAccounting()) {
            Yii::$app->session->setFlash('info', 'รายการนี้ส่งบัญชีไปแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->sent_accounting_at = date('Y-m-d H:i:s');
        $model->sent_accounting_by = Yii::$app->user->id;
        $model->save(false, ['sent_accounting_at', 'sent_accounting_by']);
        Yii::$app->session->setFlash('success', 'ส่งให้บัญชีลงบันทึกแล้ว');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /** ส่งบัญชีทีละหลายรายการ (เลือกจากทะเบียนคุมเจ้าหนี้) */
    public function actionSendAccountingBulk()
    {
        $ids = array_values(array_filter(array_map('intval', (array) Yii::$app->request->post('ids', []))));
        if (!$ids) {
            Yii::$app->session->setFlash('warning', 'ยังไม่ได้เลือกรายการที่จะส่งบัญชี');
            return $this->redirect(['index']);
        }
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->user->id;
        $done = 0;
        $skipped = 0;
        foreach (FinancePayable::find()->where(['id' => $ids])->all() as $model) {
            if ($model->status !== FinancePayable::STATUS_APPROVED || $model->isSentAccounting()) {
                $skipped++;
                continue;
            }
            $model->sent_accounting_at = $now;
            $model->sent_accounting_by = $userId;
            $model->save(false, ['sent_accounting_at', 'sent_accounting_by']);
            $done++;
        }
        Yii::$app->session->setFlash(
            $done ? 'success' : 'warning',
            "ส่งบัญชีแล้ว {$done} รายการ" . ($skipped ? " (ข้าม {$skipped} รายการที่ยังไม่อนุมัติหรือส่งแล้ว)" : '')
        );
        return $this->redirect(['index']);
    }

    public function actionCreate($inbox_id)
    {
        $inbox = $this->findInbox($inbox_id);
        $existing = FinancePayable::findOne(['finance_inbox_id' => $inbox->id]);
        if ($existing) {
            return $this->redirect(['view', 'id' => $existing->id]);
        }
        // ตั้งเจ้าหนี้ได้จากรายการที่ยังรอตรวจ (จะรับรองให้พร้อมกัน) หรือรายการที่รับรองแล้ว
        if (!in_array($inbox->status, [FinanceInbox::STATUS_PENDING_REVIEW, FinanceInbox::STATUS_ACCEPTED], true)) {
            Yii::$app->session->setFlash('warning', 'ตั้งเจ้าหนี้ได้เฉพาะรายการที่รอตรวจหรือรับรองแล้ว');
            return $this->redirect(['/finance/inbox/view', 'id' => $inbox->id]);
        }

        $service = new FinancePayableDraftService();
        $model = $service->prepare($inbox);
        if ($model->load(Yii::$app->request->post())) {
            $this->normalizeFormDates($model);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // รอตรวจ → รับรองเอกสารในจังหวะเดียวกับตั้งเจ้าหนี้ (transaction เดียว)
                if ($inbox->status === FinanceInbox::STATUS_PENDING_REVIEW) {
                    (new FinanceInboxReviewService())->review($inbox, FinanceInboxReview::DECISION_ACCEPT, 'รับรองและตั้งเจ้าหนี้');
                    $inbox->refresh();
                }
                $service->create($inbox, $model);
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'รับรองเอกสารและตั้งเจ้าหนี้ (ร่างทะเบียนเจ้าหนี้) เรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\DomainException $e) {
                $transaction->rollBack();
                $inbox->refresh();
                $model->addError($this->domainErrorAttribute($e), $e->getMessage());
            } catch (\Throwable $e) {
                $transaction->rollBack();
                $inbox->refresh();
                Yii::error($e, __METHOD__);
                $model->addError('invoice_no', 'รับรองและตั้งเจ้าหนี้ไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
            }
        }
        return $this->renderForm($model, $inbox);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findPayable($id)]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findPayable($id);
        if (!in_array($model->status, [FinancePayable::STATUS_DRAFT, FinancePayable::STATUS_NEEDS_REVISION], true)) {
            Yii::$app->session->setFlash('warning', 'รายการสถานะนี้ไม่สามารถแก้ไขได้');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ($model->load(Yii::$app->request->post())) {
            $this->normalizeFormDates($model);
            try {
                (new FinancePayableDraftService())->update($model);
                Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขร่างทะเบียนเจ้าหนี้แล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (\DomainException $e) {
                $model->addError($this->domainErrorAttribute($e), $e->getMessage());
            } catch (\Throwable $e) {
                Yii::error($e, __METHOD__);
                $model->addError('invoice_no', 'บันทึกการแก้ไขไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
            }
        }
        return $this->renderForm($model, $model->inbox);
    }

    public function actionSubmit($id)
    {
        $model = $this->findPayable($id);
        try {
            (new FinancePayableApprovalService())->decide($model, FinancePayableReview::DECISION_SUBMIT);
            Yii::$app->session->setFlash('success', 'ส่งรายการให้ผู้ตรวจอนุมัติแล้ว');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('warning', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'ส่งรายการตรวจอนุมัติไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionReview($id)
    {
        $model = $this->findPayable($id);
        $decision = (string) Yii::$app->request->post('decision');
        $requiredPermission = $decision === FinancePayableReview::DECISION_APPROVE ? 'financeApprove' : 'financeOperate';
        if (!Yii::$app->user->can($requiredPermission)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการตัดสินใจนี้');
        }
        try {
            (new FinancePayableApprovalService())->decide($model, $decision, (string) Yii::$app->request->post('note'));
            Yii::$app->session->setFlash('success', $decision === FinancePayableReview::DECISION_APPROVE
                ? 'อนุมัติรายการเข้าสู่ทะเบียนเจ้าหนี้แล้ว' : 'ส่งรายการกลับให้ผู้จัดทำแก้ไขแล้ว');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('warning', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกผลการตรวจอนุมัติไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    private function renderForm(FinancePayable $model, FinanceInbox $inbox)
    {
        $vendors = Vendor::find()->where(['name' => 'vendor', 'active' => 1])->orderBy(['title' => SORT_ASC])->all();
        $service = new FinancePayableDraftService();
        $invoiceDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $model->invoice_date) ? $model->invoice_date : date('Y-m-d');
        try {
            $chartVersion = $service->activeHospitalChart($invoiceDate);
            $accounts = $service->eligibleAccounts($invoiceDate);
        } catch (\DomainException $e) {
            $chartVersion = null;
            $accounts = [];
        }
        return $this->render('create', [
            'model' => $model,
            'inbox' => $inbox,
            'vendors' => ArrayHelper::map($vendors, 'id', static fn(Vendor $vendor) => $vendor->title . ' (' . $vendor->code . ')'),
            'chartVersion' => $chartVersion,
            'accountOptions' => ArrayHelper::map(
                $accounts,
                'id',
                static fn(AccountingChartAccount $account) => $account->code . ' — ' . $account->name,
                static fn(AccountingChartAccount $account) => $account->category === '1' ? 'สินทรัพย์/สินค้าคงคลัง' : 'ค่าใช้จ่าย'
            ),
        ]);
    }

    /** ช่องวันที่ในฟอร์มเป็น พ.ศ. (วว/ดด/พ.ศ.) → ค.ศ. Y-m-d ก่อนคำนวณ/validate */
    private function normalizeFormDates(FinancePayable $model): void
    {
        foreach (['invoice_date', 'billing_date'] as $attr) {
            $model->$attr = AppHelper::normalizeDateToDb($model->$attr);
        }
    }

    private function findPayable($id): FinancePayable
    {
        $model = FinancePayable::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบทะเบียนเจ้าหนี้');
        }
        return $model;
    }

    private function domainErrorAttribute(\DomainException $exception): string
    {
        $message = $exception->getMessage();
        return str_contains($message, 'บัญชี') || str_contains($message, 'ผัง')
            ? 'accounting_chart_account_id'
            : 'invoice_no';
    }

    private function findInbox($id): FinanceInbox
    {
        $model = FinanceInbox::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการในกล่องรับบัญชี');
        }
        return $model;
    }
}
