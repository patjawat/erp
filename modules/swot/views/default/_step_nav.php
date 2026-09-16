<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoard $model */
/** @var string $active */  // canvas | table | radar

$steps = [
    'canvas' => ['label' => 'ระดมประเด็น', 'icon' => 'bi-grid-3x3-gap', 'action' => 'board'],
    'table' => ['label' => 'จัดหมวด & น้ำหนัก', 'icon' => 'bi-table', 'action' => 'table'],
    'radar' => ['label' => 'เรดาร์สรุป', 'icon' => 'bi-pentagon', 'action' => 'radar'],
    'matrix' => ['label' => $model->isSoar() ? 'กลยุทธ์ SOAR' : 'กลยุทธ์ TOWS', 'icon' => 'bi-diagram-3', 'action' => 'matrix'],
];
?>
<div class="swot-steps d-flex flex-nowrap gap-2 align-items-center mb-3" style="overflow-x:auto;">
    <?php $i = 1; foreach ($steps as $key => $s): ?>
        <?= Html::a(
            '<span class="swot-step-no">' . $i . '</span> <i class="' . $s['icon'] . '"></i> ' . Html::encode($s['label']),
            [$s['action'], 'id' => $model->id],
            ['class' => 'btn btn-sm rounded-pill px-3 ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary')]
        ) ?>
        <?php if ($i < count($steps)): ?><i class="bi bi-chevron-right text-muted small"></i><?php endif; ?>
    <?php $i++; endforeach; ?>

    <div class="dropdown ms-auto ps-2">
        <button class="btn btn-sm btn-outline-success rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-box-arrow-up"></i> ส่งออก
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><?= Html::a('<i class="bi bi-file-earmark-text me-2"></i>พิมพ์รายงาน / PDF', ['report', 'id' => $model->id], ['class' => 'dropdown-item']) ?></li>
            <li><?= Html::a('<i class="bi bi-file-earmark-excel me-2"></i>ดาวน์โหลด Excel', ['export', 'id' => $model->id], ['class' => 'dropdown-item']) ?></li>
        </ul>
    </div>
</div>
<style>
.swot-steps .swot-step-no { display:inline-grid; place-items:center; width:18px; height:18px; border-radius:50%; background:rgba(0,0,0,.12); font-size:.7rem; font-weight:700; }
.swot-steps .btn-primary .swot-step-no { background:rgba(255,255,255,.3); }
</style>
