<?php

use yii\web\View;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;


/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);
$assetPicker = $model->listAssetForPicker();

$assetResultTemplate = <<<'JS'
function(item){
    if (!item || !item.id) { return item ? item.text : ''; }
    var $el = $(item.element);
    var img  = $el.data('image') || '';
    var name = $el.data('name')  || item.text || '';
    var code = $el.data('code')  || item.id;
    var type = $el.data('type')  || '';
    var esc  = function(s){ return $('<div/>').text(String(s == null ? '' : s)).html(); };
    var imgHtml = img
        ? '<img src="' + esc(img) + '" alt="" loading="lazy" class="asset-pick__img" />'
        : '<span class="asset-pick__img asset-pick__img--placeholder" aria-hidden="true"><i class="bi bi-box-seam"></i></span>';
    var typeHtml = type ? '<div class="asset-pick__type">' + esc(type) + '</div>' : '';
    return $(
        '<div class="asset-pick">' +
            imgHtml +
            '<div class="asset-pick__body">' +
                '<div class="asset-pick__name">' + esc(name) + '</div>' +
                '<div class="asset-pick__meta">' +
                    '<span class="asset-pick__code">' + esc(code) + '</span>' +
                    typeHtml +
                '</div>' +
            '</div>' +
        '</div>'
    );
}
JS;

$assetSelectionTemplate = <<<'JS'
function(item){
    if (!item || !item.id) { return item ? item.text : ''; }
    var $el = $(item.element);
    var img  = $el.data('image') || '';
    var name = $el.data('name')  || item.text || '';
    var code = $el.data('code')  || item.id;
    var esc  = function(s){ return $('<div/>').text(String(s == null ? '' : s)).html(); };
    var imgHtml = img
        ? '<img src="' + esc(img) + '" alt="" loading="lazy" class="asset-pick__img asset-pick__img--sm" />'
        : '<span class="asset-pick__img asset-pick__img--sm asset-pick__img--placeholder" aria-hidden="true"><i class="bi bi-box-seam"></i></span>';
    return $(
        '<span class="asset-pick asset-pick--selected">' +
            imgHtml +
            '<span class="asset-pick__body">' +
                '<span class="asset-pick__name">' + esc(name) + '</span>' +
                '<span class="asset-pick__code">' + esc(code) + '</span>' +
            '</span>' +
        '</span>'
    );
}
JS;

