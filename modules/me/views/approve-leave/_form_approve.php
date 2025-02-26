<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-reject',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/leave/approve-validator'],
])
?>
        <?php  echo $form->field($model, 'data_json[note]')->textArea()->label(false)?>
        <?php  echo $form->field($model, 'status')->hiddenInput()->label(false)?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

        \$('#form-reject').on('beforeSubmit', function (e) {
                var form = \$(this);
                Swal.fire({
                title: 'ยืนยัน?',
                text: topic+"ใช่หรือไม!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่',
                cancelButtonText: 'ยกเลิก'
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
                            console.log(response);
                            
                            // closeModal()
                            // success()
                            // location.reload()                            
                                  
                        }
                    });
                }
            })
            
     }
        

    JS;
$this->registerJS($js)
?>