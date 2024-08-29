<?php

use app\modules\am\models\Asset;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveField;
use kartik\form\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
// use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
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

#order-data_json-qty {
    height: 110px !important;
    font-size: 100px !important;
}

#stock-data_json-qty {
    height: 110px !important;
    font-size: 100px !important;
}

</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-order-item',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/inventory/receive/add-item-validator']
]); ?>
Form PO
<div class="row">
    <div class="col-8">
        <div class="card border border-primary">
            <div class="card-body">
                <?php  echo $model->product->AvatarXl()?>
            </div>
        </div>
    </div>
    <div class="col-4">
        <?= $form->field($model, 'auto_lot')->checkbox(['custom' => true, 'switch' => true,'checked' => true])->label('ล็อตอัตโนมัติ');?>
        <?= $form->field($model, 'data_json[lot_number]')->textInput()->label(false); ?>
    </div>
</div>
<div class="row">
    <div class="col-6">
<?=$form->field($model, 'data_json[mfg_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันผลิต');
                ?>

            <?=$form->field($model, 'data_json[exp_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันหมดอายุ');
                ?>

    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[qty]')->textInput(['type' => 'number', 'maxlength' => 2])->label('จำนวนรับเข้า'); ?>

    </div>
</div>

<?= $form->field($model, 'qty')->hiddenInput()->label(false) ?>
<div class="d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary shadow rounded-pill', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

    console.log($("#order-auto_lot").val())
    if($("#order-auto_lot").val()){
    $( "#order-auto_lot" ).prop( "checked", localStorage.getItem('lot_auto') == 1 ? true : false );
    $('#order-data_json-lot_number').prop('disabled',localStorage.getItem('lot_auto') == 1 ? true : false );

    if(localStorage.getItem('fsn_auto') == true)
    {
        $('#order-data_json-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')
    }

    }

    $("#order-auto_lot").change(function() {
        //ตั้งค่า Run Lot Auto
        if(this.checked) {
            console.log('lot_auto');
            localStorage.setItem('lot_auto',1);
            $('#order-data_json-lot_number').prop('disabled',this.checked);
            $('#order-data_json-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')

            $('#stock-data_json-lot_number').prop('disabled',this.checked);
            $('#stock-data_json-lot_number').val('สร้างล็อตผลิตอัตโนมัติ')

        }else{
            localStorage.setItem('lot_auto',0);
            $('#order-data_json-lot_number').prop('disabled',this.checked);
            $('#order-data_json-lot_number').val('')

            $('#stock-data_json-lot_number').prop('disabled',this.checked);
            $('#stock-data_json-lot_number').val('')

            console.log('lot_manual');
        }
    });

    $('#order-qty').keyup(function (e) { 
        
    if (e.keyCode === 8) { // Check if the key pressed is Backspace
        // Your code here
        // $('#order-data_json-po_qty').val();
        var qty = $('#order-data_json-po_qty').val();
        $('#order-qty_check').val(qty)
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



            var thaiYear = function (ct) {
        var leap=3;  
        var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];  
        if(ct){  
            var yearL=new Date(ct).getFullYear()-543;  
            leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;  
            if(leap==2){  
                dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];  
            }  
        }              
        this.setOptions({  
            i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,  
        })                
    };    
     
   
    $("#order-data_json-mfg_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    }); 
    
    $("#order-data_json-exp_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });   


    JS;
$this->registerJS($js)
?>