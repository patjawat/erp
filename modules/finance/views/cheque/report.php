<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceCheque;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceCheque[] $rows */
/** @var array $summary */
/** @var array $outstanding */
/** @var array $accounts */
/** @var array $filter */

$this->title = 'รายงานเช็ค';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนคุมเช็ค', 'url' => ['/finance/cheque']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'รายงานเช็คจ่าย + เช็คคงค้างยังไม่ขึ้นเงิน — กรองตามวันที่/บัญชี แล้ว Export Excel';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'cheque']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
$thDate = fn($d) => $d ? (date_create($d) ? date_create($d)->format('d/m/') . ((int) date_create($d)->format('Y') + 543) : $d) : '–';
$badge = [
    FinanceCheque::STATUS_DRAFT => 'bg-secondary-subtle text-secondary-emphasis',
    FinanceCheque::STATUS_PRINTED => 'bg-info-subtle text-info-emphasis',
    FinanceCheque::STATUS_HANDED => 'bg-primary-subtle text-primary-emphasis',
    FinanceCheque::STATUS_CLEARED => 'bg-success-subtle text-success-emphasis',
    FinanceCheque::STATUS_BOUNCED => 'bg-warning-subtle text-warning-emphasis',
    FinanceCheque::STATUS_VOID => 'bg-danger-subtle text-danger-emphasis',
];
$sc = fn($k) => $summary[$k]['count'] ?? 0;
$ss = fn($k) => $summary[$k]['sum'] ?? 0;
$exportUrl = Url::to(['report', 'from' => $filter['from'], 'to' => $filter['to'], 'account_id' => $filter['account_id'], 'status' => $filter['status'], 'format' => 'xlsx']);

$cards = [
    ['label' => 'คงค้าง (ยังไม่ขึ้นเงิน)', 'count' => $outstanding['count'], 'sum' => $outstanding['sum'], 'cls' => 'border-primary', 'text' => 'text-primary'],
    ['label' => 'ขึ้นเงินแล้ว', 'count' => $sc(FinanceCheque::STATUS_CLEARED), 'sum' => $ss(FinanceCheque::STATUS_CLEARED), 'cls' => 'border-success', 'text' => 'text-success'],
    ['label' => 'เช็คคืน (เด้ง)', 'count' => $sc(FinanceCheque::STATUS_BOUNCED), 'sum' => $ss(FinanceCheque::STATUS_BOUNCED), 'cls' => 'border-warning', 'text' => 'text-warning'],
    ['label' => 'ยกเลิก', 'count' => $sc(FinanceCheque::STATUS_VOID), 'sum' => $ss(FinanceCheque::STATUS_VOID), 'cls' => 'border-danger', 'text' => 'text-danger'],
];
?>

<div class="row g-2 mb-3">
    <?php foreach ($cards as $c): ?>
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm h-100 border-start border-4 <?= $c['cls'] ?>">
                <div class="card-body py-2 px-3">
                    <div class="small text-body-secondary"><?= Html::encode($c['label']) ?></div>
                    <div class="fs-5 fw-bold <?= $c['text'] ?>"><?= $fmt($c['sum']) ?></div>
                    <div class="small text-body-secondary"><?= (int) $c['count'] ?> ฉบับ</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <?= Html::beginForm(['report'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">ตั้งแต่วันที่</label>
                <input type="text" name="from" class="form-control form-control-sm" value="<?= Html::encode($filter['from']) ?>" placeholder="วว/ดด/พ.ศ.">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">ถึงวันที่</label>
                <input type="text" name="to" class="form-control form-control-sm" value="<?= Html::encode($filter['to']) ?>" placeholder="วว/ดด/พ.ศ.">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">บัญชีจ่าย</label>
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">— ทุกบัญชี —</option>
                    <?php foreach ($accounts as $aid => $al): ?>
                        <option value="<?= $aid ?>" <?= (int) $filter['account_id'] === (int) $aid ? 'selected' : '' ?>><?= Html::encode($al) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">สถานะ</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">— ทั้งหมด —</option>
                    <option value="outstanding" <?= $filter['status'] === 'outstanding' ? 'selected' : '' ?>>คงค้าง (ยังไม่ขึ้นเงิน)</option>
                    <?php foreach (FinanceCheque::statusOptions() as $sk => $sl): ?>
                        <option value="<?= $sk ?>" <?= $filter['status'] === $sk ? 'selected' : '' ?>><?= Html::encode($sl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search me-1"></i>ดู</button>
                <a href="<?= $exportUrl ?>" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
            </div>
        <?= Html::endForm() ?>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>วันที่</th><th>เลขที่เช็ค</th><th>บัญชีจ่าย</th><th>จ่ายให้</th>
                    <th class="text-end">จำนวนเงิน</th><th class="text-center">สถานะ</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">ไม่มีเช็คตามเงื่อนไข</td></tr>
            <?php else: $sum = 0; foreach ($rows as $c): $sum += (float) $c->amount; ?>
                <tr>
                    <td><?= $thDate($c->cheque_date) ?></td>
                    <td><a href="<?= Url::to(['view', 'id' => $c->id]) ?>" class="text-decoration-none"><?= Html::encode($c->cheque_no) ?></a></td>
                    <td><?= $c->cashAccount ? Html::encode($c->cashAccount->label()) : '–' ?></td>
                    <td><?= Html::encode($c->payee_name) ?></td>
                    <td class="text-end"><?= $fmt($c->amount) ?></td>
                    <td class="text-center"><span class="badge <?= $badge[$c->status] ?? 'bg-light' ?>"><?= Html::encode($c->statusLabel()) ?></span></td>
                </tr>
            <?php endforeach; ?>
                <tr class="table-light fw-bold"><td colspan="4" class="text-end">รวม <?= count($rows) ?> ฉบับ</td><td class="text-end"><?= $fmt($sum) ?></td><td></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
