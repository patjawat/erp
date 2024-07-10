<?php
use app\models\Categorise;
use app\modules\inventory\models\warehouse;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockMovement $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-movement-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-6">
    <?php
        echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(warehouse::find()->all(), 'id', 'warehouse_name'),
            'options' => ['placeholder' => 'กรุณาเลือก'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])->label('เบิกจากคลัง');
    ?>

         </div>
         <div class="col-6">
         <?php
echo $form->field($model, 'to_warehouse_id')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(warehouse::find()->all(), 'id', 'warehouse_name'),
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
    ],
])->label('เข้าคลัง');
?>
         </div>
    </div>


<?php
echo $form
    ->field($model, 'data_json[due_date]')
    ->widget(DateControl::classname(), [
        'type' => DateControl::FORMAT_DATE,
        'language' => 'th',
        'widgetOptions' => [
            'options' => ['placeholder' => 'ระบุวันที่ต้องการ ...'],
            'pluginOptions' => [
                'autoclose' => true
            ]
        ]
    ])
    ->label('วันที่ต้องการ');
?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'movement_type')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
