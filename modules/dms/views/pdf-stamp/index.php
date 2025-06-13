<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap4\Alert;

/* @var $this yii\web\View */
/* @var $model app\models\PdfStampForm */

$this->title = 'ระบบประทับตรา PDF';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
.upload-area {
    border: 2px dashed #007bff;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #0056b3;
    background: #e3f2fd;
}

.upload-area.dragover {
    border-color: #28a745;
    background: #d4edda;
}

.upload-icon {
    font-size: 48px;
    color: #007bff;
    margin-bottom: 15px;
}

.upload-text {
    font-size: 18px;
    color: #495057;
    margin-bottom: 10px;
}

.upload-hint {
    font-size: 14px;
    color: #6c757d;
}

.features-list {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.feature-icon {
    color: #28a745;
    margin-right: 10px;
    font-size: 18px;
}
');

$this->registerJs('
// Drag and drop functionality
$(document).ready(function() {
    var uploadArea = $(".upload-area");
    var fileInput = $("#pdfstampform-pdffile");
    
    uploadArea.on("click", function() {
        fileInput.click();
    });
    
    uploadArea.on("dragover", function(e) {
        e.preventDefault();
        $(this).addClass("dragover");
    });
    
    uploadArea.on("dragleave", function(e) {
        e.preventDefault();
        $(this).removeClass("dragover");
    });
    
    uploadArea.on("drop", function(e) {
        e.preventDefault();
        $(this).removeClass("dragover");
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            var file = files[0];
            if (file.type === "application/pdf") {
                fileInput[0].files = files;
                updateFileName(file.name);
            } else {
                alert("กรุณาเลือกไฟล์ PDF เท่านั้น");
            }
        }
    });
    
    fileInput.on("change", function() {
        if (this.files && this.files[0]) {
            updateFileName(this.files[0].name);
        }
    });
    
    function updateFileName(fileName) {
        $(".upload-text").text("ไฟล์ที่เลือก: " + fileName);
        $(".upload-hint").text("คลิกปุ่ม \"เริ่มต้น\" เพื่อดำเนินการต่อ");
        $(".btn-upload").prop("disabled", false);
    }
});
');
?>

<div class="pdf-stamp-index">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="display-4 text-primary">
                        <i class="fas fa-stamp"></i>
                        <?= Html::encode($this->title) ?>
                    </h1>
                    <p class="lead text-muted">
                        สร้างและจัดการตราประทับบนเอกสาร PDF ได้อย่างง่ายดาย
                    </p>
                </div>

                <!-- Upload Form -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'options' => ['enctype' => 'multipart/form-data'],
                            'fieldConfig' => [
                                'template' => '{input}{error}',
                            ],
                        ]); ?>

                        <div class="upload-area">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                คลิกหรือลากไฟล์ PDF มาวางที่นี่
                            </div>
                            <div class="upload-hint">
                                รองรับไฟล์ PDF ขนาดไม่เกิน 10MB
                            </div>
                            
                            <?= $form->field($model, 'pdfFile')->fileInput([
                                'id' => 'pdfstampform-pdffile',
                                'style' => 'display: none;',
                                'accept' => '.pdf'
                            ]) ?>
                        </div>

                        <div class="text-center mt-3">
                            <?= Html::submitButton(
                                '<i class="fas fa-play"></i> เริ่มต้น', 
                                [
                                    'class' => 'btn btn-primary btn-lg btn-upload',
                                    'disabled' => true
                                ]
                            ) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <!-- Features -->
                <div class="features-list">
                    <h4 class="text-center mb-3">
                        <i class="fas fa-star text-warning"></i>
                        คุณสมบัติเด่น
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <i class="fas fa-edit feature-icon"></i>
                                <span>แก้ไขข้อความได้อย่างอิสระ</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-palette feature-icon"></i>
                                <span>ปรับสีและขนาดตัวอักษร</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-mouse-pointer feature-icon"></i>
                                <span>ลากวางตำแหน่งได้ง่าย</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <i class="fas fa-redo-alt feature-icon"></i>
                                <span>หมุนตราประทับได้</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-border-style feature-icon"></i>
                                <span>เพิ่มกรอบและพื้นหลัง</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-download feature-icon"></i>
                                <span>ดาวน์โหลด PDF ที่ประทับแล้ว</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="alert alert-info mt-4">
                    <h5 class="alert-heading">
                        <i class="fas fa-info-circle"></i>
                        วิธีการใช้งาน
                    </h5>
                    <ol class="mb-0">
                        <li>อัพโหลดไฟล์ PDF ที่ต้องการประทับตรา</li>
                        <li>เลือกเทมเพลตตราประทับหรือสร้างใหม่</li>
                        <li>ปรับแต่งข้อความ สี และรูปแบบตามต้องการ</li>
                        <li>ลากวางตำแหน่งตราประทับ</li>
                        <li>ดาวน์โหลดไฟล์ PDF ที่ประทับตราแล้ว</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>