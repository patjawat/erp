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

// ตั้งค่าเริ่มต้น "ส่งต่อถึง" เป็นผู้อำนวยการ จะได้ไม่ต้องเลือกเองทุกครั้ง
$model->applyDefaultForward();
$hasForwardTo = !empty($model->tags_employee);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-comment',
    'enableAjaxValidation' => true,
    'validationUrl' => ['/dms/documents/comment-validator'],
    'options' => ['data-pjax' => 0, 'class' => 'fb-composer'],
]); ?>

<?= $form->field($model, 'to_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'document_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['value' => 'comment'])->label(false) ?>

<div class="d-flex gap-2 align-items-start">
    <div class="flex-shrink-0">
        <?php if ($avatarUrl): ?>
            <img src="<?= Html::encode($avatarUrl) ?>" class="rounded-circle border" style="width:36px;height:36px;object-fit:cover;" alt="<?= Html::encode($myName) ?>">
        <?php else: ?>
            <span class="d-inline-flex align-items-center justify-content-center bg-secondary text-white rounded-circle" style="width:36px;height:36px;">
                <i class="fa-solid fa-user"></i>
            </span>
        <?php endif; ?>
    </div>

    <div class="flex-grow-1 min-width-0">
        <div class="rounded-4 bg-light bg-opacity-50 border border-light-subtle p-2 fb-composer-shell">
            <?= $form->field($model, 'data_json[comment]', [
                'options' => ['class' => 'mb-0'],
            ])->textarea([
                'rows' => 1,
                'placeholder' => $isEdit ? 'แก้ไขความเห็น...' : ('แสดงความเห็นในนาม ' . $myName . '...'),
                'class' => 'form-control border-0 bg-transparent shadow-none p-2 fb-composer-input',
                'style' => 'resize:none; min-height:40px;',
            ])->label(false) ?>

            <div class="collapse mt-2<?= $hasForwardTo ? ' show' : '' ?>" id="composer-tag-people">
                <div class="px-2 pb-2">
                    <div class="small text-muted mb-1"><i class="fa-solid fa-user-tag me-1"></i>เลือกบุคคลที่ต้องการส่งต่อ</div>
                    <?= $form->field($model, 'tags_employee', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
                        'data' => $model->listEmployeeSelectTag(),
                        'options' => ['placeholder' => 'พิมพ์ค้นหา...', 'multiple' => true],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'multiple' => true,
                        ],
                    ])->label(false) ?>
                    <?php if (!$isEdit && $hasForwardTo): ?>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>ตั้งค่าเริ่มต้นไว้ให้แล้ว ถ้าไม่ต้องการส่งต่อ กดกากบาทที่ชื่อเพื่อเอาออกได้
                        </div>
                    <?php elseif (!$isEdit && $model->directorForwardedByOthers): ?>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa-solid fa-circle-check me-1 text-success"></i>เอกสารนี้ส่งถึงผู้อำนวยการไปแล้ว จึงไม่ได้ใส่ชื่อซ้ำให้ ถ้าต้องการส่งอีกครั้งเลือกเองได้
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-1 pt-1 border-top border-light-subtle mt-2">
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-sm btn-light text-secondary rounded-pill px-2 py-1 small" data-bs-toggle="collapse" data-bs-target="#composer-tag-people">
                        <i class="fa-solid fa-user-tag me-1"></i>ส่งต่อถึง
                    </button>
                    <button type="button" class="btn btn-sm btn-light text-secondary rounded-pill px-2 py-1 small" id="composer-toggle-templates">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>แม่แบบ
                    </button>
                </div>
                <button type="submit" class="btn btn-primary rounded-pill px-3 py-1 small fw-semibold">
                    <i class="fa-solid fa-paper-plane me-1"></i> <?= $isEdit ? 'บันทึก' : 'ลงความเห็น' ?>
                </button>
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
    // toggle templates panel
    $(document).off('click.composerTpl').on('click.composerTpl', '#composer-toggle-templates', function (e) {
        e.preventDefault();
        var el = document.getElementById('composer-templates');
        if (!el) return;
        var bsCollapse = bootstrap.Collapse.getOrCreateInstance(el);
        bsCollapse.toggle();
    });

    // submit handler — bypass ActiveForm's beforeSubmit so it always works
    var $form = $('#form-comment');
    $form.off('submit.fbComposer').on('submit.fbComposer', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var form = $(this);
        var $btn = form.find('button[type=submit]');
        $btn.prop('disabled', true);
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res && res.status === 'success') {
                    form[0].reset();
                    if (typeof success === 'function') { success('ลงความเห็นสำเร็จ'); }
                    if (typeof getComment === 'function') { getComment(); }
                    if (typeof reloadTimeline === 'function') {
                        reloadTimeline();
                    } else if ($('#document-tag').length) {
                        $.pjax.reload({ container: '#document-tag', history: false, timeout: false });
                    }
                }
            },
            complete: function () { $btn.prop('disabled', false); }
        });
        return false;
    });
})();
JS;
$this->registerJs($js, View::POS_END);
?>
