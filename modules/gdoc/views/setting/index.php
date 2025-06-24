<?php
use yii\helpers\Url;
use yii\helpers\Html;
$this->title = "Google API Configuration";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('_sub_menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>



<div class="row justify-content-center">
    <div class="col-12 col-xl-23">
            <div class="card">
                <div class="card-body">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <p class="lead text-muted">จัดการการตั้งค่า Google Drive และ Docs API</p>
                        <div class="mt-3">
                            <span class="status-indicator status-connected"></span>
                            <span class="text-success fw-semibold">ระบบพร้อมใช้งาน</span>
                        </div>
                    </div>
   


            <div class="row g-4">
                <!-- Credentials Upload Section -->
                <div class="col-12 col-lg-6">


                               <label class="form-label mb-0">Credentials Kay</label>
                <div class="mb-3">
                    <div class="file-single-preview" id="editImagePreview" data-isfile="<?php // $model->showImg()['isFile']?>" data-newfile="false">
                        <?php //  Html::img($model->showImg()['image'],['id' => 'editPreviewImg']) ?>
                        <div class="file-remove" id="editRemoveImage">
                            <i class="bi bi-x"></i>
                        </div>
                    </div>

                    <div class="file-upload">
                        <div class="file-upload-btn" id="editUploadBtn">
                            <i class="bi bi-cloud-arrow-up fs-3 mb-2"></i>
                           <h3 class="mb-0">
                                <i class="fas fa-key me-2"></i>
                                อัปโหลด Credentials
                            </h3>
                            <small class="d-block text-muted mt-2">ลากไฟล์ credentials.json มาที่นี่</small>
                        </div>
                        <input type="file" class="file-upload-input" id="my_file" accept="image/*">
                    </div>
                </div>

                
                    <div class="section-card h-100">
                        <div class="section-header">
                           
                        </div>
                        <div class="p-4">
          
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>วิธีการใช้งาน:</strong>
                                    <ol class="mb-0 mt-2">
                                        <li>ไปที่ Google Cloud Console</li>
                                        <li>สร้าง Service Account</li>
                                        <li>ดาวน์โหลดไฟล์ credentials.json</li>
                                        <li>อัปโหลดไฟล์ที่นี่</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Drive/Docs IDs Management -->
                <div class="col-12 col-lg-6">
                    <div class="section-card h-100">
                        <div class="section-header">
                            <h3 class="mb-0">
                                <i class="fab fa-google-drive me-2"></i>
                                จัดการ Drive & Docs ID
                            </h3>
                        </div>
                        <div class="p-4">
                            <form id="idForm">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-tag me-2"></i>ชื่อโครงการ
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="projectName"
                                        placeholder="ชื่อโครงการ...">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-link me-2"></i>Google Drive Folder ID
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="driveId"
                                        placeholder="1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms">
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        คัดลอกจาก URL ของ Google Drive Folder
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-gradient btn-lg">
                                        <i class="fas fa-plus me-2"></i>เพิ่ม Configuration
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="testConnection()">
                                        <i class="fas fa-plug me-2"></i>บันทึก
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

             </div>
            </div>

    </div>
</div>
</div>


<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>คู่มือการใช้งาน
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-key me-2"></i>การตั้งค่า Credentials</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-check text-success me-2"></i>สร้าง Service Account ใน Google Cloud</li>
                            <li><i class="fas fa-check text-success me-2"></i>เปิดใช้งาน Google Drive & Docs API</li>
                            <li><i class="fas fa-check text-success me-2"></i>ดาวน์โหลดไฟล์ JSON</li>
                            <li><i class="fas fa-check text-success me-2"></i>อัปโหลดไฟล์ในระบบ</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-link me-2"></i>การหา Drive/Docs ID</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>เปิด Google Drive หรือ Docs</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>คัดลอก URL จากแถบที่อยู่</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>ID อยู่ระหว่าง /d/ และ /edit</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>วางใน Form ด้านบน</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>