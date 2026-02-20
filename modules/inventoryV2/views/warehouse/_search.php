<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\modules\inventoryV2\models\WarehouseSearch $model */
?>
<?php $form = ActiveForm::begin([
    'action' => ['/inventory-v2/warehouse/index'],
    'method' => 'get',
    'options' => ['data-pjax' => 1, 'class' => 'row g-2 align-items-end'],
]); ?>
<div class="col-lg-5 col-md-7 col-sm-12">
    <?= $form->field($model, 'warehouse_name')->textInput(['placeholder' => 'ระบุชื่อคลังที่ต้องการค้นหา...'])->label(false) ?>
</div>
<div class="col-lg-4 col-md-4 col-sm-12">
    <?= $form->field($model, 'warehouse_type')->dropDownList([
        '' => 'ประเภทคลังทั้งหมด',
        'MAIN' => 'คลังหลัก',
        'SUB' => 'คลังย่อย',
        'BRANCH' => 'รพ.สต.',
    ], ['class' => 'form-select'])->label(false) ?>
</div>
<div class="col-auto">
    <?= Html::submitButton('<i class="bi bi-search"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>
