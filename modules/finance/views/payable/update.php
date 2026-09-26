<?php

use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinancePayable;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinancePayable $model */

$this->title = 'แก้ข้อมูลบิล ' . $model->payable_no;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเจ้าหนี้', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->payable_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ข้อมูล';
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'payable']);
$this->endBlock();

$err = static fn(string $a) => $model->hasErrors($a) ? '<div class="invalid-feedback d-block">' . Html::encode($model->getFirstError($a)) . '</div>' : '';
?>

<div class="card border shadow-sm" style="max-width:760px">
    <div class="card-header bg-body">
        <strong><?= Html::encode($model->vendor_name_snapshot) ?></strong>
        <span class="text-body-secondary ms-2">ยอดหนี้ <?= number_format((float) $model->gross_amount, 2) ?> บาท</span>
    </div>
    <div class="card-body">
        <?= Html::beginForm(['update', 'id' => $model->id], 'post') ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">เลขที่ใบแจ้งหนี้</label>
                <?= Html::textInput('FinancePayable[invoice_no]', $model->invoice_no, ['class' => 'form-control' . ($model->hasErrors('invoice_no') ? ' is-invalid' : ''), 'maxlength' => 100]) ?>
                <?= $err('invoice_no') ?>
            </div>
            <div class="col-md-6">
                <?= $this->render('@app/modules/finance/views/_date_field', ['model' => $model, 'attr' => 'invoice_date', 'label' => 'วันที่ใบแจ้งหนี้']) ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">จำนวนวันเครดิต</label>
                <?= Html::input('number', 'FinancePayable[credit_days]', $model->credit_days, ['class' => 'form-control', 'min' => 0, 'max' => 3650]) ?>
                <div class="form-text">วันครบกำหนด = <?= $model->isBilled() ? 'วันวางบิล' : 'วันที่รับ' ?> (<?= ThaiDate::date($model->billing_date) ?>) + เครดิต</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">ภาษีหัก ณ ที่จ่าย (บาท)</label>
                <?= Html::textInput('FinancePayable[withholding_tax_amount]', number_format((float) $model->withholding_tax_amount, 2, '.', ''), [
                    'class' => 'form-control text-end' . ($model->hasErrors('withholding_tax_amount') ? ' is-invalid' : ''), 'inputmode' => 'decimal',
                ]) ?>
                <?= $err('withholding_tax_amount') ?>
                <div class="form-text">ยอดจ่ายสุทธิ = ยอดหนี้ − ภาษีหัก ณ ที่จ่าย</div>
            </div>
            <div class="col-12">
                <label class="form-label">หมายเหตุ</label>
                <?= Html::textarea('FinancePayable[note]', $model->note, ['class' => 'form-control', 'rows' => 2]) ?>
            </div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
            <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">ยกเลิก</a>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
