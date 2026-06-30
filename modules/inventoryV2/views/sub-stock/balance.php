<?php
/**
 * รายงานยอดคงเหลือ — มุมเจ้าหน้าที่คลังย่อย/แผนก
 * Scope: คลังย่อย (SUB) ที่ user เป็น officer
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

$this->title = 'สรุปยอดคงเหลือ คลังย่อย';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'คลังย่อย', 'url' => ['/inventory-v2/sub-stock/dashboard']];
$this->params['breadcrumbs'][] = 'สรุปยอดคงเหลือ';

$historyUrl = Url::to(['/inventory-v2/report/item-history']);
$exportHistoryUrl = Url::to(['/inventory-v2/report/export-item-history']);
$balanceUrl = Url::to(['/inventory-v2/sub-stock/balance']);
$exportUrl = '/inventory-v2/sub-stock/export-balance';

$currentWarehouseName = null;
if ($warehouseId && isset($warehouses[$warehouseId])) {
    $currentWarehouseName = $warehouses[$warehouseId];
}
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->render('_page_head', [
    'icon'    => 'bi-boxes',
    'title'   => $this->title,
    'currentWarehouseName' => $currentWarehouseName,
    'caption' => 'เห็นเฉพาะคลังย่อยที่คุณได้รับสิทธิ์ คลิก <span class="inline-badge">ดูประวัติ</span> เพื่อเปิดบัตรเคลื่อนไหวของรายการ',
    'metas'   => $currentWarehouseName ? [] : ['ทุกคลังย่อยที่เข้าถึงได้'],
]) ?>
<?php $this->endBlock(); ?>

<?php
$subStockActionMenu = $this->render('_menu_sub_stock', [
    'active' => 'balance',
    'currentWarehouseId' => $warehouseId ?: null,
]);
foreach (['action', 'page-action'] as $actionBlock) {
    $this->beginBlock($actionBlock);
    echo $subStockActionMenu;
    $this->endBlock();
}
?>

<?= $this->render('@app/modules/inventoryV2/views/report/_balance', [
    'variant' => 'sub',
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
