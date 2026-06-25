<?php

use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\modules\hr\models\Employees;
use kartik\editors\Summernote;

$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');

$structureGroups = $model->ListStructureGroup();
$selectedGroup   = $model->data_json['structure_group'] ?? '';
$selectedLeaf    = $model->data_json['structure_type'] ?? '';
$selectedLeafTitle = $model->data_json['structure_type_name'] ?? '';
$selectedOtherNote = $model->data_json['structure_type_other'] ?? '';
$leafOptions       = $selectedGroup ? $model->ListStructureTypeByGroup($selectedGroup) : [];
$structureDefaults = $selectedLeaf ? $model->getStructureDefaults($selectedLeaf) : null;
?>

<style>
    .modal-footer {
        display: none !important;
    }

    /* ── ประเภทสิ่งปลูกสร้าง · card ───────────────────────────── */
    .struct-card .card-header {
        background: #fff;
    }

    .struct-card__label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.4rem;
        display: block;
    }

    /* seg-control: 3 chips สำหรับเลือกหมวด */
    .struct-seg {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
        padding: 0.3rem;
        background: #f7f9fc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 8px;
    }

    @media (max-width: 575.98px) {
        .struct-seg {
            grid-template-columns: 1fr;
        }
    }

    .struct-seg__item {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        min-height: 56px;
        padding: 0.55rem 0.7rem;
        text-align: center;
        line-height: 1.25;
        color: #1a202c;
        font-size: 0.875rem;
        font-weight: 500;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.14);
        border-radius: 6px;
        cursor: pointer;
        user-select: none;
        transition: border-color 120ms cubic-bezier(0.16, 1, 0.3, 1),
                    background-color 120ms cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 120ms cubic-bezier(0.16, 1, 0.3, 1),
                    transform 120ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .struct-seg__item:hover {
        background: #f1f5f9;
    }

    .struct-seg__radio {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .struct-seg__radio:focus-visible + .struct-seg__item,
    .struct-seg__item:focus-visible {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.18);
        border-color: #0d6efd;
        outline: none;
    }

    .struct-seg__radio:checked + .struct-seg__item {
        border-color: #0d6efd;
        color: #0a58ca;
        background: rgba(13, 110, 253, 0.08);
        font-weight: 600;
    }

    .struct-seg__item:active {
        transform: translateY(1px);
    }

    .struct-seg__radio:disabled + .struct-seg__item,
    .struct-seg__item.is-disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    /* ส่วน reveal ตอนเลือกหมวด */
    .struct-reveal {
        opacity: 0;
        transform: translateY(4px);
        max-height: 0;
        overflow: hidden;
        transition: opacity 180ms cubic-bezier(0.16, 1, 0.3, 1),
                    transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
                    max-height 180ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .struct-reveal.is-open {
        opacity: 1;
        transform: translateY(0);
        max-height: 220px;
    }

    /* hint แสดงค่า default ที่จะ auto-fill */
    .struct-hint {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        color: #15803d;
        background: rgba(21, 128, 61, 0.08);
        border: 1px solid rgba(21, 128, 61, 0.2);
        border-radius: 6px;
        padding: 0.55rem 0.75rem;
        line-height: 1.4;
    }

    .struct-hint--muted {
        color: #4a5568;
        background: #f7f9fc;
        border-color: rgba(15, 23, 42, 0.08);
    }

    .struct-hint__caption {
        color: #718096;
        font-size: 0.72rem;
        margin-top: 0.15rem;
    }

    /* flash ตอน auto-fill ที่ input ค่าเสื่อม */
    .struct-flash {
        animation: structFlash 240ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes structFlash {
        0%   { background-color: rgba(13, 110, 253, 0.18); }
        100% { background-color: transparent; }
    }

    /* override badge ที่ field ค่าเสื่อม */
    .struct-override-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.7rem;
        color: #718096;
        margin-left: 0.4rem;
    }

    /* undo toast */
    .struct-undo {
        position: fixed;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%) translateY(8px);
        z-index: 1080;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.55rem 1rem;
        background: #1a202c;
        color: #fff;
        font-size: 0.85rem;
        border-radius: 999px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.18);
        opacity: 0;
        pointer-events: none;
        transition: opacity 180ms cubic-bezier(0.16, 1, 0.3, 1),
                    transform 180ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .struct-undo.is-visible {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
        pointer-events: auto;
    }

    .struct-undo__btn {
        background: transparent;
        border: 0;
        color: #93c5fd;
        font-weight: 600;
        padding: 0.15rem 0.4rem;
        border-radius: 6px;
        cursor: pointer;
    }

    .struct-undo__btn:hover { color: #fff; }
    .struct-undo__btn:focus-visible { outline: 2px solid #93c5fd; outline-offset: 2px; }

    @media (prefers-reduced-motion: reduce) {
        .struct-seg__item,
        .struct-reveal,
        .struct-undo { transition: opacity 80ms linear !important; }
        .struct-flash { animation: none; }
        .struct-reveal { transform: none; }
    }
</style>
<?php $form = ActiveForm::begin([
    'id' => 'form-asset',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/am/asset/validator'],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-1 mr-2 me-2']] // spacing form field groups
]); ?>

