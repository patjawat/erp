<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Employees $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
    .card {
    border: 1px solid #ededed;
    margin-bottom: 30px;
    -webkit-box-shadow: 0 1px 10px rgba(0, 0, 0, 0.2);
    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    box-shadow: 0 1px 4px 0px rgb(126 114 114 / 7%);
}
</style>
<div class="card rounded-4 border-0">
    <div class="card-body">

<?php $form = ActiveForm::begin([
    'id' => 'form-employee',
    'type' => ActiveForm::TYPE_FLOATING
]); ?>

<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => 50])->label(false); ?>
<?= $form->field($model, 'user_id')->hiddenInput(['value' => 0])->label(false) ?>
<?= $this->render('_form_general', ['form' => $form, 'model' => $model]) ?>


<ul class="nav nav-tabs nav-tabs-solid nav-tabs-rounded">
    <li class="nav-item"><a class="nav-link active" href="#solid-rounded-tab1" data-bs-toggle="tab"><i
                class="fa-solid fa-file-circle-check"></i> ข้อมูลทั่วไป</a></li>
    <li class="nav-item"><a class="nav-link" href="#solid-rounded-tab2" data-bs-toggle="tab"><i
                class="fa-regular fa-id-card"></i> ข้อมูลเกี่ยวกับการทำงาน</a></li>

</ul>



<div class="tab-content">
    <div class="tab-pane show active" id="solid-rounded-tab1">



    </div>
    <div class="tab-pane" id="solid-rounded-tab2">
        <?php //  $this->render('_form_work', ['form' => $form, 'model' => $model]) ?>

    </div>

</div>


<div class="d-flex justify-content-center">
    <?= SiteHelper::btnSave() ?>
</div>

<?php ActiveForm::end(); ?>



    </div>
</div>

<?php
$urlUpload = Url::to('/patient/upload');
$js = <<<JS



 $('#form-employee').on('beforeSubmit', function (e) {
    var form = $(this);
    console.log('Submit');
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                // success()
                await  $.pjax.reload({ container:"#employee-container", history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js, View::POS_END)
    ?>
