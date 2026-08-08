<?php

use yii\helpers\Html;

/** @var string $active */

$this->registerCss('.pm-module-menu{max-width:100%;overflow-x:auto;scrollbar-width:thin}.pm-module-menu .btn{flex:0 0 auto;min-height:38px}@media(max-width:575.98px){.header-profile{display:none!important}.page-title-box{max-width:100vw;overflow:hidden}}');
$items = [
    'overview' => ['label' => 'ภาพรวม', 'url' => ['/pm/default/index'], 'icon' => 'layout-dashboard'],
    'strategy' => ['label' => 'แผนยุทธศาสตร์', 'url' => ['/pm/strategy-plan/index'], 'icon' => 'map'],
    'indicator' => ['label' => 'ตัวชี้วัด', 'url' => ['/pm/strategy-catalog/index', 'type' => 'indicator'], 'icon' => 'gauge'],
    'program' => ['label' => 'แผนงานหลัก', 'url' => ['/pm/strategy-catalog/index', 'type' => 'program'], 'icon' => 'network'],
    'projects' => ['label' => 'แผนงาน/โครงการ', 'url' => ['/pm/projects/index'], 'icon' => 'folder-kanban'],
    'report' => ['label' => 'รายงาน', 'url' => ['/pm/report/index'], 'icon' => 'bar-chart-3'],
    // 'settings' ถูกซ่อน — ย้ายอักษรย่อหน่วยงานไปทะเบียนหน่วยงานกลาง /settings/org-unit
];
?>
<div class="pm-module-menu d-flex flex-nowrap gap-2 align-items-center pb-1">
    <?php foreach ($items as $key => $item): ?>
        <?= Html::a(
            '<i data-lucide="' . $item['icon'] . '"></i> ' . Html::encode($item['label']),
            $item['url'],
            ['class' => 'btn btn-sm ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary')]
        ) ?>
    <?php endforeach; ?>
</div>
