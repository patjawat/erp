<?php

use app\components\ApproveLevelResolver;
use app\modules\hr\models\Employees;
use app\widgets\datepicker\DatepickerThai;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ขอลาออนไลน์';
$this->params['mobileSubtitle'] = 'ตรวจสอบสิทธิ์และส่งคำขอลา';

$employee   = $employee ?? null;
$types      = $types ?? [];
$stats      = $stats ?? [];
$roundLabel = $roundLabel ?? '';
$draftRef   = $draftRef ?? null;
$leaveWorkSendInitText = $leaveWorkSendInitText ?? '';

//   <!-- สรุปวันลา -->
$initSatsun = 0;
$initHoliday = 0;
$initTotal = 0;


$leaveTypes = [
    ''  => '— เลือกประเภทการลา —',
    '1' => 'ลาป่วย',
    '2' => 'ลาพักร้อน',
    '3' => 'ลากิจ',
    '4' => 'ลาคลอด',
    '5' => 'อื่นๆ',
];
$balanceItems = [
    ['label' => 'ลาป่วย', 'days' => 30, 'used' => 2, 'unit' => 'วัน/ปี'],
    ['label' => 'ลาพักร้อน', 'days' => 15, 'used' => 5, 'unit' => 'วัน/ปี'],
    ['label' => 'ลากิจ', 'days' => 7, 'used' => 1, 'unit' => 'วัน/ปี'],
];
$workflowSteps = [
    ['label' => 'ส่งคำขอ', 'done' => true],
    ['label' => 'หัวหน้างาน', 'done' => true],
    ['label' => 'HR', 'done' => false],
    ['label' => 'เสร็จสิ้น', 'done' => false],
];
?>
<style>
    .leave-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    .leave-card .form-control,
    .leave-card .form-select {
        border-radius: 12px;
        padding: 0.75rem 1rem;
    }

    .leave-card .form-label {
        font-weight: 500;
    }

    .btn-leave-submit {
        border-radius: 12px;
        padding: 0.875rem 1.25rem;
        font-size: 1.0625rem;
        font-weight: 600;
    }

    .balance-item {
        border-radius: 12px;
        padding: 0.75rem 1rem;
        background: rgba(93, 95, 239, 0.06);
        border: 1px solid rgba(93, 95, 239, 0.15);
    }

    .balance-item .balance-days {
        font-size: 1.25rem;
        font-weight: 700;
        color: #5D5FEF;
    }

    .workflow-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
    }

    .workflow-step::after {
        content: '';
        position: absolute;
        top: 0.75rem;
        left: calc(50% + 1.25rem);
        width: calc(100% - 2.5rem);
        height: 2px;
        background: #dee2e6;
        z-index: 0;
    }

    .workflow-step:last-child::after {
        display: none;
    }

    .workflow-step.done::after {
        background: #5D5FEF;
        opacity: 0.4;
    }

    .workflow-step .step-dot {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: #dee2e6;
        color: #6c757d;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .workflow-step.done .step-dot {
        background: #5D5FEF;
        color: #fff;
    }

    .workflow-step .step-label {
        font-size: 0.6875rem;
        margin-top: 0.35rem;
        color: #6c757d;
    }

    .workflow-step.done .step-label {
        color: #5D5FEF;
        font-weight: 500;
        
    }

    .step-label{
    max-width:100%;
}

    .btn-attach {
        border-radius: 12px;
        padding: 0.75rem 1rem;
        border: 2px dashed #dee2e6;
        background: #fff;
        color: #6c757d;
        font-weight: 500;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    .btn-attach:hover {
        border-color: #5D5FEF;
        background: rgba(93, 95, 239, 0.06);
        color: #5D5FEF;
    }

    .attach-filename {
        font-size: 0.875rem;
        color: #198754;
        margin-top: 0.35rem;
    }
</style>

<div class="booking-header mb-3">
    <h1 class="h5 fw-semibold text-dark mb-0">ขอลาออนไลน์</h1>
    <p class="small text-body-secondary mb-0">ตรวจสอบสิทธิ์และส่งคำขอลา</p>
</div>


</div>

<!-- Approval workflow -->
<div class="card leave-card mb-3">
    <div class="card-body">
        <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i data-lucide="git-branch" style="width: 1.25rem; height: 1.25rem;"></i>
            ขั้นตอนการอนุมัติ
        </h6>
        <div class="row g-0">
            <?php
            $listApprove = ApproveLevelResolver::resolve('leave', $model->emp_id);
            ?>
            <?php foreach ($listApprove as $i => $step): ?>
                <?php
                $approveEmployee = Employees::findOne(['id' => $step['emp_id']]);
                ?>
                <div class="col-3 workflow-step ">


                    <div class="step-dot">
                        <?php
                        if ($approveEmployee) {
                            echo Html::img($approveEmployee->showAvatar(), ['class' => 'rounded-circle', 'width' => 35, 'height' => 35, 'style' => 'min-width: 35px; min-height: 35px;', 'alt' => $approveEmployee->fullname]);
                        }
                        ?>
                    </div>
                    <span class="step-label d-block text-truncate">
                        <?= Html::encode($step['label']) ?>
                    </span>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php


$form = ActiveForm::begin([
    'id' => 'mobile-leave-request-form',
    'options' => [
        'enctype' => 'multipart/form-data'
    ],
]);

?>
<?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'thai_year')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<div class="card leave-card mb-3">
    <div class="card-body">

        <div class="mb-3">
            <label class="form-label fw-medium">
                ประเภทการลา <span class="text-danger">*</span>
            </label>

            <?= $form->field($model, 'leave_type_id')->dropDownList(
                $model->listLeaveType(),
                [
                    'class' => 'form-select',
                    'id' => 'leave-type'
                ]
            )->label(false) ?>

        </div>


        <div class="mb-3">
            <?= $form->field($model, 'date_start')->widget(DatepickerThai::class, [
                'options' => [
                    'id' => 'leave-date_start',
                    'placeholder' => 'เลือกวันที่เริ่มลา',
                    'class' => 'form-control',
                ],
            ])->label('วันที่เริ่มลา') ?>

        </div>


        <div class="mb-3">
            <?= $form->field($model, 'date_end')->widget(DatepickerThai::class, [
                'options' => [
                    'id' => 'leave-date_end',
                    'placeholder' => 'เลือกวันที่สิ้นสุด',
                    'class' => 'form-control',
                ],
            ])->label('วันที่สิ้นสุด') ?>

        </div>


        <div class="mb-3">
            <label class="form-label fw-medium">
                จำนวนวัน <span class="text-danger">*</span>
            </label>

            <?= $form->field($model, 'total_days')->input('number', [
                'id' => 'leave-days',
                'class' => 'form-control',
                'min' => 0.5,
                'max' => 365,
                'step' => 0.5,
                'placeholder' => 'วัน',
            ])->label(false) ?>

        </div>


        <div class="mb-3">
            <label class="form-label fw-medium">
                เหตุผลการลา <span class="text-danger">*</span>
            </label>

            <?= $form->field($model, 'reason')->textarea([
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => 'ระบุเหตุผลการลา',
            ])->label(false) ?>

        </div>


        <div class="mb-0">

            <label class="form-label fw-medium">แนบเอกสาร</label>

            <?php
            //  $form->field($model, 'attachment[]')->fileInput([
            //     'id' => 'leave-attachment',
            //     'class' => 'd-none',
            //     'accept' => '.pdf,.jpg,.jpeg,.png,.doc,.docx',
            //     'multiple' => true
            // ])->label(false) 
            ?>


            <button
                type="button"
                class="btn btn-attach w-100 d-flex align-items-center justify-content-center gap-2"
                id="btn-attach-leave">

                <i data-lucide="paperclip"
                    style="width: 1.25rem; height: 1.25rem;">
                </i>

                เลือกไฟล์แนบ

            </button>

            <div id="attach-filenames" class="attach-filename d-none"></div>

        </div>

    </div>
