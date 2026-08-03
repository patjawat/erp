<?php
use app\modules\medsop\services\DocumentAccessService;
use yii\helpers\Html;

$access = $access ?? new DocumentAccessService();
$active = $active ?? '';
$items = [
    ['dashboard', 'ภาพรวม', 'bi-speedometer2', ['/medsop/document/dashboard']],
    ['index', 'คลังเอกสาร', 'bi-journals', ['/medsop/document/index']],
    ['report', 'รายงาน', 'bi-bar-chart-line', ['/medsop/document/report']],
];
if ($access->isAdmin()) {
    $items[] = ['setting', 'ตั้งค่า', 'bi-gear', ['/medsop/document/setting']];
}
?>
<nav class="medsop-nav d-flex flex-wrap align-items-center justify-content-lg-end gap-2" aria-label="เมนู MedSOP">
    <?php foreach ($items as [$key, $label, $icon, $url]): ?>
        <?= Html::a('<i class="bi ' . $icon . '" aria-hidden="true"></i><span class="d-none d-sm-inline">' . Html::encode($label) . '</span>', $url, [
            'class' => 'btn d-inline-flex align-items-center gap-2 ' . ($active === $key ? 'btn-primary' : 'btn-outline-primary'),
            'aria-current' => $active === $key ? 'page' : null,
        ]) ?>
    <?php endforeach; ?>
</nav>
