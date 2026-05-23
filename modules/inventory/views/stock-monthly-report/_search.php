<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\StockMonthlyReportSearch $model */
/** @var array $monthOptions */
/** @var array $yearOptions */
/** @var array $warehouseOptions */
/** @var array $assetTypeOptions */
?>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'options' => ['data-pjax' => 0],
]); ?>

<div class="row g-2">
    <div class="col-12 col-md-3">
        <?= $form->field($model, 'report_year')->widget(Select2::class, [
            'data' => $yearOptions,
            'options' => ['placeholder' => 'ปี ค.ศ.'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
    <div class="col-12 col-md-3">
        <?= $form->field($model, 'report_month')->widget(Select2::class, [
            'data' => $monthOptions,
            'options' => ['placeholder' => 'เดือน'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
    <div class="col-12 col-md-6">
        <?= $form->field($model, 'warehouse_id')->widget(Select2::class, [
            'data' => $warehouseOptions,
            'options' => ['placeholder' => 'ทุกคลังหลัก'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
    <div class="col-12 col-md-6">
        <?= $form->field($model, 'category_id')->widget(Select2::class, [
            'data' => $assetTypeOptions,
            'options' => ['placeholder' => 'ทุกประเภทวัสดุ'],
            'pluginOptions' => ['allowClear' => true],
        ])->label('ประเภทวัสดุ') ?>
    </div>
    <div class="col-12 col-md-3">
        <?= $form->field($model, 'q')->textInput([
            'placeholder' => 'ค้นหารหัสพัสดุ/ชื่อรายการ',
        ])->label('ค้นหา') ?>
    </div>
    <div class="col-12 col-md-3 d-flex align-items-end">
        <div class="d-flex gap-2 w-100">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', [
                'class' => 'btn btn-primary flex-grow-1',
            ]) ?>
            <?= Html::a('<i class="fa-solid fa-rotate"></i>', ['index'], [
                'class' => 'btn btn-outline-secondary',
                'title' => 'ล้างตัวกรอง',
            ]) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
