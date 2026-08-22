<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocTemplate;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Doc $model */
/** @var app\modules\purchase\models\DocTemplate[] $templates */

/**
 * ฟอร์มสร้างเอกสาร
 *
 * รายการเรื่องต้นทางถูกดึงจากเซิร์ฟเวอร์ตามแม่แบบที่เลือก ไม่ได้ render ทุกชุดมารอไว้
 * ล่วงหน้า เพราะแม่แบบต่างชนิดผูกกับตารางต่างกัน (ใบขอซื้อ/สัญญา/หลักประกัน) และ
 * การโหลดใบขอซื้อทั้งปีมารอเปล่า ๆ ทำให้หน้านี้หนักโดยไม่ได้ใช้
 */

$templateOptions = [];
$templateMeta = [];
foreach ($templates as $template) {
    $templateOptions[$template->id] = $template->name;
    $templateMeta[$template->id] = [
        'name' => $template->name,
        'ref_type' => $template->ref_type,
        'ref_label' => $template->refTypeName(),
        'law_ref' => (string) $template->law_ref,
        'note' => (string) $template->note,
    ];
}

$form = ActiveForm::begin([
    'id' => 'doc-form',
    'options' => ['autocomplete' => 'off'],
]);
?>

<div class="row g-3">
    <div class="col-lg-6">
        <?= $form->field($model, 'template_id')->dropDownList($templateOptions, [
            'prompt' => '— เลือกแม่แบบเอกสาร —',
            'id' => 'doc-template-id',
        ])->label('แม่แบบเอกสาร') ?>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            <label class="form-label" for="doc-ref-id">
                เรื่องต้นทาง <span class="text-muted small" id="doc-ref-label"></span>
            </label>
            <?= Html::activeHiddenInput($model, 'ref_type', ['id' => 'doc-ref-type']) ?>
            <?= Html::activeDropDownList($model, 'ref_id', [], [
                'id' => 'doc-ref-id',
                'class' => 'form-select',
                'prompt' => '— เลือกแม่แบบก่อน —',
            ]) ?>
            <?= Html::error($model, 'ref_id', ['class' => 'invalid-feedback d-block']) ?>
            <div class="form-text" id="doc-ref-help">
                ระบบจะดึงเลขที่ วันที่ วงเงิน ชื่อผู้ขาย และรายการพัสดุจากเรื่องนี้มาเติมในเอกสารให้
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <?= $form->field($model, 'title')->textInput([
            'maxlength' => true,
            'placeholder' => 'เลือกแม่แบบแล้วระบบจะเติมชื่อให้',
        ])->hint('ชื่อนี้ใช้เรียกในทะเบียนเอกสาร แก้เป็นชื่อที่สื่อกว่านี้ได้') ?>
    </div>

    <div class="col-lg-3 col-6">
        <?= $form->field($model, 'doc_no')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น บข.001/2569',
        ]) ?>
    </div>

    <div class="col-lg-3 col-6">
        <?= $form->field($model, 'doc_date')->textInput([
            'type' => 'date',
        ]) ?>
    </div>

    <div class="col-lg-3 col-6">
        <?= $form->field($model, 'thai_year')->textInput(['type' => 'number']) ?>
    </div>

    <div class="col-lg-9">
        <?= $form->field($model, 'note')->textarea([
            'rows' => 2,
            'placeholder' => 'หมายเหตุภายใน ไม่ขึ้นบนกระดาษ',
        ]) ?>
    </div>
</div>

<div class="alert alert-secondary small d-none" id="doc-template-note"></div>

<div class="alert alert-light border small">
    <i class="bi bi-lightbulb me-1"></i>
    ค่าที่ระบบเติมให้เป็นเพียงค่าตั้งต้น หลังกดสร้าง ระบบจะเปิดกระดาษ A4 ให้แก้ได้ทุกจุด
    ก่อนพริ้นท์หรือส่งออก Word — ค่าที่หาไม่ได้จะขึ้นเป็นจุดไข่ปลาให้กรอกเอง
</div>

