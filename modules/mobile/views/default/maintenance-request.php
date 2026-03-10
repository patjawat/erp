<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'แจ้งซ่อม';
$this->params['mobileSubtitle'] = 'แจ้งปัญหาอุปกรณ์หรือสถานที่ พร้อมแนบรูป';

$problemTypes = [
    ''  => '— เลือกประเภทปัญหา —',
    '1' => 'ไฟฟ้า / ไฟดับ',
    '2' => 'ประปา / น้ำรั่ว',
    '3' => 'เครื่องปรับอากาศ',
    '4' => 'อุปกรณ์สำนักงาน (เครื่องพิมพ์, คอมพิวเตอร์)',
    '5' => 'อาคาร / ผนัง / ประตู',
    '6' => 'อื่นๆ',
];
?>
<style>
.maint-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.maint-card .form-control,
.maint-card .form-select {
    border-radius: 12px;
    padding: 0.75rem 1rem;
}
.maint-card .form-label { font-weight: 500; }
.btn-maint-submit {
    border-radius: 12px;
    padding: 0.875rem 1.25rem;
    font-size: 1.0625rem;
    font-weight: 600;
}
.maint-btn-camera, .maint-btn-gallery {
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-weight: 500;
    border: 2px dashed #dee2e6;
    background: #fff;
    color: #6c757d;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.maint-btn-camera:hover, .maint-btn-gallery:hover {
    border-color: #5D5FEF;
    background: rgba(93, 95, 239, 0.06);
    color: #5D5FEF;
}
.maint-preview-list { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem; }
.maint-preview-item {
    position: relative;
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 12px;
    overflow: hidden;
    background: #f1f3f5;
    flex-shrink: 0;
}
.maint-preview-item img { width: 100%; height: 100%; object-fit: cover; }
.maint-preview-item .maint-preview-remove {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    color: #fff;
    border: 0;
    padding: 0;
    font-size: 0.75rem;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.maint-qr-section {
    border-radius: 12px;
    padding: 0.75rem 1rem;
    background: rgba(93, 95, 239, 0.06);
    border: 1px solid rgba(93, 95, 239, 0.15);
}
</style>

<div class="booking-header mb-3">
    <h1 class="h5 fw-semibold text-dark mb-0">แจ้งซ่อม</h1>
    <p class="small text-body-secondary mb-0">แจ้งปัญหาอุปกรณ์หรือสถานที่ พร้อมแนบรูป</p>
</div>

<?php $form = ActiveForm::begin([
    'id' => 'mobile-maintenance-form',
    'options' => ['class' => '', 'enctype' => 'multipart/form-data'],
]); ?>

<div class="card maint-card mb-3">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-medium">ประเภทปัญหา <span class="text-danger">*</span></label>
            <select name="problem_type" id="problem-type" class="form-select" required>
                <?php foreach ($problemTypes as $value => $label): ?>
                    <option value="<?= Html::encode($value) ?>"><?= Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">สถานที่ <span class="text-danger">*</span></label>
            <input type="text" name="location" class="form-control" placeholder="เช่น ชั้น 3 ห้อง 301, อาคาร A" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-medium">รายละเอียดปัญหา <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="3" placeholder="อธิบายอาการหรือความเสียหายที่พบ" required></textarea>
        </div>

        <!-- QR ครุภัณฑ์ (optional) -->
        <div class="mb-3 maint-qr-section">
            <label class="form-label fw-medium mb-2">ครุภัณฑ์ที่เกี่ยวข้อง (ไม่บังคับ)</label>
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" name="asset_code" id="asset-code" class="form-control flex-grow-1" style="min-width: 8rem;" placeholder="รหัสครุภัณฑ์">
                <a href="<?= Html::encode(Url::to(['/mobile/default/scan', 'return' => 'maintenance'])) ?>" class="btn btn-outline-primary d-flex align-items-center gap-1" style="border-radius: 12px;">
                    <i data-lucide="qr-code" style="width: 1.125rem; height: 1.125rem;"></i>
                    สแกน QR
                </a>
            </div>
        </div>

        <!-- แนบรูปภาพ -->
        <div class="mb-0">
            <label class="form-label fw-medium">แนบรูปภาพ</label>
            <input type="file" name="photos[]" id="maint-photos" class="d-none" accept="image/*" multiple>
            <input type="file" id="maint-photos-camera" class="d-none" accept="image/*" capture="environment" multiple>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn maint-btn-camera d-flex align-items-center gap-2" id="btn-camera">
                    <i data-lucide="camera" style="width: 1.25rem; height: 1.25rem;"></i>
                    ถ่ายรูป
                </button>
                <button type="button" class="btn maint-btn-gallery d-flex align-items-center gap-2" id="btn-gallery">
                    <i data-lucide="image" style="width: 1.25rem; height: 1.25rem;"></i>
                    เลือกจากแกลเลอรี
                </button>
            </div>
            <div id="maint-preview" class="maint-preview-list"></div>
        </div>
    </div>
</div>

<div class="d-grid mb-3">
    <button type="submit" class="btn btn-primary btn-maint-submit" name="action" value="submit">
        <i data-lucide="send" class="me-2" style="width: 1.25rem; height: 1.25rem; vertical-align: -0.3em;"></i>
        ส่งแจ้งซ่อม
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<'JS'
(function() {
    var cameraInput = document.getElementById('maint-photos-camera');
    var galleryInput = document.getElementById('maint-photos');
    var btnCamera = document.getElementById('btn-camera');
    var btnGallery = document.getElementById('btn-gallery');
    var previewEl = document.getElementById('maint-preview');

    var collectedFiles = [];

    function addFiles(files) {
        for (var i = 0; i < files.length; i++) {
            if (files[i].type.indexOf('image/') !== 0) continue;
            collectedFiles.push(files[i]);
        }
        renderPreview();
        syncHiddenInput();
    }
    function removeFile(index) {
        collectedFiles.splice(index, 1);
        renderPreview();
        syncHiddenInput();
    }
    function renderPreview() {
        previewEl.innerHTML = '';
        collectedFiles.forEach(function(file, i) {
            var div = document.createElement('div');
            div.className = 'maint-preview-item';
            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = '';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'maint-preview-remove';
            btn.setAttribute('aria-label', 'ลบรูป');
            btn.textContent = '×';
            btn.addEventListener('click', function() { removeFile(i); });
            div.appendChild(img);
            div.appendChild(btn);
            previewEl.appendChild(div);
        });
    }
    function syncHiddenInput() {
        var dt = new DataTransfer();
        collectedFiles.forEach(function(f) { dt.items.add(f); });
        if (galleryInput) galleryInput.files = dt.files;
    }

    if (btnCamera && cameraInput) {
        btnCamera.addEventListener('click', function() { cameraInput.click(); });
        cameraInput.addEventListener('change', function() {
            if (this.files.length) addFiles(Array.prototype.slice.call(this.files));
            this.value = '';
        });
    }
    if (btnGallery && galleryInput) {
        btnGallery.addEventListener('click', function() { galleryInput.click(); });
        galleryInput.addEventListener('change', function() {
            if (this.files.length) addFiles(Array.prototype.slice.call(this.files));
            this.value = '';
        });
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
