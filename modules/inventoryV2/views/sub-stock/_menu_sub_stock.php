<?php
/**
 * Sub-stock action buttons for page header.
 * UI pattern ตรงกับ _menu_main.php เพื่อความสอดคล้องของระบบ
 *
 * @var string $active 'dashboard' | 'issue' | 'requisition' | 'balance'
 * @var int|null $currentWarehouseId
 */
use yii\helpers\Url;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;

$active = $active ?? '';
$currentWarehouseId = $currentWarehouseId ?? null;

if ($currentWarehouseId === null) {
    $requestedWarehouseId = Yii::$app->request->get('warehouse_id');
    $currentWarehouseId = is_numeric($requestedWarehouseId) ? (int) $requestedWarehouseId : null;
}

$wParam = $currentWarehouseId ? ['warehouse_id' => (int) $currentWarehouseId] : [];

$isAdmin = Yii::$app->user->can('admin');
$allowedSubIds = $isAdmin
    ? null
    : array_map(fn($w) => (int) $w->id, Warehouse::findSubWarehousesForUser());
$canCreateRequisition = $isAdmin || !empty($allowedSubIds);

try {
    $pendingQuery = StockOrder::find()
        ->where([
            'order_type'  => StockOrder::ORDER_TYPE_OUT,
            'source_type' => 'REQUEST',
            'status'      => StockOrder::STATUS_PENDING,
        ]);
    if (!$isAdmin) {
        $pendingQuery->andWhere(['sub_warehouse_id' => $allowedSubIds]);
    }
    $requisitionPendingCount = (int) $pendingQuery->count();
} catch (\Throwable $e) {
    $requisitionPendingCount = 0;
}
?>
<nav class="inventory-nav inventory-nav-sub" aria-label="เมนูคลังย่อย">
    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
        <a href="<?= Url::to(array_merge(['/inventory-v2/sub-stock/dashboard'], $wParam)) ?>" class="btn btn-sm <?= $active === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
            <i class="bi bi-grid-1x2 me-1"></i>ภาพรวม
        </a>
        <a href="<?= Url::to(array_merge(['/inventory-v2/sub-stock/issue'], $wParam)) ?>" class="btn btn-sm <?= $active === 'issue' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i>ตัดจ่ายคลังย่อย
        </a>
        <?php if ($canCreateRequisition): ?>
        <a href="<?= Url::to(['/inventory-v2/requisition']) ?>" class="btn btn-sm <?= $active === 'requisition' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3">
            <i class="bi bi-file-earmark-plus me-1"></i>เบิกของคลังหลัก
            <?php if ($requisitionPendingCount > 0): ?>
                <span class="badge text-bg-danger ms-1" title="รออนุมัติ"><?= $requisitionPendingCount ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>
        <a href="<?= Url::to(array_merge(['/inventory-v2/sub-stock/balance'], $wParam)) ?>" class="btn btn-sm <?= $active === 'balance' ? 'btn-info' : 'btn-outline-info' ?> rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i>สถานะคงคลัง
        </a>
    </div>
</nav>
