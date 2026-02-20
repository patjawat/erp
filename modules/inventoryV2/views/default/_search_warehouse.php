<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\modules\inventoryV2\models\WarehouseSearch $model */
?>
<div class="warehouse-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/inventory-v2/default/setting'],
        'method' => 'get',
        'options' => ['class' => 'row g-2 align-items-end'],
    ]); ?>
    <div class="col-md-4 col-lg-3">
        <?= $form->field($model, 'warehouse_name')->textInput([
            'placeholder' => 'ค้นหาชื่อคลัง...',
            'class' => 'form-control form-control-sm',
        ])->label(false) ?>
    </div>
    <div class="col-md-3 col-lg-2">
        <?= $form->field($model, 'warehouse_type')->dropDownList([
            '' => 'ทุกประเภท',
            'MAIN' => 'คลังหลัก',
            'SUB' => 'คลังย่อย',
            'BRANCH' => 'รพ.สต.',
        ], ['class' => 'form-select form-select-sm'])->label(false) ?>
    </div>
    <div class="col-auto">
        <?= Html::submitButton('<i class="bi bi-search"></i> ค้นหา', ['class' => 'btn btn-primary btn-sm']) ?>
        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> ล้าง', ['/inventory-v2/default/setting'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