</div>

<!-- Leave balance -->
<div class="card leave-card mb-3">
    <div class="card-body">
        <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i data-lucide="wallet" style="width: 1.25rem; height: 1.25rem;"></i>
            สรุปการลา
        </h6>
        <div class="d-flex flex-column gap-2">
            <div class="balance-item d-flex align-items-center justify-content-between">
                <span class="fw-medium">วันเสาร์-อาทิตย์ (วัน)</span>
                <span class="balance-days" id="leave-summary-satsun">0<span class="small fw-normal text-body-secondary"></span></span>
            </div>

            <div class="balance-item d-flex align-items-center justify-content-between">
                <span class="fw-medium">วันหยุดนักขัตฤกษ์ (วัน)</span>
                <span class="balance-days" id="leave-summary-holiday">28 <span class="small fw-normal text-body-secondary"></span></span>
            </div>

            <div class="balance-item d-flex align-items-center justify-content-between">
                <span class="fw-medium">สรุปวันลา (วัน)</span>
                <input type="text" id="leave-summary-total" class="form-control text-center fw-bold text-primary d-none" value="<?= (float) $initTotal ?>" readonly tabindex="-1">
                <span class="balance-days" id="leave-summary-total-show">0 <span class="small fw-normal text-body-secondary"></span></span>
            </div>
        </div>
    </div>


    <div class="d-grid mb-3">

        <?= Html::submitButton(
            '<i data-lucide="send" class="me-2" style="width:1.25rem;height:1.25rem;vertical-align:-0.3em;"></i> ส่งคำขอลา',
            [
                'class' => 'btn btn-primary btn-leave-submit',
                'name' => 'action',
                'value' => 'submit'
            ]
        ) ?>

    </div>

    <?php ActiveForm::end(); ?>

    <?php
    \app\widgets\datepicker\Assets::register($this);
    $this->registerJs("if (typeof thaiDatepicker === 'function') thaiDatepicker('#leave-date_start,#leave-date_end');", \yii\web\View::POS_END);
    $calDaysUrl       = \yii\helpers\Url::to(['/leave/leave/cal-days']);
    $updateShiftUrl   = \yii\helpers\Url::to(['/leave/leave/update-work-shift']);
    $empWorkShift     = $employee && isset($employee->work_shift) ? $employee->work_shift : 'normal';
    $csrfParam        = \Yii::$app->request->csrfParam;
    $csrfToken        = \Yii::$app->request->csrfToken;
    $js = <<<JS
