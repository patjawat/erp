<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<div class="asset-detail-form">

    <?php $form = ActiveForm::begin(['id' => 'form-asset-detail']); ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

    <?= $this->render($model->name.'/_form',['model' => $model,'form' => $form])?>
<?php substr($model->code, 0, strpos($model->code, '/')) ?>
    
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php Pjax::end();?>

<?php

$js = <<< JS


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
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});
JS;
$this->registerJs($js);
?>