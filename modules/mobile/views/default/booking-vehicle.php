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
.booking-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.booking-card .form-control,
.booking-card .form-select {
    border-radius: 12px;
    padding: 0.75rem 1rem;
}
.booking-card .form-label { font-weight: 500; }
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

<div class="booking-header mb-3">
    <h1 class="h5 fw-semibold text-dark mb-0">จองรถราชการ</h1>
    <p class="small text-body-secondary mb-0">กรอกข้อมูลเพื่อตรวจสอบรถว่างและส่งคำขอ</p>
</div>

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
            <i data-lucide="car" style="width: 1.25rem; height: 1.25rem;"></i>
            รถที่ว่าง
        </h6>
        <div id="vehicle-list-empty" class="vehicle-list-empty">
            <i data-lucide="car-front" style="width: 2rem; height: 2rem; opacity: 0.4;" class="mb-2 d-block mx-auto"></i>
            กด "ตรวจสอบรถว่าง" เพื่อดูรายการรถที่ว่างในวันและเวลาที่เลือก
        </div>
        <div id="vehicle-list" class="d-none">
            <div class="vehicle-item mb-2" data-vehicle-id="1">
                <div class="card vehicle-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 36' fill='%23adb5bd'%3E%3Cpath d='M8 22l4-8h24l4 8v6H8v-6zm4-6h20l-2 4H14l-2-4z'/%3E%3C/svg%3E" alt="" class="vehicle-thumb" width="56" height="44">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">กก 1234 กรุงเทพ</div>
                            <div class="small text-body-secondary">Toyota Camry · 5 ที่นั่ง</div>
                            <div class="small text-body-secondary">คนขับ: นายสมชาย ใจดี</div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1 mt-1">ว่าง</span>
                        </div>
                        <div class="form-check flex-shrink-0">
                            <input class="form-check-input" type="radio" name="vehicle_id" value="1" id="v1">
                            <label class="form-check-label small" for="v1">เลือก</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vehicle-item mb-2" data-vehicle-id="2">
                <div class="card vehicle-card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 36' fill='%23adb5bd'%3E%3Cpath d='M8 22l4-8h24l4 8v6H8v-6zm4-6h20l-2 4H14l-2-4z'/%3E%3C/svg%3E" alt="" class="vehicle-thumb" width="56" height="44">
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold">กข 5678 กรุงเทพ</div>
                            <div class="small text-body-secondary">Honda Accord · 5 ที่นั่ง</div>
                            <div class="small text-body-secondary">คนขับ: นายวิชัย รถเร็ว</div>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1 mt-1">ว่าง</span>
                        </div>
                        <div class="form-check flex-shrink-0">
                            <input class="form-check-input" type="radio" name="vehicle_id" value="2" id="v2">
                            <label class="form-check-label small" for="v2">เลือก</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
(function() {
    var btn = document.getElementById('btn-check-vehicles');
    var empty = document.getElementById('vehicle-list-empty');
    var list = document.getElementById('vehicle-list');
    if (btn && empty && list) {
        btn.addEventListener('click', function() {
            empty.classList.add('d-none');
            list.classList.remove('d-none');
            if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
        });
    }
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>