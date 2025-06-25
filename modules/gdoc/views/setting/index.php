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

<style>
    
</style>
<div class="container">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2 class="h2">ตั้งค่าระบบเชื่อมต่อ Google API</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-question-circle me-1"></i>คู่มือการใช้งาน
                        </button>
                    </div>
                </div>

                <!-- Credentials Upload Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card settings-card">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-key me-2"></i>อัปโหลดไฟล์ Credentials
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>
                                    อัปโหลดไฟล์ credentials.json ที่ได้จาก Google Cloud Console เพื่อเชื่อมต่อกับ Google API
                                </div>
                                
                                <div class="file-upload" id="credentialsUpload">
                                    <i class="bi bi-cloud-arrow-up" style="font-size: 48px; color: var(--primary);"></i>
                                    <h5 class="mt-3">คลิกหรือลากไฟล์มาที่นี่</h5>
                                    <p class="file-upload-text">รองรับไฟล์ .json เท่านั้น</p>
                                    <div class="file-name" id="credentialsFileName"></div>
                                    <input type="file" class="file-upload-input" id="credentialsFile" accept=".json">
                                </div>
                                
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold">สถานะ: </span>
                                            <span class="badge bg-success" id="credentialsStatus">พร้อมใช้งาน</span>
                                        </div>
                                        <div>
                                            <button class="btn btn-primary" id="uploadCredentialsBtn">
                                                <i class="bi bi-upload me-1"></i>อัปโหลด
                                            </button>
                                            <button class="btn btn-outline-danger" id="removeCredentialsBtn">
                                                <i class="bi bi-trash me-1"></i>ลบ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Document IDs Management -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card settings-card">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-file-earmark-text me-2"></i>จัดการ Google Document IDs
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDocModal">
                                        <i class="bi bi-plus-circle me-1"></i>เพิ่มเอกสารใหม่
                                    </button>
                                </div>
                                
                                <div class="table-container">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col" width="5%">#</th>
                                                <th scope="col" width="20%">ชื่อเอกสาร</th>
                                                <th scope="col" width="35%">Document ID</th>
                                                <th scope="col" width="15%">ประเภท</th>
                                                <th scope="col" width="10%">สถานะ</th>
                                                <th scope="col" width="15%">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>แบบฟอร์มลงทะเบียน</td>
                                                <td><code>1xABC123def456GHI789jkl0MNopq_rsTUvwXYZ</code></td>
                                                <td><span class="badge bg-info">Google Form</span></td>
                                                <td><span class="badge bg-success badge-status">ใช้งาน</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning action-btn" data-bs-toggle="modal" data-bs-target="#editDocModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal" data-bs-target="#deleteDocModal">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>ข้อมูลสมาชิก</td>
                                                <td><code>2yDEF456ghi789JKL012mno3PQrst_UVwxYZA</code></td>
                                                <td><span class="badge bg-primary">Google Sheet</span></td>
                                                <td><span class="badge bg-success badge-status">ใช้งาน</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning action-btn" data-bs-toggle="modal" data-bs-target="#editDocModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal" data-bs-target="#deleteDocModal">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>รายงานประจำเดือน</td>
                                                <td><code>3zGHI789jkl012MNO345pqr6STuvw_XYZabC</code></td>
                                                <td><span class="badge bg-secondary">Google Doc</span></td>
                                                <td><span class="badge bg-warning text-dark badge-status">รอตรวจสอบ</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning action-btn" data-bs-toggle="modal" data-bs-target="#editDocModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal" data-bs-target="#deleteDocModal">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card settings-card">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-gear me-2"></i>ตั้งค่าระบบ
                            </div>
                            <div class="card-body">
                                <form id="systemSettingsForm">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="apiTimeout" class="form-label">ระยะเวลาหมดอายุการเชื่อมต่อ (นาที)</label>
                                            <input type="number" class="form-control" id="apiTimeout" value="30" min="1" max="120">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="maxRetries" class="form-label">จำนวนครั้งที่ลองใหม่เมื่อเกิดข้อผิดพลาด</label>
                                            <input type="number" class="form-control" id="maxRetries" value="3" min="1" max="10">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="defaultPermission" class="form-label">สิทธิ์การเข้าถึงเริ่มต้น</label>
                                            <select class="form-select" id="defaultPermission">
                                                <option value="read">อ่านอย่างเดียว</option>
                                                <option value="edit" selected="">แก้ไขได้</option>
                                                <option value="admin">ผู้ดูแลระบบ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="notificationEmail" class="form-label">อีเมลแจ้งเตือน</label>
                                            <input type="email" class="form-control" id="notificationEmail" placeholder="example@domain.com">
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="enableNotifications" checked="">
                                        <label class="form-check-label" for="enableNotifications">เปิดใช้งานการแจ้งเตือน</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="enableAutoSync" checked="">
                                        <label class="form-check-label" for="enableAutoSync">เปิดใช้งานการซิงค์ข้อมูลอัตโนมัติ</label>
                                    </div>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary me-2" id="resetSettingsBtn">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>คืนค่าเริ่มต้น
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="saveSettingsBtn">
                                            <i class="bi bi-save me-1"></i>บันทึกการตั้งค่า
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>