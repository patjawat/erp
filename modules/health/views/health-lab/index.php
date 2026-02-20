<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ตั้งค่ารายการ LAB';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'setting'])
?>
<?php $this->endBlock(); ?>


<div class="lab-setting-index container-fluid py-4">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-cog me-2 text-primary"></i><?= Html::encode($this->title) ?>
            </h5>
            <?= Html::a('<i class="fas fa-plus me-1"></i> เพิ่มรายการ Lab', ['create'], [
                'class' => 'btn btn-primary rounded-pill px-4 shadow-sm open-modal', // เพิ่ม class open-modal
                'data-title' => 'เพิ่มรายการ LAB ใหม่', // ข้อความที่จะโชว์บนหัว Modal
                'data-size' => 'modal-lg',            // กำหนดขนาด Modal
            ]) ?>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
    <thead class="bg-light">
        <tr>
            <th class="ps-4 py-3 text-muted small uppercase" style="width: 80px;">#</th>
            <th class="py-3 text-muted small uppercase" style="width: 150px;">รหัส LAB</th>
            <th class="py-3 text-muted small uppercase">รายการ / ประเภท</th>
            <th class="py-3 text-muted small uppercase" style="width: 140px;">ช่วงอายุ</th>
            <th class="py-3 text-muted small uppercase" style="width: 80px;">เพศ</th>
            <th class="py-3 text-muted small uppercase text-end">ราคาพื้นฐาน</th>
            <th class="py-3 text-center text-muted small uppercase" style="width: 180px;">จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($dataProvider->getModels())): ?>
            <?php foreach ($dataProvider->getModels() as $index => $model): ?>
                <tr>
                    <td class="ps-4 text-muted small"><?= $index + 1 ?></td>
                    
                    <td>
                        <code class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                            <?= $model->lab_code ?>
                        </code>
                    </td>
                    
                    <td>
                        <div class="fw-bold text-dark"><?= $model->lab_name ?></div>
                    </td>
                    <td>
                        <?= Html::encode($model->getAgeConditionLabel()) ?>
                    </td>
                    <td>
                        <?php
                        $genderLabels = ['all' => 'ทุกคน', 'male' => 'ชาย', 'female' => 'หญิง'];
                        $gender = $model->gender_condition ?? 'all';
                        echo Html::encode($genderLabels[$gender] ?? $gender);
                        ?>
                    </td>
                    <td class="text-end fw-bold">
                        <span class="text-primary">฿<?= number_format($model->lab_price, 2) ?></span>
                    </td>

                    <td class="text-center">
    <div class="btn-group shadow-sm rounded-pill bg-white border p-1">
        <?= Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-light border-0 rounded-circle text-info open-modal',
            'style' => 'width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;',
            'title' => 'ดูรายละเอียด',
            'data-size' => 'modal-md'
        ]) ?>
        
        <?= Html::a('<i class="fas fa-pencil-alt"></i>', ['update', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-light border-0 rounded-circle text-primary open-modal ms-1',
            'style' => 'width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;',
            'title' => 'แก้ไขข้อมูล',
            'data-size' => 'modal-lg'
        ]) ?>
        
        <?= Html::a('<i class="fas fa-trash-alt"></i>', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-sm btn-light border-0 rounded-circle text-danger ms-1',
            'style' => 'width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;',
            'title' => 'ลบรายการ',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการ ' . $model->lab_name . '?',
                'method' => 'post',
            ],
        ]) ?>
    </div>
</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center py-5 text-muted small">ไม่พบข้อมูลรายการตั้งค่า LAB</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-info {
        background-color: #e3f2fd;
    }

    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: none;
    }

    .table tbody tr td {
        border-bottom: 1px solid #f1f1f1;
    }
</style>