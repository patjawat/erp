<?php
use yii\helpers\Html;
use yii\bootstrap4\Modal;
use yii\widgets\ActiveForm;
use app\widgets\StampPreviewWidget;
use app\modules\dms\models\Documents;

/* @var $this yii\web\View */
/* @var $model app\models\Document */

$this->title = 'อัพโหลดเอกสารและเพิ่มตราประทับ';
$this->params['breadcrumbs'][] = ['label' => 'เอกสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// FontAwesome โหลดจาก AppAsset (self-hosted FA7) แล้ว — ไม่ต้องเรียก CDN ซ้ำ
$this->registerCssFile('@web/libs/font-awesome/fontawesome-free-7.1.0-web/css/all.min.css');
?>

<div class="document-upload">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title"><?= Html::encode($this->title) ?></h1>
                <p class="text-muted">อัพโหลดไฟล์ PDF และเพิ่มตราประทับรับเอกสารอัตโนมัติ</p>
            </div>
            <div class="col-auto">
                <?= Html::a('<i class="fas fa-arrow-left"></i> กลับหน้าหลัก', ['index'], [
                    'class' => 'btn btn-outline-secondary'
                ]) ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- ฟอร์มอัพโหลด -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload"></i> ข้อมูลเอกสาร
                    </h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'id' => 'upload-form',
                        'options' => [
                            'enctype' => 'multipart/form-data',
                            'class' => 'needs-validation',
                            'novalidate' => true
                        ],
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{error}\n{hint}",
                            'labelOptions' => ['class' => 'form-label fw-bold'],
                            'inputOptions' => ['class' => 'form-control'],
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            'hintOptions' => ['class' => 'form-text text-muted'],
                        ],
                    ]); ?>

                    <!-- การอัพโหลดไฟล์ -->
                    <div class="row">
                        <div class="col-12">
                            <div class="upload-area mb-4" id="upload-area">
                                <?= $form->field($model, 'pdf_file')->fileInput([
                                    'accept' => '.pdf',
                                    'class' => 'form-control form-control-lg',
                                    'id' => 'pdf-file-input',
                                    'required' => true
                                ])->label('<i class="fas fa-file-pdf text-danger"></i> ไฟล์ PDF')->hint('เลือกไฟล์ PDF ขนาดไม่เกิน 10 MB') ?>
                                
                                <!-- แสดงข้อมูลไฟล์ -->
                                <div id="file-info" class="mt-2" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="file-details"></span>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div id="upload-progress" class="mt-2" style="display: none;">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อมูลเอกสาร -->
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'document_number')->textInput([
                                'placeholder' => 'เช่น 2568/001 (ปล่อยว่างเพื่อสร้างอัตโนมัติ)',
                                'maxlength' => 50
                            ])->hint('หากไม่ระบุ ระบบจะสร้างเลขที่เอกสารอัตโนมัติ') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'title')->textInput([
                                'placeholder' => 'หัวเรื่องเอกสาร',
                                'maxlength' => 500
                            ]) ?>
                        </div>
                    </div>

                    <!-- ข้อมูลหน่วยงาน -->
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'department')->textInput([
                                'value' => $model->department ?: 'กรมการสาธารณสุข',
                                'placeholder' => 'ชื่อหน่วยงาน',
                                'maxlength' => 200
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'office')->textInput([
                                'value' => $model->office ?: 'กรุงเทพมหานคร',
                                'placeholder' => 'สำนักงาน/จังหวัด',
                                'maxlength' => 200
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'phone')->textInput([
                                'value' => $model->phone ?: '0-2000-0000',
                                'placeholder' => 'หมายเลขโทรศัพท์',
                                'maxlength' => 20
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ตัวอย่างตราประทับ</label>
                            <br>
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#stamp-preview-modal">
                                <i class="fas fa-eye"></i> ดูตัวอย่างตราประทับ
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="update-preview">
                                <i class="fas fa-refresh"></i> อัพเดทตัวอย่าง
                            </button>
                        </div>
                    </div>

                    <!-- ปุ่มส่งฟอร์ม -->
                    <div class="form-group mt-4">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], [
                                'class' => 'btn btn-outline-secondary me-md-2'
                            ]) ?>
                            <?= Html::submitButton('<i class="fas fa-upload"></i> อัพโหลดและเพิ่มตราประทับ', [
                                'class' => 'btn btn-primary btn-lg',
                                'id' => 'submit-btn',
                                'data-loading-text' => '<i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...'
                            ]) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <!-- แผงข้อมูลด้านข้าง -->
        <div class="col-md-4">
            <!-- คำแนะนำ -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> คำแนะนำการใช้งาน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0">
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            <small>ไฟล์ต้องเป็น PDF เท่านั้น</small>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="fas fa-weight-hanging text-warning me-2"></i>
                            <small>ขนาดไฟล์ไม่เกิน 10 MB</small>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="fas fa-stamp text-primary me-2"></i>
                            <small>ตราประทับจะแสดงที่มุมขวาบนของหน้าแรก</small>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="fas fa-hashtag text-success me-2"></i>
                            <small>เลขที่เอกสารจะสร้างอัตโนมัติหากไม่ระบุ</small>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <i class="fas fa-clock text-info me-2"></i>
                            <small>วันที่และเวลารับจะเป็นเวลาปัจจุบัน</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- สถิติการใช้งาน -->
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> สถิติการใช้งาน
                    </h6>
                </div>
                <div class="card-body">
                    <?php
                    $totalDocs = Documents::find()->count();
                    $todayDocs = Documents::find()
                        ->where(['>=', 'created_at', date('Y-m-d 00:00:00')])
                        ->count();
                    $monthDocs = Documents::find()
                        ->where(['>=', 'created_at', date('Y-m-01 00:00:00')])
                        ->count();
                    ?>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <div class="stat-number text-primary"><?= number_format($totalDocs) ?></div>
                                <div class="stat-label">รวมทั้งหมด</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <div class="stat-number text-success"><?= number_format($monthDocs) ?></div>
                                <div class="stat-label">เดือนนี้</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <div class="stat-number text-info"><?= number_format($todayDocs) ?></div>
                                <div class="stat-label">วันนี้</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Custom CSS -->
<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.upload-area.dragover {
    border-color: #28a745;
    background-color: #d4edda;
}

.stat-item {
    padding: 10px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
}

.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.page-title {
    margin-bottom: 0.5rem;
    color: #495057;
}

.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.btn {
    border-radius: 6px;
}

.form-control {
    border-radius: 6px;
}
</style>

<!-- JavaScript -->
<?php
$this->registerJs("
// ตัวแปรสำหรับเก็บข้อมูล
let selectedFile = null;

// Drag and Drop functionality
const uploadArea = document.getElementById('upload-area');
const fileInput = document.getElementById('pdf-file-input');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect(files[0]);
    }
});

