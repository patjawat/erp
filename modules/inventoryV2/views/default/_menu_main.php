<?php
use yii\helpers\Url;

/**
 * เมนูสำหรับผู้ดูแลคลังหลัก (ลดรายการ)
 * @var string $active 'dashboard' | 'receive' | 'issue' | 'report' | 'setting'
 */
$active = $active ?? '';
?>
<nav class="inventory-nav inventory-nav-main" aria-label="เมนูคลังหลัก">
    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
        <a href="<?= Url::to(['/inventory-v2/receive/index']) ?>" class="btn btn-sm <?= $active === 'receive' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
            <i class="bi bi-box-arrow-in-down me-1"></i>รับเข้าคลัง
        </a>
        <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="btn btn-sm <?= $active === 'issue' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3">
            <i class="bi bi-box-arrow-right me-1"></i>จ่ายพัสดุ
        </a>
                <?php
        $reportBalanceUrl = ['/inventory-v2/report/balance-by-warehouse'];
        if (!empty($subWarehouseIds) && count($subWarehouseIds) === 1) {
            $reportBalanceUrl['warehouse_id'] = $subWarehouseIds[0];
        }
        ?>
        <a href="<?= Url::to($reportBalanceUrl) ?>" class="btn btn-sm <?= ($active ?? '') === 'report' ? 'btn-info' : 'btn-outline-info' ?> rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i>สถานะคงคลัง
        </a>
        <div class="dropdown">
            <button type="button" class="btn btn-sm <?= ($active === 'report' || strpos((string)$active, 'report') === 0) ? 'btn-info' : 'btn-outline-info' ?> rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-journal-text me-1"></i>รายงาน
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/report/balance-by-warehouse']) ?>"><i class="bi bi-boxes me-2"></i>ยอดคงเหลือตามคลัง</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/report/material-summary']) ?>"><i class="bi bi-journal-check me-2"></i>สรุปรายงานวัสดุคงคลัง</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/report/disbursement-by-month']) ?>"><i class="bi bi-calendar3-week me-2"></i>ประวัติจ่ายวัสดุ × เดือน</a></li>
            </ul>
        </div>
        <div class="dropdown">
            <button type="button" class="btn btn-sm <?= ($active === 'setting' || $active === 'stock-item') ? 'btn-dark' : 'btn-outline-dark' ?> rounded-pill px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear me-1"></i>ตั้งค่า
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <!-- <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/default/setting']) ?>"><i class="bi bi-gear me-2"></i>ตั้งค่าคลังสินค้า</a></li> -->
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/warehouse']) ?>"><i class="bi bi-gear me-2"></i>ตั้งค่าคลังสินค้า</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(['/inventory-v2/stock-item/index']) ?>"><i class="bi bi-box-seam me-2"></i>จัดการวัสดุ</a></li>
            </ul>
        </div>
    </div>
</nav>
