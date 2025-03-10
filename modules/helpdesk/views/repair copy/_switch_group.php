<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="repair-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-group',
    ]); ?>
    <div class="row">
        <div class="d-flex justify-content-center">
            <?= $form->field($model, 'repair_group')->radioList($model->listRepairGroup(),['inline'=>false,'custom' => true])->label('<i class="fa-regular fa-paper-plane"></i> แจ้งไปยัง') ?>
        </div>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
 
$('#form-group').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                success()
                try {
                    loadRepairHostory()
                } catch (error) {
                    
                }
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js)
?>