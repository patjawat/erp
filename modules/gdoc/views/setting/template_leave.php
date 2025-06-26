   <?php
use yii\helpers\Url;
$this->title = "Google API Configuration";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('_sub_menu',['active' => 'template'])?>
<?php $this->endBlock(); ?>
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
                                        <button class="btn btn-sm btn-warning action-btn" data-bs-toggle="modal"
                                            data-bs-target="#editDocModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal"
                                            data-bs-target="#deleteDocModal">
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
                                        <button class="btn btn-sm btn-warning action-btn" data-bs-toggle="modal"
                                            data-bs-target="#editDocModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal"
                                            data-bs-target="#deleteDocModal">
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
                                        <button class="btn btn-sm btn-warning action-btn" data-bs-toggle="modal"
                                            data-bs-target="#editDocModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger action-btn" data-bs-toggle="modal"
                                            data-bs-target="#deleteDocModal">
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