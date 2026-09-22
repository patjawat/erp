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
use app\modules\accounting\models\AccountingChartAccount;
use app\modules\sm\models\Vendor;

class PayableController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view', 'aging', 'letter'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['create', 'update', 'submit', 'send-accounting', 'send-accounting-bulk'], 'roles' => ['financeOperate']],
                ['allow' => true, 'actions' => ['review'], 'roles' => ['financeApprove', 'financeOperate']],
                ['allow' => true, 'actions' => ['pay'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'create' => ['GET', 'POST'], 'update' => ['GET', 'POST'], 'submit' => ['POST'], 'review' => ['POST'],
                'pay' => ['GET', 'POST'], 'send-accounting' => ['POST'], 'send-accounting-bulk' => ['POST'],
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
        ]);
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
            SELECT p.id, p.payable_no, p.invoice_no, p.due_date, p.gross_amount,
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

    /** บันทึกรอบจ่าย: สร้าง batch + settlement ผูก payment_id */
    private function processPayment($req)
    {
        $vendor = trim((string) $req->post('vendor', ''));
        $settleDate = AppHelper::normalizeDateToDb((string) $req->post('pay_date')) ?: date('Y-m-d');
        $lines = (array) $req->post('pay', []);

        $selected = [];
        $gross = $wht = $net = 0.0;
        foreach ($lines as $pid => $amt) {
            $pid = (int) $pid;
            $amt = (float) str_replace([',', ' '], '', (string) $amt);
            if ($amt <= 0) {
                continue;
            }
            $p = FinancePayable::findOne(['id' => $pid, 'status' => FinancePayable::STATUS_APPROVED]);
            if (!$p) {
                continue;
            }
            $out = $p->getOutstanding();
            if ($amt > $out + 0.005) {
                $amt = $out;
            }
            if ($amt <= 0) {
                continue;
            }
            $selected[] = ['p' => $p, 'amt' => $amt];
            $gross += (float) $p->gross_amount;
            $wht += (float) $p->withholding_tax_amount;
            $net += $amt;
        }
        if (!$selected) {
            Yii::$app->session->setFlash('error', 'ยังไม่ได้เลือกบิลที่จะจ่าย');
            return $this->redirect(['pay', 'vendor' => $vendor]);
        }

        // บัญชีจ่าย: ถ้าเลือกจากทะเบียน ให้ดึงธนาคาร/สาขามาเติมอัตโนมัติ
        $accountId = (int) $req->post('cash_account_id', 0) ?: null;
        $bankName = trim((string) $req->post('bank_name', '')) ?: null;
        $bankBranch = trim((string) $req->post('bank_branch', '')) ?: null;
        if ($accountId) {
            $acc = FinanceCashAccount::findOne($accountId);
            if ($acc) {
                $bankName = $acc->bank_name ?: $bankName;
                $bankBranch = $acc->branch ?: $bankBranch;
            }
        }
        $payMethod = (string) $req->post('pay_method', 'cheque');
        $chequeNo = trim((string) $req->post('cheque_no', '')) ?: null;

        $tx = Yii::$app->db->beginTransaction();
        try {
            $pay = new FinancePayablePayment([
                'vendor_name_snapshot' => $vendor ?: $selected[0]['p']->vendor_name_snapshot,
                'vendor_id' => (int) $selected[0]['p']->vendor_id,
                'cash_account_id' => $accountId,
                'pay_date' => $settleDate,
                'pay_method' => $payMethod,
                'bank_name' => $bankName,
                'bank_branch' => $bankBranch,
                'cheque_no' => $chequeNo,
                'doc_no' => trim((string) $req->post('doc_no', '')) ?: null,
                'subject' => trim((string) $req->post('subject', '')) ?: null,
                'gross_total' => $gross,
                'wht_total' => $wht,
                'net_total' => $net,
                'note' => trim((string) $req->post('note', '')) ?: null,
            ]);
            $pay->save(false);
            foreach ($selected as $s) {
                (new FinancePayableSettlement([
                    'payable_id' => $s['p']->id,
                    'payment_id' => $pay->id,
                    'amount' => $s['amt'],
                    'settle_date' => $settleDate,
                    'note' => $pay->cheque_no ? ('เช็ค ' . $pay->cheque_no) : null,
                ]))->save(false);
            }

            // จ่ายด้วยเช็ค → บันทึกเช็คเข้าทะเบียนคุมเช็คอัตโนมัติ
            if ($payMethod === 'cheque' && $chequeNo) {
                $cheque = FinanceCheque::fromPayment($pay);
                $cheque->template_id = (int) $req->post('template_id', 0) ?: null;
                $cheque->cheque_book_no = trim((string) $req->post('cheque_book_no', '')) ?: null;
                $cheque->is_ac_payee = $req->post('is_ac_payee') ? 1 : 0;
                $cheque->status = FinanceCheque::STATUS_DRAFT;
                $cheque->save(false);
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกจ่ายชำระ ' . count($selected) . ' บิล รวม ' . number_format($net, 2) . ' บาท');
            return $this->redirect(['letter', 'id' => $pay->id]);
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกจ่ายชำระไม่สำเร็จ');
            return $this->redirect(['pay', 'vendor' => $vendor]);
        }
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
