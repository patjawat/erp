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

    /* รูปภาพทรัพย์สิน: preview/upload — scoped ไม่กระทบฟอร์มอื่นที่ id ซ้ำกัน (land/structure/building) */
    #form-asset {
        --photo-ink-2: #4a5568;   /* label, ผ่าน contrast 7.1:1 บน surface */
        --photo-ink-3: #64748b;   /* hint/caption, ผ่าน contrast 4.5:1 บน surface (ไม่ใช้ ink-4 #a0aec0 เพราะตกที่ ~2.1:1) */
        --photo-ink-4: #a0aec0;   /* decorative icon เท่านั้น ไม่ใช้กับตัวอักษร */
        --photo-surface-2: #f7f9fc;
        --photo-line: rgba(15, 23, 42, .08);
        --photo-line-strong: rgba(15, 23, 42, .14);
        --photo-primary: #0d6efd;
        --photo-primary-soft: rgba(13, 110, 253, .35);
        --photo-danger: #b91c1c;
    }

    #form-asset .asset-photo {
        display: flex;
        flex-direction: column;
    }

    #form-asset .file-file-preview {
        margin-top: 0;
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        height: auto;
        max-height: 280px;
        border-radius: 10px;
        overflow: hidden;
        background-color: var(--photo-surface-2);
        border: 1px solid var(--photo-line);
    }

    #form-asset .file-file-preview img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        padding: .5rem;
    }

    #form-asset .file-remove {
        position: absolute;
        top: .5rem;
        right: .5rem;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
        color: var(--photo-ink-2);
        border: 1px solid var(--photo-line);
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .12);
        cursor: pointer;
        transition: background-color 120ms cubic-bezier(.16, 1, .3, 1), color 120ms cubic-bezier(.16, 1, .3, 1), border-color 120ms cubic-bezier(.16, 1, .3, 1);
    }

    #form-asset .file-remove:hover {
        background-color: var(--photo-danger);
        border-color: var(--photo-danger);
        color: #fff;
    }

    #form-asset .file-remove:focus-visible {
        outline: 3px solid var(--photo-primary-soft);
        outline-offset: 2px;
    }

    #form-asset .file-remove i {
        font-size: 1.15rem;
        line-height: 1;
    }

    #form-asset .file-upload-btn {
        height: auto;
        min-height: 160px;
        aspect-ratio: 4 / 3;
        max-height: 280px;
        border-radius: 10px;
        border-color: var(--photo-line-strong);
        background-color: var(--photo-surface-2);
        transition: border-color 180ms cubic-bezier(.16, 1, .3, 1), background-color 180ms cubic-bezier(.16, 1, .3, 1);
    }

    #form-asset .file-upload-btn:hover {
        border-color: var(--photo-primary);
        background-color: rgba(13, 110, 253, .04);
    }

    #form-asset .file-upload:focus-within .file-upload-btn {
        border-color: var(--photo-primary);
        outline: 3px solid var(--photo-primary-soft);
        outline-offset: 2px;
    }

    #form-asset .file-upload-btn__icon {
        font-size: 1.75rem;
        color: var(--photo-ink-4);
        margin-bottom: .5rem;
        transition: color 180ms cubic-bezier(.16, 1, .3, 1);
    }

    #form-asset .file-upload-btn:hover .file-upload-btn__icon {
        color: var(--photo-primary);
    }

    #form-asset .file-upload-btn__text {
        font-size: .85rem;
        font-weight: 600;
        color: var(--photo-ink-2);
    }

    #form-asset .file-upload-btn__hint {
        display: block;
        margin-top: .35rem;
        font-size: .72rem;
        color: var(--photo-ink-3);
    }

    /* offcanvas จัดการหมวดหมู่: เว้นด้านบนให้พ้น header สูง 64px (.header-fixed ของธีม) ไม่ให้ทับหัว offcanvas เอง */
    #category-manage-offcanvas {
        top: 72px;
        height: calc(100% - 72px);
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
                <div class="asset-photo mb-0">
                    <div class="file-file-preview" id="editImagePreview" data-isfile="<?= $model->showImg()['isFile'] ?>" data-newfile="false">
                        <?= Html::img($model->showImg()['image'], ['id' => 'editPreviewImg', 'alt' => 'รูปภาพทรัพย์สิน']) ?>
                        <button type="button" class="file-remove" id="editRemoveImage" aria-label="ลบรูปภาพ" title="ลบรูปภาพ">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="file-upload">
                        <div class="file-upload-btn" id="editUploadBtn">
                            <i class="bi bi-cloud-arrow-up file-upload-btn__icon"></i>
                            <span class="file-upload-btn__text">คลิกหรือลากไฟล์มาวางที่นี่</span>
                            <small class="file-upload-btn__hint">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 5MB</small>
                        </div>
                        <input type="file" class="file-upload-input" id="my_file" accept="image/*" aria-label="อัปโหลดรูปภาพทรัพย์สิน">
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
                            $vendorAddUrl = Url::to(['/am/vendor/create', 'quick' => 1, 'title' => '<i class="fa-solid fa-plus me-1"></i> เพิ่มผู้ขาย/ผู้จำหน่าย/ผู้บริจาคใหม่']);
                            $vendorManageUrl = Url::to(['/am/vendor/index']);
                            $vendorActions = '<div class="vendor-quick-actions">'
                                . Html::a('<i class="fa-solid fa-plus"></i><span>เพิ่มผู้ขายใหม่</span>', $vendorAddUrl, [
                                    'class' => 'vendor-quick-actions__add open-modal',
                                    'data-size' => 'modal-md',
                                    'id' => 'btn-add-vendor-quick',
                                ])
                                . Html::a('จัดการรายชื่อ<i class="fa-solid fa-up-right-from-square"></i>', $vendorManageUrl, [
                                    'class' => 'vendor-quick-actions__manage',
                                    'target' => '_blank',
                                    'rel' => 'noopener',
                                    'title' => 'เปิดหน้าจัดการในแท็บใหม่',
                                ])
                                . Html::a('<i class="fa-solid fa-arrows-rotate"></i>รีเฟรชรายชื่อ', '#', [
                                    'class' => 'vendor-quick-actions__refresh',
                                    'id' => 'btn-refresh-vendors',
                                    'title' => 'โหลดรายชื่อผู้ขายล่าสุดจากระบบ',
                                ])
                                . '</div>';
                            echo $form->field($model, 'data_json[vendor_id]', [
                                'template' => '{label}{input}{hint}{error}' . $vendorActions,
                            ])->widget(Select2::classname(), [
                                'data' => $model->ListVendor(),
                                'options' => ['placeholder' => 'เลือกผู้จำหน่าย/ผู้ขาย', 'id' => 'asset-data_json-vendor_id'],
                                'pluginOptions' => ['allowClear' => true],
                                'pluginEvents' => [
                                    "select2:select" => "function(result) { var data = $(this).select2('data')[0]; $('#asset-data_json-vendor_text').val(data.text); }",
                                ]
                            ])->label('ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค');
                            ?>
                            <style>
                                .vendor-quick-actions {
                                    display: inline-flex; flex-wrap: wrap; align-items: center;
                                    gap: 0.15rem 0.85rem;
                                    margin-top: 0.4rem;
                                    font-size: 0.8rem;
                                }
                                .vendor-quick-actions__add,
                                .vendor-quick-actions__manage,
                                .vendor-quick-actions__refresh {
                                    display: inline-flex; align-items: center; gap: 0.32rem;
                                    padding: 0.15rem 0;
                                    text-decoration: none;
                                    transition: color 120ms cubic-bezier(0.16, 1, 0.3, 1);
                                    font-weight: 500;
                                }
                                .vendor-quick-actions__add { color: #0a58ca; }
                                .vendor-quick-actions__add:hover { color: #084298; text-decoration: underline; text-underline-offset: 3px; }
                                .vendor-quick-actions__add i { font-size: 0.72rem; }
                                .vendor-quick-actions__manage { color: #4a5568; }
                                .vendor-quick-actions__manage:hover { color: #1a202c; text-decoration: underline; text-underline-offset: 3px; }
                                .vendor-quick-actions__manage i { font-size: 0.7rem; opacity: 0.7; }
                                .vendor-quick-actions__refresh { color: #4a5568; }
                                .vendor-quick-actions__refresh:hover { color: #0a58ca; text-decoration: underline; text-underline-offset: 3px; }
                                .vendor-quick-actions__refresh i { font-size: 0.72rem; }
                                .vendor-quick-actions a:focus-visible {
                                    outline: none;
                                    box-shadow: 0 0 0 3px rgba(13,110,253,.18);
                                    border-radius: 4px;
                                }
                            </style>
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
                <h6 class="text-uppercase text-secondary m-0">ค่าเสื่อมราคา (จากหมวดทรัพย์สิน)</h6>
            </div>
            <div class="card-body">
                <div class="form-section mb-0">
                    <p class="text-muted small mb-3">กำหนดที่หมวดทรัพย์สิน — แก้ไขได้ที่หน้าจัดการหมวดหมู่</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label text-secondary small mb-1">อายุการใช้งาน (ปี)</label>
                            <div class="fw-semibold fs-5" id="deprec-useful-life"><?= $model->useful_life !== null && $model->useful_life !== '' ? Html::encode($model->useful_life) : '—' ?></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary small mb-1">อัตราค่าเสื่อม</label>
                            <div class="fw-semibold fs-5"><span id="deprec-rate"><?= $model->depreciation_rate !== null && $model->depreciation_rate !== '' ? Html::encode($model->depreciation_rate) : '—' ?></span> %</div>
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

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="shield-alert"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">การประเมินความเสี่ยง</h6>
            </div>
            <div class="card-body">
                <?php
                $assetDataJson = $model->data_json;
                if (is_string($assetDataJson)) {
                    $assetDataJson = json_decode($assetDataJson, true) ?: [];
                }
                $assetRiskHint = 'ระบบจะอัพเดตค่านี้อัตโนมัติเมื่อมีการบันทึกผลสอบเทียบครั้งใหม่ที่ระบุระดับความเสี่ยง';
                if (is_array($assetDataJson) && ($assetDataJson['risk_level_source'] ?? null) === 'calibration') {
                    $syncedAt = $assetDataJson['risk_level_synced_at'] ?? null;
                    $assetRiskHint = 'ค่าปัจจุบันมาจากผลสอบเทียบครั้งล่าสุด'
                        . ($syncedAt ? ' (อัพเดตเมื่อ ' . Yii::$app->thaiDate->toThaiDate($syncedAt, true, true) . ')' : '');
                }
                ?>
                <?= $this->render('@app/modules/am/views/_partials/_risk_chips', [
                    'name'  => 'Asset[risk_level]',
                    'id'    => 'asset-risk_level',
                    'value' => $model->risk_level,
                    'label' => 'ระดับความเสี่ยงของทรัพย์สิน',
                    'hint'  => $assetRiskHint,
                ]) ?>
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
                            echo $form->field($model, 'asset_category_id', [
                                'addon' => [
                                    'append' => [
                                        'content' => Html::button('<i data-lucide="settings"></i>', [
                                            'type' => 'button',
                                            'class' => 'btn btn-sm btn-secondary',
                                            'title' => 'จัดการหมวดหมู่ครุภัณฑ์',
                                            'data-bs-toggle' => 'offcanvas',
                                            'data-bs-target' => '#category-manage-offcanvas',
                                            'aria-controls' => 'category-manage-offcanvas',
                                        ]),
                                        'asButton' => true,
                                    ],
                                ],
                            ])->widget(DepDrop::class, [
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
                            <?php
                            echo $form->field($model, 'fsn_number', [
                                'addon' => [
                                    'append' => ['content' => Html::a('<i class="fa-solid fa-wand-magic-sparkles me-1"></i> สร้างหมายเลข', '#', ['class' => 'btn btn-outline-primary next-code', 'title' => 'สร้างหมายเลขครุภัณฑ์อัตโนมัติจากหมวดที่เลือก']), 'asButton' => true]
                                ]
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'เลือกหมวด + ปีงบ แล้วกดสร้างหมายเลข',
                                'class' => 'form-control'
                            ])->label('หมายเลขครุภัณฑ์')->hint('ระบบสร้างจากหมวดที่เลือก · แก้ไขได้', ['class' => 'form-text text-muted small']); ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'gfmis')->textInput(['maxlength' => true, 'placeholder' => 'รหัสโครงสร้างงบประมาณ (GFMIS)'])->label('รหัสโครงสร้างงบประมาณ(GFMIS)') ?>
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
                            ])->label('สถานะสุขภาพครุภัณฑ์')->hint('ดึงข้อมูลสถานะจาก `ListAssetStatus()`'); ?>
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

<div class="offcanvas offcanvas-end" tabindex="-1" id="category-manage-offcanvas" aria-labelledby="category-manage-offcanvas-label">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="category-manage-offcanvas-label">
            <i class="bi bi-tags"></i> จัดการหมวดหมู่ครุภัณฑ์
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body">
        <div class="text-center text-muted py-4">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <div class="small mt-2">กำลังโหลดรายการ...</div>
        </div>
    </div>
</div>

<?php
$ref = Json::encode($model->ref);
$urlUpload = Url::to('/filemanager/uploads/single');
$nextAssetNumberUrl = Url::to(['/am/equip/next-asset-number']);
$vendorOptionsUrl = Url::to(['/am/vendor/options']);
$categoryDefaultsUrl = Url::to(['/am/asset-item/category-defaults']);
$categoryQuickListUrl = Url::to(['/am/asset-category/quick-list']);
$categoryQuickListItemsUrl = Url::to(['/am/asset-category/quick-list-items']);

$js = <<< JS

 thaiDatepicker('#asset-receive_date,#asset-data_json-expire_date,#asset-data_json-inspection_date')
 isFile()

 // prefix (FSN) มาจากหมวดทรัพย์สินที่เลือก — ค่า = categorise.code
 function currentAssetPrefix() {
    return ($('#asset_category_id').val() || '').toString().trim();
 }
 function currentAssetNumber() {
    return ($('#asset-fsn_number').val() || '').toString().trim();
 }

 function fetchNextAssetNumber(showConfirm) {
    var prefix = currentAssetPrefix();
    var onYear = ($('#asset-on_year').val() || '').toString().trim();
    $('#form-asset .is-invalid').removeClass('is-invalid');
    if (!prefix) {
        $('#asset_category_id').addClass('is-invalid');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'กรุณาเลือกหมวดทรัพย์สินก่อน', showConfirmButton: false, timer: 3000 });
        }
        return;
    }
    if (!onYear) {
        $('#asset-on_year').addClass('is-invalid');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'กรุณาระบุปีงบประมาณก่อน', showConfirmButton: false, timer: 3000 });
        }
        return;
    }
    $.get('$nextAssetNumberUrl', { category_id: prefix, on_year: onYear }, function (res) {
        if (res.error) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: res.error, showConfirmButton: false, timer: 3000 });
            }
            return;
        }
        if (res.asset_number) {
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
                        $('#asset-fsn_number').val(res.asset_number).removeClass('is-invalid');
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'กำหนดหมายเลขแล้ว', showConfirmButton: false, timer: 2000 });
                    }
                });
            } else {
                $('#asset-fsn_number').val(res.asset_number).removeClass('is-invalid');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'สร้างหมายเลขครุภัณฑ์แล้ว', showConfirmButton: false, timer: 2000 });
                }
            }
        }
    }, 'json');
 }

 // แสดงค่าเสื่อม (read-only) จากหมวดทรัพย์สินที่เลือก
 function loadCategoryDeprecation(prefix) {
    if (!prefix) {
        $('#deprec-useful-life').text('—');
        $('#deprec-rate').text('—');
        return;
    }
    $.get('$categoryDefaultsUrl', { code: prefix }, function (res) {
        if (res && res.status === 'success') {
            var d = res.defaults || {};
            var ul = (d.useful_life !== null && d.useful_life !== undefined && d.useful_life !== '') ? d.useful_life : '—';
            var dr = (d.depreciation_rate !== null && d.depreciation_rate !== undefined && d.depreciation_rate !== '') ? d.depreciation_rate : '—';
            $('#deprec-useful-life').text(ul);
            $('#deprec-rate').text(dr);
        }
    }, 'json');
 }

 $('#asset-on_year').on('blur', function () {
    var hasYear = ($(this).val() || '').toString().trim();
    if (currentAssetPrefix() && hasYear && !currentAssetNumber()) { fetchNextAssetNumber(false); }
 });
 $('.next-code').on('click', function (e) {
    e.preventDefault();
    fetchNextAssetNumber(true);
 });

 // เมื่อเลือกหมวดทรัพย์สิน: อัปเดตค่าเสื่อม (read-only) + สร้างหมายเลขให้ถ้ามีปีงบและยังไม่มีหมายเลข
 $('#asset_category_id').on('change', function () {
    var prefix = ($(this).val() || '').toString().trim();
    loadCategoryDeprecation(prefix);
    if (!prefix) { return; }
    $(this).removeClass('is-invalid');
    var hasYear = ($('#asset-on_year').val() || '').toString().trim();
    if (hasYear && !currentAssetNumber()) { fetchNextAssetNumber(false); }
 });

 // -- จัดการหมวดหมู่ครุภัณฑ์ผ่าน offcanvas: โหลด shell (ตัวกรอง+รายการ), ค้นหา/กรองประเภทแบบ server-side, ลบ, รีเฟรชหลังเพิ่ม/แก้ไข --
 // cache: false เสมอ เพราะ \$.get() ปกติ browser cache ผล GET ซ้ำ ทำให้เห็นข้อมูลเก่าหลังแก้ไขจนกว่าจะ reload หน้าเพจ
 // โหลด shell ทั้งชุด (ตัวกรองประเภท + ช่องค้นหา + รายการ) ใช้ตอนเปิด offcanvas ครั้งแรกเท่านั้น
 function loadCategoryQuickList() {
    $.ajax({
        url: '$categoryQuickListUrl',
        type: 'GET',
        dataType: 'json',
        cache: false
    }).done(function (res) {
        if (res && res.content) {
            $('#category-manage-offcanvas .offcanvas-body').html(res.content);
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        }
    });
 }

 // โหลดเฉพาะแถวรายการ (ไม่แตะช่องค้นหา/dropdown กรอง) ใช้รีเฟรชตอนพิมพ์ค้นหา/เปลี่ยนตัวกรอง/ลบ/บันทึก
 function loadCategoryQuickListItems() {
    $.ajax({
        url: '$categoryQuickListItemsUrl',
        type: 'GET',
        dataType: 'json',
        cache: false,
        data: {
            'AssetCategorySearch[q]': $('.category-quick-list__search').val() || '',
            'AssetCategorySearch[category_id]': $('.category-quick-list__type-filter').val() || ''
        }
    }).done(function (res) {
        if (res && res.content) {
            $('.category-quick-list__items').html(res.content);
        }
    });
 }

 $('#category-manage-offcanvas').on('show.bs.offcanvas', function () {
    loadCategoryQuickList();
 });

 var categoryQuickSearchTimer = null;
 $(document).off('input.categoryQuickSearch', '.category-quick-list__search')
    .on('input.categoryQuickSearch', '.category-quick-list__search', function () {
        clearTimeout(categoryQuickSearchTimer);
        categoryQuickSearchTimer = setTimeout(loadCategoryQuickListItems, 300);
    });

 $(document).off('change.categoryQuickFilter', '.category-quick-list__type-filter')
    .on('change.categoryQuickFilter', '.category-quick-list__type-filter', function () {
        loadCategoryQuickListItems();
    });

 // ปุ่มรีเฟรชแบบกดเอง — ใช้ path เดียวกับตอนเปลี่ยน filter ประเภท (ยืนยันแล้วว่าทำงานถูกต้อง)
 // สำรองไว้เผื่อกรณีรีเฟรชอัตโนมัติหลังบันทึกไม่ทำงาน จะได้ยังมีทางดึงข้อมูลล่าสุดได้แน่นอน
 $(document).off('click.categoryQuickRefresh', '.category-quick-list__refresh')
    .on('click.categoryQuickRefresh', '.category-quick-list__refresh', function () {
        loadCategoryQuickListItems();
    });

 $(document).off('click.categoryQuickDelete', '.category-quick-list__delete')
    .on('click.categoryQuickDelete', '.category-quick-list__delete', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (typeof Swal === 'undefined') { return; }
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'ลบหมวดหมู่ครุภัณฑ์นี้ ไม่สามารถย้อนกลับได้',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then(function (result) {
            if (!result.isConfirmed) { return; }
            $.post(url, function (res) {
                if (res && res.status === 'success') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'ลบสำเร็จ', showConfirmButton: false, timer: 1800 });
                    loadCategoryQuickListItems();
                } else {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'ลบไม่สำเร็จ', showConfirmButton: false, timer: 2200 });
                }
            }, 'json');
        });
    });

 // รีเฟรช list ทุกครั้งที่ #main-modal ปิด ขณะ offcanvas เปิดอยู่ — ใช้ native Bootstrap event ('hidden.bs.modal')
 // โดยตรง ไม่พึ่ง custom event ('assetCategory:saved') ที่ยิงข้ามจาก asset-category/_form.php (สคริปต์ที่โหลดผ่าน
 // .open-modal แยกต่างหาก) เพื่อตัดจุดเสี่ยงที่ signal อาจไปไม่ถึง ครอบคลุมทั้งกรณีบันทึกสำเร็จและกดยกเลิก/ปิด
 $('#main-modal').on('hidden.bs.modal', function () {
    if ($('#category-manage-offcanvas').hasClass('show')) {
        loadCategoryQuickListItems();
    }
 });

 // เมื่อบันทึกหมวดหมู่ (เพิ่ม/แก้ไข) สำเร็จ — ใช้รีเฟรช dropdown "หมวดหมู่" หลักของฟอร์มครุภัณฑ์เพิ่มเติม
 // (การรีเฟรช list ใน offcanvas เองใช้ hidden.bs.modal ด้านบนแล้ว ไม่ต้องพึ่ง event นี้อีก)
 $(document).off('assetCategory:saved.assetForm').on('assetCategory:saved.assetForm', function () {
    var typeSelect = $('#asset_type_id');
    if (typeSelect.val()) { typeSelect.trigger('change'); }
 });

 // -- Vendor inline-add: refetch full list from server + rebuild Select2 + auto-select new --
 // Find the actual vendor <select> element. Prefer the explicit id, but fall back to
 // the [name] attribute so this stays robust even if the widget renames the id.
 function findVendorSelect() {
    var \$sel = \$('#asset-data_json-vendor_id');
    if (\$sel.length) return \$sel;
    var \$byName = \$('select[name="Asset[data_json][vendor_id]"]');
    if (\$byName.length) return \$byName;
    return \$();
 }

 function reloadVendorOptions(targetId, targetText, opts) {
    opts = opts || {};
    var silent = !!opts.silent;
    var \$sel = findVendorSelect();
    if (!\$sel.length) {
        console.warn('[vendor] select element not found');
        return;
    }
    var preserveVal = \$sel.val();
    var wanted = (targetId != null && targetId !== '') ? String(targetId)
                 : (preserveVal || '');
    console.log('[vendor] reload options', { url: '$vendorOptionsUrl', wanted: wanted });

    \$.ajax({
        url: '$vendorOptionsUrl',
        type: 'GET',
        dataType: 'json',
        cache: false
    }).done(function (items) {
        console.log('[vendor] got items', items && items.length);
        if (!Array.isArray(items)) { items = []; }

        \$sel.empty();
        \$sel.append(new Option('', '', false, false));   // placeholder for allowClear
        var matched = false;
        items.forEach(function (it) {
            var id = String(it.id);
            var isSel = id === String(wanted);
            if (isSel) { matched = true; }
            \$sel.append(new Option(it.text, id, isSel, isSel));
        });
        if (!matched && targetId && targetText) {
            \$sel.append(new Option(targetText, String(targetId), true, true));
            wanted = String(targetId);
            matched = true;
        }
        \$sel.val(wanted || null).trigger('change');
        if (targetText) {
            \$('#asset-data_json-vendor_text').val(targetText);
        }
        if (!silent && typeof Swal !== 'undefined' && targetId) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success',
                title: 'เพิ่มผู้ขายและเลือกใช้แล้ว',
                showConfirmButton: false, timer: 1800
            });
        }
    }).fail(function (xhr, status, err) {
        console.error('[vendor] options fetch failed', status, err, xhr && xhr.status);
        // Inline-append fallback so the new vendor is at least usable now
        if (targetId) {
            var idStr = String(targetId);
            if (!\$sel.find('option[value="' + idStr.replace(/"/g, '\\\\"') + '"]').length) {
                \$sel.append(new Option(targetText || idStr, idStr, true, true));
            }
            \$sel.val(idStr).trigger('change');
            if (targetText) {
                \$('#asset-data_json-vendor_text').val(targetText);
            }
        }
        if (!silent && typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'warning',
                title: 'โหลดรายชื่อล้มเหลว ใช้ค่าที่เพิ่มล่าสุดได้',
                showConfirmButton: false, timer: 2400
            });
        }
    });
 }
 window.reloadVendorOptions = reloadVendorOptions;

 \$(document).off('vendor:saved.assetForm').on('vendor:saved.assetForm', function (e, vendor) {
    console.log('[vendor] vendor:saved event received', vendor);
    var id = (vendor && vendor.id != null) ? vendor.id : null;
    var text = (vendor && vendor.text != null) ? vendor.text : null;
    reloadVendorOptions(id, text);
 });

 // Manual refresh link handler
 \$(document).off('click.vendorRefresh', '#btn-refresh-vendors')
            .on('click.vendorRefresh', '#btn-refresh-vendors', function (e) {
    e.preventDefault();
    reloadVendorOptions(null, null, { silent: false });
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



