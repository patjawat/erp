<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\components\ThaiDateHelper;
use app\components\CategoriseHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\DevelopmentDetail;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
$emp = UserHelper::GetEmployee();
$listDocumentMe  = $emp->listDocumentMe();


?>



<style>
:not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 2px);
    padding: 4px;
    font-size: 1.0rem;
    line-height: 1.5;
    border-radius: .3rem;
}



.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px);
    line-height: 1.5;
    padding: 6px;
}

.avatar-form .avatar {
    height: 1.9rem !important;
    width: 1.9rem !important;
}

.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px);
    line-height: 1.5;
    padding: 0.1rem 0.1rem 0.5rem 0.1rem;
}

/* === Travel party section: tokens เทียบเคียงกับ DESIGN.md === */
:root {
    --tp-ink-1: #1a202c;
    --tp-ink-2: #4a5568;
    --tp-ink-3: #718096;
    --tp-ink-4: #a0aec0;
    --tp-surface: #ffffff;
    --tp-surface-2: #f7f9fc;
    --tp-surface-3: #eef2f7;
    --tp-surface-hover: #f1f5f9;
    --tp-line: rgba(15, 23, 42, 0.08);
    --tp-line-strong: rgba(15, 23, 42, 0.14);
    --tp-primary: #0d6efd;
    --tp-primary-ink: #0a58ca;
    --tp-primary-soft: rgba(13, 110, 253, 0.08);
    --tp-success: #15803d;
    --tp-success-soft: rgba(21, 128, 61, 0.10);
    --tp-warning: #b45309;
    --tp-warning-soft: rgba(180, 83, 9, 0.10);
    --tp-radius-sm: 8px;
    --tp-radius-xs: 6px;
    --tp-shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
    --tp-ease: cubic-bezier(0.16, 1, 0.3, 1);
    --tp-t-fast: 120ms;
    --tp-t-mid: 180ms;
    --tp-t-slow: 240ms;
}

.travel-party-section {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.travel-party-section__label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--tp-ink-2);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.travel-party-section__label .bi {
    color: var(--tp-ink-3);
    font-size: 0.95rem;
}

#travel-party-members-list {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 0.1rem 0;
}

#travel-party-members-list:empty::before {
    content: 'ยังไม่มีสมาชิกอื่นในคณะ — ค้นหาเพิ่มได้ด้านล่าง';
    display: block;
    padding: 0.75rem 0.9rem;
    color: var(--tp-ink-3);
    font-size: 0.85rem;
    background: var(--tp-surface-2);
    border: 1px dashed var(--tp-line-strong);
    border-radius: var(--tp-radius-sm);
}

.travel-party-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.55rem 0.65rem;
    border-bottom: 1px solid var(--tp-line) !important;
    transition: background var(--tp-t-fast) var(--tp-ease);
    /* เข้าแบบนุ่ม — transform + opacity เท่านั้น */
    animation: tp-row-in var(--tp-t-slow) var(--tp-ease) both;
}

.travel-party-row:hover {
    background: var(--tp-surface-hover);
}

.travel-party-row:last-child {
    border-bottom: 0 !important;
}

@keyframes tp-row-in {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.travel-party-row.is-leaving {
    animation: tp-row-out 160ms var(--tp-ease) both;
}

@keyframes tp-row-out {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-3px);
    }
}

.travel-party-row .btn-remove-member {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    border: 1px solid var(--tp-line);
    background: var(--tp-surface);
    color: var(--tp-ink-4);
    transition: color var(--tp-t-fast) var(--tp-ease),
                background var(--tp-t-fast) var(--tp-ease),
                border-color var(--tp-t-fast) var(--tp-ease);
    line-height: 1;
}

.travel-party-row .btn-remove-member:hover,
.travel-party-row .btn-remove-member:focus-visible {
    color: #b91c1c;
    background: rgba(185, 28, 28, 0.08);
    border-color: rgba(185, 28, 28, 0.22);
    outline: none;
}

.travel-party-add-row {
    display: flex;
    align-items: stretch;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.travel-party-add-row > .flex-grow-1 {
    min-width: 240px;
}

#btn-add-travel-member {
    border-radius: var(--tp-radius-sm);
    padding: 0.4rem 0.9rem;
    border-color: var(--tp-line-strong);
    color: var(--tp-primary-ink);
    background: var(--tp-surface);
    transition: background var(--tp-t-fast) var(--tp-ease),
                border-color var(--tp-t-fast) var(--tp-ease),
                color var(--tp-t-fast) var(--tp-ease);
}

#btn-add-travel-member:hover,
#btn-add-travel-member:focus-visible {
    background: var(--tp-primary-soft);
    border-color: rgba(13, 110, 253, 0.32);
    color: var(--tp-primary-ink);
    outline: none;
    box-shadow: 0 0 0 3px var(--tp-primary-soft);
}

.travel-party-hint {
    font-size: 0.78rem;
    color: var(--tp-ink-3);
    margin: 0;
    line-height: 1.4;
}

/* === Leader preview chip area === */
#travel-party-leaders {
    margin-top: 0.25rem;
    padding: 0.85rem 0.9rem;
    background: var(--tp-surface-2);
    border: 1px solid var(--tp-line);
    border-radius: var(--tp-radius-sm);
    transition: opacity var(--tp-t-mid) var(--tp-ease);
}

#travel-party-leaders.is-loading {
    opacity: 0.55;
}

.tp-leaders__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    margin-bottom: 0.55rem;
}

.tp-leaders__title {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--tp-ink-2);
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin: 0;
}

.tp-leaders__title .bi {
    color: var(--tp-primary);
    font-size: 1rem;
}

.tp-leaders__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 0.4rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--tp-ink-2);
    background: var(--tp-surface-3);
    font-variant-numeric: tabular-nums;
}

.tp-leaders__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.tp-leader-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.6rem 0.25rem 0.5rem;
    background: var(--tp-surface);
    border: 1px solid var(--tp-line-strong);
    border-radius: 999px;
    font-size: 0.78rem;
    color: var(--tp-ink-1);
    line-height: 1.4;
    max-width: 16rem;
    /* stagger entry */
    animation: tp-chip-in var(--tp-t-mid) var(--tp-ease) both;
    animation-delay: calc(var(--tp-i, 0) * 40ms);
}

.tp-leader-chip__dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: var(--tp-success);
    flex-shrink: 0;
}

.tp-leader-chip.is-no-line {
    background: var(--tp-warning-soft);
    border-color: rgba(180, 83, 9, 0.22);
    color: var(--tp-warning);
}

.tp-leader-chip.is-no-line .tp-leader-chip__dot {
    background: var(--tp-warning);
}

.tp-leader-chip__name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tp-leader-chip__badge {
    font-size: 0.7rem;
    color: var(--tp-ink-3);
    background: var(--tp-surface-3);
    padding: 0 0.4rem;
    border-radius: 999px;
    font-variant-numeric: tabular-nums;
}

