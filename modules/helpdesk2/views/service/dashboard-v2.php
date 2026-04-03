<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $title ชื่อศูนย์ (เช่น ศูนย์คอมพิวเตอร์) */
/** @var string $icon HTML ไอคอน */
/** @var string $active ค่า active สำหรับเมนูย่อย */
/** @var array $dashboardParams พารามิเตอร์ส่งต่อไปยัง views/dashboard/index */

$this->title = 'แดชบอร์ด V2 — ' . $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $title;
$this->params['breadcrumbs'][] = 'แดชบอร์ด V2';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <?= $icon ?> <?= Html::encode($title) ?> — แดชบอร์ด V2
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/helpdesk2/menu', ['active' => $active]) ?>
<?php $this->endBlock(); ?>

<div class="shadow-sm mb-3 py-3 px-3 rounded-3 bg-primary bg-opacity-10 text-body border border-primary-subtle">
    <div class="d-flex gap-3 align-items-start">
        <div class="flex-shrink-0 text-primary pt-1">
            <i class="bi bi-lightning-charge-fill fs-5"></i>
        </div>
        <div class="small mb-0">
            <span class="fw-semibold d-block mb-1">รับเรื่องจากแดชบอร์ด</span>
            ในตาราง <strong>รายการแจ้งซ่อมล่าสุด</strong> หากสถานะเป็นรอรับเรื่อง ให้กด <strong>รับเรื่อง</strong> — ระบบจะบันทึกการรับเรื่องและงานจะปรากฏในเมนู <strong>ทะเบียนงานซ่อม</strong> ของศูนย์นี้เพื่อดำเนินการต่อ (หรือกด <strong>ดู</strong> เพื่อเปิดหน้างาน V2)
        </div>
    </div>
</div>

<?= $this->render('@app/modules/helpdesk2/views/dashboard/index', array_merge($dashboardParams, [
    'pageTitle' => $this->title,
    'skipDashboardBreadcrumbs' => true,
])) ?>
