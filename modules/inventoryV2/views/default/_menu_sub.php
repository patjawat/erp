<?php
use yii\helpers\Url;

/**
 * เมนูสำหรับผู้ดูแลคลังย่อย (ลดรายการ)
 * @var string $active 'dashboard' | 'requisition-create' | 'requisition-index' | 'issue' | 'report'
 * @var int[] $subWarehouseIds รายการ id คลังย่อยของ user (ใช้ส่ง warehouse_id ตอนลิงก์ไปรายงาน)
 */
$active = $active ?? '';
$subWarehouseIds = $subWarehouseIds ?? [];
?>
<nav class="inventory-nav inventory-nav-sub" aria-label="เมนูคลังย่อย">
    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
        <a href="<?= Url::to(['/inventory-v2/requisition']) ?>" class="btn btn-sm <?= $active === 'requisition-create' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
            <i class="bi bi-file-earmark-plus me-1"></i>ใบขอเบิก
        </a>
        <a href="<?= Url::to(['/inventory-v2/sub-stock/issue']) ?>" class="btn btn-sm <?= $active === 'issue' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i>บันทึกการจ่าย
        </a>
        <?php
        $reportBalanceUrl = ['/inventory-v2/sub-stock/balance'];
        if (!empty($subWarehouseIds) && count($subWarehouseIds) === 1) {
            $reportBalanceUrl['warehouse_id'] = $subWarehouseIds[0];
        }
        ?>
        <a href="<?= Url::to($reportBalanceUrl) ?>" class="btn btn-sm <?= ($active ?? '') === 'report' ? 'btn-info' : 'btn-outline-info' ?> rounded-pill px-3">
            <i class="bi bi-boxes me-1"></i>สถานะคงคลัง
        </a>

    </div>
</nav>
