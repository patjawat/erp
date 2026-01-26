<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentOrg $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="document-org-form">

   <?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/dms/document-org/validator']
    ]); ?>


    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'code')->textInput()->label('รหัสชื่อหน่วยงาน') ?>
    <?= $form->field($model, 'title')->textInput()->label('ชื่อหน่วยงาน') ?>
    <?= $form->field($model, 'data_json[url]')->textInput()->label('WebHook URL') ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>