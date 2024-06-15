<?php

use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
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
<?php Pjax::begin(['id' => 'purchase-container']); ?>
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
            <div class="border border-secondary rounded p-4" style="height: 277px;">
                <h6 class="text-center">คำสั่ง</h6>
                <div class="row">
                    <div class="col-12">
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('ตามคำสั่ง') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('เลขที่คำสั่ง') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('ลงวันที่') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="border border-secondary rounded p-4">
                <h6 class="text-center">แผนงานโครงการ</h6>
                <div class="row">
                    <div class="col-12">
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('ชื่อโครงการ') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('โครงการเลขที่') ?>
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('รหัสอ้างอิง EGP') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('การเบิกจ่ายเงิน') ?>
                        <?= $form->field($model, 'data_json[8]')->textInput()->label('รายการแผน EGP') ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="border border-secondary rounded p-4 mt-3">
                <h6 class=" mb-0 text-center">วิธีการซื้อ/จ้าง</h6>
                <div class="row">
                    <div class="col-4">

                        <?php
                            echo $form->field($model, 'data_json[purchase_type]')->widget(Select2::classname(), [
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

                        <?= $form->field($model, 'data_json[5]')->textInput()->label('เหตุผลความจำเป็น') ?>
                     

                    </div>
                    <div class="col-4">
                        <?php
                            echo $form->field($model, 'data_json[1]')->widget(Select2::classname(), [
                                'data' => $model->ListMethodget(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('วิธีจัดหา');
                        ?>

                                    <?=
                                        $form->field($model, 'data_json[7]')->widget(Select2::classname(), [
                                            'data' => $model->ListBudgetdetail(),
                                            'options' => ['placeholder' => 'กรุณาเลือก'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('หมวดเงิน');
                                    ?>
                    </div>
                    <div class="col-4">
                        <?=
                            $form->field($model, 'data_json[2]')->widget(Select2::classname(), [
                                'data' => $model->ListBudgetdetail(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('ประเภทเงิน');
                        ?>
                        <?= $form->field($model, 'data_json[1]')->textInput()->label('เงื่อนไข') ?>
                        
                        </div>
                        
                        <div class="col-4">
                            <?= $form->field($model, 'data_json[6]')->radioList(['1' => 'เกณฑ์ราคา', '2' => 'เกณฑ์ประเมินประสิทธิภาพต่อราคา'])->label('การพิจารณา') ?>
                            </div>
                            <div class="col-8">
                        <?= $form->field($model, 'data_json[2]')->textArea()->label('เหตุผลการจัดหา') ?>

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
<?php Pjax::end() ?>