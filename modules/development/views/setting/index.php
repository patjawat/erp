<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'การตั้งค่าแบบฟอร์มไปราชการ';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวมอบรม/ประชุม/ดูงาน', 'url' => ['/development/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$settings = [
    [
        'label' => 'ประเภทการอบรม/ประชุม/ดูงาน',
        'description' => 'จัดการรายการประเภทกิจกรรม (เช่น ประชุมวิชาการ ฝึกอบรม ศึกษาดูงาน) ที่แสดงในแบบฟอร์มและรายงาน',
        'url' => ['/settings/categorise/index', 'name' => 'development_type', 'title' => 'ประเภทการอบรม/ประชุม/ดูงาน'],
        'icon' => 'bi-journal-bookmark',
    ],
    [
        'label' => 'ประเภทยานพาหนะ',
        'description' => 'จัดการรายการประเภทยานพาหนะ (เช่น รถส่วนกลาง รถจ้างเหมา) สำหรับเลือกในแบบฟอร์มขอไปราชการ',
        'url' => ['/settings/categorise/index', 'name' => 'vehicle_type', 'title' => 'ประเภทยานพาหนะ'],
        'icon' => 'bi-truck',
    ],
    [
        'label' => 'ประเภทค่าใช้จ่าย',
        'description' => 'จัดการรายการประเภทค่าใช้จ่าย (เช่น ค่าเบี้ยเลี้ยง ค่าพาหนะ ค่าลงทะเบียน) สำหรับบันทึกในใบขอไปราชการ',
        'url' => ['/settings/categorise/index', 'name' => 'expense_type', 'title' => 'ประเภทค่าใช้จ่าย'],
        'icon' => 'bi-currency-exchange',
    ],
];
?>
<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ภาพรวม', ['/development/default/dashboard'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    <?= $this->render('../default/_menu', ['active' => 'setting']) ?>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2">
                <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3">
                    <i class="bi bi-gear-fill"></i>
                </span>
                <div>
                    <h4 class="fw-bold text-body mb-0"><?= Html::encode($this->title) ?></h4>
                    <p class="text-muted small mb-0">จัดการข้อมูลหลักที่ใช้ในแบบฟอร์มบันทึกข้อความขอไปราชการ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($settings as $item): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start gap-3">
                        <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                            <i class="bi <?= Html::encode($item['icon']) ?> fs-4"></i>
                        </span>
                        <div class="flex-grow-1 min-w-0">
                            <h5 class="fw-semibold text-body mb-2"><?= Html::encode($item['label']) ?></h5>
                            <p class="text-muted small mb-3"><?= Html::encode($item['description']) ?></p>
                            <?= Html::a('จัดการรายการ <i class="bi bi-arrow-right ms-1"></i>', $item['url'], [
                                'class' => 'btn btn-outline-primary rounded-pill btn-sm',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
