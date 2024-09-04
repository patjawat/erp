<?php
use app\models\Categorise;
use app\modules\inventory\models\warehouse;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\components\AsseteHelper;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="stock-movement-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-12">
        <?php
echo $form->field($model, 'data_json[asset_type]')->widget(Select2::classname(), [
    'data' => $model->ListAssetType(),
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
    ],
])->label('ประเภท');
?>
    <?php
        // echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
        //     'data' => ArrayHelper::map(warehouse::find()->all(), 'id', 'warehouse_name'),
        //     'options' => ['placeholder' => 'กรุณาเลือก'],
        //     'pluginOptions' => [
        //         'allowClear' => true,
        //         'dropdownParent' => '#main-modal',
        //     ],
        // ])->label('เบิกจากคลัง');
    ?>

         </div>
         <div class="col-12">

         </div>
    </div>


    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'movement_type')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
