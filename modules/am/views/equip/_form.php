<?php

use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use yii\widgets\MaskedInput;
use app\modules\hr\models\Employees;
use kartik\editors\Summernote;

$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');

// รูปแบบหมายเลขครุภัณฑ์ที่ตั้งค่าไว้ (สำหรับแสดงใน label)
$assetNumberPattern = \app\modules\am\services\AssetNumberGenerator::getActivePattern();
$assetNumberExample = str_replace(['{category}', '{year}', '{seq}'], ['7910-003-0003', '66', '01'], $assetNumberPattern);


?>

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

<?= $form->field($model, 'asset_item_id')->hiddenInput()->label(false); ?>
<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="image"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">รูปภาพทรัพย์สิน</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="file-file-preview" id="editImagePreview" data-isfile="<?= $model->showImg()['isFile'] ?>" data-newfile="false">
                        <?= Html::img($model->showImg()['image'], ['id' => 'editPreviewImg']) ?>
                        <div class="file-remove" id="editRemoveImage">
                            <i class="bi bi-x"></i>
                        </div>
                    </div>
                    <div class="file-upload">
                        <div class="file-upload-btn" id="editUploadBtn">
                            <i class="bi bi-cloud-arrow-up fs-3 mb-2"></i>
                            <span>คลิกหรือลากไฟล์มาวางที่นี่</span>
                            <small class="d-block text-muted mt-2">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
                        </div>
                        <input type="file" class="file-upload-input" id="my_file" accept="image/*">
                    </div>
                </div>
            </div>
        </div>


        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="shopping-cart"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ข้อมูลการจัดซื้อ</h6>
            </div>
            <div class="card-body">
                <div class="form-section mb-0">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'receive_date')->textInput(['placeholder' => 'วว/ดด/พ.ศ.'])->label('วันที่รับเข้า'); ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'price', [
                                'template' => '{label}<div class="input-group"><span class="input-group-text">฿</span>{input}</div>{hint}{error}',
                            ])->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => '0.00', 'class' => 'form-control'])->label('ราคาตามใบเสร็จ/ราคาแรกรับ'); ?>
                            <small class="text-muted">ราคาทุนตามหลักฐานการจัดซื้อ</small>
                        </div>
                        <div class="col-12">
                            <?php
                            echo $form->field($model, 'data_json[vendor_id]')->widget(Select2::classname(), [
                                'data' => $model->ListVendor(),
                                'options' => ['placeholder' => 'เลือกผู้จำหน่าย/ผู้ขาย'],
                                'pluginOptions' => ['allowClear' => true],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { var data = $(this).select2('data')[0]; $('#asset-data_json-vendor_text').val(data.text); }",
                                ]
                            ])->label('ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค');
                            ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[order_number]')->textInput(['maxlength' => true, 'placeholder' => 'เลขที่ใบกำกับ/ใบส่งของ'])->label('เลขที่ใบกำกับ/ใบส่งของ'); ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <?php
                            echo $form->field($model, 'data_json[budget_type]')->widget(Select2::classname(), [
                                'data' => $model->ListBudgetdetail(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => ['allowClear' => true],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { var data = $(this).select2('data')[0]; $('#asset-data_json-budget_type_text').val(data.text); }",
                                ]
                            ])->label('ประเภทเงิน');
                            ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <?= $form->field($model, 'on_year')->widget(MaskedInput::className(), ['mask' => '9999'])->label('ปีงบประมาณ'); ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <?php
                            echo $form->field($model, 'data_json[method_get]')->widget(Select2::classname(), [
                                'data' => $model->ListMethodget(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => ['allowClear' => true],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { var data = $(this).select2('data')[0]; $('#asset-data_json-method_get_text').val(data.text); }",
                                ]
                            ])->label('วิธีได้มา');
                            ?>
                        </div>
                        <div class="col-12">
                            <?php
                            echo $form->field($model, 'purchase')->widget(Select2::classname(), [
                                'data' => $model->ListPurchase(),
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => ['allowClear' => true],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { var data = $(this).select2('data')[0]; $('#asset-data_json-purchase_text').val(data.text); }",
                                ]
                            ])->label('วิธีการได้มา');
                            ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[inspection_date]')->textInput(['placeholder' => 'วว/ดด/พ.ศ.'])->label('วันที่ตรวจรับ'); ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[expire_date]')->textInput(['placeholder' => 'วว/ดด/พ.ศ.'])->label('วันหมดประกัน'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="trending-down"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">เตรียมข้อมูลค่าเสื่อม</h6>
            </div>
            <div class="card-body">
                <div class="form-section mb-0">
                    <p class="text-muted small mb-3">ใช้สำหรับคำนวณค่าเสื่อมราคาในอนาคต</p>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'useful_life')->textInput(['type' => 'number', 'min' => 1, 'placeholder' => 'เช่น 5'])->label('อายุการใช้งาน (ปี)'); ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'depreciation_rate', [
                                'template' => '{label}<div class="input-group">{input}<span class="input-group-text">%</span></div>{hint}{error}',
                            ])->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'placeholder' => 'เช่น 20.00',
                                'class' => 'form-control',
                            ])->label('อัตราค่าเสื่อม')->hint('ระบุทศนิยมได้ 2 ตำแหน่ง (ถ้ามี)'); ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="building"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">สถานที่และหน่วยงาน</h6>
            </div>
            <div class="card-body">
                <div class="form-section mb-0">
                    <div class="row g-3">
                        <div class="col-12">
                            <?= $form->field($model, 'department')->widget(\kartik\tree\TreeViewInput::className(), [
                                'name' => 'department',
                                'query' => app\modules\hr\models\Organization::find()->addOrderBy('root, lft'),
                                'value' => 1,
                                'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                                'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                                'fontAwesome' => true,
                                'asDropdown' => true,
                                'multiple' => false,
                                'options' => ['disabled' => false],
                            ])->label('หน่วยงานผู้รับผิดชอบ'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </div>
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="tag"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ข้อมูลพื้นฐาน</h6>
            </div>
            <div class="card-body">
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-12">
                            <?php
                            echo $form->field($model, 'asset_name', [
                                'addon' => [
                                    'append' => ['content' => Html::a('<i class="fa-solid fa-magnifying-glass"></i>', ['/am/asset-item/list-item', 'title' => '<i class="bi bi-ui-checks"></i> แสดงทะเบียนรหัสทรัพย์สิน'], ['class' => 'btn btn-secondary open-modal', 'data' => ['size' => 'modal-xl']]), 'asButton' => true]
                                ]
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'ค้นหาชื่อครุภัณฑ์',
                                'readonly' => false,  // Make field readonly
                                'class' => 'form-control'  // Add background color
                            ])->label('ชื่อครุภัณฑ์');
                            ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php

                            // Select2 - ประเภทครุภัณฑ์
                            echo $form->field($model, 'asset_type_id')->widget(Select2::classname(), [
                                'data' => $model->listAssetType(),
                                'options' => [
                                    'placeholder' => 'เลือกประเภท...',
                                    'id' => 'asset_type_id'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('ประเภทครุภัณฑ์');
                            ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php
                            // DepDrop - หมวดหมู่ครุภัณฑ์
                            echo $form->field($model, 'asset_category_id')->widget(DepDrop::class, [
                                'options' => ['id' => 'asset_category_id', 'placeholder' => 'เลือกหมวดทรัพย์สิน ...'],
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'pluginOptions' => [
                                    'depends' => ['asset_type_id'], // ต้องตรงกับ id ของ dropdown แรก
                                    'url' => Url::to(['/am/asset-item/get-asset-category']),
                                    'loadingText' => 'กำลังโหลด ...',
                                    'initialize' => true, // กรณีแก้ไขข้อมูล ต้องโหลดค่าปัจจุบัน
                                ],
                            ])->label('หมวดหมู่'); ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'fsn_number', [
                                'addon' => [
                                    'append' => [
                                        'content' => Html::a('<i class="fa-solid fa-magnifying-glass"></i>', ['/am/fsn/list-fsn', 'title' => '<i class="bi bi-ui-checks"></i> แสดงทะเบียน FSN'], ['class' => 'btn btn-secondary open-modal', 'data' => ['size' => 'modal-xl']]),
                                        'asButton' => true
                                    ]
                                ]
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'ค้นหาเลข FSN',
                                'readonly' => false,
                                'class' => 'form-control'
                            ])->label('FSN'); ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php
                            echo $form->field($model, 'code', [
                                'addon' => [
                                    'append' => ['content' => Html::a('<i class="fa-solid fa-wand-magic-sparkles me-1"></i> สร้างหมายเลข', '#', ['class' => 'btn btn-outline-primary next-code', 'title' => 'สร้างหมายเลขครุภัณฑ์อัตโนมัติจาก FSN ที่เลือก']), 'asButton' => true]
                                ]
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'กดปุ่มสร้างหมายเลข หรือกรอก FSN แล้วจะสร้างอัตโนมัติ',
                                'readonly' => false,
                                'class' => 'form-control'
                            ])->label('หมายเลขครุภัณฑ์')->hint('สามารถแก้ไขได้ตามต้องการ', ['class' => 'form-text text-muted small']); ?>
                        </div>


                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[fsn_old]')->textInput(['maxlength' => true])->label('เลขครุภัณฑ์เดิม') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php
                            echo $form->field($model, 'data_json[brand]')->widget(Select2::classname(), [
                                'data' => $model->listBand(),
                                'options' => ['placeholder' => 'เลือกยี่ห้อ...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้

                                ],
                            ])->label("ยี่ห้อ")
                            ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <?php
                            echo $form->field($model, 'data_json[asset_model]')->widget(Select2::classname(), [
                                'data' => $model->listModel(),
                                'options' => ['placeholder' => 'เลือกรุ่น/โมเดล...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้

                                ],
                            ])->label("รุ่น/โมเดล")
                            ?>

                        </div>

                        <div class="col-6 col-md-3">
                            <?php
                            echo $form->field($model, 'data_json[color_name]')->widget(Select2::classname(), [
                                'data' => $model->listColor(),
                                'options' => ['placeholder' => 'เลือกสี...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                ],
                            ])->label("สี")
                            ?>
                        </div>

                        <div class="col-6 col-md-3">
                            <?php
                            echo $form->field($model, 'data_json[unit]')->widget(Select2::classname(), [
                                'data' => $model->listUnit(),
                                'options' => ['placeholder' => 'ระบุ...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                ],
                            ])->label("หน่วยนับ")
                            ?>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[serial_number]')->textInput()->label('S/N') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php // $form->field($model, 'data_json[license_plate]')->textInput()->label('เลขทะเบียน (รถยนต์)') 
                            ?>
                            <?= $form->field($model, 'license_plate')->textInput()->label('เลขทะเบียน (รถยนต์)') ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[asset_options]')->widget(Summernote::class, [
                                'useKrajeePresets' => true,
                                'pluginOptions' => [
                                    'height' => 150, // ความสูงเริ่มต้น (px)
                                    'minHeight' => 150, // ความสูงต่ำสุด
                                    'maxHeight' => 500, // ความสูงสูงสุด
                                ]
                            ])->label('คุณลักษณะเฉพาะ'); ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[note]')->textarea([
                                'rows' => 3,
                                'placeholder' => 'กรอกหมายเหตุเพิ่มเติม...',
                                'class' => 'form-control'
                            ])->label('หมายเหตุ'); ?>
                        </div>

                    </div>

                </div>

                <div class="border-bottom d-flex align-items-center gap-2 py-2 mb-2 mt-4">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i data-lucide="map-pin"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">สถานที่และผู้รับผิดชอบ</h6>
                </div>
                <div class="form-section mt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[location]')->textInput(['placeholder' => 'อาคาร/ห้อง/คลัง'])->label('สถานที่ตั้ง/คลัง/ห้อง'); ?>
                        </div>
                        <div class="col-12 col-md-6">
                 
                            <?php
                            $url = \yii\helpers\Url::to(['/depdrop/employee']);


                            // 1. หาข้อมูลพนักงานก่อน
                            $employee = !empty($model->owner) ? Employees::findOne($model->owner) : null;

                            // 2. เช็กว่าเจอตัวแปร $employee ไหม ถ้าเจอค่อยดึง fullname ถ้าไม่เจอให้เป็นค่าว่าง
                            $ownerName = $employee ? $employee->fullname : '';

                            echo $form->field($model, 'owner')->widget(Select2::classname(), [
                                'initValueText' => $ownerName, // ใช้ตัวแปรที่เช็กแล้ว
                                'options' => ['placeholder' => 'กรุณาเลือก'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 1,
                                    'language' => [
                                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                    ],
                                    'ajax' => [
                                        'url' => $url,
                                        'dataType' => 'json',
                                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                    ],
                                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                    'templateResult' => new JsExpression('function(city) { return city.text; }'),
                                    'templateSelection' => new JsExpression('function (city) { return city.text; }'),
                                ],
                            ])->label('ผู้รับผิดชอบ');
                            ?>
                        </div>
                        <div class="col-12 col-md-3">
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
                            ])->label('สถานะ'); ?>
                        </div>
                        <div class="col-12 col-md-3">
                            <?php
                            echo $form->field($model, 'asset_condition')->widget(Select2::classname(), [
                                'data' => $model->ListAssetCondition(),
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
                            ]); ?>
                        </div>
                    </div>
                </div>




                <!-- ปุ่มดำเนินการ -->
                <div class="row g-2 mt-4">
                    <div class="col-12 d-flex flex-wrap justify-content-between gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                            <i class="bi bi-x-circle me-2"></i>ล้างข้อมูล
                        </button>
                        <div>
                            <?= Html::a('<i class="bi bi-arrow-left"></i> ย้อนกลับ', Yii::$app->request->referrer ?: ['/am/asset/view', 'id' => $model->id], ['class' => 'btn btn-secondary shadow']) ?>
                            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary shadow', 'id' => 'summit']) ?>

                        </div>
                    </div>
                </div>

            </div>
        </div>


    </div>

