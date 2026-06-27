<?php
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\modules\inventoryV2\models\WarehouseSearch $model */
/** @var array $departmentOptions */
?>
<div class="warehouse-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/inventory-v2/default/setting'],
        'method' => 'get',
        'options' => ['class' => 'warehouse-search-form'],
    ]); ?>
    <div class="warehouse-filter warehouse-filter-name">
        <?= $form->field($model, 'warehouse_name')->textInput([
            'placeholder' => 'ค้นหาชื่อคลัง...',
            'class' => 'form-control form-control-sm',
        ])->label(false) ?>
    </div>
    <div class="warehouse-filter warehouse-filter-officer">
        <?= $form->field($model, 'officer_name')->textInput([
            'placeholder' => 'ค้นหาชื่อเจ้าหน้าที่รับผิดชอบ...',
            'class' => 'form-control form-control-sm',
        ])->label(false) ?>
    </div>
    <div class="warehouse-filter warehouse-filter-department">
        <?= $form->field($model, 'department_id')->widget(Select2::className(), [
            'data' => $departmentOptions ?? [],
            'options' => [
                'placeholder' => 'ค้นหาแผนก/ฝ่ายตามโครงสร้าง...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '100%',
            ],
        ])->label(false) ?>
    </div>
    <div class="warehouse-filter warehouse-filter-type">
        <?= $form->field($model, 'warehouse_type')->dropDownList([
            '' => 'ทุกประเภท',
            'MAIN' => 'คลังหลัก',
            'SUB' => 'คลังย่อย',
            'BRANCH' => 'รพ.สต.',
        ], ['class' => 'form-select form-select-sm'])->label(false) ?>
    </div>
    <div class="warehouse-filter-actions">
        <?= Html::submitButton('<i class="bi bi-search"></i> ค้นหา', ['class' => 'btn btn-primary btn-sm']) ?>
        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> ล้าง', ['/inventory-v2/default/setting'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
