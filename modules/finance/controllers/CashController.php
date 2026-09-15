<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinanceCashAccount;
use app\modules\finance\models\FinanceCashCategory;
use app\modules\finance\models\FinanceCashClose;
use app\modules\finance\models\FinanceCashPlan;
use app\modules\finance\models\FinanceCashTxn;
use app\modules\finance\models\FinanceCashYearClose;
use app\modules\finance\models\FinanceCashYearCloseItem;
use app\modules\finance\models\FinanceReceiptBook;
use app\modules\finance\models\FinanceCashVoucher;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ระบบรับ-จ่ายเงินบำรุง (พอร์ต mophcash) — เฟส 1
 * ผังบัญชี 3 ระดับ + บันทึกรายการรับ/จ่าย + ทะเบียน (list)
 */
class CashController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['financeOperate']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['post'],
                    'delete' => ['post'],
                    'voucher-save' => ['post'],
                    'voucher-delete' => ['post'],
                    'seed-accounts' => ['post'],
                    'plan-save' => ['post'],
                    'close-yearly-save' => ['post'],
                    'close-do' => ['post'],
                    'category-save' => ['post'],
                    'category-delete' => ['post'],
                    'seed-chart' => ['post'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        return $this->redirect(['overview']);
    }

    // ---- ภาพรวมรายรับ-รายจ่าย (dashboard) --------------------------------

    public function actionOverview($year = null, $period = 'year')
    {
        $year = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        $today = date('Y-m-d');

        // KPI วันนี้
        $inToday = (float) FinanceCashTxn::find()->where(['txn_type' => 'IN', 'doc_date' => $today, 'voucher_id' => null])->sum('amount');
        $outToday = (float) FinanceCashVoucher::find()->where(['pay_date' => $today])->sum('net_amount');

        // donut ตามกลุ่ม (ตามช่วงที่เลือก)
        [$start, $end] = $this->periodRange($period, $year, $today);
        $donutIn = $this->groupSums(FinanceCashCategory::TYPE_IN, $start, $end);
        $donutOut = $this->groupSums(FinanceCashCategory::TYPE_OUT, $start, $end);

        // trend 10 วันล่าสุด
        $days = [];
        for ($i = 9; $i >= 0; $i--) {
            $days[] = date('Y-m-d', strtotime("-$i day"));
        }
        $inByDay = FinanceCashTxn::find()->select(['doc_date', 's' => 'SUM(amount)'])
            ->where(['txn_type' => 'IN'])->andWhere(['between', 'doc_date', $days[0], $days[9]])
            ->groupBy('doc_date')->indexBy('doc_date')->asArray()->all();
        $outByDay = FinanceCashTxn::find()->select(['doc_date', 's' => 'SUM(amount)'])
            ->where(['txn_type' => 'OUT'])->andWhere(['between', 'doc_date', $days[0], $days[9]])
            ->groupBy('doc_date')->indexBy('doc_date')->asArray()->all();
        $trend = ['labels' => [], 'in' => [], 'out' => []];
        foreach ($days as $d) {
            $trend['labels'][] = AppHelper::convertToThai($d);
            $trend['in'][] = round((float) ($inByDay[$d]['s'] ?? 0), 2);
            $trend['out'][] = round((float) ($outByDay[$d]['s'] ?? 0), 2);
        }

        // รายเดือนตลอดปีงบ (ต.ค. → ก.ย.)
        $monthly = $this->monthlyData($year);

        return $this->render('overview', [
            'year' => $year,
            'period' => $period,
            'inToday' => $inToday,
            'outToday' => $outToday,
            'donutIn' => $donutIn,
            'donutOut' => $donutOut,
            'trend' => $trend,
            'monthly' => $monthly,
        ]);
    }

    private function periodRange(string $period, int $year, string $today): array
    {
        if ($period === 'today') {
            return [$today, $today];
        }
        if ($period === 'month') {
            return [date('Y-m-01'), date('Y-m-t')];
        }
        $gy = $year - 543;
        return [sprintf('%04d-10-01', $gy - 1), sprintf('%04d-09-30', $gy)];
    }

    /** ผลรวมตามกลุ่มบนสุด [groupName => sum] ในช่วงวันที่ */
    private function groupSums(string $type, string $start, string $end): array
    {
        $rows = FinanceCashTxn::find()->select(['category_id', 's' => 'SUM(amount)'])
            ->where(['txn_type' => $type])->andWhere(['between', 'doc_date', $start, $end])
            ->groupBy('category_id')->asArray()->all();
        $map = $this->groupNameMap();
        $out = [];
        foreach ($rows as $r) {
            $g = $map[(int) $r['category_id']] ?? 'อื่น ๆ';
            $out[$g] = ($out[$g] ?? 0) + (float) $r['s'];
        }
        arsort($out);
        return $out;
    }

    /** map category_id => ชื่อกลุ่มบนสุด */
    private function groupNameMap(): array
    {
        $rows = FinanceCashCategory::find()->select(['id', 'parent_id', 'name'])->asArray()->all();
        $parent = [];
        $name = [];
        foreach ($rows as $r) {
            $parent[(int) $r['id']] = (int) ($r['parent_id'] ?? 0);
            $name[(int) $r['id']] = $r['name'];
        }
        $map = [];
        foreach ($parent as $id => $p) {
            $cur = $id;
            $guard = 0;
            while (($parent[$cur] ?? 0) && $guard++ < 5) {
                $cur = $parent[$cur];
            }
            $map[$id] = $name[$cur] ?? $name[$id];
        }
        return $map;
    }

    private function monthlyData(int $year): array
    {
        $rows = FinanceCashTxn::find()
            ->select(['txn_type', 'ym' => "DATE_FORMAT(doc_date,'%Y-%m')", 's' => 'SUM(amount)'])
            ->where(['fiscal_year' => $year])
            ->groupBy(['txn_type', 'ym'])->asArray()->all();
        $sum = [];
        foreach ($rows as $r) {
            $sum[$r['txn_type']][$r['ym']] = (float) $r['s'];
        }
        $gy = $year - 543;
        $labels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
        $yms = [];
        foreach ([10, 11, 12] as $m) {
            $yms[] = sprintf('%04d-%02d', $gy - 1, $m);
        }
        foreach (range(1, 9) as $m) {
            $yms[] = sprintf('%04d-%02d', $gy, $m);
        }
        $in = [];
        $out = [];
        foreach ($yms as $ym) {
            $in[] = round($sum['IN'][$ym] ?? 0, 2);
            $out[] = round($sum['OUT'][$ym] ?? 0, 2);
        }
        return ['labels' => $labels, 'in' => $in, 'out' => $out];
    }

    public function actionIncome()
    {
        return $this->renderList(FinanceCashCategory::TYPE_IN, 'income');
    }

    public function actionExpense()
    {
        $req = Yii::$app->request;
        $fiscalYear = (int) $req->get('fiscal_year', FinanceCashTxn::currentFiscalYear());
        $date = trim((string) $req->get('date', ''));
        $q = trim((string) $req->get('q', ''));

        $query = FinanceCashVoucher::find()->with('account')->where(['fiscal_year' => $fiscalYear]);
        if ($date !== '') {
            $db = AppHelper::normalizeDateToDb($date);
            if ($db) {
                $query->andWhere(['pay_date' => $db]);
            }
        }
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'doc_no', $q], ['like', 'cheque_no', $q], ['like', 'payee_name', $q], ['like', 'note', $q],
            ]);
        }
        $sum = (clone $query)->sum('net_amount');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['pay_date' => SORT_DESC, 'id' => SORT_DESC]],
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('expense_list', [
            'active' => 'expense',
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
            'date' => $date,
            'q' => $q,
            'sum' => (float) $sum,
            'tree' => FinanceCashCategory::treeArray(FinanceCashCategory::TYPE_OUT),
            'accounts' => FinanceCashAccount::activeList(),
            'vendors' => $this->vendorTitles(),
        ]);
    }

    /** ทะเบียนผู้ขาย/ห้างร้าน ใช้ร่วมกับระบบพัสดุ (categorise name='vendor') สำหรับ "จ่ายให้" */
    private function vendorTitles(): array
    {
        return (new \yii\db\Query())->select('title')->from('categorise')
            ->where(['name' => 'vendor', 'active' => 1])
            ->andWhere(['not', ['title' => null]])->andWhere(['<>', 'title', ''])
            ->orderBy('title')->column();
    }

    /** ทะเบียนรายการรับ/จ่าย พร้อมตัวกรอง ปีงบ/วันที่/ค้นหา + แบ่งหน้า */
    private function renderList(string $type, string $active)
    {
        $req = Yii::$app->request;
        $fiscalYear = (int) $req->get('fiscal_year', FinanceCashTxn::currentFiscalYear());
        $date = trim((string) $req->get('date', ''));
        $q = trim((string) $req->get('q', ''));

        $query = FinanceCashTxn::find()
            ->with('category')
            ->where(['txn_type' => $type, 'fiscal_year' => $fiscalYear]);

        if ($date !== '') {
            $dbDate = AppHelper::normalizeDateToDb($date);
            if ($dbDate) {
                $query->andWhere(['doc_date' => $dbDate]);
            }
        }
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'doc_no', $q],
                ['like', 'party_name', $q],
                ['like', 'note', $q],
            ]);
        }

        $sum = (clone $query)->sum('amount');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['doc_date' => SORT_DESC, 'id' => SORT_DESC]],
            'pagination' => ['pageSize' => 25],
        ]);

        return $this->render('list', [
            'type' => $type,
            'active' => $active,
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
            'date' => $date,
            'q' => $q,
            'sum' => (float) $sum,
            'tree' => FinanceCashCategory::treeArray($type),
            'receiptBooks' => FinanceReceiptBook::issuedToEmployee($this->currentEmpId()),
        ]);
    }

    /** บันทึก/แก้ไขรายการผ่าน popup (AJAX) — id ว่าง = สร้างใหม่ */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $id = (int) ($post['id'] ?? 0);
        $model = $id ? FinanceCashTxn::findOne($id) : new FinanceCashTxn();
        if (!$model) {
            return ['ok' => false, 'message' => 'ไม่พบรายการ'];
        }
        if ($id && $model->is_closed) {
            return ['ok' => false, 'message' => 'รายการนี้อยู่ในงวดที่ปิดบัญชีแล้ว แก้ไขไม่ได้'];
        }
        $model->load($post);
        // แปลงวันที่ไทย (วว/ดด/พ.ศ.) → ค.ศ. Y-m-d ก่อน validate
        $model->doc_date = AppHelper::normalizeDateToDb($post['FinanceCashTxn']['doc_date'] ?? null);
        if ($model->save()) {
            // เตือน (ไม่บล็อก) ถ้าเลขใบเสร็จไม่ตรงทะเบียนเล่ม/นอกช่วง/ซ้ำ/ไม่ใช่เล่มที่เบิก
            if ($model->txn_type === FinanceCashCategory::TYPE_IN && $model->doc_no) {
                $warn = $this->receiptWarnings($model->doc_no);
                if ($warn) {
                    Yii::$app->session->setFlash('warning', 'บันทึกแล้ว — ข้อควรระวังใบเสร็จ: ' . implode(' · ', $warn));
                }
            }
            return ['ok' => true, 'message' => 'บันทึกรายการเรียบร้อย'];
        }
        return ['ok' => false, 'errors' => $model->getErrors()];
    }

    /** ดึงข้อมูลรายการเดิมมาเติมใน popup ตอนแก้ไข */
    public function actionGet($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findTxn($id);
        $chain = ['group' => null, 'category' => null, 'account' => null];
        $node = $model->category;
        $guard = 0;
        while ($node !== null && $guard++ < 5) {
            $chain[$node->level] = $node->id;
            $node = $node->parent;
        }
        return [
            'id' => $model->id,
            'txn_type' => $model->txn_type,
            'fiscal_year' => $model->fiscal_year,
            'category_id' => $model->category_id,
            'chain' => $chain,
            'doc_date' => $model->doc_date ? AppHelper::convertToThai($model->doc_date) : '',
            'doc_no' => $model->doc_no,
            'pay_method' => $model->pay_method,
            'amount' => $model->amount !== null ? (float) $model->amount : '',
            'party_name' => $model->party_name,
            'note' => $model->note,
        ];
    }

    // ---- ใบสำคัญจ่าย (voucher: header + หลายบรรทัด) --------------------------

    /** บันทึก/แก้ไขใบสำคัญจ่าย — header + บรรทัด (finance_cash_txn ฝั่ง OUT) ใน 1 transaction */
    public function actionVoucherSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $id = (int) ($post['id'] ?? 0);
        $v = $id ? FinanceCashVoucher::findOne($id) : new FinanceCashVoucher();
        if (!$v) {
            return ['ok' => false, 'message' => 'ไม่พบใบสำคัญ'];
        }
        if ($id && $v->is_closed) {
            return ['ok' => false, 'message' => 'ใบสำคัญนี้อยู่ในงวดที่ปิดบัญชีแล้ว แก้ไขไม่ได้'];
        }

        $v->fiscal_year = (int) ($post['fiscal_year'] ?? 0);
        $v->pay_date = AppHelper::normalizeDateToDb($post['pay_date'] ?? null);
        $v->doc_no = trim((string) ($post['doc_no'] ?? '')) ?: null;
        $v->pay_method = $post['pay_method'] ?? null;
        $v->cheque_no = trim((string) ($post['cheque_no'] ?? '')) ?: null;
        $v->account_id = ((int) ($post['account_id'] ?? 0)) ?: null;
        $v->payee_name = trim((string) ($post['payee_name'] ?? '')) ?: null;
        $v->note = trim((string) ($post['note'] ?? '')) ?: null;
        // ผูก "จ่ายให้" เข้าทะเบียนผู้ขายพัสดุ (categorise vendor) ถ้าชื่อตรง — เก็บ id อ้างอิง + ชื่อ snapshot
        $v->payee_id = $v->payee_name
            ? ((int) (new \yii\db\Query())->select('id')->from('categorise')
                ->where(['name' => 'vendor', 'title' => $v->payee_name])->scalar() ?: 0) ?: null
            : null;

        // บรรทัด
        $clean = [];
        $subtotal = 0.0;
        foreach ((array) ($post['lines'] ?? []) as $ln) {
            $cid = (int) ($ln['category_id'] ?? 0);
            $amt = (float) str_replace([',', ' '], '', (string) ($ln['amount'] ?? 0));
            if ($cid && $amt > 0) {
                $clean[] = ['category_id' => $cid, 'bc_ref' => trim((string) ($ln['bc_ref'] ?? '')) ?: null, 'amount' => $amt];
                $subtotal += $amt;
            }
        }
        $vat = (float) str_replace([',', ' '], '', (string) ($post['vat_amount'] ?? 0));
        $v->applyTotals($subtotal, $vat, ($post['wht_type'] ?? '') ?: null);

        $errors = [];
        if (!$v->pay_method) {
            $errors['pay_method'] = ['เลือกวิธีจ่าย'];
        }
        if (!$v->account_id) {
            $errors['account_id'] = ['เลือกบัญชีที่จ่าย'];
        }
        if (empty($clean)) {
            $errors['lines'] = ['เพิ่มรายการจ่ายที่มีหมวดและจำนวนเงินอย่างน้อย 1 บรรทัด'];
        }
        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }
        if (!$v->validate()) {
            return ['ok' => false, 'errors' => $v->getErrors()];
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $v->save(false);
            FinanceCashTxn::deleteAll(['voucher_id' => $v->id]);
            foreach ($clean as $ln) {
                (new FinanceCashTxn([
                    'txn_type' => FinanceCashCategory::TYPE_OUT,
                    'fiscal_year' => $v->fiscal_year,
                    'category_id' => $ln['category_id'],
                    'voucher_id' => $v->id,
                    'money_account_id' => $v->account_id,
                    'doc_date' => $v->pay_date,
                    'doc_no' => $v->cheque_no ?: $v->doc_no,
                    'bc_ref' => $ln['bc_ref'],
                    'pay_method' => $v->pay_method,
                    'amount' => $ln['amount'],
                    'party_name' => $v->payee_name,
                    'note' => $v->note,
                    'is_closed' => $v->is_closed,
                ]))->save(false);
            }
            $tx->commit();
            return ['ok' => true, 'message' => 'บันทึกใบสำคัญจ่ายเรียบร้อย'];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['ok' => false, 'message' => 'บันทึกไม่สำเร็จ: ' . $e->getMessage()];
        }
    }

    /** ดึงใบสำคัญ + บรรทัด มาเติม popup ตอนแก้ไข */
    public function actionVoucherGet($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $v = FinanceCashVoucher::findOne((int) $id);
        if (!$v) {
            throw new NotFoundHttpException('ไม่พบใบสำคัญ');
        }
        $lines = [];
        foreach ($v->items as $it) {
            $lines[] = [
                'category_id' => $it->category_id,
                'chain' => $this->categoryChain($it->category),
                'name' => $it->category ? $it->category->name : '',
                'bc_ref' => $it->bc_ref,
                'amount' => (float) $it->amount,
            ];
        }
        return [
            'id' => $v->id,
            'fiscal_year' => $v->fiscal_year,
            'pay_date' => $v->pay_date ? AppHelper::convertToThai($v->pay_date) : '',
            'doc_no' => $v->doc_no,
            'pay_method' => $v->pay_method,
            'cheque_no' => $v->cheque_no,
            'account_id' => $v->account_id,
            'payee_name' => $v->payee_name,
            'note' => $v->note,
            'vat_amount' => (float) $v->vat_amount,
            'wht_type' => $v->wht_type,
            'lines' => $lines,
        ];
    }

    public function actionVoucherDelete($id)
    {
        $v = FinanceCashVoucher::findOne((int) $id);
        if (!$v) {
            throw new NotFoundHttpException('ไม่พบใบสำคัญ');
        }
        if ($v->is_closed) {
            Yii::$app->session->setFlash('error', 'ใบสำคัญนี้อยู่ในงวดที่ปิดบัญชีแล้ว ลบไม่ได้');
        } else {
            $year = $v->fiscal_year;
            $v->delete(); // FK CASCADE ลบบรรทัดใน finance_cash_txn ให้เอง
            Yii::$app->session->setFlash('success', 'ลบใบสำคัญจ่ายเรียบร้อย');
            return $this->redirect(['expense', 'fiscal_year' => $year]);
        }
        return $this->redirect(['expense']);
    }

    /** สายบรรพบุรุษของหมวด [group, category, account] สำหรับ preselect cascade */
    private function categoryChain(?FinanceCashCategory $cat): array
    {
        $chain = ['group' => null, 'category' => null, 'account' => null];
        $node = $cat;
        $guard = 0;
        while ($node !== null && $guard++ < 5) {
            $chain[$node->level] = $node->id;
            $node = $node->parent;
        }
        return $chain;
    }

    /** ป้อนบัญชีเงินเริ่มต้น (ตามหน้าเงินคงเหลือ mophcash) — idempotent */
    public function actionSeedAccounts()
    {
        if (FinanceCashAccount::find()->exists()) {
            Yii::$app->session->setFlash('warning', 'มีบัญชีเงินอยู่แล้ว ข้ามการ seed');
            return $this->redirect(['expense']);
        }
        $seed = [
            ['code' => null, 'name' => 'เงินสด', 'type' => 'cash'],
            ['code' => null, 'name' => 'เงินฝากคลังสำนักงานจังหวัดเลย', 'type' => 'treasury'],
            ['code' => '433-100-7049', 'name' => 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย', 'type' => 'bank'],
            ['code' => '433-020-0457', 'name' => 'อุดหนุนผู้มีปัญหาสถานะและสิทธิ', 'type' => 'bank'],
            ['code' => '433-030-7979', 'name' => 'แพทย์แผนไทย', 'type' => 'bank'],
            ['code' => '433-040-8708', 'name' => 'เงินบริจาคโรงพยาบาลสมเด็จพระยุพราชด่านซ้าย', 'type' => 'bank'],
        ];
        $i = 0;
        foreach ($seed as $s) {
            (new FinanceCashAccount(['code' => $s['code'], 'name' => $s['name'], 'account_type' => $s['type'], 'sort_order' => $i++]))->save(false);
        }
        Yii::$app->session->setFlash('success', 'ป้อนบัญชีเงินเริ่มต้นแล้ว ' . count($seed) . ' บัญชี');
        return $this->redirect(['expense']);
    }

    // ---- แผนรายรับ-รายจ่ายประจำปี -----------------------------------------

    public function actionPlan($year = null)
    {
        $year = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        return $this->render('plan', ['year' => $year] + $this->planMatrix($year));
    }

    public function actionPlanSave()
    {
        $post = Yii::$app->request->post();
        $year = (int) ($post['year'] ?? FinanceCashTxn::currentFiscalYear());
        $typeByCat = [];
        foreach (FinanceCashCategory::find()->select(['id', 'txn_type'])->asArray()->all() as $c) {
            $typeByCat[(int) $c['id']] = $c['txn_type'];
        }
        $count = 0;
        foreach ((array) ($post['plan'] ?? []) as $catId => $years) {
            $catId = (int) $catId;
            if (!isset($typeByCat[$catId])) {
                continue;
            }
            foreach ((array) $years as $fy => $amt) {
                $fy = (int) $fy;
                $amt = (float) str_replace([',', ' '], '', (string) $amt);
                $row = FinanceCashPlan::findOne(['category_id' => $catId, 'fiscal_year' => $fy]);
                if (!$row && $amt <= 0) {
                    continue;
                }
                if (!$row) {
                    $row = new FinanceCashPlan(['category_id' => $catId, 'fiscal_year' => $fy]);
                }
                $row->txn_type = $typeByCat[$catId];
                $row->amount = $amt;
                if ($row->save()) {
                    $count++;
                }
            }
        }
        Yii::$app->session->setFlash('success', "บันทึกแผนแล้ว $count รายการ");
        return $this->redirect(['plan', 'year' => $year]);
    }

    public function actionPlanExcel($year = null)
    {
        $year = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        $m = $this->planMatrix($year);
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('แผนรายรับ-รายจ่าย');
        $s->setCellValue('A1', 'แผนรายรับ-รายจ่ายเงินบำรุง ปีงบประมาณ ' . $m['planYears'][0] . '-' . end($m['planYears']));
        $r = 3;
        $col = 'B';
        $s->setCellValue('A' . $r, 'รายการ');
        foreach ($m['actualYears'] as $ay) {
            $s->setCellValue($col . $r, 'ผล ' . $ay);
            $col++;
        }
        foreach ($m['planYears'] as $py) {
            $s->setCellValue($col . $r, 'แผน ' . $py);
            $col++;
        }
        $r++;
        foreach ([FinanceCashCategory::TYPE_IN => 'รายรับ', FinanceCashCategory::TYPE_OUT => 'รายจ่าย'] as $type => $label) {
            $s->setCellValue("A$r", $label);
            $r++;
            foreach ($m['types'][$type]['groups'] as $g) {
                $s->setCellValue("A$r", $g['name']);
                $r++;
                foreach ($g['rows'] as $row) {
                    $s->setCellValue("A$r", '   ' . $row['name']);
                    $col = 'B';
                    foreach ($m['actualYears'] as $ay) {
                        $s->setCellValue($col . $r, $row['actual'][$ay] ?? 0);
                        $col++;
                    }
                    foreach ($m['planYears'] as $py) {
                        $s->setCellValue($col . $r, $row['plan'][$py] ?? 0);
                        $col++;
                    }
                    $r++;
                }
            }
        }
        foreach (range('A', 'G') as $c) {
            $s->getColumnDimension($c)->setAutoSize(true);
        }
        return $this->streamBook($book, 'แผนรายรับจ่าย_' . $year . '.xlsx', 'plan');
    }

    /** matrix แผน: ย้อนหลัง 3 ปี (actual จาก txn) + แผนล่วงหน้า 3 ปี (finance_cash_plan) ต่อหมวด */
    private function planMatrix(int $year): array
    {
        $actualYears = [$year - 3, $year - 2, $year - 1];
        $planYears = [$year, $year + 1, $year + 2];

        $actual = [];
        foreach (FinanceCashTxn::find()->select(['fiscal_year', 'category_id', 's' => 'SUM(amount)'])
            ->where(['fiscal_year' => $actualYears])->groupBy(['fiscal_year', 'category_id'])->asArray()->all() as $r) {
            $actual[(int) $r['category_id']][(int) $r['fiscal_year']] = (float) $r['s'];
        }
        $plan = [];
        foreach (FinanceCashPlan::find()->where(['fiscal_year' => $planYears])->asArray()->all() as $r) {
            $plan[(int) $r['category_id']][(int) $r['fiscal_year']] = (float) $r['amount'];
        }

        $types = [];
        foreach ([FinanceCashCategory::TYPE_IN, FinanceCashCategory::TYPE_OUT] as $type) {
            $tree = FinanceCashCategory::treeArray($type);
            $childrenOf = [];
            foreach ($tree as $n) {
                $childrenOf[(int) ($n['parent_id'] ?? 0)][] = $n;
            }
            $leaves = function ($nodeId) use (&$leaves, $childrenOf) {
                $kids = $childrenOf[(int) $nodeId] ?? [];
                if (!$kids) {
                    return [];
                }
                $out = [];
                foreach ($kids as $k) {
                    $sub = $leaves($k['id']);
                    $out = $sub ? array_merge($out, $sub) : array_merge($out, [$k]);
                }
                return $out;
            };
            $groups = [];
            $totA = array_fill_keys($actualYears, 0.0);
            $totP = array_fill_keys($planYears, 0.0);
            foreach ($childrenOf[0] ?? [] as $group) {
                $rows = [];
                $subA = array_fill_keys($actualYears, 0.0);
                $subP = array_fill_keys($planYears, 0.0);
                foreach ($leaves($group['id']) as $leaf) {
                    $cid = (int) $leaf['id'];
                    $a = [];
                    foreach ($actualYears as $ay) {
                        $a[$ay] = $actual[$cid][$ay] ?? 0.0;
                        $subA[$ay] += $a[$ay];
                    }
                    $p = [];
                    foreach ($planYears as $py) {
                        $p[$py] = $plan[$cid][$py] ?? 0.0;
                        $subP[$py] += $p[$py];
                    }
                    $rows[] = ['id' => $cid, 'name' => $leaf['name'], 'actual' => $a, 'plan' => $p];
                }
                foreach ($actualYears as $ay) {
                    $totA[$ay] += $subA[$ay];
                }
                foreach ($planYears as $py) {
                    $totP[$py] += $subP[$py];
                }
                $groups[] = ['name' => $group['name'], 'rows' => $rows, 'subA' => $subA, 'subP' => $subP];
            }
            $types[$type] = ['groups' => $groups, 'totA' => $totA, 'totP' => $totP];
        }

        // ผลต่างสุทธิ รับ - จ่าย ต่อปี
        $netA = [];
        foreach ($actualYears as $ay) {
            $netA[$ay] = ($types[FinanceCashCategory::TYPE_IN]['totA'][$ay] ?? 0) - ($types[FinanceCashCategory::TYPE_OUT]['totA'][$ay] ?? 0);
        }
        $netP = [];
        foreach ($planYears as $py) {
            $netP[$py] = ($types[FinanceCashCategory::TYPE_IN]['totP'][$py] ?? 0) - ($types[FinanceCashCategory::TYPE_OUT]['totP'][$py] ?? 0);
        }

        return ['actualYears' => $actualYears, 'planYears' => $planYears, 'types' => $types, 'netA' => $netA, 'netP' => $netP];
    }

    // ---- ปิดบัญชี ---------------------------------------------------------

    public function actionCloseDaily($date = null)
    {
        $dbDate = ($date ? AppHelper::normalizeDateToDb($date) : null) ?: date('Y-m-d');

        $inRows = FinanceCashTxn::find()->with('category')
            ->where(['txn_type' => FinanceCashCategory::TYPE_IN, 'doc_date' => $dbDate, 'is_closed' => 0, 'voucher_id' => null])
            ->orderBy(['id' => SORT_ASC])->all();
        $outRows = FinanceCashVoucher::find()->with('account')
            ->where(['pay_date' => $dbDate, 'is_closed' => 0])->orderBy(['id' => SORT_ASC])->all();
        $closedBatches = FinanceCashClose::find()
            ->where(['close_type' => FinanceCashClose::TYPE_DAILY, 'close_date' => $dbDate])
            ->orderBy(['id' => SORT_DESC])->all();

        return $this->render('close_daily', [
            'dbDate' => $dbDate,
            'thaiDate' => AppHelper::convertToThai($dbDate),
            'inRows' => $inRows,
            'outRows' => $outRows,
            'summary' => $this->dailySummary($dbDate),
            'closedBatches' => $closedBatches,
        ]);
    }

    public function actionCloseDo()
    {
        $post = Yii::$app->request->post();
        $dbDate = AppHelper::normalizeDateToDb($post['date'] ?? null) ?: date('Y-m-d');
        $txnIds = array_filter(array_map('intval', (array) ($post['txn_ids'] ?? [])));
        $voucherIds = array_filter(array_map('intval', (array) ($post['voucher_ids'] ?? [])));
        $redirect = $this->redirect(['close-daily', 'date' => AppHelper::convertToThai($dbDate)]);

        if (!$txnIds && !$voucherIds) {
            Yii::$app->session->setFlash('warning', 'ยังไม่ได้เลือกรายการที่จะปิด');
            return $redirect;
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $batch = new FinanceCashClose([
                'close_type' => FinanceCashClose::TYPE_DAILY,
                'close_date' => $dbDate,
                'fiscal_year' => FinanceCashTxn::currentFiscalYear(),
            ]);
            $batch->save(false);

            $totalIn = 0.0;
            $inCount = 0;
            foreach (FinanceCashTxn::find()->where(['id' => $txnIds, 'txn_type' => 'IN', 'is_closed' => 0])->all() as $r) {
                $r->is_closed = 1;
                $r->close_batch_id = $batch->id;
                $r->save(false);
                $totalIn += (float) $r->amount;
                $inCount++;
            }
            $totalOut = 0.0;
            $outCount = 0;
            foreach (FinanceCashVoucher::find()->where(['id' => $voucherIds, 'is_closed' => 0])->all() as $v) {
                $v->is_closed = 1;
                $v->close_batch_id = $batch->id;
                $v->save(false);
                FinanceCashTxn::updateAll(['is_closed' => 1, 'close_batch_id' => $batch->id], ['voucher_id' => $v->id]);
                $totalOut += (float) $v->net_amount;
                $outCount++;
            }
            $batch->total_in = $totalIn;
            $batch->in_count = $inCount;
            $batch->total_out = $totalOut;
            $batch->out_count = $outCount;
            $batch->save(false);

            $tx->commit();
            Yii::$app->session->setFlash('success', "ปิดบัญชีวันที่ " . AppHelper::convertToThai($dbDate) . " เรียบร้อย (รับ $inCount / จ่าย $outCount รายการ)");
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('error', 'ปิดบัญชีไม่สำเร็จ: ' . $e->getMessage());
        }
        return $redirect;
    }

    public function actionCloseSummary($year = null)
    {
        $fy = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        $batches = FinanceCashClose::find()
            ->where(['close_type' => FinanceCashClose::TYPE_DAILY, 'fiscal_year' => $fy])
            ->orderBy(['close_date' => SORT_DESC, 'id' => SORT_DESC])->all();
        return $this->render('close_summary', ['fy' => $fy, 'batches' => $batches]);
    }

    public function actionCloseYearly($year = null, $sync = 0)
    {
        $fy = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        return $this->render('close_yearly', ['fy' => $fy] + $this->yearCloseWorksheet($fy, (bool) $sync));
    }

    public function actionCloseYearlySave()
    {
        $post = Yii::$app->request->post();
        $fy = (int) ($post['fiscal_year'] ?? FinanceCashTxn::currentFiscalYear());

        $head = FinanceCashYearClose::findOne(['fiscal_year' => $fy]) ?: new FinanceCashYearClose(['fiscal_year' => $fy]);
        foreach (['carried_forward', 'fund_pending', 'obligation', 'purchase_obligation',
            'cash_amount', 'treasury_amount', 'bank_fixed', 'bank_savings', 'bank_current'] as $f) {
            $head->$f = $post[$f] ?? 0;
        }
        $head->save();

        foreach ((array) ($post['item'] ?? []) as $catId => $amt) {
            $catId = (int) $catId;
            $amt = (float) str_replace([',', ' '], '', (string) $amt);
            $row = FinanceCashYearCloseItem::findOne(['fiscal_year' => $fy, 'category_id' => $catId]);
            if (!$row && $amt == 0.0) {
                continue;
            }
            if (!$row) {
                $row = new FinanceCashYearCloseItem(['fiscal_year' => $fy, 'category_id' => $catId]);
            }
            $row->amount = $amt;
            $row->save();
        }
        Yii::$app->session->setFlash('success', 'บันทึกปิดบัญชีประจำปีเรียบร้อย');
        return $this->redirect(['close-yearly', 'year' => $fy]);
    }

    /** worksheet ปิดบัญชีประจำปี — ยอดหมวดแก้ได้ (ซิงค์จาก txn) + reconciliation + composition */
    private function yearCloseWorksheet(int $fy, bool $sync): array
    {
        // ยอดจริงต่อหมวดจาก txn (ใช้ตอนซิงค์ หรือเมื่อยังไม่เคยบันทึก)
        $actual = [];
        foreach (FinanceCashTxn::find()->select(['category_id', 's' => 'SUM(amount)'])
            ->where(['fiscal_year' => $fy])->groupBy('category_id')->asArray()->all() as $r) {
            $actual[(int) $r['category_id']] = (float) $r['s'];
        }
        $savedItems = [];
        foreach (FinanceCashYearCloseItem::find()->where(['fiscal_year' => $fy])->asArray()->all() as $r) {
            $savedItems[(int) $r['category_id']] = (float) $r['amount'];
        }
        $head = FinanceCashYearClose::findOne(['fiscal_year' => $fy]);
        $hasSaved = $head !== null;

        $val = function ($catId) use ($sync, $savedItems, $actual) {
            if (!$sync && isset($savedItems[$catId])) {
                return $savedItems[$catId];
            }
            return $actual[$catId] ?? 0.0;
        };

        $types = [];
        $totIn = 0.0;
        $totOut = 0.0;
        foreach ([FinanceCashCategory::TYPE_IN, FinanceCashCategory::TYPE_OUT] as $type) {
            $tree = FinanceCashCategory::treeArray($type);
            $childrenOf = [];
            foreach ($tree as $n) {
                $childrenOf[(int) ($n['parent_id'] ?? 0)][] = $n;
            }
            $leaves = function ($id) use (&$leaves, $childrenOf) {
                $kids = $childrenOf[(int) $id] ?? [];
                if (!$kids) {
                    return [];
                }
                $out = [];
                foreach ($kids as $k) {
                    $sub = $leaves($k['id']);
                    $out = $sub ? array_merge($out, $sub) : array_merge($out, [$k]);
                }
                return $out;
            };
            $groups = [];
            foreach ($childrenOf[0] ?? [] as $group) {
                $rows = [];
                $sub = 0.0;
                foreach ($leaves($group['id']) as $leaf) {
                    $a = $val((int) $leaf['id']);
                    $sub += $a;
                    $rows[] = ['id' => (int) $leaf['id'], 'name' => $leaf['name'], 'amount' => $a];
                }
                $groups[] = ['name' => $group['name'], 'rows' => $rows, 'subtotal' => $sub];
                if ($type === FinanceCashCategory::TYPE_IN) {
                    $totIn += $sub;
                } else {
                    $totOut += $sub;
                }
            }
            $types[$type] = $groups;
        }

        // หัว (reconciliation + composition)
        $fields = ['carried_forward', 'fund_pending', 'obligation', 'purchase_obligation',
            'cash_amount', 'treasury_amount', 'bank_fixed', 'bank_savings', 'bank_current'];
        $H = [];
        foreach ($fields as $f) {
            $H[$f] = $hasSaved && !$sync ? (float) $head->$f : 0.0;
        }
        // ตอนซิงค์ (หรือยังไม่เคยบันทึก) → เติม composition จากยอดคงเหลือบัญชีของปีงบ
        if ($sync || !$hasSaved) {
            $H['cash_amount'] = $this->accTypeBalance($fy, FinanceCashAccount::TYPE_CASH);
            $H['treasury_amount'] = $this->accTypeBalance($fy, FinanceCashAccount::TYPE_TREASURY);
            $H['bank_fixed'] = $this->accTypeBalance($fy, FinanceCashAccount::TYPE_BANK, 'ฝากประจำ');
            $H['bank_savings'] = $this->accTypeBalance($fy, FinanceCashAccount::TYPE_BANK, 'ออมทรัพย์');
            $H['bank_current'] = $this->accTypeBalance($fy, FinanceCashAccount::TYPE_BANK, 'กระแสรายวัน');
        }

        $net = $totIn - $totOut;
        $balance1 = $net + $H['carried_forward'];
        $balanceAfter = $balance1 - $H['fund_pending'] - $H['obligation'] - $H['purchase_obligation'];
        $comp2 = $H['cash_amount'] + $H['treasury_amount'] + $H['bank_fixed'] + $H['bank_savings'] + $H['bank_current'];

        return [
            'types' => $types, 'totIn' => $totIn, 'totOut' => $totOut, 'net' => $net,
            'H' => $H, 'balance1' => $balance1, 'balanceAfter' => $balanceAfter, 'comp2' => $comp2,
            'hasSaved' => $hasSaved,
        ];
    }

    private function accTypeBalance(int $fy, string $type, ?string $deposit = null): float
    {
        $q = FinanceCashAccount::find()->where(['account_type' => $type]);
        if ($deposit !== null) {
            $q->andWhere(['deposit_type' => $deposit]);
        }
        $sum = 0.0;
        foreach ($q->all() as $a) {
            $sum += $a->balanceFor($fy);
        }
        return $sum;
    }

    /** สรุปประจำวันแยกตามวิธีรับ/จ่าย (รับจาก txn เดี่ยว, จ่ายจาก voucher) */
    private function dailySummary(string $dbDate): array
    {
        $in = FinanceCashTxn::find()
            ->select(['pay_method', 'c' => 'COUNT(*)', 's' => 'SUM(amount)'])
            ->where(['txn_type' => 'IN', 'doc_date' => $dbDate, 'voucher_id' => null])
            ->groupBy('pay_method')->asArray()->all();
        $out = FinanceCashVoucher::find()
            ->select(['pay_method', 'c' => 'COUNT(*)', 's' => 'SUM(net_amount)'])
            ->where(['pay_date' => $dbDate])
            ->groupBy('pay_method')->asArray()->all();
        return ['in' => $in, 'out' => $out];
    }

    /** รายงานประจำปี: ผลรวมต่อหมวด จัดกลุ่มตามผังบัญชี ทั้งรับ-จ่าย */
    private function yearlyReport(int $fy): array
    {
        $sums = FinanceCashTxn::find()
            ->select(['category_id', 's' => 'SUM(amount)'])
            ->where(['fiscal_year' => $fy])
            ->groupBy('category_id')->asArray()->all();
        $byCat = [];
        foreach ($sums as $r) {
            $byCat[(int) $r['category_id']] = (float) $r['s'];
        }

        $result = [];
        foreach ([FinanceCashCategory::TYPE_IN, FinanceCashCategory::TYPE_OUT] as $type) {
            $tree = FinanceCashCategory::treeArray($type);
            $childrenOf = [];
            foreach ($tree as $n) {
                $childrenOf[(int) ($n['parent_id'] ?? 0)][] = $n;
            }
            $leafSum = function ($nodeId) use (&$leafSum, $childrenOf, $byCat) {
                $kids = $childrenOf[(int) $nodeId] ?? [];
                if (!$kids) {
                    return $byCat[(int) $nodeId] ?? 0.0;
                }
                $s = 0.0;
                foreach ($kids as $k) {
                    $s += $leafSum($k['id']);
                }
                return $s;
            };
            $groups = [];
            $grand = 0.0;
            foreach ($childrenOf[0] ?? [] as $group) {
                $rows = [];
                foreach ($childrenOf[(int) $group['id']] ?? [] as $cat) {
                    $amt = $leafSum($cat['id']);
                    $rows[] = ['name' => $cat['name'], 'amount' => $amt];
                }
                $gt = $leafSum($group['id']);
                $grand += $gt;
                $groups[] = ['name' => $group['name'], 'rows' => $rows, 'total' => $gt];
            }
            $result[$type] = ['groups' => $groups, 'total' => $grand];
        }
        return $result;
    }

    /** ส่งออก Excel — report = register | balance407 | daily | summary | yearly */
    public function actionCloseExcel($report = 'yearly', $date = null, $year = null)
    {
        $fy = (int) ($year ?: FinanceCashTxn::currentFiscalYear());

        // รายงานตามต้นฉบับ mophcash (ทำใน service แยก)
        if ($report === 'register' || $report === 'balance407') {
            $dbDate = ($date ? AppHelper::normalizeDateToDb($date) : null) ?: date('Y-m-d');
            if ($report === 'register') {
                $book = \app\modules\finance\services\CashReportService::registerSpreadsheet($dbDate);
                $fileName = 'ทะเบียนปิดบัญชี_' . AppHelper::convertToThai($dbDate) . '.xlsx';
            } else {
                $book = \app\modules\finance\services\CashReportService::balance407Spreadsheet($fy, $dbDate);
                $fileName = 'เงินคงเหลือประจำวัน407_' . AppHelper::convertToThai($dbDate) . '.xlsx';
            }
            return $this->streamBook($book, $fileName, $report);
        }

        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $book->getActiveSheet();

        if ($report === 'daily') {
            $dbDate = ($date ? AppHelper::normalizeDateToDb($date) : null) ?: date('Y-m-d');
            $sheet->setTitle('ปิดบัญชีประจำวัน');
            $sheet->setCellValue('A1', 'ปิดบัญชีประจำวัน ' . AppHelper::convertToThai($dbDate));
            $r = 3;
            $sheet->setCellValue("A$r", 'ประเภท')->setCellValue("B$r", 'หัวข้อ/เลขที่')->setCellValue("C$r", 'วิธี')->setCellValue("D$r", 'จำนวนเงิน');
            $r++;
            foreach (FinanceCashTxn::find()->with('category')->where(['txn_type' => 'IN', 'doc_date' => $dbDate, 'voucher_id' => null])->all() as $t) {
                $sheet->setCellValue("A$r", 'รับ')->setCellValue("B$r", $t->category ? $t->category->name : ($t->doc_no ?: ''))
                    ->setCellValue("C$r", $t->payMethodLabel())->setCellValue("D$r", (float) $t->amount);
                $r++;
            }
            foreach (FinanceCashVoucher::find()->where(['pay_date' => $dbDate])->all() as $v) {
                $sheet->setCellValue("A$r", 'จ่าย')->setCellValue("B$r", $v->doc_no ?: $v->cheque_no)
                    ->setCellValue("C$r", $v->payMethodLabel())->setCellValue("D$r", (float) $v->net_amount);
                $r++;
            }
            $fileName = 'ปิดบัญชีประจำวัน_' . AppHelper::convertToThai($dbDate) . '.xlsx';
        } elseif ($report === 'summary') {
            $sheet->setTitle('สรุปการปิดบัญชี');
            $sheet->setCellValue('A1', 'สรุปการปิดบัญชี ปีงบประมาณ ' . $fy);
            $sheet->setCellValue('A3', 'วันที่ปิด')->setCellValue('B3', 'จำนวนรับ')->setCellValue('C3', 'ยอดรับ')
                ->setCellValue('D3', 'จำนวนจ่าย')->setCellValue('E3', 'ยอดจ่าย');
            $r = 4;
            foreach (FinanceCashClose::find()->where(['close_type' => 'daily', 'fiscal_year' => $fy])->orderBy(['close_date' => SORT_ASC])->all() as $b) {
                $sheet->setCellValue("A$r", AppHelper::convertToThai($b->close_date))->setCellValue("B$r", $b->in_count)
                    ->setCellValue("C$r", (float) $b->total_in)->setCellValue("D$r", $b->out_count)->setCellValue("E$r", (float) $b->total_out);
                $r++;
            }
            $fileName = 'สรุปการปิดบัญชี_' . $fy . '.xlsx';
        } else {
            $sheet->setTitle('รายงานประจำปี');
            $sheet->setCellValue('A1', 'รายงานรับ-จ่ายประจำปีงบประมาณ ' . $fy);
            $data = $this->yearlyReport($fy);
            $r = 3;
            foreach ([FinanceCashCategory::TYPE_IN => 'รายรับ', FinanceCashCategory::TYPE_OUT => 'รายจ่าย'] as $type => $label) {
                $sheet->setCellValue("A$r", $label);
                $r++;
                foreach ($data[$type]['groups'] as $g) {
                    $sheet->setCellValue("A$r", $g['name'])->setCellValue("C$r", $g['total']);
                    $r++;
                    foreach ($g['rows'] as $row) {
                        $sheet->setCellValue("B$r", $row['name'])->setCellValue("C$r", $row['amount']);
                        $r++;
                    }
                }
                $sheet->setCellValue("A$r", 'รวม' . $label)->setCellValue("C$r", $data[$type]['total']);
                $r += 2;
            }
            $fileName = 'รายงานรับจ่ายประจำปี_' . $fy . '.xlsx';
        }

        return $this->streamBook($book, $fileName, $report);
    }

    /** เขียน xlsx ลง runtime แล้ว sendFile (ลบทิ้งหลังส่ง) */
    private function streamBook(\PhpOffice\PhpSpreadsheet\Spreadsheet $book, string $fileName, string $report)
    {
        $path = Yii::getAlias('@runtime') . '/cash_' . $report . '_' . date('YmdHis') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($path);
        return Yii::$app->response->sendFile($path, $fileName, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($path) {
            @unlink($path);
        });
    }

    public function actionDelete($id)
    {
        $model = $this->findTxn($id);
        if ($model->is_closed) {
            Yii::$app->session->setFlash('error', 'รายการนี้อยู่ในงวดที่ปิดบัญชีแล้ว ลบไม่ได้');
        } else {
            $type = $model->txn_type;
            $year = $model->fiscal_year;
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบรายการเรียบร้อย');
            return $this->redirect([$type === FinanceCashCategory::TYPE_OUT ? 'expense' : 'income', 'fiscal_year' => $year]);
        }
        return $this->redirect(['income']);
    }

    // ---- จัดการผังบัญชี (chart of accounts) --------------------------------

    public function actionCategory($type = FinanceCashCategory::TYPE_IN)
    {
        $type = $type === FinanceCashCategory::TYPE_OUT ? FinanceCashCategory::TYPE_OUT : FinanceCashCategory::TYPE_IN;
        return $this->render('category', [
            'type' => $type,
            'tree' => FinanceCashCategory::treeArray($type),
        ]);
    }

    public function actionCategorySave()
    {
        $post = Yii::$app->request->post();
        $id = (int) ($post['id'] ?? 0);
        $model = $id ? FinanceCashCategory::findOne($id) : new FinanceCashCategory();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหมวด');
        }
        $model->txn_type = ($post['txn_type'] ?? FinanceCashCategory::TYPE_IN) === FinanceCashCategory::TYPE_OUT
            ? FinanceCashCategory::TYPE_OUT : FinanceCashCategory::TYPE_IN;
        $model->name = trim((string) ($post['name'] ?? ''));
        $model->description = trim((string) ($post['description'] ?? '')) ?: null;
        $model->parent_id = ((int) ($post['parent_id'] ?? 0)) ?: null;
        $model->sort_order = (int) ($post['sort_order'] ?? 0);
        $model->is_active = isset($post['is_active']) ? (int) $post['is_active'] : 1;
        // ระดับคิดจากความลึกของแม่: ไม่มีแม่=group, แม่เป็น group=category, ที่เหลือ=account
        $model->level = $model->levelFromParent();

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกผังบัญชีเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['category', 'type' => $model->txn_type]);
    }

    public function actionCategoryDelete()
    {
        $id = (int) Yii::$app->request->post('id');
        $model = FinanceCashCategory::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหมวด');
        }
        $type = $model->txn_type;
        if ($model->getChildren()->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะยังมีหมวดย่อยอยู่ภายใน');
        } elseif (FinanceCashTxn::find()->where(['category_id' => $id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะมีรายการบันทึกใช้หมวดนี้อยู่');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบหมวดเรียบร้อย');
        }
        return $this->redirect(['category', 'type' => $type]);
    }

    /** ป้อนผังบัญชีมาตรฐาน mophcash ครั้งเดียว (idempotent — ข้ามถ้ามีผังอยู่แล้ว) */
    public function actionSeedChart()
    {
        if (FinanceCashCategory::find()->exists()) {
            Yii::$app->session->setFlash('warning', 'มีผังบัญชีอยู่แล้ว ข้ามการ seed (กันซ้ำ)');
            return $this->redirect(['category']);
        }
        $count = 0;
        $insert = function (array $node, string $type, ?int $parentId, string $level, int &$count) use (&$insert) {
            $c = new FinanceCashCategory([
                'txn_type' => $type,
                'parent_id' => $parentId,
                'level' => $level,
                'name' => $node['name'],
                'description' => $node['desc'] ?? null,
                'sort_order' => $count,
            ]);
            $c->save(false);
            $count++;
            $childLevel = $level === FinanceCashCategory::LEVEL_GROUP
                ? FinanceCashCategory::LEVEL_CATEGORY : FinanceCashCategory::LEVEL_ACCOUNT;
            foreach ($node['children'] ?? [] as $child) {
                $insert($child, $type, $c->id, $childLevel, $count);
            }
        };

        foreach (self::standardChart() as $type => $groups) {
            foreach ($groups as $group) {
                $insert($group, $type, null, FinanceCashCategory::LEVEL_GROUP, $count);
            }
        }
        Yii::$app->session->setFlash('success', "ป้อนผังบัญชีมาตรฐานแล้ว $count รายการ");
        return $this->redirect(['category']);
    }

    private function findTxn($id): FinanceCashTxn
    {
        $model = FinanceCashTxn::findOne((int) $id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        return $model;
    }

    /** employee id ของผู้ใช้ปัจจุบัน (0 ถ้าไม่ผูก) */
    private function currentEmpId(): int
    {
        try {
            if (!Yii::$app->has('user')) {
                return 0;
            }
            $me = \app\components\UserHelper::GetEmployee();
            return $me ? (int) $me->id : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** ตรวจเลขใบเสร็จกับทะเบียนเล่ม — คืน array ข้อความเตือน (ไม่บล็อก) */
    private function receiptWarnings(string $docNo): array
    {
        $parts = explode('/', $docNo, 2);
        if (count($parts) < 2 || !ctype_digit(trim($parts[1]))) {
            return []; // ไม่ใช่รูปแบบ "เล่ม/เลข" → ข้าม
        }
        $bookNo = $parts[0];
        $num = (int) $parts[1];
        $book = FinanceReceiptBook::find()->where(['book_no' => $bookNo])->one();
        if (!$book) {
            return ["ไม่พบเล่ม $bookNo ในทะเบียนใบเสร็จ"];
        }
        $w = [];
        if ($num < $book->number_from || $num > $book->number_to) {
            $w[] = "เลข {$num} อยู่นอกช่วงเล่ม ({$book->number_from}–{$book->number_to})";
        }
        $dup = (int) FinanceCashTxn::find()->where(['txn_type' => FinanceCashCategory::TYPE_IN, 'doc_no' => $docNo])->count();
        if ($dup > 1) {
            $w[] = "เลข $docNo ถูกใช้ซ้ำ ($dup ครั้ง)";
        }
        $empId = $this->currentEmpId();
        if ($book->issued_to_emp_id && $empId && (int) $book->issued_to_emp_id !== $empId) {
            $w[] = "เล่ม $bookNo เบิกให้เจ้าหน้าที่คนอื่น";
        }
        return $w;
    }

    /**
     * ผังบัญชีมาตรฐานเงินบำรุง — คัดจาก tree "เลือกหัวข้อรายรับ/รายจ่าย" ของ mophcash โดยตรง
     * (เฉพาะหัวข้อที่มีปุ่ม [เลือก] = leaf ที่ใช้ลงรายการ) โครงสร้าง 2 ระดับ กลุ่ม → หัวข้อ
     * รายรับ 15 หัวข้อ (9+6) / รายจ่าย 23 หัวข้อ (12+6+3+2)
     */
    private static function standardChart(): array
    {
        $n = fn (string $name, array $children = []) => ['name' => $name, 'children' => $children];
        return [
            FinanceCashCategory::TYPE_IN => [
                $n('รายรับจากการดำเนินงาน', [
                    $n('รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC'),
                    $n('รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC งบลงทุน'),
                    $n('รายรับจากระบบปฏิบัติการฉุกเฉิน (EMS)'),
                    $n('รายรับค่ารักษาพยาบาลเบิกจ่ายตรงกรมบัญชีกลาง'),
                    $n('รายรับค่ารักษาพยาบาลผู้ป่วยเบิกต้นสังกัด'),
                    $n('รายรับค่ารักษาพยาบาลเบิกจาก อปท.'),
                    $n('รายรับค่ารักษาพยาบาลจากกองทุนประกันสังคม'),
                    $n('รายรับค่ารักษาพยาบาลแรงงานต่างด้าว'),
                    $n('รายรับค่ารักษาพยาบาลและการบริการอื่น'),
                ]),
                $n('รายรับอื่น', [
                    $n('รายรับเงินช่วยเหลือ'),
                    $n('รายรับเงินอุดหนุน'),
                    $n('รายรับจากการบริจาค'),
                    $n('รายรับดอกเบี้ยเงินฝากธนาคาร'),
                    $n('รายรับอื่น'),
                    $n('รายรับไม่ทราบแหล่งที่มา'),
                ]),
            ],
            FinanceCashCategory::TYPE_OUT => [
                $n('รายจ่ายบุคลากร', [
                    $n('ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง'),
                    $n('ค่าล่วงเวลางานบริการ / งานสนับสนุน'),
                    $n('ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่'),
                    $n('ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน'),
                    $n('ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)'),
                    $n('ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)'),
                    $n('เงินเพิ่ม (พ.ต.ส)'),
                    $n('ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5'),
                    $n('ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)'),
                    $n('ค่าตอบแทนอื่น'),
                    $n('เงินค่าใช้จ่ายบุคลากรอื่น'),
                    $n('ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)'),
                ]),
                $n('รายจ่ายจากการดำเนินงาน', [
                    $n('ค่ายา'),
                    $n('ค่าเวชภัณฑ์มิใช่ยา'),
                    $n('ค่าวัสดุ'),
                    $n('ค่าสาธารณูปโภค'),
                    $n('ค่าใช้สอย'),
                    $n('ค่าใช้จ่ายดำเนินงานอื่น'),
                ]),
                $n('รายจ่ายลงทุน', [
                    $n('ค่าครุภัณฑ์'),
                    $n('ค่าที่ดินและสิ่งก่อสร้าง'),
                    $n('ค่าครุภัณฑ์ต่ำกว่าเกณฑ์'),
                ]),
                $n('รายจ่ายอื่น', [
                    $n('รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.'),
                    $n('รายจ่ายอื่นๆ'),
                ]),
            ],
        ];
    }
}
