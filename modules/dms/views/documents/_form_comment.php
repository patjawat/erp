<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentsDetail $model */

$me = UserHelper::GetEmployee();
$avatarUrl = $me ? $me->showAvatar() : '';
$myName = $me ? $me->fullname : 'ฉัน';
$isEdit = !$model->isNewRecord;

// dept ที่ยังไม่ได้ส่ง (กันส่งซ้ำ)
$departmentList = $model->listAvailableDepartments();

$action = $isEdit
    ? Url::to(['/me/documents/update-comment', 'id' => $model->id])
    : Url::to(['/me/documents/comment', 'id' => $model->document_id]);
$testNotifyUrl = Url::to(['test-notification', 'id' => $model->document_id]);
$canTestNotification = \Yii::$app->user->can('admin');
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-comment',
    'action' => $action,
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'options' => ['data-pjax' => 0, 'class' => 'fb-composer'],
]); ?>

<?= $form->field($model, 'to_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'document_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['value' => 'comment'])->label(false) ?>

<div class="w-100">
        <div class="rounded-4 bg-light bg-opacity-50 border border-light-subtle p-2">
            <?= $form->field($model, 'data_json[comment]', ['options' => ['class' => 'mb-0']])->textarea([
                'rows' => 1,
                'placeholder' => $isEdit ? 'แก้ไขความเห็น...' : ('แสดงความเห็นในนาม ' . $myName . '...'),
                'class' => 'form-control border-0 bg-transparent shadow-none p-2',
                'style' => 'resize:none; min-height:40px;',
            ])->label(false) ?>

            <div class="collapse mt-2" id="composer-tag-people">
                <div class="px-2 pb-2">
                    <div class="small text-muted mb-1"><i class="fa-solid fa-user-tag me-1"></i>ส่งต่อถึงบุคคล</div>
                    <?= $form->field($model, 'tags_employee', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
                        'data' => $model->listEmployeeSelectTag(),
                        'options' => ['placeholder' => 'พิมพ์ค้นหาบุคคล...', 'multiple' => true],
                        'pluginOptions' => ['allowClear' => true, 'multiple' => true],
                    ])->label(false) ?>
                </div>
            </div>

            <div class="collapse mt-2" id="composer-tag-dept">
                <div class="px-2 pb-2">
                    <div class="small text-muted mb-1"><i class="fa-solid fa-building me-1"></i>ส่งต่อถึงหน่วยงานxxx</div>
                    <?= $form->field($model, 'tags_department', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
                        'data' => $departmentList,
                        'options' => ['placeholder' => 'พิมพ์ค้นหาหน่วยงาน...', 'multiple' => true],
                        'pluginOptions' => ['allowClear' => true, 'multiple' => true],
                    ])->label(false) ?>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-1 pt-2 border-top border-light-subtle mt-2">
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-light text-secondary rounded-pill px-2 py-1 small" data-bs-toggle="collapse" data-bs-target="#composer-tag-people">
                        <i class="fa-solid fa-user-tag me-1"></i>ส่งต่อถึง
                    </button>
                    <button type="button" class="btn btn-sm btn-light text-secondary rounded-pill px-2 py-1 small" data-bs-toggle="collapse" data-bs-target="#composer-tag-dept">
                        <i class="fa-solid fa-building me-1"></i>ส่งหน่วยงาน
                    </button>
                    <button type="button" class="btn btn-sm btn-light text-secondary rounded-pill px-2 py-1 small" id="composer-toggle-templates">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>แม่แบบ
                    </button>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-end">
                    <span id="char-count" class="badge bg-secondary opacity-50 rounded-pill small text-white">0 ตัวอักษร</span>
                    <?php if ($canTestNotification): ?>
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-1 small fw-semibold btn-test-notification" data-test-notify-url="<?= Html::encode($testNotifyUrl) ?>">
                            <i class="fa-solid fa-vial me-1"></i> ทดสอบแจ้งเตือน
                        </button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary rounded-pill px-3 py-1 small fw-semibold">
                        <i class="fa-solid fa-paper-plane me-1"></i> <?= $isEdit ? 'บันทึก' : 'ลงความเห็น' ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapse mt-2" id="composer-templates">
            <div class="rounded-3 bg-white border border-light-subtle p-2">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="small text-muted fw-semibold">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>ข้อความที่ใช้บ่อย (<span id="counttemplate">0</span>)
                    </div>
                    <button id="btn-save-temp-now" type="button" class="btn btn-sm btn-primary rounded-pill px-2 py-1 small" style="display:none;">
                        <i class="fa-regular fa-floppy-disk me-1"></i>บันทึกแม่แบบ
                    </button>
                </div>
                <div id="viewlistCommenttemplate"></div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< 'JS'
