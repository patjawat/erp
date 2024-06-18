<?php

use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
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
<div class="order-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-order',
        // 'type' => ActiveForm::TYPE_HORIZONTAL,
        'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
    ]); ?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>

    <div class="row">

        <div class="col-6">
            <div class="border border-secondary rounded p-4">
                <h6 class=" text-center"><i class="fa-solid fa-circle-info text-primary"></i> วิธีการซื้อ/จ้าง</h6>
                <div class="row">
                    <div class="col-6">

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
                                            \$('#asset-data_json-purchase_text').val(data.text)
                                        }",
                                ]
                            ])->label('วิธีซื้อหรือจ้าง');
                        ?>

                        <?= $form->field($model, 'data_json[pq_reason]')->textInput()->label('เหตุผลความจำเป็น') ?>


                    </div>
                    <div class="col-6">
                        <?php
                            echo $form->field($model, 'data_json[pq_method_get]')->widget(Select2::classname(), [
                                'data' => $model->ListMethodget(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('วิธีจัดหา');
                        ?>

                        <?=
                            $form->field($model, 'data_json[pq_budget_group]')->widget(Select2::classname(), [
                                'data' => $model->ListBudgetdetail(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('หมวดเงิน');
                        ?>
                    </div>
                    <div class="col-6">
                        <?=
                            $form->field($model, 'data_json[pq_budget_type]')->widget(Select2::classname(), [
                                'data' => $model->ListBudgetdetail(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('ประเภทเงิน');
                        ?>
                        
                        </div>
                        
                        <div class="col-6">
                            <?= $form->field($model, 'data_json[pq_condition]')->textInput()->label('เงื่อนไข') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[pq_income_reason]')->textArea(['rows' => 5, 'style' => 'height: 106px;'])->label('เหตุผลการจัดหา') ?>
                            <?= $form->field($model, 'data_json[pq_consideration]')->radioList(['1' => 'เกณฑ์ราคา', '2' => 'เกณฑ์ประเมินประสิทธิภาพต่อราคา'])->label('การพิจารณา') ?>
                </div>
                </div>
            </div>
        </div>

        <div class="col-6">
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
                        <?= $form->field($model, 'data_json[order_date]')->textInput()->label('ลงวันที่') ?>
                    </div>
                </div>
            </div>


            <div class="border border-secondary rounded p-4 mt-3">
                <h6 class="text-center"><i class="fa-solid fa-circle-info text-primary"></i> แผนงานโครงการ</h6>
                <div class="row">
                    <div class="col-12">
                        <?= $form->field($model, 'data_json[pq_project_name')->textInput()->label('ชื่อโครงการ') ?>
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


    </div>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

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