<?= $form->field($model, 'asset_item_id')->hiddenInput()->label(false); ?>
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

    </div>
    <div class="col-8">

        <!-- ─── ประเภทสิ่งปลูกสร้าง (cascade) ─────────────────────────── -->
        <div class="card struct-card mb-3">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i data-lucide="layers"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ประเภทสิ่งปลูกสร้าง</h6>
            </div>
            <div class="card-body">
                <!-- ค่าที่ส่งจริงตอน submit (เก็บลง data_json) -->
                <input type="hidden" name="Asset[data_json][structure_group]"      id="structure-group-input"       value="<?= Html::encode($selectedGroup) ?>">
                <input type="hidden" name="Asset[data_json][structure_group_name]" id="structure-group-name-input"  value="<?= Html::encode($structureGroups[$selectedGroup] ?? '') ?>">
                <input type="hidden" name="Asset[data_json][structure_type]"       id="structure-type-input"        value="<?= Html::encode($selectedLeaf) ?>">
                <input type="hidden" name="Asset[data_json][structure_type_name]"  id="structure-type-name-input"   value="<?= Html::encode($selectedLeafTitle) ?>">

                <!-- ── ขั้น 1: หมวด ────────────────────────────────── -->
                <label class="struct-card__label">หมวด</label>
                <div class="struct-seg" role="radiogroup" aria-label="หมวดสิ่งปลูกสร้าง" id="structure-seg">
                    <?php foreach ($structureGroups as $code => $title): ?>
                        <?php $rid = 'structure-grp-' . Html::encode($code); ?>
                        <input class="struct-seg__radio" type="radio" name="structure_group_radio" id="<?= $rid ?>"
                               value="<?= Html::encode($code) ?>"
                               data-title="<?= Html::encode($title) ?>"
                               <?= $selectedGroup === $code ? 'checked' : '' ?>>
                        <label class="struct-seg__item" for="<?= $rid ?>" tabindex="0" role="radio"
                               aria-checked="<?= $selectedGroup === $code ? 'true' : 'false' ?>">
                            <?= Html::encode($title) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- ── ขั้น 2: leaf (เผยเมื่อเลือกหมวด) ─────────────── -->
                <div class="struct-reveal mt-3 <?= $selectedGroup ? 'is-open' : '' ?>" id="structure-leaf-wrap">
                    <label class="struct-card__label" for="structure-leaf-select">รายการ</label>
                    <select class="form-select" id="structure-leaf-select" data-placeholder="เลือกรายการ">
                        <option value=""></option>
                        <?php foreach ($leafOptions as $code => $title): ?>
                            <option value="<?= Html::encode($code) ?>" <?= $selectedLeaf === $code ? 'selected' : '' ?>>
                                <?= Html::encode($title) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ── ขั้น 2b: ระบุเพิ่มเติม (เผยเมื่อ leaf มี allow_other_note) -->
                <div class="struct-reveal mt-3 <?= !empty($structureDefaults['allow_other_note']) ? 'is-open' : '' ?>" id="structure-other-wrap">
                    <label class="struct-card__label" for="structure-other-input">ระบุประเภทเพิ่มเติม</label>
                    <input type="text" class="form-control" name="Asset[data_json][structure_type_other]" id="structure-other-input"
                           value="<?= Html::encode($selectedOtherNote) ?>"
                           placeholder="เช่น ป้อมยาม, ห้องเก็บของชั่วคราว" maxlength="255">
                </div>

                <!-- ── hint ค่าเสื่อม ──────────────────────────────── -->
                <div class="struct-hint <?= $structureDefaults ? '' : 'struct-hint--muted' ?> mt-3" id="structure-hint">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <div id="structure-hint-text">
                            <?php if ($structureDefaults && ($structureDefaults['useful_life'] !== null || $structureDefaults['depreciation_rate'] !== null)): ?>
                                อายุการใช้งาน <strong><?= (int) $structureDefaults['useful_life'] ?></strong> ปี ·
                                อัตราค่าเสื่อม <strong><?= number_format((float) $structureDefaults['depreciation_rate'], 2) ?></strong>%
                            <?php else: ?>
                                เลือกหมวดและรายการ เพื่อเติมค่าเสื่อมอัตโนมัติ
                            <?php endif; ?>
                        </div>
                        <div class="struct-hint__caption">
                            ค่าจะถูกเติมที่การ์ด "เตรียมข้อมูลค่าเสื่อม" และผู้ใช้แก้ไขเองได้ตลอด
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
                <h6 class="text-uppercase text-secondary m-0">ข้อมูลพื้นฐาน</h6>
            </div>
            <div class="card-body">
                <!-- ข้อมูลทั่วไป -->
                <div class="form-section">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?php
                            echo $form->field($model, 'asset_name', [
                                'addon' => [
                                    'append' => ['content' => Html::a('<i class="fa-solid fa-magnifying-glass"></i>', ['/am/asset-item/list-item', 'title' => '<i class="bi bi-ui-checks"></i> แสดงทะเบียนรหัสทรัพย์สิน'], ['class' => 'btn btn-secondary open-modal', 'data' => ['size' => 'modal-xl']]), 'asButton' => true]
                                ]
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'ระบุชื่อสิ่งปลูกสร้าง',
                                'readonly' => false,  // Make field readonly
                                'class' => 'form-control'  // Add background color
                            ])->label('ชื่อสิ่งปลูกสร้าง');
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                            echo $form->field($model, 'code', [
                                'addon' => [
                                    'append' => ['content' => Html::a('<i class="fa-solid fa-bars-progress"></i>', ['/am/asset/next-code'], ['class' => 'btn btn-info next-code']), 'asButton' => true]
                                ]
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'ค้นหาเลข FSN',
                                'readonly' => false,  // Make field readonly
                                // 'class' => 'form-control bg-primary text-white'  // Add background color
                                'class' => 'form-control'  // Add background color
                            ])->label('หมายเลขครุภัณฑ์'); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'gfmis')->textInput(['maxlength' => true, 'placeholder' => 'รหัสโครงสร้างงบประมาณ (GFMIS)'])->label('รหัสโครงสร้างงบประมาณ(GFMIS)') ?>
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
                            <?= $form->field($model, 'data_json[location]')->textInput()->label('สถานที่ตั้ง/อาคาร/ห้อง'); ?>
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
                                'pluginEvents' => [
                                    // "select2:select" => "function(result) { 
                                    //     var data = $(this).select2('data')[0]
                                    //     $('#asset-data_json-method_get_text').val(data.text)
                                    //  }",
                                ]
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
                        </div>
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

