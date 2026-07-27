<?php

use app\components\ThaiDateHelper;
use app\modules\booking\models\VehicleDetail;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var VehicleDetail $mission */
/** @var array $saveErrors */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'บันทึกภารกิจขับรถ';
$this->params['mobileSubtitle'] = 'ลงเวลา เลขไมล์ และผลการเดินทาง';

$saveErrors = $saveErrors ?? [];
$vehicle = $mission->vehicle;
$location = (string) ($vehicle?->locationOrg?->title ?? $vehicle?->location ?? '-');
$reason = (string) ($vehicle?->reason ?? '');
$requestCode = (string) ($vehicle?->code ?? $mission->vehicle_id);
$plate = (string) ($mission->license_plate ?? '');
$requester = (string) ($vehicle?->employee?->fullname ?? '-');
$companions = $vehicle ? $vehicle->companions() : ['items' => [], 'count' => 0];
$dateText = '-';
try {
    $dateText = $mission->date_start ? ThaiDateHelper::formatThaiDate($mission->date_start, 'short') : '-';
} catch (\Throwable $e) {
    $dateText = (string) ($mission->date_start ?: '-');
}

$statusOptions = [
    'Pending' => 'รอดำเนินการ',
    'Approve' => 'อนุมัติแล้ว',
    'Pass' => 'ได้รับมอบหมาย',
    'Success' => 'เสร็จสิ้นภารกิจ',
    'Cancel' => 'ยกเลิก',
];
?>

