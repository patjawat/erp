<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListVendor $model */
?>

<div class="list-setting-form list-vendor-form list-vendor-quick">
    <?php $form = ActiveForm::begin([
        'id' => 'form-vendor-quick',
        'enableAjaxValidation' => true,
        'validationUrl' => ['/am/vendor/validator'],
    ]); ?>

    <div class="row g-3">
        <div class="col-12">
            <?= $form->field($model, 'title', [
                'template' => '{label}<small class="vendor-quick__hint">บันทึกแล้วระบบจะเลือกใช้รายการนี้ในแบบฟอร์มทรัพย์สินทันที</small>{input}{hint}{error}',
            ])->textInput([
                'maxlength' => true,
                'placeholder' => 'เช่น บริษัท ก จำกัด, นาย ข ผู้บริจาค',
                'autocomplete' => 'off',
                'autofocus' => true,
            ])->label('ชื่อผู้ขาย/ผู้จำหน่าย/ผู้บริจาค') ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'description')->textarea([
                'rows' => 2,
                'placeholder' => 'ที่อยู่/เบอร์ติดต่อ (ไม่บังคับ)',
            ])->label('รายละเอียดเพิ่มเติม') ?>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top vendor-quick__actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
            ยกเลิก
        </button>
        <?= Html::submitButton('<i class="fa-solid fa-check"></i> บันทึกและเลือกใช้', [
            'class' => 'btn btn-sm btn-primary px-3 d-inline-flex align-items-center gap-1',
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
.list-vendor-quick .vendor-quick__hint {
    display: block;
    color: #718096;
    font-size: 0.78rem;
    font-weight: 400;
    line-height: 1.4;
    margin: 0.1rem 0 0.4rem;
}
.list-vendor-quick .control-label { font-weight: 500; color: #4a5568; font-size: 0.86rem; margin-bottom: 0.1rem; }
.list-vendor-quick .form-control { border-color: rgba(15,23,42,.14); }
.list-vendor-quick .form-control:focus {
    border-color: rgba(13,110,253,.5);
    box-shadow: 0 0 0 3px rgba(13,110,253,.08);
}
.list-vendor-quick .vendor-quick__actions { border-top-color: rgba(15,23,42,.08) !important; }
</style>

<?php
$js = <<<JS
(function() {
    var \$form = \$('#form-vendor-quick');
    if (!\$form.length) {
        console.warn('[vendor-quick] form not found');
        return;
    }
    console.log('[vendor-quick] handler bound on', \$form.attr('action'));

    \$form.on('beforeSubmit', function(e) {
        var form = \$(this);
        console.log('[vendor-quick] beforeSubmit fired, posting to', form.attr('action'));
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json'
        }).done(function(response) {
            console.log('[vendor-quick] response', response);
            if (response && response.status === 'success' && response.vendor) {
                // Direct call to the parent page's reload helper. Bypasses the
                // event-chain which has timing issues with modal close + swal.
                if (typeof window.reloadVendorOptions === 'function') {
                    console.log('[vendor-quick] calling parent reloadVendorOptions directly');
                    window.reloadVendorOptions(response.vendor.id, response.vendor.text);
                } else {
                    console.warn('[vendor-quick] window.reloadVendorOptions not found, falling back to event');
                }
                // Also dispatch event for any other listeners (defense in depth)
                \$(document).trigger('vendor:saved', [response.vendor]);

                if (typeof closeModal === 'function') {
                    closeModal();
                } else {
                    \$('.modal.show').modal('hide');
                }
            } else {
                console.warn('[vendor-quick] save did not return success', response);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'warning',
                        title: 'บันทึกไม่สำเร็จ ตรวจสอบข้อมูล', showConfirmButton: false, timer: 2400 });
                }
            }
        }).fail(function(xhr, status, err) {
            console.error('[vendor-quick] save failed', status, err, xhr && xhr.status);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ toast: true, position: 'top-end', icon: 'error',
                    title: 'เพิ่มผู้ขายไม่สำเร็จ (HTTP ' + (xhr ? xhr.status : '?') + ')',
                    showConfirmButton: false, timer: 2800 });
            }
        });
        return false;
    });
})();
JS;
$this->registerJs($js);
?>
