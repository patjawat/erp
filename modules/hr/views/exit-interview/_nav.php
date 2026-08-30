<?php
use yii\helpers\Html;
$active = $active ?? 'dashboard';
$items = [
    'dashboard' => ['ภาพรวม', 'bar-chart-3', ['/hr/exit-interview/index']],
    'registry' => ['รายการสัมภาษณ์', 'clipboard-list', ['/hr/exit-interview/registry']],
    'templates' => ['แบบสอบถาม', 'files', ['/hr/exit-interview/templates']],
];
if (!Yii::$app->user->can('exitInterviewViewAnalytics') && !Yii::$app->user->can('admin')) unset($items['dashboard']);
if (!Yii::$app->user->can('exitInterviewManage') && !Yii::$app->user->can('admin')) unset($items['registry']);
if (!Yii::$app->user->can('exitInterviewManageTemplate') && !Yii::$app->user->can('admin')) unset($items['templates']);
?>
<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="เมนู Exit Interview">
    <?php foreach ($items as $key => [$label, $icon, $url]): ?>
        <?= Html::a('<i data-lucide="' . $icon . '" aria-hidden="true"></i><span>' . Html::encode($label) . '</span>', $url, [
            'class' => 'btn ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary') . ' d-inline-flex align-items-center gap-2',
            'aria-current' => $active === $key ? 'page' : null,
            'data-pjax' => '0',
        ]) ?>
    <?php endforeach ?>
</nav>
