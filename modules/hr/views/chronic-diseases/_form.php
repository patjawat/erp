<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/hr/chronic-diseases/validator']
    ]); ?>

    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'title')->textInput() ?>

    <?php ActiveForm::end(); ?>

<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>