<?php

use yii\helpers\Html;
use kartik\form\ActiveForm; // or kartik\widgets\ActiveForm


/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-detail-form">

    <?php 
    $form = ActiveForm::begin([
        'id' => 'form-asset-detail',
        'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
        'validationUrl' =>['/am/asset-detail/validator']
    ]); 
?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

    <?php echo $this->render($model->name.'/_form',['model' => $model,'form' => $form])?>
<?php substr($model->code, 0, strpos($model->code, '/')) ?>
    
<?php if($model->name == "ma" && isset($model->asset->assetItem->ma_items)):?>
    <div class="form-group d-flex justify-content-center">
        <?= app\components\AppHelper::BtnSave() ?>
    </div>
<?php endif?>
    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<< JS


$('#loadMa').click(function (e) { 
    e.preventDefault();
    loadMa();
});
$('#form-asset-detail').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                console.log(response.data);
                closeModal()
                success()
                loadMa();
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});
JS;
$this->registerJs($js);
?>