@keyframes tp-chip-in {
    from {
        opacity: 0;
        transform: translateY(-2px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.tp-leaders__empty {
    font-size: 0.8rem;
    color: var(--tp-ink-3);
    margin: 0;
    line-height: 1.45;
}

.tp-leaders__foot {
    font-size: 0.72rem;
    color: var(--tp-ink-3);
    margin: 0.55rem 0 0 0;
    line-height: 1.4;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .travel-party-row,
    .travel-party-row.is-leaving,
    .tp-leader-chip {
        animation: none !important;
    }
    #travel-party-leaders,
    .travel-party-row,
    .travel-party-row .btn-remove-member,
    #btn-add-travel-member {
        transition-duration: 80ms !important;
    }
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-development',
    'options' => [
        'data-confirm-title' => 'ยืนยันส่งคำขอ?',
        'data-confirm-text' => 'ระบบจะบันทึกคำขอ สร้างลำดับการอนุมัติ และแจ้งผู้เกี่ยวข้อง',
        'data-confirm-button' => '<i class="bi bi-check2-circle me-1"></i> ยืนยันบันทึก',
        'data-loading-title' => 'กำลังบันทึกคำขอ',
        'data-loading-text' => 'กรุณารอสักครู่ ระบบกำลังสร้างลำดับการอนุมัติ',
    ],
]); ?>
<?php if (!$model->isNewRecord): ?>
<input type="hidden" name="development_id" value="<?= (int) $model->id ?>">
<?php endif; ?>

<div class="container-fluid px-3 px-sm-4 pb-4">
    <!-- ข้อมูลอ้างอิงเอกสาร -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-file-earmark-text me-2"></i>ข้อมูลเอกสาร</strong>
        </div>
        <div class="card-body">
            <?php
            $travelCalculation = is_array($model->data_json ?? null)
                ? ($model->data_json['travel_calculation'] ?? [])
                : [];
            $hasTravelCalculation = !empty($travelCalculation);
            $roomRate = (string)($travelCalculation['room_rate'] ?? '0');
            $vehicleRate = (string)($travelCalculation['vehicle_rate'] ?? '0');
            $additionalItems = isset($travelCalculation['additional_items']) && is_array($travelCalculation['additional_items'])
                ? array_values($travelCalculation['additional_items'])
                : [];
            if (!$additionalItems && (float)($travelCalculation['other_cost'] ?? 0) > 0) {
                $additionalItems[] = ['type' => 'other', 'description' => '', 'amount' => $travelCalculation['other_cost']];
            }
            $additionalTypeOptions = [
                'public_transport' => 'รถประจำทาง / ขนส่งสาธารณะ',
                'parking' => 'ค่าจอดรถ / ค่าผ่านทาง',
                'other' => 'ค่าใช้จ่ายอื่น',
            ];
            ?>
            <?php ob_start(); ?>
            <div class="border rounded-3 mb-4" id="travel-cost-calculator" data-has-saved="<?= $hasTravelCalculation ? '1' : '0' ?>">
                <div class="p-3 bg-body-tertiary rounded-top-3">
                    <div>
                        <div class="fw-semibold fs-5"><i class="bi bi-calculator me-2 text-primary"></i>คำนวณค่าใช้จ่ายเดินทางไปราชการ</div>
                        <div class="small text-body-secondary mt-1">ใช้วันเวลา ระยะทาง และรายชื่อคณะเดินทางจากข้อมูลในคำขอนี้</div>
                    </div>
                </div>

                <div id="travel-cost-calculator-body">
                    <div class="p-3 border-top">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="travel-calc-participants" class="form-label">จำนวนผู้เดินทาง</label>
                                <div class="input-group">
                                    <input type="number" min="1" step="1" class="form-control travel-calc-input"
                                           id="travel-calc-participants" name="Development[data_json][travel_calculation][participants]"
                                           value="<?= Html::encode($travelCalculation['participants'] ?? 1) ?>">
                                    <span class="input-group-text">คน</span>
                                </div>
                                <div class="form-text">ระบบนับผู้ขอรวมกับสมาชิกคณะเดินทาง</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="travel-calc-meals" class="form-label">มื้ออาหารที่เจ้าภาพจัดให้ต่อคน</label>
                                <div class="input-group">
                                    <input type="number" min="0" step="1" class="form-control travel-calc-input"
                                           id="travel-calc-meals" name="Development[data_json][travel_calculation][deducted_meals]"
                                           value="<?= Html::encode($travelCalculation['deducted_meals'] ?? 0) ?>">
                                    <span class="input-group-text">มื้อ/คน</span>
                                </div>
                                <div class="form-text">หักมื้อละ 80 บาทต่อคน</div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="travel-calc-room-rate" class="form-label">อัตราค่าที่พัก</label>
                                <select class="form-select travel-calc-input" id="travel-calc-room-rate"
                                        name="Development[data_json][travel_calculation][room_rate]">
                                    <?php foreach (['0' => 'ไม่เบิกค่าที่พัก', '1600' => 'ห้องพักคนเดียว 1,600 บาท/ห้อง', '1000' => 'ห้องพักคู่ 1,000 บาท/ห้อง'] as $value => $label): ?>
                                        <option value="<?= $value ?>"<?= $roomRate === $value ? ' selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <option value="custom"<?= $roomRate === 'custom' ? ' selected' : '' ?>>ระบุอัตราเอง</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3<?= $roomRate === 'custom' ? '' : ' d-none' ?>" id="travel-calc-custom-room-wrap">
                                <label for="travel-calc-custom-room" class="form-label">อัตราที่พักที่เบิกจริง</label>
                                <div class="input-group">
                                    <input type="number" min="0" step="0.01" class="form-control travel-calc-input"
                                           id="travel-calc-custom-room" name="Development[data_json][travel_calculation][custom_room_rate]"
                                           value="<?= Html::encode($travelCalculation['custom_room_rate'] ?? 0) ?>">
                                    <span class="input-group-text">บาท/คน/คืน</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="travel-calc-nights" class="form-label">จำนวนคืน</label>
                                <div class="input-group">
                                    <input type="number" min="0" step="1" class="form-control travel-calc-input"
                                           id="travel-calc-nights" name="Development[data_json][travel_calculation][nights]"
                                           value="<?= Html::encode($travelCalculation['nights'] ?? 0) ?>">
                                    <span class="input-group-text">คืน</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="travel-calc-vehicle-rate" class="form-label">อัตราพาหนะส่วนตัว</label>
                                <select class="form-select travel-calc-input" id="travel-calc-vehicle-rate"
                                        name="Development[data_json][travel_calculation][vehicle_rate]">
                                    <option value="0"<?= $vehicleRate === '0' ? ' selected' : '' ?>>ไม่เบิกพาหนะส่วนตัว</option>
                                    <option value="4"<?= $vehicleRate === '4' ? ' selected' : '' ?>>รถยนต์ 4 บาท/กม.</option>
                                    <option value="2"<?= $vehicleRate === '2' ? ' selected' : '' ?>>รถจักรยานยนต์ 2 บาท/กม.</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="travel-calc-distance" class="form-label">ระยะทางรวม</label>
                                <div class="input-group">
                                    <input type="number" min="0" step="0.01" class="form-control travel-calc-input"
                                           id="travel-calc-distance" name="Development[data_json][travel_calculation][distance]"
                                           value="<?= Html::encode($travelCalculation['distance'] ?? ($model->data_json['distance'] ?? 0)) ?>">
                                    <span class="input-group-text">กม.</span>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 mt-3">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-bottom pb-2 mb-3">
                                <div class="fw-semibold text-primary"><i class="bi bi-ticket-perforated me-2"></i>ค่าพาหนะอื่น / ค่าใช้จ่ายเพิ่มเติม</div>
                                <button type="button" class="btn btn-sm btn-success" id="btn-add-travel-expense">
                                    <i class="bi bi-plus-lg me-1"></i>เพิ่มรายการ
                                </button>
                            </div>
                            <div id="travel-additional-items" data-next-index="<?= count($additionalItems) ?>">
                                <?php foreach ($additionalItems as $index => $item): ?>
                                    <div class="row g-2 align-items-end mb-2 travel-additional-row">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small">ประเภทค่าใช้จ่าย</label>
                                            <select class="form-select travel-additional-type" name="Development[data_json][travel_calculation][additional_items][<?= $index ?>][type]">
                                                <?php foreach ($additionalTypeOptions as $value => $label): ?>
                                                    <option value="<?= $value ?>"<?= ($item['type'] ?? 'other') === $value ? ' selected' : '' ?>><?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small">รายละเอียด</label>
                                            <input type="text" maxlength="255" class="form-control" name="Development[data_json][travel_calculation][additional_items][<?= $index ?>][description]" value="<?= Html::encode($item['description'] ?? '') ?>" placeholder="รายละเอียด (ถ้ามี)">
                                        </div>
                                        <div class="col-10 col-md-3">
                                            <label class="form-label small">จำนวนเงิน</label>
                                            <div class="input-group">
                                                <input type="number" min="0" step="0.01" class="form-control travel-calc-input travel-additional-amount" name="Development[data_json][travel_calculation][additional_items][<?= $index ?>][amount]" value="<?= Html::encode($item['amount'] ?? 0) ?>">
                                                <span class="input-group-text">บาท</span>
                                            </div>
                                        </div>
                                        <div class="col-2 col-md-1">
                                            <button type="button" class="btn btn-outline-danger w-100 btn-remove-travel-expense" aria-label="ลบรายการค่าใช้จ่าย"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="text-center text-body-secondary small py-2<?= $additionalItems ? ' d-none' : '' ?>" id="travel-additional-empty">ยังไม่มีรายการเพิ่มเติม</div>
                            </div>
                        </div>

                        <input type="hidden" id="travel-calc-allowance-rate" name="Development[data_json][travel_calculation][allowance_rate]" value="240">
                        <input type="hidden" id="travel-calc-meal-rate" name="Development[data_json][travel_calculation][meal_rate]" value="80">
                        <input type="hidden" id="travel-calc-duration-hours" name="Development[data_json][travel_calculation][duration_hours]"
                               value="<?= Html::encode($travelCalculation['duration_hours'] ?? 0) ?>">
                        <input type="hidden" id="travel-calc-allowance-amount" name="Development[data_json][travel_calculation][allowance_amount]"
                               value="<?= Html::encode($travelCalculation['allowance_amount'] ?? 0) ?>">
                        <input type="hidden" id="travel-calc-accommodation-amount" name="Development[data_json][travel_calculation][accommodation_amount]"
                               value="<?= Html::encode($travelCalculation['accommodation_amount'] ?? 0) ?>">
                        <input type="hidden" id="travel-calc-room-count" name="Development[data_json][travel_calculation][room_count]"
                               value="<?= Html::encode($travelCalculation['room_count'] ?? 0) ?>">
                        <input type="hidden" id="travel-calc-vehicle-amount" name="Development[data_json][travel_calculation][vehicle_amount]"
                               value="<?= Html::encode($travelCalculation['vehicle_amount'] ?? 0) ?>">
                        <input type="hidden" id="travel-calc-total" name="Development[data_json][travel_calculation][calculated_total]"
                               value="<?= Html::encode($travelCalculation['calculated_total'] ?? 0) ?>">

                        <div class="bg-success-subtle text-success-emphasis border border-success-subtle rounded-3 p-3 mt-3" aria-live="polite">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold fs-5 mb-2">สรุปงบประมาณที่เบิกได้</div>
                                    <div id="travel-calc-summary" class="small">ระบุวันเวลาเดินทางเพื่อคำนวณเบี้ยเลี้ยง</div>
                                </div>
                                <div class="text-lg-end">
                                    <div class="small">รวมเงินทั้งสิ้น</div>
                                    <div class="fs-3 fw-bold"><span id="travel-calc-grand-total">0.00</span> บาท</div>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-top border-success-subtle mt-3 pt-3">
                                <div class="small" id="travel-calc-sync-status">ยอดที่แสดงยังไม่ถูกนำไปใช้</div>
                                <button type="button" class="btn btn-primary" id="btn-apply-travel-calculation">
                                    <i class="bi bi-calculator me-1"></i> นำยอดคำนวณไปใช้
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $travelCalculatorMarkup = ob_get_clean(); ?>

            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <?= $form->field($model, 'thai_year')->textInput(['class' => 'form-control']) ?>
                    <?= $form->field($model, 'data_json[doc_number]')->textInput(['class' => 'form-control'])->label('เลขที่หนังสือ') ?>
                </div>
                <div class="col-12 col-md-9">
                    <?php

                        $listDocumentData = ArrayHelper::map($listDocumentMe, 'id', function($model) {
                            return [
                                'text' => $model['topic'] ?? null,
                                'topic' => $model['topic'] ?? null,
                                'doc_number' => $model['doc_number'] ?? null,
                                'document_org_name' => $model['document_org_name'] ?? 'ไม่ระบุหน่วยงาน',
                            ];
                        });
                        $listDocumentOptions = ArrayHelper::map($listDocumentMe, 'id', function($document) {
                            $docNumber = trim((string)($document['doc_number'] ?? '')) ?: 'ไม่ระบุเลขที่';
                            $topic = trim((string)($document['topic'] ?? '')) ?: 'ไม่ระบุเรื่อง';
                            $organization = trim((string)($document['document_org_name'] ?? '')) ?: 'ไม่ระบุหน่วยงาน';

                            return $docNumber . ' · ' . $topic . ' · ' . $organization;
                        });
                        $listDocumentJson = Json::htmlEncode($listDocumentData);

                            echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                                'data' => $listDocumentOptions,
                                'options' => ['placeholder' => 'ค้นหาจากเลขที่ เรื่อง หรือหน่วยงาน ...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'templateResult' => new JsExpression("function(data) {
                                        if (!data.id) return data.text;
                                        var documents = {$listDocumentJson};
                                        var documentData = documents[String(data.id)] || {};
                                        var resultNode = jQuery('<div class=\"py-1\"></div>');
                                        jQuery('<div class=\"fw-semibold text-body\"></div>')
                                            .text((documentData.doc_number || 'ไม่ระบุเลขที่') + ' · ' + (documentData.topic || 'ไม่ระบุเรื่อง'))
                                            .appendTo(resultNode);
                                        jQuery('<div class=\"small text-body-secondary\"></div>')
                                            .text(documentData.document_org_name || 'ไม่ระบุหน่วยงาน')
                                            .appendTo(resultNode);
                                        return resultNode;
                                    }"),
                                    'templateSelection' => new JsExpression("function(data) {
                                        if (!data.id) return data.text;
                                        var documents = {$listDocumentJson};
                                        var documentData = documents[String(data.id)] || {};
                                        return (documentData.doc_number || 'ไม่ระบุเลขที่') + ' · ' + (documentData.topic || 'ไม่ระบุเรื่อง');
                                    }"),
                                    // 'dropdownParent' => '#main-modal',
                                ],
                                'pluginEvents' => [
                                    'select2:select' =>  new JsExpression("function(e) {
                                        var documents = {$listDocumentJson};
                                        var documentData = documents[String(e.params.data.id)] || {};
                                        $('#development-topic').val(documentData.topic || '').trigger('change');
                                        $('[name=\"Development[data_json][doc_number]\"]').val(documentData.doc_number || '').trigger('change');
                                    }"),
                                    'select2:clear' => new JsExpression("function() {
                                        $('[name=\"Development[data_json][doc_number]\"]').val('').trigger('change');
                                    }"),
                                ]
                            ])->label('หนังสืออ้างอิง');
                            ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'data_json[custom_text]')->textarea([
                        'class' => 'form-control',
                        'rows' => 2,
                        'placeholder' => 'ข้อความที่ต้องการให้แสดงบน PDF ตามตำแหน่งที่ตั้งค่าในเทมเพลต',
                    ])->label('ข้อความกำหนดเอง') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- รายละเอียดการพัฒนา -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-info-circle me-2"></i>รายละเอียดการพัฒนา</strong>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-0">
                <div class="col-12">
                    <?= $form->field($model, 'topic')->textInput(['maxlength' => true, 'placeholder' => 'ระบุหัวข้อการอบรม/ประชุม/ดูงาน']) ?>
                </div>
            </div>
            <div class="row g-3 mb-0">
                <div class="col-12">
                    <?= $form->field($model, 'data_json[travel_party]')->textInput(['maxlength' => true, 'placeholder' => 'เช่น คณะกรรมการโครงการ, หน่วยงานที่เดินทางร่วมกัน'])->label('คำอธิบายคณะเดินทาง') ?>
                </div>
            </div>

            <!-- สมาชิกคณะเดินทาง (เพิ่มจากฟอร์มได้) -->
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <div class="travel-party-section"
                         data-leaders-url="<?= Html::encode(Url::to(['/me/development/leaders-by-members'])) ?>">
                        <label class="travel-party-section__label">
                            <i class="bi bi-people"></i> รายชื่อสมาชิกคณะเดินทาง
                        </label>

                        <div id="travel-party-members-list" data-emp-search-url="<?= Html::encode(Url::to(['/depdrop/employee-by-id'])) ?>">
                            <?php
                            $existingMembers = $model->isNewRecord ? [] : $model->listMember();
                            foreach ($existingMembers as $detail):
                                $emp = $detail->emp;
                                if (!$emp) {
                                    $label = trim((string)($detail->data_json['label'] ?? '')) ?: $detail->emp_id;
                            ?>
                            <div class="travel-party-row">
                                <input type="hidden" name="member_emp_ids[]" value="<?= Html::encode($detail->emp_id) ?>">
                                <span class="text-body flex-grow-1"><?= Html::encode($label) ?></span>
                                <button type="button" class="btn-remove-member" title="ลบ" aria-label="ลบสมาชิก"><i class="bi bi-trash"></i></button>
                            </div>
                            <?php
                                    continue;
                                }
                            ?>
                            <div class="travel-party-row">
                                <input type="hidden" name="member_emp_ids[]" value="<?= Html::encode($detail->emp_id) ?>">
                                <div class="flex-grow-1"><?= $emp->getAvatar(false) ?></div>
                                <button type="button" class="btn-remove-member" title="ลบ" aria-label="ลบสมาชิก"><i class="bi bi-trash"></i></button>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="travel-party-add-row">
                            <div class="flex-grow-1">
                                <select id="member-emp-select-new" class="form-control" style="width: 100%;">
                                    <option value="">เลือกบุคลากรเพื่อเพิ่มในคณะเดินทาง ...</option>
                                </select>
                            </div>
                            <button type="button" id="btn-add-travel-member" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-1"></i> เพิ่มสมาชิก
                            </button>
                        </div>
                        <p class="travel-party-hint">เลือกบุคลากรจากรายการแล้วกด «เพิ่มสมาชิก» หรือกด Enter</p>

                        <!-- Preview หัวหน้าที่จะได้รับแจ้งเตือนผ่าน Telegram เมื่อบันทึก -->
                        <div id="travel-party-leaders" aria-live="polite" hidden>
                            <div class="tp-leaders__head">
                                <p class="tp-leaders__title">
                                    <i class="bi bi-send"></i> หัวหน้าที่จะได้รับแจ้งเตือนผ่าน Telegram
                                </p>
                                <span class="tp-leaders__count" id="tp-leaders-count">0</span>
                            </div>
                            <div class="tp-leaders__list" id="tp-leaders-list"></div>
                            <p class="tp-leaders__empty" id="tp-leaders-empty" hidden>
                                ระบบไม่พบหัวหน้างานของสมาชิกในระบบ — ข้ามการแจ้งเตือน
                            </p>
                            <p class="tp-leaders__foot" id="tp-leaders-foot" hidden>
                                ป้าย <span style="color: var(--tp-warning); font-weight: 600;">สีส้ม</span> = ยังไม่ผูก Telegram จะถูกข้าม
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-0">
                <!-- คอลัมน์ซ้าย -->
                <div class="col-12 col-md-6">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'date_start')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.']) ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'date_end')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.']) ?>
                        </div>
                    </div>

                    <?php
                            echo $form->field($model, 'development_type_id')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentType(),
                                'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ประเภทการพัฒนา');
                            ?>

                    <?php
                            echo $form->field($model, 'data_json[development_level_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentLevel(true),
                                'options' => ['placeholder' => 'เลือกระดับการพัฒนา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ระดับการพัฒนา');
                            ?>

                    <?php
                            echo $form->field($model, 'data_json[time_slot]')->widget(Select2::classname(), [
                                'data' => [
                                    'เต็มวัน' => 'เต็มวัน',
                                    'ครั้งวันเช้า' => 'ครั้งวันเช้า',
                                    'ครั้งวันบ่าย' => 'ครั้งวันบ่าย',
                                ],
                                'options' => ['placeholder' => 'เลือกช่วงเวลา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ช่วงเวลา');
                            ?>
                </div>

                <!-- คอลัมน์ขวา -->
                <div class="col-12 col-md-6">
                    <?php
                            echo $form->field($model, 'data_json[development_go_type_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentGoType(true),
                                'options' => ['placeholder' => 'เลือกลักษณะ'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ลักษณะการเข้าร่วม');
                            ?>

                    <?php
                            echo $form->field($model, 'data_json[claim_type_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentClaimType(true),
                                'options' => ['placeholder' => 'เลือกการเบิกเงิน'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('การเบิกเงิน');
                            ?>


                    <?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employee = Employees::find()->where(['id' => $model->leader_id])->one();
            $initEmployee = empty($model->leader_id) ? '' : Employees::findOne($model->leader_id)->getAvatar(false);//กำหนดค่าเริ่มต้น
            
            echo $form->field($model,'leader_id')->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                // 'size' => Select2::,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    // 'dropdownParent' => '#main-modal',
                    'allowClear'=>true,
                    'minimumInputLength'=>1,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                    'ajax'=>[
                        'url'=>$url,
                        'dataType'=>'json',//รูปแบบการอ่านคือ json
                        'data'=>new JsExpression('function(params) { return {q:params.term};}')
                    ],
                    'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                    'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                    'templateSelection'=>new JsExpression('function(emp) {return emp.text;}'),
                ],

                    ])->label('หัวหน้างาน');
                    ?>


                    <?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employee = Employees::find()->where(['id' => $model->leader_group_id])->one();
            $initEmployee = empty($model->leader_group_id) ? '' : Employees::findOne($model->leader_group_id)->getAvatar(false);//กำหนดค่าเริ่มต้น
            
            echo $form->field($model,'leader_group_id')->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                // 'size' => Select2::,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    // 'dropdownParent' => '#main-modal',
                    'allowClear'=>true,
                    'minimumInputLength'=>1,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                    'ajax'=>[
                        'url'=>$url,
                        'dataType'=>'json',//รูปแบบการอ่านคือ json
                        'data'=>new JsExpression('function(params) { return {q:params.term};}')
                    ],
                    'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                    'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                    'templateSelection'=>new JsExpression('function(emp) {return emp.text;}'),
                ],

                    ])->label('หัวหน้ากลุ่มงาน');
                    ?>

                <div class="avatar-form">
            <?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employeeAssignedTo = Employees::find()->where(['id' => $model->assigned_to])->one();
            $initEmployeeAssignedTo = empty($model->assigned_to) ? '' : Employees::findOne($model->assigned_to)->getAvatar(false);//กำหนดค่าเริ่มต้น
            
            echo $form->field($model,'assigned_to')->widget(Select2::classname(), [
                'initValueText' => $initEmployeeAssignedTo,
                // 'size' => Select2::,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    // 'dropdownParent' => '#main-modal',
                    'width' => '350px',
                    'allowClear'=>true,
                    'minimumInputLength'=>1,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                    'ajax'=>[
                        'url'=>$url,
                        'dataType'=>'json',//รูปแบบการอ่านคือ json
                        'data'=>new JsExpression('function(params) { return {q:params.term};}')
                    ],
                    'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                    'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                    'templateSelection'=>new JsExpression('function(emp) {return emp.text;}'),
                ],

                    ])->label('ผู้ปฏิบัติหน้าที่แทน');
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- สถานที่และหน่วยงาน -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-geo-alt me-2"></i>สถานที่และหน่วยงาน</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <?php
                            echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListLocationOrg(true),
                                'options' => ['placeholder' => 'เลือกสถานที่'],
                        'pluginOptions' => [
                            'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                            'allowClear' => true,
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) { 
                                            }',
                            'select2:unselecting' => 'function() {

                                            }',
                        ],
                
                    ])->label('สถานที่จัดงาน');?>

                    <?php
                            echo $form->field($model, 'data_json[province_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListProvinceName(true),
                                'options' => ['placeholder' => 'เลือกจังหวัด'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                ],
                            ])->label('จังหวัด');
                            ?>
                </div>

                <div class="col-12 col-md-6">
                    <?php
                            echo $form->field($model, 'data_json[location_org]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListLocationOrg(true),
                                'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                                'pluginOptions' => [
                                      'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('หน่วยงานที่จัด');
                            ?>

                    <?= $form->field($model, 'data_json[location_org_type]')->radioList([
                                'ในจังหวัด' => 'ในจังหวัด',
                                'ต่างจังหวัด' => 'ต่างจังหวัด',
                                'ต่างประเทศ' => 'ต่างประเทศ',
                            ], [
                                'item' => function($index, $label, $name, $checked, $value) {
                                    $checked = $checked ? 'checked' : '';
                                    return "<div class='form-check form-check-inline'>
                                                <input class='form-check-input' type='radio' name='{$name}' id='{$index}' value='{$value}' {$checked}>
                                                <label class='form-check-label' for='{$index}'>{$label}</label>
                                            </div>";
                                }
                            ])->label('ประเภทสถานที่'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ข้อมูลการเดินทาง -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-car-front me-2"></i>ข้อมูลการเดินทาง</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <?= $form->field($model, 'vehicle_date_start')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.'])->label('วันไป') ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <?= $form->field($model, 'data_json[vehicle_time_start]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลา') ?>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <?= $form->field($model, 'vehicle_date_end')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.'])->label('วันกลับ') ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <?= $form->field($model, 'data_json[vehicle_time_end]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลา') ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?php
                                echo $form->field($model, 'vehicle_type_id')->widget(Select2::classname(), [
                                    'data' => $model->ListVehicleType(),
                                    'options' => ['placeholder' => 'เลือกพาหนะเดินทาง'],
                                    'pluginOptions' => [
                                        // 'dropdownParent' => '#main-modal',
                                        'allowClear' => true,
                                    ],
                                ])->label('พาหนะเดินทาง');
                                ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[license_plate]')->textInput(['placeholder' => 'ระบุทะเบียนพาหนะเดินทาง'])->label('ทะเบียนพาหนะเดินทาง') ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'data_json[distance]')->textInput(['placeholder' => 'ระบุระยะทาง'])->label('ระยะทาง/กิโลเมตร') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ประมาณค่าใช้จ่ายในการเข้ารับการอบรม/ประชุม/สัมมนา ครั้งนี้ -->
    <div class="card mb-3 border-0 shadow-sm" id="estimated-cost-card">
        <div class="card-header p-2">
            <strong><i class="fa-solid fa-money-bill-1 me-2"></i> ประมาณค่าใช้จ่ายในการเข้ารับการอบรม/ประชุม/สัมมนา ครั้งนี้</strong>
        </div>
        <div class="card-body">
            <?= $travelCalculatorMarkup ?>
            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_registration]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่าลงทะเบียน (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_accommodation]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่าที่พัก (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_vehicle_fuel]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่ายานพาหนะ/น้ำมันเชื้อเพลิง (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_allowance]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่าเบี้ยเลี้ยง (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_other]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('อื่น ๆ (บาท)') ?>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-end align-items-center border-top pt-3 mt-1">
                        <span class="fw-semibold me-2">รวมทั้งหมด:</span>
                        <span id="estimated-cost-total" class="fs-5 text-primary fw-bold">0.00</span>
                        <span class="ms-1">บาท</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-4">
        <div class="col-12 text-center">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle me-2"></i> บันทึกข้อมูล', ['class' => 'btn btn-primary rounded-pill px-4 py-2 shadow me-2', 'id' => 'summit']) ?>

            <?= Html::a(
                '<i class="bi bi-arrow-left-circle me-2"></i> ย้อนกลับ',
                'javascript:history.back()',
                ['class' => 'btn btn-secondary rounded-pill px-4 py-2 shadow']
            ) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>


<?php

$additionalTypeOptionsJson = Json::htmlEncode($additionalTypeOptions);
$js = <<<JS

\$memberSelectUrl = \$('#travel-party-members-list').data('emp-search-url') || '';
if (\$memberSelectUrl && \$('#member-emp-select-new').length) {
    \$('#member-emp-select-new').select2({
        theme: 'krajee-bs5',
        allowClear: true,
        placeholder: 'เลือกบุคลากรเพื่อเพิ่มในคณะเดินทาง ...',
        minimumInputLength: 1,
        ajax: {
            url: \$memberSelectUrl,
            dataType: 'json',
            data: function(params) { return { q: params.term }; },
            processResults: function(data) {
                return { results: data.results || [], pagination: data.pagination || { more: false } };
            }
        },
        escapeMarkup: function(m) { return m; },
        templateResult: function(emp) { return emp && emp.text ? emp.text : 'กำลังค้นหา...'; },
        templateSelection: function(emp) { return emp.text || ''; }
    });
    function appendTravelMemberRow(id, avatarHtml) {
        var exists = \$('#travel-party-members-list input[name="member_emp_ids[]"]').filter(function() { return \$(this).val() === String(id); }).length;
        if (exists) return;
        var row = \$('<div class="travel-party-row"></div>');
        row.append(\$('<input type="hidden" name="member_emp_ids[]">').val(id));
        row.append(\$('<div class="flex-grow-1"></div>').html(avatarHtml || ('<span class="text-body">' + id + '</span>')));
        row.append(\$('<button type="button" class="btn-remove-member" title="ลบ" aria-label="ลบสมาชิก"><i class="bi bi-trash"></i></button>'));
        \$('#travel-party-members-list').append(row);
        refreshLeadersPreview();
    }
    \$('#member-emp-select-new').on('select2:select', function(e) {
        var data = e.params.data;
        var id = data.id;
        var avatarHtml = (data.text && typeof data.text === 'string') ? data.text : null;
        appendTravelMemberRow(id, avatarHtml);
        \$('#member-emp-select-new').val(null).trigger('change');
    });
    \$('#btn-add-travel-member').on('click', function() {
        var sel = \$('#member-emp-select-new').select2('data');
        if (sel && sel[0] && sel[0].id) {
            var data = sel[0];
            var id = data.id;
            var avatarHtml = (data.text && typeof data.text === 'string') ? data.text : null;
            appendTravelMemberRow(id, avatarHtml);
            \$('#member-emp-select-new').val(null).trigger('change');
        }
    });
}
\$('#travel-party-members-list').on('click', '.btn-remove-member', function() {
    var row = \$(this).closest('.travel-party-row');
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced) {
        row.remove();
        refreshLeadersPreview();
        return;
    }
    row.addClass('is-leaving');
    setTimeout(function() {
        row.remove();
        refreshLeadersPreview();
    }, 160);
});

