<?php

use yii\helpers\Html;

$active = $active ?? 'overview';
$items = [
    'overview' => ['ภาพรวม', 'layout-dashboard', ['/hr/workforce/index']],
    'jd' => ['JD', 'file-text', ['/hr/workforce/index', 'section' => 'jd']],
    'idp' => ['IDP', 'target', ['/hr/idp/index']],
    'trm' => ['TRM', 'signpost', ['/hr/training-roadmap/index']],
    'appraisal' => ['ประเมินผล', 'chart-no-axes-combined', ['/hr/workforce/index', 'section' => 'appraisal']],
    'health' => ['Health', 'heart-pulse', ['/health/default/index']],
    'exit' => ['Exit Int', 'log-out', ['/hr/workforce/index', 'section' => 'exit']],
];
?>
<nav class="workforce-nav" aria-label="เมนูงานบุคลากร">
    <?php foreach ($items as $key => [$label, $icon, $url]): ?>
        <?= Html::a(
            '<i data-lucide="' . $icon . '" aria-hidden="true"></i><span>' . Html::encode($label) . '</span>',
            $url,
            [
                'class' => 'workforce-nav__item workforce-nav__item--' . $key . ($active === $key ? ' is-active' : ''),
                'title' => $key === 'trm' ? 'Training Roadmap' : ($key === 'health' ? 'สุขภาพและการเจ็บป่วยจากการทำงาน' : ($key === 'exit' ? 'Exit Interview' : $label)),
                'aria-current' => $active === $key ? 'page' : null,
                'data-pjax' => '0',
            ]
        ) ?>
    <?php endforeach ?>
</nav>
