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


<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']] // spacing form field groups
]); ?>
<div class="d-flex flex-column gap-4 pb-5">

    <div class="px-0">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary-gradient">
                        <h6 class="">ข้อมูลพัสดุหลัก</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label fw-medium">หมวดพัสดุ <span class="text-danger">*</span></label><input type="text" class="form-control bg-light" readonly="" value="ที่ดิน"></div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('รหัสคุม FSN (Federal Stock Number)') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'data_json[lan_number]')->textInput(['maxlength' => true])->label('เลขที่ โฉนด') ?>
                            </div>
                            <div class="col-md-6"><label class="form-label fw-medium">ที่เอกสาร (ถ้ามี)</label><input type="text" class="form-control" value=""></div>
                            <div class="col-12 border-top my-2"></div>
                            <div class="col-12"><label class="form-label fw-medium">เนื้อที่</label></div>

                            <div class="col-md-4">
                                <?= $form->field($model, 'data_json[land_size]', [
                                    'addon' => ['append' => ['content' => 'ไร่']],
                                ])->textInput()->label(false) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'data_json[land_size_ngan]', [
                                    'addon' => ['append' => ['content' => 'งาน']],
                                ])->textInput()->label(false) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'data_json[land_size_tarangwa]', [
                                    'addon' => ['append' => ['content' => 'ตารางวา']],
                                ])->textInput()->label(false) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'data_json[address]')->textArea(['rows' => 5])->label('ที่ตั้ง') ?>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary-gradient">
                        <h6 class="">ข้อมูลงบประมาณและการได้มา</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <?= $form->field($model, 'on_year')->widget(MaskedInput::className(), ['mask' => '9999'])->label('ปีงบประมาณ') ?>
                            </div>

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
                            <div class="col-md-3">
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
                            <div class="col-md-3">
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
                            <div class="col-md-6"><label class="form-label fw-medium">รหัสงบ GFMIS (ถ้ามี)</label><input type="text" class="form-control" value=""></div>
                            <div class="col-md-6"><label class="form-label fw-medium">เลขที่สัญญา (ถ้ามี)</label><input type="text" class="form-control" value=""></div>
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
                            <div class="col-md-3">
                                 <?= $form->field($model, 'receive_date')->textInput()->label('วันที่รับเข้า'); ?>
                            </div>
                            <div class="col-md-3">
                                <?= $form->field($model, 'price')->textInput(['type' => 'number'])->label('วงเงิน') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary-gradient">
                        <h6 class="">ไฟล์แนบเอกสารประกอบ</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label fw-medium">ไฟล์สแกนโฉนด</label><input type="file" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-medium">ใบอนุญาตใช้น้ำ (ถ้ามี)</label><input type="file" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-medium">ภาพถ่าย</label><input type="file" class="form-control" accept="image/*"></div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[land_address]')->textArea(['maxlength' => true])->label('ที่ตั้ง') ?>
                                <?= $model->upload('asset') ?>
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
                            <div class="col-6">
<div class="col-12 d-flex justify-content-end gap-2 py-4">
                <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary shadow', 'id' => 'summit']) ?>
            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
            </div>
            

    </div>
</div>






<!-- ปุ่มดำเนินการ -->
<div class="row mt-4">
    <div class="col-12 d-flex justify-content-center">

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