$this->registerCss(<<<CSS
.asset-pick { display: flex; align-items: center; gap: .75rem; min-height: 2.75rem; padding: .125rem 0; min-width: 0; }
.asset-pick--selected { display: inline-flex; gap: .5rem; align-items: center; min-height: 2rem; max-width: 100%; }
.asset-pick__img {
    width: 44px; height: 44px; flex: 0 0 44px;
    object-fit: cover; border-radius: .5rem;
    background: var(--bs-tertiary-bg, #f1f3f5);
    border: 1px solid var(--bs-border-color, #e9ecef);
    display: inline-flex; align-items: center; justify-content: center;
}
.asset-pick__img--sm { width: 26px; height: 26px; flex: 0 0 26px; border-radius: .375rem; }
.asset-pick__img--placeholder { color: var(--bs-secondary-color, #6c757d); font-size: 1.05rem; }
.asset-pick__img--sm.asset-pick__img--placeholder { font-size: .8rem; }
.asset-pick__body { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; gap: .15rem; }
.asset-pick--selected .asset-pick__body { flex-direction: row; align-items: baseline; gap: .5rem; min-width: 0; flex: 1 1 auto; }
.asset-pick__name {
    font-weight: 600; color: var(--bs-body-color, #212529);
    font-size: .9375rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.asset-pick--selected .asset-pick__name { font-weight: 500; font-size: .9rem; }
.asset-pick__meta { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.asset-pick__code {
    font-family: ui-monospace, SFMono-Regular, "JetBrains Mono", Menlo, Consolas, monospace;
    font-size: .8125rem;
    color: var(--bs-primary, #0d6efd);
    letter-spacing: .01em;
}
.asset-pick--selected .asset-pick__code {
    font-size: .75rem;
    background: rgba(var(--bs-primary-rgb, 13,110,253), .08);
    padding: .05rem .4rem; border-radius: .25rem;
}
.asset-pick__type {
    font-size: .75rem; color: var(--bs-secondary-color, #6c757d);
    position: relative; padding-left: .6rem;
}
.asset-pick__type::before {
    content: ""; position: absolute; left: 0; top: 50%;
    width: 3px; height: 3px; border-radius: 50%;
    background: currentColor; opacity: .45;
    transform: translateY(-50%);
}

/* Select2 row sizing + focus polish (theme-agnostic) */
.select2-results__option { padding: .5rem .75rem; }
.select2-results__option--highlighted[aria-selected],
.select2-results__option--highlighted {
    background-color: rgba(var(--bs-primary-rgb, 13,110,253), .09) !important;
    color: inherit !important;
}
.select2-results__option[aria-selected=true] {
    background-color: rgba(var(--bs-primary-rgb, 13,110,253), .14) !important;
    color: inherit !important;
}
.select2-container .select2-selection--single { min-height: 2.625rem; }
.select2-container--bootstrap-5 .select2-selection--single,
.select2-container--krajee-bs5 .select2-selection--single { min-height: 2.625rem; padding: .25rem .5rem; }
.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered,
.select2-container--krajee-bs5 .select2-selection--single .select2-selection__rendered {
    padding-left: .25rem; line-height: 1.4;
}

/* Motion: subtle scale on the highlighted row's image */
.asset-pick__img { transition: transform .18s cubic-bezier(.2,.7,.2,1), box-shadow .18s cubic-bezier(.2,.7,.2,1); }
.select2-results__option--highlighted .asset-pick__img {
    transform: scale(1.05);
    box-shadow: 0 2px 6px rgba(var(--bs-primary-rgb, 13,110,253), .18);
}
@media (prefers-reduced-motion: reduce) {
    .asset-pick__img { transition: none; }
    .select2-results__option--highlighted .asset-pick__img { transform: none; box-shadow: none; }
}
CSS, [], 'helpdesk-asset-picker');

?>

<?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/me/repair-v2/create-validator']
    ]); ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['value' => 'repair'])->label(false) ?>

<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-primary">
            <i class="bi bi-info-circle me-2"></i>
            กรุณากรอกข้อมูลให้ครบถ้วนเพื่อความรวดเร็วในการดำเนินการ
        </div>
    </div>

    <div class="col-12 col-md-6">
        <?=$form->field($model, 'device_type_id')->widget(Select2::classname(), [
                    'data' => $model->listDeviceType(),
                    'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ประเภทอุปกรณ์');
                ?>
    </div>

    <div class="col-12 col-md-6">
            <?= $form->field($model, 'asset_number')->widget(Select2::classname(), [
                'data' => $assetPicker['items'],
                'options' => array_merge(
                    [
                        'placeholder' => 'เลือกครุภัณฑ์ (ค้นหาจากชื่อหรือรหัส)...',
                        'aria-label' => 'เลือกครุภัณฑ์',
                    ],
                    $assetPicker['options']
                ),
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#main-modal',
                    'dropdownCssClass' => 'asset-pick__dropdown',
                    'minimumResultsForSearch' => 0,
                    'escapeMarkup' => new JsExpression('function(m){ return m; }'),
                    'templateResult' => new JsExpression($assetResultTemplate),
                    'templateSelection' => new JsExpression($assetSelectionTemplate),
                ],
                'pluginEvents' => [
                    "select2:select" => "function(e) { getRepairGroup(e.params.data.id); }",
                ],
            ])->label('รหัสครุภัณฑ์'); ?>
            <div class="form-text small text-muted mt-1">
                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                แนะนำให้ระบุครุภัณฑ์ เพื่อติดตามประวัติซ่อม/สอบเทียบและตัวชี้วัดชนิดเครื่องมือ
                <span class="text-secondary">(หากยังไม่ทราบ ช่างระบุภายหลังได้)</span>
            </div>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'title')->textArea(['rows' => 3, 'placeholder' => 'กรุณาอธิบายปัญหาที่พบโดยละเอียด...'])->label('รายละเอียดปัญหา') ?>
    </div>

    <div class="col-12 col-md-6">
        <?= $form->field($model, 'data_json[location]')->textInput(['placeholder'=>'เช่น ห้อง 301, แผนกบัญชี'])->label('สถานที่') ?>
    </div>
     <div class="col-12 col-md-6">
        <?= $form->field($model, 'data_json[phone]')->textInput(['placeholder'=>'เบอร์โทรศัพท์ติดต่อ'])->label('โทร') ?>
    </div>

    <div class="col-12 col-md-6">
        <?=$form->field($model, 'repair_group')->widget(Select2::classname(), [
                    'data' => $model->listRepairGroup(),
                    'options' => ['placeholder' => 'เลือกแผนกช่าง ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('แผนกช่าง');
                ?>

    </div>

    <div class="col-12 col-md-6">
        <?=$form->field($model, 'data_json[urgency]')->widget(Select2::classname(), [
                    'data' => $model->listUrgency(),
                    'options' => ['placeholder' => 'เลือกความเร่งด่วน ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ความเร่งด่วน');
                ?>

    </div>

    <div class="col-12 col-md-12">
        <?= $form->field($model, 'request_repair_date')->widget(\app\widgets\datepicker\DatepickerThai::class, [
            'options' => ['placeholder' => 'เลือกวันที่ต้องการให้ซ่อม'],
        ])->label('วันที่ต้องการให้ซ่อม'); ?>
    </div>

    <div class="col-12">
        <?=$model->upload('repair_request')?>

    </div>

    <div class="col-12" id="imagePreviewContainer" style="display: none;">
        <div class="d-flex flex-wrap gap-2 mt-2" id="imagePreview"></div>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5, 'placeholder' => 'ข้อมูลเพิ่มเติมที่อาจเป็นประโยชน์ต่อการซ่อม...'])->label('หมายเหตุเพิ่มเติม') ?>

    </div>

    <div class="col-12 d-flex justify-content-center mt-4 gap-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>
            บันทึก
        </button>
        <button type="button" class="btn btn-outline-secondary me-2">ยกเลิก</button>
    </div>
</div>
</div>

<?php ActiveForm::end(); ?>
<?php
$getRepairGroupUrl = Url::to(['/me/repair-v2/get-repair-group']);
$assetNumber = json_encode($model->asset_number);
$js = <<< JS

     if ($assetNumber) {
        getRepairGroup($assetNumber);
    }

    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
    function getRepairGroup(id)
    {
        $.ajax({
            type: "get",
            url: "$getRepairGroupUrl",
            data: {id:id},
            dataType: "json",
            success: function (response) {
                console.log(response);
               $('#helpdesk-repair_group').val(response).trigger('change');
                
            }
        });
    }

JS;
$this->registerJS($js,View::POS_END)
?>