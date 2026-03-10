<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use app\widgets\datepicker\DatepickerThai;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ขอลาออนไลน์';
$this->params['mobileSubtitle'] = 'ตรวจสอบสิทธิ์และส่งคำขอลา';

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
.leave-card .form-label { font-weight: 500; }
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
.balance-item .balance-days { font-size: 1.25rem; font-weight: 700; color: #5D5FEF; }
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
.workflow-step:last-child::after { display: none; }
.workflow-step.done::after { background: #5D5FEF; opacity: 0.4; }
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
.workflow-step .step-label { font-size: 0.6875rem; margin-top: 0.35rem; color: #6c757d; }
.workflow-step.done .step-label { color: #5D5FEF; font-weight: 500; }
.btn-attach {
    border-radius: 12px;
    padding: 0.75rem 1rem;
    border: 2px dashed #dee2e6;
    background: #fff;
    color: #6c757d;
    font-weight: 500;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.btn-attach:hover { border-color: #5D5FEF; background: rgba(93, 95, 239, 0.06); color: #5D5FEF; }
.attach-filename { font-size: 0.875rem; color: #198754; margin-top: 0.35rem; }
</style>

<div class="booking-header mb-3">
    <h1 class="h5 fw-semibold text-dark mb-0">ขอลาออนไลน์</h1>
    <p class="small text-body-secondary mb-0">ตรวจสอบสิทธิ์และส่งคำขอลา</p>
</div>

<!-- Leave balance -->
<div class="card leave-card mb-3">
    <div class="card-body">
        <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i data-lucide="wallet" style="width: 1.25rem; height: 1.25rem;"></i>
            สิทธิ์การลาคงเหลือ
        </h6>
        <div class="d-flex flex-column gap-2">
            <?php foreach ($balanceItems as $b): ?>
                <?php $left = $b['days'] - $b['used']; ?>
                <div class="balance-item d-flex align-items-center justify-content-between">
                    <span class="fw-medium"><?= Html::encode($b['label']) ?></span>
                    <span class="balance-days"><?= $left ?> <span class="small fw-normal text-body-secondary">/ <?= $b['days'] ?> <?= $b['unit'] ?></span></span>
                </div>
            <?php endforeach; ?>
        </div>
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
            <?php foreach ($workflowSteps as $i => $step): ?>
                <div class="col-3 workflow-step <?= $step['done'] ? 'done' : '' ?>">
                    <div class="step-dot"><?= $step['done'] ? '✓' : ($i + 1) ?></div>
                    <span class="step-label"><?= Html::encode($step['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'mobile-leave-request-form',
    'options' => ['class' => '', 'enctype' => 'multipart/form-data'],
]); ?>

<div class="card leave-card mb-3">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-medium">ประเภทการลา <span class="text-danger">*</span></label>
            <select name="leave_type" id="leave-type" class="form-select" required>
                <?php foreach ($leaveTypes as $value => $label): ?>
                    <option value="<?= Html::encode($value) ?>"><?= Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">วันที่เริ่มลา <span class="text-danger">*</span></label>
            <?= DatepickerThai::widget([
                'name' => 'date_start',
                'value' => '',
                'options' => [
                    'id' => 'leave-date-start',
                    'placeholder' => 'เลือกวันที่เริ่มลา',
                    'class' => 'form-control',
                ],
            ]) ?>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">วันที่สิ้นสุด <span class="text-danger">*</span></label>
            <?= DatepickerThai::widget([
                'name' => 'date_end',
                'value' => '',
                'options' => [
                    'id' => 'leave-date-end',
                    'placeholder' => 'เลือกวันที่สิ้นสุด',
                    'class' => 'form-control',
                ],
            ]) ?>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">จำนวนวัน <span class="text-danger">*</span></label>
            <input type="number" name="days" id="leave-days" class="form-control" min="0.5" max="365" step="0.5" value="1" placeholder="วัน" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">เหตุผลการลา <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control" rows="3" placeholder="ระบุเหตุผลการลา" required></textarea>
        </div>
        <div class="mb-0">
            <label class="form-label fw-medium">แนบเอกสาร</label>
            <input type="file" name="attachment[]" id="leave-attachment" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
            <button type="button" class="btn btn-attach w-100 d-flex align-items-center justify-content-center gap-2" id="btn-attach-leave">
                <i data-lucide="paperclip" style="width: 1.25rem; height: 1.25rem;"></i>
                เลือกไฟล์แนบ
            </button>
            <div id="attach-filenames" class="attach-filename d-none"></div>
        </div>
    </div>
</div>

<div class="d-grid mb-3">
    <button type="submit" class="btn btn-primary btn-leave-submit" name="action" value="submit">
        <i data-lucide="send" class="me-2" style="width: 1.25rem; height: 1.25rem; vertical-align: -0.3em;"></i>
        ส่งคำขอลา
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<'JS'
(function() {
    var startEl = document.getElementById('leave-date-start');
    var endEl = document.getElementById('leave-date-end');
    var daysEl = document.getElementById('leave-days');
    var attachInput = document.getElementById('leave-attachment');
    var btnAttach = document.getElementById('btn-attach-leave');
    var attachNames = document.getElementById('attach-filenames');

    function parseDdMmYy(str) {
        if (!str || !/^\d{1,2}\/\d{1,2}\/\d{2,4}$/.test(str.trim())) return null;
        var parts = str.trim().split('/');
        var d = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10) - 1;
        var y = parseInt(parts[2], 10);
        if (y < 100) y += 2500;
        if (y > 2500) y -= 543;
        var date = new Date(y, m, d);
        return isNaN(date.getTime()) ? null : date;
    }
    function diffDays(start, end) {
        var ms = end - start;
        return Math.round(ms / (24 * 60 * 60 * 1000)) + 1;
    }
    function updateDays() {
        if (!startEl || !endEl || !daysEl) return;
        var start = parseDdMmYy(startEl.value);
        var end = parseDdMmYy(endEl.value);
        if (start && end && end >= start) {
            var days = diffDays(start, end);
            daysEl.value = days;
        }
    }
    if (startEl) startEl.addEventListener('change', updateDays);
    if (endEl) endEl.addEventListener('change', updateDays);

    if (btnAttach && attachInput) {
        btnAttach.addEventListener('click', function() { attachInput.click(); });
        attachInput.addEventListener('change', function() {
            var files = this.files;
            if (!files.length) {
                attachNames.classList.add('d-none');
                attachNames.textContent = '';
                return;
            }
            var names = [];
            for (var i = 0; i < files.length; i++) names.push(files[i].name);
            attachNames.textContent = 'แนบแล้ว: ' + names.join(', ');
            attachNames.classList.remove('d-none');
        });
    }
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
