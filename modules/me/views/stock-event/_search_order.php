<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\WarehouseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['/me/stock-event/reuqest-order'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<div class="row">
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุสิ่งที่ต้องการค้นหา..'])->label(false) ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?php
        echo $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
            'data' => $model->listWareHouseMain(),
            'options' => ['placeholder' => 'คลังทั้งหมด ...'],
            'pluginOptions' => ['allowClear' => true],
        ])->label(false);
        ?>

    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
        <?php
        echo $form->field($model, 'order_status')->widget(Select2::classname(), [
            'data' => ['pending' => 'รอดำเนินการ', 'success' => 'สำเร็จ'],
            'options' => ['placeholder' => 'สถานะทั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>

    <div class="col-1">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>