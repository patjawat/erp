<?php

use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm; // or kartik\widgets\ActiveForm
use app\components\AppHelper;
use yii\web\View;
/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
    .modal-footer {
        display:none !important;
    }
</style>
<div class="employee-detail-form">

<?php 
    $form = ActiveForm::begin([
        'id' => 'form-emp-detail',
        'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
        'validationUrl' =>['/hr/employee-detail/validator']
    ]); 
?>

    <?php
    //  $form = ActiveForm::begin([
    //     'id' => 'form-emp-detail',
    //     'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
    //     'validationUrl' =>['/hr/employee-detail/validator']
    //     ]);
         ?>

<?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false)?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false)?>

    <?=$this->render($model->name,['form'=> $form,'model'=> $model]);?>


    <div class="form-group mt-4 d-flex justify-content-center">
        <?= AppHelper::BtnSave(); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
// $urlUpload = Url::to('/patient/upload');
$js = <<<JS


 $('#form-emp-detail').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (res) {
            form.yiiActiveForm('updateMessages', res, true);
            if (form.find('.invalid-feedback').length) {
                // validation failed
            } else {
                // validation succeeded
            }
            if(res.status == 'success') {
                // alert(data.status)ห
                console.log(res.container);
                $('#main-modal').modal('toggle');
                success()
                 $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});                                                        
            }
        }
    });
    return false;
});
JS;
$this->registerJS($js, View::POS_END)
    ?>