/* === Preview หัวหน้าที่จะได้รับแจ้งเตือน LINE === */
var \$leadersBox = \$('#travel-party-leaders');
var \$leadersList = \$('#tp-leaders-list');
var \$leadersCount = \$('#tp-leaders-count');
var \$leadersEmpty = \$('#tp-leaders-empty');
var \$leadersFoot = \$('#tp-leaders-foot');
var leadersUrl = \$('.travel-party-section').data('leaders-url') || '';
var leadersReqToken = 0;
var leadersDebounceT = null;

function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

function collectMemberIds() {
    var ids = [];
    \$('#travel-party-members-list input[name="member_emp_ids[]"]').each(function() {
        var v = \$(this).val();
        if (v) ids.push(v);
    });
    return ids;
}

function renderLeaders(leaders) {
    \$leadersList.empty();
    if (!leaders || !leaders.length) {
        \$leadersBox.prop('hidden', false);
        \$leadersCount.text('0');
        \$leadersEmpty.prop('hidden', false);
        \$leadersFoot.prop('hidden', true);
        return;
    }
    \$leadersBox.prop('hidden', false);
    \$leadersEmpty.prop('hidden', true);
    \$leadersCount.text(String(leaders.length));
    var anyNoLine = false;
    leaders.forEach(function(l, i) {
        var noTg = !l.has_telegram;
        if (noTg) anyNoLine = true;
        var memberCount = (l.members && l.members.length) || 0;
        var memberLabel = memberCount > 1 ? ('×' + memberCount) : '';
        var titleAttr = memberCount ? 'รับแจ้งเตือนจาก: ' + l.members.join(', ') : '';
        var chip = \$(
            '<span class="tp-leader-chip' + (noTg ? ' is-no-line' : '') + '" style="--tp-i:' + i + '" title="' + escapeHtml(titleAttr) + '">'
            + '<span class="tp-leader-chip__dot"></span>'
            + '<span class="tp-leader-chip__name">' + escapeHtml(l.fullname) + '</span>'
            + (memberLabel ? '<span class="tp-leader-chip__badge">' + escapeHtml(memberLabel) + '</span>' : '')
            + '</span>'
        );
        \$leadersList.append(chip);
    });
    \$leadersFoot.prop('hidden', !anyNoLine);
}

