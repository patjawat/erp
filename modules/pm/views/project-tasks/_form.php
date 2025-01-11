<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectTasks $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="project-tasks-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-task',
    ]); ?>

    <?= $form->field($model, 'level')->textInput() ?>
    <?= $form->field($model, 'project_id')->textInput() ?>

    <?= $form->field($model, 'emp_id')->textInput() ?>

    <?= $form->field($model, 'task_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

$js = <<< JS

    \$('#form-task').on('beforeSubmit', function (e) {
        e.preventDefault();
            var form = $(this);
            console.log('Submit');
            Swal.fire({
            title: "ยืนยัน?",
            text: "บันทึกหนังสือ!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "ยกเลิก!",
            confirmButtonText: "ใช่, ยืนยัน!"
            }).then((result) => {
            if (result.isConfirmed) {
                
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            closeModal()
                            // success()
                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });

            }else{

            }
            });
            return false;
        });
         
JS;
$this->registerJS($js,View::POS_END);
?>