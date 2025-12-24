<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */
// $formWarehouse = Yii::$app->session->get('selectMainWarehouse');

$cart = \Yii::$app->cartMain;
$products = $cart->getItems();
$assetType = \Yii::$app->session->get('asset_type');

?>
<?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/main-stock/create-validator'],
]); ?>

<?php echo $this->render('_form_order', ['form' => $form, 'model' => $model]); ?>

<?php echo $form->field($model, 'name')->hiddenInput()->label(false); ?>
<?php echo $form->field($model, 'data_json[checker_confirm]')->hiddenInput()->label(false); ?>
<?php if ($model->isNewRecord): ?>
    <?php echo $form->field($model, 'asset_type_id')->hiddenInput(['value' => $assetType->code ?? ''])->label(false); ?>
    <?php echo $form->field($model, 'data_json[asset_type_name]')->hiddenInput(['value' => $assetType->title ?? ''])->label(false); ?>
<?php endif; ?>
<?php echo $model->isNewRecord ? $form->field($model, 'category_id')->hiddenInput()->label(false) : null; ?>

<div class="text-center">
    <?php if ($cart->getCount() == 0): ?>
        <button type="button" class="btn btn-primary" disabled><i class="fa-solid fa-cart-shopping"></i> เบิก</button>
    <?php else: ?>
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']); ?>
    <?php endif ?>
</div>

<?php ActiveForm::end(); ?>

<div id="viewMainCart"></div>

<?php
$viewMainCartUrl = Url::to(['/me/main-stock/view-cart']);
$js = <<< JS


ViewMainCar();
async function ViewMainCar()
    {
    await $.ajax({
        type: "get",
        url: "$viewMainCartUrl",
        dataType: "json",
        success: function (res) {
            $('#viewMainCart').html(res.content)
            $('.countMainItem').html(res.countItem)
            if(res.countItem < 1){
                $("#main-modal").modal("hide");

            }
            console.log(res.countItem);
        }
    });
    }

    handleFormSubmit('#form', null, async function(response) {
        if (response.redirect_url) {
        window.location.href = response.redirect_url;
    } else {
        location.reload();
    }
    });
    
JS;
$this->registerJS($js);
?>