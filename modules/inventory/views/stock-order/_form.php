<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="stock-in-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/inventory/stock-in/create-validator']
    ]); ?>

    <?php if ($model->name == 'order'): ?>
        <?= $this->render('_form_order', ['form' => $form, 'model' => $model]) ?>
    <?php endif; ?>

    <?php if ($model->name == 'order_item'): ?>
        <?= $this->render('_form_order_item', ['form' => $form, 'model' => $model]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'name')->hiddenInput()->label(false);?>
    <?= $form->field($model, 'data_json[checker_confirm]')->hiddenInput()->label(false);?>
    <?= $model->isNewRecord ? $form->field($model, 'category_id')->hiddenInput()->label(false) : null;?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS


    thaiDatepicker('#stockevent-created_at')

    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
    
JS;
$this->registerJS($js)
?>