(function(){
    var calDaysUrl     = "{$calDaysUrl}";
    var updateShiftUrl = "{$updateShiftUrl}";
    var csrfParam      = "{$csrfParam}";
    var csrfToken      = "{$csrfToken}";

    // ── helpers ───────────────────────────────────────────────────────────
    function getDateStart()     { var el = document.getElementById('leave-date_start');      return el ? el.value.trim() : ''; }
    function getDateEnd()       { var el = document.getElementById('leave-date_end');        return el ? el.value.trim() : ''; }
    function getDateStartType() { var el = document.getElementById('leave-date_start_type'); return el ? parseFloat(el.value) || 0 : 0; }
    function getDateEndType()   { var el = document.getElementById('leave-date_end_type');   return el ? parseFloat(el.value) || 0 : 0; }
    function getWorkShift()     { var el = document.getElementById('leave-work_shift');      return el ? el.value : ''; }
    function getLeaveTypeId()   { var el = document.getElementById('leave-create-form-leave_type_id'); return el ? el.value : ''; }

    // ── toggleTotalEditable — เวร 8: สรุปวันลาแก้ไขได้ ───────────────────
    function toggleTotalEditable() {
        var shiftEl = document.getElementById('leave-work_shift');
        var totalEl = document.getElementById('leave-summary-total');
        var hintEl  = document.getElementById('leave-total-hint');
        if (!totalEl) return;
        var isShift8 = shiftEl && shiftEl.value === 'shift';
        if (isShift8) {
            totalEl.removeAttribute('readonly');
            totalEl.tabIndex = 0;
            totalEl.classList.add('border-info');
            if (hintEl) hintEl.classList.remove('d-none');
        } else {
            totalEl.setAttribute('readonly', 'readonly');
            totalEl.tabIndex = -1;
            totalEl.classList.remove('border-info');
            if (hintEl) hintEl.classList.add('d-none');
        }
    }

    // ── updateSummary ──────────────────────────────────────────────────────
    function updateSummary(satsun, holiday, total) {
        var s = document.getElementById('leave-summary-satsun');
        var h = document.getElementById('leave-summary-holiday');
        var t = document.getElementById('leave-summary-total');
        var tShow = document.getElementById('leave-summary-total-show');
        var m = document.getElementById('leave-total_days_manual');
        var totalDaysInput = document.getElementById('leave-total_days');
        var b = document.getElementById('leave-total-days');
        if (s) s.textContent = satsun  != null ? satsun  : 0;
        if (h) h.textContent = holiday != null ? holiday : 0;
        if (t) t.value = total   != null ? total   : 0;
        if (tShow) tShow.textContent = (total != null ? total : 0);
        if (m) m.value = (total   != null ? total   : 0);
        if (totalDaysInput) totalDaysInput.value = (total != null ? total : 0);
        if (b) b.textContent = (total != null ? total : 0) + ' วัน';
    }

    // ── toggleDateEndType ─── ปิด end_type เมื่อ start==end ──────────────
    function toggleDateEndType() {
        var s = getDateStart();
        var e = getDateEnd();
        var endTypeEl = document.getElementById('leave-date_end_type');
        if (!endTypeEl) return;
        if (s && e && s.length >= 8 && e.length >= 8 && s === e) {
            endTypeEl.disabled = true;
            endTypeEl.value = '0';
        } else {
            endTypeEl.disabled = false;
        }
    }

    // ── calDays — เรียก /leave/leave/cal-days ─────────────────────────────
    function calDays() {
        var s = getDateStart();
        var e = getDateEnd();
        if (!s || !e || s.length < 8 || e.length < 8) { updateSummary(0, 0, 0); return; }
        var params = new URLSearchParams({
            date_start:      s,
            date_end:        e,
            date_start_type: getDateStartType(),
            date_end_type:   getDateEndType(),
            leave_type_id:   getLeaveTypeId(),
            work_shift:      getWorkShift(),
        });
        fetch(calDaysUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.status === 'error') {
                    updateSummary(0, 0, 0);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message, timer: 4000, showConfirmButton: false });
                    } else {
                        alert(res.message);
                    }
                    return;
                }
                updateSummary(res.satsunDays || 0, res.holiday || 0, res.total || 0);
                // auto-fill work_shift ถ้าผู้ใช้ยังไม่เลือก
                var shiftEl = document.getElementById('leave-work_shift');
                if (shiftEl && shiftEl.value === '' && res.shift) {
                    shiftEl.value = res.shift;
                }
            })
            .catch(function(){ updateSummary(0, 0, 0); });
    }

    // ── updateWorkShift — บันทึก work_shift ลง DB แล้ว recalc ────────────
    function updateWorkShift(val) {
        if (!val) { calDays(); return; }
        var fd = new FormData();
        fd.append('work_shift', val);
        fd.append(csrfParam, csrfToken);
        fetch(updateShiftUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(){ calDays(); })
            .catch(function(){ calDays(); });
    }

    // ── bind events ───────────────────────────────────────────────────────
    function bindEvents() {
        var startEl     = document.getElementById('leave-date_start');
        var endEl       = document.getElementById('leave-date_end');
        var shiftEl     = document.getElementById('leave-work_shift');
        var startTypeEl = document.getElementById('leave-date_start_type');
        var endTypeEl   = document.getElementById('leave-date_end_type');

        function onDateChange() { toggleDateEndType(); calDays(); }

        if (startEl) { startEl.addEventListener('change', onDateChange); startEl.addEventListener('blur', onDateChange); }
        if (endEl)   { endEl.addEventListener('change',   onDateChange); endEl.addEventListener('blur',   onDateChange); }
        if (startTypeEl) startTypeEl.addEventListener('change', calDays);
        if (endTypeEl)   endTypeEl.addEventListener('change',   calDays);

        if (shiftEl) {
            shiftEl.addEventListener('change', function(){
                toggleTotalEditable();
                updateWorkShift(this.value);
            });
        }

        // sync manual total → hidden field + badge เมื่อผู้ใช้แก้ไขตรง (เวร 8)
        var totalEl = document.getElementById('leave-summary-total');
        if (totalEl) {
            totalEl.addEventListener('input', function() {
                var v = parseFloat(this.value.replace(/[^0-9.]/g, '')) || 0;
                var m = document.getElementById('leave-total_days_manual');
                var b = document.getElementById('leave-total-days');
                if (m) m.value = v;
                if (b) b.textContent = v + ' วัน';
            });
        }

        // xdsoft datepicker: bind ผ่าน setOptions + changedatetime.xdsoft (ครอบคลุมทุก version)
        if (typeof jQuery !== 'undefined') {
            function onPickerChange() {
                setTimeout(function() { toggleDateEndType(); calDays(); }, 50);
            }
            jQuery('#leave-date_start, #leave-date_end').datetimepicker('setOptions', {
                onSelectDate: onPickerChange
            });
            jQuery(document).on('changedatetime.xdsoft', '#leave-date_start,#leave-date_end', onPickerChange);
        }
    }

    // ── leave type dropdown ───────────────────────────────────────────────
    var leaveTypeEl = document.getElementById('leave-create-form-leave_type_id');
    if (leaveTypeEl) {
        leaveTypeEl.addEventListener('change', function(){ calDays(); });
    }

    // ── init ──────────────────────────────────────────────────────────────
    bindEvents();
    toggleDateEndType();
    toggleTotalEditable();
    calDays();

    // โหมดแก้ไข: beforeSubmit ตรวจวันลา + ยืนยัน + ส่ง ajax
    var formUpdate = document.getElementById('form-leave-update');
    if (formUpdate && typeof jQuery !== 'undefined') {
        jQuery(formUpdate).on('beforeSubmit', function(e){
            e.preventDefault();
            var form = jQuery(this);
            var total = parseFloat(document.getElementById('leave-total_days') && document.getElementById('leave-total_days').value) || 0;
            if (total <= 0 && typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'วันลาต้องมากกว่า 0', text: '' });
                return false;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'ยืนยันบันทึก?', icon: 'question', showCancelButton: true, confirmButtonText: 'บันทึก', cancelButtonText: 'ยกเลิก' }).then(function(r){
                    if (!r.isConfirmed) return;
                    if (document.getElementById('main-modal')) jQuery('#main-modal').modal('hide');
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
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
    $this->registerJs($js, \yii\web\View::POS_END);
    ?>