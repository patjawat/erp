<?php
/**
 * รายงานยอดคงเหลือ — มุมผู้ดูแลคลังหลัก
 * Scope: คลังหลัก (MAIN) ที่ user เป็น officer
 *
 * @var \yii\web\View $this
 * @var int|null $warehouseId
 * @var array    $warehouses
 * @var array    $rows
 * @var array    $summary
 * @var array    $categories
 * @var mixed    $categoryId
 * @var mixed    $status
 * @var mixed    $search
 * @var int      $accessibleWarehouseCount
 */
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'สรุปยอดคงเหลือ คลังหลัก';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'คลังหลัก', 'url' => ['/inventory-v2/main-stock/dashboard']];
$this->params['breadcrumbs'][] = 'สรุปยอดคงเหลือ';

$historyUrl = Url::to(['/inventory-v2/report/item-history']);
$exportHistoryUrl = Url::to(['/inventory-v2/report/export-item-history']);
$balanceUrl = Url::to(['/inventory-v2/main-stock/balance']);
$exportUrl = '/inventory-v2/main-stock/export-balance';

$currentWarehouseName = null;
if ($warehouseId && isset($warehouses[$warehouseId])) {
    $currentWarehouseName = $warehouses[$warehouseId];
}
?>

<?php $this->beginBlock('page-title'); ?>
<div class="bal-page-head">
    <div class="bal-page-head__title">
        <i class="bi bi-building" aria-hidden="true"></i>
        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4>
    </div>
    <p class="bal-page-head__caption mb-0">
        <span class="bal-page-head__role">มุมผู้ดูแลคลังหลัก</span>
        <?= $currentWarehouseName
            ? '· กำลังดู <strong>' . Html::encode($currentWarehouseName) . '</strong>'
            : '· เห็นข้อมูลทุกคลังหลักที่คุณรับผิดชอบ' ?>
    </p>
</div>
<style>
.bal-page-head { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.2rem; }
.bal-page-head__title { display: inline-flex; align-items: center; gap: 0.5rem; }
.bal-page-head__title i { color: #0d6efd; font-size: 1.15rem; }
.bal-page-head__title h4 { font-size: 1.15rem; font-weight: 600; color: #1a202c; line-height: 1.2; }
.bal-page-head__caption { width: 100%; font-size: 0.82rem; color: #4a5568; line-height: 1.5; margin-top: 0.2rem; }
.bal-page-head__role { color: #0a58ca; font-weight: 600; }
</style>
<?php $this->endBlock(); ?>

<?php
$actionMenu = $this->render('@app/modules/inventoryV2/views/default/_menu_main', [
    'active' => 'report',
    'subWarehouseIds' => [],
]);
foreach (['action', 'page-action'] as $actionBlock) {
    $this->beginBlock($actionBlock);
    echo $actionMenu;
    $this->endBlock();
}
?>

<?= $this->render('@app/modules/inventoryV2/views/report/_balance', [
    'variant' => 'main',
    'balanceUrl' => $balanceUrl,
    'exportUrl' => $exportUrl,
    'historyUrl' => $historyUrl,
    'exportHistoryUrl' => $exportHistoryUrl,
    'warehouseId' => $warehouseId,
    'warehouses' => $warehouses,
    'rows' => $rows,
    'summary' => $summary,
    'categories' => $categories,
    'categoryId' => $categoryId,
    'status' => $status,
    'search' => $search,
    'accessibleWarehouseCount' => $accessibleWarehouseCount,
]) ?>
