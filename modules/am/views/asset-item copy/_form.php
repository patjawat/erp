<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-item-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
        'validationUrl' =>['/am/asset-item/validator']
    ]); ?>


    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?php // echo $form->field($model, 'group_id')->textInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>


  <?php
  echo $form->field($model, 'group_id')->widget(Select2::classname(), [
    'data' => $model->listAssetType(),
        'options' => [
        'placeholder' => 'เลือกประเภท...',
        'id' => 'group_id'
    ],
        'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
    ],
])->label('ประเภท');
?>
<?php
echo $form->field($model, 'category_id')->widget(DepDrop::classname(), [
    'options' => ['placeholder' => 'เลือกหมวดรัพย์สิน ...'],
     'data' => $model->listAssetCategory(),
    'type' => DepDrop::TYPE_SELECT2,
    'select2Options' => ['pluginOptions' => ['allowClear' => true]],
    'pluginOptions' => [
        'depends' => ['group_id'],
        'url' => Url::to(['/am/asset-item/get-asset-category']),
        'loadingText' => 'กำลังโหลด ...',
        'params' => ['depdrop_all_params' => 'assetitem-asset_type_id'],
        'initDepends' => ['assetitem-asset_type_id'],
        'initialize' => true,

    ]
])->label('หมวดหมู่');
?>
    <?= $form->field($model, 'title')->textarea(['rows' => 2]) ?>

    
    <?= $form->field($model, 'description')->textarea(['rows' => 3])->label('คำอธิบายเพิ่มเติม') ?>
    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[fsn]')->textInput(['maxlength' => true])->label('FSN') ?>
            <?= $form->field($model, 'data_json[price]')->textInput(['maxlength' => true])->label('ราคา') ?>

        </div>
        <div class="col-6">
            <?= $form->field($model, 'data_json[depreciation]')->textInput(['maxlength' => true])->label('ค่าเสื่อมราคา') ?>
            <?= $form->field($model, 'data_json[service_life]')->textInput()->label('อายุการใช้งาน') ?>
        </div>

    </div>
    
 <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS

// เรียกใช้ function handleFormSubmit
handleFormSubmit('#form', null, async function(response) {
    await location.reload();
});    
JS;
$this->registerJs($js,View::POS_END);
?>