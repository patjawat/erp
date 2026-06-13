<?php

use app\widgets\datepicker\DatepickerThai;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var \app\modules\booking\models\Vehicle $model */
/** @var array $saveErrors */
/** @var int $existingGoType */
/** @var string $existingDriver */
/** @var string $existingPhone */
/** @var int $existingPassengers */
/** @var string $existingVehicleType */
/** @var string $existingPlate */
/** @var string $existingUrgent */
/** @var string $existingNotes */
/** @var string $requesterName */
/** @var string $requesterDept */

$saveErrors = $saveErrors ?? [];
?>

<section class="bv-mode bv-mode-wizard" data-mode-section="wizard">

    <nav class="bv-wizard" id="bv-wizard" aria-label="ขั้นตอนการจองรถ">
        <div class="bv-wizard-track" role="progressbar"
             aria-valuemin="1" aria-valuemax="5" aria-valuenow="1"
             aria-label="ความคืบหน้า">
            <div class="bv-wizard-fill" id="bv-wizard-fill"></div>
        </div>
        <ol class="bv-wizard-steps">
            <?php foreach ([
                1 => 'เดินทาง',
                2 => 'ผู้ขอ',
                3 => 'รถและคนขับ',
                4 => 'ตรวจสอบ',
                5 => 'ยืนยัน',
            ] as $num => $lbl): ?>
                <li class="bv-wizard-step <?= $num === 1 ? 'is-active' : '' ?>" data-step="<?= $num ?>">
                    <span class="bv-wizard-pip">
                        <span class="bv-wizard-pip-num"><?= $num ?></span>
                        <i data-lucide="check" class="bv-wizard-pip-check"></i>
                    </span>
                    <span><?= Html::encode($lbl) ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <div class="bv-body px-3">

        <?php if (!empty($saveErrors)): ?>
            <div class="bv-error-summary mb-3" role="alert">
                <i data-lucide="alert-triangle" class="mi-sm flex-shrink-0 mt-1"></i>
                <div>
                    <strong class="d-block mb-1">กรุณาตรวจสอบฟิลด์ที่กรอก</strong>
                    <ul>
                        <?php foreach ($saveErrors as $attr => $msg): ?>
                            <li><?= Html::encode(is_string($msg) ? $msg : (string) reset((array) $msg)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'id'      => 'mobile-booking-vehicle-form',
            'method'  => 'post',
            'options' => ['novalidate' => 'novalidate'],
            'fieldConfig' => [
                'options'      => ['class' => 'mb-3'],
                'labelOptions' => ['class' => 'form-label'],
                'errorOptions' => ['class' => 'invalid-feedback d-block'],
            ],
        ]); ?>

        <input type="hidden"
               name="<?= Html::encode(Html::getInputName($model, 'refer_type')) ?>"
               value="<?= Html::encode((string) ($model->refer_type ?? 'normal')) ?>">

        <!-- ─── Step 1: ข้อมูลการเดินทาง ─── -->
        <section class="bv-panel is-active" data-step-panel="1" data-step-title="การเดินทาง">
            <header class="bv-panel-head">
                <p class="bv-panel-eyebrow">ขั้นตอนที่ 1 จาก 5</p>
                <h2 class="bv-panel-title">ข้อมูลการเดินทาง</h2>
                <p class="bv-panel-desc">วัน เวลา จุดหมาย และวัตถุประสงค์ของการใช้รถ</p>
            </header>

            <div class="bv-card">
                <div class="mb-3">
                    <label class="form-label is-req">ประเภทการเดินทาง</label>
                    <div class="pill-group" role="radiogroup" aria-label="ประเภทการเดินทาง" aria-required="true">
                        <?php foreach ([1 => 'ไปกลับวันเดียว', 2 => 'ค้างคืน'] as $val => $lab): ?>
                            <?php $checked = $existingGoType === $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                       name="<?= Html::encode(Html::getInputName($model, 'go_type')) ?>"
                                       value="<?= $val ?>"
                                       <?= $checked ? 'checked' : '' ?>
                                       data-pill-target="bv-go-type">
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="bv-go-type" value="<?= $existingGoType ?>">
                </div>

                <div class="bv-row bv-row-2 mb-3">
                    <?= $form->field($model, 'date_start', [
                        'template'     => '{label}{input}{error}',
                        'options'      => ['class' => 'mb-0'],
                        'labelOptions' => ['class' => 'form-label is-req'],
                    ])->widget(DatepickerThai::class, [
                        'options' => ['class' => 'form-control', 'placeholder' => 'วันเริ่ม', 'autocomplete' => 'off', 'aria-required' => 'true', 'required' => true],
                    ])->label('วันที่ใช้งาน') ?>
                    <?= $form->field($model, 'time_start', [
                        'template'     => '{label}{input}{error}',
                        'options'      => ['class' => 'mb-0'],
                        'labelOptions' => ['class' => 'form-label is-req'],
                    ])->input('time', ['step' => 300, 'aria-required' => 'true', 'required' => true])->label('เวลาออกเดินทาง') ?>
                </div>

                <div class="bv-row bv-row-2 mb-3" id="bv-end-row">
                    <?= $form->field($model, 'date_end', [
                        'template'     => '{label}{input}{error}',
                        'options'      => ['class' => 'mb-0'],
                    ])->widget(DatepickerThai::class, [
                        'options' => ['class' => 'form-control', 'placeholder' => 'วันสิ้นสุด', 'autocomplete' => 'off'],
                    ])->label('วันสิ้นสุด') ?>
                    <?= $form->field($model, 'time_end', [
                        'template'     => '{label}{input}{error}',
                        'options'      => ['class' => 'mb-0'],
                        'labelOptions' => ['class' => 'form-label is-req'],
                    ])->input('time', ['step' => 300, 'aria-required' => 'true', 'required' => true])->label('เวลาเดินทางกลับ') ?>
                </div>

                <?= $form->field($model, 'location', [
                    'labelOptions' => ['class' => 'form-label is-req'],
                ])->widget(Select2::class, [
                    'data'    => $model->ListOrg(),
                    'options' => [
                        'placeholder'   => 'ค้นหาหรือพิมพ์จุดหมายปลายทาง',
                        'aria-required' => 'true',
                        'aria-label'    => 'จุดหมายปลายทาง',
                        'required'      => true,
                    ],
                    'pluginOptions' => [
                        'tags'                    => true,
                        'allowClear'              => true,
                        'minimumResultsForSearch' => 0,
                        'tokenSeparators'         => [],
                        'language'                => [
                            'noResults'     => new JsExpression('function(){ return "ไม่พบในรายการ — กด Enter เพื่อใช้ข้อความนี้"; }'),
                            'searching'     => new JsExpression('function(){ return "กำลังค้นหา..."; }'),
                            'inputTooShort' => new JsExpression('function(){ return "พิมพ์อย่างน้อย 1 ตัวอักษร"; }'),
                        ],
                    ],
                ])->label('จุดหมายปลายทาง') ?>

                <?= $form->field($model, 'reason', [
                    'labelOptions' => ['class' => 'form-label is-req'],
                    'options'      => ['class' => 'mb-0'],
                ])->textarea([
                    'rows' => 3,
                    'placeholder' => 'ระบุวัตถุประสงค์ของการใช้รถ',
                    'aria-required' => 'true',
                    'required' => true,
                ])->label('วัตถุประสงค์การใช้รถ') ?>
            </div>
        </section>

        <!-- ─── Step 2: รายละเอียดผู้ขอ ─── -->
        <section class="bv-panel" data-step-panel="2" data-step-title="ผู้ขอใช้รถ" hidden>
            <header class="bv-panel-head">
                <p class="bv-panel-eyebrow">ขั้นตอนที่ 2 จาก 5</p>
                <h2 class="bv-panel-title">รายละเอียดผู้ขอใช้รถ</h2>
                <p class="bv-panel-desc">ตรวจสอบข้อมูลผู้ขอและช่องทางติดต่อ</p>
            </header>

            <div class="bv-card">
                <div class="bv-kv-list">
                    <div class="bv-kv">
                        <span class="bv-kv-icon" aria-hidden="true"><i data-lucide="user"></i></span>
                        <div class="bv-kv-body">
                            <p class="bv-kv-key mb-0">ผู้ขอใช้รถ</p>
                            <p class="bv-kv-val mb-0"><?= Html::encode($requesterName) ?></p>
                        </div>
                    </div>
                    <div class="bv-kv">
                        <span class="bv-kv-icon" aria-hidden="true"><i data-lucide="building-2"></i></span>
                        <div class="bv-kv-body">
                            <p class="bv-kv-key mb-0">หน่วยงาน</p>
                            <p class="bv-kv-val mb-0"><?= Html::encode($requesterDept) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bv-card">
                <div class="mb-3">
                    <label for="bv-phone" class="form-label is-req">เบอร์โทรศัพท์ติดต่อ</label>
                    <input type="tel"
                           id="bv-phone"
                           name="<?= Html::encode(Html::getInputName($model, 'data_json[phone]')) ?>"
                           value="<?= Html::encode($existingPhone) ?>"
                           class="form-control"
                           inputmode="tel"
                           autocomplete="tel"
                           placeholder="08X-XXX-XXXX"
                           aria-required="true"
                           required>
                </div>

                <div class="mb-0">
                    <label for="bv-passengers" class="form-label is-req">จำนวนผู้โดยสาร</label>
                    <input type="number"
                           id="bv-passengers"
                           name="<?= Html::encode(Html::getInputName($model, 'data_json[passengers]')) ?>"
                           value="<?= (int) $existingPassengers ?>"
                           class="form-control"
                           inputmode="numeric"
                           min="1" max="99"
                           aria-required="true"
                           required>
                </div>
            </div>
        </section>

        <!-- ─── Step 3: รถและคนขับ ─── -->
        <section class="bv-panel" data-step-panel="3" data-step-title="รถและคนขับ" hidden>
            <header class="bv-panel-head">
                <p class="bv-panel-eyebrow">ขั้นตอนที่ 3 จาก 5</p>
                <h2 class="bv-panel-title">รายละเอียดรถและคนขับ</h2>
                <p class="bv-panel-desc">เลือกประเภทรถและระบุผู้ขับขี่ตามต้องการ</p>
            </header>

            <div class="bv-card">
                <div class="mb-3">
                    <label class="form-label is-req">ประเภทรถ</label>
                    <div class="pill-group" role="radiogroup" aria-label="ประเภทรถ" aria-required="true">
                        <?php foreach (['general' => 'รถยนต์ทั่วไป', 'ambulance' => 'รถพยาบาล'] as $val => $lab): ?>
                            <?php $checked = $existingVehicleType === $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                       name="<?= Html::encode(Html::getInputName($model, 'vehicle_type_id')) ?>"
                                       value="<?= Html::encode($val) ?>"
                                       <?= $checked ? 'checked' : '' ?>
                                       data-pill-target="bv-vehicle-type">
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="bv-vehicle-type" value="<?= Html::encode($existingVehicleType) ?>">
                </div>

                <div class="mb-3">
                    <label for="bv-plate" class="form-label">ทะเบียน / หมายเลขรถที่ต้องการ
                        <span class="text-body-tertiary fw-normal">(ไม่บังคับ)</span>
                    </label>
                    <input type="text"
                           id="bv-plate"
                           name="<?= Html::encode(Html::getInputName($model, 'license_plate')) ?>"
                           value="<?= Html::encode($existingPlate) ?>"
                           class="form-control"
                           placeholder="เว้นว่างให้เจ้าหน้าที่จัดสรรอัตโนมัติ">
                </div>

                <div class="mb-3">
                    <label class="form-label is-req">ระดับความเร่งด่วน</label>
                    <div class="pill-group" role="radiogroup" aria-label="ระดับความเร่งด่วน" aria-required="true">
                        <?php foreach (['ปกติ' => 'ปกติ', 'ด่วน' => 'ด่วน', 'ด่วนที่สุด' => 'ด่วนที่สุด'] as $val => $lab): ?>
                            <?php $checked = $existingUrgent === $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                       name="<?= Html::encode(Html::getInputName($model, 'urgent')) ?>"
                                       value="<?= Html::encode($val) ?>"
                                       <?= $checked ? 'checked' : '' ?>
                                       data-pill-target="bv-urgent">
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="bv-urgent" value="<?= Html::encode($existingUrgent) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">ผู้ขับรถ
                        <span class="text-body-tertiary fw-normal">(ไม่บังคับ)</span>
                    </label>
                    <div class="pill-group" role="radiogroup" aria-label="ผู้ขับรถ">
                        <?php foreach (['' => 'ไม่ระบุ', 'self' => 'ขับเอง', 'driver' => 'พนักงาน'] as $val => $lab): ?>
                            <?php $checked = $existingDriver === (string) $val; ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio"
                                       name="<?= Html::encode(Html::getInputName($model, 'data_json[driver]')) ?>"
                                       value="<?= Html::encode((string) $val) ?>"
                                       <?= $checked ? 'checked' : '' ?>
                                       data-pill-target="bv-driver">
                                <?= Html::encode($lab) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="bv-driver" value="<?= Html::encode($existingDriver) ?>">
                </div>

                <div class="mb-0">
                    <label for="bv-notes" class="form-label">หมายเหตุเพิ่มเติม
                        <span class="text-body-tertiary fw-normal">(ไม่บังคับ)</span>
                    </label>
                    <textarea id="bv-notes"
                              name="<?= Html::encode(Html::getInputName($model, 'data_json[notes]')) ?>"
                              class="form-control"
                              rows="3"
                              placeholder="ข้อมูลเพิ่มเติมที่เจ้าหน้าที่ควรทราบ"><?= Html::encode($existingNotes) ?></textarea>
                </div>
            </div>
        </section>

        <!-- ─── Step 4: ตรวจสอบ ─── -->
        <section class="bv-panel" data-step-panel="4" data-step-title="ตรวจสอบข้อมูล" hidden>
            <header class="bv-panel-head">
                <p class="bv-panel-eyebrow">ขั้นตอนที่ 4 จาก 5</p>
                <h2 class="bv-panel-title">ตรวจสอบข้อมูล</h2>
                <p class="bv-panel-desc">ยืนยันความถูกต้องก่อนส่งคำขอ กดแก้ไขเพื่อกลับไปแก้</p>
            </header>

            <div class="bv-summary-card">
                <header class="bv-summary-head">
                    <h3 class="bv-summary-title"><i data-lucide="calendar-range"></i> การเดินทาง</h3>
                    <button type="button" class="bv-summary-edit" data-jump-step="1">
                        <i data-lucide="pencil" class="me-1" style="width:14px;height:14px;vertical-align:-2px;"></i> แก้ไข
                    </button>
                </header>
                <div class="bv-summary-body">
                    <dl class="bv-summary-dl" data-summary="trip"></dl>
                </div>
            </div>

            <div class="bv-summary-card">
                <header class="bv-summary-head">
                    <h3 class="bv-summary-title"><i data-lucide="user"></i> ผู้ขอใช้รถ</h3>
                    <button type="button" class="bv-summary-edit" data-jump-step="2">
                        <i data-lucide="pencil" class="me-1" style="width:14px;height:14px;vertical-align:-2px;"></i> แก้ไข
                    </button>
                </header>
                <div class="bv-summary-body">
                    <dl class="bv-summary-dl" data-summary="requester"></dl>
                </div>
            </div>

            <div class="bv-summary-card">
                <header class="bv-summary-head">
                    <h3 class="bv-summary-title"><i data-lucide="car"></i> รถและคนขับ</h3>
                    <button type="button" class="bv-summary-edit" data-jump-step="3">
                        <i data-lucide="pencil" class="me-1" style="width:14px;height:14px;vertical-align:-2px;"></i> แก้ไข
                    </button>
                </header>
                <div class="bv-summary-body">
                    <dl class="bv-summary-dl" data-summary="vehicle"></dl>
                </div>
            </div>

            <div class="bv-completeness" id="bv-completeness" role="status" aria-live="polite">
                <i data-lucide="check-circle"></i>
                <span id="bv-completeness-text">ข้อมูลครบถ้วน พร้อมส่งคำขอ</span>
            </div>
        </section>

        <!-- ─── Step 5: ยืนยัน ─── -->
        <section class="bv-panel" data-step-panel="5" data-step-title="ยืนยันการจอง" hidden>
            <header class="bv-panel-head">
                <p class="bv-panel-eyebrow">ขั้นตอนสุดท้าย</p>
                <h2 class="bv-panel-title">ยืนยันการจอง</h2>
                <p class="bv-panel-desc">ตรวจสอบความถูกต้องครั้งสุดท้ายก่อนกดส่งคำขอ</p>
            </header>

            <div class="bv-confirm-card">
                <label class="bv-confirm-row" for="bv-confirm-chk">
                    <input type="checkbox" id="bv-confirm-chk" aria-describedby="bv-confirm-fineprint">
                    <span class="bv-confirm-text">
                        ข้าพเจ้ายืนยันว่าข้อมูลที่กรอกถูกต้องและขอใช้รถเพื่อปฏิบัติงานราชการตามวัตถุประสงค์ที่ระบุ
                    </span>
                </label>
                <p class="bv-confirm-fineprint" id="bv-confirm-fineprint">
                    เจ้าหน้าที่จะตรวจสอบคำขอและแจ้งผลผ่านระบบ
                    หากต้องการแก้ไขหลังส่งคำขอ ให้ติดต่องานยานพาหนะ
                </p>
            </div>
        </section>

        <?php ActiveForm::end(); ?>
    </div>
</section>
