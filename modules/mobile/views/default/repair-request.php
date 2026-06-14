<?php

use app\components\ThaiDateHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\helpdesk2\models\Helpdesk $model */
/** @var \app\modules\hr\models\Employees $employee */
/** @var array|null $assetInfo */
/** @var string $sendType */
/** @var array $deviceTypes */
/** @var array $urgencyOpts */
/** @var array $repairGroups */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'แจ้งส่งซ่อม';
$this->params['mobileSubtitle'] = 'กรอกข้อมูลทีละขั้นตอน';

$assetInfo    = $assetInfo ?? null;
$existingAsset    = (string) ($assetInfo['code'] ?? ($model->asset_number ?? ''));
$existingAssetName = (string) ($assetInfo['name'] ?? '');
$existingDataJson = is_array($model->data_json ?? null) ? $model->data_json : [];

// Prefill ค่าผู้แจ้ง (location/phone) จาก employee + data_json เดิม
$prefillPhone = trim((string) ($existingDataJson['phone'] ?? ''));
if ($prefillPhone === '') {
    try { $prefillPhone = (string) ($employee->phone ?? $employee->mobile ?? ''); } catch (\Throwable $e) {}
}
$prefillLocation = trim((string) ($existingDataJson['location'] ?? ($assetInfo['location'] ?? '')));

$repairGetGroupUrl = Url::to(['/mobile/default/repair-get-group']);
$scanUrl = Url::to(['/mobile/default/scan', 'return' => 'repair']);
$backUrl = $existingAsset !== ''
    ? Url::to(['/mobile/default/asset', 'code' => $existingAsset])
    : Url::to(['/mobile/default/services']);
?>

<style>
.rp-root { margin: -1rem -1rem 0; display: flex; flex-direction: column; }
.rp-scroll { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 9.5rem); }

