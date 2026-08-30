<?php

use yii\helpers\Html;

$active = $active ?? 'overview';
$items = [
    'overview' => ['ภาพรวม', 'layout-dashboard', ['/hr/workforce/index']],
    'jd' => ['JD', 'file-text', ['/hr/workforce/index', 'section' => 'jd']],
    'kpi' => ['KPI', 'goal', ['/hr/workforce/index', 'section' => 'kpi']],
    'core' => ['Core', 'shield-check', ['/hr/competency/index']],
    'idp' => ['IDP', 'target', ['/hr/idp/index']],
    'trm' => ['TRM', 'signpost', ['/hr/training-roadmap/index']],
    'appraisal' => ['ประเมินผล', 'chart-no-axes-combined', ['/hr/workforce/index', 'section' => 'appraisal']],
    'talent' => ['9 Box', 'grid-3x3', ['/hr/talent-grid/index']],
    'health' => ['Health', 'heart-pulse', ['/health/default/index']],
    'exit' => ['Exit Int', 'log-out', ['/hr/exit-interview/index']],
];
$titles = [
    'core' => 'สมรรถนะหลัก (Core Competency)',
    'trm' => 'Training Roadmap',
    'health' => 'สุขภาพและการเจ็บป่วยจากการทำงาน',
    'exit' => 'Exit Interview',
    'talent' => 'ตารางจำแนกศักยภาพบุคลากร 9 Box',
];
?>
<nav class="workforce-nav" aria-label="เมนูงานบุคลากร">
    <?php foreach ($items as $key => [$label, $icon, $url]): ?>
        <?= Html::a(
            '<i data-lucide="' . $icon . '" aria-hidden="true"></i><span>' . Html::encode($label) . '</span>',
            $url,
            [
                'class' => 'workforce-nav__item workforce-nav__item--' . $key . ($active === $key ? ' is-active' : ''),
                'title' => $titles[$key] ?? $label,
                'aria-current' => $active === $key ? 'page' : null,
                'data-pjax' => '0',
            ]
        ) ?>
    <?php endforeach ?>
</nav>
