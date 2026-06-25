<?php
use yii\helpers\Html;

/** @var array $revisions */
/** @var \app\modules\inventoryV2\models\StockOrder $model */

if (empty($revisions)) {
    return;
}

$indexBy = function ($rows) {
    $out = [];
    foreach ($rows as $r) {
        $out[$r['item_code']] = (float) $r['qty'];
    }
    return $out;
};

$nameCache = [];
$lookupName = function ($code) use (&$nameCache) {
    if (isset($nameCache[$code])) {
        return $nameCache[$code];
    }
    $item = \app\modules\inventoryV2\models\StockItem::findOne(['code' => $code]);
    $nameCache[$code] = $item ? ($item->item_name ?? $code) : $code;
    return $nameCache[$code];
};
?>

<div class="ae-revisions card border-0 shadow-sm mb-3">
    <button class="ae-revisions__head btn btn-link text-decoration-none w-100 d-flex justify-content-between align-items-center px-3 py-2"
            type="button" data-bs-toggle="collapse" data-bs-target="#ae-revisions-body" aria-expanded="false">
        <span class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-warning"></i>
            <span class="fw-semibold text-body">ประวัติการปรับโดยผู้อนุมัติ</span>
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill"><?= count($revisions) ?> ครั้ง</span>
        </span>
        <i class="bi bi-chevron-down ae-revisions__chev"></i>
    </button>
    <div class="collapse" id="ae-revisions-body">
        <div class="px-3 pb-3">
            <?php foreach ($revisions as $i => $rev): ?>
                <?php
                    $before = $indexBy($rev['before'] ?? []);
                    $after = $indexBy($rev['after'] ?? []);
                    $allCodes = array_unique(array_merge(array_keys($before), array_keys($after)));
                    sort($allCodes);
                    $atFormatted = !empty($rev['at']) ? \app\components\ThaiDateHelper::formatThaiDate($rev['at']) : '-';
                ?>
                <div class="ae-rev-card border rounded-3 p-3 mb-2 bg-light bg-opacity-50">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="small">
                            <i class="bi bi-person-check me-1 text-muted"></i>
                            <span class="fw-semibold"><?= Html::encode($rev['by_name'] ?: '—') ?></span>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-calendar3 me-1"></i><?= Html::encode($atFormatted) ?>
                        </div>
                    </div>
                    <table class="table table-sm mb-0 align-middle ae-rev-table">
                        <thead>
                            <tr class="small text-muted">
                                <th>รายการ</th>
                                <th class="text-end" style="width:120px">ก่อน</th>
                                <th class="text-center" style="width:40px"></th>
                                <th class="text-end" style="width:120px">หลัง</th>
                                <th class="text-end" style="width:120px">เปลี่ยนแปลง</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allCodes as $code): ?>
                                <?php
                                    $b = $before[$code] ?? null;
                                    $a = $after[$code] ?? null;
                                    $delta = ($a ?? 0) - ($b ?? 0);
                                    $rowCls = '';
                                    if ($b === null) $rowCls = 'ae-rev-added';
                                    elseif ($a === null) $rowCls = 'ae-rev-removed';
                                    elseif (abs($delta) > 0.001) $rowCls = 'ae-rev-changed';
                                ?>
                                <tr class="<?= $rowCls ?>">
                                    <td><span class="small font-monospace text-muted"><?= Html::encode($code) ?></span> <?= Html::encode($lookupName($code)) ?></td>
                                    <td class="text-end" style="font-variant-numeric: tabular-nums">
                                        <?= $b === null ? '<span class="text-muted">—</span>' : number_format($b, 2) ?>
                                    </td>
                                    <td class="text-center text-muted"><i class="bi bi-arrow-right"></i></td>
                                    <td class="text-end" style="font-variant-numeric: tabular-nums">
                                        <?= $a === null ? '<span class="text-muted">—</span>' : number_format($a, 2) ?>
                                    </td>
                                    <td class="text-end" style="font-variant-numeric: tabular-nums">
                                        <?php if ($b === null): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill">+ เพิ่ม</span>
                                        <?php elseif ($a === null): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill">− ลบ</span>
                                        <?php elseif (abs($delta) < 0.001): ?>
                                            <span class="text-muted small">—</span>
                                        <?php else: ?>
                                            <span class="<?= $delta > 0 ? 'text-success' : 'text-warning' ?> fw-semibold">
                                                <?= ($delta > 0 ? '+' : '') . number_format($delta, 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
$this->registerCss(<<<CSS
.ae-revisions__head { color: #1a202c; }
.ae-revisions__head[aria-expanded="true"] .ae-revisions__chev { transform: rotate(180deg); }
.ae-revisions__chev { transition: transform 180ms cubic-bezier(0.16, 1, 0.3, 1); }
.ae-rev-table thead th { font-weight: 600; border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
.ae-rev-table tr.ae-rev-added td { background: rgba(21, 128, 61, 0.05); }
.ae-rev-table tr.ae-rev-removed td { background: rgba(185, 28, 28, 0.05); }
.ae-rev-table tr.ae-rev-changed td { background: rgba(180, 83, 9, 0.04); }
@media (prefers-reduced-motion: reduce) {
    .ae-revisions__chev { transition: none; }
}
CSS
);