function refreshLeadersPreview() {
    if (!leadersUrl) return;
    if (leadersDebounceT) clearTimeout(leadersDebounceT);
    leadersDebounceT = setTimeout(function() {
        var ids = collectMemberIds();
        if (!ids.length) {
            \$leadersBox.prop('hidden', true);
            \$leadersList.empty();
            return;
        }
        var myReq = ++leadersReqToken;
        \$leadersBox.addClass('is-loading');
        \$.ajax({
            url: leadersUrl,
            method: 'GET',
            data: { ids: ids },
            traditional: true,
            dataType: 'json'
        }).done(function(res) {
            if (myReq !== leadersReqToken) return; // stale
            renderLeaders((res && res.leaders) || []);
        }).fail(function() {
            if (myReq !== leadersReqToken) return;
            \$leadersBox.prop('hidden', true);
        }).always(function() {
            if (myReq === leadersReqToken) \$leadersBox.removeClass('is-loading');
        });
    }, 250);
}

// โหลดครั้งแรก (สำหรับ update mode ที่มีสมาชิกอยู่แล้ว)
refreshLeadersPreview();

// คำนวณรวมประมาณค่าใช้จ่าย
var \$travelCalculator = \$('#travel-cost-calculator');
var travelAdditionalTypes = {$additionalTypeOptionsJson};
var travelCalculationDirty = false;

