<?php

use app\modules\finance\models\FinanceCashAccount;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinanceBankReconcile $model */

$isNew = $model->isNewRecord;
$this->title = ($isNew ? 'สร้าง' : 'แก้ไข') . 'งบพิสูจน์ยอดเงินฝาก';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'งบพิสูจน์ยอด', 'url' => ['index']];
$this->params['breadcrumbs'][] = $isNew ? 'สร้าง' : 'แก้ไข';

$months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$val = fn ($v) => $v === null ? '' : $v;

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-bank fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'bankrec']);
$this->endBlock();
?>

<div class="card shadow-sm">
    <div class="card-body">
        <?= Html::beginForm('', 'post') ?>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label small mb-1">บัญชีเงินฝาก</label>
                <?= Html::dropDownList('FinanceBankReconcile[cash_account_id]', $model->cash_account_id, FinanceCashAccount::activeList(), ['class' => 'form-select', 'prompt' => '— เลือกบัญชี —']) ?>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">ปีงบประมาณ</label>
                <?= Html::textInput('FinanceBankReconcile[fiscal_year]', $val($model->fiscal_year), ['class' => 'form-control', 'type' => 'number']) ?>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">เดือน</label>
                <?= Html::dropDownList('FinanceBankReconcile[period_month]', $model->period_month, $months, ['class' => 'form-select', 'prompt' => '— ทั้งปี —']) ?>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">วันที่ตาม statement</label>
                <?= DatepickerThai::widget(['name' => 'FinanceBankReconcile[statement_date]', 'value' => $model->statement_date ?: date('Y-m-d'), 'options' => ['class' => 'form-control', 'autocomplete' => 'off']]) ?>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">ยอดตาม statement ธนาคาร</label>
                <?= Html::textInput('FinanceBankReconcile[statement_balance]', $val($model->statement_balance), ['class' => 'form-control text-end', 'inputmode' => 'decimal']) ?>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">ยอดตามบัญชี รพ. (book)</label>
                <?= Html::textInput('FinanceBankReconcile[book_balance]', $val($model->book_balance), ['class' => 'form-control text-end', 'inputmode' => 'decimal']) ?>
            </div>
            <div class="col-12">
                <label class="form-label small mb-1">หมายเหตุ</label>
                <?= Html::textarea('FinanceBankReconcile[note]', $val($model->note), ['class' => 'form-control', 'rows' => 2]) ?>
            </div>
        </div>
        <?php if ($model->hasErrors()): ?>
            <div class="alert alert-danger mt-3 mb-0 small"><?= implode('<br>', $model->getErrorSummary(true)) ?></div>
        <?php endif; ?>
        <div class="d-flex gap-2 mt-3">
            <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-success']) ?>
            <a href="<?= Url::to($isNew ? ['index'] : ['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
        <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>บันทึกหัวงบก่อน แล้วจึงเพิ่มรายการกระทบยอด (เช็คค้างจ่าย/เงินฝากระหว่างทาง/ค่าธรรมเนียม)</div>
        <?= Html::endForm() ?>
    </div>
</div>
