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
<?= $form->field($model, 'code')->hiddenInput()->label(false); ?>
<div class="row">
<div class="col-4">
        <div class="card">
            <div class="card-body">
                <!-- รูปภาพ -->
                <label class="form-label mb-0">รูปภาพทรัพย์สิน</label>
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


        <div class="card">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="tag"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ข้อมูลงบประมาณ</h6>
            </div>
            <div class="card-body">

                <!-- ข้อมูลงบประมาณ -->
                <div class="form-section">
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
                            <?= $form->field($model, 'price')->textInput(['type' => 'number'])->label('ราคาแรกรับ') ?>
                        </div>

                        <div class="col-md-4">
                            <?= $form->field($model, 'on_year')->widget(MaskedInput::className(), ['mask' => '9999'])->label('ปีงบประมาณ') ?>
                        </div>


                        <div class="col-md-12">
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
                            ])->label('หน่วยงานภายในตามโครงสร้าง'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="tag"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ข้อมูลการได้มา</h6>
            </div>
            <div class="card-body">

                <!-- ข้อมูลการได้มา -->
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-12">

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

                        <div class="col-md-12 purchase-method-field">
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

                        <div class="col-md-12">
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
                            ])->label('ผู้ขาย/ผู้บริจาค');
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'data_json[inspection_date]')->textInput()->label('วันที่ตรวจรับ'); ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'receive_date')->textInput()->label('วันที่รับเข้า'); ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'data_json[expire_date]')->textInput()->label('วันหมดประกัน'); ?>
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
                    <p class="text-body-secondary small mb-3">กำหนดจากหมวดอาคาร เพื่อให้การคำนวณรายตัวและภาพรวมใช้เกณฑ์เดียวกัน</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-body-secondary small mb-1">อายุการใช้งาน (ปี)</label>
                            <div class="fw-semibold fs-5" id="building-useful-life"><?= $model->useful_life !== null && $model->useful_life !== '' ? Html::encode($model->useful_life) : '—' ?></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-body-secondary small mb-1">อัตราค่าเสื่อมต่อปี</label>
                            <div class="fw-semibold fs-5"><span id="building-depreciation-rate"><?= $model->depreciation_rate !== null && $model->depreciation_rate !== '' ? Html::encode($model->depreciation_rate) : '—' ?></span> %</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="col-8">
        <div class="card">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="tag"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ข้อมูลพื้นฐาน</h6>
            </div>
            <div class="card-body">
                <!-- ข้อมูลทั่วไป -->
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-12">
                            <?php
                            echo $form->field($model, 'asset_name')->textInput([
                                'maxlength' => true,
                                'placeholder' => 'ระบุชื่ออาคาร',
                                'class' => 'form-control',
                            ])->label('ชื่ออาคาร');
                            ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php
                            echo $form->field($model, 'asset_type_id')->widget(Select2::classname(), [
                                'data' => $model->listAssetType('BLDG'),
                                'options' => [
                                    'placeholder' => 'เลือกประเภทอาคาร...',
                                    'id' => 'asset_type_id',
                                ],
                                'pluginOptions' => ['allowClear' => false],
                            ])->label('ประเภทอาคาร');
                            ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php
                            echo $form->field($model, 'asset_category_id', [
                                'addon' => [
                                    'append' => [
                                        'content' => Html::a(
                                            '<i class="fa-solid fa-gear"></i>',
                                            ['/am/asset-category/create', 'group' => 'BLDG', 'title' => 'เพิ่มหมวดอาคาร'],
                                            [
                                                'class' => 'btn btn-outline-secondary open-modal',
                                                'title' => 'เพิ่มและตั้งค่ารหัสหมวดอาคาร',
                                                'aria-label' => 'เพิ่มและตั้งค่ารหัสหมวดอาคาร',
                                                'data-size' => 'modal-lg',
                                            ]
                                        ),
                                        'asButton' => true,
                                    ],
                                ],
                            ])->widget(DepDrop::class, [
                                'options' => [
                                    'id' => 'asset_category_id',
                                    'placeholder' => 'เลือกหมวดอาคาร...',
                                ],
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => false],
                                ],
                                'pluginOptions' => [
                                    'depends' => ['asset_type_id'],
                                    'url' => Url::to(['/am/asset-item/get-asset-category']),
                                    'loadingText' => 'กำลังโหลด...',
                                    'initialize' => true,
                                ],
                            ])->label('หมวดอาคาร / รหัสทะเบียน')->hint(
                                'เลือกรหัสจริงของหน่วยงาน เช่น 0920-004-0001 หากยังไม่มีให้กดปุ่มตั้งค่าด้านขวา',
                                ['class' => 'form-text text-muted small']
                            );
                            ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?php
                            echo $form->field($model, 'fsn_number', [
                                'addon' => [
                                    'append' => [
                                        'content' => Html::a(
                                            '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> สร้างหมายเลข',
                                            '#',
                                            [
                                                'class' => 'btn btn-outline-primary next-code',
                                                'title' => 'สร้างหมายเลขทะเบียนอาคารจากรหัสหมวด',
                                            ]
                                        ),
                                        'asButton' => true,
                                    ],
                                ]
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'เลือกหมวด + ปีงบ แล้วกดสร้างหมายเลข',
                                'class' => 'form-control',
                            ])->label('หมายเลขทะเบียนอาคาร')->hint(
                                'ระบบสร้างจากรหัสหมวดและปีงบประมาณ สามารถแก้ไขได้',
                                ['class' => 'form-text text-muted small']
                            ); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'gfmis')->textInput(['maxlength' => true, 'placeholder' => 'รหัสโครงสร้างงบประมาณ (GFMIS)'])->label('รหัสโครงสร้างงบประมาณ(GFMIS)') ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $form->field($model, 'data_json[floors]')->textInput()->label('จำนวนชั้น'); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $form->field($model, 'data_json[area]')->textInput()->label('พื้นที่ใช้สอย (ตร.ม.)'); ?>
                        </div>
                        



                        <div class="col-md-12">
                            <?= $form->field($model, 'data_json[asset_options]')->widget(Summernote::class, [
                                'useKrajeePresets' => true,
                                'pluginOptions' => [
                                    'height' => 150, // ความสูงเริ่มต้น (px)
                                    'minHeight' => 150, // ความสูงต่ำสุด
                                    'maxHeight' => 500, // ความสูงสูงสุด
                                ]
                            ])->label('คุณลักษณะเฉพาะ / รายละเอียดเพิ่มเติม'); ?>
                        </div>

                    </div>

                </div>

                <!-- ข้อมูลสถานที่และวันที่ -->
                <div class="form-section">
                    <div class="card-header border-bottom d-flex align-items-center gap-2">
                        <div class="erp-icon-box bg-primary bg-opacity-10">
                            <i data-lucide="tag"></i>
                        </div>
                        <h6 class="text-uppercase text-secondary m-0">ข้อมูลสถานที่และวันที่</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <?= $form->field($model, 'data_json[location]')->textInput()->label('สถานที่ตั้ง'); ?>
                        </div>


                        <div class="col-md-6">
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
                            ])->label('สถานะ'); ?>
                        </div>ข้อมูลคุณลักษณะเพิ่มเติม
                    </div>
                </div>
                <?= $model->Upload('building_photo') ?>
                <!-- ปุ่มดำเนินการ -->
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-between">
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




