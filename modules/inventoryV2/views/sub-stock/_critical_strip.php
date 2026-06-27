<?php
/**
 * Critical strip — แสดงเฉพาะถ้ามีของต่ำกว่า Min
 * Flat danger-soft bg + left accent (3px) — ไม่ใช้ gradient decoration
 *
 * @var int $criticalCount
 * @var array $subWarehouseIds
 */
use yii\helpers\Url;

$criticalCount = (int) ($criticalCount ?? 0);
$subWarehouseIds = $subWarehouseIds ?? [];

if ($criticalCount === 0) {
    return;
}

$reportBalanceUrl = ['/inventory-v2/sub-stock/balance'];
if (!empty($subWarehouseIds) && count($subWarehouseIds) === 1) {
    $reportBalanceUrl['warehouse_id'] = $subWarehouseIds[0];
}
?>
<div class="critical-strip" role="alert">
    <span class="critical-strip__icon" aria-hidden="true">
        <i class="bi bi-exclamation-circle-fill"></i>
    </span>
    <div class="critical-strip__body">
        <div class="critical-strip__title">มีรายการต้องดูแล</div>
        <div class="critical-strip__caption">วัสดุต่ำกว่าจุดสั่งซื้อ ควรขอเบิกเพิ่ม</div>
    </div>
    <a href="<?= Url::to($reportBalanceUrl) ?>" class="critical-strip__action">
        <span class="critical-strip__count"><?= $criticalCount ?></span>
        <span>รายการ</span>
        <i class="bi bi-arrow-right" aria-hidden="true"></i>
    </a>
</div>
