<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\approveV2\models\ApproveLevelSetting;

/** @var yii\web\View $this */

$this->title = 'ตั้งค่าระดับการอนุมัติ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/approve-v2']];
$this->params['breadcrumbs'][] = $this->title;

$systems = ApproveLevelSetting::systemOptions();
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
    <i data-lucide="settings"></i>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <p class="text-muted mb-0">
            กำหนดระดับการอนุมัติของแต่ละระบบ (เช่น การลา จัดซื้อ ไปราชการ) — <strong>ตามหลัก: เรียกจากระดับลึกสุด</strong> (แผนกพนักงาน) <strong>ขึ้นมาก่อน</strong> แล้วค่อยขึ้นไประดับบน
            ผู้อนุมัติมาจาก <strong>ผังโครงสร้างองค์กร</strong> (<a href="<?= Url::to(['/hr/organization/diagram']) ?>">/hr/organization/diagram</a>) หรือตามบทบาท (Role)
        </p>
        <p class="text-muted small mb-0 mt-2">
            ตัวอย่างลำดับ: หัวหน้างาน → หัวหน้างาน → หัวหน้ากลุ่มงาน → ตรวจสอบ → ผอ.อนุมัติ (หรือกำหนดผู้อนุมัติแทนก็ได้)
        </p>
        <p class="text-muted small mb-0 mt-1">
            สถานะ: <strong>None</strong> = ยังไม่ถึงคิว, <strong>Pending</strong> = ถึงคิวรออนุมัติ, <strong>Pass</strong> = อนุมัติแล้ว, <strong>Reject</strong> = ไม่อนุมัติ
        </p>
    </div>
</div>

<div class="row g-4">
    <?php foreach ($systems as $code => $label): ?>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start gap-3">
                    <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                        <i class="bi bi-diagram-3 fs-4"></i>
                    </span>
                    <div class="flex-grow-1 min-w-0">
                        <h5 class="fw-semibold text-body mb-2"><?= Html::encode($label) ?></h5>
                        <p class="text-muted small mb-3">ระดับการอนุมัติสำหรับระบบ <?= Html::encode($label) ?></p>
                        <?= Html::a('จัดการระดับการอนุมัติ <i class="bi bi-arrow-right ms-1"></i>', ['levels', 'system' => $code], [
                            'class' => 'btn btn-outline-primary rounded-pill btn-sm',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
