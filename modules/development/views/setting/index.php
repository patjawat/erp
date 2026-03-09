<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'แบบฟอร์มไปราชการ';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวมอบรม/ประชุม/ดูงาน', 'url' => ['/development/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$items = [
    [
        'label'       => 'ประเภทการอบรม/ประชุม/ดูงาน',
        'description' => 'จัดการรายการประเภทกิจกรรม (เช่น ประชุมวิชาการ ฝึกอบรม ศึกษาดูงาน) ที่แสดงในแบบฟอร์มและรายงาน',
        'url'         => ['/settings/categorise/index', 'name' => 'development_type', 'title' => 'ประเภทการอบรม/ประชุม/ดูงาน'],
        'icon'        => 'bi-journal-bookmark',
    ],
    [
        'label'       => 'ประเภทยานพาหนะ',
        'description' => 'จัดการรายการประเภทยานพาหนะ (เช่น รถส่วนกลาง รถจ้างเหมา) สำหรับเลือกในแบบฟอร์มขอไปราชการ',
        'url'         => ['/settings/categorise/index', 'name' => 'vehicle_type', 'title' => 'ประเภทยานพาหนะ'],
        'icon'        => 'bi-truck',
    ],
    [
        'label'       => 'ประเภทค่าใช้จ่าย',
        'description' => 'จัดการรายการประเภทค่าใช้จ่าย (เช่น ค่าเบี้ยเลี้ยง ค่าพาหนะ ค่าลงทะเบียน) สำหรับบันทึกในใบขอไปราชการ',
        'url'         => ['/settings/categorise/index', 'name' => 'expense_type', 'title' => 'ประเภทค่าใช้จ่าย'],
        'icon'        => 'bi-currency-exchange',
    ],
];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-file-earmark-text text-primary"></i>
    <h4 class="fw-medium text-body mb-0"><?= Html::encode($this->title) ?></h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/development/views/menu_admin', ['active' => 'setting-form']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
    <?= Yii::$app->session->getFlash('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-gear-wide-connected"></i> จัดการข้อมูลหลักสำหรับแบบฟอร์มไปราชการ
        </h6>
        <span class="small text-muted">เลือกรายการเพื่อจัดการข้อมูลที่ใช้ในแบบฟอร์มบันทึกข้อความขอไปราชการ</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 py-3">รายการ</th>
                    <th class="py-3">คำอธิบาย</th>
                    <th class="py-3 text-end pe-4">จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <i class="bi <?= Html::encode($item['icon']) ?>"></i>
                            </span>
                            <span class="fw-medium small"><?= Html::encode($item['label']) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="small text-muted"><?= Html::encode($item['description']) ?></span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary btn-sm rounded-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical me-1"></i> จัดการ
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><?= Html::a('<i class="bi bi-pencil me-2"></i> จัดการรายการ', $item['url'], ['class' => 'dropdown-item']) ?></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent border-top py-2 px-4">
        <p class="small text-muted mb-0">
            <i class="bi bi-info-circle me-1"></i>
            ข้อมูลหลักเหล่านี้จะถูกใช้ในแบบฟอร์มบันทึกข้อความขอไปราชการ และรายงาน
        </p>
    </div>
</div>
