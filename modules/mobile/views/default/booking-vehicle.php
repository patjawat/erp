<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use app\widgets\datepicker\DatepickerThai;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'จองรถราชการ';
$this->params['mobileSubtitle'] = 'กรอกข้อมูลเพื่อตรวจสอบรถว่างและส่งคำขอ';
?>
<style>
.btn-booking {
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    font-weight: 600;
}
.vehicle-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.2s ease;
}
.vehicle-card:hover { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
.vehicle-card .vehicle-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 12px;
    background: rgba(13, 110, 253, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}
.vehicle-card .vehicle-icon i { color: var(--mobile-primary, #0d6efd); }
.vehicle-card .vehicle-thumb { width: 3.5rem; height: 2.75rem; border-radius: 10px; object-fit: cover; background: #e9ecef; }
.vehicle-list-empty { padding: 2rem 1rem; text-align: center; color: #6c757d; font-size: 0.9375rem; }
</style>

<?php $form = ActiveForm::begin([
    'id' => 'mobile-booking-vehicle-form',
    'options' => ['class' => ''],
]); ?>

<div class="card booking-card mb-3">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-medium">วันที่เดินทาง <span class="text-danger">*</span></label>
            <?= DatepickerThai::widget([
                'name' => 'travel_date',
                'value' => date('d/m/Y'),
                'options' => [
                    'id' => 'booking-travel-date',
                    'placeholder' => 'เลือกวันที่เดินทาง',
                    'class' => 'form-control',
                ],
            ]) ?>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">เวลาเดินทาง <span class="text-danger">*</span></label>
            <input type="time" name="travel_time" class="form-control" value="08:00" step="300">
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">จุดหมายปลายทาง <span class="text-danger">*</span></label>
            <input type="text" name="destination" class="form-control" placeholder="ระบุจุดหมายปลายทาง" autocomplete="off">
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">วัตถุประสงค์ <span class="text-danger">*</span></label>
            <textarea name="purpose" class="form-control" rows="2" placeholder="ระบุวัตถุประสงค์การเดินทาง"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">จำนวนผู้โดยสาร <span class="text-danger">*</span></label>
            <input type="number" name="passengers" class="form-control" min="1" max="99" value="1" placeholder="จำนวนคน">
        </div>
        <div class="mb-0">
            <label class="form-label fw-medium">คนขับรถ</label>
            <select name="driver" class="form-select">
                <option value="">— เลือกคนขับ (ถ้ามี) —</option>
                <option value="self">ขับเอง</option>
                <option value="driver">พนักงานขับรถ</option>
            </select>
        </div>
    </div>
</div>

<div class="d-grid gap-2 mb-3">
    <button type="button" class="btn btn-outline-primary btn-booking" id="btn-check-vehicles">
        <i data-lucide="search" class="me-1" style="width: 1.15rem; height: 1.15rem; vertical-align: -0.25em;"></i>
        ตรวจสอบรถว่าง
    </button>
    <button type="submit" class="btn btn-primary btn-booking" name="action" value="submit">
        <i data-lucide="check-circle" class="me-1" style="width: 1.15rem; height: 1.15rem; vertical-align: -0.25em;"></i>
        ยืนยันการจอง
    </button>
</div>

<?php ActiveForm::end(); ?>

<div class="card booking-card mb-3" id="vehicle-list-card">
    <div class="card-body">
        <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
            <i data-lucide="car" class="mi-md"></i>
            รถที่ว่าง
        </h6>
        <div id="vehicle-list-empty" class="vehicle-list-empty">
            <i data-lucide="car-front" class="mi-xl mb-2 d-block mx-auto" style="opacity: 0.4;"></i>
            กด "ตรวจสอบรถว่าง" เพื่อดูรายการรถที่ว่างในวันและเวลาที่เลือก
        </div>
        <div id="vehicle-list-loading" class="vehicle-list-empty d-none" role="status" aria-live="polite">
            <i data-lucide="loader-2" class="mi-xl mb-2 d-block mx-auto" style="animation: mobile-loading-spin 0.7s linear infinite;"></i>
            กำลังตรวจสอบรถที่ว่าง...
        </div>
        <div id="vehicle-list-error" class="vehicle-list-empty d-none text-danger" role="alert">
            <i data-lucide="alert-circle" class="mi-xl mb-2 d-block mx-auto"></i>
            <span id="vehicle-list-error-msg">โหลดรายการรถไม่สำเร็จ</span>
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" id="btn-retry-vehicles">ลองอีกครั้ง</button>
            </div>
        </div>
        <div id="vehicle-list-results" class="d-none" role="list" aria-label="รายการรถที่ว่าง"></div>
    </div>
</div>

<?php
$js = <<<'JS'
(function() {
    var form = document.getElementById('mobile-booking-vehicle-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!window.mobileConfirm(this, 'คุณต้องการบันทึกข้อมูลใช่หรือไม่?')) e.preventDefault();
        });
    }
    var btn = document.getElementById('btn-check-vehicles');
    var empty = document.getElementById('vehicle-list-empty');
    var loading = document.getElementById('vehicle-list-loading');
    var errorBox = document.getElementById('vehicle-list-error');
    var results = document.getElementById('vehicle-list-results');

    function showOnly(el) {
        [empty, loading, errorBox, results].forEach(function(n) {
            if (n) n.classList.add('d-none');
        });
        if (el) el.classList.remove('d-none');
    }

    // TODO: wire up to /booking/vehicle/availability endpoint when available.
    // For now, surface an honest "in development" message instead of demo cars.
    function checkVehicles() {
        showOnly(loading);
        setTimeout(function() {
            results.innerHTML = '<div class="text-center text-body-secondary py-3">'
                + '<i data-lucide="info" class="mi-md mb-2 d-block mx-auto"></i>'
                + 'ระบบตรวจสอบรถว่างแบบเรียลไทม์อยู่ระหว่างพัฒนา<br>'
                + 'กรุณายืนยันคำขอ — เจ้าหน้าที่จะจัดสรรรถและแจ้งกลับ'
                + '</div>';
            showOnly(results);
            if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        }, 300);
    }

    if (btn) btn.addEventListener('click', checkVehicles);
    var retry = document.getElementById('btn-retry-vehicles');
    if (retry) retry.addEventListener('click', checkVehicles);
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>