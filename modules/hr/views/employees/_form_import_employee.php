<?php
use yii\helpers\Html;
use app\models\Categorise;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

$this->title = 'นำเข้าไฟล์ CSV';
$listProductType = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type', 'category_id' => [4]])->all(), 'code', 'title');
?>

    <!-- Upload Form Section -->
    <div class="row justify-content-center">
        <div class="col-lg-12 col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php $form = ActiveForm::begin([
                        'options' => [
                            'enctype' => 'multipart/form-data', 
                            'id' => 'upload-form',
                            'class' => 'needs-validation'
                        ]
                    ]) ?>
                    
                    <!-- File Upload Section -->
                    <div class="row g-4 align-items-end">
                        <div class="col-md-6">
                            <label for="csvFile" class="form-label">
                                เลือกไฟล์นำเข้า
                            </label>
                            <div class="position-relative">
                                <?= Html::fileInput('csvFile', null, [
                                    'id' => 'csvFile',
                                    'class' => 'form-control',
                                    'accept' => '.xlsx,.csv',
                                ]) ?>
                                <div class="invalid-feedback">
                                    กรุณาเลือกไฟล์ .xlsx หรือ .csv
                                </div>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                รองรับไฟล์ .xlsx (แนะนำ) หรือ .csv
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?= Html::a(
                                '<i class="bi bi-file-earmark-arrow-down me-1"></i> ดาวน์โหลด Template (.xlsx)',
                                ['/hr/employees/import-template'],
                                ['class' => 'btn btn-outline-success', 'target' => '_blank']
                            ) ?>
                            <div class="form-text">
                                <i class="fas fa-lightbulb text-warning me-1"></i>
                                โหลด template ที่มี dropdown ตัวเลือกจากข้อมูลจริง + ตัวอย่าง 2 แถว แล้วกรอกก่อนอัปโหลด
                            </div>
                        </div>
                    </div>
                    
                    <?= Html::hiddenInput('order_id', $order_id ?? null, ['id' => 'order_id']) ?>
                    <?php ActiveForm::end() ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div id="preview-section" style="display: none;">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <div id="preview-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Button Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                <div id="import-btn" style="display:none;">
                    <button class="btn btn-success btn-lg px-5 py-3 shadow-sm" id="btn-import" type="button">
                        <i class="fas fa-file-import me-2"></i>
                        <span class="fw-semibold">ยืนยันนำเข้าข้อมูล</span>
                        <div class="spinner-border spinner-border-sm ms-2 d-none" id="import-spinner" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                    <?= Html::hiddenInput('filePath', null, ['id' => 'filePath']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Result Report -->
    <div class="row mt-4">
        <div class="col-12">
            <div id="import-report"></div>
        </div>
    </div>


<!-- Loading Overlay -->
<div id="loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.7); z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="text-center text-white">
            <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4>กำลังประมวลผล...</h4>
            <p class="mb-0">กรุณารอสักครู่</p>
        </div>
    </div>
</div>

<?php
$js = <<<JS
$(document).ready(function() {
    // Show loading function
    function showLoading() {
        $('#loading-overlay').removeClass('d-none');
    }
    
    // Hide loading function
    function hideLoading() {
        $('#loading-overlay').addClass('d-none');
    }
    
    // Show success toast
    function showSuccessToast(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            alert(message);
        }
    }
    
    // Show error toast
    function showErrorToast(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: message,
                timer: 3000,
                showConfirmButton: true,
                toast: true,
                position: 'top-end'
            });
        } else {
            alert(message);
        }
    }

    // แสดงรายงานผลการนำเข้า (สรุป + แถวที่ข้าม/ผิดพลาด)
    function renderImportReport(res) {
        var s = res.summary || {};
        var esc = function (t) { return $('<div>').text(t == null ? '' : t).html(); };
        var alertClass = res.status === 'success' ? 'alert-success' : 'alert-warning';

        var html = '<div class="alert ' + alertClass + ' border-0 shadow-sm">';
        html += '<h6 class="fw-semibold mb-2"><i class="fas fa-clipboard-check me-1"></i> ' + esc(res.message) + '</h6>';
        html += '<div class="d-flex flex-wrap gap-3 small">';
        html += '<span><i class="fas fa-user-plus text-success me-1"></i>เพิ่มใหม่ <strong>' + (s.inserted || 0) + '</strong></span>';
        html += '<span><i class="fas fa-user-edit text-primary me-1"></i>อัปเดต <strong>' + (s.updated || 0) + '</strong></span>';
        html += '<span><i class="fas fa-forward text-warning me-1"></i>ข้าม <strong>' + (s.skipped || 0) + '</strong></span>';
        html += '<span><i class="fas fa-triangle-exclamation text-danger me-1"></i>บันทึกไม่สำเร็จ <strong>' + (s.failed || 0) + '</strong></span>';
        html += '</div></div>';

        var rowErrors = res.rowErrors || [];
        var failures = res.failures || [];
        if (rowErrors.length || failures.length) {
            html += '<div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">';
            html += '<table class="table table-sm table-bordered mb-0"><thead class="table-light"><tr>';
            html += '<th class="text-center" style="width:90px">แถว</th><th class="text-center" style="width:150px">เลขบัตร</th><th>รายละเอียด</th></tr></thead><tbody>';
            rowErrors.forEach(function (e) {
                html += '<tr><td class="text-center">' + esc(e.row) + '</td><td class="text-center">' + esc(e.cid) + '</td><td class="text-danger small">' + esc((e.messages || []).join(' · ')) + '</td></tr>';
            });
            failures.forEach(function (f) {
                html += '<tr><td class="text-center">-</td><td class="text-center">' + esc(f.cid) + '</td><td class="text-danger small">บันทึกไม่สำเร็จ: ' + esc(f.message) + '</td></tr>';
            });
            html += '</tbody></table></div></div></div>';
        }

        $('#import-report').html(html);
    }

    // 1️⃣ AJAX preview with enhanced UI
    $('#csvFile').on('change', function() {
        var file = this.files[0];
        if(!file) return;
        
        // Validate file type
        var fn = file.name.toLowerCase();
        if (!fn.endsWith('.xlsx') && !fn.endsWith('.csv')) {
            showErrorToast('กรุณาเลือกไฟล์ .xlsx หรือ .csv');
            $(this).val('');
            return;
        }
        
        showLoading();
        var formData = new FormData();
        formData.append('csvFile', file);

        $.ajax({
            url: '/hr/employees/import-preview',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                hideLoading();
                if(res.status === 'success'){
                    // Expand modal if exists
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl");
                    $(".modal-dialog").addClass('modal-xxl');

                    // Create beautiful table
                    var html = '<table class="table table-hover table-striped table-bordered mb-0">';
                    html += '<thead>';
                    html += '<tr>';
                    res.preview[0].forEach(function(h){ 
                        html += '<th class="text-center">' + h + '</th>'; 
                    });
                    html += '</tr></thead>';
                    html += '<tbody class="table-group-divider">';
                    
                    res.preview.slice(1).forEach(function(row, index){
                        html += '<tr class="' + (index % 2 === 0 ? 'table-light' : '') + '">';
                        row.forEach(function(cell){ 
                            html += '<td class="text-center">' + (cell || '-') + '</td>'; 
                        });
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    
                    $('#preview-table').html(html);
                    $('#preview-section').slideDown();
                    $('#filePath').val(res.filePath);
                    $('#import-btn').fadeIn();
                    
                    // Scroll to preview
                    $('html, body').animate({
                        scrollTop: $("#preview-section").offset().top - 20
                    }, 1000);
                    
                } else {
                    showErrorToast(res.message || 'เกิดข้อผิดพลาดในการอัปโหลด');
                }
            },
            error: function(xhr, status, error){
                hideLoading();
                showErrorToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        });
    });

    // 2️⃣ AJAX import with enhanced UI
    $('#btn-import').on('click', function() {
        var filePath = $('#filePath').val();
        
        
        // Show confirmation
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการนำเข้าข้อมูล?',
                text: "ข้อมูลจะถูกนำเข้าสู่ระบบ ต้องการดำเนินการต่อหรือไม่?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    performImport();
                }
            });
        } else {
            if (confirm('ยืนยันการนำเข้าข้อมูล?')) {
                performImport();
            }
        }
        
        function performImport() {
            // Show loading state on button
            $('#import-spinner').removeClass('d-none');
            $('#btn-import').prop('disabled', true);
            showLoading();

            $.ajax({
                url: '/hr/employees/import-csv',
                type: 'POST',
                data: { 
                    filePath: filePath
                },
                success: function(res) {
                    hideLoading();
                    $('#import-spinner').addClass('d-none');
                    $('#btn-import').prop('disabled', false);
                    
                    if (res.status === 'success' || res.status === 'warning') {
                        renderImportReport(res);
                        if (res.status === 'success') {
                            showSuccessToast(res.message || 'นำเข้าข้อมูลสำเร็จ');
                        }
                        // reset ส่วนอัปโหลด (คงรายงานผลไว้ให้ผู้ใช้อ่าน)
                        $('#preview-table').html('');
                        $('#preview-section').slideUp();
                        $('#import-btn').fadeOut();
                        $('#csvFile').val('');
                        $('#filePath').val('');
                    } else {
                        showErrorToast(res.message || 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    $('#import-spinner').addClass('d-none');
                    $('#btn-import').prop('disabled', false);
                    showErrorToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            });
        }
    });
    
    // File input animation
    $('#csvFile').on('change', function() {
        if (this.files && this.files[0]) {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });
});
JS;

$this->registerJs($js);
?>

<style>
/* Custom CSS for enhanced styling */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3) !important;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
}

#preview-section {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Select2 Bootstrap 5 theme adjustments */
.select2-container--bootstrap-5 .select2-selection {
    min-height: calc(2.25rem + 2px);
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}
</style>