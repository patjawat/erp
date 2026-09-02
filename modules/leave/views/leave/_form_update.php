<?php
/**
 * ฟอร์มแก้ไขใบลา — ใช้กับโมเดล Leave เท่านั้น (actionUpdate)
 * @var app\modules\leave\models\Leave $model
 */
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;

$formatJs = <<<'JS'
var formatRepo = function (repo) { if (repo.loading) return repo.avatar; return '<div style="overflow:hidden;">' + (repo.avatar || '') + '</div>'; };
var formatRepoSelection = function (repo) { return repo.avatar || repo.avatar; }
JS;
$this->registerJs($formatJs, View::POS_HEAD);
$resultsJs = <<<JS
function (data, params) { params.page = params.page || 1; return { results: data.results, pagination: { more: (params.page * 30) < data.total_count } }; }
JS;

$form = ActiveForm::begin([
    'id' => 'form-leave-update',
    'action' => ['/leave/leave/update', 'id' => $model->id],
    'enableAjaxValidation' => true,
    'validationUrl' => ['/leave/leave/update-validation', 'id' => $model->id],
]);
?>
<?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
<div class="row">
    <div class="col-12">
        <div class="row mb-3">
            <div class="col-md-6">
                <?= $form->field($model, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                    'options' => ['placeholder' => 'เลือกวันที่', 'class' => 'form-control'],
                ]) ?>
                <?= $form->field($model, 'date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                    'options' => ['placeholder' => 'เลือกวันที่', 'class' => 'form-control'],
                ]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'date_start_type')->dropDownList(['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'], ['class' => 'form-select'])->label('ประเภท') ?>
                <?= $form->field($model, 'date_end_type')->dropDownList(['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'], ['class' => 'form-select'])->label('ประเภท') ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <?= $form->field($model, 'leave_type_id')->dropDownList($model->listLeaveType(), [
                    'class' => 'form-select',
                    'prompt' => '--- เลือกประเภทการลา ---',
                ])->label('ประเภทการลา') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'data_json[work_shift]')->dropDownList(
                    ['normal' => 'เวรเช้า', 'shift' => 'เวร 8 ชั่วโมง'],
                    ['id' => 'work_shift', 'class' => 'form-select', 'prompt' => '--- เลือก ---']
                )->label('ประเภทของเวร') ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-4">
                <?= $form->field($model, 'data_json[sat_sun_days]')->textInput(['id' => 'satsunDays', 'class' => 'form-control', 'readonly' => true])->label('วันเสาร์-อาทิตย์') ?>
            </div>
            <div class="col-4">
                <?= $form->field($model, 'data_json[holidays]')->textInput(['id' => 'holiday', 'class' => 'form-control', 'readonly' => true])->label('วันหยุดนักขัตฤกษ์') ?>
            </div>
            <div class="col-4">
                <?= $form->field($model, 'total_days')->textInput(['id' => 'summaryDay', 'class' => 'form-control', 'readonly' => true])->label('สรุปวันลา') ?>
            </div>
        </div>
        <div class="mb-3">
            <?= $form->field($model, 'data_json[phone]')->textInput(['class' => 'form-control'])->label('เบอร์โทรติดต่อ') ?>
        </div>
        <div class="mb-3">
            <?= $form->field($model, 'data_json[place_go]')->dropDownList(
                ['ภายในจังหวัด' => 'ภายในจังหวัด', 'ต่างจังหวัด' => 'ต่างจังหวัด', 'ต่างประเทศ' => 'ต่างประเทศ'],
                ['class' => 'form-select', 'prompt' => '--- เลือก ---']
            )->label('สถานที่ไป') ?>
        </div>
        <div class="mb-3">
            <?= $form->field($model, 'data_json[address]')->textarea(['rows' => 2, 'class' => 'form-control'])->label('ระหว่างลาติดต่อ') ?>
        </div>
        <div class="mb-3">
            <?= $form->field($model, 'data_json[leave_work_send_id]')->widget(Select2::class, [
                'initValueText' => $leaveWorkSendInitText ?? '',
                'options' => ['placeholder' => 'เลือกรายการ...'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#main-modal',
                    'minimumInputLength' => 1,
                    'ajax' => [
                        'url' => Url::to(['/depdrop/employee-by-id']),
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new JsExpression('function(params){ return {q:params.term, page: params.page}; }'),
                        'processResults' => new JsExpression($resultsJs),
                        'cache' => true,
                    ],
                    'escapeMarkup' => new JsExpression('function(m){ return m; }'),
                    'templateResult' => new JsExpression('formatRepo'),
                    'templateSelection' => new JsExpression('formatRepoSelection'),
                ],
            ])->label('มอบหมายงานให้') ?>
        </div>
        <?= $this->render('@app/modules/leave/views/leave/approve', ['form' => $form, 'model' => $model]) ?>
        <div class="mb-3">
            <?= $form->field($model, 'data_json[reason]')->textarea(['rows' => 3, 'class' => 'form-control'])->label('เหตุผล/เนื่องจาก') ?>
        </div>
        <div class="mb-3">
            <label class="form-label">เอกสารแนบ/ใบรับรองแพทย์</label>
            <?= $model->Upload('leave_file') ?>
        </div>
    </div>
</div>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[leave_work_send]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>
<div class="d-flex gap-2 justify-content-end">
    <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
</div>
<?php ActiveForm::end(); ?>

<?php
$calDaysUrl = Url::to(['/leave/leave/cal-days']);
$updateShiftUrl = Url::to(['/leave/leave/update-work-shift']);
$this->registerJs(<<<JS
(function(){
    function calDays() {
        var \$ = jQuery;
        if (!\$('#leave-date_start').length) return;
        \$.get('$calDaysUrl', {
            date_start: \$('#leave-date_start').val(),
            date_end: \$('#leave-date_end').val(),
            date_start_type: \$('#leave-date_start_type').val(),
            date_end_type: \$('#leave-date_end_type').val(),
            leave_type_id: \$('#leave-leave_type_id').val(),
            work_shift: \$('#work_shift').val()
        }).done(function(res){
            if (res.status === 'error') return;
            \$('#satsunDays').val(res.satsunDays || 0);
            \$('#holiday').val(res.holiday || 0);
            \$('#summaryDay').val(res.total || 0);
        });
    }
    jQuery('#leave-date_start, #leave-date_end, #leave-date_start_type, #leave-date_end_type, #leave-leave_type_id, #work_shift').on('change', calDays);
    calDays();
    jQuery('#form-leave-update').on('beforeSubmit', function(e){
        e.preventDefault();
        var form = jQuery(this);
        var total = parseFloat(jQuery('#summaryDay').val()) || 0;
        if (total <= 0 && typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'วันลาต้องมากกว่า 0', text: '' });
            return false;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'ยืนยันบันทึก?', icon: 'question', showCancelButton: true, confirmButtonText: 'บันทึก', cancelButtonText: 'ยกเลิก' }).then(function(r){
                if (!r.isConfirmed) return;
                jQuery('#main-modal').modal('hide');
                Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
                jQuery.ajax({ url: form.attr('action'), type: 'post', data: form.serialize(), dataType: 'json' })
                    .done(function(res){
                        if (res.status === 'success') {
                            Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', showConfirmButton: false, timer: 1500 }).then(function(){
                                if (res.redirect) location.href = res.redirect; else location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', text: res.message || '' });
                        }
                    })
                    .fail(function(){ Swal.fire({ icon: 'error', text: 'เกิดข้อผิดพลาด' }); });
            });
        } else {
            form.off('beforeSubmit').submit();
        }
        return false;
    });
})();
JS
, View::POS_END);
?>
