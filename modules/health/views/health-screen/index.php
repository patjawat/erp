<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

$this->title = 'จัดการข้อมูลตรวจสุขภาพพนักงาน';
?>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-microscope me-2"></i><?= Html::encode($this->title) ?>
            </h5>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4">พนักงาน</th>
                            <th>ปีที่ตรวจ</th>
                            <th>วันที่คัดกรอง</th>
                            <th>สถานะการตรวจ</th>
                            <th class="text-center" style="width: 350px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dataProvider->getModels())): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลการตรวจสุขภาพ</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($dataProvider->getModels() as $index => $item): ?>
                            <?php 
                                // ตัวอย่าง Logic การดึงสถานะจาก data_json หรือ database
                                $hasLab = isset($item->data_json['lab_results']);
                                $hasDoctor = isset($item->data_json['doctor_opinion']);
                                
                                $statusLabel = '<span class="badge bg-secondary">รอดำเนินการ</span>';
                                if ($hasLab && !$hasDoctor) {
                                    $statusLabel = '<span class="badge bg-warning text-dark">รอพบแพทย์</span>';
                                } elseif ($hasLab && $hasDoctor) {
                                    $statusLabel = '<span class="badge bg-success">เสร็จสมบูรณ์</span>';
                                } elseif (!$hasLab) {
                                    $statusLabel = '<span class="badge bg-info">รอผล Lab</span>';
                                }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                            <?= strtoupper(substr($item->emp_id, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= $item->employee->fullname ?? 'ไม่ระบุชื่อ' ?></div>
                                            <small class="text-muted">ID: <?= $item->emp_id ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $item->thai_year ?></td>
                                <td><?= Yii::$app->formatter->asDate($item->date_checkup, 'medium') ?></td>
                                <td><?= $statusLabel ?></td>
                                <td class="text-center">
    <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
        <?= Html::a('<i class="fas fa-vial"></i> Lab', 
            ['lab-confirm', 'id' => $item->id], 
            ['class' => 'btn btn-sm btn-outline-info border-0 rounded-pill px-3', 'title' => 'ลงผล LAB']) 
        ?>
        
        <?= Html::a('<i class="fas fa-stethoscope"></i> ตรวจร่างกาย', 
            ['physical-exam', 'id' => $item->id], 
            ['class' => 'btn btn-sm btn-outline-success border-0 rounded-pill px-3', 'title' => 'บันทึกผลการตรวจร่างกาย']) 
        ?>

        <?= Html::a('<i class="fas fa-user-md"></i> แพทย์', 
            ['doctor-entry', 'id' => $item->id], 
            ['class' => 'btn btn-sm btn-outline-primary border-0 rounded-pill px-3', 'title' => 'ลงความเห็นแพทย์']) 
        ?>
        
        <?= Html::a('<i class="fas fa-print"></i>', 
            ['print-report', 'id' => $item->id], 
            ['class' => 'btn btn-sm btn-outline-secondary border-0 rounded-pill px-2', 'target' => '_blank']) 
        ?>
    </div>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white py-3 border-top-0">
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <div>แสดงผลจาก <?= count($dataProvider->getModels()) ?> รายการ</div>
                <div>
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                        'listOptions' => ['class' => 'pagination pagination-sm mb-0'],
                        'linkContainerOptions' => ['class' => 'page-item'],
                        'linkOptions' => ['class' => 'page-link'],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }
    .bg-hover:hover {
        background-color: #f8f9fa;
    }
    .btn-group .btn:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }
    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
        border-radius: 6px;
    }
</style>