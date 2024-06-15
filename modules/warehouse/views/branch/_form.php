<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Store $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="store-form">

<?php $form = ActiveForm::begin(['id' => 'form-branch']); ?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <div class="row">
<div class="col-4">
    <?= $form->field($model, 'code')->textInput()->label('รหัสคลัง') ?>
</div>
<div class="col-8">
    <?= $form->field($model, 'title')->textInput()->label('ชื่อคคลัง') ?>
</div>
    </div>
    <?= $form->field($model, 'active')->checkbox(['custom' => true])->label('สถานะใช้งาน') ?>


    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>
<?php ActiveForm::end(); ?>


<?php
$ref = $model->ref;
$js = <<< JS

                    \$('#form-branch').on('beforeSubmit', function (e) {
                        var form = \$(this);
                        \$.ajax({
                            url: form.attr('action'),
                            type: 'post',
                            data: form.serialize(),
                            dataType: 'json',
                            success: async function (response) {
                                form.yiiActiveForm('updateMessages', response, true);
                                if(response.status == 'success') {
                                    closeModal()
                                    success()
                                    await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                }
                            }
                        });
                        return false;
                    });
    JS;
$this->registerJS($js)
?>