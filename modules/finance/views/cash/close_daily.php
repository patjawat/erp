<?php

use app\modules\finance\models\FinanceCashTxn;
use app\modules\finance\models\FinanceCashVoucher;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $dbDate */
/** @var string $thaiDate */
/** @var FinanceCashTxn[] $inRows */
/** @var FinanceCashVoucher[] $outRows */
/** @var array $summary */
/** @var app\modules\finance\models\FinanceCashClose[] $closedBatches */

$this->title = 'ปิดบัญชีประจำวัน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-lock" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>เลือกวันที่ → ติ๊กรายการ → ปิดบัญชี (ล็อกแก้ไข + สรุปตามวิธีรับ-จ่าย)<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$inMethods = FinanceCashTxn::PAY_METHODS;
$outMethods = FinanceCashVoucher::PAY_METHODS;
?>

<?= $this->render('_menu', ['active' => 'close']) ?>
<?= $this->render('_close_menu', ['active' => 'daily']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label" for="f-date">วันที่</label>
                <?= DatepickerThai::widget(['name' => 'date', 'value' => $thaiDate, 'options' => ['id' => 'f-date', 'autocomplete' => 'off']]) ?>
            </div>
            <div class="col-6 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>ค้นหา</button>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-excel me-1"></i>ส่งออก/พิมพ์รายงาน</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= Url::to(['close-excel', 'report' => 'register', 'date' => $thaiDate]) ?>"><i class="bi bi-table me-2"></i>ทะเบียนปิดบัญชี (สรุปการปิดบัญชี)</a></li>
                        <li><a class="dropdown-item" href="<?= Url::to(['close-excel', 'report' => 'balance407', 'date' => $thaiDate]) ?>"><i class="bi bi-cash-stack me-2"></i>รายงานเงินคงเหลือประจำวัน (407)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= Url::to(['close-excel', 'report' => 'daily', 'date' => $thaiDate]) ?>"><i class="bi bi-list-ul me-2"></i>สรุปรับ-จ่ายวันนี้ (ย่อ)</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($closedBatches): ?>
    <div class="alert alert-secondary"><i class="bi bi-info-circle me-1"></i>
        วันนี้ปิดบัญชีไปแล้ว <?= count($closedBatches) ?> งวด — รายการที่ปิดแล้วถูกล็อกและไม่แสดงด้านล่าง (ดูใน “สรุปการปิดบัญชี”)
    </div>
<?php endif; ?>

<?= Html::beginForm(['close-do'], 'post', ['id' => 'close-form']) ?>
<?= Html::hiddenInput('date', $dbDate) ?>
<div class="row g-3">
    <!-- รายรับ -->
    <div class="col-lg-6">
        <div class="card border h-100">
            <div class="card-header bg-success-subtle d-flex justify-content-between">
                <span class="fw-semibold text-success-emphasis">รายรับ (<?= count($inRows) ?>)</span>
                <div class="form-check mb-0"><input class="form-check-input" type="checkbox" data-check-all="in" id="ca-in"><label class="form-check-label small" for="ca-in">เลือกทั้งหมด</label></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th style="width:34px"></th><th>หัวข้อ</th><th>วิธี</th><th class="text-end">จำนวน</th></tr></thead>
                    <tbody>
                        <?php foreach ($inRows as $t): ?>
                            <tr>
                                <td><input class="form-check-input" type="checkbox" name="txn_ids[]" value="<?= $t->id ?>" data-grp="in"></td>
                                <td><small><?= Html::encode($t->category ? $t->category->name : '-') ?></small></td>
                                <td><small><?= Html::encode($t->payMethodLabel()) ?></small></td>
                                <td class="text-end"><?= number_format((float) $t->amount, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$inRows): ?><tr><td colspan="4" class="text-center text-body-secondary py-3">ไม่มีรายการรับที่ยังไม่ปิด</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- รายจ่าย -->
    <div class="col-lg-6">
        <div class="card border h-100">
            <div class="card-header bg-warning-subtle d-flex justify-content-between">
                <span class="fw-semibold text-warning-emphasis">รายจ่าย (<?= count($outRows) ?> ใบสำคัญ)</span>
                <div class="form-check mb-0"><input class="form-check-input" type="checkbox" data-check-all="out" id="ca-out"><label class="form-check-label small" for="ca-out">เลือกทั้งหมด</label></div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th style="width:34px"></th><th>เลขใบสำคัญ/เช็ค</th><th>วิธี</th><th class="text-end">จ่ายจริง</th></tr></thead>
                    <tbody>
                        <?php foreach ($outRows as $v): ?>
                            <tr>
                                <td><input class="form-check-input" type="checkbox" name="voucher_ids[]" value="<?= $v->id ?>" data-grp="out"></td>
                                <td><small><?= Html::encode($v->doc_no ?: $v->cheque_no ?: '-') ?></small></td>
                                <td><small><?= Html::encode($v->payMethodLabel()) ?></small></td>
                                <td class="text-end"><?= number_format((float) $v->net_amount, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$outRows): ?><tr><td colspan="4" class="text-center text-body-secondary py-3">ไม่มีใบสำคัญจ่ายที่ยังไม่ปิด</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-3">
    <button type="submit" class="btn btn-dark btn-lg" <?= (!$inRows && !$outRows) ? 'disabled' : '' ?> onclick="return confirm('ยืนยันปิดบัญชีรายการที่เลือก? รายการที่ปิดจะถูกล็อกแก้ไข')">
        <i class="bi bi-lock-fill me-1"></i> ปิดบัญชี
    </button>
</div>
<?= Html::endForm() ?>

<!-- สรุปการปิดบัญชี (ยอดของวันนั้นแยกตามวิธี) -->
<div class="card border mt-3">
    <div class="card-header bg-body fw-semibold">สรุปการปิดบัญชี — <?= Html::encode($thaiDate) ?></div>
    <div class="card-body row g-4">
        <div class="col-md-6">
            <h6 class="text-success">รายรับ</h6>
            <table class="table table-sm">
                <?php $ti = 0; foreach ($summary['in'] as $row): $ti += (float) $row['s']; ?>
                    <tr><td><?= Html::encode($inMethods[$row['pay_method']] ?? ($row['pay_method'] ?: 'ไม่ระบุ')) ?></td>
                        <td class="text-end text-body-secondary"><?= (int) $row['c'] ?> รายการ</td>
                        <td class="text-end fw-semibold"><?= number_format((float) $row['s'], 2) ?></td></tr>
                <?php endforeach; ?>
                <tr class="table-light"><td class="fw-bold">รวมรับ</td><td></td><td class="text-end fw-bold"><?= number_format($ti, 2) ?></td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-warning">รายจ่าย</h6>
            <table class="table table-sm">
                <?php $to = 0; foreach ($summary['out'] as $row): $to += (float) $row['s']; ?>
                    <tr><td><?= Html::encode($outMethods[$row['pay_method']] ?? ($row['pay_method'] ?: 'ไม่ระบุ')) ?></td>
                        <td class="text-end text-body-secondary"><?= (int) $row['c'] ?> รายการ</td>
                        <td class="text-end fw-semibold"><?= number_format((float) $row['s'], 2) ?></td></tr>
                <?php endforeach; ?>
                <tr class="table-light"><td class="fw-bold">รวมจ่าย</td><td></td><td class="text-end fw-bold"><?= number_format($to, 2) ?></td></tr>
            </table>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
document.querySelectorAll('[data-check-all]').forEach(function (master) {
    master.addEventListener('change', function () {
        document.querySelectorAll('input[data-grp="' + master.dataset.checkAll + '"]').forEach(function (cb) { cb.checked = master.checked; });
    });
});
JS);
?>
