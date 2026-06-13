<?php

use app\widgets\datepicker\DatepickerThai;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var \app\modules\booking\models\Meeting $model */
/** @var array $saveErrors */
/** @var string $formAction */
/** @var array $roomCards code => rich metadata */
/** @var array $roomLayouts */
/** @var array $urgentOptions */
/** @var array $equipmentItems */
/** @var array $quickDates */
/** @var array $periodPresets */
/** @var string $periodValue */
/** @var string[] $selectedEquipment */
/** @var string $requesterName */
/** @var string $requesterDept */
/** @var string $dateInputId */
/** @var string $dateEndInputId */
/** @var string $timeStartInputId */
/** @var string $timeEndInputId */
/** @var string $roomInputId */
/** @var string $layoutInputId */
/** @var string $urgentInputId */
/** @var string $periodInputId */
/** @var string $titleInputId */
/** @var string $peopleInputId */
/** @var string $phoneInputId */
/** @var string $detailsInputId */

$saveErrors = $saveErrors ?? [];
?>

<section class="bm-mode bm-mode-wizard bm-panel" data-mode-section="wizard">

    <?php if (!empty($saveErrors)): ?>
        <div class="bm-alert" role="alert">
            <i data-lucide="alert-circle" class="mi-sm mt-1" aria-hidden="true"></i>
            <div>
                <p class="mb-1 fw-semibold">กรุณาตรวจสอบข้อมูลที่กรอก</p>
                <ul>
                    <?php foreach ($saveErrors as $msg): ?>
                        <li><?= Html::encode(is_string($msg) ? $msg : (string) reset((array) $msg)) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <nav class="bm-stepper" aria-label="ขั้นตอนการจอง">
        <button type="button" class="bm-step-tab is-active" data-step-jump="1"><span>1</span>วันเวลา</button>
        <button type="button" class="bm-step-tab" data-step-jump="2"><span>2</span>เลือกห้อง</button>
        <button type="button" class="bm-step-tab" data-step-jump="3"><span>3</span>รายละเอียด</button>
        <button type="button" class="bm-step-tab" data-step-jump="4"><span>4</span>ยืนยัน</button>
    </nav>

    <?php $form = ActiveForm::begin([
        'id'      => 'mobile-booking-meeting-form',
        'action'  => $formAction,
        'method'  => 'post',
        'options' => ['novalidate' => 'novalidate'],
        'fieldConfig' => [
            'options'      => ['class' => 'mb-0'],
            'labelOptions' => ['class' => 'form-label fw-semibold'],
            'errorOptions' => ['class' => 'invalid-feedback d-block'],
        ],
    ]); ?>

    <?= Html::activeHiddenInput($model, 'room_id', ['id' => $roomInputId]) ?>
    <?= Html::activeHiddenInput($model, 'room_layout_id', ['id' => $layoutInputId]) ?>
    <?= Html::activeHiddenInput($model, 'urgent', ['id' => $urgentInputId]) ?>
    <?= Html::activeHiddenInput($model, 'data_json[period_time]', ['id' => $periodInputId]) ?>

    <!-- ─── Step 1: วันเวลา ─── -->
    <section class="bm-panel" data-step-panel="1">
        <div class="bm-section-head">
            <span class="bm-section-icon" aria-hidden="true"><i data-lucide="clock-3"></i></span>
            <div>
                <h2 class="bm-section-title">กำหนดจุดเริ่มต้นและสิ้นสุด</h2>
                <p class="bm-section-sub">ระบุวันเวลาเริ่มและวันเวลาสิ้นสุดแบบเดียวกับระบบจองรถ เพื่อให้ตรวจสอบห้องว่างแม่นขึ้น</p>
            </div>
        </div>

        <div class="bm-chip-grid" aria-label="เลือกวันที่แบบเร็ว">
            <?php foreach ($quickDates as $q): ?>
                <button type="button"
                        class="bm-chip"
                        data-date-value="<?= Html::encode($q['value']) ?>">
                    <span><?= Html::encode($q['label']) ?></span>
                    <small><?= Html::encode($q['date']) ?></small>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="bm-field-stack">
            <div class="bm-point-grid">
                <div class="bm-point-card">
                    <div class="bm-point-head">
                        <i data-lucide="play-circle" aria-hidden="true"></i>
                        <span>จุดเริ่มต้น</span>
                    </div>
                    <div class="bm-inline-fields">
                        <?= $form->field($model, 'date_start')->widget(DatepickerThai::class, [
                            'options' => [
                                'id'           => $dateInputId,
                                'placeholder'  => 'วันเริ่ม',
                                'class'        => 'form-control',
                                'autocomplete' => 'off',
                            ],
                        ])->label('วันเริ่ม') ?>
                        <?= $form->field($model, 'time_start')->input('time', [
                            'id'    => $timeStartInputId,
                            'class' => 'form-control',
                            'step'  => 300,
                        ])->label('เวลาเริ่ม') ?>
                    </div>
                </div>

                <div class="bm-point-card">
                    <div class="bm-point-head">
                        <i data-lucide="stop-circle" aria-hidden="true"></i>
                        <span>จุดสิ้นสุด</span>
                    </div>
                    <div class="bm-inline-fields">
                        <?= $form->field($model, 'date_end')->widget(DatepickerThai::class, [
                            'options' => [
                                'id'           => $dateEndInputId,
                                'placeholder'  => 'วันสิ้นสุด',
                                'class'        => 'form-control',
                                'autocomplete' => 'off',
                            ],
                        ])->label('วันสิ้นสุด') ?>
                        <?= $form->field($model, 'time_end')->input('time', [
                            'id'    => $timeEndInputId,
                            'class' => 'form-control',
                            'step'  => 300,
                        ])->label('เวลาสิ้นสุด') ?>
                    </div>
                </div>
            </div>

            <div class="bm-period-grid" aria-label="เลือกช่วงเวลา">
                <?php foreach ($periodPresets as $preset): ?>
                    <button type="button"
                            class="bm-radio-chip <?= $periodValue === $preset['value'] ? 'is-active' : '' ?>"
                            data-period-value="<?= Html::encode($preset['value']) ?>"
                            data-time-start="<?= Html::encode($preset['start']) ?>"
                            data-time-end="<?= Html::encode($preset['end']) ?>">
                        <span><?= Html::encode($preset['label']) ?></span>
                        <small><?= Html::encode($preset['time']) ?></small>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="bm-status-note" id="bm-time-status" role="status" aria-live="polite">
                <i data-lucide="info" class="mi-sm mt-1" aria-hidden="true"></i>
                <span>เลือกจุดเริ่มต้นและจุดสิ้นสุดเพื่อไปขั้นตอนเลือกห้อง</span>
            </div>
        </div>
        <div class="bm-step-error" data-step-error="1"></div>
    </section>

    <!-- ─── Step 2: เลือกห้อง ─── -->
    <section class="bm-panel" data-step-panel="2" hidden>
        <div class="bm-section-head">
            <span class="bm-section-icon" aria-hidden="true"><i data-lucide="layout-grid"></i></span>
            <div>
                <h2 class="bm-section-title">เลือกห้องประชุม</h2>
                <p class="bm-section-sub">แตะห้องที่ว่างตามช่วงเวลาที่เลือก รายละเอียดห้องจัดเป็นการ์ดให้อ่านเร็ว</p>
            </div>
        </div>

        <div class="bm-time-strip">
            <span class="bm-time-strip-icon" aria-hidden="true"><i data-lucide="calendar-clock"></i></span>
            <div>
                <p class="bm-time-label" id="bm-strip-main">ยังไม่ได้เลือกช่วงเวลา</p>
                <p class="bm-time-sub" id="bm-strip-sub">ตรวจสอบห้องว่างหลังเลือกวันและเวลา</p>
            </div>
        </div>

        <button type="button" class="bm-refresh" id="bm-check-availability">
            <i data-lucide="refresh-cw" aria-hidden="true"></i>
            <span>ตรวจสอบห้องว่าง</span>
        </button>

        <?php if (empty($roomCards)): ?>
            <div class="bm-empty">
                <i data-lucide="inbox" class="mi-lg mb-2" aria-hidden="true"></i>
                <p class="mb-1 fw-semibold">ยังไม่มีข้อมูลห้องประชุม</p>
                <p class="mb-0 small">กรุณาติดต่อผู้ดูแลระบบเพื่อเพิ่มห้องประชุม</p>
            </div>
        <?php else: ?>
            <div class="bm-room-list" id="bm-room-list" role="radiogroup" aria-label="เลือกห้องประชุม">
                <?php foreach ($roomCards as $code => $room): ?>
                    <?php
                    $accessories = array_slice((array) ($room['accessories'] ?? []), 0, 3);
                    $capacity = $room['capacity'] ?? null;
                    ?>
                    <button type="button"
                            class="bm-room-card"
                            data-room-card
                            data-room-code="<?= Html::encode($code) ?>"
                            data-availability="unknown"
                            role="radio"
                            aria-checked="false">
                        <span class="bm-room-card-head">
                            <span class="min-w-0">
                                <span class="bm-room-title"><?= Html::encode($room['title'] ?? $code) ?></span>
                            </span>
                            <span class="bm-badge" data-room-status>รอตรวจสอบ</span>
                        </span>
                        <span class="bm-room-meta">
                            <span><i data-lucide="users" aria-hidden="true"></i><?= $capacity !== null ? Html::encode((string) $capacity) . ' ที่นั่ง' : 'ไม่ระบุความจุ' ?></span>
                            <span><i data-lucide="building-2" aria-hidden="true"></i><?= Html::encode((string) ($room['location'] ?? 'ไม่ระบุอาคาร')) ?></span>
                        </span>
                        <?php if (!empty($accessories)): ?>
                            <span class="bm-room-tags">
                                <?php foreach ($accessories as $acc): ?>
                                    <span class="bm-tag"><?= Html::encode((string) $acc) ?></span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="bm-step-error" data-step-error="2"></div>
    </section>

    <!-- ─── Step 3: รายละเอียด ─── -->
    <section class="bm-panel" data-step-panel="3" hidden>
        <div class="bm-section-head">
            <span class="bm-section-icon" aria-hidden="true"><i data-lucide="clipboard-list"></i></span>
            <div>
                <h2 class="bm-section-title">รายละเอียดการประชุม</h2>
                <p class="bm-section-sub">กรอกเฉพาะข้อมูลที่จำเป็น ระบบเติมผู้จองและหน่วยงานจากบัญชีของคุณ</p>
            </div>
        </div>

        <div class="bm-requester">
            <span class="bm-requester-icon" aria-hidden="true"><i data-lucide="user-round"></i></span>
            <div>
                <p class="bm-requester-name"><?= Html::encode($requesterName) ?></p>
                <p class="bm-requester-dept"><?= Html::encode($requesterDept) ?></p>
            </div>
        </div>

        <div class="bm-field-stack">
            <?= $form->field($model, 'title')->textInput([
                'id'           => $titleInputId,
                'maxlength'    => 255,
                'placeholder'  => 'เช่น ประชุมคณะกรรมการบริหาร',
                'autocomplete' => 'off',
            ])->label('หัวข้อประชุม') ?>

            <div class="bm-inline-fields">
                <?= $form->field($model, 'emp_number')->input('number', [
                    'id'          => $peopleInputId,
                    'min'         => 1,
                    'max'         => 999,
                    'placeholder' => 'จำนวนคน',
                ])->label('จำนวนคน') ?>
                <?= $form->field($model, 'data_json[phone]')->textInput([
                    'id'          => $phoneInputId,
                    'inputmode'   => 'tel',
                    'placeholder' => 'เบอร์ติดต่อ',
                ])->label('เบอร์ติดต่อ') ?>
            </div>

            <?php if (!empty($roomLayouts)): ?>
                <div>
                    <label class="form-label fw-semibold">รูปแบบการจัดห้อง</label>
                    <div class="bm-layout-grid" data-choice-group="layout">
                        <?php foreach ($roomLayouts as $code => $label): ?>
                            <button type="button"
                                    class="bm-radio-chip <?= (string) $model->room_layout_id === (string) $code ? 'is-active' : '' ?>"
                                    data-choice-input="<?= Html::encode($layoutInputId) ?>"
                                    data-choice-value="<?= Html::encode((string) $code) ?>">
                                <?= Html::encode((string) $label) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($urgentOptions)): ?>
                <div>
                    <label class="form-label fw-semibold">ความเร่งด่วน</label>
                    <div class="bm-layout-grid" data-choice-group="urgent">
                        <?php foreach ($urgentOptions as $code => $label): ?>
                            <button type="button"
                                    class="bm-radio-chip <?= (string) $model->urgent === (string) $code ? 'is-active' : '' ?>"
                                    data-choice-input="<?= Html::encode($urgentInputId) ?>"
                                    data-choice-value="<?= Html::encode((string) $code) ?>">
                                <?= Html::encode((string) $label) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?= $form->field($model, 'data_json[meeting_details]')->textarea([
                'id'          => $detailsInputId,
                'rows'        => 3,
                'placeholder' => 'รายละเอียดเพิ่มเติมหรือหมายเหตุถึงผู้ดูแลห้อง',
            ])->label('หมายเหตุเพิ่มเติม') ?>

            <?php if (!empty($equipmentItems)): ?>
                <div>
                    <label class="form-label fw-semibold">อุปกรณ์ที่ต้องใช้</label>
                    <div class="bm-equipment-grid">
                        <?php foreach ($equipmentItems as $value => $label): ?>
                            <?php
                            $value = (string) $value;
                            $inputId = 'bm-eq-' . preg_replace('/[^A-Za-z0-9\-_]/', '-', $value);
                            ?>
                            <label class="bm-equipment-chip <?= in_array($value, $selectedEquipment, true) ? 'is-active' : '' ?>" for="<?= Html::encode($inputId) ?>">
                                <input type="checkbox"
                                       id="<?= Html::encode($inputId) ?>"
                                       name="<?= Html::encode(Html::getInputName($model, 'data_json[equipment]')) ?>[]"
                                       value="<?= Html::encode($value) ?>"
                                       <?= in_array($value, $selectedEquipment, true) ? 'checked' : '' ?>>
                                <span><?= Html::encode((string) $label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="bm-step-error" data-step-error="3"></div>
    </section>

    <!-- ─── Step 4: ยืนยัน ─── -->
    <section class="bm-panel" data-step-panel="4" hidden>
        <div class="bm-section-head">
            <span class="bm-section-icon" aria-hidden="true"><i data-lucide="check-circle-2"></i></span>
            <div>
                <h2 class="bm-section-title">ตรวจสอบและยืนยัน</h2>
                <p class="bm-section-sub">ตรวจสอบข้อมูลก่อนส่งคำขอจองห้องประชุม</p>
            </div>
        </div>

        <div class="bm-summary-card" aria-label="สรุปการจอง">
            <div class="bm-summary-row">
                <span class="bm-summary-label">วันและเวลา</span>
                <span class="bm-summary-value" id="bm-summary-time">-</span>
            </div>
            <div class="bm-summary-row">
                <span class="bm-summary-label">ห้องประชุม</span>
                <span class="bm-summary-value" id="bm-summary-room">-</span>
            </div>
            <div class="bm-summary-row">
                <span class="bm-summary-label">รายละเอียด</span>
                <span class="bm-summary-value" id="bm-summary-detail">-</span>
            </div>
            <div class="bm-summary-row">
                <span class="bm-summary-label">ผู้จอง</span>
                <span class="bm-summary-value"><?= Html::encode($requesterName) ?> · <?= Html::encode($requesterDept) ?></span>
            </div>
            <div class="bm-summary-row">
                <span class="bm-summary-label">อุปกรณ์/หมายเหตุ</span>
                <span class="bm-summary-value" id="bm-summary-extra">-</span>
            </div>
        </div>

        <div class="bm-confirm">
            <input type="checkbox" id="bm-confirm-check">
            <label for="bm-confirm-check">ยืนยันว่าข้อมูลวัน เวลา ห้องประชุม และเบอร์ติดต่อถูกต้อง พร้อมส่งคำขอให้ผู้ดูแลตรวจสอบ</label>
        </div>
        <div class="bm-step-error" data-step-error="4"></div>
    </section>

    <?php ActiveForm::end(); ?>
</section>