<style>
.dmu-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 6rem);
}
.dmu-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}
.dmu-summary,
.dmu-card {
    border-radius: 16px;
    border: 1px solid var(--ink-line);
    background: var(--surface);
    box-shadow: var(--shadow-sm);
}
.dmu-summary {
    padding: var(--space-md);
    display: flex;
    gap: var(--space-sm);
    align-items: flex-start;
}
.dmu-medal {
    width: 3rem;
    height: 3rem;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--cat-vehicle-bg);
    color: var(--cat-vehicle-fg);
}
.dmu-medal svg {
    width: 1.375rem;
    height: 1.375rem;
}
.dmu-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 700;
    line-height: 1.25;
    text-wrap: pretty;
}
.dmu-sub {
    margin: var(--space-2xs) 0 0;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.45;
}
.dmu-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2xs) var(--space-sm);
    margin-top: var(--space-sm);
    color: var(--ink-3);
    font-size: var(--fs-xs);
}
.dmu-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.dmu-meta svg {
    width: 0.875rem;
    height: 0.875rem;
    color: var(--ink-4);
}
.dmu-card {
    padding: var(--space-md);
}
.dmu-card-title {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    margin: 0 0 var(--space-sm);
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
}
.dmu-card-title svg {
    width: 1rem;
    height: 1rem;
    color: var(--mobile-primary);
}
.dmu-card-lead {
    margin: calc(-1 * var(--space-2xs)) 0 var(--space-sm);
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.45;
}
.dmu-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-sm);
}
.dmu-people {
    display: flex;
    flex-direction: column;
}
.dmu-person {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-xs) 0;
    border-bottom: 1px solid var(--ink-line);
}
.dmu-person:first-child { padding-top: 0; }
.dmu-person:last-child { border-bottom: 0; padding-bottom: 0; }
.dmu-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 999px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid var(--ink-line);
    background: var(--surface-2);
}
.dmu-avatar.is-guest {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-4);
}
.dmu-avatar.is-guest svg { width: 1.15rem; height: 1.15rem; }
.dmu-person-body { min-width: 0; flex: 1; }
.dmu-person-name {
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}
.dmu-person-role {
    margin-top: 1px;
    color: var(--ink-4);
    font-size: var(--fs-xs);
    font-weight: 600;
}
.dmu-count {
    margin-left: auto;
    color: var(--ink-4);
    font-size: var(--fs-sm);
    font-weight: 600;
}
.dmu-card .form-label {
    color: var(--ink-2);
    font-size: var(--fs-sm);
    font-weight: 600;
    margin-bottom: var(--space-xs);
}
.dmu-card .form-control,
.dmu-card .form-select {
    min-height: 2.875rem;
    border-radius: 12px;
    border-color: #dbe3ef;
    color: var(--ink);
    font-size: var(--fs-md);
}
.dmu-card .form-control:focus,
.dmu-card .form-select:focus {
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 3px var(--mobile-primary-soft);
}
.dmu-error {
    border-radius: 12px;
    border: 1px solid var(--danger-soft);
    background: var(--danger-soft);
    color: var(--danger-strong);
    padding: var(--space-sm);
    font-size: var(--fs-sm);
}
.dmu-upload-card {
    overflow: hidden;
}
.dmu-upload-head {
    display: flex;
    align-items: flex-start;
    gap: var(--space-sm);
    margin-bottom: var(--space-sm);
}
.dmu-upload-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}
.dmu-upload-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}
.dmu-upload-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
    line-height: 1.25;
}
.dmu-upload-desc {
    margin: 2px 0 0;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.4;
}
.dmu-upload-chips {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2xs);
    margin-bottom: var(--space-sm);
}
.dmu-upload-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    min-height: 2rem;
    padding: 0 var(--space-xs);
    border-radius: 999px;
    background: var(--surface-2);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 600;
}
.dmu-upload-chip svg {
    width: 0.875rem;
    height: 0.875rem;
    color: var(--mobile-primary);
}
.dmu-upload {
    border-radius: 16px;
    border: 1px dashed color-mix(in oklch, var(--mobile-primary) 34%, var(--ink-line));
    background: color-mix(in oklch, var(--mobile-primary) 5%, var(--surface));
    padding: var(--space-sm);
    transition:
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1),
        background 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.dmu-upload.is-uploading {
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 3px var(--mobile-primary-soft);
}
.dmu-upload.is-ready {
    border-style: solid;
    border-color: var(--success-soft);
    background: color-mix(in oklch, var(--success) 6%, var(--surface));
}
.dmu-upload.is-error {
    border-style: solid;
    border-color: var(--danger-soft);
    background: color-mix(in oklch, var(--danger-strong) 6%, var(--surface));
}
.dmu-upload-cue {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-sm);
    border-radius: 14px;
    background: var(--surface);
    border: 1px solid var(--ink-line);
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}
.dmu-upload-cue svg {
    width: 1.375rem;
    height: 1.375rem;
    color: var(--mobile-primary);
    flex-shrink: 0;
}
.dmu-upload-cue strong {
    display: block;
    color: var(--ink);
    font-size: var(--fs-sm);
    line-height: 1.25;
}
.dmu-upload-cue span {
    display: block;
    margin-top: 2px;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.35;
}
.dmu-upload-widget {
    margin-top: var(--space-sm);
}
.dmu-upload .file-input {
    width: 100%;
}
.dmu-upload .file-preview {
    border: 0;
    border-radius: 14px;
    background: var(--surface);
    padding: var(--space-xs);
    margin-bottom: var(--space-xs);
}
.dmu-upload .file-drop-zone {
    border: 0;
    margin: 0;
    min-height: 0;
}
.dmu-upload .file-caption {
    min-height: 2.75rem;
    border-radius: 12px;
}
.dmu-upload .input-group {
    gap: var(--space-xs);
}
.dmu-upload .input-group > .form-control,
.dmu-upload .input-group > .btn {
    border-radius: 12px !important;
}
.dmu-upload .btn,
.dmu-upload .fileinput-upload-button,
.dmu-upload .fileinput-remove-button,
.dmu-upload .btn-file {
    min-height: 2.75rem;
    border-radius: 12px;
    font-size: var(--fs-sm);
    font-weight: 600;
}
.dmu-upload .btn-file {
    background: var(--mobile-primary);
    border-color: var(--mobile-primary);
    color: #fff;
}
.dmu-upload .krajee-default.file-preview-frame {
    border-radius: 12px;
    box-shadow: none;
    border-color: var(--ink-line);
}
.dmu-upload-status {
    margin: var(--space-xs) var(--space-2xs) 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.35;
}
.dmu-actions {
    position: sticky;
    bottom: 0;
    z-index: var(--z-sticky);
    margin: var(--space-md) calc(-1 * var(--space-md)) 0;
    padding: var(--space-sm) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + var(--space-sm));
    background: var(--surface);
    border-top: 1px solid var(--ink-line);
    box-shadow: 0 -4px 16px color-mix(in oklch, var(--ink) 8%, transparent);
    display: grid;
    grid-template-columns: 0.45fr 1fr;
    gap: var(--space-xs);
}
.dmu-actions .btn {
    min-height: 3rem;
    border-radius: 12px;
    font-size: var(--fs-md);
    font-weight: 600;
}
@media (max-width: 359.98px) {
    .dmu-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon' => 'clipboard-pen-line',
    'title' => $this->params['mobileTitle'],
    'subtitle' => $this->params['mobileSubtitle'],
]) ?>

