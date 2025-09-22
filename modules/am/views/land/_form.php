<?php

use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;

$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');

?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-map"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<style>
    .modal-footer {
        display: none !important;
    }
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="card">
    <div class="card-body">


        <h5 class="section-title">ข้อมูลทั่วไป</h5>
        <div class="form-section">
            <div class="row">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                    <!-- ข้อมูลทั่วไป -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[lan_number]')->textInput(['maxlength' => true])->label('เลขที่ โฉนด') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('รหัสคุม FSN (Federal Stock Number)') ?>

                        </div>
                    </div>

                </div>
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[land_size]')->textInput()->label('เนื้อที่ไร่') ?>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between">
                                <?= $form->field($model, 'data_json[land_size_ngan]')->textInput()->label('เนื้อที่งาน') ?>
                                <?= $form->field($model, 'data_json[land_size_tarangwa]')->textInput()->label('เนื้อที่ตารางวา') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'data_json[land_address]')->textArea(['maxlength' => true])->label('ที่ตั้ง') ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                <!-- ข้อมูลงบประมาณ -->
                <div class="form-section">
                    <h5 class="section-title">ข้อมูลงบประมาณ</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <?php
                            echo $form->field($model, 'data_json[budget_type]')->widget(Select2::classname(), [
                                'data' => $model->ListBudgetdetail(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-budget_type_text').val(data.text)
                                         }",
                                ]
                            ])->label('ประเภทเงิน');
                            ?>
                        </div>

                        <div class="col-md-4">
                            <?= $form->field($model, 'price')->textInput(['type' => 'number'])->label('วงเงิน') ?>
                        </div>

                        <div class="col-md-4">
                            <?= $form->field($model, 'on_year')->widget(MaskedInput::className(), ['mask' => '9999'])->label('ปีงบประมาณ') ?>
                        </div>

                        <div class="col-md-6">
                            <label for="budgetSource" class="form-label">แหล่งงบประมาณ</label>
                            <input type="text" class="form-control" id="budgetSource">
                        </div>

                        <div class="col-md-6">
                            <?php
                            echo $form->field($model, 'asset_status')->widget(Select2::classname(), [
                                'data' => $model->ListAssetStatus(),
                                'options' => ['placeholder' => 'กรุณาเลือก...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                ]
                            ])->label('สถานะ');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                <!-- ข้อมูลการได้มา -->
                <div class="form-section">

                    <h5 class="section-title">ข้อมูลการได้มา</h5>
                    <div class="row g-3">
                        <div class="col-md-6">

                            <?php
                            echo $form->field($model, 'data_json[method_get]')->widget(Select2::classname(), [
                                'data' => $model->ListMethodget(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                ]
                            ])->label('วิธีได้มา');
                            ?>
                        </div>

                        <div class="col-md-6 purchase-method-field">
                            <?php
                            echo $form->field($model, 'purchase')->widget(Select2::classname(), [
                                'data' => $model->ListPurchase(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-purchase_text').val(data.text)
                                        }",
                                ]
                            ])->label('วิธีการได้มา');
                            ?>
                        </div>

                        <div class="col-md-6">
                            <?php
                            echo $form->field($model, 'data_json[vendor_id]')->widget(Select2::classname(), [
                                'data' => $model->ListVendor(),
                                'options' => ['placeholder' => 'เลือกผู้จำหน่าย'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-vendor_text').val(data.text)
                                         }",
                                ]
                            ])->label('ซื้อจาก');
                            ?>
                        </div>

                        <div class="col-md-6">
                            <label for="documentRef" class="form-label">เลขที่เอกสารอ้างอิง</label>
                            <input type="text" class="form-control" id="documentRef"
                                placeholder="เช่น เลขที่สัญญา, ใบสั่งซื้อ">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?= $model->upload('asset') ?>
    </div>
</div>

<!-- ปุ่มดำเนินการ -->
<div class="row mt-4">
    <div class="col-12 d-flex justify-content-center">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left"></i> ย้อนกลับ', Yii::$app->request->referrer ?: ['/am/asset/view', 'id' => $model->id], ['class' => 'btn btn-secondary shadow']) ?>
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary shadow', 'id' => 'summit']) ?>

        </div>
    </div>
</div>

<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'asset_group_id')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'asset_name')->hiddenInput(['value' => 'ที่ดิน'])->label(false) ?>
<?php ActiveForm::end(); ?>






</div>
</div>



<?php
$ref = Json::encode($model->ref); // ปลอดภัยแม้มีอักขระพิเศษ
$urlUpload = Url::to('/filemanager/uploads/single');

$js = <<< JS
 //กำหนดให้ปฏิทินแสดงวันที่
 thaiDatepicker('#asset-receive_date,#asset-data_json-expire_date,#asset-data_json-inspection_date')

 isFile()
\$('#form-asset').on('beforeSubmit', function (e) {
            var form = \$(this);
           
            
            Swal.fire({
            title: "ยืนยัน?",
            text: "บันทึกข้อมูล!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "ยกเลิก!",
            confirmButtonText: "ใช่, ยืนยัน!"
            }).then((result) => {
            if (result.isConfirmed) {
                 uploadImage('asset',$ref);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        console.log(response);
                        
                        if(response.status == 'success') {
                            
                            closeModal()
                            success()
                             window.location.reload(true);
                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });

            }
            });
            return false;
        });

JS;
$this->registerJs($js);
?>