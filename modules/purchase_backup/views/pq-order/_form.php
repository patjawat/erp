<?php

use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.col-form-label {
    text-align: end;
}
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
]); ?>


<div class="row">
    <div class="col-6">
        <div class="border border-secondary rounded p-4" style="height: 276px;">
            <h6 class="text-center"><i class="fa-solid fa-circle-info text-primary"></i> คำสั่ง</h6>
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'data_json[order]')->textInput()->label('ตามคำสั่ง') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[order_number]')->textInput()->label('เลขที่คำสั่ง') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[order_date]')->widget(DateControl::classname(), [
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'th',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                            'pluginOptions' => [
                                'autoclose' => true
                            ]
                        ]
                    ])->label('ลงวันที่') ?>
                </div>
            </div>
        </div>



    </div>
    <!-- End Col-6 -->
    <div class="col-6">
        <div class="border border-secondary rounded p-4">
            <h6 class="text-center"><i class="fa-solid fa-circle-info text-primary"></i> แผนงานโครงการ</h6>
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
    <div class="col-12">
        <div class="border border-secondary rounded p-4 mt-3">
            <h6 class=" text-center"><i class="fa-solid fa-circle-info text-primary"></i> วิธีการซื้อ/จ้าง</h6>
            <div class="row">
                <div class="col-6">
                    <?= $form->field($model, 'data_json[pq_reason]')->textArea(['style' => 'height: 107px;'])->label('เหตุผลความจำเป็น') ?>


                    <?php
                        $conditionUrl = Url::to(['/depdrop/categorise-by-code']);
                        echo $form->field($model, 'data_json[pq_condition]')->widget(Select2::classname(), [
                            'data' => $model->ListPurchaseCondition(),
                            'options' => ['placeholder' => 'กรุณาเลือก'],
                            'pluginOptions' => [
                                'allowClear' => true,
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

                <div class="col-6">
                    <div class="d-flex justify-content-between">
                                <div class="w-50">

                                    <?php
                                        echo $form->field($model, 'data_json[pq_purchase_type]')->widget(Select2::classname(), [
                                            'data' => $model->ListPurchase(),
                                            'options' => ['placeholder' => 'กรุณาเลือก'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
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
                                        <div class="w-50">
                        <?php
                            echo $form->field($model, 'data_json[pq_method_get]')->widget(Select2::classname(), [
                                'data' => $model->ListMethodget(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0]
                                            \$('#order-data_json-pq_method_get_name').val(data.text)
                                        }",
                                ]
                            ])->label('วิธีจัดหา');
                        ?>


                    </div>
                    </div>

                    <?=
                        $form->field($model, 'data_json[pq_budget_group]')->widget(Select2::classname(), [
                            'data' => $model->ListBudgetdetail(),
                            'options' => ['placeholder' => 'กรุณาเลือก'],
                            'pluginOptions' => [
                                'allowClear' => true,
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
                <div class="col-12">
                    <?= $form->field($model, 'data_json[pq_income_reason]')->textArea(['rows' => 5, 'style' => 'height: 106px;'])->label('เหตุผลการจัดหา') ?>
                    <?= $form->field($model, 'data_json[pq_consideration]')->radioList(['1' => 'เกณฑ์ราคา', '2' => 'เกณฑ์ประเมินประสิทธิภาพต่อราคา'])->label('การพิจารณา') ?>
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

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>


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
                    closeModal()
                    success()
                    await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                }
            }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END)
?>