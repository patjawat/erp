<?php

use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\finance\models\FinancePatientDeposit $model */

$isNew = $model->isNewRecord;
$this->title = ($isNew ? 'รับ' : 'แก้ไข') . 'เงินมัดจำผู้ป่วย';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เงินมัดจำ', 'url' => ['deposits']];
$this->params['breadcrumbs'][] = $isNew ? 'รับเงินมัดจำ' : 'แก้ไข';

$this->beginBlock('page-title');
echo '<div class="d-flex align-items-center gap-2"><i class="bi bi-piggy-bank fs-4"></i><h4 class="mb-0">' . Html::encode($this->title) . '</h4></div>';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'ar']);
$this->endBlock();

$val = fn ($v) => $v === null ? '' : $v;
?>

<div class="card shadow-sm">
    <div class="card-body">
        <?= Html::beginForm('', 'post') ?>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small mb-1">วันที่รับฝาก</label>
                <?= DatepickerThai::widget(['name' => 'FinancePatientDeposit[deposit_date]', 'value' => $model->deposit_date ?: date('Y-m-d'), 'options' => ['class' => 'form-control', 'autocomplete' => 'off']]) ?>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">เลขที่ใบรับเงิน</label>
                <?= Html::textInput('FinancePatientDeposit[receipt_no]', $val($model->receipt_no), ['class' => 'form-control', 'maxlength' => 64]) ?>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">HN</label>
                <?= Html::textInput('FinancePatientDeposit[hn]', $val($model->hn), ['class' => 'form-control', 'maxlength' => 32]) ?>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">AN</label>
                <?= Html::textInput('FinancePatientDeposit[an]', $val($model->an), ['class' => 'form-control', 'maxlength' => 32]) ?>
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-1">ชื่อผู้ป่วย</label>
                <?= Html::textInput('FinancePatientDeposit[patient_name]', $val($model->patient_name), ['class' => 'form-control', 'maxlength' => 255]) ?>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">ยอดรับฝาก</label>
                <?= Html::textInput('FinancePatientDeposit[amount]', $val($model->amount), ['class' => 'form-control text-end', 'inputmode' => 'decimal']) ?>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">หักชำระแล้ว</label>
                <?= Html::textInput('FinancePatientDeposit[used_amount]', $val($model->used_amount), ['class' => 'form-control text-end', 'inputmode' => 'decimal']) ?>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">คืนแล้ว</label>
                <?= Html::textInput('FinancePatientDeposit[refunded_amount]', $val($model->refunded_amount), ['class' => 'form-control text-end', 'inputmode' => 'decimal']) ?>
            </div>
            <div class="col-12">
                <label class="form-label small mb-1">หมายเหตุ</label>
                <?= Html::textInput('FinancePatientDeposit[note]', $val($model->note), ['class' => 'form-control', 'maxlength' => 500]) ?>
            </div>
        </div>
        <?php if ($model->hasErrors()): ?>
            <div class="alert alert-danger mt-3 mb-0 small"><?= implode('<br>', $model->getErrorSummary(true)) ?></div>
        <?php endif; ?>
        <div class="d-flex gap-2 mt-3">
            <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-success']) ?>
            <a href="<?= Url::to(['deposits']) ?>" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
        <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>สถานะคำนวณอัตโนมัติจากยอดคงเหลือ (รับฝาก − หัก − คืน)</div>
        <?= Html::endForm() ?>
    </div>
</div>
