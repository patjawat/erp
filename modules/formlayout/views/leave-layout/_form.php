<?php

use yii\helpers\Html;
use app\models\Categorise;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
$leaveType = ArrayHelper::map(Categorise::find()->where(['name' => 'leave_type'])->all(),'code','title');
/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="categorise-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

    <?=$form->field($model, 'category_id')->widget(Select2::classname(), [
    'data' => $leaveType,
    'options' => [
        'placeholder' => 'เลือกประเภท...',
    ],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
    ],
    'pluginEvents'=> [
    "change" => "function() { 
      var selectedText = 'แบบฟอร์ม'+$(this).find('option:selected').text();
      $('#categorise-title').val(selectedText);
    }",
]
])->label('ประเภท');

?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_json')->textInput() ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>