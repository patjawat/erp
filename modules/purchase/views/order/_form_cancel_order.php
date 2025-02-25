<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\DatePicker;
use app\modules\am\models\Asset;
use kartik\widgets\DateTimePicker;
use kartik\datecontrol\DateControl;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>
<style>
.col-form-label {
    text-align: end;
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-cancel_order_note',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/order/cancel-order-validator'],
]); ?>

<!-- ชื่อของประเภท -->

  <?= $form->field($model, 'data_json[cancel_order_note]')->textArea()->label('ระบุเหตุผล') ?>
  <?= $form->field($model, 'status')->hiddenInput(['value' => 7])->label(false) ?>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>



<?php
$js = <<< JS

        \$('#form-cancel_order_note').on('beforeSubmit', function (e) {
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
                            try {
                                // loadRepairHostory()
                            } catch (error) {
                                
                            }
                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


    JS;
$this->registerJS($js)
?>