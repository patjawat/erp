<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use app\widgets\datepicker\DatepickerThai;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Meeting $meeting */
/** @var array $rooms */
/** @var array $saveErrors */
$this->params['current_page']   = $current_page ?? 'profile';
$this->params['mobileTitle']    = 'แก้ไขการจองห้องประชุม';
$this->params['mobileSubtitle'] = $meeting->code ?? '';

$rooms = $rooms ?? [];
$saveErrors = $saveErrors ?? [];
$weekdays = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
$today = (int) date('j');
$month = (int) date('n');
$year = (int) date('Y');
$firstDay = (int) date('w', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));

$dateValue = $meeting->date_start ? date('d/m/Y', strtotime($meeting->date_start)) : date('d/m/Y');
$timeStart = substr($meeting->time_start ?? '09:00', 0, 5);
$timeEnd = substr($meeting->time_end ?? '10:00', 0, 5);
?>
<style>
.meeting-booking-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.meeting-booking-card .form-control, .meeting-booking-card .form-select { border-radius: 12px; padding: 0.75rem 1rem; }
.meeting-booking-card .form-label { font-weight: 500; }
.btn-meeting { border-radius: 12px; padding: 0.875rem 1rem; font-size: 1rem; font-weight: 600; }
.meeting-calendar { border-radius: 16px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); background: #fff; }
.meeting-calendar-header { background: linear-gradient(135deg, #5D5FEF 0%, #4c4ed9 100%); color: #fff; padding: 0.75rem 1rem; font-weight: 600; font-size: 0.9375rem; display: flex; align-items: center; justify-content: space-between; }
.meeting-calendar-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-size: 0.75rem; color: #6c757d; font-weight: 500; padding: 0.5rem 0.25rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.meeting-calendar-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; padding: 0.5rem; }
.meeting-calendar-day { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; font-size: 0.8125rem; border-radius: 10px; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; }
.meeting-calendar-day.other-month { color: #adb5bd; }
.meeting-calendar-day.today { background: rgba(93, 95, 239, 0.15); color: #5D5FEF; font-weight: 600; }
.meeting-calendar-day.selected { background: #5D5FEF; color: #fff; font-weight: 600; }
.room-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06); }
.room-card .room-icon { width: 2.5rem; height: 2.5rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.room-card .room-icon.available { background: rgba(25, 135, 84, 0.12); }
.room-card .room-icon.available i { color: #198754; }
.room-card .room-icon.occupied { background: rgba(220, 53, 69, 0.12); }
.room-card .room-icon.occupied i { color: #dc3545; }
.room-list-empty { padding: 2rem 1rem; text-align: center; color: #6c757d; font-size: 0.9375rem; }
.btn-back-mobile { border-radius: 12px; }
</style>

<div class="d-flex flex-column gap-3">
    <a href="<?= Html::encode(Url::to(['/mobile/default/meeting-view', 'id' => $meeting->id])) ?>" class="btn btn-outline-secondary btn-back-mobile align-self-start">
        <i data-lucide="arrow-left" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i> กลับ
    </a>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($saveErrors)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <ul class="mb-0 small"><?php foreach ($saveErrors as $msg): ?><li><?= Html::encode(is_string($msg) ? $msg : reset($msg)) ?></li><?php endforeach; ?></ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
        </div>
    <?php endif; ?>

    <div class="meeting-calendar mb-3">
        <div class="meeting-calendar-header">
            <span id="cal-month-label"><?= $year + 543 ?> — <?= ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'][$month] ?></span>
        </div>
        <div class="meeting-calendar-weekdays">
            <?php foreach ($weekdays as $w): ?><span><?= $w ?></span><?php endforeach; ?>
        </div>
        <div class="meeting-calendar-days" id="meeting-calendar-days">
            <?php
            $blank = $firstDay;
            for ($i = 0; $i < $blank; $i++) echo '<span class="meeting-calendar-day other-month"></span>';
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cls = 'meeting-calendar-day';
                if ($d === $today) $cls .= ' today';
                echo '<span class="' . $cls . '" data-day="' . $d . '" data-month="' . $month . '" data-year="' . $year . '">' . $d . '</span>';
            }
            ?>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'mobile-meeting-update-form',
        'action' => ['/mobile/default/meeting-update', 'id' => $meeting->id],
        'options' => ['class' => ''],
    ]); ?>

    <div class="card meeting-booking-card mb-3">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-medium">ห้องประชุม <span class="text-danger">*</span></label>
                <select name="room_id" id="booking-room" class="form-select" required>
                    <option value="">— เลือกห้อง —</option>
                    <?php foreach ($rooms as $code => $title): ?>
                        <option value="<?= Html::encode($code) ?>" <?= ($meeting->room_id === (string)$code) ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">วันที่ <span class="text-danger">*</span></label>
                <?= DatepickerThai::widget([
                    'name' => 'meeting_date',
                    'value' => $dateValue,
                    'options' => [
                        'id' => 'booking-meeting-date',
                        'placeholder' => 'เลือกวันที่ประชุม',
                        'class' => 'form-control',
                    ],
                ]) ?>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-medium">เวลาเริ่ม <span class="text-danger">*</span></label>
                    <input type="time" name="time_start" class="form-control" value="<?= Html::encode($timeStart) ?>" step="300">
                </div>
                <div class="col-6">
                    <label class="form-label fw-medium">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                    <input type="time" name="time_end" class="form-control" value="<?= Html::encode($timeEnd) ?>" step="300">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">หัวข้อประชุม <span class="text-danger">*</span></label>
                <input type="text" name="meeting_title" class="form-control" placeholder="ระบุหัวข้อการประชุม" value="<?= Html::encode($meeting->title) ?>" autocomplete="off" required>
            </div>
            <div class="mb-0">
                <label class="form-label fw-medium">จำนวนผู้เข้าร่วม</label>
                <input type="number" name="attendees" class="form-control" min="1" max="999" value="<?= (int)($meeting->emp_number ?? 1) ?>" placeholder="จำนวนคน">
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 mb-3">
        <button type="button" class="btn btn-outline-primary btn-meeting" id="btn-check-availability">
            <i data-lucide="calendar-check" class="me-1" style="width: 1.15rem; height: 1.15rem; vertical-align: -0.25em;"></i>
            ตรวจสอบเวลาว่าง
        </button>
        <button type="submit" class="btn btn-primary btn-meeting" name="action" value="submit">
            <i data-lucide="check-circle" class="me-1" style="width: 1.15rem; height: 1.15rem; vertical-align: -0.25em;"></i>
            บันทึกการแก้ไข
        </button>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="card meeting-booking-card mb-3" id="room-availability-card">
        <div class="card-body">
            <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                <i data-lucide="layout-grid" style="width: 1.25rem; height: 1.25rem;"></i>
                ห้องประชุมและสถานะ
            </h6>
            <div id="room-list-empty" class="room-list-empty">
                เลือกวันที่และช่วงเวลา แล้วกด "ตรวจสอบเวลาว่าง"
            </div>
            <div id="room-list-loading" class="room-list-empty d-none">
                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                <span>กำลังตรวจสอบ...</span>
            </div>
            <div id="room-list" class="d-none"></div>
        </div>
    </div>
</div>

<?php
$availabilityUrl = Url::to(['/mobile/default/meeting-room-availability']);
$excludeId = (int) $meeting->id;
$js = <<<JS
(function() {
    var daysEl = document.getElementById('meeting-calendar-days');
    var dateInput = document.getElementById('booking-meeting-date');
    var timeStartInput = document.querySelector('input[name="time_start"]');
    var timeEndInput = document.querySelector('input[name="time_end"]');
    var btnCheck = document.getElementById('btn-check-availability');
    var emptyEl = document.getElementById('room-list-empty');
    var loadingEl = document.getElementById('room-list-loading');
    var listEl = document.getElementById('room-list');
    var excludeId = {$excludeId};

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }
    if (daysEl && dateInput) {
        daysEl.addEventListener('click', function(e) {
            var day = e.target.closest('.meeting-calendar-day');
            if (!day || day.classList.contains('other-month') || !day.dataset.day) return;
            daysEl.querySelectorAll('.meeting-calendar-day.selected').forEach(function(d) { d.classList.remove('selected'); });
            day.classList.add('selected');
            var d = day.dataset.day, m = day.dataset.month, y = day.dataset.year;
            var dStr = (String(d).length === 1 ? '0' + d : d) + '/' + (String(m).length === 1 ? '0' + m : m) + '/' + y;
            dateInput.value = dStr;
        });
    }
    var formEl = document.getElementById('mobile-meeting-update-form');
    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            if (!confirm('คุณต้องการบันทึกการแก้ไขใช่หรือไม่?')) e.preventDefault();
        });
    }
    if (btnCheck && emptyEl && loadingEl && listEl) {
        btnCheck.addEventListener('click', function() {
            var meetingDate = dateInput ? dateInput.value.trim() : '';
            var timeStart = timeStartInput ? timeStartInput.value.trim() : '';
            var timeEnd = timeEndInput ? timeEndInput.value.trim() : '';
            if (!meetingDate || !timeStart || !timeEnd) {
                alert('กรุณาเลือกวันที่ และเวลาเริ่ม–สิ้นสุด');
                return;
            }
            emptyEl.classList.add('d-none');
            listEl.classList.add('d-none');
            loadingEl.classList.remove('d-none');
            listEl.innerHTML = '';
            var url = '{$availabilityUrl}' + '?meeting_date=' + encodeURIComponent(meetingDate) + '&time_start=' + encodeURIComponent(timeStart) + '&time_end=' + encodeURIComponent(timeEnd) + '&exclude_id=' + excludeId;
            fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    loadingEl.classList.add('d-none');
                    if (data.ok && data.rooms && data.rooms.length) {
                        var html = '';
                        data.rooms.forEach(function(r) {
                            var cap = r.capacity != null ? 'ความจุ ' + r.capacity + ' ที่นั่ง' : '';
                            var iconCls = r.available ? 'room-icon available' : 'room-icon occupied';
                            var badgeCls = r.available
                                ? 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0'
                                : 'badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0';
                            var badgeText = r.available ? 'ว่าง' : 'ไม่ว่าง';
                            html += '<div class="room-item mb-2"><div class="card room-card"><div class="card-body d-flex align-items-center gap-3">' +
                                '<div class="' + iconCls + ' flex-shrink-0"><i data-lucide="layout-grid" style="width: 1.25rem; height: 1.25rem;"></i></div>' +
                                '<div class="flex-grow-1 min-w-0"><div class="fw-semibold">' + escapeHtml(r.title) + '</div><div class="small text-body-secondary">' + escapeHtml(cap) + '</div></div>' +
                                '<span class="' + badgeCls + '">' + badgeText + '</span></div></div></div>';
                        });
                        listEl.innerHTML = html;
                        listEl.classList.remove('d-none');
                        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
                    } else {
                        emptyEl.classList.remove('d-none');
                        if (data.message) emptyEl.innerHTML = '<span class="text-danger">' + escapeHtml(data.message) + '</span>';
                    }
                })
                .catch(function() {
                    loadingEl.classList.add('d-none');
                    emptyEl.classList.remove('d-none');
                    emptyEl.innerHTML = '<span class="text-danger">ไม่สามารถโหลดข้อมูลได้</span>';
                });
        });
    }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