<div class="app-scroll dmu-scroll">
    <div class="dmu-stack">
        <?php if (!empty($saveErrors)): ?>
            <div class="dmu-error">
                <?= Html::encode(reset($saveErrors)) ?>
            </div>
        <?php endif; ?>

        <section class="dmu-summary" aria-label="สรุปภารกิจ">
            <span class="dmu-medal" aria-hidden="true"><i data-lucide="route"></i></span>
            <div class="min-w-0">
                <h2 class="dmu-title"><?= Html::encode($location) ?></h2>
                <?php if ($reason !== ''): ?>
                    <p class="dmu-sub"><?= Html::encode($reason) ?></p>
                <?php endif; ?>
                <div class="dmu-meta">
                    <span><i data-lucide="hash" aria-hidden="true"></i><?= Html::encode($requestCode) ?></span>
                    <span><i data-lucide="calendar" aria-hidden="true"></i><?= Html::encode($dateText) ?></span>
                    <?php if ($plate !== ''): ?>
                        <span><i data-lucide="car" aria-hidden="true"></i><?= Html::encode($plate) ?></span>
                    <?php endif; ?>
                    <span><i data-lucide="user" aria-hidden="true"></i><?= Html::encode($requester) ?></span>
                </div>
            </div>
        </section>

        <?php if (($companions['count'] ?? 0) > 0): ?>
            <section class="dmu-card" aria-label="ผู้ร่วมเดินทาง">
                <h3 class="dmu-card-title">
                    <i data-lucide="users" aria-hidden="true"></i> ผู้ร่วมเดินทาง
                    <span class="dmu-count"><?= (int) $companions['count'] ?> คน</span>
                </h3>
                <div class="dmu-people">
                    <?php foreach ($companions['items'] as $companion): ?>
                        <?php $emp = $companion['employee']; ?>
                        <div class="dmu-person">
                            <?php if ($emp): ?>
                                <img class="dmu-avatar" src="<?= Html::encode($emp->showAvatar()) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <span class="dmu-avatar is-guest" aria-hidden="true"><i data-lucide="user"></i></span>
                            <?php endif; ?>
                            <div class="dmu-person-body">
                                <div class="dmu-person-name"><?= Html::encode($companion['name']) ?></div>
                                <div class="dmu-person-role"><?= Html::encode($emp ? $emp->positionName() : 'บุคคลภายนอก') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'driver-mission-form']); ?>

        <section class="dmu-card">
            <h3 class="dmu-card-title"><i data-lucide="calendar-clock" aria-hidden="true"></i> เวลาเดินทาง</h3>
            <div class="dmu-grid">
                <?= $form->field($mission, 'date_start')->textInput(['placeholder' => 'วัน/เดือน/พ.ศ.'])->label('วันออกเดินทาง') ?>
                <?= $form->field($mission, 'time_start')->input('time')->label('เวลาออก') ?>
                <?= $form->field($mission, 'date_end')->textInput(['placeholder' => 'วัน/เดือน/พ.ศ.'])->label('วันกลับ') ?>
                <?= $form->field($mission, 'time_end')->input('time')->label('เวลากลับ') ?>
            </div>
        </section>

        <section class="dmu-card">
            <h3 class="dmu-card-title"><i data-lucide="gauge" aria-hidden="true"></i> เลขไมล์และน้ำมัน</h3>
            <div class="dmu-grid">
                <?= $form->field($mission, 'mileage_start')->input('number', ['step' => '0.01', 'inputmode' => 'decimal'])->label('เลขไมล์ก่อนออก') ?>
                <?= $form->field($mission, 'mileage_end')->input('number', ['step' => '0.01', 'inputmode' => 'decimal'])->label('เลขไมล์หลังกลับ') ?>
                <?= $form->field($mission, 'distance_km')->input('number', ['step' => '0.01', 'inputmode' => 'decimal'])->label('ระยะทาง (กม.)') ?>
                <?= $form->field($mission, 'oil_liter')->input('number', ['step' => '0.01', 'inputmode' => 'decimal'])->label('น้ำมัน (ลิตร)') ?>
                <?= $form->field($mission, 'oil_price')->input('number', ['step' => '0.01', 'inputmode' => 'decimal'])->label('ค่าน้ำมัน (บาท)') ?>
                <?= $form->field($mission, 'status')->dropDownList($statusOptions)->label('สถานะภารกิจ') ?>
            </div>
        </section>

        <?= $form->field($mission, 'ref')->hiddenInput()->label(false) ?>

        <section class="dmu-card dmu-upload-card">
            <div class="dmu-upload-head">
                <span class="dmu-upload-icon" aria-hidden="true"><i data-lucide="receipt-text"></i></span>
                <div class="min-w-0">
                    <h3 class="dmu-upload-title">บิลค่าใช้จ่ายและเอกสาร</h3>
                    <p class="dmu-upload-desc">แนบรูปถ่ายใบเสร็จ, PDF หรือเอกสารประกอบภารกิจ</p>
                </div>
            </div>
            <div class="dmu-upload-chips" aria-label="ชนิดไฟล์ที่รองรับ">
                <span class="dmu-upload-chip"><i data-lucide="camera" aria-hidden="true"></i>รูปถ่ายบิล</span>
                <span class="dmu-upload-chip"><i data-lucide="file-text" aria-hidden="true"></i>PDF</span>
                <span class="dmu-upload-chip"><i data-lucide="files" aria-hidden="true"></i>เอกสารอื่น</span>
            </div>
            <div class="dmu-upload" id="driver-mission-upload">
                <div class="dmu-upload-cue" role="button" tabindex="0" aria-label="เลือกไฟล์บิลค่าใช้จ่ายและเอกสาร">
                    <i data-lucide="cloud-upload" aria-hidden="true"></i>
                    <span>
                        <strong>แตะเพื่อเลือกไฟล์หรือถ่ายรูป</strong>
                        <span>เลือกได้หลายไฟล์ ระบบจะอัปโหลดให้อัตโนมัติหลังเลือกไฟล์</span>
                    </span>
                </div>
                <div class="dmu-upload-widget">
                    <?= $mission->Upload() ?>
                </div>
                <p class="dmu-upload-status" data-upload-status>ยังไม่มีไฟล์แนบ ไฟล์แนบไม่บังคับ</p>
            </div>
        </section>

        <div class="dmu-actions">
            <a href="<?= Html::encode(Url::to(['/mobile/default/driver-missions'])) ?>" class="btn btn-light d-inline-flex align-items-center justify-content-center gap-1">
                <i data-lucide="arrow-left" class="mi-sm" aria-hidden="true"></i>
                กลับ
            </a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1">
                <i data-lucide="save" class="mi-sm" aria-hidden="true"></i>
                บันทึกภารกิจ
            </button>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    var form = document.getElementById('driver-mission-form');
    var start = document.getElementById('vehicledetail-mileage_start');
    var end = document.getElementById('vehicledetail-mileage_end');
    var distance = document.getElementById('vehicledetail-distance_km');
    var uploadShell = document.getElementById('driver-mission-upload');
    var uploadStatus = uploadShell ? uploadShell.querySelector('[data-upload-status]') : null;
    var uploading = false;

    function calculateDistance() {
        if (!start || !end || !distance) return;
        var s = parseFloat(start.value);
        var e = parseFloat(end.value);
        if (Number.isNaN(s) || Number.isNaN(e)) return;
        distance.value = Math.max(0, e - s).toFixed(2);
    }

    if (start && end) {
        start.addEventListener('input', calculateDistance);
        end.addEventListener('input', calculateDistance);
    }

    function setUploadState(state, text) {
        if (!uploadShell) return;
        uploadShell.classList.remove('is-uploading', 'is-ready', 'is-error');
        if (state) uploadShell.classList.add('is-' + state);
        if (uploadStatus && text) uploadStatus.textContent = text;
    }

    function updateFileSummary() {
        if (!uploadShell) return;
        var frames = uploadShell.querySelectorAll('.file-preview-frame').length;
        if (frames > 0) {
            setUploadState('ready', 'แนบไฟล์แล้ว ' + frames + ' รายการ สามารถบันทึกภารกิจได้');
        } else {
            setUploadState('', 'ยังไม่มีไฟล์แนบ ไฟล์แนบไม่บังคับ');
        }
    }

    if (uploadShell && window.jQuery) {
        var $uploadInput = window.jQuery(uploadShell).find('input[type="file"]').first();
        var cue = uploadShell.querySelector('.dmu-upload-cue');

        $uploadInput.attr('accept', 'image/*,.pdf,.doc,.docx,.xls,.xlsx');

        if (cue && $uploadInput.length) {
            cue.addEventListener('click', function () {
                $uploadInput.trigger('click');
            });
            cue.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $uploadInput.trigger('click');
                }
            });
        }

        $uploadInput
            .on('filebatchselected.driverMission', function () {
                uploading = true;
                setUploadState('uploading', 'กำลังอัปโหลดไฟล์แนบ กรุณารอสักครู่');
                if (typeof $uploadInput.fileinput === 'function') {
                    $uploadInput.fileinput('upload');
                } else {
                    uploading = false;
                    updateFileSummary();
                }
            })
            .on('filebatchuploadcomplete.driverMission filedeleted.driverMission filecleared.driverMission', function () {
                uploading = false;
                window.setTimeout(updateFileSummary, 120);
            })
            .on('fileuploaderror.driverMission filebatchuploaderror.driverMission', function () {
                uploading = false;
                setUploadState('error', 'อัปโหลดไฟล์ไม่สำเร็จ ลองเลือกไฟล์อีกครั้ง');
            });

        window.setTimeout(updateFileSummary, 200);
    }

    if (window.jQuery && typeof handleFormSubmit === 'function') {
        window.jQuery(document)
            .off('beforeSubmit.driverMissionGuard', '#driver-mission-form')
            .on('beforeSubmit.driverMissionGuard', '#driver-mission-form', function (event) {
                if (!uploading) return true;
                event.preventDefault();
                event.stopImmediatePropagation();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'กำลังอัปโหลดไฟล์',
                        text: 'รอให้ไฟล์แนบอัปโหลดเสร็จก่อนบันทึกภารกิจ',
                        confirmButtonText: 'รับทราบ',
                        confirmButtonColor: '#0d6efd'
                    });
                }
                return false;
            });
        handleFormSubmit('#driver-mission-form', null, function () {});
    } else if (form) {
        form.addEventListener('submit', function (event) {
            if (uploading) {
                event.preventDefault();
                window.alert('รอให้ไฟล์แนบอัปโหลดเสร็จก่อนบันทึกภารกิจ');
                return;
            }
            if (!window.confirm('ยืนยันการบันทึกภารกิจขับรถ?')) {
                event.preventDefault();
            }
        });
    }
})();
JS);
?>
