<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use app\modules\hr\models\Organization;
use app\modules\am\models\AssetStatus;
use app\modules\am\models\AssetCondition;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var string $section */

$formId = 'bulk-edit-form';
?>

<?php $form = ActiveForm::begin([
    'id' => $formId,
    'action' => Url::to(['/am/equip/bulk-edit', 'section' => $section]),
    'enableClientValidation' => false,
    'options' => ['class' => 'bulk-edit-form'],
]); ?>

<div class="alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-3" style="font-size: 0.86rem;">
    <i class="fa-solid fa-circle-info"></i>
    <span>ค่าที่กำหนดจะถูกใช้กับ <strong class="bulk-edit-count">0</strong> รายการที่เลือกไว้</span>
</div>

<?php if ($section === 'category'): ?>

    <div class="mb-3">
        <?= $form->field($model, 'asset_type_id')->widget(Select2::class, [
            'data' => $model->listAssetType(),
            'options' => ['placeholder' => 'เลือกประเภท...', 'id' => 'bulk_asset_type_id'],
            'pluginOptions' => ['allowClear' => true, 'dropdownParent' => '#main-modal'],
        ])->label('ประเภทครุภัณฑ์') ?>
    </div>

    <div class="mb-2">
        <?= $form->field($model, 'asset_category_id')->widget(DepDrop::class, [
            'options' => ['id' => 'bulk_asset_category_id', 'placeholder' => 'เลือกหมวดหมู่...'],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => ['pluginOptions' => ['allowClear' => true, 'dropdownParent' => '#main-modal']],
            'pluginOptions' => [
                'depends' => ['bulk_asset_type_id'],
                'url' => Url::to(['/am/asset-item/get-asset-category']),
                'loadingText' => 'กำลังโหลด...',
                'initialize' => true,
            ],
        ])->label('หมวดหมู่ครุภัณฑ์ <span class="text-danger">*</span>') ?>
    </div>

<?php elseif ($section === 'assignment'): ?>

    <?php
    $departments = ArrayHelper::map(
        Organization::find()->orderBy('root, lft')->all(),
        'id',
        'name'
    );
    ?>

    <p class="text-body-secondary mb-3" style="font-size: 0.82rem;">
        <i class="fa-regular fa-lightbulb me-1"></i> เว้นช่องที่ไม่ต้องการเปลี่ยนไว้ว่าง — ระบบจะแก้เฉพาะช่องที่กรอก
    </p>

    <div class="mb-3">
        <?= $form->field($model, 'data_json[location]')->textInput([
            'placeholder' => 'อาคาร / ห้อง / คลัง',
            'value' => '',
        ])->label('สถานที่ตั้ง / คลัง / ห้อง') ?>
    </div>

    <div class="mb-3">
        <?= $form->field($model, 'department')->widget(Select2::class, [
            'data' => $departments,
            'options' => ['placeholder' => 'เลือกหน่วยงาน...'],
            'pluginOptions' => ['allowClear' => true, 'dropdownParent' => '#main-modal'],
        ])->label('หน่วยงานที่ตั้ง') ?>
    </div>

    <div class="mb-2">
        <?= $form->field($model, 'owner')->widget(Select2::class, [
            'options' => ['placeholder' => 'ค้นหาชื่อพนักงาน...'],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 1,
                'dropdownParent' => new JsExpression("$('#main-modal')"),
                'ajax' => [
                    'url' => Url::to(['/depdrop/employee']),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params){ return {q: params.term}; }'),
                ],
                'escapeMarkup' => new JsExpression('function(m){ return m; }'),
                'templateResult' => new JsExpression('function(d){ return d.text; }'),
                'templateSelection' => new JsExpression('function(d){ return d.text; }'),
            ],
        ])->label('ผู้รับผิดชอบ') ?>
    </div>

<?php elseif ($section === 'receive_date'): ?>

    <div class="mb-2">
        <?= $form->field($model, 'receive_date')->widget(\app\widgets\datepicker\DatepickerThai::class, [
            'options' => ['id' => 'bulk_receive_date', 'placeholder' => 'วว/ดด/ปปปป (พ.ศ.)', 'value' => ''],
        ])->label('วันที่รับเข้า <span class="text-danger">*</span>') ?>
    </div>

