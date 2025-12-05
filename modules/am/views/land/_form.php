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

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <?= Html::a(
        '<i class="fa-solid fa-angle-left"></i>',
        Yii::$app->request->referrer ?: ['/am/land'],
        [
            'class' => 'btn btn-light btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center',
            'style' => 'width:32px; height:32px;'
        ]
    ) ?>
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package text-primary">
            <path d="m7.5 4.27 9 5.15"></path>
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
            <path d="m3.3 7 8.7 5 8.7-5"></path>
            <path d="M12 22V12"></path>
        </svg> ระบบบริหารทรัพย์สิน</h4>
</div>

<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu', ['active' => 'asset']) ?>
<?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']] // spacing form field groups
]); ?>
<div class="d-flex flex-column gap-4 pb-5">

    <div class="px-0">
        <form class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">ข้อมูลพัสดุหลัก</h6>
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
                </div>
            </div>
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">ข้อมูลงบประมาณและการได้มา</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label fw-medium">แหล่งงบประมาณ <span class="text-danger">*</span></label><input type="text" class="form-control" required="" placeholder="เช่น เงินบริจาค" value="เงินบริจาค"></div>
                            <div class="col-md-6"><label class="form-label fw-medium">วิธีได้มา <span class="text-danger">*</span></label><select class="form-select" required="">
                                    <option value="">- เลือก -</option>
                                    <option value="ตกลงราคา">ตกลงราคา</option>
                                    <option value="e-Bidding">e-Bidding</option>
                                    <option value="e-Market">e-Market</option>
                                    <option value="เฉพาะเจาะจง">เฉพาะเจาะจง</option>
                                    <option value="รับบริจาค">รับบริจาค</option>
                                    <option value="จ้างเหมา">จ้างเหมา</option>
                                </select></div>
                            <div class="col-md-6"><label class="form-label fw-medium">รหัสงบ GFMIS (ถ้ามี)</label><input type="text" class="form-control" value=""></div>
                            <div class="col-md-6"><label class="form-label fw-medium">เลขที่สัญญา (ถ้ามี)</label><input type="text" class="form-control" value=""></div>
                            <div class="col-md-6"><label class="form-label fw-medium">ซื้อจาก / ผู้ขาย</label><input type="text" class="form-control" value="นายใจบุญ ค้ำจุน"></div>
                            <div class="col-md-3"><label class="form-label fw-medium">วันที่รับ</label><input type="date" class="form-control" value="2010-01-15"></div>
                            <div class="col-md-3"><label class="form-label fw-medium">วงเงิน</label><input type="number" class="form-control" value=""></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">ไฟล์แนบเอกสารประกอบ</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label fw-medium">ไฟล์สแกนโฉนด</label><input type="file" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-medium">ใบอนุญาตใช้น้ำ (ถ้ามี)</label><input type="file" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label fw-medium">ภาพถ่าย</label><input type="file" class="form-control" accept="image/*"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 py-4"><button type="button" class="btn btn-outline-secondary btn-lg">ย้อนกลับ</button><button type="submit" class="btn btn-primary btn-lg d-flex align-items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-save">
                        <path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path>
                        <path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"></path>
                        <path d="M7 3v4a1 1 0 0 0 1 1h7"></path>
                    </svg> บันทึกข้อมูล</button></div>
        </form>
    </div>
</div>




<div class="card">
    <div class="card-body">


        <h5 class="section-title">ข้อมูลทั่วไป</h5>
        <div class="form-section">
            <div class="row">
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                    <!-- ข้อมูลทั่วไป -->
                    <div class="row g-3">
                        <div class="col-md-6">

                        </div>
                        <div class="col-md-6">


                        </div>
                    </div>

                </div>
                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                    <div class="row g-3">
                        <div class="col-md-6">

                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between">
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