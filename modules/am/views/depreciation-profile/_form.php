<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\am\models\DepreciationProfile;

/** @var yii\web\View $this */
/** @var app\modules\am\models\DepreciationProfile $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="card">
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row g-3">
            <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?></div>
            <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>

            <div class="col-md-4"><?= $form->field($model, 'method')->dropDownList(DepreciationProfile::methodOptions()) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'useful_life_months')->textInput(['type' => 'number', 'min' => 1]) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'annual_rate')->textInput(['type' => 'number', 'step' => '0.0001'])->hint('เว้นว่างได้ ถ้าคิดจากอายุการใช้งาน') ?></div>

            <div class="col-md-4"><?= $form->field($model, 'salvage_value_type')->dropDownList(DepreciationProfile::salvageTypeOptions()) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'salvage_value')->textInput(['type' => 'number', 'step' => '0.0001'])->hint('จำนวนเงิน หรือ % ตามชนิดที่เลือก') ?></div>
            <div class="col-md-4"><?= $form->field($model, 'rounding_scale')->textInput(['type' => 'number', 'min' => 0, 'max' => 6]) ?></div>

            <div class="col-md-4"><?= $form->field($model, 'calculation_basis')->dropDownList(DepreciationProfile::basisOptions()) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'start_rule')->dropDownList(DepreciationProfile::startRuleOptions()) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'status')->dropDownList(DepreciationProfile::statusOptions()) ?></div>

            <div class="col-md-6"><?= $form->field($model, 'effective_from')->input('date') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'effective_to')->input('date') ?></div>
        </div>

        <div class="mt-3">
            <?= Html::submitButton('<i data-lucide="save"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