<div class="d-flex gap-2">
    <?= Html::submitButton('<i class="bi bi-magic me-1"></i>สร้างเอกสารและเปิดหน้าแก้ไข', [
        'class' => 'btn btn-success',
    ]) ?>
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$meta = json_encode($templateMeta, JSON_UNESCAPED_UNICODE);
$optionsUrl = Url::to(['/purchase/doc/ref-options']);
$noneType = DocTemplate::REF_NONE;
$selected = (int) $model->ref_id;

$js = <<<JS
var docTemplateMeta = {$meta};

function docLoadRefOptions(preselect) {
    var id = jQuery('#doc-template-id').val();
    var \$select = jQuery('#doc-ref-id');
    var meta = docTemplateMeta[id];

    jQuery('#doc-template-note').addClass('d-none').text('');

    if (!meta) {
        \$select.prop('disabled', true).html('<option value="">— เลือกแม่แบบก่อน —</option>');
        jQuery('#doc-ref-label').text('');
        return;
    }

    jQuery('#doc-ref-type').val(meta.ref_type);
    jQuery('#doc-ref-label').text('(' + meta.ref_label + ')');

    // เติมชื่อเอกสารให้ตั้งแต่เลือกแม่แบบ ไม่ปล่อยให้ว่างแล้วไปเติมที่ฝั่งเซิร์ฟเวอร์
    // เพราะ title เป็นช่องบังคับ ตัวตรวจฝั่งเบราว์เซอร์ของ ActiveForm จะขึ้นเตือน
    // "กรุณากรอกชื่อเอกสาร" และกดส่งไม่ผ่านตั้งแต่ต้น ผู้ใช้จึงไม่มีทางไปถึงโค้ด
    // ที่เติมค่าให้ฝั่งเซิร์ฟเวอร์เลย — เขียนทับเฉพาะตอนที่ยังว่าง จะได้ไม่ลบชื่อที่ผู้ใช้แก้เอง
    var \$title = jQuery('#doc-title');
    if (\$title.length && jQuery.trim(\$title.val()) === '') {
        \$title.val(meta.name);
    }

    // ข้อความเตือนของแม่แบบ (เช่น ยังไม่ผ่านการตรวจทานจากงานพัสดุ) ต้องเห็นตอนเลือก
    // ไม่ใช่เห็นหลังพิมพ์ออกมาแล้ว
    var note = [meta.law_ref, meta.note].filter(function (t) { return t; }).join(' — ');
    if (note) {
        jQuery('#doc-template-note').removeClass('d-none').text(note);
    }

    if (meta.ref_type === '{$noneType}') {
        \$select.prop('disabled', true)
            .html('<option value="">— แม่แบบนี้ไม่ผูกเรื่อง กรอกเนื้อหาเองบนหน้าแก้ไข —</option>');
        jQuery('#doc-ref-help').text('แม่แบบนี้ไม่ดึงข้อมูลจากเรื่องใด');
        return;
    }

    jQuery('#doc-ref-help').text(
        'ระบบจะดึงเลขที่ วันที่ วงเงิน ชื่อผู้ขาย และรายการพัสดุจากเรื่องนี้มาเติมในเอกสารให้'
    );
    \$select.prop('disabled', true).html('<option value="">กำลังโหลด…</option>');

    jQuery.getJSON('{$optionsUrl}', {
        ref_type: meta.ref_type,
        thai_year: jQuery('#doc-thai_year').val()
    }).done(function (res) {
        var html = '<option value="">— เลือก' + meta.ref_label + ' —</option>';
        (res.options || []).forEach(function (row) {
            html += '<option value="' + row.id + '">' + jQuery('<div>').text(row.label).html() + '</option>';
        });
        \$select.prop('disabled', false).html(html);
        if (preselect) { \$select.val(String(preselect)); }
        if (!(res.options || []).length) {
            \$select.prop('disabled', true)
                .html('<option value="">ยังไม่มี' + meta.ref_label + 'ในปีงบที่เลือก</option>');
        }
    }).fail(function () {
        \$select.prop('disabled', true).html('<option value="">โหลดรายการไม่สำเร็จ</option>');
    });
}

jQuery('#doc-template-id').on('change', function () { docLoadRefOptions(null); });
jQuery('#doc-thai_year').on('change', function () { docLoadRefOptions(jQuery('#doc-ref-id').val()); });
docLoadRefOptions({$selected});
JS;

$this->registerJs($js, View::POS_READY);
?>