<!-- Undo toast (เผยเมื่อเปลี่ยนหมวดทับของเดิม) -->
<div class="struct-undo" id="structure-undo" role="status" aria-live="polite">
    <span id="structure-undo-text">ยกเลิกการเปลี่ยนหมวด</span>
    <button type="button" class="struct-undo__btn" id="structure-undo-btn">ย้อนกลับ</button>
</div>


</div>
</div>



<?php
$ref = Json::encode($model->ref); // ปลอดภัยแม้มีอักขระพิเศษ
$urlUpload = Url::to('/filemanager/uploads/single');

$js = <<< JS

 //กำหนดให้ปฏิทินแสดงวันที่
 thaiDatepicker('#asset-receive_date,#asset-data_json-expire_date,#asset-data_json-inspection_date')


 isFile()

/* ───────────────────────────────────────────────────────────
 *  Structure type cascade — หมวด → leaf → auto-fill ค่าเสื่อม
 * ─────────────────────────────────────────────────────────── */
(function () {
    const \$seg            = \$('#structure-seg');
    const \$leafWrap       = \$('#structure-leaf-wrap');
    const \$leafSelect     = \$('#structure-leaf-select');
    const \$otherWrap      = \$('#structure-other-wrap');
    const \$otherInput     = \$('#structure-other-input');
    const \$hintText       = \$('#structure-hint-text');
    const \$hint           = \$('#structure-hint');

    const \$groupCodeIn    = \$('#structure-group-input');
    const \$groupNameIn    = \$('#structure-group-name-input');
    const \$typeCodeIn     = \$('#structure-type-input');
    const \$typeNameIn     = \$('#structure-type-name-input');

    const \$life           = \$('#asset-useful_life');
    const \$rate           = \$('#asset-depreciation_rate');

    const \$undo           = \$('#structure-undo');
    const \$undoBtn        = \$('#structure-undo-btn');

    // จำสภาพก่อนถูกเปลี่ยน (สำหรับ undo)
    let lastSnapshot = null;
    let undoTimer = null;

    // Init Select2 บน leaf
    if (\$leafSelect.length && typeof \$leafSelect.select2 === 'function') {
        \$leafSelect.select2({
            placeholder: 'เลือกรายการ',
            allowClear: true,
            width: '100%',
        });
    }

    // ── helper: snapshot ปัจจุบัน
    function snapshot() {
        return {
            groupCode: \$groupCodeIn.val(),
            groupName: \$groupNameIn.val(),
            typeCode:  \$typeCodeIn.val(),
            typeName:  \$typeNameIn.val(),
            life:      \$life.val(),
            rate:      \$rate.val(),
            other:     \$otherInput.val(),
            checked:   \$seg.find('.struct-seg__radio:checked').val() || null,
            leafHtml:  \$leafSelect.html(),
        };
    }

    function restore(snap) {
        if (!snap) return;
        \$groupCodeIn.val(snap.groupCode);
        \$groupNameIn.val(snap.groupName);
        \$typeCodeIn.val(snap.typeCode);
        \$typeNameIn.val(snap.typeName);
        \$life.val(snap.life);
        \$rate.val(snap.rate);
        \$otherInput.val(snap.other);
        // restore radio
        \$seg.find('.struct-seg__radio').prop('checked', false);
        \$seg.find('.struct-seg__item').attr('aria-checked', 'false');
        if (snap.checked) {
            const \$r = \$seg.find('.struct-seg__radio[value="' + snap.checked + '"]');
            \$r.prop('checked', true);
            \$r.next('.struct-seg__item').attr('aria-checked', 'true');
            \$leafWrap.addClass('is-open');
        } else {
            \$leafWrap.removeClass('is-open');
        }
        // restore leaf options
        \$leafSelect.html(snap.leafHtml).trigger('change.select2');
        // refresh hint
        refreshHint();
    }

    function refreshHint(life, rate) {
        const L = life !== undefined ? life : \$life.val();
        const R = rate !== undefined ? rate : \$rate.val();
        if (L || R) {
            \$hint.removeClass('struct-hint--muted');
            \$hintText.html(
                'อายุการใช้งาน <strong>' + (L || '-') + '</strong> ปี · ' +
                'อัตราค่าเสื่อม <strong>' + (R ? parseFloat(R).toFixed(2) : '-') + '</strong>%'
            );
        } else {
            \$hint.addClass('struct-hint--muted');
            \$hintText.text('เลือกหมวดและรายการ เพื่อเติมค่าเสื่อมอัตโนมัติ');
        }
    }

    function flash(\$el) {
        if (!\$el.length) return;
        \$el.removeClass('struct-flash');
        // reflow
        void \$el[0].offsetWidth;
        \$el.addClass('struct-flash');
    }

    function showUndo() {
        \$undo.addClass('is-visible');
        if (undoTimer) clearTimeout(undoTimer);
        undoTimer = setTimeout(hideUndo, 5000);
    }
    function hideUndo() {
        \$undo.removeClass('is-visible');
        if (undoTimer) { clearTimeout(undoTimer); undoTimer = null; }
    }

    \$undoBtn.on('click', function () {
        restore(lastSnapshot);
        lastSnapshot = null;
        hideUndo();
    });

    // ── ขั้น 1: เลือกหมวด
    \$seg.on('change', '.struct-seg__radio', function () {
        const \$radio = \$(this);
        const code   = \$radio.val();
        const title  = \$radio.data('title') || '';

        // ถ้ามีของเดิม → snapshot สำหรับ undo
        const hadSelection = !!\$typeCodeIn.val() || !!\$groupCodeIn.val();
        if (hadSelection) {
            lastSnapshot = snapshot();
        }

        // อัปเดต aria-checked
        \$seg.find('.struct-seg__item').attr('aria-checked', 'false');
        \$radio.next('.struct-seg__item').attr('aria-checked', 'true');

        // เซ็ตค่ากลุ่ม
        \$groupCodeIn.val(code);
        \$groupNameIn.val(title);

        // clear leaf เดิม
        \$typeCodeIn.val('');
        \$typeNameIn.val('');
        \$otherWrap.removeClass('is-open');
        \$otherInput.val('');

        // โหลด leaf list
        \$.ajax({
            url: '/am/structure/structure-depdrop',
            type: 'POST',
            data: { group_code: code, _csrf: yii.getCsrfToken() },
            dataType: 'json',
        }).done(function (res) {
            if (res.status !== 'success') return;

            let html = '<option value=""></option>';
            (res.items || []).forEach(function (it) {
                html += '<option value="' + it.id + '">' + it.text + '</option>';
            });
            \$leafSelect.html(html).val('').trigger('change.select2');

            \$leafWrap.addClass('is-open');

            // auto-fill ค่าเสื่อมจาก group default (ถ้า user ยังไม่แก้)
            const d = res.defaults || {};
            if (d.useful_life != null && d.depreciation_rate != null) {
                \$life.val(d.useful_life);
                \$rate.val(parseFloat(d.depreciation_rate).toFixed(2));
                flash(\$life); flash(\$rate);
                refreshHint(d.useful_life, d.depreciation_rate);
            }

            if (hadSelection) showUndo();
        });
    });

    // ── ขั้น 2: เลือก leaf
    \$leafSelect.on('change', function () {
        const code = \$(this).val();
        const title = \$(this).find('option:selected').text().trim();

        \$typeCodeIn.val(code || '');
        \$typeNameIn.val(title || '');

        if (!code) {
            \$otherWrap.removeClass('is-open');
            \$otherInput.val('');
            return;
        }

        \$.ajax({
            url: '/am/structure/structure-leaf-defaults',
            type: 'POST',
            data: { leaf_code: code, _csrf: yii.getCsrfToken() },
            dataType: 'json',
        }).done(function (res) {
            if (res.status !== 'success') return;
            const d = res.defaults || {};

            // เปิด/ปิด "ระบุเพิ่มเติม" ตาม flag
            if (d.allow_other_note) {
                \$otherWrap.addClass('is-open');
            } else {
                \$otherWrap.removeClass('is-open');
                \$otherInput.val('');
            }

            // auto-fill ค่าเสื่อม (override ค่าจากกลุ่มเมื่อ leaf มีค่าเฉพาะ)
            if (d.useful_life != null) {
                \$life.val(d.useful_life);
                flash(\$life);
            }
            if (d.depreciation_rate != null) {
                \$rate.val(parseFloat(d.depreciation_rate).toFixed(2));
                flash(\$rate);
            }
            refreshHint(\$life.val(), \$rate.val());
        });
    });

    // ── update hint เมื่อ user override ด้วยมือ
    \$life.add(\$rate).on('input', function () {
        refreshHint(\$life.val(), \$rate.val());
    });
})();


 $('.next-code').on('click', function (e) { 
    e.preventDefault();
    $.ajax({
        type: "post",
        url: "/am/asset/next-code",
        data: $('#form-asset').serialize(),
        dataType: "json",
        success: function (response) {
            // ลบ class invalid เดิมทั้งหมดก่อน
            $('#form-asset .is-invalid').removeClass('is-invalid');

            if(response.status == 'error') {
                for (let inputId in response.data) {
                    let input = $('#' + inputId);

                    // เพิ่ม class invalid ให้ input
                    input.addClass('is-invalid');

                    // แสดง toast แค่แจ้งเตือน ไม่แสดงใต้ input
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'ต้องระบุ FSN ก่อน',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }

            if(response.status == 'success') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'ค้นหาหมายเลขครุภัณฑ์สำเร็จ',
                    showConfirmButton: false,
                    timer: 3000
                });

                // อัพเดตค่าใน input fsn_number
                $('#asset-code').val(response.data);
            }
        }
    });
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
