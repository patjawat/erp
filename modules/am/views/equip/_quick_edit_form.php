<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\depdrop\DepDrop;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var string $section */

$formId = 'quick-edit-form-' . $model->id;
?>

<?php $form = ActiveForm::begin([
    'id' => $formId,
    'action' => Url::to(['/am/equip/quick-edit', 'id' => $model->id, 'section' => $section]),
    'enableClientValidation' => false,
    'options' => ['class' => 'quick-edit-form'],
]); ?>

<?php if ($section === 'category'): ?>

    <div class="mb-3">
        <?= $form->field($model, 'asset_type_id')->widget(Select2::class, [
            'data' => $model->listAssetType(),
            'options' => ['placeholder' => 'เลือกประเภท...', 'id' => 'qedit_asset_type_id'],
            'pluginOptions' => ['allowClear' => true],
        ])->label('ประเภทครุภัณฑ์') ?>
    </div>

    <div class="mb-2">
        <?= $form->field($model, 'asset_category_id')->widget(DepDrop::class, [
            'options' => ['id' => 'qedit_asset_category_id', 'placeholder' => 'เลือกหมวดหมู่...'],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => ['pluginOptions' => ['allowClear' => true]],
            'pluginOptions' => [
                'depends' => ['qedit_asset_type_id'],
                'url' => Url::to(['/am/asset-item/get-asset-category']),
                'loadingText' => 'กำลังโหลด...',
                'initialize' => true,
            ],
        ])->label('หมวดหมู่ครุภัณฑ์') ?>
    </div>

<?php else: ?>

    <?php
    $employee = !empty($model->owner) ? Employees::findOne($model->owner) : null;
    $ownerName = $employee ? $employee->fullname : '';
    $departments = ArrayHelper::map(
        Organization::find()->orderBy('root, lft')->all(),
        'id',
        'name'
    );
    ?>

    <div class="mb-3">
        <?= $form->field($model, 'data_json[location]')->textInput([
            'placeholder' => 'อาคาร / ห้อง / คลัง',
            'value' => is_array($model->data_json) ? ($model->data_json['location'] ?? '') : '',
        ])->label('สถานที่ตั้ง / คลัง / ห้อง') ?>
    </div>

    <div class="mb-3">
        <?= $form->field($model, 'department')->widget(Select2::class, [
            'data' => $departments,
            'options' => ['placeholder' => 'เลือกหน่วยงาน...'],
            'pluginOptions' => ['allowClear' => true],
        ])->label('หน่วยงานที่ตั้ง') ?>
    </div>

    <div class="mb-2">
        <?= $form->field($model, 'owner')->widget(Select2::class, [
            'initValueText' => $ownerName,
            'options' => ['placeholder' => 'ค้นหาชื่อพนักงาน...'],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 1,
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

<?php endif; ?>

<div class="d-flex justify-content-end gap-2 pt-2 border-top mt-3">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="submit" class="btn btn-primary fw-semibold">
        <i class="fa-regular fa-floppy-disk me-1"></i> บันทึก
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
(function () {
    var \$form = $('#{$formId}');
    if (!\$form.length) { return; }

    \$form.off('submit.qedit').on('submit.qedit', function (e) {
        e.preventDefault();
        var \$btn = \$form.find('button[type="submit"]');
        var original = \$btn.html();
        \$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...');

        $.ajax({
            url: \$form.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: \$form.serialize(),
            success: function (res) {
                if (res && res.status === 'success') {
                    if (typeof erpHideModal === 'function') { erpHideModal('#main-modal'); }
                    if (window.erpQuickEditToast) { window.erpQuickEditToast('อัปเดตรายการแล้ว'); }
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