<?php ActiveForm::end(); ?>


</div>
</div>



<?php
$ref = Json::encode($model->ref); // ปลอดภัยแม้มีอักขระพิเศษ
$urlUpload = Url::to('/filemanager/uploads/single');
$nextAssetNumberUrl = Url::to(['/am/equip/next-asset-number']);
$categoryDefaultsUrl = Url::to(['/am/asset-item/category-defaults']);

$js = <<< JS

 //กำหนดให้ปฏิทินแสดงวันที่
 thaiDatepicker('#asset-receive_date,#asset-data_json-expire_date,#asset-data_json-inspection_date')


 isFile()

 function loadBuildingCategoryDefaults(categoryCode) {
    if (!categoryCode) {
        $('#building-useful-life, #building-depreciation-rate').text('—');
        return;
    }
    $.get('$categoryDefaultsUrl', { code: categoryCode }, function (response) {
        if (!response || response.status !== 'success') { return; }
        var defaults = response.defaults || {};
        $('#building-useful-life').text(defaults.useful_life || '—');
        $('#building-depreciation-rate').text(defaults.depreciation_rate || '—');
    }, 'json');
 }

 function fetchNextBuildingNumber() {
    var prefix = ($('#asset_category_id').val() || '').toString().trim();
    var onYear = ($('#asset-on_year').val() || '').toString().trim();
    $('#form-asset .is-invalid').removeClass('is-invalid');

    if (!prefix) {
        $('#asset_category_id').addClass('is-invalid').trigger('focus');
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'กรุณาเลือกหมวดอาคารก่อน', showConfirmButton: false, timer: 3000 });
        return;
    }
    if (!onYear) {
        $('#asset-on_year').addClass('is-invalid').trigger('focus');
        Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'กรุณาระบุปีงบประมาณก่อน', showConfirmButton: false, timer: 3000 });
        return;
    }

    $.get('$nextAssetNumberUrl', { category_id: prefix, on_year: onYear }, function (response) {
        if (response.error) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: response.error, showConfirmButton: false, timer: 3000 });
            return;
        }
        if (!response.asset_number) { return; }

        Swal.fire({
            title: 'หมายเลขที่จะได้',
            html: '<p class="mb-0">ระบบเสนอหมายเลขทะเบียนอาคาร</p><p class="fs-4 fw-bold text-primary mb-0 mt-2">' + response.asset_number + '</p><p class="text-muted small mt-2">ใช้หมายเลขนี้หรือไม่?</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช้หมายเลขนี้',
            cancelButtonText: 'ยกเลิก'
        }).then(function (result) {
            if (result.isConfirmed) {
                $('#asset-fsn_number').val(response.asset_number).removeClass('is-invalid');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'กำหนดหมายเลขแล้ว', showConfirmButton: false, timer: 2000 });
            }
        });
    }, 'json');
 }

 $('#asset_category_id').on('change', function () {
    var categoryCode = ($(this).val() || '').toString().trim();
    loadBuildingCategoryDefaults(categoryCode);
    if (categoryCode && ($('#asset-on_year').val() || '').toString().trim() && !($('#asset-fsn_number').val() || '').toString().trim()) {
        fetchNextBuildingNumber();
    }
 });
 $('#asset-on_year').on('blur', function () {
    if (($('#asset_category_id').val() || '').toString().trim() && !($('#asset-fsn_number').val() || '').toString().trim()) {
        fetchNextBuildingNumber();
    }
 });
 loadBuildingCategoryDefaults(($('#asset_category_id').val() || '').toString().trim());
 $('.next-code').on('click', function (e) {
    e.preventDefault();
    fetchNextBuildingNumber();
 });

 $(document).off('assetCategory:saved.building').on('assetCategory:saved.building', function (event, response) {
    if (!response || !response.code || !response.category_id) { return; }
    if (response.category_id.toString() !== ($('#asset_type_id').val() || '').toString()) { return; }

    $('#asset_category_id').one('depdrop:afterChange', function () {
        $(this).val(response.code).trigger('change');
    });
    $('#asset_type_id').trigger('change');
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
                            closeModal();
                            success();
                            window.location.href = response.url || '/am/building';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'บันทึกไม่สำเร็จ',
                                text: response.message || 'กรุณาตรวจสอบข้อมูลแล้วลองอีกครั้ง'
                            });
                        }
                    },
                    error: function (xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองอีกครั้ง';
                        Swal.fire({
                            icon: 'error',
                            title: 'บันทึกไม่สำเร็จ',
                            text: message
                        });
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
