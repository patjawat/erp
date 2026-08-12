<?php
use yii\helpers\Html;
use app\models\Categorise;
use kartik\widgets\Select2;
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
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="csvFile" class="form-label">
                                เลือกไฟล์ CSV
                            </label>
                            <div class="position-relative">
                                <?= Html::fileInput('csvFile', null, [
                                    'id' => 'csvFile',
                                    'class' => 'form-control',
                                    'accept' => '.csv',
                                ]) ?>
                                <div class="invalid-feedback">
                                    กรุณาเลือกไฟล์ CSV
                                </div>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle text-info me-1"></i>
                                รองรับไฟล์ .csv เท่านั้น
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                ประเภทวัสดุ
                            </label>
                            <?= $form->field($model, 'category_id')->widget(Select2::class, [
                                'data' => $listProductType,
                                'options' => [
                                    'id' => 'category_id',
                                    'placeholder' => 'กรุณาเลือกประเภทสินทรัพย์',
                                    'class' => 'form-select-lg'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'theme' => 'bootstrap-5',
                                    'dropdownParent' => '#main-modal',
                                ],
                                'pluginEvents' => [
                                    "select2:unselect" => "function() { $(this).submit(); }",
                                    'select2:select' => "function(result) { 
                                        var data = $(this).select2('data')[0].text;
                                        $('#order-data_json-product_type_name').val(data);
                                    }",
                                ]
                            ])->label(false) ?>
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
                        <i class="bi bi-box-arrow-in-down me-2"></i>
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

    // 1️⃣ AJAX preview with enhanced UI
    $('#csvFile').on('change', function() {
        var file = this.files[0];
        if(!file) return;
        
        // Validate file type
        if (!file.name.toLowerCase().endsWith('.csv')) {
            showErrorToast('กรุณาเลือกไฟล์ .csv เท่านั้น');
            $(this).val('');
            return;
        }
        
        showLoading();
        var formData = new FormData();
        formData.append('csvFile', file);

        $.ajax({
            url: '/inventory/import/preview',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                hideLoading();
                if(res.status === 'success'){
                    // Expand modal if exists
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl");
                    $(".modal-dialog").addClass('modal-xl');

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
                        html += '<tr class="' + (index % 2 === 0 ? 'bg-body-tertiary' : '') + '">';
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
        var orderId = $('#order_id').val();
        var categoryId = $('#category_id').val();
        
        if(categoryId == ''){
             showErrorToast('ประเภทวัสดุ');
            return; 
        }
        
        if(!filePath) { 
            showErrorToast('ไม่พบไฟล์ที่จะนำเข้า');
            return; 
        }
        
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
                url: '/sm/import-product/import-csv',
                type: 'POST',
                data: { 
                    filePath: filePath, 
                    category_id: categoryId
                },
                success: function(res) {
                    hideLoading();
                    $('#import-spinner').addClass('d-none');
                    $('#btn-import').prop('disabled', false);
                    
                    if(res.status === 'success'){
                        showSuccessToast(res.message || 'นำเข้าข้อมูลสำเร็จ');
                        
                        // Reset form
                        setTimeout(() => {
                            $('#preview-table').html('');
                            $('#preview-section').slideUp();
                            $('#import-btn').fadeOut();
                            $('#csvFile').val('');
                            $('#filePath').val('');
                        }, 1500);
                        
                        // Reload page after success message
                        // setTimeout(() => {
                        //     window.location.reload(true);
                        // }, 2500);
                        
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