<?php

use app\modules\am\models\Asset;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveField;
use kartik\form\ActiveForm;
// use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\DateTimePicker;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<style>
.modal-body {
    /* padding: 0 !important */
}

#stockmovement-qty {
    font-size: 45px;
    font-weight: 500;
}
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-order-item',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/inventory/receive/validator']
]); ?>

<?=$product->AvatarXl()?>
<hr>
<div class="row">
    <div class="col-6"> <?php
            echo $form
                ->field($model, 'data_json[mfg_date]')
                ->widget(DateControl::classname(), [
                    'type' => DateControl::FORMAT_DATE,
                    'language' => 'th',
                    'widgetOptions' => [
                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                        'pluginOptions' => [
                            'autoclose' => true
                        ]
                    ]
                ])
                ->label('วันผลิต');
        ?>

        <?php
            echo $form
                ->field($model, 'data_json[exp_date]')
                ->widget(DateControl::classname(), [
                    'type' => DateControl::FORMAT_DATE,
                    'language' => 'th',
                    'widgetOptions' => [
                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                        'pluginOptions' => [
                            'autoclose' => true
                        ]
                    ]
                ])
                ->label('วันหมดอายุ');
        ?>
     
    </div>
    <div class="col-6">
    <div class="mt-4 mb-4">
        <?= $form->field($model, 'data_json[auto_lot]')->checkbox(['custom' => true, 'switch' => true])->label('ล็อตอันโนมัติ');?>
    </div>
    <div style="margin-top: 28px;">
        <?= $form->field($model, 'lot_number')->textInput()->label('ล็อตผลิต'); ?>

    </div>
    </div>

</div>





<?= $form->field($model, 'qty')->textInput(['type' => 'number', 'maxlength' => 2])->label('จำนวนรับเข้า'); ?>


<?= $form->field($model, 'qty_check')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'category_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'po_number')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'to_warehouse_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[product_name]')->hiddenInput(['value' => $product->title])->label(false); ?>
<?= $form->field($model, 'data_json[unit]')->hiddenInput(['value' => isset($product->data_json['unut']) ? $product->data_json['unut'] : null])->label(false); ?>
<?= $form->field($model, 'data_json[product_type_name]')->hiddenInput(['value' => $product->productType->title])->label(false); ?>

<?= $form->field($model, 'data_json[po_qty]')->hiddenInput()->label(false); ?>

<?= $form->field($model, 'product_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'movement_type')->hiddenInput()->label(false); ?>

<div class="d-grid gap-2">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

    $('#stockmovement-qty').keyup(function (e) { 
        
    if (e.keyCode === 8) { // Check if the key pressed is Backspace
        // Your code here
        // $('#stockmovement-data_json-po_qty').val();
        var qty = $('#stockmovement-data_json-po_qty').val();
        $('#stockmovement-qty_check').val(qty)
    }
    });

        \$('#form-order-item').on('beforeSubmit', function (e) {
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