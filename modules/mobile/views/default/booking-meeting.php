<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use app\widgets\datepicker\DatepickerThai;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'จองห้องประชุม';
$this->params['mobileSubtitle'] = 'เลือกวันที่และห้อง แล้วตรวจสอบเวลาว่าง';

$weekdays = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
$today = (int) date('j');
$month = (int) date('n');
$year = (int) date('Y');
$firstDay = (int) date('w', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
?>
<style>
.meeting-booking-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.meeting-booking-card .form-control,
.meeting-booking-card .form-select {
    border-radius: 12px;
    padding: 0.75rem 1rem;
}
.meeting-booking-card .form-label { font-weight: 500; }
.btn-meeting {
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 600;
}
/* Calendar */
.meeting-calendar {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    background: #fff;
}
.meeting-calendar-header {
    background: linear-gradient(135deg, #5D5FEF 0%, #4c4ed9 100%);
    color: #fff;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.9375rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.meeting-calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
    padding: 0.5rem 0.25rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}
.meeting-calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    padding: 0.5rem;
}
.meeting-calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8125rem;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}
.meeting-calendar-day.other-month { color: #adb5bd; }
.meeting-calendar-day.today {
    background: rgba(93, 95, 239, 0.15);
    color: #5D5FEF;
    font-weight: 600;
}
.meeting-calendar-day.selected {
    background: #5D5FEF;
    color: #fff;
    font-weight: 600;
}
.meeting-calendar-day:not(.other-month):active { background: rgba(93, 95, 239, 0.25); }
/* Room cards */
.room-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease;
}
.room-card:hover { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
.room-card .room-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.room-card .room-icon.available { background: rgba(25, 135, 84, 0.12); }
.room-card .room-icon.available i { color: #198754; }
.room-card .room-icon.occupied { background: rgba(220, 53, 69, 0.12); }
.room-card .room-icon.occupied i { color: #dc3545; }
.room-list-empty { padding: 2rem 1rem; text-align: center; color: #6c757d; font-size: 0.9375rem; }
</style>

<div class="booking-header mb-3">
    <h1 class="h5 fw-semibold text-dark mb-0">จองห้องประชุม</h1>
    <p class="small text-body-secondary mb-0">เลือกวันที่และห้อง แล้วตรวจสอบเวลาว่าง</p>
</div>

<!-- Calendar -->
<div class="meeting-calendar mb-3">
    <div class="meeting-calendar-header">
        <span id="cal-month-label"><?= $year + 543 ?> — <?= ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'][$month] ?></span>
    </div>
    <div class="meeting-calendar-weekdays">
        <?php foreach ($weekdays as $w): ?>
            <span><?= $w ?></span>
        <?php endforeach; ?>
    </div>
    <div class="meeting-calendar-days" id="meeting-calendar-days">
        <?php
        $blank = $firstDay;
        for ($i = 0; $i < $blank; $i++) {
            echo '<span class="meeting-calendar-day other-month"></span>';
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cls = 'meeting-calendar-day';
            if ($d === $today) $cls .= ' today';
            echo '<span class="' . $cls . '" data-day="' . $d . '" data-month="' . $month . '" data-year="' . $year . '">' . $d . '</span>';
        }
        ?>
    </div>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'mobile-booking-meeting-form',
    'options' => ['class' => ''],
]); ?>

<div class="card meeting-booking-card mb-3">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-medium">ห้องประชุม <span class="text-danger">*</span></label>
            <select name="room_id" id="booking-room" class="form-select">
                <option value="">— เลือกห้อง —</option>
                <option value="1">ห้องประชุม A (10 ที่นั่ง)</option>
                <option value="2">ห้องประชุม B (20 ที่นั่ง)</option>
                <option value="3">ห้องประชุม C (30 ที่นั่ง)</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">วันที่ <span class="text-danger">*</span></label>
            <?= DatepickerThai::widget([
                'name' => 'meeting_date',
                'value' => date('d/m/Y'),
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
                <input type="time" name="time_start" class="form-control" value="09:00" step="300">
            </div>
            <div class="col-6">
                <label class="form-label fw-medium">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                <input type="time" name="time_end" class="form-control" value="10:00" step="300">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">หัวข้อประชุม <span class="text-danger">*</span></label>
            <input type="text" name="meeting_title" class="form-control" placeholder="ระบุหัวข้อการประชุม" autocomplete="off">
        </div>
        <div class="mb-0">
            <label class="form-label fw-medium">จำนวนผู้เข้าร่วม</label>
            <input type="number" name="attendees" class="form-control" min="1" max="999" value="1" placeholder="จำนวนคน">
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
        ยืนยันการจอง
    </button>
</div>

<?php ActiveForm::end(); ?>

<!-- Room availability list -->
<div class="card meeting-booking-card mb-3" id="room-availability-card">
    <div class="card-body">
        <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i data-lucide="layout-grid" style="width: 1.25rem; height: 1.25rem;"></i>
            ห้องประชุมและสถานะ
        </h6>
        <div id="room-list-empty" class="room-list-empty">
            <i data-lucide="calendar-search" style="width: 2rem; height: 2rem; opacity: 0.4;" class="mb-2 d-block mx-auto"></i>
            เลือกวันที่และช่วงเวลา แล้วกด "ตรวจสอบเวลาว่าง" เพื่อดูห้องว่าง
        </div>
        <div id="room-list" class="d-none">
            <div class="room-item mb-2" data-room-id="1">
                <div class="card room-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="room-icon available flex-shrink-0">
                            <i data-lucide="layout-grid" style="width: 1.25rem; height: 1.25rem;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">ห้องประชุม A</div>
                            <div class="small text-body-secondary">ความจุ 10 ที่นั่ง</div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0">ว่าง</span>
                    </div>
                </div>
            </div>
            <div class="room-item mb-2" data-room-id="2">
                <div class="card room-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="room-icon occupied flex-shrink-0">
                            <i data-lucide="layout-grid" style="width: 1.25rem; height: 1.25rem;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">ห้องประชุม B</div>
                            <div class="small text-body-secondary">ความจุ 20 ที่นั่ง</div>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0">ไม่ว่าง</span>
                    </div>
                </div>
            </div>
            <div class="room-item mb-2" data-room-id="3">
                <div class="card room-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="room-icon available flex-shrink-0">
                            <i data-lucide="layout-grid" style="width: 1.25rem; height: 1.25rem;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">ห้องประชุม C</div>
                            <div class="small text-body-secondary">ความจุ 30 ที่นั่ง</div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1 flex-shrink-0">ว่าง</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
(function() {
    var daysEl = document.getElementById('meeting-calendar-days');
    var dateInput = document.getElementById('booking-meeting-date');
    var btnCheck = document.getElementById('btn-check-availability');
    var emptyEl = document.getElementById('room-list-empty');
    var listEl = document.getElementById('room-list');

    if (daysEl) {
        daysEl.addEventListener('click', function(e) {
            var day = e.target.closest('.meeting-calendar-day');
            if (!day || day.classList.contains('other-month') || !day.dataset.day) return;
            daysEl.querySelectorAll('.meeting-calendar-day.selected').forEach(function(d) { d.classList.remove('selected'); });
            daysEl.querySelectorAll('.meeting-calendar-day.today').forEach(function(d) { d.classList.remove('today'); });
            day.classList.add('selected');
            var d = day.dataset.day, m = day.dataset.month, y = day.dataset.year;
            var dStr = (String(d).length === 1 ? '0' + d : d) + '/' + (String(m).length === 1 ? '0' + m : m) + '/' + y;
            if (dateInput) dateInput.value = dStr;
        });
    }

    if (btnCheck && emptyEl && listEl) {
        btnCheck.addEventListener('click', function() {
            emptyEl.classList.add('d-none');
            listEl.classList.remove('d-none');
            if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        });
    }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