</div>





<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'asset_group_id')->hiddenInput(['maxlength' => true])->label(false) ?>


<?php //  $this->render('_form_detail3',['model' => $model, 'form' => $form]) 
?>

<!-- ถ้าเป็นรถยนต์ -->
<?php // if ($model->assetItem?->asset_category_id == 4): 
?>
<?php //  $this->render('asset_item', ['model' => $model, 'form' => $form]) 
?>
<?php // endif; 
?>

<?php ActiveForm::end(); ?>

<?php
$ref = Json::encode($model->ref);
$urlUpload = Url::to('/filemanager/uploads/single');
$nextAssetNumberUrl = Url::to(['/am/equip/next-asset-number']);

$js = <<< JS

 thaiDatepicker('#asset-receive_date,#asset-data_json-expire_date,#asset-data_json-inspection_date')
 isFile()

 function fetchNextAssetNumber(showConfirm) {
    var categoryId = ($('#asset-fsn_number').val() || '').toString().trim();
    if (!categoryId) {
        $('#form-asset .is-invalid').removeClass('is-invalid');
        $('#asset-fsn_number').addClass('is-invalid');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'กรุณาระบุ FSN ก่อน', showConfirmButton: false, timer: 3000 });
        }
        return;
    }
    $.get('$nextAssetNumberUrl', { category_id: categoryId }, function (res) {
        if (res.asset_number) {
            $('#asset-fsn_number').removeClass('is-invalid');
            if (showConfirm && typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'หมายเลขที่จะได้',
                    html: '<p class="mb-0">ระบบเสนอหมายเลขครุภัณฑ์</p><p class="fs-4 fw-bold text-primary mb-0 mt-2">' + res.asset_number + '</p><p class="text-muted small mt-2">ใช้หมายเลขนี้หรือไม่?</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ใช้หมายเลขนี้',
                    cancelButtonText: 'ยกเลิก'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $('#asset-code').val(res.asset_number);
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'กำหนดหมายเลขแล้ว', showConfirmButton: false, timer: 2000 });
                    }
                });
            } else {
                $('#asset-code').val(res.asset_number);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'สร้างหมายเลขครุภัณฑ์แล้ว', showConfirmButton: false, timer: 2000 });
                }
            }
        }
    }, 'json');
 }

 $('#asset-fsn_number').on('blur', function () {
    if (($(this).val() || '').toString().trim() && !$('#asset-code').val()) { fetchNextAssetNumber(false); }
 });
 $('.next-code').on('click', function (e) {
    e.preventDefault();
    fetchNextAssetNumber(true);
 });


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

        
    \$('.select-image').click(function (e) { 
            \$('#file').click();
            
        });
        \$('#file').on('change', function (e) {
        const image = this.files[0];

        if (image.size < 2000000) {
            const reader = new FileReader();
            reader.onload = function () {
                const imgArea = \$('.img-area');
                imgArea.find('img').remove();

                const imgUrl = reader.result;
                const img = \$('<img>').attr('src', imgUrl);
                imgArea.append(img).addClass('active').data('img', image.name);

                const file = \$('#file').prop('files')[0];
                const formData = new FormData();
                formData.append("asset", file);
                formData.append("id", 1);
                formData.append("ref", '$ref');
                formData.append("name", 'asset');

                console.log(file);

                \$.ajax({
                    url: '$urlUpload',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        console.log(res);
                        \$('.img-room').attr('src', res.img);
                        // await \$.pjax.reload({ container: response.container, history: false, replace: false, timeout: false });
                    }
                });
            };
            reader.readAsDataURL(image);
        } else {
            alert("Image size more than 2MB");
        }
    });

    
JS;
$this->registerJs($js);
?>