(function () {
    $(document).off('click.composerTpl').on('click.composerTpl', '#composer-toggle-templates', function (e) {
        e.preventDefault();
        var el = document.getElementById('composer-templates');
        if (!el) return;
        bootstrap.Collapse.getOrCreateInstance(el).toggle();
    });

    var $form = $('#form-comment');
    // bind capture-phase submit ผ่าน addEventListener เพื่อ block ActiveForm และทุก handler อื่น
    var formEl = $form.get(0);
    if (formEl && !formEl.__fbBound) {
        formEl.__fbBound = true;
        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
            var form = $(formEl);
            var $btn = form.find('button[type=submit]');
            $btn.prop('disabled', true);
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res && res.status === 'success') {
                        if (typeof success === 'function') { success('ลงความเห็นสำเร็จ'); }
                        if (typeof listComment === 'function') { listComment(); }
                        if (typeof getComment === 'function') { getComment(); }
                    } else {
                        Swal.fire({ icon: 'warning', title: (res && res.message) || 'บันทึกไม่สำเร็จ' });
                    }
                },
                error: function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด';
                    Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: msg });
                },
                complete: function () { $btn.prop('disabled', false); }
            });
            return false;
        }, true);  // useCapture = true → fire ก่อน handler อื่นทั้งหมด
    }

    $(document).off('click.testNotify', '.btn-test-notification').on('click.testNotify', '.btn-test-notification', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var form = $btn.closest('form');
        var url = $btn.data('test-notify-url');
        if (!url || !form.length) {
            return;
        }

        $btn.prop('disabled', true);
        $.ajax({
            url: url,
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res && (res.status === 'success' || res.status === 'warning')) {
                    var safeMessage = $('<div>').text(res.message || 'ส่งการแจ้งเตือนทดสอบแล้ว').html();
                    var swalOptions = {
                        icon: res.status === 'success' ? 'success' : 'warning',
                        title: res.message || 'ส่งการแจ้งเตือนทดสอบแล้ว',
                    };
                    if (res.status === 'warning' && res.details_html) {
                        swalOptions.html = '<div class="text-start">' + safeMessage + '<hr class="my-2">' + res.details_html + '</div>';
                    } else {
                        swalOptions.text = 'LINE: ' + (res.line_sent || 0) + ' | Telegram: ' + (res.telegram_sent || 0);
                    }
                    Swal.fire(swalOptions);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: (res && res.message) || 'ทดสอบไม่สำเร็จ',
                    });
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด';
                Swal.fire({ icon: 'error', title: 'ทดสอบไม่สำเร็จ', text: msg });
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // char count
    function updateCharCount() {
        var ta = document.getElementById('documentsdetail-data_json-comment');
        if (!ta) return;
        var len = (ta.value || '').length;
        var $cc = $('#char-count');
        $cc.text(len + ' ตัวอักษร');
        if (len > 0) { $cc.removeClass('opacity-50').addClass('opacity-100'); }
        else { $cc.removeClass('opacity-100').addClass('opacity-50'); }
    }
    $(document).off('input.composer keyup.composer', '#documentsdetail-data_json-comment')
        .on('input.composer keyup.composer', '#documentsdetail-data_json-comment', updateCharCount);
    updateCharCount();
})();
JS;
$this->registerJs($js, View::POS_END);
?>
