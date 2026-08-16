<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $detail */

$labels = [
    'low-stock' => ['รายการต่ำกว่าจุดสั่งซื้อ', 'bi-exclamation-triangle', 'danger'],
    'expiring' => ['Lot ใกล้หมดอายุภายใน 90 วัน', 'bi-clock', 'warning'],
    'expired' => ['Lot หมดอายุแล้ว', 'bi-calendar-x', 'danger'],
    'sufficient' => ['รายการอยู่เหนือขั้นต่ำ', 'bi-check-circle', 'success'],
];
[$title, $icon, $color] = $labels[$detail['type']];
$this->title = $title;
$this->registerCss('.inventory-alert-detail .executive-card{border-radius:.75rem}.inventory-alert-detail .metric-value{font-variant-numeric:tabular-nums}');
?>

<div class="inventory-alert-detail container-fluid py-3 py-lg-4">
    <header class="mb-4">
        <?= Html::a('<i class="bi bi-arrow-left me-2"></i>กลับภาพรวมคลัง', ['/executive/dashboard/inventory'], ['class' => 'btn btn-outline-secondary btn-sm mb-3']) ?>
        <div class="d-flex align-items-center gap-3"><span class="rounded-3 bg-<?= $color ?>-subtle text-<?= $color ?>-emphasis p-3"><i class="bi <?= $icon ?> fs-4"></i></span><div><div class="small text-primary fw-semibold">รายละเอียดเพื่อประกอบการตัดสินใจ</div><h1 class="h3 mb-0"><?= Html::encode($title) ?></h1></div></div>
    </header>
    <section class="card executive-card border shadow-sm">
        <div class="card-header bg-body py-3 d-flex justify-content-between"><h2 class="h5 mb-0">รายการทั้งหมด</h2><span class="badge bg-<?= $color ?>-subtle text-<?= $color ?>-emphasis"><?= number_format(count($detail['rows'])) ?> รายการ</span></div>
        <?php if ($detail['rows']): ?>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light">
            <?php if ($detail['kind'] === 'stock'): ?><tr><th>รหัส</th><th>รายการ</th><th class="text-end">คงเหลือ</th><th class="text-end">ขั้นต่ำ</th></tr>
            <?php else: ?><tr><th>รหัส</th><th>รายการ</th><th>คลัง</th><th>Lot</th><th class="text-end">คงเหลือ</th><th class="text-end">มูลค่า</th><th class="text-end">วันหมดอายุ</th></tr><?php endif; ?>
            </thead><tbody>
            <?php foreach ($detail['rows'] as $row): ?>
                <?php if ($detail['kind'] === 'stock'): ?><tr><td class="text-body-secondary"><?= Html::encode($row['code']) ?></td><td class="fw-semibold"><?= Html::encode($row['name'] ?: $row['code']) ?></td><td class="text-end metric-value"><?= number_format((float) $row['balance'], 2) ?></td><td class="text-end metric-value"><?= number_format((float) $row['minimum'], 2) ?></td></tr>
                <?php else: ?><tr><td class="text-body-secondary"><?= Html::encode($row['code']) ?></td><td class="fw-semibold"><?= Html::encode($row['name'] ?: $row['code']) ?></td><td><?= Html::encode($row['warehouse'] ?: '—') ?></td><td><?= Html::encode($row['lot']) ?></td><td class="text-end metric-value"><?= number_format((float) $row['qty'], 2) ?></td><td class="text-end metric-value"><?= number_format((float) $row['value'], 2) ?></td><td class="text-end text-nowrap"><?= Yii::$app->formatter->asDate($row['expiry_date'], 'php:d/m/Y') ?></td></tr><?php endif; ?>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?><div class="card-body py-5 text-center"><i class="bi bi-inbox fs-2 text-body-secondary"></i><h3 class="h6 mt-3 mb-0">ไม่พบรายการในกลุ่มนี้</h3></div><?php endif; ?>
    </section>
</div>
