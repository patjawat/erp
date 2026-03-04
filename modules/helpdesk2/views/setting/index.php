<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\helpdesk2\models\RepairFormSetting;

/** @var yii\web\View $this */
/** @var bool $hasTemplate */
/** @var string|null $templateUrl */

$this->title = 'แบบฟอร์มใบส่งซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานซ่อม', 'url' => ['/helpdesk/service/index']];
$this->params['breadcrumbs'][] = $this->title;

$csrfParam  = Yii::$app->request->csrfParam;
$csrfToken  = Yii::$app->request->csrfToken;
$deleteUrl  = Url::to(['/helpdesk/setting/delete-template']);
$uploadUrl  = Url::to(['/helpdesk/setting/upload-template']);
$toggleUrl  = Url::to(['/helpdesk/setting/toggle-enabled']);
$isEnabled  = RepairFormSetting::isEnabled();
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-file-earmark-pdf text-primary"></i>
    <h4 class="fw-medium text-body mb-0"><?= Html::encode($this->title) ?></h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/helpdesk2/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
    <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('warning')): ?>
<div class="alert alert-warning alert-dismissible fade show mb-3">
    <?= Html::encode(Yii::$app->session->getFlash('warning')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div id="upload-alert" class="alert d-none mb-3"></div>

<!-- ── การเปิด/ปิดใช้งานแบบฟอร์ม ── -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0
                            <?= $isEnabled ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' ?>"
                     style="width:44px;height:44px;">
                    <i class="bi bi-printer fs-5"></i>
                </div>
                <div>
                    <div class="fw-semibold small">เปิดใช้งานแบบฟอร์มใบส่งซ่อม</div>
                    <div class="text-muted" style="font-size:.75rem;" id="toggle-status-text">
                        <?= $isEnabled
                            ? '<span class="text-success">เปิดใช้งานอยู่ — ปุ่มพิมพ์ใบส่งซ่อมจะแสดงในหน้าจัดการงานซ่อม</span>'
                            : '<span class="text-muted">ปิดอยู่ — ปุ่มพิมพ์ใบส่งซ่อมจะไม่แสดง</span>' ?>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge <?= $isEnabled
                    ? 'bg-success bg-opacity-10 text-success border border-success-subtle'
                    : 'bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle' ?> rounded-pill fw-medium px-2 py-1"
                      id="toggle-badge">
                    <?= $isEnabled ? '<i class="bi bi-check-circle me-1"></i>เปิดใช้งาน' : '<i class="bi bi-dash-circle me-1"></i>ปิดอยู่' ?>
                </span>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="toggle-form-enabled"
                           style="width:3rem;height:1.5rem;cursor:pointer;"
                           <?= $isEnabled ? 'checked' : '' ?>
                           <?= !$hasTemplate ? 'disabled title="กรุณาอัปโหลด Template ก่อน"' : '' ?>>
                </div>
            </div>
        </div>
        <?php if (!$hasTemplate): ?>
        <div class="mt-3 p-2 rounded-3 bg-warning-subtle border border-warning-subtle d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle text-warning flex-shrink-0"></i>
            <span class="small text-warning-emphasis">ต้องอัปโหลด Template PDF ก่อนจึงจะเปิดใช้งานได้</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">

    <!-- ── คอลัมน์ซ้าย: อัปโหลด template ── -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 rounded-top-3">
                <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-upload"></i> อัปโหลด Template PDF
                </h6>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">
                    อัปโหลดแบบฟอร์มใบส่งซ่อม (ไฟล์ PDF) เพื่อใช้เป็น template พิมพ์ใบส่งซ่อม
                    จากนั้นกำหนดตำแหน่งข้อมูลบนหน้า PDF
                </p>

                <!-- สถานะ template ปัจจุบัน -->
                <div class="mb-4 p-3 rounded-3 border <?= $hasTemplate ? 'border-success' : 'border-warning' ?> bg-<?= $hasTemplate ? 'success' : 'warning' ?>-subtle">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-3 <?= $hasTemplate ? 'text-success' : 'text-warning' ?>">
                            <i class="bi bi-<?= $hasTemplate ? 'file-earmark-check' : 'file-earmark-x' ?>"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small <?= $hasTemplate ? 'text-success' : 'text-warning' ?>">
                                <?= $hasTemplate ? 'มี Template อยู่แล้ว' : 'ยังไม่มี Template' ?>
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">
                                <?= $hasTemplate
                                    ? 'สามารถกำหนดตำแหน่งข้อมูลบน PDF ได้เลย หรืออัปโหลดใหม่เพื่อแทนที่'
                                    : 'กรุณาอัปโหลดไฟล์ PDF เพื่อเริ่มต้น' ?>
                            </div>
                        </div>
                        <?php if ($hasTemplate): ?>
                        <div class="ms-auto">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">
                                <i class="bi bi-check-circle me-1"></i>พร้อมใช้งาน
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Drop zone อัปโหลด -->
                <div id="drop-zone"
                     class="border border-2 border-dashed rounded-3 p-4 text-center mb-3"
                     style="border-color: #adb5bd !important; cursor: pointer; transition: all .2s;">
                    <i class="bi bi-cloud-upload fs-2 text-muted mb-2 d-block"></i>
                    <p class="small text-muted mb-1">ลากไฟล์ PDF มาวาง หรือ</p>
                    <label for="pdf-file-input" class="btn btn-outline-primary btn-sm rounded-3 mb-2" style="cursor:pointer;">
                        <i class="bi bi-folder2-open me-1"></i> เลือกไฟล์
                    </label>
                    <input type="file" id="pdf-file-input" accept=".pdf,application/pdf" class="d-none">
                    <p class="text-muted mb-0" style="font-size:.75rem;" id="selected-file-name">รองรับเฉพาะ PDF เท่านั้น</p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" id="btn-upload" class="btn btn-primary rounded-3" disabled>
                        <i class="bi bi-cloud-upload me-1"></i> อัปโหลด
                    </button>
                    <?php if ($hasTemplate): ?>
                    <?= Html::a(
                        '<i class="bi bi-geo-alt me-1"></i> กำหนดตำแหน่ง',
                        ['/helpdesk/setting/positions'],
                        ['class' => 'btn btn-outline-primary rounded-3']
                    ) ?>
                    <button type="button" id="btn-delete-template" class="btn btn-outline-danger rounded-3">
                        <i class="bi bi-trash me-1"></i> ลบ Template
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── คอลัมน์ขวา: Preview template ── -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 rounded-top-3">
                <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-eye"></i> Preview Template
                </h6>
            </div>
            <div class="card-body p-3">
                <?php if ($hasTemplate): ?>
                <div class="rounded-3 overflow-hidden border" style="height: 500px;">
                    <iframe src="<?= Html::encode($templateUrl) ?>#toolbar=0"
                            class="w-100 h-100 border-0"
                            id="template-preview"
                            title="Preview แบบฟอร์มใบส่งซ่อม"></iframe>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-muted rounded-3 border"
                     style="height: 500px; background: #f8f9fa;">
                    <i class="bi bi-file-earmark-pdf fs-1 mb-3 opacity-25"></i>
                    <p class="small mb-0">ยังไม่มี Template — อัปโหลดไฟล์ PDF ทางซ้าย</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- ── ขั้นตอนการใช้งาน ── -->
<div class="card border-0 shadow-sm rounded-3 mt-4">
    <div class="card-body p-4">
        <h6 class="fw-semibold text-body mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-list-ol text-primary"></i> ขั้นตอนการตั้งค่า
        </h6>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="d-flex gap-3 align-items-start">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px; height:36px; font-size:.85rem; font-weight:700;">1</div>
                    <div>
                        <div class="fw-semibold small">อัปโหลด Template PDF</div>
                        <div class="text-muted" style="font-size:.75rem;">อัปโหลดแบบฟอร์มใบส่งซ่อม (เปล่า) ในรูปแบบ PDF</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="d-flex gap-3 align-items-start">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px; height:36px; font-size:.85rem; font-weight:700;">2</div>
                    <div>
                        <div class="fw-semibold small">กำหนดตำแหน่งข้อมูล</div>
                        <div class="text-muted" style="font-size:.75rem;">ลากฟิลด์ข้อมูลไปวางบน PDF ให้ตรงกับช่องในแบบฟอร์ม</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="d-flex gap-3 align-items-start">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px; height:36px; font-size:.85rem; font-weight:700;">3</div>
                    <div>
                        <div class="fw-semibold small">พิมพ์ใบส่งซ่อม</div>
                        <div class="text-muted" style="font-size:.75rem;">กดพิมพ์ที่หน้ารายละเอียดงานซ่อมได้ทันที</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
(function() {
    var dropZone     = document.getElementById('drop-zone');
    var fileInput    = document.getElementById('pdf-file-input');
    var uploadBtn    = document.getElementById('btn-upload');
    var deleteBtn    = document.getElementById('btn-delete-template');
    var fileNameEl   = document.getElementById('selected-file-name');
    var alertEl      = document.getElementById('upload-alert');
    var selectedFile = null;

    function showAlert(type, msg) {
        alertEl.className = 'alert alert-' + type + ' alert-dismissible fade show mb-3';
        alertEl.innerHTML = msg + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>';
    }

    function setFile(file) {
        if (!file) return;
        selectedFile = file;
        fileNameEl.textContent = file.name;
        uploadBtn.disabled = false;
        dropZone.style.borderColor = '#0d6efd';
        dropZone.style.background  = 'rgba(13,110,253,.05)';
    }

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) setFile(this.files[0]);
    });

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#0d6efd';
        this.style.background  = 'rgba(13,110,253,.05)';
    });
    dropZone.addEventListener('dragleave', function() {
        if (!selectedFile) {
            this.style.borderColor = '#adb5bd';
            this.style.background  = '';
        }
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        var file = e.dataTransfer.files && e.dataTransfer.files[0];
        if (file && file.type === 'application/pdf') {
            setFile(file);
        } else {
            showAlert('danger', 'รองรับเฉพาะไฟล์ PDF เท่านั้น');
        }
    });

    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            if (!selectedFile) return;
            var formData = new FormData();
            formData.append('template_pdf', selectedFile);
            formData.append('{$csrfParam}', '{$csrfToken}');
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังอัปโหลด...';
            fetch('{$uploadUrl}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    showAlert('success', res.message || 'อัปโหลดเรียบร้อย');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    showAlert('danger', res.error || res.message || 'อัปโหลดไม่สำเร็จ');
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i> อัปโหลด';
                }
            })
            .catch(function() {
                showAlert('danger', 'เกิดข้อผิดพลาด กรุณาลองใหม่');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i> อัปโหลด';
            });
        });
    }

    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('ต้องการลบ Template PDF ใช่หรือไม่?')) return;
            deleteBtn.disabled = true;
            fetch('{$deleteUrl}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ '{$csrfParam}': '{$csrfToken}' })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    showAlert('success', res.message || 'ลบเรียบร้อย');
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    showAlert('danger', res.error || 'ลบไม่สำเร็จ');
                    deleteBtn.disabled = false;
                }
            })
            .catch(function() {
                showAlert('danger', 'เกิดข้อผิดพลาด');
                deleteBtn.disabled = false;
            });
        });
    }

    // ── Toggle เปิด/ปิดแบบฟอร์ม ──
    var toggleSwitch = document.getElementById('toggle-form-enabled');
    var toggleBadge  = document.getElementById('toggle-badge');
    var toggleText   = document.getElementById('toggle-status-text');
    if (toggleSwitch) {
        toggleSwitch.addEventListener('change', function() {
            toggleSwitch.disabled = true;
            fetch('{$toggleUrl}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ '{$csrfParam}': '{$csrfToken}' })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    var on = res.enabled === 1;
                    toggleSwitch.checked = on;
                    if (toggleBadge) {
                        toggleBadge.className = on
                            ? 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1'
                            : 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1';
                        toggleBadge.innerHTML = on
                            ? '<i class="bi bi-check-circle me-1"></i>เปิดใช้งาน'
                            : '<i class="bi bi-dash-circle me-1"></i>ปิดอยู่';
                    }
                    if (toggleText) {
                        toggleText.innerHTML = on
                            ? '<span class="text-success">เปิดใช้งานอยู่ — ปุ่มพิมพ์ใบส่งซ่อมจะแสดงในหน้าจัดการงานซ่อม</span>'
                            : '<span class="text-muted">ปิดอยู่ — ปุ่มพิมพ์ใบส่งซ่อมจะไม่แสดง</span>';
                    }
                    var iconEl = toggleSwitch.closest('.card-body').querySelector('.rounded-3.d-flex');
                    if (iconEl) {
                        iconEl.className = 'rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 '
                            + (on ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary');
                    }
                } else {
                    toggleSwitch.checked = !toggleSwitch.checked;
                    showAlert('danger', 'เกิดข้อผิดพลาด กรุณาลองใหม่');
                }
            })
            .catch(function() {
                toggleSwitch.checked = !toggleSwitch.checked;
                showAlert('danger', 'เกิดข้อผิดพลาด กรุณาลองใหม่');
            })
            .finally(function() { toggleSwitch.disabled = false; });
        });
    }
})();
JS
);
?>