function travelCalcNumber(selector) {
    var value = parseFloat(\$(selector).val());
    return isNaN(value) || value < 0 ? 0 : value;
}

function travelCalcMoney(value) {
    return Number(value || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function travelAdditionalTotal() {
    var total = 0;
    \$('#travel-additional-items .travel-additional-amount').each(function() {
        var amount = parseFloat(\$(this).val());
        if (!isNaN(amount) && amount > 0) total += amount;
    });
    return total;
}

function refreshTravelAdditionalEmpty() {
    \$('#travel-additional-empty').toggleClass('d-none', \$('.travel-additional-row').length > 0);
}

function markTravelCalculationDirty() {
    travelCalculationDirty = true;
    \$('#travel-calc-sync-status')
        .removeClass('text-success-emphasis')
        .addClass('text-warning-emphasis fw-semibold')
        .text('ข้อมูลเปลี่ยนแล้ว กรุณานำยอดคำนวณไปใช้ก่อนบันทึก');
    \$('#btn-apply-travel-calculation').prop('disabled', false);
}

function addTravelAdditionalRow() {
    var \$list = \$('#travel-additional-items');
    var index = parseInt(\$list.attr('data-next-index'), 10) || 0;
    \$list.attr('data-next-index', index + 1);
    var options = '';
    Object.keys(travelAdditionalTypes).forEach(function(value) {
        options += '<option value="' + value + '">' + travelAdditionalTypes[value] + '</option>';
    });
    var base = 'Development[data_json][travel_calculation][additional_items][' + index + ']';
    var html = '<div class="row g-2 align-items-end mb-2 travel-additional-row">'
        + '<div class="col-12 col-md-4"><label class="form-label small">ประเภทค่าใช้จ่าย</label>'
        + '<select class="form-select travel-additional-type" name="' + base + '[type]">' + options + '</select></div>'
        + '<div class="col-12 col-md-4"><label class="form-label small">รายละเอียด</label>'
        + '<input type="text" maxlength="255" class="form-control" name="' + base + '[description]" placeholder="รายละเอียด (ถ้ามี)"></div>'
        + '<div class="col-10 col-md-3"><label class="form-label small">จำนวนเงิน</label><div class="input-group">'
        + '<input type="number" min="0" step="0.01" class="form-control travel-calc-input travel-additional-amount" name="' + base + '[amount]" value="0">'
        + '<span class="input-group-text">บาท</span></div></div>'
        + '<div class="col-2 col-md-1"><button type="button" class="btn btn-outline-danger w-100 btn-remove-travel-expense" aria-label="ลบรายการค่าใช้จ่าย"><i class="bi bi-trash"></i></button></div>'
        + '</div>';
    \$('#travel-additional-empty').before(html);
    refreshTravelAdditionalEmpty();
    markTravelCalculationDirty();
    calculateTravelCost(false);
}

function parseTravelDate(value, timeValue) {
    var parts = String(value || '').trim().split(/[\/\-]/);
    if (parts.length !== 3) return null;
    var day, month, year;
    if (parts[0].length === 4) {
        year = parseInt(parts[0], 10); month = parseInt(parts[1], 10); day = parseInt(parts[2], 10);
    } else {
        day = parseInt(parts[0], 10); month = parseInt(parts[1], 10); year = parseInt(parts[2], 10);
    }
    if (year > 2400) year -= 543;
    var time = String(timeValue || '00:00').split(':');
    var parsed = new Date(year, month - 1, day, parseInt(time[0], 10) || 0, parseInt(time[1], 10) || 0, 0, 0);
    if (parsed.getFullYear() !== year || parsed.getMonth() !== month - 1 || parsed.getDate() !== day) return null;
    return parsed;
}

function travelDateField(name) { return \$('[name="Development[' + name + ']"]'); }
function travelJsonField(name) { return \$('[name="Development[data_json][' + name + ']"]'); }

function travelCalcDuration() {
    var start = parseTravelDate(travelDateField('vehicle_date_start').val(), travelJsonField('vehicle_time_start').val());
    var end = parseTravelDate(travelDateField('vehicle_date_end').val(), travelJsonField('vehicle_time_end').val());
    if (!start || !end || end <= start) return null;
    return (end.getTime() - start.getTime()) / 3600000;
}

function syncTravelCalculatorDefaults() {
    if (\$travelCalculator.attr('data-has-saved') === '1' || \$travelCalculator.attr('data-defaults-ready') === '1') return;
    var memberIds = {};
    \$('#travel-party-members-list input[name="member_emp_ids[]"]').each(function() {
        if (\$(this).val()) memberIds[String(\$(this).val())] = true;
    });
    \$('#travel-calc-participants').val(Math.max(1, 1 + Object.keys(memberIds).length));
    var sourceDistance = travelJsonField('distance').val();
    if (sourceDistance !== '' && travelCalcNumber('#travel-calc-distance') === 0) \$('#travel-calc-distance').val(sourceDistance);
    var start = parseTravelDate(travelDateField('vehicle_date_start').val(), '00:00');
    var end = parseTravelDate(travelDateField('vehicle_date_end').val(), '00:00');
    if (start && end && end >= start && travelCalcNumber('#travel-calc-nights') === 0) {
        \$('#travel-calc-nights').val(Math.max(0, Math.round((end.getTime() - start.getTime()) / 86400000)));
    }
    \$travelCalculator.attr('data-defaults-ready', '1');
}

function calculateTravelCost(applyValues) {
    syncTravelCalculatorDefaults();
    var participants = Math.max(1, Math.floor(travelCalcNumber('#travel-calc-participants')));
    var durationHours = travelCalcDuration();
    var allowanceUnits = 0;
    if (durationHours !== null) {
        var fullDays = Math.floor(durationHours / 24);
        var remainder = durationHours % 24;
        allowanceUnits = fullDays + (remainder > 12 ? 1 : (remainder > 6 ? 0.5 : 0));
    }
    var allowanceGross = allowanceUnits * 240 * participants;
    var mealDeduction = Math.min(allowanceGross, Math.floor(travelCalcNumber('#travel-calc-meals')) * 80 * participants);
    var allowance = Math.max(0, allowanceGross - mealDeduction);
    var selectedRoomRate = \$('#travel-calc-room-rate').val();
    var roomRate = selectedRoomRate === 'custom' ? travelCalcNumber('#travel-calc-custom-room') : parseFloat(selectedRoomRate || 0);
    var nights = Math.floor(travelCalcNumber('#travel-calc-nights'));
    var roomCount = selectedRoomRate === '1000'
        ? Math.ceil(participants / 2)
        : (roomRate > 0 ? participants : 0);
    var accommodation = roomRate * nights * roomCount;
    var vehicle = travelCalcNumber('#travel-calc-vehicle-rate') * travelCalcNumber('#travel-calc-distance');
    var other = travelAdditionalTotal();
    var registration = travelCalcNumber('[name="Development[data_json][estimated_cost_registration]"]');
    var calculatedTotal = registration + allowance + accommodation + vehicle + other;

    \$('#travel-calc-duration-hours').val(durationHours === null ? 0 : durationHours.toFixed(2));
    \$('#travel-calc-allowance-amount').val(allowance.toFixed(2));
    \$('#travel-calc-accommodation-amount').val(accommodation.toFixed(2));
    \$('#travel-calc-room-count').val(roomCount);
    \$('#travel-calc-vehicle-amount').val(vehicle.toFixed(2));
    \$('#travel-calc-total').val(calculatedTotal.toFixed(2));
    var durationText = durationHours === null
        ? 'วันเวลาเดินทางยังไม่ครบ'
        : Math.floor(durationHours / 24) + ' วัน ' + Math.round(durationHours % 24) + ' ชั่วโมง';
    var allowanceFormula = travelCalcMoney(allowanceGross) + (mealDeduction > 0 ? ' − ค่าอาหาร ' + travelCalcMoney(mealDeduction) : '');
    \$('#travel-calc-summary').html(
        '<div class="mb-2"><span class="fw-semibold">ระยะเวลาเดินทาง:</span> ' + durationText + ' · ' + participants + ' คน · สิทธิเบี้ยเลี้ยง ' + allowanceUnits + ' วัน/คน</div>' +
        '<div>• เบี้ยเลี้ยง: ' + allowanceFormula + ' = <strong>' + travelCalcMoney(allowance) + ' บาท</strong></div>' +
        '<div>• ค่าที่พัก: ' + travelCalcMoney(accommodation) + ' บาท' + (roomCount > 0 ? ' (' + roomCount + ' ห้อง × ' + nights + ' คืน)' : '') + '</div>' +
        '<div>• พาหนะส่วนตัว: ' + travelCalcMoney(vehicle) + ' บาท</div>' +
        '<div>• ค่าใช้จ่ายเพิ่มเติม: ' + travelCalcMoney(other) + ' บาท</div>' +
        (registration > 0 ? '<div>• ค่าลงทะเบียน: ' + travelCalcMoney(registration) + ' บาท</div>' : '')
    );
    \$('#travel-calc-grand-total').text(travelCalcMoney(calculatedTotal));
    if (applyValues) {
        travelJsonField('estimated_cost_allowance').val(allowance.toFixed(2)).trigger('input');
        travelJsonField('estimated_cost_accommodation').val(accommodation.toFixed(2)).trigger('input');
        travelJsonField('estimated_cost_vehicle_fuel').val(vehicle.toFixed(2)).trigger('input');
        travelJsonField('estimated_cost_other').val(other.toFixed(2)).trigger('input');
        travelCalculationDirty = false;
        \$travelCalculator.attr('data-has-saved', '1');
        \$('#travel-calc-sync-status')
            .removeClass('text-warning-emphasis fw-semibold')
            .addClass('text-success-emphasis')
            .text('นำยอดคำนวณไปใช้ในช่องประมาณค่าใช้จ่ายแล้ว');
        \$('#btn-apply-travel-calculation').prop('disabled', true);
    }
}

\$('#travel-calc-room-rate').on('change', function() {
    \$('#travel-calc-custom-room-wrap').toggleClass('d-none', \$(this).val() !== 'custom');
    calculateTravelCost(false);
});
\$('#travel-calc-participants').on('input', function() {
    \$travelCalculator.attr('data-participants-edited', '1');
});
\$('#travel-cost-calculator').on('input change', '.travel-calc-input', function() {
    markTravelCalculationDirty();
    calculateTravelCost(false);
});
\$('#btn-add-travel-expense').on('click', addTravelAdditionalRow);
\$('#travel-additional-items').on('click', '.btn-remove-travel-expense', function() {
    \$(this).closest('.travel-additional-row').remove();
    refreshTravelAdditionalEmpty();
    markTravelCalculationDirty();
    calculateTravelCost(false);
});
\$('#btn-apply-travel-calculation').on('click', function() { calculateTravelCost(true); });
travelDateField('vehicle_date_start').add(travelDateField('vehicle_date_end'))
    .add(travelJsonField('vehicle_time_start')).add(travelJsonField('vehicle_time_end')).on('change', function() {
        syncTravelCalculatorDefaults();
        markTravelCalculationDirty();
        calculateTravelCost(false);
    });
\$('#travel-calc-distance').on('input', function() {
    travelJsonField('distance').val(\$(this).val());
});
travelJsonField('distance').on('input change', function() {
    \$('#travel-calc-distance').val(\$(this).val());
    markTravelCalculationDirty();
    calculateTravelCost(false);
});
syncTravelCalculatorDefaults();
calculateTravelCost(false);
refreshTravelAdditionalEmpty();
if (\$travelCalculator.attr('data-has-saved') === '1') {
    \$('#travel-calc-sync-status').text('กำลังแสดงยอดคำนวณที่บันทึกไว้ แก้ข้อมูลแล้วให้นำยอดใหม่ไปใช้');
} else {
    \$('#travel-calc-sync-status').text('ตรวจสอบรายละเอียด แล้วกด “นำยอดคำนวณไปใช้”');
}
var travelMembersList = document.getElementById('travel-party-members-list');
if (travelMembersList && window.MutationObserver) {
    new MutationObserver(function() {
        if (\$travelCalculator.attr('data-participants-edited') === '1') return;
        var memberIds = {};
        \$('#travel-party-members-list input[name="member_emp_ids[]"]').each(function() {
            if (\$(this).val()) memberIds[String(\$(this).val())] = true;
        });
        \$('#travel-calc-participants').val(Math.max(1, 1 + Object.keys(memberIds).length));
        markTravelCalculationDirty();
        calculateTravelCost(false);
    }).observe(travelMembersList, { childList: true });
}

function updateEstimatedCostTotal() {
    var total = 0;
    \$('#estimated-cost-card .estimated-cost-input').each(function() {
        var v = parseFloat(\$(this).val());
        if (!isNaN(v) && v >= 0) total += v;
    });
    \$('#estimated-cost-total').text(total.toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ','));
}
\$('#estimated-cost-card').on('input change', '.estimated-cost-input', updateEstimatedCostTotal);
travelJsonField('estimated_cost_registration').on('input change', function() {
    markTravelCalculationDirty();
    calculateTravelCost(false);
});
updateEstimatedCostTotal();

\$('#form-development').on('beforeSubmit.travelCalculation', function() {
    if (!travelCalculationDirty) return true;
    \$('#travel-calc-sync-status')
        .addClass('text-danger-emphasis fw-semibold')
        .text('ยังบันทึกไม่ได้: กรุณากด “นำยอดคำนวณไปใช้” ก่อน');
    document.getElementById('travel-cost-calculator').scrollIntoView({ behavior: 'smooth', block: 'center' });
    return false;
});

    thaiDatepicker('#development-date_start,#development-date_end,#development-vehicle_date_start,#development-vehicle_date_end');

JS;
$this->registerJS($js, View::POS_END);

if ($model->isNewRecord && Yii::$app->controller->module->id === 'me') {
    $this->registerJS("handleFormSubmit('#form-development');", View::POS_END);
}

?>
