<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceArInvoice;
use app\modules\finance\models\FinanceArSettlement;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinanceArInvoice[] $invoices */
/** @var int $fy */
/** @var int $fundId */
/** @var string|null $status */
/** @var array $funds */
/** @var int[] $fiscalYears */

$this->title = 'ทะเบียนลูกหนี้ค่ารักษา';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ลูกหนี้ค่ารักษา', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนลูกหนี้';

$canOperate = Yii::$app->user->can('financeOperate');
$money = fn ($v) => number_format((float) $v, 2);
$statusLabels = FinanceArInvoice::statusOptions();

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-list-ul fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'ar']);
$this->endBlock();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= Url::to(['index', 'fiscal_year' => $fy]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>ภาพรวม AR</a>
</div>

<form method="get" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">ปีงบ</label>
            <select name="fiscal_year" class="form-select form-select-sm">
                <?php foreach ($fiscalYears as $y): ?><option value="<?= $y ?>" <?= $y === $fy ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label small mb-1">สิทธิ</label>
            <select name="fund_id" class="form-select form-select-sm">
                <option value="">ทุกสิทธิ</option>
                <?php foreach ($funds as $id => $name): ?><option value="<?= $id ?>" <?= $id === $fundId ? 'selected' : '' ?>><?= Html::encode($name) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small mb-1">สถานะ</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">ทุกสถานะ</option>
                <?php foreach ($statusLabels as $k => $v): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= Html::encode($v) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-auto"><button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>กรอง</button></div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-header bg-body"><span class="text-body-secondary small">แสดงสูงสุด 500 รายการ · <?= count($invoices) ?> รายการ</span></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle text-nowrap">
            <thead class="table-light text-center">
                <tr>
                    <th class="text-start">วันที่</th><th class="text-start">เลขอ้างอิง</th><th class="text-start">สิทธิ</th><th class="text-start">ผู้ป่วย/HN</th>
                    <th class="text-end">ตั้งเบิก</th><th class="text-end">คงค้าง</th><th>สถานะ</th><?php if ($canOperate): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$invoices): ?>
                    <tr><td colspan="<?= $canOperate ? 8 : 7 ?>" class="text-center text-body-secondary py-4">ไม่พบลูกหนี้</td></tr>
                <?php endif; ?>
                <?php foreach ($invoices as $inv): ?>
                    <?php $out = $inv->getOutstanding(); ?>
                    <tr>
                        <td><?= $inv->service_date ? Html::encode(AppHelper::convertToThai($inv->service_date)) : '-' ?></td>
                        <td><?= Html::encode($inv->doc_no ?: '-') ?></td>
                        <td><?= Html::encode($inv->fund->name ?? '-') ?></td>
                        <td><?= Html::encode($inv->patient_name ?: ($inv->hn ? 'HN ' . $inv->hn : '-')) ?></td>
                        <td class="text-end"><?= $money($inv->billed_amount) ?></td>
                        <td class="text-end fw-semibold <?= $out > 0 ? 'text-warning-emphasis' : 'text-success-emphasis' ?>"><?= $money($out) ?></td>
                        <td class="text-center"><span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode($inv->statusLabel()) ?></span></td>
                        <?php if ($canOperate): ?>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-success py-0 px-1 js-settle"
                                    data-id="<?= $inv->id ?>" data-label="<?= Html::encode(($inv->doc_no ?: '#' . $inv->id) . ' · คงค้าง ' . $money($out)) ?>"
                                    data-bs-toggle="modal" data-bs-target="#settleModal" title="รับชำระ/ตัดปรับ"><i class="bi bi-cash-coin"></i></button>
                                <?= Html::beginForm(['delete-invoice', 'id' => $inv->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ลบลูกหนี้นี้และการรับชำระทั้งหมด?')"]) ?>
                                <button type="submit" class="btn btn-sm btn-link text-danger py-0 px-1" title="ลบ"><i class="bi bi-trash"></i></button>
                                <?= Html::endForm() ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canOperate): ?>
<div class="modal fade" id="settleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <?= Html::beginForm(['add-settlement', 'id' => 0], 'post', ['id' => 'settleForm']) ?>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รับชำระ / ตัดปรับลูกหนี้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <p class="text-body-secondary small mb-3" id="settleTarget"></p>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small mb-1">วันที่</label>
                        <?= DatepickerThai::widget(['name' => 'FinanceArSettlement[settle_date]', 'value' => date('Y-m-d'), 'options' => ['class' => 'form-control form-control-sm', 'autocomplete' => 'off']]) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">ประเภท</label>
                        <?= Html::dropDownList('FinanceArSettlement[kind]', 'receipt', FinanceArSettlement::kindOptions(), ['class' => 'form-select form-select-sm']) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">จำนวนเงิน</label>
                        <?= Html::textInput('FinanceArSettlement[amount]', '', ['class' => 'form-control form-control-sm text-end', 'inputmode' => 'decimal']) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">เลขที่เอกสาร</label>
                        <?= Html::textInput('FinanceArSettlement[doc_no]', '', ['class' => 'form-control form-control-sm']) ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label small mb-1">หมายเหตุ</label>
                        <?= Html::textInput('FinanceArSettlement[note]', '', ['class' => 'form-control form-control-sm']) ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>บันทึก</button>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php
$actionBase = Url::to(['add-settlement']);
$this->registerJs(<<<JS
document.querySelectorAll('.js-settle').forEach(function(b){
  b.addEventListener('click', function(){
    document.getElementById('settleForm').action = '$actionBase' + '?id=' + this.dataset.id;
    document.getElementById('settleTarget').textContent = this.dataset.label;
  });
});
JS);
?>
<?php endif; ?>
