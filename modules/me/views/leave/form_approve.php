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
    'id' => 'form',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/leave/approve-validator'],
])
?>
<?php  echo $form->field($model, 'status')->hiddenInput()->label(false)?>
<?php  echo $model->status == 'Reject' ?  $form->field($model, 'data_json[note]')->textArea()->label(false) : ''?>

    <div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>



<?php ActiveForm::end(); ?>


<?php
$js = <<< JS

        \$('#form').on('beforeSubmit', function (e) {
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
                            location.reload()
                            // try {
                            //     loadRepairHostory()
                            // } catch (error) {
                                
                            // }
                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


    JS;
$this->registerJS($js)
?>