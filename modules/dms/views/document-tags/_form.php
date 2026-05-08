<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;

$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

$this->registerJs($formatJs, View::POS_HEAD);

$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;

$isUpdate = !$model->isNewRecord;
$action = $isUpdate
    ? Url::to(['/dms/document-tags/update', 'id' => $model->id])
    : Url::to(['/dms/document-tags/create', 'document_id' => $model->document_id, 'ref' => $model->ref, 'name' => $model->name]);

$initEmployee = null;
if ($isUpdate && !empty($model->tag_id)) {
    $emp = Employees::find()->where(['id' => $model->tag_id])->one();
    if ($emp) {
        $initEmployee = $emp->getAvatar(false);
    }
}
?>

<div class="document-tags-form">

    <?php $form = ActiveForm::begin([
        'id' => 'formDocumentTags',
        'action' => $action,
    ]); ?>

    <?php if (!$isUpdate): ?>
        <div class="mb-3">
            <?= $form->field($model, 'tags_employee')->widget(Select2::classname(), [
                'options' => [
                    'placeholder' => 'พิมพ์เพื่อค้นหาบุคคล (เลือกได้หลายคน)...',
                    'multiple' => true,
                ],
                'size' => Select2::LARGE,
                'pluginOptions' => [
                    'dropdownParent' => '#main-modal',
                    'allowClear' => true,
                    'multiple' => true,
                    'minimumInputLength' => 1,
                    'ajax' => [
                        'url' => Url::to(['/depdrop/employee-by-id']),
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                        'processResults' => new JsExpression($resultsJs),
                        'cache' => true,
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                    'templateResult' => new JsExpression('formatRepo'),
                ],
            ])->label('เลือกบุคคล <span class="text-muted small">(เลือกได้หลายคน)</span>') ?>
            <div class="form-text"><i class="fa-regular fa-circle-info me-1"></i>พิมพ์ชื่อ จะแสดง dropdown ให้เลือก กดเลือกได้หลายชื่อก่อนกดบันทึก</div>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <?= $form->field($model, 'tag_id')->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                'options' => ['placeholder' => 'พิมพ์เพื่อค้นหาบุคคล...'],
                'size' => Select2::LARGE,
                'pluginOptions' => [
                    'dropdownParent' => '#main-modal',
                    'allowClear' => true,
                    'minimumInputLength' => 1,
                    'ajax' => [
                        'url' => Url::to(['/depdrop/employee-by-id']),
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                        'processResults' => new JsExpression($resultsJs),
                        'cache' => true,
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                    'templateResult' => new JsExpression('formatRepo'),
                ],
            ])->label('เลือกบุคคล') ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <?= $form->field($model, 'data_json[comment]')->textarea([
            'rows' => 2,
            'placeholder' => 'เช่น เพื่อทราบ / เพื่อพิจารณา ...',
            'value' => isset($model->data_json['comment']) ? $model->data_json['comment'] : '',
        ])->label('หมายเหตุ <span class="text-muted small">(ใช้ร่วมกันทุกคนที่เลือก)</span>') ?>
    </div>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'document_id')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'doc_number')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'doc_regis_number')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'status')->hiddenInput(['maxlength' => true])->label(false) ?>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> ยกเลิก
        </button>
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ' . ($isUpdate ? 'บันทึกการแก้ไข' : 'บันทึก'), [
            'class' => 'btn btn-primary rounded-pill',
            'id' => 'summit',
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<< 'JS'
(function () {
    var $form = $('#formDocumentTags');
    if (!$form.length) { return; }
    // ป้องกัน form submit แบบ fallthrough ในทุกกรณี (รวมถึงเมื่อไม่มี client validator)
    $form.attr('data-pjax', 0);
    $form.off('submit.dmsTag').on('submit.dmsTag', function (e) {
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
            success: function (response) {
                if (!response || response.status !== 'success') {
                    var warnMsg = (response && response.message) ? response.message : 'บันทึกไม่สำเร็จ';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: warnMsg });
                    }
                    return;
                }
                // อยู่หน้า view เดิม → ปิด modal + รีโหลดเฉพาะส่วน tag/timeline
                $('#main-modal').modal('hide');
                var msg = (response.created)
                    ? ('บันทึกแล้ว ' + response.created + ' รายการ' + (response.skipped ? ' (ข้าม ' + response.skipped + ' ที่ tag ไว้แล้ว)' : ''))
                    : 'บันทึกแล้ว';
                if (typeof success === 'function') { success(msg); }
                if ($('#document-tag').length) {
                    $.pjax.reload({ container: '#document-tag', history: false, timeout: false });
                }
            },
            error: function (xhr) {
                if (typeof Swal === 'undefined') { return; }
                if (xhr.status === 403) {
                    Swal.fire({ icon: 'error', title: 'ไม่ได้รับอนุญาต', text: 'แก้ไขได้เฉพาะ tag ที่ตัวเองสร้างเท่านั้น' });
                } else {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด';
                    Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: msg });
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
