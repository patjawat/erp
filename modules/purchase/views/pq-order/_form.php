<?php

use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php
                            try {
                                $orderTypeName =  $model->data_json['order_type_name'];
                            } catch (\Throwable $th) {
                                $orderTypeName = '';
                            }
                        ?>
<?php Pjax::begin(['id' => 'purchase-container']); ?>
<style>
.col-form-label {
    text-align: end;
}
</style>


<div class="card">
    <div class="card-body">
        <h5><i class="fa-solid fa-circle-info text-primary"></i> ทะเบียนคุมขอซื้อ/ขอจ้าง :
            <?=$orderTypeName?></h5>
        <?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/pq-order/validator'],
]); ?>


        <div class="row mt-4">
            <div class="col-4">
                <div class="border border-secondary rounded p-4">
                    <h6 class="text-center"><i class="fa-solid fa-circle-info text-primary"></i> คำสั่ง</h6>
                    <div class="row">
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[order]')->textInput()->label('ตามคำสั่ง') ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'data_json[order_number]')->textInput()->label('เลขที่คำสั่ง') ?>
                        </div>
                        <div class="col-6">
                            <?=$form->field($model, 'data_json[order_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ลงวันที่');
                ?>
                        </div>
                    </div>
                </div>



                <div class="border border-secondary rounded p-4 mt-4">
                    <h6 class="text-center"><i class="fa-solid fa-circle-info text-primary"></i> แผนงานโครงการ
                    </h6>
                    <div class="row">
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[pq_project_name]')->textInput()->label('ชื่อโครงการ') ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'data_json[pq_project_id]')->textInput()->label('โครงการเลขที่') ?>
                            <?= $form->field($model, 'data_json[pq_egp_number]')->textInput()->label('รหัสอ้างอิง EGP') ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'data_json[pq_disbursement]')->textInput()->label('การเบิกจ่ายเงิน') ?>
                            <?= $form->field($model, 'data_json[pq_egp_report]')->textInput()->label('รายการแผน EGP') ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- End Col-6 -->
            <div class="col-8">
                <div class="border border-secondary rounded p-4">
                    <h6 class=" text-center"><i class="fa-solid fa-circle-info text-primary"></i>
                        วิธีการซื้อ/จ้าง</h6>
                    <div class="row">
                        <div class="col-6">
                            <?php
                                        echo $form->field($model, 'data_json[pq_purchase_type]')->widget(Select2::classname(), [
                                            'data' => $model->ListPurchase(),
                                            'options' => ['placeholder' => 'กรุณาเลือก'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                                // 'dropdownParent' => '#main-modal',
                                            ],
                                            'pluginEvents' => [
                                                'select2:select' => "function(result) { 
                                        var data = \$(this).select2('data')[0]
                                        \$('#order-data_json-pq_purchase_type_name').val(data.text)
                                        }",
                                            ]
                                        ])->label('วิธีซื้อหรือจ้าง');
                                    ?>




                        </div>

                        <div class="col-6">

                            <?php
                        $conditionUrl = Url::to(['/depdrop/categorise-by-code']);
                        echo $form->field($model, 'data_json[pq_condition]')->widget(Select2::classname(), [
                            'data' => $model->ListPurchaseCondition(),
                            'options' => ['placeholder' => 'กรุณาเลือก'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'dropdownParent' => '#main-modal',
                            ],
                            'pluginEvents' => [
                                'select2:select' => 'function(result) { 
                                                  $.ajax({
                                                    url: \'' . $conditionUrl . "',
                                                    type: 'get',
                                                    data: {name:'purchase_condition',code:\$(this).val()},
                                                    dataType: 'json',
                                                    success: async function (response) {
                                                        console.log(response)       
                                                         \$('#order-data_json-pq_condition_name').val(response.title)           
                                                         \$('#order-data_json-pq_income_reason').val(response.data_json.comment)           
                                                        }

                                                });
                                        }",
                            ]
                        ])->label('เงื่อนไข')
                    ?>


                        </div>

                        <div class="col-12">
                            <?= $form->field($model, 'data_json[pq_income_reason]')->textArea(['rows' => 5, 'style' => 'height: 106px;'])->label('เหตุผลการจัดหา') ?>
                        </div>
                        <div class="col-6">

                            <?php
                            echo $form->field($model, 'data_json[pq_method_get]')->widget(Select2::classname(), [
                                'data' => $model->ListMethodget(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    // 'dropdownParent' => '#main-modal',
                                ],
                                'pluginEvents' => [
                                    'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0]
                                            \$('#order-data_json-pq_method_get_name').val(data.text)
                                        }",
                                ]
                            ])->label('วิธีจัดหา');
                        ?>

                            <?=
                        $form->field($model, 'data_json[pq_budget_group]')->widget(Select2::classname(), [
                            'data' => $model->ListBudgetGroup(),
                            'options' => ['placeholder' => 'กรุณาเลือก'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'dropdownParent' => '#main-modal',
                            ],
                            'pluginEvents' => [
                                'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0]
                                            \$('#order-data_json-pq_budget_group_name').val(data.text)
                                        }",
                            ]
                        ])->label('หมวดเงิน');
                    ?>
                            <?=
                        $form->field($model, 'data_json[pq_budget_type]')->widget(Select2::classname(), [
                            'data' => $model->ListBudgetdetail(),
                            'options' => ['placeholder' => 'กรุณาเลือก'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'dropdownParent' => '#main-modal',
                            ],
                            'pluginEvents' => [
                                'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0]
                                            \$('#order-data_json-pq_budget_type_name').val(data.text)
                                        }",
                            ]
                        ])->label('ประเภทเงิน');
                    ?>


                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'data_json[pq_consideration]')->radioList(['เกณฑ์ราคา' => 'เกณฑ์ราคา', 'เกณฑ์ประเมินประสิทธิภาพต่อราคา' => 'เกณฑ์ประเมินประสิทธิภาพต่อราคา'],['custom' => true, 'inline' => true])->label('การพิจารณา') ?>
                            <?= $form->field($model, 'data_json[pq_reason]')->textArea(['style' => 'height: 130px;'])->label('เหตุผลความจำเป็น') ?>


                        </div>
                        <div class="col-12">



                            <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'data_json[pq_purchase_type_name]')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'data_json[pq_method_get_name]')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'data_json[pq_budget_group_name]')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'data_json[pq_budget_type_name]')->hiddenInput()->label(false) ?>
                            <?= $form->field($model, 'data_json[pq_condition_name]')->hiddenInput()->label(false) ?>
                        </div>
                    </div>
                </div>

            </div>
            <!-- End Col-6 -->
        </div>
        <!-- End Row -->

        <div class="form-group mt-3 d-flex justify-content-center gap-3">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
            <?=Html::a('<i class="fa-solid fa-circle-left"></i> ย้อนกลับ',['/purchase/order/view','id' => $model->id],['class' => 'btn btn-secondary rounded-pill shadow'])?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>


<?php
$js = <<< JS


    \$('#form-order').on('beforeSubmit', function (e) {
        var form = \$(this);
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: async function (response) {
                form.yiiActiveForm('updateMessages', response, true);
                if(response.status == 'success') {
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
     
   
    $("#order-data_json-order_date").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,          
        onShow:thaiYear,                  
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });   



    JS;
$this->registerJS($js, View::POS_END)
?>
<?php Pjax::end(); ?>