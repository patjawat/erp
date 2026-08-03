<?php

use yii\helpers\Html;

/** @var string $active */

$items = [
    'overview' => ['label' => 'ภาพรวม', 'url' => ['/pm/default/index'], 'icon' => 'layout-dashboard'],
    'projects' => ['label' => 'แผนงาน/โครงการ', 'url' => ['/pm/projects/index'], 'icon' => 'folder-kanban'],
    'report' => ['label' => 'รายงาน', 'url' => ['/pm/report/index'], 'icon' => 'bar-chart-3'],
    // 'settings' ถูกซ่อน — ย้ายอักษรย่อหน่วยงานไปทะเบียนหน่วยงานกลาง /settings/org-unit
];
?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?php foreach ($items as $key => $item): ?>
        <?= Html::a(
            '<i data-lucide="' . $item['icon'] . '"></i> ' . Html::encode($item['label']),
            $item['url'],
            ['class' => 'btn btn-sm ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary')]
        ) ?>
    <?php endforeach; ?>
</div>
