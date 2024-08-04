<?php

use app\models\Categorise;
use app\modules\inventory\models\Warehouse;
use app\modules\purchase\models\Order;
// use kartik\date\DatePicker;
use kartik\datecontrol\DateControl;
use iamsaint\datetimepicker\Datetimepicker;

use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use kato\AirDatepicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use DateTime;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
// $listOpOrder = ArrayHelper::map(Order::find()->where(['name' => 'order'])->all(), 'id', 'po_number');
$listOpOrder = ArrayHelper::map(Categorise::find()->all(), 'id', 'title');
$receive_type_name = $model->receive_type == 'receive' ? 'รับเข้าปกติ' : 'รับจากใบใบสั่งซื้อ';
?>
<style>
.col-form-label {
    text-align: end;
}
</style>


    <?php $form = ActiveForm::begin([
        'id' => 'form-rc',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/inventory/receive/create-validator']

    ]); ?>

    <div class="row">
        <div class="col-12">
        <?=$form->field($model, 'data_json[to_stock_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันรับเข้าคลัง');
                ?>
           
           <?=$form->field($model, 'data_json[checked_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('วันที่กรรมการคลังตรวจรับ');
                ?>
           
        
        </div>
        <div class="col-6">
            <div class="mb-3 highlight-addon has-success">
                <label class="form-label has-star">วิธีรับเข้า</label>
                <input type="text" class="form-control" value="<?= $receive_type_name ?>" disabled="true">
            </div>

            <?php
                // echo $form->field($model, 'to_warehouse_id')->widget(Select2::classname(), [
                //     'data' => ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name'),
                //     'options' => ['placeholder' => 'กรุณาเลือก'],
                //     'pluginOptions' => [
                //         'allowClear' => true,
                //         'dropdownParent' => '#main-modal',
                //     ],
                // ])->label('คลัง');
            ?>
        </div>
    </div>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'po_number')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'receive_type')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'to_warehouse_id')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>


    <?php
        $ref = $model->ref;
        $js = <<< JS

                            \$('#form-rc').on('beforeSubmit', function (e) {
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
                
            
                $("#stock-data_json-to_stock_date").datetimepicker({
                    timepicker:false,
                    format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
                    lang:'th',  // แสดงภาษาไทย
                    onChangeMonth:thaiYear,          
                    onShow:thaiYear,                  
                    yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
                    closeOnDateSelect:true,
                }); 

                $("#stock-data_json-checked_date").datetimepicker({
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