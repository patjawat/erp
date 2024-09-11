<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveTypes $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="leave-types-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'max_days')->textInput(['maxlength' => true]) ?>

    
    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<< JS

    // \$('#boardId').val(8).trigger('change');
    $('#form').on('beforeSubmit',  function (e) {
        var form = \$(this);
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success:  function (response) {
                form.yiiActiveForm('updateMessages', response, true);
                if(response.status == 'success') {
                    $("#main-modal").modal("hide");
                    success()
                      $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});  
                }
            }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END)
?>