<?php

use yii\helpers\Url;

$this->title = 'อนุมัติ V3';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
    <i data-lucide="layout-grid"></i>
    <?= $this->title ?>
</h4>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="text-muted mb-0">โมดูล Approve V3 — ใช้ตาราง <code>approve</code> เดิม และ model <code>app\modules\approveV3\models\Approve</code></p>
        <p class="mb-0 mt-2">
            <?= \yii\helpers\Html::a('<i class="bi bi-gear me-1"></i> ตั้งค่าระดับการอนุมัติของแต่ละระบบ', ['/approve-v3/setting/index'], ['class' => 'btn btn-outline-primary rounded-3']) ?>
        </p>
    </div>
</div>