<?php elseif ($section === 'price'): ?>

    <div class="mb-2">
        <?= $form->field($model, 'price')->input('number', [
            'step' => '0.01',
            'min' => '0',
            'placeholder' => '0.00',
            'value' => '',
        ])->label('ราคาแรกรับ (บาท) <span class="text-danger">*</span>') ?>
    </div>

<?php elseif ($section === 'asset_condition'): ?>

    <?php
    $conditionItems = ArrayHelper::map(
        AssetCondition::find()->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
        'id',
        'name'
    );
    ?>
    <div class="mb-2">
        <?= $form->field($model, 'asset_condition')->dropDownList($conditionItems, [
            'prompt' => 'เลือกสภาพ...',
        ])->label('สภาพครุภัณฑ์ <span class="text-danger">*</span>') ?>
    </div>

<?php elseif ($section === 'asset_status'): ?>

    <?php
    $statusItems = ArrayHelper::map(AssetStatus::find()->all(), 'id', 'name');
    ?>
    <div class="mb-2">
        <?= $form->field($model, 'asset_status')->dropDownList($statusItems, [
            'prompt' => 'เลือกสถานะ...',
        ])->label('สถานะ <span class="text-danger">*</span>') ?>
    </div>

<?php elseif ($section === 'risk_level'): ?>

    <div class="mb-2">
        <?= $form->field($model, 'risk_level')->dropDownList([
            'L' => 'ต่ำ',
            'M' => 'กลาง',
            'H' => 'สูง',
            '' => 'ยังไม่ประเมิน',
        ])->label('ระดับความเสี่ยง') ?>
    </div>

<?php endif; ?>

<div class="d-flex justify-content-end gap-2 pt-2 border-top mt-3">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="submit" class="btn btn-primary fw-semibold">
        <i class="fa-regular fa-floppy-disk me-1"></i> บันทึกทั้งหมด
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
(function () {
    var \$form = $('#{$formId}');
    if (!\$form.length) { return; }

    function selectedIds() {
        return $('.equip-bulk-check:checked').map(function () { return this.value; }).get();
    }

    // แสดงจำนวนที่เลือกในโมดัล
    \$form.find('.bulk-edit-count').text(selectedIds().length);

    \$form.off('submit.bulk').on('submit.bulk', function (e) {
        e.preventDefault();

        var ids = selectedIds();
        if (!ids.length) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'ยังไม่ได้เลือกรายการ', text: 'กรุณาเลือกอย่างน้อย 1 รายการ', confirmButtonText: 'ตกลง' });
            }
            return;
        }

        var \$btn = \$form.find('button[type="submit"]');
        var original = \$btn.html();
        \$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...');

        var data = \$form.serialize();
        ids.forEach(function (id) { data += '&ids[]=' + encodeURIComponent(id); });

        $.ajax({
            url: \$form.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function (res) {
                if (res && res.status === 'success') {
                    if (typeof erpHideModal === 'function') { erpHideModal('#main-modal'); }
                    if (window.erpQuickEditToast) { window.erpQuickEditToast(res.message || 'อัปเดตรายการแล้ว'); }
                    if (typeof erpReloadPjax === 'function') {
                        if (!erpReloadPjax(res.container || '#am-container')) { location.reload(); }
                    } else if (window.jQuery && $.pjax) {
                        $.pjax.reload({ container: res.container || '#am-container', async: false });
                    } else {
                        location.reload();
                    }
                } else {
                    \$btn.prop('disabled', false).html(original);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ', text: (res && res.message) || 'กรุณาลองใหม่', confirmButtonText: 'ตกลง' });
                    }
                }
            },
            error: function (xhr) {
                \$btn.prop('disabled', false).html(original);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'การเชื่อมต่อขัดข้อง', text: 'ไม่สามารถติดต่อ Server ได้ (Error ' + xhr.status + ')' });
                }
            }
        });
    });
})();
JS;
$this->registerJs($js);
?>
