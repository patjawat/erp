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

#stock-qty {
    height: 110px !important;
    font-size: 100px !important;
}
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-order-item',
    // 'type' => ActiveForm::TYPE_HORIZONTAL,
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/inventory/receive/validator']
]); ?>

<div class="row">
    <div class="col-8">
        <div class="card border border-primary">
            <div class="card-body">
                <?=$model->orderItem->product->AvatarXl()?>
            </div>
        </div>
</div>
<div class="col-4">    
    <?= $form->field($model, 'data_json[auto_lot]')->checkbox(['custom' => true, 'switch' => true])->label('ล็อตอันโนมัติ');?>
    <?= $form->field($model, 'lot_number')->textInput()->label(false); ?>
</div>
</div>
<div class="ms-5">
    <?php // $form->field($model, 'data_json[item_type]')->radioList(['จัดซื้อ' => 'จัดซื้อ','ยอดยกมา' => 'ยอดยกมา','ของแถม' => 'ของแถม','ของบริจาค' => 'ของบริจาค'], ['inline'=>true,'custom' => true])->label('ประเภท');?>
</div>
        <div class="row">
            <div class="col-6">
            <?php
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
           <?= $form->field($model, 'qty')->textInput(['type' => 'number', 'maxlength' => 2])->label('จำนวนรับเข้า'); ?>

            </div>
      
         
 
        </div>



<?= $form->field($model, 'qty_check')->textInput()->label(false) ?>
<?= $form->field($model, 'name')->textInput()->label(false) ?>
<?= $form->field($model, 'category_id')->textInput()->label(false); ?>
<?= $form->field($model, 'po_number')->textInput()->label(false); ?>
<?= $form->field($model, 'to_warehouse_id')->textInput()->label(false); ?>
<?= $form->field($model, 'data_json[product_name]')->textInput(['value' => $model->orderItem->product->title])->label(false); ?>
<?= $form->field($model, 'data_json[unit]')->textInput(['value' => isset($model->orderItem->product->data_json['unut']) ? $model->orderItem->product->data_json['unut'] : null])->label(false); ?>
<?= $form->field($model, 'data_json[product_type_name]')->textInput(['value' => $model->orderItem->product->productType->title])->label(false); ?>

<?= $form->field($model, 'data_json[po_qty]')->textInput()->label(false); ?>

<?= $form->field($model, 'asset_item')->textInput()->label('asset_item'); ?>
<?= $form->field($model, 'movement_type')->textInput()->label(false); ?>

<div class="d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary shadow rounded-pill', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

    $('#Stock-qty').keyup(function (e) { 
        
    if (e.keyCode === 8) { // Check if the key pressed is Backspace
        // Your code here
        // $('#Stock-data_json-po_qty').val();
        var qty = $('#Stock-data_json-po_qty').val();
        $('#Stock-qty_check').val(qty)
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