// File input change event
fileInput.addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        handleFileSelect(e.target.files[0]);
    }
});

// Function to handle file selection
function handleFileSelect(file) {
    selectedFile = file;
    
    // ตรวจสอบไฟล์
    if (!validateFile(file)) {
        return;
    }
    
    // แสดงข้อมูลไฟล์
    displayFileInfo(file);
}

// Function to validate file
function validateFile(file) {
    const fileInfo = document.getElementById('file-info');
    const fileDetails = document.getElementById('file-details');
    
    // ตรวจสอบประเภทไฟล์
    if (file.type !== 'application/pdf') {
        fileDetails.innerHTML = '❌ กรุณาเลือกไฟล์ PDF เท่านั้น';
        fileInfo.className = 'mt-2 alert alert-danger';
        fileInfo.style.display = 'block';
        fileInput.value = '';
        return false;
    }
    
    // ตรวจสอบขนาดไฟล์ (10 MB)
    if (file.size > 10 * 1024 * 1024) {
        fileDetails.innerHTML = '❌ ไฟล์มีขนาดใหญ่เกิน 10 MB (' + formatFileSize(file.size) + ')';
        fileInfo.className = 'mt-2 alert alert-danger';
        fileInfo.style.display = 'block';
        fileInput.value = '';
        return false;
    }
    
    return true;
}

// Function to display file information
function displayFileInfo(file) {
    const fileInfo = document.getElementById('file-info');
    const fileDetails = document.getElementById('file-details');
    
    fileDetails.innerHTML = `
        ✅ <strong>\${file.name}</strong><br>
        📁 ขนาด: \${formatFileSize(file.size)}<br>
        📅 แก้ไขล่าสุด: \${new Date(file.lastModified).toLocaleString('th-TH')}
    `;
    
    fileInfo.className = 'mt-2 alert alert-success';
    fileInfo.style.display = 'block';
}

// Function to format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form submission
$('#upload-form').on('submit', function(e) {
    const submitBtn = $('#submit-btn');
    
    // ตรวจสอบไฟล์
    if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('กรุณาเลือกไฟล์ PDF');
        return false;
    }
    
    // แสดง loading state
    submitBtn.prop('disabled', true);
    const originalText = submitBtn.html();
    submitBtn.html(submitBtn.data('loading-text'));
    
    // แสดง progress bar
    $('#upload-progress').show();
    
    // Simulate progress (เนื่องจากเป็นการส่งฟอร์มปกติ)
    let progress = 0;
    const progressInterval = setInterval(function() {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        $('#upload-progress .progress-bar').css('width', progress + '%');
    }, 200);
    
    // หยุด interval หลังจาก 5 วินาที (ในกรณีที่ไม่ redirect)
    setTimeout(function() {
        clearInterval(progressInterval);
        $('#upload-progress .progress-bar').css('width', '100%');
    }, 5000);
});

// Update preview function
$('#update-preview').on('click', function() {
    updateStampPreview();
});

// Function to update stamp preview
function updateStampPreview() {
    const docNumber = $('#document-document_number').val() || '2568/001';
    const department = $('#document-department').val() || 'กรมการสาธารณสุข';
    const office = $('#document-office').val() || 'กรุงเทพมหานคร';
    const phone = $('#document-phone').val() || '0-2000-0000';
    
    // อัพเดทข้อมูลใน modal
    $('#preview-doc-number').text(docNumber);
    $('#preview-department').text(department);
    
    // TODO: อัพเดท widget ตัวอย่าง (ต้องใช้ AJAX)
    console.log('Preview updated with:', {docNumber, department, office, phone});
}

// Auto-update preview when form fields change
$('#document-document_number, #document-department, #document-office, #document-phone').on('input', function() {
    updateStampPreview();
});

// Bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

console.log('Upload form initialized successfully');
");
?>
