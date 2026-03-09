<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var bool $hasTemplate */
/** @var string|null $templateUrl */

$this->title = 'Template รายงานขอไปราชการ';
$this->params['breadcrumbs'][] = ['label' => 'อบรม/ประชุม/ดูงาน', 'url' => ['/hr/development/index']];
$this->params['breadcrumbs'][] = $this->title;

$uploadActionUrl = Url::to('/filemanager/uploads/upload-pdf');
$uploadUrlJs = json_encode($uploadActionUrl);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$csrfParamJs = json_encode($csrfParam);
$csrfTokenJs = json_encode($csrfToken);
$formName = 'form_development_pdf';
$ref = $model->ref;

$this->registerCss('
.lt-sidebar { position: sticky; top: 1rem; }
.lt-pdf-col { height: calc(100vh - 160px); min-height: 480px; }
.lt-dropzone { border: 2px dashed var(--bs-border-color); border-radius: .75rem; transition: border-color .2s, background-color .2s; cursor: pointer; }
.lt-dropzone:hover, .lt-dropzone.is-over { border-color: var(--bs-primary); background-color: var(--bs-primary-bg-subtle, #e9effd); }
');
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-file-earmark-pdf text-primary"></i>
    <h4 class="fw-medium text-body mb-0"><?= Html::encode($this->title) ?></h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => 'setting-template']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
    <?= Yii::$app->session->getFlash('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <?= Yii::$app->session->getFlash('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-2 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> รายการ', ['/hr/development/index'], ['class' => 'btn btn-outline-secondary btn-sm rounded-3']) ?>
            <h6 class="mb-0 small fw-semibold">
                <i class="bi bi-file-earmark-pdf me-1"></i> <?= Html::encode($this->title) ?>
                <?php if ($hasTemplate): ?>
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1 ms-1">มีไฟล์แล้ว</span>
                <?php else: ?>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1 ms-1">ยังไม่มีเทมเพลต</span>
                <?php endif; ?>
            </h6>
        </div>
        <?php if ($hasTemplate): ?>
        <div class="d-flex gap-2 align-items-center">
            <?= Html::a(
                '<i class="bi bi-geo-alt me-1"></i> กำหนดตำแหน่งข้อมูล',
                ['/hr/development/pdf-positions'],
                ['class' => 'btn btn-primary btn-sm rounded-3 fw-medium']
            ) ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($hasTemplate): ?>
    <!-- มีเทมเพลต: sidebar + PDF preview -->
    <div class="row g-0">
        <div class="col-12 col-md-3 border-end p-3">
            <div class="lt-sidebar">
                <p class="small fw-semibold text-body mb-2 d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-repeat text-primary"></i> เปลี่ยนเทมเพลต
                </p>
                <div id="lt-dropzone" class="lt-dropzone text-center py-3 px-2 mb-2">
                    <i class="bi bi-cloud-upload text-primary mb-1 d-block" style="font-size:1.5rem;"></i>
                    <p class="small text-muted mb-2" style="font-size:.75rem;">ลากไฟล์มาวางที่นี่ หรือ</p>
                    <label class="btn btn-outline-primary btn-sm rounded-3 mb-0 px-2 py-1" style="cursor:pointer;font-size:.78rem;">
                        <i class="bi bi-folder2-open me-1"></i> เลือกไฟล์
                        <input type="file" id="leave-pdf-file-input" accept=".pdf,application/pdf" style="display:none;">
                    </label>
                    <p id="leave-pdf-filename" class="small text-muted mt-1 mb-0 text-truncate" style="font-size:.72rem;">ยังไม่ได้เลือกไฟล์</p>
                </div>
                <button type="button" id="leave-pdf-upload-btn" class="btn btn-primary rounded-3 w-100 btn-sm" disabled>
                    <i class="bi bi-cloud-upload me-1"></i> อัปโหลดใหม่
                </button>
                <div id="leave-pdf-upload-msg" class="mt-2"></div>
                <hr class="my-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-2">
                    <p class="small text-primary fw-semibold mb-1" style="font-size:.75rem;"><i class="bi bi-lightbulb me-1"></i> template กลาง</p>
                    <ol class="text-muted mb-0 ps-3" style="font-size:.72rem;">
                        <li class="mb-1">อัปโหลด PDF ต้นแบบ</li>
                        <li class="mb-1">กด <strong>กำหนดตำแหน่งข้อมูล</strong> ลากวางฟิลด์</li>
                        <li>บันทึกตำแหน่งแล้วไปพิมพ์จากรายการไปราชการ</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-9 p-0 lt-pdf-col">
            <iframe src="<?= Html::encode($templateUrl) ?>#toolbar=0&navpanes=0&scrollbar=0"
                class="border-0 w-100 h-100 d-block" title="เทมเพลต PDF"></iframe>
        </div>
    </div>

    <?php else: ?>
    <!-- ยังไม่มีเทมเพลต: upload zone ใหญ่กลางจอ -->
    <div class="card-body p-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:72px;height:72px;">
                        <i class="bi bi-file-earmark-pdf text-primary fs-2"></i>
                    </div>
                    <h5 class="fw-semibold mb-1">อัปโหลดเทมเพลต PDF</h5>
                    <p class="text-muted small mb-0">สำหรับ <strong>ฟอร์มใบขอไปราชการ</strong></p>
                </div>
                <div id="lt-dropzone" class="lt-dropzone text-center py-5 px-4 mb-3">
                    <i class="bi bi-cloud-upload fs-1 text-primary mb-3 d-block"></i>
                    <p class="fw-medium mb-1">ลากไฟล์ PDF มาวางที่นี่</p>
                    <p class="small text-muted mb-3">หรือ</p>
                    <label class="btn btn-outline-primary rounded-3 mb-0" style="cursor:pointer;">
                        <i class="bi bi-folder2-open me-1"></i> เลือกไฟล์จากเครื่อง
                        <input type="file" id="leave-pdf-file-input" accept=".pdf,application/pdf" style="display:none;">
                    </label>
                    <p id="leave-pdf-filename" class="small text-muted mt-3 mb-0 text-truncate">ยังไม่ได้เลือกไฟล์</p>
                </div>
                <button type="button" id="leave-pdf-upload-btn" class="btn btn-primary rounded-3 w-100" disabled>
                    <i class="bi bi-cloud-upload me-1"></i> อัปโหลดเทมเพลต
                </button>
                <div id="leave-pdf-upload-msg" class="mt-2"></div>
                <p class="text-muted small text-center mt-3 mb-0">รองรับเฉพาะไฟล์ <code>.pdf</code></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
(function(){
    var uploadUrl = <?= $uploadUrlJs ?>;
    var csrfParam = <?= $csrfParamJs ?>;
    var csrfToken = <?= $csrfTokenJs ?>;
    var fileInput = document.getElementById('leave-pdf-file-input');
    var uploadBtn = document.getElementById('leave-pdf-upload-btn');
    var filenameEl = document.getElementById('leave-pdf-filename');
    var msgEl = document.getElementById('leave-pdf-upload-msg');
    var dropzone = document.getElementById('lt-dropzone');

    if (dropzone) {
        ['dragenter','dragover'].forEach(function(ev){ dropzone.addEventListener(ev, function(e){ e.preventDefault(); dropzone.classList.add('is-over'); }); });
        ['dragleave','dragend','drop'].forEach(function(ev){ dropzone.addEventListener(ev, function(){ dropzone.classList.remove('is-over'); }); });
        dropzone.addEventListener('drop', function(e){
            e.preventDefault();
            var files = e.dataTransfer && e.dataTransfer.files;
            if (files && files[0] && (files[0].type === 'application/pdf' || files[0].name && files[0].name.toLowerCase().endsWith('.pdf'))) {
                filenameEl.textContent = files[0].name;
                if (uploadBtn) uploadBtn.disabled = false;
                if (fileInput) fileInput._droppedFile = files[0];
            }
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function(){
            if (fileInput.files && fileInput.files[0]) { filenameEl.textContent = fileInput.files[0].name; if (uploadBtn) uploadBtn.disabled = false; fileInput._droppedFile = null; }
            else { filenameEl.textContent = 'ยังไม่ได้เลือกไฟล์'; if (uploadBtn) uploadBtn.disabled = true; }
        });
    }

    if (uploadBtn) {
        uploadBtn.addEventListener('click', function(){
            var theFile = (fileInput && fileInput._droppedFile) || (fileInput && fileInput.files && fileInput.files[0]);
            if (!theFile) return;
            var fd = new FormData();
            fd.append('<?= $formName ?>', theFile);
            fd.append('id', '1');
            fd.append('ref', '<?= $ref ?>');
            fd.append('name', '<?= $formName ?>');
            fd.append(csrfParam, csrfToken);
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังอัปโหลด...';
            if (msgEl) msgEl.innerHTML = '';
            var xhr = new window.XMLHttpRequest();
            xhr.open('POST', uploadUrl, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function(){
                uploadBtn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i> อัปโหลด<?= $hasTemplate ? 'ใหม่' : 'เทมเพลต' ?>';
                uploadBtn.disabled = false;
                if (xhr.status === 200) {
                    try {
                        var j = JSON.parse(xhr.responseText);
                        if (j && (j.success === true || j.status === 'success')) {
                            if (msgEl) msgEl.innerHTML = '<div class="alert alert-success py-2 px-3 small mt-2"><i class="bi bi-check-circle me-1"></i>อัปโหลดเรียบร้อย กำลังโหลดใหม่...</div>';
                            setTimeout(function(){ window.location.reload(); }, 800);
                            return;
                        }
                        if (j && (j.error || j.message) && msgEl) { msgEl.innerHTML = '<div class="alert alert-danger py-2 px-3 small mt-2">' + (j.error || j.message) + '</div>'; return; }
                    } catch(e) {}
                    window.location.reload();
                } else if (msgEl) {
                    msgEl.innerHTML = '<div class="alert alert-danger py-2 px-3 small mt-2">เกิดข้อผิดพลาด HTTP ' + xhr.status + '</div>';
                }
            };
            xhr.onerror = function(){ uploadBtn.disabled = false; uploadBtn.innerHTML = '<i class="bi bi-cloud-upload me-1"></i> อัปโหลด'; if (msgEl) msgEl.innerHTML = '<div class="alert alert-danger py-2 px-3 small mt-2">เกิดข้อผิดพลาด</div>'; };
            xhr.send(fd);
        });
    }
})();
</script>
