<?php
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;

/**
 * เมนูสำหรับผู้ดูแลคลังหลัก (ลดรายการ)
 * @var string $active 'dashboard' | 'receive' | 'issue' | 'report' | 'setting'
 * @var int|null $mainWarehouseId คลังหลักที่กำลังกรองอยู่ในหน้าปัจจุบัน (null = ทุกคลัง)
 */
$active = $active ?? '';
$mainWarehouseId = $mainWarehouseId ?? null;
// ผู้เรียกส่งยอดมาแทนได้ (เช่นหน้า issue ที่ต้อง sync badge ผ่าน pjax); ถ้าไม่ส่งมาให้คำนวณเอง
$issuePendingCount = $issuePendingCount ?? null;

if ($issuePendingCount === null) {
    try {
        $issuePendingQuery = StockOrder::find()
            ->where([
                'order_type'  => 'OUT',
                'source_type' => 'REQUEST',
                'status'      => [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED],
            ]);
        // เห็นหมดทุกคลัง = admin หรือผู้มีสิทธิ์ warehouse; นอกนั้นนับเฉพาะคลังที่ user ถูกกำหนดเป็นผู้รับผิดชอบคลัง (officer)
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('warehouse')) {
            $issuePendingQuery
                ->andWhere(['main_warehouse_id' => ArrayHelper::getColumn(Warehouse::findMainWarehousesForReceive(), 'id')]);
        }
        if ($mainWarehouseId) {
            $issuePendingQuery->andWhere(['main_warehouse_id' => $mainWarehouseId]);
        }
        $issuePendingCount = (int) $issuePendingQuery->count();
    } catch (\Throwable $e) {
        $issuePendingCount = 0;
    }
}
?>
<nav class="inventory-nav inventory-nav-main" aria-label="เมนูคลังหลัก">
    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
        <a href="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" class="btn btn-sm <?= $active === 'dashboard' ? 'btn-secondary' : 'btn-outline-secondary' ?> rounded-pill px-3">
            <i class="bi bi-speedometer2 me-1"></i>ภาพรวม
        </a>
        <a href="<?= Url::to(['/inventory-v2/receive/index']) ?>" class="btn btn-sm <?= $active === 'receive' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
            <i class="bi bi-box-arrow-in-down me-1"></i>รับเข้าคลัง
        </a>
        <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="btn btn-sm <?= $active === 'issue' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3" data-issue-nav-link>
            <i class="bi bi-box-arrow-right me-1"></i>จ่ายวัสดุ
            <?php if ($issuePendingCount > 0): ?>
                <span class="badge text-bg-danger ms-1" data-issue-pending-badge title="รออนุมัติ/รอจ่าย"><?= $issuePendingCount ?></span>
            <?php endif; ?>
        </a>
        <div class="dropdown">
            <button type="button" class="btn btn-sm <?= ($active === 'stock-health' || $active === 'report' || strpos((string)$active, 'report') === 0) ? 'btn-info' : 'btn-outline-info' ?> rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-journal-text me-1"></i>รายงาน
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/main-stock/balance']) ?>"><i class="bi bi-boxes me-2"></i>สถานะคงคลัง</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/report/material-summary']) ?>"><i class="bi bi-journal-check me-2"></i>สรุปรายงานวัสดุคงคลัง</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/report/procurement-plan']) ?>"><i class="bi bi-clipboard2-data me-2"></i>การใช้งานวัสดุรายตัว</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/report/disbursement-by-month']) ?>"><i class="bi bi-calendar3-week me-2"></i>ประวัติจ่ายวัสดุ × เดือน</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item <?= $active === 'stock-health' ? 'active' : '' ?>" href="<?= Url::to(['/inventory-v2/stock-health/index']) ?>"><i class="bi bi-clipboard2-pulse me-2"></i>ตรวจสุขภาพสต็อก</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item <?= $active === 'report-material-plan' ? 'active' : '' ?>" href="<?= Url::to(['/inventory-v2/material-plan/index']) ?>"><i class="bi bi-calendar2-check me-2"></i>จัดทำแผนวัสดุประจำปี</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <button type="button" class="btn btn-sm <?= ($active === 'setting' || $active === 'stock-item' || $active === 'stock-adjust') ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear me-1"></i>ตั้งค่า
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <!-- <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/default/setting']) ?>"><i class="bi bi-gear me-2"></i>ตั้งค่าคลังสินค้า</a></li> -->
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/warehouse']) ?>"><i class="bi bi-gear me-2"></i>ตั้งค่าคลังสินค้า</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>"><i class="bi bi-box-seam me-2"></i>จัดการวัสดุ</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item <?= $active === 'stock-adjust' ? 'active' : '' ?>" href="<?= Url::to(['/inventory-v2/stock-adjust/index']) ?>"><i class="bi bi-wrench-adjustable me-2"></i>ปรับยอด stock</a></li>
            </ul>
        </div>
    </div>
</nav>
