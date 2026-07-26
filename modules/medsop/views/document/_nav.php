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
<nav class="medsop-nav d-flex flex-wrap align-items-center justify-content-lg-end gap-1" aria-label="เมนู MedSOP">
    <?php foreach ($items as [$key, $label, $icon, $url]): ?>
        <?= Html::a('<i class="bi ' . $icon . ' me-1" aria-hidden="true"></i>' . Html::encode($label), $url, [
            'class' => 'btn btn-sm px-3 ' . ($active === $key ? 'btn-primary' : 'btn-outline-secondary'),
            'aria-current' => $active === $key ? 'page' : null,
        ]) ?>
    <?php endforeach; ?>
</nav>