/* ───── Sticky progress wizard ───── */
.rp-wizard {
    position: sticky; top: 0;
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    padding: var(--space-md) var(--space-md) var(--space-sm);
    border-bottom: 1px solid var(--ink-line);
    box-shadow: 0 2px 8px color-mix(in oklch, var(--ink) 4%, transparent);
}
.rp-wizard-track { position: relative; height: 4px; background: var(--surface-3); border-radius: 999px; overflow: hidden; margin-bottom: var(--space-sm); }
.rp-wizard-fill { position: absolute; inset: 0 auto 0 0; width: 25%; background: var(--mobile-primary); border-radius: 999px; transition: width 360ms cubic-bezier(0.22, 1, 0.36, 1); }
.rp-wizard-steps { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-2xs); }
.rp-wizard-step { display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; color: var(--ink-4); font-size: 0.6875rem; font-weight: 500; line-height: 1.2; }
.rp-wizard-pip { width: 1.5rem; height: 1.5rem; border-radius: 50%; background: var(--surface-3); color: var(--ink-4); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; transition: background 240ms, color 240ms; }
.rp-wizard-pip svg { width: 14px; height: 14px; }
.rp-wizard-step.is-active .rp-wizard-pip { background: var(--mobile-primary); color: #fff; box-shadow: 0 0 0 4px color-mix(in oklch, var(--mobile-primary) 12%, transparent); }
.rp-wizard-step.is-active { color: var(--mobile-primary); font-weight: 600; }
.rp-wizard-step.is-done .rp-wizard-pip { background: var(--success); color: #fff; }
.rp-wizard-step.is-done { color: var(--ink-2); }
.rp-wizard-step.is-done .rp-wizard-pip-num { display: none; }
.rp-wizard-step:not(.is-done) .rp-wizard-pip-check { display: none; }

/* ───── Panels ───── */
.rp-body { padding: var(--space-md); display: flex; flex-direction: column; gap: var(--space-md); }
.rp-panel { display: flex; flex-direction: column; gap: var(--space-md); }
.rp-panel[hidden] { display: none !important; }
.rp-panel.is-active { animation: rp-step-enter 280ms cubic-bezier(0.22, 1, 0.36, 1); }
@keyframes rp-step-enter { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.rp-panel-head { padding: 0 var(--space-2xs); }
.rp-panel-eyebrow { font-size: var(--fs-xs); font-weight: 500; color: var(--mobile-primary); margin: 0 0 4px; }
.rp-panel-title { font-size: var(--fs-xl); font-weight: 700; color: var(--ink); margin: 0 0 4px; line-height: 1.2; text-wrap: balance; }
.rp-panel-desc { font-size: var(--fs-sm); color: var(--ink-3); margin: 0; line-height: 1.45; }

.rp-card { background: var(--surface); border-radius: 16px; padding: 1.25rem; box-shadow: var(--shadow-md); }
.rp-card .form-control, .rp-card .form-select { border-radius: 12px; padding: 0.75rem 1rem; min-height: 3rem; }
.rp-card .form-label { font-weight: 500; font-size: var(--fs-sm); color: var(--ink-2); margin-bottom: var(--space-xs); }
.rp-card .form-label.req::after { content: ' *'; color: var(--danger); font-weight: 700; }
.rp-help { font-size: var(--fs-xs); color: var(--ink-4); margin-top: 4px; }
.rp-invalid { display: none; margin-top: 6px; font-size: var(--fs-xs); color: var(--danger-strong); }
.rp-invalid.is-show { display: block; }

.rp-asset-chip {
    display: flex; align-items: center; gap: var(--space-sm);
    border-radius: 12px; padding: var(--space-sm) var(--space-md);
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
    font-size: var(--fs-sm);
    margin-bottom: var(--space-md);
}
.rp-asset-chip-icon { width: 2rem; height: 2rem; border-radius: 10px; background: rgba(255,255,255,0.6); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rp-asset-chip-meta { flex: 1; min-width: 0; }
.rp-asset-chip-code { font-family: ui-monospace, Menlo, monospace; font-weight: 800; font-size: var(--fs-xs); }
.rp-asset-chip-name { font-weight: 700; }

.rp-scan-row { display: flex; gap: var(--space-xs); }
.rp-scan-row .form-control { flex: 1; min-width: 0; }
.rp-scan-btn {
    min-height: 3rem; border-radius: 12px;
    padding: 0 var(--space-md);
    background: var(--surface-2); color: var(--ink-2);
    border: 1.5px dashed var(--ink-line);
    display: inline-flex; align-items: center; gap: 6px;
    font-weight: 600; font-size: var(--fs-sm);
    text-decoration: none;
}
.rp-scan-btn:hover { color: var(--mobile-primary); border-color: var(--mobile-primary); background: var(--mobile-primary-soft); }
.rp-scan-btn svg { width: 1.05rem; height: 1.05rem; }

/* Photo picker — reuse pattern จาก bm- */
.rp-photo-controls { display: flex; gap: var(--space-xs); flex-wrap: wrap; }
.rp-photo-btn {
    flex: 1 1 auto; min-height: 3rem; border-radius: 12px;
    padding: 0 var(--space-md);
    background: var(--surface-2); border: 1.5px dashed var(--ink-line);
    color: var(--ink-2); font-weight: 600; font-size: var(--fs-sm);
    display: inline-flex; align-items: center; justify-content: center; gap: var(--space-xs);
    transition: border-color 200ms, background 200ms, color 200ms;
}
.rp-photo-btn:active, .rp-photo-btn:focus { border-color: var(--mobile-primary); background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.rp-photo-preview { display: flex; flex-wrap: wrap; gap: var(--space-xs); margin-top: var(--space-sm); }
.rp-photo-tile {
    position: relative; width: 4.75rem; height: 4.75rem;
    border-radius: 12px; overflow: hidden; background: var(--surface-2); flex-shrink: 0;
    box-shadow: var(--shadow-sm);
    animation: rp-tile-in 320ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes rp-tile-in { from { opacity: 0; transform: scale(0.88); } to { opacity: 1; transform: scale(1); } }
.rp-photo-tile img { width: 100%; height: 100%; object-fit: cover; display: block; }
.rp-photo-remove {
    position: absolute; top: 4px; right: 4px;
    width: 1.5rem; height: 1.5rem; border-radius: 50%;
    background: rgba(0,0,0,0.6); color: #fff; border: 0; padding: 0;
    display: flex; align-items: center; justify-content: center;
}
.rp-photo-remove svg { width: 0.85rem; height: 0.85rem; }

/* Step 4 summary */
.rp-summary { display: flex; flex-direction: column; gap: var(--space-xs); }
.rp-summary-row { display: flex; justify-content: space-between; gap: var(--space-md); padding: var(--space-xs) 0; border-bottom: 1px solid var(--ink-line); font-size: var(--fs-sm); }
.rp-summary-row:last-child { border-bottom: 0; }
.rp-summary-label { color: var(--ink-4); font-weight: 500; flex-shrink: 0; }
.rp-summary-value { color: var(--ink); font-weight: 700; text-align: right; word-break: break-word; }

.rp-confirm-row {
    display: flex; align-items: flex-start; gap: var(--space-sm);
    padding: var(--space-sm); margin: 0; border-radius: 12px;
    background: var(--surface-2);
    cursor: pointer;
}
.rp-confirm-row input[type="checkbox"] { flex-shrink: 0; width: 1.25rem; height: 1.25rem; margin-top: 2px; accent-color: var(--mobile-primary); }
.rp-confirm-text { font-size: var(--fs-sm); color: var(--ink); line-height: 1.5; }

.rp-step-error {
    display: none; margin-top: var(--space-sm);
    border-radius: 12px; background: var(--danger-soft); color: var(--danger-strong);
    padding: var(--space-xs) var(--space-sm);
    font-size: var(--fs-sm); line-height: 1.45;
}
.rp-step-error.is-visible { display: block; }

/* Action bar */
.rp-actions {
    position: fixed; left: 0; right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    background: var(--surface); padding: var(--space-md);
    box-shadow: 0 -4px 16px color-mix(in oklch, var(--ink) 8%, transparent);
    border-top: 1px solid var(--ink-line);
    z-index: 1031;
    display: grid; gap: var(--space-xs);
}
.rp-actions[data-step="1"] { grid-template-columns: 1fr; }
.rp-actions:not([data-step="1"]) { grid-template-columns: auto 1fr; }
.rp-actions .btn {
    min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    gap: var(--space-2xs);
    transition: opacity 200ms cubic-bezier(0.22, 1, 0.36, 1), background-color 180ms ease-out, transform 120ms ease-out;
}
.rp-actions .btn:active { transform: scale(0.985); }
.rp-actions .btn-prev { padding: 0 var(--space-md); background: var(--surface-2); border-color: var(--surface-2); color: var(--ink-2); }
.rp-actions[data-step="1"] #rp-prev { display: none; }
.rp-actions[data-step="4"] #rp-next { display: none; }
.rp-actions:not([data-step="4"]) #rp-submit { display: none; }
.rp-actions .btn[disabled] { opacity: 0.55; cursor: not-allowed; }

@media (prefers-reduced-motion: reduce) {
    .rp-wizard-fill, .rp-wizard-pip { transition: none !important; }
    .rp-panel.is-active { animation: none !important; }
    .rp-photo-tile, .rp-actions .btn { animation: none !important; transition: none !important; }
}
</style>

<div class="rp-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => 'wrench',
        'title'    => $this->params['mobileTitle'],
        'subtitle' => $this->params['mobileSubtitle'],
    ]) ?>

    <div class="app-scroll rp-scroll">

        <nav class="rp-wizard" aria-label="ขั้นตอนการแจ้งซ่อม">
            <div class="rp-wizard-track" role="progressbar" aria-valuemin="1" aria-valuemax="4" aria-valuenow="1">
                <div class="rp-wizard-fill" id="rp-fill"></div>
            </div>
            <ol class="rp-wizard-steps">
                <?php foreach ([1 => 'ครุภัณฑ์', 2 => 'รายละเอียด', 3 => 'ผู้แจ้ง', 4 => 'ยืนยัน'] as $n => $lbl): ?>
                    <li class="rp-wizard-step <?= $n === 1 ? 'is-active' : '' ?>" data-step="<?= $n ?>"
                        <?= $n === 1 ? ' aria-current="step"' : '' ?>>
                        <span class="rp-wizard-pip">
                            <span class="rp-wizard-pip-num"><?= $n ?></span>
                            <i data-lucide="check" class="rp-wizard-pip-check"></i>
                        </span>
                        <span><?= Html::encode($lbl) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger rounded-3 mx-3 mt-3 mb-0" role="alert">
                <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
            </div>
        <?php endif; ?>

        <div class="rp-body">

            <?php $form = ActiveForm::begin([
                'id'      => 'mobile-repair-form',
                'options' => ['enctype' => 'multipart/form-data', 'novalidate' => 'novalidate'],
            ]); ?>

            <!-- hidden carriers -->
            <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'name')->hiddenInput(['value' => 'repair'])->label(false) ?>

            <!-- ── Step 1: ครุภัณฑ์/อุปกรณ์ ── -->
            <section class="rp-panel is-active" data-step-panel="1">
                <header class="rp-panel-head">
                    <p class="rp-panel-eyebrow">ขั้นตอนที่ 1 จาก 4</p>
                    <h2 class="rp-panel-title">ข้อมูลครุภัณฑ์ที่จะแจ้งซ่อม</h2>
                    <p class="rp-panel-desc">ระบุรหัสครุภัณฑ์ (หรือสแกน QR) แล้วเลือกประเภทอุปกรณ์ + แผนกช่าง</p>
                </header>

                <div class="rp-card">
                    <?php if ($existingAssetName !== '' && $existingAsset !== ''): ?>
                        <div class="rp-asset-chip">
                            <span class="rp-asset-chip-icon" aria-hidden="true"><i data-lucide="package-check"></i></span>
                            <div class="rp-asset-chip-meta">
                                <div class="rp-asset-chip-name"><?= Html::encode($existingAssetName) ?></div>
                                <div class="rp-asset-chip-code"><?= Html::encode($existingAsset) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="rp-asset_number">รหัสครุภัณฑ์</label>
                        <div class="rp-scan-row">
                            <?= $form->field($model, 'asset_number', ['template' => '{input}'])->textInput([
                                'id' => 'rp-asset_number',
                                'class' => 'form-control',
                                'placeholder' => 'พิมพ์รหัสหรือสแกน QR',
                                'autocomplete' => 'off',
                                'value' => $existingAsset,
                            ])->label(false) ?>
                            <a href="<?= Html::encode($scanUrl) ?>" class="rp-scan-btn" aria-label="สแกน QR">
                                <i data-lucide="qr-code"></i>
                                สแกน
                            </a>
                        </div>
                        <p class="rp-help">หากเป็นปัญหาทั่วไปที่ไม่มีรหัส ปล่อยช่องนี้ว่างได้</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="rp-device_type">ประเภทอุปกรณ์</label>
                        <?= $form->field($model, 'device_type_id', ['template' => '{input}'])->dropDownList(
                            $deviceTypes,
                            ['id' => 'rp-device_type', 'class' => 'form-select', 'prompt' => 'เลือกประเภทอุปกรณ์']
                        )->label(false) ?>
                    </div>

                    <div class="mb-0">
                        <label class="form-label req" for="rp-repair_group">แผนกช่าง</label>
                        <?= $form->field($model, 'repair_group', ['template' => '{input}'])->dropDownList(
                            $repairGroups,
                            ['id' => 'rp-repair_group', 'class' => 'form-select', 'prompt' => 'เลือกแผนกช่าง', 'required' => true]
                        )->label(false) ?>
                        <p class="rp-help">ระบบจะเลือกอัตโนมัติจากรหัสครุภัณฑ์ แต่ปรับเปลี่ยนได้</p>
                        <div class="rp-invalid" data-invalid="repair_group"></div>
                    </div>
                </div>
                <div class="rp-step-error" role="alert" aria-live="polite" data-step-error="1"></div>
            </section>

            <!-- ── Step 2: รายละเอียดปัญหา ── -->
            <section class="rp-panel" data-step-panel="2" hidden>
                <header class="rp-panel-head">
                    <p class="rp-panel-eyebrow">ขั้นตอนที่ 2 จาก 4</p>
                    <h2 class="rp-panel-title">รายละเอียดปัญหา</h2>
                    <p class="rp-panel-desc">อธิบายอาการที่พบและความเร่งด่วน เพื่อให้ช่างประเมินงานได้เร็ว</p>
                </header>

                <div class="rp-card">
                    <div class="mb-3">
                        <label class="form-label req" for="rp-title">รายละเอียดปัญหา</label>
                        <?= $form->field($model, 'title', ['template' => '{input}'])->textarea([
                            'id' => 'rp-title',
                            'rows' => 4,
                            'placeholder' => 'อธิบายอาการ เช่น เปิดไม่ติด, มีเสียงผิดปกติ, รั่วซึม',
                            'required' => true,
                        ])->label(false) ?>
                        <div class="rp-invalid" data-invalid="title"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label req" for="rp-urgency">ความเร่งด่วน</label>
                        <select name="Helpdesk[data_json][urgency]" id="rp-urgency" class="form-select" required>
                            <option value="">เลือกความเร่งด่วน</option>
                            <?php foreach ($urgencyOpts as $code => $label): ?>
                                <option value="<?= Html::encode((string) $code) ?>"
                                    <?= ((string) $code === (string) ($existingDataJson['urgency'] ?? '')) ? 'selected' : '' ?>>
                                    <?= Html::encode($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="rp-invalid" data-invalid="urgency"></div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label" for="rp-date">วันที่ต้องการให้ซ่อม</label>
                        <?= $form->field($model, 'request_repair_date', ['template' => '{input}'])->widget(DatepickerThai::class, [
                            'options' => ['class' => 'form-control', 'id' => 'rp-date', 'placeholder' => 'ระบุวันที่ (ไม่บังคับ)', 'autocomplete' => 'off'],
                        ])->label(false) ?>
                    </div>
                </div>
                <div class="rp-step-error" role="alert" aria-live="polite" data-step-error="2"></div>
            </section>

            <!-- ── Step 3: ข้อมูลผู้แจ้ง ── -->
            <section class="rp-panel" data-step-panel="3" hidden>
                <header class="rp-panel-head">
                    <p class="rp-panel-eyebrow">ขั้นตอนที่ 3 จาก 4</p>
                    <h2 class="rp-panel-title">ข้อมูลผู้แจ้งและสถานที่</h2>
                    <p class="rp-panel-desc">ระบุสถานที่ตั้งและช่องทางติดต่อ พร้อมแนบรูปประกอบหากมี</p>
                </header>

                <div class="rp-card">
                    <div class="mb-3">
                        <label class="form-label req" for="rp-location">สถานที่</label>
                        <input type="text" id="rp-location" name="Helpdesk[data_json][location]" class="form-control"
                               placeholder="เช่น ห้อง 301, อาคาร A, แผนกบัญชี"
                               value="<?= Html::encode($prefillLocation) ?>" required>
                        <div class="rp-invalid" data-invalid="location"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="rp-phone">เบอร์โทรติดต่อ</label>
                        <input type="tel" id="rp-phone" name="Helpdesk[data_json][phone]" class="form-control"
                               placeholder="0XX-XXX-XXXX" inputmode="tel"
                               value="<?= Html::encode($prefillPhone) ?>">
                    </div>

                    <div class="mb-0">
                        <label class="form-label" for="rp-note">หมายเหตุเพิ่มเติม</label>
                        <textarea id="rp-note" name="Helpdesk[data_json][note]" class="form-control" rows="3"
                                  placeholder="ข้อมูลเพิ่มเติมที่อาจเป็นประโยชน์ต่อการซ่อม"><?= Html::encode((string) ($existingDataJson['note'] ?? '')) ?></textarea>
                    </div>
                </div>

                <div class="rp-card">
                    <label class="form-label">รูปภาพประกอบ</label>
                    <input type="file" name="photos[]" id="rp-photos" class="d-none" accept="image/*" multiple>
                    <input type="file" id="rp-photos-camera" class="d-none" accept="image/*" capture="environment" multiple>
                    <div class="rp-photo-controls">
                        <button type="button" class="rp-photo-btn" id="rp-btn-camera">
                            <i data-lucide="camera"></i>
                            <span>ถ่ายรูป</span>
                        </button>
                        <button type="button" class="rp-photo-btn" id="rp-btn-gallery">
                            <i data-lucide="image"></i>
                            <span>เลือกจากแกลเลอรี</span>
                        </button>
                    </div>
                    <div id="rp-photo-preview" class="rp-photo-preview"></div>
                </div>

                <div class="rp-step-error" role="alert" aria-live="polite" data-step-error="3"></div>
            </section>

            <!-- ── Step 4: ตรวจสอบและยืนยัน ── -->
            <section class="rp-panel" data-step-panel="4" hidden>
                <header class="rp-panel-head">
                    <p class="rp-panel-eyebrow">ขั้นตอนสุดท้าย</p>
                    <h2 class="rp-panel-title">ตรวจสอบและยืนยันการส่งซ่อม</h2>
                    <p class="rp-panel-desc">ตรวจสอบรายละเอียดก่อนส่งคำขอให้ทีมช่าง</p>
                </header>

                <div class="rp-card">
                    <div class="rp-summary" id="rp-summary">
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">รหัสครุภัณฑ์</span>
                            <span class="rp-summary-value" id="rp-sum-asset">-</span>
                        </div>
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">แผนกช่าง</span>
                            <span class="rp-summary-value" id="rp-sum-group">-</span>
                        </div>
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">ประเภทอุปกรณ์</span>
                            <span class="rp-summary-value" id="rp-sum-device">-</span>
                        </div>
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">รายละเอียดปัญหา</span>
                            <span class="rp-summary-value" id="rp-sum-title">-</span>
                        </div>
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">ความเร่งด่วน</span>
                            <span class="rp-summary-value" id="rp-sum-urgency">-</span>
                        </div>
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">สถานที่</span>
                            <span class="rp-summary-value" id="rp-sum-location">-</span>
                        </div>
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">วันที่ต้องการ</span>
                            <span class="rp-summary-value" id="rp-sum-date">-</span>
                        </div>
                        <div class="rp-summary-row">
                            <span class="rp-summary-label">เบอร์ติดต่อ</span>
                            <span class="rp-summary-value" id="rp-sum-phone">-</span>
                        </div>
                    </div>
                </div>

                <div class="rp-card">
                    <label class="rp-confirm-row" for="rp-confirm-chk">
                        <input type="checkbox" id="rp-confirm-chk">
                        <span class="rp-confirm-text">
                            ข้าพเจ้ายืนยันว่าข้อมูลที่กรอกถูกต้อง และต้องการส่งคำขอแจ้งซ่อมตามรายการนี้
                        </span>
                    </label>
                </div>

                <div class="rp-step-error" role="alert" aria-live="polite" data-step-error="4"></div>
            </section>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Action bar -->
    <div class="rp-actions" id="rp-actions" data-step="1">
        <button type="button" class="btn btn-prev" id="rp-prev">
            <i data-lucide="arrow-left"></i>
            <span>ย้อนกลับ</span>
        </button>
        <button type="button" class="btn btn-primary" id="rp-next">
            <span>ถัดไป</span>
            <i data-lucide="arrow-right"></i>
        </button>
        <button type="submit" class="btn btn-primary" id="rp-submit" form="mobile-repair-form" disabled>
            <i data-lucide="send"></i>
            <span>ส่งคำขอซ่อม</span>
        </button>
    </div>
</div>

<?php
\app\widgets\datepicker\Assets::register($this);
$this->registerJs("if (typeof thaiDatepicker === 'function') thaiDatepicker('#rp-date');", View::POS_END);

$repairGetGroupUrlJs = json_encode($repairGetGroupUrl);
$wizardJs = <<<JS
(function(){
    var TOTAL = 4, current = 1;
    var fill    = document.getElementById('rp-fill');
    var actions = document.getElementById('rp-actions');
    var panels  = document.querySelectorAll('.rp-panel');
    var steps   = document.querySelectorAll('.rp-wizard-step');
    var prevBtn = document.getElementById('rp-prev');
    var nextBtn = document.getElementById('rp-next');
    var submitBtn = document.getElementById('rp-submit');
    var confirmChk = document.getElementById('rp-confirm-chk');
    var form = document.getElementById('mobile-repair-form');

    function val(id) { var el = document.getElementById(id); return el ? (el.value || '').trim() : ''; }
    function selectText(id) {
        var el = document.getElementById(id);
        if (!el || el.selectedIndex < 0) return '';
        var opt = el.options[el.selectedIndex];
        return opt && opt.value ? opt.text : '';
    }
    function setStepError(n, msg) {
        var el = document.querySelector('[data-step-error="' + n + '"]');
        if (!el) return;
        el.textContent = msg || '';
        el.classList.toggle('is-visible', !!msg);
    }
    function clearStepErrors() {
        document.querySelectorAll('[data-step-error]').forEach(function(el){ el.textContent=''; el.classList.remove('is-visible'); });
    }
    function setFieldInvalid(name, msg) {
        var el = document.querySelector('[data-invalid="' + name + '"]');
        if (!el) return;
        el.textContent = msg || '';
        el.classList.toggle('is-show', !!msg);
    }
    function clearFieldInvalid() {
        document.querySelectorAll('[data-invalid]').forEach(function(el){ el.textContent=''; el.classList.remove('is-show'); });
    }

    function validateStep(n, show) {
        var msg = '';
        if (n === 1) {
            if (!val('rp-repair_group')) { msg = 'กรุณาเลือกแผนกช่าง'; if (show) setFieldInvalid('repair_group', msg); }
        } else if (n === 2) {
            if (!val('rp-title')) { msg = 'กรุณาระบุรายละเอียดปัญหา'; if (show) setFieldInvalid('title', msg); }
            else if (!val('rp-urgency')) { msg = 'กรุณาเลือกความเร่งด่วน'; if (show) setFieldInvalid('urgency', msg); }
        } else if (n === 3) {
            if (!val('rp-location')) { msg = 'กรุณาระบุสถานที่'; if (show) setFieldInvalid('location', msg); }
        } else if (n === 4) {
            if (!confirmChk || !confirmChk.checked) msg = 'กรุณาติ๊กยืนยันก่อนส่งคำขอ';
        }
        if (show) setStepError(n, msg);
        return !msg;
    }

    function refreshSummary() {
        var set = function(id, v){ var el = document.getElementById(id); if (el) el.textContent = (v && v.toString().trim() !== '') ? v : '-'; };
        set('rp-sum-asset',    val('rp-asset_number'));
        set('rp-sum-group',    selectText('rp-repair_group'));
        set('rp-sum-device',   selectText('rp-device_type'));
        set('rp-sum-title',    val('rp-title'));
        set('rp-sum-urgency',  selectText('rp-urgency'));
        set('rp-sum-location', val('rp-location'));
        set('rp-sum-date',     val('rp-date'));
        set('rp-sum-phone',    val('rp-phone'));
    }

    function updateActions() {
        if (nextBtn) nextBtn.disabled = !validateStep(current, false);
        if (submitBtn) submitBtn.disabled = !(confirmChk && confirmChk.checked);
    }

    function goTo(n) {
        n = Math.max(1, Math.min(TOTAL, n));
        current = n;
        panels.forEach(function(p){
            var isCurr = Number(p.dataset.stepPanel) === n;
            p.hidden = !isCurr;
            p.classList.toggle('is-active', isCurr);
        });
        steps.forEach(function(s){
            var sNum = Number(s.dataset.step);
            s.classList.toggle('is-active', sNum === n);
            s.classList.toggle('is-done', sNum < n);
            if (sNum === n) s.setAttribute('aria-current', 'step');
            else s.removeAttribute('aria-current');
        });
        var pct = (n / TOTAL * 100).toFixed(4);
        if (fill) fill.style.width = pct + '%';
        if (actions) actions.dataset.step = String(n);
        clearStepErrors();
        if (n === 4) refreshSummary();
        updateActions();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    if (prevBtn) prevBtn.addEventListener('click', function(){ goTo(current - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function(){
        clearFieldInvalid();
        if (validateStep(current, true)) goTo(current + 1);
    });
    if (confirmChk) confirmChk.addEventListener('change', updateActions);
    if (form) {
        form.addEventListener('input',  updateActions);
        form.addEventListener('change', updateActions);
    }

    // Auto-fill repair_group เมื่อกรอก asset_number (debounce)
    var assetInput = document.getElementById('rp-asset_number');
    var groupSelect = document.getElementById('rp-repair_group');
    var lookupTimer = null;
    if (assetInput && groupSelect) {
        assetInput.addEventListener('input', function(){
            var code = this.value.trim();
            if (!code) return;
            // ไม่ override ถ้าเลือกแล้ว
            if (groupSelect.value) return;
            clearTimeout(lookupTimer);
            lookupTimer = setTimeout(function(){
                var params = new URLSearchParams({ asset_number: code });
                fetch({$repairGetGroupUrlJs} + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r){ return r.json(); })
                    .then(function(res){
                        if (res && res.repair_group && !groupSelect.value) {
                            groupSelect.value = String(res.repair_group);
                            groupSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    })
                    .catch(function(){});
            }, 420);
        });
    }

    // Photo picker
    var camIn = document.getElementById('rp-photos-camera');
    var galIn = document.getElementById('rp-photos');
    var btnCam = document.getElementById('rp-btn-camera');
    var btnGal = document.getElementById('rp-btn-gallery');
    var prevEl = document.getElementById('rp-photo-preview');
    var files = [];
    function renderPhotos() {
        if (!prevEl) return;
        prevEl.innerHTML = '';
        files.forEach(function(f, i){
            var tile = document.createElement('div'); tile.className = 'rp-photo-tile';
            var img = document.createElement('img'); img.src = URL.createObjectURL(f); img.alt = '';
            var rm = document.createElement('button');
            rm.type = 'button'; rm.className = 'rp-photo-remove'; rm.setAttribute('aria-label', 'ลบรูปนี้');
            rm.innerHTML = '<i data-lucide="x"></i>';
            rm.addEventListener('click', function(){ files.splice(i,1); renderPhotos(); syncFiles(); });
            tile.appendChild(img); tile.appendChild(rm);
            prevEl.appendChild(tile);
        });
        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
    }
    function syncFiles() {
        if (typeof DataTransfer === 'undefined' || !galIn) return;
        var dt = new DataTransfer();
        files.forEach(function(f){ dt.items.add(f); });
        galIn.files = dt.files;
    }
    function addFiles(list) {
        for (var i=0;i<list.length;i++) if (list[i].type.indexOf('image/')===0) files.push(list[i]);
        renderPhotos(); syncFiles();
    }
    if (btnCam && camIn) {
        btnCam.addEventListener('click', function(){ camIn.click(); });
        camIn.addEventListener('change', function(){ if (this.files.length) addFiles([].slice.call(this.files)); this.value=''; });
    }
    if (btnGal && galIn) {
        btnGal.addEventListener('click', function(){ galIn.click(); });
        galIn.addEventListener('change', function(){ if (this.files.length) addFiles([].slice.call(this.files)); this.value=''; });
    }

    // Submit: bypass ActiveForm/Yii client-script interception ด้วย fetch+FormData ตรงๆ
    // เหตุผล: form.requestSubmit() เคยทำให้ Swal loading ค้างเพราะ ActiveForm hook beforeSubmit
    //          ดักไว้และไม่ปล่อยให้ native submit เกิดขึ้นจริง
    // Controller (actionRepairRequest) ตอบ JSON เมื่อเป็น AJAX อยู่แล้ว — เราจัดการ flow เอง
    function submitViaFetch() {
        var fd = new FormData(form);
        // เพิ่ม CSRF (Yii ใส่ใน meta tag _csrf)
        var csrfParamMeta = document.querySelector('meta[name="csrf-param"]');
        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfParamMeta && csrfTokenMeta && !fd.has(csrfParamMeta.getAttribute('content'))) {
            fd.append(csrfParamMeta.getAttribute('content'), csrfTokenMeta.getAttribute('content'));
        }
        var url = form.getAttribute('action') || window.location.pathname + window.location.search;

        fetch(url, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then(function(r){ return r.json().catch(function(){ return { status: 'error', message: 'รูปแบบข้อมูลที่ตอบกลับไม่ถูกต้อง' }; }); })
        .then(function(res){
            if (res && res.status === 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งคำขอซ่อมเรียบร้อย',
                        text: res.message || 'ระบบส่งคำขอให้ทีมช่างแล้ว',
                        timer: 1400,
                        showConfirmButton: false,
                    }).then(function(){
                        window.location.href = res.redirect_url || window.location.href;
                    });
                } else {
                    window.location.href = res.redirect_url || window.location.href;
                }
                return;
            }
            // error: แสดงข้อความ + ปลดล็อค submit
            if (submitBtn) submitBtn.disabled = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'ส่งคำขอไม่สำเร็จ',
                    text: (res && res.message) || 'กรุณาตรวจสอบข้อมูลแล้วลองใหม่',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#d33',
                });
            }
            // โชว์ field errors ถ้ามี
            if (res && res.errors && typeof res.errors === 'object') {
                Object.keys(res.errors).forEach(function(k){
                    setFieldInvalid(k, Array.isArray(res.errors[k]) ? res.errors[k][0] : res.errors[k]);
                });
            }
        })
        .catch(function(err){
            if (submitBtn) submitBtn.disabled = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'การเชื่อมต่อขัดข้อง',
                    text: 'ไม่สามารถติดต่อ server ได้ กรุณาลองใหม่',
                    confirmButtonText: 'รับทราบ',
                });
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e){
            // step ระหว่างทาง → เปลี่ยนเป็น "ถัดไป"
            if (current !== TOTAL) {
                e.preventDefault();
                if (validateStep(current, true)) goTo(current + 1);
                return;
            }
            // step สุดท้าย: validate + confirm + fetch
            e.preventDefault();
            if (!validateStep(TOTAL, true)) return;

            if (typeof Swal === 'undefined') {
                if (window.confirm('ยืนยันการส่งคำขอซ่อม?')) {
                    if (submitBtn) submitBtn.disabled = true;
                    submitViaFetch();
                }
                return;
            }
            Swal.fire({
                title: 'ยืนยันการส่งคำขอซ่อม?',
                text: 'โปรดตรวจสอบความถูกต้องก่อนส่งให้ทีมช่าง',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-paper-plane me-1"></i> ยืนยันส่งคำขอ',
                cancelButtonText: 'ยกเลิก',
            }).then(function(r){
                if (r.isConfirmed) {
                    if (submitBtn) submitBtn.disabled = true;
                    Swal.fire({
                        title: 'กำลังส่งคำขอซ่อม',
                        text: 'ระบบกำลังบันทึกและแจ้งทีมช่าง...',
                        allowOutsideClick: false,
                        didOpen: function(){ Swal.showLoading(); },
                    });
                    submitViaFetch();
                }
            });
        });
    }

    goTo(1);
})();
JS;
$this->registerJs($wizardJs, View::POS_END);
?>
