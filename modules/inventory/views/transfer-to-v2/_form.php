<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $model */
/** @var array $listWarehouses [id => name] */
/** @var string $mainWarehouseId */
?>

<?php $form = ActiveForm::begin([
    'action' => ['/inventory/transfer-to-v2/index'],
    'method' => 'get',
    'options' => ['data-pjax' => 1],
]); ?>

<div class="row align-items-end g-2">
    <div class="col-md-2">
        <?= $form->field($model, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, [
            'options' => ['placeholder' => 'เริ่มจากวันที่', 'class' => 'form-control'],
        ])->label(false) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, [
            'options' => ['placeholder' => 'ถึงวันที่', 'class' => 'form-control'],
        ])->label(false) ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'asset_type_id')->widget(Select2::class, [
            'data' => $model->ListAssetType(),
            'options' => ['placeholder' => 'เลือกประเภทวัสดุ (บังคับ)', 'class' => 'form-control'],
            'pluginOptions' => ['allowClear' => false],
        ])->label(false) ?>
    </div>
    <div class="col-md-3">
        <?= Html::dropDownList('main_warehouse_id', $mainWarehouseId, ['' => '-- เลือกคลัง V2 --'] + $listWarehouses, [
            'class' => 'form-select',
            'required' => true,
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= Html::submitButton('<i class="fa-solid fa-list me-1"></i> ดูรายการ', ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
