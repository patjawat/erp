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

   <!-- Configurations Table -->
                    <div class="mt-5">
                        <div class="section-card">
                            <div class="section-header">
                                <h3 class="mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Configurations ที่บันทึกไว้
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="configurationsTable">
                                        <thead>
                                            <tr>
                                                <th>สถานะ</th>
                                                <th>ชื่อโครงการ</th>
                                                <th>ประเภท</th>
                                                <th>Drive ID</th>
                                                <th>Docs ID</th>
                                                <th>วันที่สร้าง</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="configurationsBody">
                                            <!-- Sample Data -->
                                            <tr>
                                                <td>
                                                    <span class="status-indicator status-connected"></span>
                                                    <span class="badge bg-success">เชื่อมต่อแล้ว</span>
                                                </td>
                                                <td class="fw-semibold">Document System</td>
                                                <td><span class="badge bg-primary">Document Management</span></td>
                                                <td class="font-monospace text-truncate" style="max-width: 150px;">1BxiMVs0XRA5nFMdKvBd...</td>
                                                <td class="font-monospace text-truncate" style="max-width: 150px;">1BxiMVs0XRA5nFMdKvBd...</td>
                                                <td>25/06/2568</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editConfig(1)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-success" onclick="testConfig(1)">
                                                            <i class="fas fa-plug"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteConfig(1)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>