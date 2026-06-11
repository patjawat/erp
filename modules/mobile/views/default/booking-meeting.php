<?php

use app\modules\booking\models\Meeting;
use app\widgets\datepicker\DatepickerThai;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var Meeting $model */
/** @var array $rooms รายการห้องจาก booking (code => title) */
/** @var array $saveErrors */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'จองห้องประชุม';
$this->params['mobileSubtitle'] = 'เลือกวันที่และห้อง แล้วตรวจสอบเวลาว่าง';

$rooms      = $rooms ?? [];
$saveErrors = $saveErrors ?? [];

$weekdays    = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
$today       = (int) date('j');
$month       = (int) date('n');
$year        = (int) date('Y');
$firstDay    = (int) date('w', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
?>
<style>
.btn-meeting {
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 600;
}
/* Calendar */
.meeting-calendar { overflow: hidden; background: #fff; }
.meeting-calendar-header {
    background: linear-gradient(135deg, var(--mobile-primary) 0%, var(--mobile-primary-dark) 100%);
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
.meeting-calendar-day.other-month { color: #adb5bd; cursor: default; }
.meeting-calendar-day.today {
    background: var(--mobile-primary-soft-border);
    color: var(--mobile-primary);
    font-weight: 600;
}
.meeting-calendar-day.selected {
    background: var(--mobile-primary);
    color: #fff;
    font-weight: 600;
}
.meeting-calendar-day:not(.other-month):active { background: var(--mobile-primary-soft-strong); }
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

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
    </div>
<?php endif; ?>
<?php if (!empty($saveErrors)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <p class="mb-1 small fw-semibold">กรุณาตรวจสอบข้อมูลที่กรอก</p>
        <ul class="mb-0 small">
            <?php foreach ($saveErrors as $attr => $msg): ?>
                <li><?= Html::encode(is_string($msg) ? $msg : (string) reset((array) $msg)) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
    </div>
<?php endif; ?>

<!-- Calendar -->
<div class="meeting-calendar mb-3">
    <div class="meeting-calendar-header">
        <span id="cal-month-label">
            <?= $year + 543 ?> · <?= ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'][$month] ?>
        </span>
    </div>
    <div class="meeting-calendar-weekdays">
        <?php foreach ($weekdays as $w): ?>
            <span><?= Html::encode($w) ?></span>
        <?php endforeach; ?>
    </div>
    <div class="meeting-calendar-days" id="meeting-calendar-days">
        <?php
        for ($i = 0; $i < $firstDay; $i++) {
            echo '<span class="meeting-calendar-day other-month"></span>';
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cls = 'meeting-calendar-day';
            if ($d === $today) {
                $cls .= ' today';
            }
            echo '<span class="' . $cls . '" data-day="' . $d . '" data-month="' . $month . '" data-year="' . $year . '">' . $d . '</span>';
        }
        ?>
    </div>
</div>

<?php $form = ActiveForm::begin([
    'id'      => 'mobile-booking-meeting-form',
    'method'  => 'post',
    'options' => ['novalidate' => 'novalidate'],
    'fieldConfig' => [
        'options'      => ['class' => 'mb-3'],
        'labelOptions' => ['class' => 'form-label fw-medium'],
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ],
]); ?>

<div class="card meeting-booking-card mb-3">
    <div class="card-body">

        <?= $form->field($model, 'room_id')->dropDownList($rooms, [
            'prompt'   => 'เลือกห้อง',
            'required' => true,
        ])->label('ห้องประชุม') ?>

        <?= $form->field($model, 'date_start')->widget(DatepickerThai::class, [
            'options' => [
                'placeholder' => 'เลือกวันที่ประชุม',
                'class'       => 'form-control',
                'autocomplete' => 'off',
                'required'    => true,
            ],
        ])->label('วันที่') ?>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <?= $form->field($model, 'time_start', ['options' => ['class' => 'mb-0']])->input('time', [
                    'class'    => 'form-control',
                    'step'     => 300,
                    'required' => true,
                ])->label('เวลาเริ่ม') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'time_end', ['options' => ['class' => 'mb-0']])->input('time', [
                    'class'    => 'form-control',
                    'step'     => 300,
                    'required' => true,
                ])->label('เวลาสิ้นสุด') ?>
            </div>
        </div>

        <?= $form->field($model, 'title')->textInput([
            'maxlength'   => 255,
            'placeholder' => 'ระบุหัวข้อการประชุม',
            'autocomplete' => 'off',
            'required'    => true,
        ])->label('หัวข้อประชุม') ?>

        <?= $form->field($model, 'emp_number', ['options' => ['class' => 'mb-0']])->input('number', [
            'min'         => 1,
            'max'         => 999,
            'placeholder' => 'จำนวนคน',
        ])->label('จำนวนผู้เข้าร่วม') ?>

    </div>
</div>

<div class="d-grid gap-2 mb-3">
    <button type="button" class="btn btn-outline-primary btn-meeting" id="btn-check-availability">
        <i data-lucide="calendar-check" class="mi-sm mi-baseline me-1"></i>
        ตรวจสอบเวลาว่าง
    </button>
    <?= Html::submitButton(
        '<i data-lucide="check-circle" class="mi-sm mi-baseline me-1"></i> ยืนยันการจอง',
        ['class' => 'btn btn-primary btn-meeting', 'name' => 'action', 'value' => 'submit']
    ) ?>
</div>

<?php ActiveForm::end(); ?>

<!-- Room availability list -->
<div class="card meeting-booking-card mb-3" id="room-availability-card">
    <div class="card-body">
        <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i data-lucide="layout-grid" class="mi-md"></i>
            ห้องประชุมและสถานะ
        </h6>
        <div id="room-list-empty" class="room-list-empty">
            <i data-lucide="calendar-search" class="mi-xl mb-2 d-block mx-auto" style="opacity: 0.4;"></i>
            เลือกวันที่และช่วงเวลา แล้วกด "ตรวจสอบเวลาว่าง" เพื่อดูห้องว่าง
        </div>
        <div id="room-list-loading" class="room-list-empty d-none" role="status" aria-live="polite">
            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
            <span>กำลังตรวจสอบ...</span>
        </div>
        <div id="room-list" class="d-none" role="list"></div>
    </div>
</div>

<?php
$availabilityUrl = Url::to(['/mobile/default/meeting-room-availability']);
$formFieldId = function (string $attr) use ($model): string {
    return Html::getInputId($model, $attr);
};
$dateInputId    = $formFieldId('date_start');
$timeStartId    = $formFieldId('time_start');
$timeEndId      = $formFieldId('time_end');
$roomSelectId   = $formFieldId('room_id');

$js = <<<JS
(function() {
    var daysEl         = document.getElementById('meeting-calendar-days');
    var dateInput      = document.getElementById('{$dateInputId}');
    var timeStartInput = document.getElementById('{$timeStartId}');
    var timeEndInput   = document.getElementById('{$timeEndId}');
    var roomSelect     = document.getElementById('{$roomSelectId}');
    var btnCheck       = document.getElementById('btn-check-availability');
    var emptyEl        = document.getElementById('room-list-empty');
    var loadingEl      = document.getElementById('room-list-loading');
    var listEl         = document.getElementById('room-list');
    var availabilityUrl = "{$availabilityUrl}";

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s == null ? '' : String(s);
        return div.innerHTML;
    }

    // Calendar day → mirror into Thai date input
    if (daysEl && dateInput) {
        daysEl.addEventListener('click', function(e) {
            var day = e.target.closest('.meeting-calendar-day');
            if (!day || day.classList.contains('other-month') || !day.dataset.day) return;
            daysEl.querySelectorAll('.meeting-calendar-day.selected').forEach(function(d) { d.classList.remove('selected'); });
            day.classList.add('selected');
            var d = day.dataset.day, m = day.dataset.month, y = day.dataset.year;
            var dStr = (String(d).length === 1 ? '0' + d : d)
                + '/' + (String(m).length === 1 ? '0' + m : m)
                + '/' + y;
            dateInput.value = dStr;
            dateInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    // Form submit → erp.js handleFormSubmit: Swal confirm + AJAX + JSON response handling
    // (supports inline ActiveForm validation messages via form.yiiActiveForm('updateMessages')).
    if (typeof handleFormSubmit === 'function') {
        handleFormSubmit('#mobile-booking-meeting-form', null, function(response) {
            // Success callback — let handleFormSubmit redirect via response.redirect_url.
        });
    }

    // Availability check
    if (btnCheck && emptyEl && loadingEl && listEl) {
        btnCheck.addEventListener('click', function() {
            var meetingDate = dateInput      ? dateInput.value.trim()      : '';
            var timeStart   = timeStartInput ? timeStartInput.value.trim() : '';
            var timeEnd     = timeEndInput   ? timeEndInput.value.trim()   : '';

            if (!meetingDate || !timeStart || !timeEnd) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'ข้อมูลไม่ครบ', text: 'กรุณาเลือกวันที่ และเวลาเริ่ม-สิ้นสุด' });
                } else {
                    alert('กรุณาเลือกวันที่ และเวลาเริ่ม-สิ้นสุด');
                }
                return;
            }

            emptyEl.classList.add('d-none');
            listEl.classList.add('d-none');
            loadingEl.classList.remove('d-none');
            listEl.innerHTML = '';

            var params = new URLSearchParams({
                meeting_date: meetingDate,
                time_start:   timeStart,
                time_end:     timeEnd,
            });
            fetch(availabilityUrl + '?' + params.toString(), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
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
                        // Pick-button when room is available and we have the room select
                        var pickBtn = (r.available && roomSelect)
                            ? '<button type="button" class="btn btn-sm btn-outline-primary rounded-3 ms-2 flex-shrink-0 js-room-pick" data-room-code="' + escapeHtml(r.code) + '">เลือก</button>'
                            : '';
                        html += '<div class="room-item mb-2" role="listitem">'
                            + '<div class="card room-card">'
                            +   '<div class="card-body d-flex align-items-center gap-3">'
                            +     '<div class="' + iconCls + ' flex-shrink-0"><i data-lucide="layout-grid" class="mi-md"></i></div>'
                            +     '<div class="flex-grow-1 min-w-0">'
                            +       '<div class="fw-semibold">' + escapeHtml(r.title) + '</div>'
                            +       '<div class="small text-body-secondary">' + escapeHtml(cap) + '</div>'
                            +     '</div>'
                            +     '<span class="' + badgeCls + '">' + badgeText + '</span>'
                            +     pickBtn
                            +   '</div>'
                            + '</div>'
                            + '</div>';
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

    // Delegate "เลือก" click → set room_id select and scroll back to form
    if (listEl && roomSelect) {
        listEl.addEventListener('click', function(e) {
            var btn = e.target.closest('.js-room-pick');
            if (!btn) return;
            var code = btn.dataset.roomCode;
            if (!code) return;
            roomSelect.value = code;
            roomSelect.dispatchEvent(new Event('change', { bubbles: true }));
            roomSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
