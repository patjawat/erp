<?php

use PhpParser\Node\Expr\Print_;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Dashboard คลังหลัก';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$stats = $stats ?? [
    'pending_count' => 0,
    'critical_count' => 0,
    'total_value' => 0,
    'lots_count' => 0,
    'items_with_stock' => 0,
    'insufficient_to_disburse_count' => 0,
];
$warehouses = $warehouses ?? [];
$pendingRequisitions = $pendingRequisitions ?? [];
$chartData = $chartData ?? ['mode' => 'year', 'year' => (int) date('Y') + 543, 'month' => null, 'direction' => 'IN', 'months' => [], 'series' => [], 'totals' => [], 'total' => 0, 'is_empty' => true];
$defaultYear = $defaultYear ?? ((int) date('Y') + 543);
$yearOptions = $yearOptions ?? [$defaultYear];
$currentWarehouseId = $currentWarehouseId ?? null;
$warehouseIdParam = $currentWarehouseId !== null ? (int)$currentWarehouseId : 'all';

$currentWarehouseName = 'ทั้งหมด';
if ($currentWarehouseId && $warehouses) {
    foreach ($warehouses as $w) {
        if ((int)$w->id === (int)$currentWarehouseId) {
            $currentWarehouseName = $w->warehouse_name;
            break;
        }
    }
}

$this->registerJsFile('https://cdn.jsdelivr.net/npm/apexcharts', ['position' => View::POS_HEAD]);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0"><?= Html::encode($currentWarehouseName) ?> — ภาพรวมสต็อก ใบเบิกรอจ่าย และแนวโน้มรับเข้า-จ่ายออก</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="container-fluid px-3 px-md-4">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-12 col-lg-auto">
            <form method="get" action="<?= Url::to(['/inventory-v2/main-stock/dashboard']) ?>" id="form-warehouse" class="d-inline">
                <label for="warehouseFilter" class="form-label visually-hidden">เลือกคลัง</label>
                <select name="warehouse_id" class="form-select form-select-sm border shadow-sm rounded-pill px-3" id="warehouseFilter" style="min-width: 180px;">
                    <option value="all" <?= $currentWarehouseId === null ? 'selected' : '' ?>>แสดงคลังทั้งหมด</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= (int)$w->id ?>" <?= (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>><?= Html::encode($w->warehouse_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="col-12 col-lg-auto">
            <?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'dashboard']) ?>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php
$pendingCount = (int) $stats['pending_count'];
$insufficientCount = (int) ($stats['insufficient_to_disburse_count'] ?? 0);
$criticalCount = (int) $stats['critical_count'];
$totalValue = (float) $stats['total_value'];

$kpiEndpoints = [
    'pending' => [
        'endpoint' => Url::to(['/inventory-v2/main-stock/pending-requisitions-offcanvas']),
        'full' => Url::to(['/inventory-v2/issue/index']),
        'title' => 'ใบเบิกที่รอจัดของ',
        'count' => $pendingCount,
    ],
    'insufficient' => [
        'endpoint' => Url::to(['/inventory-v2/main-stock/insufficient-offcanvas']),
        'full' => Url::to(['/inventory-v2/report/insufficient-to-disburse']),
        'title' => 'วัสดุไม่พอจ่าย',
        'count' => $insufficientCount,
    ],
    'critical' => [
        'endpoint' => Url::to(['/inventory-v2/main-stock/critical-items-offcanvas']),
        'full' => Url::to(['/inventory-v2/main-stock/balance', 'warehouse_id' => $currentWarehouseId]),
        'title' => 'ต่ำกว่าจุดสั่งซื้อ',
        'count' => $criticalCount,
    ],
    'value' => [
        'endpoint' => Url::to(['/inventory-v2/main-stock/top-value-offcanvas']),
        'full' => Url::to(['/inventory-v2/main-stock/items-with-stock']),
        'title' => 'มูลค่าพัสดุในคลัง',
        'count' => $totalValue > 0 ? 1 : 0, // count > 0 means clickable
    ],
];

$kpiCard = function (string $key, string $tone, string $label, string $value, string $unit, string $cta) use ($kpiEndpoints) {
    $cfg = $kpiEndpoints[$key];
    $isDisabled = $cfg['count'] <= 0;
    $attrs = [
        'class' => 'kpi-card kpi-card--' . $tone . ($isDisabled ? ' is-disabled' : ''),
        'href' => $cfg['full'],
        'data-kpi-key' => $key,
        'data-oc-endpoint' => $cfg['endpoint'],
        'data-oc-title' => $cfg['title'],
        'data-oc-fullpage' => $cfg['full'],
    ];
    if ($isDisabled) {
        $attrs['aria-disabled'] = 'true';
        $attrs['tabindex'] = '-1';
    }
    $attrStr = '';
    foreach ($attrs as $k => $v) $attrStr .= ' ' . $k . '="' . htmlspecialchars((string) $v, ENT_QUOTES) . '"';
    return '<a' . $attrStr . '>'
        . '<div class="kpi-card__body">'
        .   '<p class="kpi-card__label">' . htmlspecialchars($label) . '</p>'
        .   '<div class="kpi-card__value">'
        .     '<span class="kpi-card__num">' . $value . '</span>'
        .     '<span class="kpi-card__unit">' . htmlspecialchars($unit) . '</span>'
        .   '</div>'
        .   '<span class="kpi-card__cta">' . htmlspecialchars($cta) . ' <i class="bi bi-arrow-right" aria-hidden="true"></i></span>'
        . '</div>'
        . '</a>';
};
?>
<div class="container-fluid py-4 px-3 px-md-4 main-stock-dashboard">
    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <?= $kpiCard('pending', 'primary', 'ใบเบิกที่รอการจ่าย', '<span id="kpi-pending">' . $pendingCount . '</span>', 'ฉบับ', 'ดูใบเบิก') ?>
        </div>
        <div class="col-6 col-lg-3">
            <?= $kpiCard('insufficient', 'warning', 'วัสดุไม่พอจ่าย', '<span id="kpi-insufficient">' . $insufficientCount . '</span>', 'รายการ', 'ดูรายการ') ?>
        </div>
        <div class="col-6 col-lg-3">
            <?= $kpiCard('critical', 'danger', 'ต่ำกว่าจุดสั่งซื้อ', '<span id="kpi-critical">' . $criticalCount . '</span>', 'รายการ', 'ดูพัสดุ') ?>
        </div>
        <div class="col-6 col-lg-3">
            <?= $kpiCard('value', 'value', 'มูลค่าพัสดุในคลัง', '<span id="kpi-value">' . number_format($totalValue, 0) . '</span>', '฿', 'ดูมูลค่า') ?>
        </div>
    </div>

    <div class="row g-3">
        <!-- แนวโน้มการเคลื่อนไหวมูลค่าคลัง -->
        <div class="col-lg-8">
            <section class="movement-card h-100" aria-labelledby="movement-card-title">
                <header class="movement-card__head">
                    <h6 id="movement-card-title" class="movement-card__title">
                        <i class="bi bi-bar-chart-line"></i>
                        แนวโน้มการเคลื่อนไหวมูลค่าคลัง
                    </h6>
                    <span class="movement-card__chip" id="chart-badge" title="คลังที่แสดง"><?= Html::encode($currentWarehouseName) ?></span>
                </header>

                <div class="movement-toolbar" role="toolbar" aria-label="ตัวกรองข้อมูลกราฟ">
                    <div class="movement-toolbar__field">
                        <label class="movement-label" for="movementYear">ปี (พ.ศ.)</label>
                        <select id="movementYear" class="movement-select">
                            <?php foreach ($yearOptions as $y): ?>
                                <option value="<?= (int)$y ?>" <?= (int)$y === (int)$defaultYear ? 'selected' : '' ?>><?= (int)$y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="movement-toolbar__field">
                        <span class="movement-label">ทิศทาง</span>
                        <div class="seg-control" role="radiogroup" aria-label="ทิศทางการเคลื่อนไหว" data-seg="direction">
                            <button type="button" class="seg-control__item is-active" data-value="IN" role="radio" aria-checked="true">
                                <i class="bi bi-arrow-down-left-circle"></i><span>รับเข้า</span>
                            </button>
                            <button type="button" class="seg-control__item" data-value="OUT" role="radio" aria-checked="false">
                                <i class="bi bi-arrow-up-right-circle"></i><span>จ่ายออก</span>
                            </button>
                            <button type="button" class="seg-control__item" data-value="NET" role="radio" aria-checked="false">
                                <i class="bi bi-arrow-down-up"></i><span>สุทธิ</span>
                            </button>
                        </div>
                    </div>

                    <div class="movement-toolbar__field movement-toolbar__field--view">
                        <span class="movement-label">มุมมอง</span>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <div class="seg-control" role="radiogroup" aria-label="มุมมองข้อมูล" data-seg="view">
                                <button type="button" class="seg-control__item is-active" data-value="year" role="radio" aria-checked="true">ทั้งปี</button>
                                <button type="button" class="seg-control__item" data-value="month" role="radio" aria-checked="false">รายเดือน</button>
                            </div>
                            <select id="movementMonth" class="movement-select" hidden aria-label="เลือกเดือน">
                                <?php
                                $monthNamesTH = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                                $monthFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                                $currentMonth = (int) date('n');
                                for ($m = 1; $m <= 12; $m++):
                                ?>
                                    <option value="<?= $m ?>" <?= $m === $currentMonth ? 'selected' : '' ?>><?= $monthFull[$m - 1] ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="movement-body">
                    <div class="movement-skeleton" hidden aria-hidden="true">
                        <div class="movement-skeleton__bars">
                            <?php for ($i = 0; $i < 12; $i++): ?>
                                <span class="movement-skeleton__bar" style="--h: <?= 35 + ($i * 5) % 60 ?>%"></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div id="mainWarehouseChart" aria-live="polite" aria-busy="false"></div>
                    <div class="movement-empty" hidden>
                        <div class="movement-empty__icon"><i class="bi bi-bar-chart"></i></div>
                        <p class="movement-empty__title">ไม่มีข้อมูลในช่วงที่เลือก</p>
                        <p class="movement-empty__caption">ลองเปลี่ยนปี เดือน หรือทิศทาง</p>
                    </div>
                </div>

                <footer class="movement-legend" aria-live="polite">
                    <div class="movement-legend__chips" id="movementTotalsChips"></div>
                    <div class="movement-legend__total">
                        <span class="movement-legend__total-label">รวม</span>
                        <span class="movement-legend__total-value" id="movementGrandTotal">0</span>
                        <span class="movement-legend__total-unit">บาท</span>
                    </div>
                </footer>

                <p class="movement-hint" id="movementHint">
                    <i class="bi bi-hand-index-thumb"></i>
                    <span>คลิกแท่งกราฟเพื่อดูรายการพัสดุของประเภทนั้น</span>
                </p>
            </section>
        </div>

        <!-- รายการเบิกที่รอจัดของ -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="text-white mb-0 small fw-normal"><i class="bi bi-box-seam me-1"></i>รายการเบิกที่รอจัดของ</h6>
                    <span class="badge text-bg-light text-dark"><?= count($pendingRequisitions) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($pendingRequisitions)): ?>
                            <div class="list-group-item border-0 p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-2"></i>
                                <p class="mb-0 mt-2 small">ไม่มีใบขอเบิกรอดำเนินการ</p>
                            </div>
                        <?php else: ?>
                            <?php
                            foreach ($pendingRequisitions as $req): ?>
                                <?php
                                $subName = $req->subWarehouse ? $req->subWarehouse->warehouse_name : 'ไม่ระบุ';
                                $detailCount = is_array($req->stockDetails) ? count($req->stockDetails) : (method_exists($req, 'getStockDetails') ? $req->getStockDetails()->count() : 0);
                                $timeAgo = $req->order_date ? Yii::$app->formatter->asRelativeTime(strtotime($req->order_date)) : '';
                                $statusInfo = \app\modules\inventoryV2\models\StockOrder::getStatusBadgeConfigFor($req->status);
                                $statusIcon = !empty($statusInfo['icon']) ? '<i data-lucide="' . Html::encode($statusInfo['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>' : '';
                                ?>
                                <div class="list-group-item border-0 border-start border-3 border-primary px-3 py-2 pending-item">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-1 flex-wrap mb-1">
                                                <span class="badge text-bg-primary"><?= Html::encode($req->order_no) ?></span>
                                                <span class="<?= $statusInfo['class'] ?>"><?= $statusIcon . Html::encode($statusInfo['label']) ?></span>
                                            </div>
                                            <div class="fw-bold text-truncate"><?= Html::encode($subName) ?></div>
                                            <small class="text-muted"><?= $detailCount ?> รายการ · <?= $timeAgo ?></small>
                                        </div>
                                        <?= Html::a('จัดของจ่าย', ['/inventory-v2/issue/process', 'id' => $req->id], ['class' => 'btn btn-primary btn-sm rounded-pill flex-shrink-0']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 py-2 text-center">
                    <a href="<?= Url::to(['/inventory-v2/issue/index']) ?>" class="small text-muted text-decoration-none">ดูรายการค้างจ่ายทั้งหมด <i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── KPI Offcanvas (shared shell) ─── -->
    <div class="offcanvas offcanvas-end kpi-oc" tabindex="-1" id="kpiOffcanvas" aria-labelledby="kpiOffcanvasTitle">
        <header class="kpi-oc__head">
            <div class="kpi-oc__head-meta">
                <h6 class="kpi-oc__title" id="kpiOffcanvasTitle">รายการ</h6>
                <p class="kpi-oc__sub">
                    <span id="kpiOffcanvasCount">0</span>
                    <span class="kpi-oc__sub-sep" aria-hidden="true">·</span>
                    <span id="kpiOffcanvasWarehouse"><?= Html::encode($currentWarehouseName) ?></span>
                </p>
            </div>
            <button type="button" class="kpi-oc__close" data-bs-dismiss="offcanvas" aria-label="ปิด">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </header>
        <div class="kpi-oc__content" id="kpiOffcanvasContent" aria-live="polite">
            <!-- AJAX content injected here -->
        </div>
    </div>

    <!-- ─── Movement drill-down modal ─── -->
    <div class="modal fade movement-items-modal" id="movementItemsModal" tabindex="-1" aria-labelledby="movementItemsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
            <div class="modal-content movement-items-modal__content">
                <header class="movement-items-modal__head">
                    <div class="movement-items-modal__head-row">
                        <span class="movement-items-modal__dir-badge" id="movementItemsDirBadge" data-direction="IN">
                            <i class="bi bi-arrow-down-left-circle" aria-hidden="true"></i>
                            <span id="movementItemsDirLabel">รับเข้า</span>
                        </span>
                        <button type="button" class="movement-items-modal__close" data-bs-dismiss="modal" aria-label="ปิด">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <h5 id="movementItemsModalTitle" class="movement-items-modal__title">
                        <span class="movement-items-modal__cat-dot" id="movementItemsCatDot" aria-hidden="true"></span>
                        <span id="movementItemsCatName">ประเภทพัสดุ</span>
                    </h5>
                    <p class="movement-items-modal__caption">
                        <span id="movementItemsPeriod">—</span>
                        <span class="movement-items-modal__caption-sep" aria-hidden="true">·</span>
                        <span id="movementItemsWarehouse">—</span>
                    </p>
                </header>

                <div class="movement-items-modal__summary" id="movementItemsSummary" hidden>
                    <div class="movement-items-modal__sum-cell">
                        <span class="movement-items-modal__sum-label">จำนวนพัสดุ</span>
                        <span class="movement-items-modal__sum-value" id="movementItemsCount">0</span>
                        <span class="movement-items-modal__sum-unit">ชนิด</span>
                    </div>
                    <div class="movement-items-modal__sum-divider" aria-hidden="true"></div>
                    <div class="movement-items-modal__sum-cell movement-items-modal__sum-cell--total">
                        <span class="movement-items-modal__sum-label">มูลค่ารวม</span>
                        <span class="movement-items-modal__sum-value" id="movementItemsTotal">0.00</span>
                        <span class="movement-items-modal__sum-unit">บาท</span>
                    </div>
                </div>

                <div class="movement-items-modal__body" id="movementItemsBody" aria-live="polite" aria-busy="false">
                    <!-- content injected by JS -->
                </div>

                <footer class="movement-items-modal__foot">
                    <span class="movement-items-modal__foot-hint">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        <span id="movementItemsFootNote">มูลค่าจ่ายออกอ้างราคาทุนตามล็อตที่บันทึก</span>
                    </span>
                    <button type="button" class="btn-light btn-sm rounded-pill px-3" data-bs-dismiss="modal">ปิด</button>
                </footer>
            </div>
        </div>
    </div>
</div>



<style>
.main-stock-dashboard {
    --ink-1: #1a202c; --ink-2: #4a5568; --ink-3: #718096; --ink-4: #a0aec0;
    --surface: #ffffff; --surface-2: #f7f9fc; --surface-3: #eef2f7; --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08); --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd; --primary-ink: #0a58ca; --primary-soft: rgba(13, 110, 253, 0.08); --primary-line: rgba(13, 110, 253, 0.22);
    --success: #15803d; --warning: #b45309; --danger: #b91c1c;
    --radius: 10px; --radius-sm: 8px; --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15,23,42,0.04), 0 1px 1px rgba(15,23,42,0.03);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms; --t-mid: 180ms;
}

/* ─── KPI cards (unified) ─── */
.main-stock-dashboard .kpi-card {
    display: block;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    height: 100%;
    text-decoration: none;
    color: var(--ink-1);
    overflow: hidden;
    position: relative;
    transition: transform var(--t-mid) var(--ease), box-shadow var(--t-mid) var(--ease), border-color var(--t-fast) var(--ease);
}
.main-stock-dashboard .kpi-card::before {
    content: '';
    position: absolute; left: 0; right: 0; top: 0;
    height: 3px;
    background: var(--kpi-accent, var(--primary));
}
.main-stock-dashboard .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(15,23,42,0.08), 0 2px 4px rgba(15,23,42,0.04);
    color: var(--ink-1);
    text-decoration: none;
    border-color: var(--line-strong);
}
.main-stock-dashboard .kpi-card:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-soft), var(--shadow-1);
    border-color: var(--primary);
}
.main-stock-dashboard .kpi-card.is-disabled {
    pointer-events: none;
    opacity: 0.6;
}
.main-stock-dashboard .kpi-card.is-disabled .kpi-card__cta { color: var(--ink-3); }
.main-stock-dashboard .kpi-card__body { padding: 0.95rem 1rem 0.85rem; }
.main-stock-dashboard .kpi-card__label {
    margin: 0 0 0.35rem;
    font-size: 0.78rem; font-weight: 600;
    color: var(--ink-3);
    line-height: 1.2;
}
.main-stock-dashboard .kpi-card__value {
    display: flex; align-items: baseline; gap: 0.4rem;
    line-height: 1.1;
}
.main-stock-dashboard .kpi-card__num {
    font-size: 1.65rem; font-weight: 700;
    color: var(--kpi-num, var(--ink-1));
    font-variant-numeric: tabular-nums;
}
.main-stock-dashboard .kpi-card__unit { font-size: 0.78rem; color: var(--ink-3); }
.main-stock-dashboard .kpi-card__cta {
    display: inline-flex; align-items: center; gap: 0.3rem;
    margin-top: 0.6rem;
    font-size: 0.78rem; font-weight: 600;
    color: var(--kpi-cta, var(--primary-ink));
}
.main-stock-dashboard .kpi-card__cta i {
    transition: transform var(--t-fast) var(--ease);
}
.main-stock-dashboard .kpi-card:hover .kpi-card__cta i { transform: translateX(2px); }

/* Tones — top accent + number + cta only */
.main-stock-dashboard .kpi-card--primary  { --kpi-accent: var(--primary);  --kpi-num: var(--primary-ink);  --kpi-cta: var(--primary-ink); }
.main-stock-dashboard .kpi-card--warning  { --kpi-accent: var(--warning);  --kpi-num: var(--warning);      --kpi-cta: var(--warning); }
.main-stock-dashboard .kpi-card--danger   { --kpi-accent: var(--danger);   --kpi-num: var(--danger);       --kpi-cta: var(--danger); }
.main-stock-dashboard .kpi-card--value    { --kpi-accent: #1a202c;         --kpi-num: var(--ink-1);        --kpi-cta: var(--ink-2); }
.main-stock-dashboard .pending-item { transition: background-color var(--t-fast) var(--ease); }
.main-stock-dashboard .pending-item:hover { background-color: rgba(13, 110, 253, 0.04); }
.main-stock-dashboard #warehouseFilter:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

/* ─── Movement card ─── */
.main-stock-dashboard .movement-card {
    display: flex; flex-direction: column;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    overflow: hidden;
    color: var(--ink-1);
}
.main-stock-dashboard .movement-card__head {
    display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
    padding: 0.85rem 1rem; border-bottom: 1px solid var(--line);
    background: var(--surface);
}
.main-stock-dashboard .movement-card__title {
    display: flex; align-items: center; gap: 0.5rem;
    margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--ink-1);
    line-height: 1.2;
}
.main-stock-dashboard .movement-card__title i { color: var(--primary); font-size: 1.05rem; }
.main-stock-dashboard .movement-card__chip {
    display: inline-flex; align-items: center; gap: 0.3rem;
    background: var(--surface-3); color: var(--ink-2);
    font-size: 0.72rem; font-weight: 600;
    padding: 0.25rem 0.6rem; border-radius: 999px;
    max-width: 12rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* ─── Toolbar ─── */
.main-stock-dashboard .movement-toolbar {
    display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: flex-end;
    padding: 0.75rem 1rem; border-bottom: 1px solid var(--line);
    background: var(--surface-2);
}
.main-stock-dashboard .movement-toolbar__field { display: flex; flex-direction: column; gap: 0.35rem; }
.main-stock-dashboard .movement-label {
    font-size: 0.75rem; font-weight: 600; color: var(--ink-3);
    line-height: 1; display: block;
}
.main-stock-dashboard .movement-select {
    appearance: none;
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    padding: 0.4rem 1.85rem 0.4rem 0.7rem;
    min-height: 36px; min-width: 110px;
    font-size: 0.85rem; color: var(--ink-1);
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='%234a5568'><path d='M3.205 5.795l4.795 4.795 4.795-4.795-1.41-1.41-3.385 3.385-3.385-3.385z'/></svg>");
    background-repeat: no-repeat; background-position: right 0.55rem center; background-size: 0.7rem;
    transition: border-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
    cursor: pointer;
}
.main-stock-dashboard .movement-select:hover { border-color: var(--primary-line); }
.main-stock-dashboard .movement-select:focus-visible {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.main-stock-dashboard .movement-select:disabled { opacity: 0.55; cursor: not-allowed; }

/* ─── Segmented control ─── */
.main-stock-dashboard .seg-control {
    display: inline-flex; background: var(--surface);
    border: 1px solid var(--line-strong); border-radius: var(--radius-sm);
    padding: 3px;
}
.main-stock-dashboard .seg-control__item {
    display: inline-flex; align-items: center; gap: 0.35rem;
    background: transparent; border: 0; cursor: pointer;
    padding: 0.4rem 0.75rem; min-height: 30px;
    font-size: 0.82rem; font-weight: 600; color: var(--ink-2);
    border-radius: calc(var(--radius-sm) - 2px);
    transition: background var(--t-fast) var(--ease), color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
    white-space: nowrap;
}
.main-stock-dashboard .seg-control__item i { font-size: 0.95rem; }
.main-stock-dashboard .seg-control__item:hover:not(.is-active) {
    background: var(--surface-hover); color: var(--ink-1);
}
.main-stock-dashboard .seg-control__item.is-active {
    background: var(--surface); color: var(--primary-ink);
    box-shadow: 0 0 0 1px var(--primary-line), 0 1px 2px rgba(13, 110, 253, 0.12);
}
.main-stock-dashboard .seg-control__item:focus-visible {
    outline: none; box-shadow: 0 0 0 3px var(--primary-soft);
}
.main-stock-dashboard .seg-control__item:disabled { opacity: 0.5; cursor: not-allowed; }

/* ─── Chart body ─── */
.main-stock-dashboard .movement-body {
    position: relative;
    padding: 0.75rem 0.5rem 0.25rem;
    min-height: 320px;
}
.main-stock-dashboard #mainWarehouseChart {
    min-height: 320px;
    transition: opacity var(--t-mid) var(--ease);
}
.main-stock-dashboard .movement-body[data-loading="true"] #mainWarehouseChart { opacity: 0.35; pointer-events: none; }

/* Skeleton */
.main-stock-dashboard .movement-skeleton {
    position: absolute; inset: 0.75rem 0.5rem 0.25rem;
    display: flex; align-items: flex-end; justify-content: center;
    padding: 1.5rem 1.5rem 3rem;
    pointer-events: none;
}
.main-stock-dashboard .movement-skeleton__bars {
    display: flex; align-items: flex-end; gap: 0.5rem;
    width: 100%; height: 100%;
}
.main-stock-dashboard .movement-skeleton__bar {
    flex: 1; height: var(--h, 40%);
    background: linear-gradient(180deg, var(--surface-3) 0%, var(--surface-2) 100%);
    border-radius: var(--radius-xs) var(--radius-xs) 0 0;
    animation: movement-pulse 1.4s var(--ease) infinite;
}
.main-stock-dashboard .movement-skeleton__bar:nth-child(2n) { animation-delay: 0.1s; }
.main-stock-dashboard .movement-skeleton__bar:nth-child(3n) { animation-delay: 0.2s; }
@keyframes movement-pulse {
    0%, 100% { opacity: 0.55; }
    50% { opacity: 0.95; }
}

/* Empty state */
.main-stock-dashboard .movement-empty {
    position: absolute; inset: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.5rem; padding: 2rem 1rem; text-align: center;
}
.main-stock-dashboard .movement-empty__icon {
    width: 56px; height: 56px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-3); color: var(--ink-3);
    border-radius: 14px; font-size: 1.5rem;
}
.main-stock-dashboard .movement-empty__title { margin: 0.25rem 0 0; font-size: 0.95rem; font-weight: 600; color: var(--ink-2); }
.main-stock-dashboard .movement-empty__caption { margin: 0; font-size: 0.8rem; color: var(--ink-3); }

/* ─── Legend / totals ─── */
.main-stock-dashboard .movement-legend {
    padding: 0.85rem 1rem;
    border-top: 1px solid var(--line);
    background: var(--surface-2);
    display: flex; flex-wrap: wrap; gap: 0.85rem; align-items: center; justify-content: space-between;
}
.main-stock-dashboard .movement-legend__chips {
    display: flex; flex-wrap: wrap; gap: 0.4rem; flex: 1; min-width: 0;
}
.main-stock-dashboard .movement-legend__chip {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--surface); border: 1px solid var(--line);
    padding: 0.3rem 0.6rem; border-radius: 999px;
    font-size: 0.78rem; color: var(--ink-1);
    transition: border-color var(--t-fast) var(--ease), background var(--t-fast) var(--ease);
    cursor: default;
}
.main-stock-dashboard .movement-legend__chip:hover { border-color: var(--line-strong); background: var(--surface-hover); }
.main-stock-dashboard .movement-legend__dot {
    width: 9px; height: 9px; border-radius: 50%; display: inline-block; flex-shrink: 0;
}
.main-stock-dashboard .movement-legend__chip-name { font-weight: 600; color: var(--ink-1); }
.main-stock-dashboard .movement-legend__chip-value { color: var(--ink-2); font-variant-numeric: tabular-nums; }
.main-stock-dashboard .movement-legend__chip-pct { color: var(--ink-3); font-size: 0.72rem; font-variant-numeric: tabular-nums; }
.main-stock-dashboard .movement-legend__total { display: inline-flex; align-items: baseline; gap: 0.45rem; flex-shrink: 0; }
.main-stock-dashboard .movement-legend__total-label { font-size: 0.78rem; color: var(--ink-3); font-weight: 600; }
.main-stock-dashboard .movement-legend__total-value { font-size: 1.25rem; font-weight: 700; color: var(--ink-1); font-variant-numeric: tabular-nums; line-height: 1; }
.main-stock-dashboard .movement-legend__total-unit { font-size: 0.78rem; color: var(--ink-3); }

/* Mobile */
@media (max-width: 575.98px) {
    .main-stock-dashboard .movement-toolbar { gap: 0.85rem; padding: 0.75rem; }
    .main-stock-dashboard .movement-toolbar__field { width: 100%; }
    .main-stock-dashboard .movement-toolbar__field .seg-control,
    .main-stock-dashboard .movement-select { width: 100%; }
    .main-stock-dashboard .seg-control__item { flex: 1; justify-content: center; padding: 0.4rem 0.5rem; }
    .main-stock-dashboard .movement-legend { flex-direction: column; align-items: flex-start; }
}

/* ─── Click affordance hint ─── */
.main-stock-dashboard .movement-hint {
    margin: 0;
    padding: 0.55rem 1rem 0.7rem;
    display: inline-flex; align-items: center; gap: 0.4rem;
    font-size: 0.74rem; color: var(--ink-3);
    line-height: 1.25;
}
.main-stock-dashboard .movement-hint i { color: var(--primary); font-size: 0.85rem; }

/* Chart bars feel clickable */
.main-stock-dashboard #mainWarehouseChart .apexcharts-bar-series .apexcharts-bar-area { cursor: pointer; }
.main-stock-dashboard #mainWarehouseChart .apexcharts-bar-area { transition: filter var(--t-fast) var(--ease); }
.main-stock-dashboard #mainWarehouseChart .apexcharts-bar-area:hover { filter: brightness(1.08); }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .main-stock-dashboard .movement-skeleton__bar,
    .main-stock-dashboard .kpi-card-link,
    .main-stock-dashboard .pending-item,
    .main-stock-dashboard .seg-control__item,
    .main-stock-dashboard .movement-select,
    .main-stock-dashboard .movement-legend__chip,
    .main-stock-dashboard #mainWarehouseChart { transition: none !important; animation: none !important; }
}

/* ═══════════════ KPI Offcanvas ═══════════════ */
.kpi-oc {
    --ink-1: #1a202c; --ink-2: #4a5568; --ink-3: #718096; --ink-4: #a0aec0;
    --surface: #ffffff; --surface-2: #f7f9fc; --surface-3: #eef2f7; --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08); --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd; --primary-ink: #0a58ca; --primary-soft: rgba(13, 110, 253, 0.08);
    --success: #15803d; --warning: #b45309; --danger: #b91c1c;
    --radius: 10px; --radius-sm: 8px; --radius-xs: 6px;
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms; --t-mid: 180ms; --t-slow: 240ms;
    width: 460px !important;
    max-width: 100vw;
    border: 0;
    box-shadow: -8px 0 30px rgba(15,23,42,0.10);
    transition: transform var(--t-slow) var(--ease) !important;
}
@media (max-width: 575.98px) {
    .kpi-oc { width: 100vw !important; }
}

/* Head */
.kpi-oc__head {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem;
    padding: 1rem 1.1rem;
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
}
.kpi-oc__head-meta { min-width: 0; flex: 1; }
.kpi-oc__title {
    margin: 0; font-size: 1rem; font-weight: 700; color: var(--ink-1);
    line-height: 1.25;
}
.kpi-oc__sub {
    margin: 0.2rem 0 0;
    font-size: 0.76rem; color: var(--ink-3);
    display: inline-flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;
}
.kpi-oc__sub-sep { color: var(--ink-4); }
.kpi-oc__close {
    appearance: none; background: transparent; border: 0; cursor: pointer;
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 6px; color: var(--ink-3);
    flex-shrink: 0;
    transition: background var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
}
.kpi-oc__close:hover { background: var(--surface-hover); color: var(--ink-1); }
.kpi-oc__close:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--primary-soft); color: var(--ink-1); }

/* Content body */
.kpi-oc__content {
    flex: 1; min-height: 0;
    display: flex; flex-direction: column;
    overflow: hidden;
    background: var(--surface);
}

/* Search bar */
.kpi-oc__bar {
    padding: 0.7rem 1rem;
    background: var(--surface);
    border-bottom: 1px solid var(--line);
    flex-shrink: 0;
}
.kpi-oc__search { position: relative; }
.search-input-wrap.kpi-oc__search .search-input__icon {
    position: absolute; left: 0.65rem; top: 50%;
    transform: translateY(-50%);
    color: var(--ink-4); font-size: 0.85rem;
    pointer-events: none;
}
.search-input-wrap.kpi-oc__search .form-control-sm {
    padding-left: 2rem !important;
    background: var(--surface-2);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    transition: border-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
}
.search-input-wrap.kpi-oc__search .form-control-sm:focus {
    background: var(--surface);
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}

/* List */
.kpi-oc__list {
    list-style: none; margin: 0; padding: 0.35rem 0;
    flex: 1; min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
}
.kpi-oc__row {
    padding: 0 0.5rem;
}
.kpi-oc__row + .kpi-oc__row {
    border-top: 1px solid rgba(15, 23, 42, 0.04);
}
.kpi-oc__row-link {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.65rem 0.55rem;
    color: var(--ink-1); text-decoration: none;
    border-radius: 8px;
    transition: background var(--t-fast) var(--ease);
}
a.kpi-oc__row-link:hover { background: var(--surface-hover); color: var(--ink-1); text-decoration: none; }
a.kpi-oc__row-link:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--primary-soft); background: var(--surface-hover); }
.kpi-oc__row-rank {
    flex-shrink: 0;
    width: 24px; text-align: right;
    font-size: 0.78rem; font-variant-numeric: tabular-nums;
    color: var(--ink-3); font-weight: 600;
}
.kpi-oc__row-icon, .kpi-oc__row-thumb {
    flex-shrink: 0;
    width: 38px; height: 38px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-3); color: var(--ink-3);
    border: 1px solid var(--line);
    border-radius: 8px;
    overflow: hidden;
    font-size: 1rem;
}
.kpi-oc__row-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.kpi-oc__row-thumb.is-empty { background: var(--surface-2); }
.kpi-oc__row-icon { background: var(--primary-soft); color: var(--primary-ink); }
.kpi-oc__row-main { flex: 1; min-width: 0; }
.kpi-oc__row-title {
    display: flex; align-items: center; gap: 0.4rem; flex-wrap: nowrap;
    color: var(--ink-1); font-weight: 600;
    font-size: 0.86rem; line-height: 1.3;
    overflow: hidden;
}
.kpi-oc__row-code {
    font-family: ui-monospace, "SFMono-Regular", Menlo, monospace;
    font-size: 0.75rem; color: var(--ink-2);
    background: var(--surface-3);
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    flex-shrink: 0;
}
.kpi-oc__row-name {
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    min-width: 0;
}
.kpi-oc__row-sep { color: var(--ink-4); }
.kpi-oc__row-meta {
    margin-top: 0.15rem;
    font-size: 0.74rem; color: var(--ink-3);
    display: inline-flex; align-items: center; gap: 0.3rem; flex-wrap: wrap;
}
.kpi-oc__row-fig {
    flex-shrink: 0; text-align: right;
    display: flex; flex-direction: column; align-items: flex-end; gap: 0.05rem;
    min-width: 5rem;
}
.kpi-oc__row-value {
    font-size: 0.95rem; font-weight: 700; color: var(--ink-1);
    font-variant-numeric: tabular-nums; line-height: 1.1;
}
.kpi-oc__row-value--danger { color: var(--danger); }
.kpi-oc__row-unit { font-size: 0.7rem; color: var(--ink-3); }
.kpi-oc__row-action {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 0.3rem;
    color: var(--primary-ink); font-size: 0.78rem; font-weight: 600;
}
.kpi-oc__row-cta { display: inline-block; }
@media (max-width: 575.98px) {
    .kpi-oc__row-cta { display: none; }
}

/* Empty state (uses PRODUCT.md vocab) */
.kpi-oc__content .empty-block {
    flex: 1;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center;
    padding: 2.25rem 1.5rem;
    gap: 0.4rem;
    color: var(--ink-3);
}
.kpi-oc__content .empty-block i {
    width: 56px; height: 56px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-3); color: var(--ink-3);
    border-radius: 14px; font-size: 1.5rem;
    margin-bottom: 0.4rem;
}
.kpi-oc__content .empty-block__title {
    margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--ink-2);
}
.kpi-oc__content .empty-block__caption {
    margin: 0; font-size: 0.8rem; color: var(--ink-3);
    max-width: 32ch;
}

/* Loading skeleton (injected by JS) */
.kpi-oc__skeleton {
    flex: 1;
    padding: 0.5rem 0.75rem;
}
.kpi-oc__skel-row {
    display: flex; align-items: center; gap: 0.7rem;
    padding: 0.65rem 0.55rem;
    border-bottom: 1px solid rgba(15, 23, 42, 0.04);
}
.kpi-oc__skel-block {
    background: linear-gradient(90deg, var(--surface-3) 0%, var(--surface-2) 50%, var(--surface-3) 100%);
    background-size: 200% 100%;
    border-radius: 4px;
    animation: kpi-oc-shimmer 1.4s linear infinite;
}
.kpi-oc__skel-thumb { width: 38px; height: 38px; border-radius: 8px; flex-shrink: 0; }
.kpi-oc__skel-main { flex: 1; height: 32px; }
.kpi-oc__skel-fig { width: 64px; height: 32px; flex-shrink: 0; }
@keyframes kpi-oc-shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

/* Error state */
.kpi-oc__error {
    flex: 1;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center;
    padding: 2rem 1.5rem;
    gap: 0.4rem;
}
.kpi-oc__error i {
    width: 56px; height: 56px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(185, 28, 28, 0.10); color: var(--danger);
    border-radius: 14px; font-size: 1.5rem;
    margin-bottom: 0.4rem;
}
.kpi-oc__error-title { margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--ink-2); }
.kpi-oc__error-caption { margin: 0; font-size: 0.8rem; color: var(--ink-3); }
.kpi-oc__retry {
    margin-top: 0.5rem;
    appearance: none;
    background: var(--surface); border: 1px solid var(--line-strong);
    color: var(--ink-1); font-size: 0.82rem; font-weight: 600;
    padding: 0.4rem 0.85rem; border-radius: var(--radius-sm);
    cursor: pointer;
    transition: background var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.kpi-oc__retry:hover { background: var(--surface-hover); border-color: var(--ink-4); }

/* Foot */
.kpi-oc__foot {
    flex-shrink: 0;
    padding: 0.7rem 1rem;
    background: var(--surface-2);
    border-top: 1px solid var(--line);
    display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
    flex-wrap: wrap;
}
.kpi-oc__foot-meta { font-size: 0.76rem; color: var(--ink-3); }
.kpi-oc__foot-meta strong { color: var(--ink-1); font-weight: 600; font-variant-numeric: tabular-nums; }
.kpi-oc__foot-link {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.8rem; font-weight: 600;
    color: var(--primary-ink); text-decoration: none;
    padding: 0.35rem 0.7rem;
    border-radius: var(--radius-sm);
    background: var(--surface);
    border: 1px solid var(--line-strong);
    transition: background var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.kpi-oc__foot-link:hover { background: var(--surface-hover); border-color: var(--primary-line, rgba(13,110,253,0.22)); color: var(--primary-ink); text-decoration: none; }
.kpi-oc__foot-link i { transition: transform var(--t-fast) var(--ease); }
.kpi-oc__foot-link:hover i { transform: translateX(2px); }

@media (prefers-reduced-motion: reduce) {
    .kpi-oc, .kpi-oc * { transition: none !important; animation: none !important; }
    .kpi-oc__skel-block { animation: none !important; }
}

/* ═══════════════ Movement items modal ═══════════════ */
.movement-items-modal {
    --ink-1: #1a202c; --ink-2: #4a5568; --ink-3: #718096; --ink-4: #a0aec0;
    --surface: #ffffff; --surface-2: #f7f9fc; --surface-3: #eef2f7; --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08); --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd; --primary-ink: #0a58ca; --primary-soft: rgba(13, 110, 253, 0.08);
    --success: #15803d; --success-soft: rgba(21,128,61,0.10);
    --warning: #b45309; --warning-soft: rgba(180,83,9,0.10);
    --danger: #b91c1c; --danger-soft: rgba(185,28,28,0.10);
    --radius: 10px; --radius-sm: 8px; --radius-xs: 6px;
    --shadow-2: 0 18px 50px rgba(15,23,42,0.12), 0 4px 12px rgba(15,23,42,0.06);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms; --t-mid: 180ms;
}
.movement-items-modal .modal-dialog { max-width: 760px; }
.movement-items-modal .movement-items-modal__content {
    border: 0; border-radius: var(--radius);
    box-shadow: var(--shadow-2);
    overflow: hidden;
    background: var(--surface);
    color: var(--ink-1);
}

/* Enter motion */
.movement-items-modal.fade .modal-dialog {
    transform: translateY(8px) scale(0.985);
    opacity: 0;
    transition: transform var(--t-mid) var(--ease), opacity var(--t-mid) var(--ease);
}
.movement-items-modal.show .modal-dialog {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* Head */
.movement-items-modal__head {
    padding: 1rem 1.1rem 0.9rem;
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
    display: flex; flex-direction: column; gap: 0.55rem;
}
.movement-items-modal__head-row {
    display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
}
.movement-items-modal__dir-badge {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    font-size: 0.78rem; font-weight: 600;
    border-radius: 999px;
    border: 1px solid transparent;
}
.movement-items-modal__dir-badge i { font-size: 0.95rem; }
.movement-items-modal__dir-badge[data-direction="IN"] {
    color: var(--success); background: var(--success-soft); border-color: rgba(21,128,61,0.18);
}
.movement-items-modal__dir-badge[data-direction="OUT"] {
    color: var(--warning); background: var(--warning-soft); border-color: rgba(180,83,9,0.18);
}
.movement-items-modal__close {
    appearance: none; background: transparent; border: 0; cursor: pointer;
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 6px; color: var(--ink-3);
    transition: background var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
}
.movement-items-modal__close:hover { background: var(--surface-hover); color: var(--ink-1); }
.movement-items-modal__close:focus-visible {
    outline: none; box-shadow: 0 0 0 3px var(--primary-soft); color: var(--ink-1);
}

.movement-items-modal__title {
    margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--ink-1);
    display: flex; align-items: center; gap: 0.55rem;
    line-height: 1.2; min-width: 0;
}
.movement-items-modal__cat-dot {
    width: 12px; height: 12px; border-radius: 999px; flex-shrink: 0;
    background: var(--ink-4);
    box-shadow: 0 0 0 3px rgba(13,110,253,0.0);
}
.movement-items-modal__caption {
    margin: 0; font-size: 0.78rem; color: var(--ink-3); line-height: 1.3;
    display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;
}
.movement-items-modal__caption-sep { color: var(--ink-4); }

/* Summary strip */
.movement-items-modal__summary {
    display: flex; align-items: stretch;
    padding: 0.85rem 1.1rem;
    background: var(--surface);
    border-bottom: 1px solid var(--line);
    gap: 1rem;
}
.movement-items-modal__sum-cell {
    display: flex; flex-direction: column; gap: 0.15rem; min-width: 0;
}
.movement-items-modal__sum-cell--total { margin-left: auto; align-items: flex-end; text-align: right; }
.movement-items-modal__sum-label { font-size: 0.72rem; font-weight: 600; color: var(--ink-3); }
.movement-items-modal__sum-value {
    font-size: 1.35rem; font-weight: 700; color: var(--ink-1);
    font-variant-numeric: tabular-nums; line-height: 1.1;
}
.movement-items-modal__sum-cell--total .movement-items-modal__sum-value { color: var(--primary-ink); }
.movement-items-modal__sum-unit { font-size: 0.72rem; color: var(--ink-3); }
.movement-items-modal__sum-divider {
    width: 1px; background: var(--line); align-self: stretch;
}

/* Body */
.movement-items-modal__body {
    padding: 0;
    max-height: 60vh;
    overflow-y: auto;
    background: var(--surface);
}

/* Table (desktop) */
.movement-items-modal__table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
}
.movement-items-modal__table thead th {
    position: sticky; top: 0; z-index: 2;
    background: var(--surface-2);
    color: var(--ink-2); font-weight: 600; font-size: 0.74rem;
    text-align: left;
    padding: 0.55rem 1rem;
    border-bottom: 1px solid var(--line);
    white-space: nowrap;
}
.movement-items-modal__table thead th.is-num { text-align: right; }
.movement-items-modal__table tbody td {
    padding: 0.6rem 1rem;
    border-bottom: 1px solid var(--line);
    color: var(--ink-1);
    vertical-align: middle;
}
.movement-items-modal__table tbody tr:last-child td { border-bottom: 0; }
.movement-items-modal__table tbody tr:nth-child(even) td { background: rgba(15,23,42,0.012); }
.movement-items-modal__table tbody tr:hover td { background: var(--surface-hover); }
.movement-items-modal__table td.is-num {
    text-align: right; font-variant-numeric: tabular-nums; color: var(--ink-1);
    white-space: nowrap;
}
.movement-items-modal__table td.is-value { font-weight: 600; }
.movement-items-modal__table .col-idx { color: var(--ink-3); width: 2.25rem; text-align: right; padding-right: 0.5rem; }
.movement-items-modal__table .col-thumb { width: 48px; padding-left: 0.5rem; padding-right: 0.25rem; }
.movement-items-modal__table thead th.col-thumb { padding-left: 0.5rem; padding-right: 0.25rem; }
.movement-items-modal__table .col-code { font-family: ui-monospace, "SFMono-Regular", Menlo, monospace; font-size: 0.78rem; color: var(--ink-2); white-space: nowrap; }
.movement-items-modal__table .col-name { color: var(--ink-1); }
.movement-items-modal__table .col-unit { color: var(--ink-3); white-space: nowrap; }

/* Thumbnail (table + card share base) */
.movement-items-modal__thumb {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: 8px;
    background: var(--surface-3);
    border: 1px solid var(--line);
    overflow: hidden;
    flex-shrink: 0;
    color: var(--ink-4);
    font-size: 1rem;
}
.movement-items-modal__thumb img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.movement-items-modal__thumb.is-empty { background: var(--surface-2); }
.movement-items-modal__thumb--card { width: 44px; height: 44px; border-radius: 10px; }

/* Mobile cards */
.movement-items-modal__cards { display: none; padding: 0.5rem 0.75rem; }
.movement-items-modal__card {
    display: flex; align-items: flex-start; gap: 0.75rem;
    padding: 0.75rem;
    border-bottom: 1px solid var(--line);
}
.movement-items-modal__card:last-child { border-bottom: 0; }
.movement-items-modal__card-idx {
    flex-shrink: 0;
    width: 28px; height: 28px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-3); color: var(--ink-2);
    font-size: 0.74rem; font-weight: 600;
    border-radius: 999px;
    font-variant-numeric: tabular-nums;
}
.movement-items-modal__card-main { flex: 1; min-width: 0; }
.movement-items-modal__card-name {
    font-size: 0.88rem; font-weight: 600; color: var(--ink-1);
    margin-bottom: 0.1rem; line-height: 1.3;
}
.movement-items-modal__card-meta {
    font-size: 0.74rem; color: var(--ink-3);
    font-family: ui-monospace, "SFMono-Regular", Menlo, monospace;
}
.movement-items-modal__card-fig {
    flex-shrink: 0; text-align: right;
    display: flex; flex-direction: column; align-items: flex-end; gap: 0.1rem;
}
.movement-items-modal__card-qty {
    font-size: 0.88rem; font-weight: 600; color: var(--ink-1);
    font-variant-numeric: tabular-nums; line-height: 1.2;
}
.movement-items-modal__card-qty-unit { font-size: 0.7rem; color: var(--ink-3); margin-left: 0.2rem; font-weight: 400; }
.movement-items-modal__card-value {
    font-size: 0.78rem; color: var(--primary-ink);
    font-variant-numeric: tabular-nums; font-weight: 600;
}

/* Skeleton */
.movement-items-modal__skeleton { padding: 0.5rem 0; }
.movement-items-modal__skel-row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.7rem 1rem;
    border-bottom: 1px solid var(--line);
}
.movement-items-modal__skel-block {
    background: linear-gradient(90deg, var(--surface-3) 0%, var(--surface-2) 50%, var(--surface-3) 100%);
    background-size: 200% 100%;
    border-radius: 4px;
    animation: movement-items-shimmer 1.4s linear infinite;
}
.movement-items-modal__skel-idx { width: 24px; height: 14px; }
.movement-items-modal__skel-name { flex: 1; height: 14px; }
.movement-items-modal__skel-num { width: 80px; height: 14px; }
@keyframes movement-items-shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

/* Empty & error */
.movement-items-modal__state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center;
    padding: 2.25rem 1.5rem;
    gap: 0.5rem;
}
.movement-items-modal__state-icon {
    width: 56px; height: 56px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-3); color: var(--ink-3);
    border-radius: 14px; font-size: 1.5rem;
    margin-bottom: 0.25rem;
}
.movement-items-modal__state-icon.is-error { background: var(--danger-soft); color: var(--danger); }
.movement-items-modal__state-title { margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--ink-2); }
.movement-items-modal__state-caption { margin: 0; font-size: 0.8rem; color: var(--ink-3); }
.movement-items-modal__state-action {
    margin-top: 0.5rem;
    appearance: none;
    background: var(--surface); border: 1px solid var(--line-strong);
    color: var(--ink-1); font-size: 0.82rem; font-weight: 600;
    padding: 0.4rem 0.85rem; border-radius: var(--radius-sm);
    cursor: pointer;
    transition: background var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.movement-items-modal__state-action:hover { background: var(--surface-hover); border-color: var(--primary-line, rgba(13,110,253,0.22)); }

/* Foot */
.movement-items-modal__foot {
    padding: 0.7rem 1.1rem;
    background: var(--surface-2);
    border-top: 1px solid var(--line);
    display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
    flex-wrap: wrap;
}
.movement-items-modal__foot-hint {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.74rem; color: var(--ink-3); line-height: 1.3;
    min-width: 0;
}
.movement-items-modal__foot-hint i { color: var(--ink-4); }
.movement-items-modal__foot .btn-light {
    background: var(--surface); border: 1px solid var(--line-strong); color: var(--ink-1);
    transition: background var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.movement-items-modal__foot .btn-light:hover {
    background: var(--surface-hover); border-color: var(--ink-4); color: var(--ink-1);
}

/* Mobile */
@media (max-width: 575.98px) {
    .movement-items-modal .modal-dialog { margin: 0.5rem; }
    .movement-items-modal__head { padding: 0.85rem 0.9rem 0.75rem; }
    .movement-items-modal__title { font-size: 0.98rem; }
    .movement-items-modal__summary { padding: 0.7rem 0.9rem; gap: 0.5rem; }
    .movement-items-modal__sum-value { font-size: 1.15rem; }
    .movement-items-modal__table { display: none; }
    .movement-items-modal__cards { display: block; }
    .movement-items-modal__foot { padding: 0.65rem 0.9rem; }
    .movement-items-modal__foot-hint { flex: 1; }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .movement-items-modal.fade .modal-dialog,
    .movement-items-modal.show .modal-dialog { transition: none !important; transform: none !important; opacity: 1 !important; }
    .movement-items-modal__skel-block { animation: none !important; }
    .movement-items-modal__close { transition: none !important; }
    .main-stock-dashboard #mainWarehouseChart .apexcharts-bar-area { transition: none !important; }
}
</style>

<?php
$initialChartJson = json_encode($chartData, JSON_UNESCAPED_UNICODE);
$movementEndpoint = Url::to(['/inventory-v2/main-stock/movement-chart']);
$itemsEndpoint = Url::to(['/inventory-v2/main-stock/movement-items']);
$warehouseIdForChart = $currentWarehouseId !== null ? (int) $currentWarehouseId : 'all';
$js = <<<JS
$(document).ready(function() {
    var initialData = $initialChartJson;
    var endpoint = '{$movementEndpoint}';
    var itemsEndpoint = '{$itemsEndpoint}';
    var warehouseId = '{$warehouseIdForChart}';
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var state = {
        year: initialData.year,
        month: null,
        direction: initialData.direction || 'IN',
        view: 'year'
    };
    var lastChartData = initialData;
    var lastTriggerEl = null;

    var \$body = $('.movement-body');
    var \$skeleton = \$body.find('.movement-skeleton');
    var \$empty = \$body.find('.movement-empty');
    var \$chart = \$body.find('#mainWarehouseChart');
    var \$totalsChips = $('#movementTotalsChips');
    var \$grandTotal = $('#movementGrandTotal');
    var \$monthSelect = $('#movementMonth');
    var \$yearSelect = $('#movementYear');

    var thbFormatter = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var thbCompact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 });

    function fmt(v) { return thbFormatter.format(v || 0); }
    function fmtCompact(v) {
        if (Math.abs(v) < 1000) return Math.round(v).toLocaleString('th-TH');
        return thbCompact.format(v);
    }

    var chartInstance = null;

    function buildOptions(data) {
        var canDrill = (state.direction === 'IN' || state.direction === 'OUT');
        var chartEvents = canDrill ? {
            dataPointSelection: function(event, ctx, cfg) {
                handleBarClick(data, cfg, event);
            }
        } : {};
        var commonChart = {
            height: 320,
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { enabled: !reducedMotion, easing: 'easeout', speed: 280, animateGradually: { enabled: false } },
            background: 'transparent',
            events: chartEvents
        };
        var commonTheme = {
            grid: { borderColor: 'rgba(15,23,42,0.06)', strokeDashArray: 4, padding: { left: 8, right: 8, top: 0, bottom: 0 } },
            dataLabels: { enabled: false },
            legend: { show: false },
            tooltip: {
                theme: 'light',
                y: { formatter: function(v) { return fmt(v) + ' บาท'; } },
                style: { fontSize: '12px', fontFamily: 'inherit' }
            }
        };
        var colors = (data.series || []).map(function(s) { return s.color; });

        if (data.mode === 'year') {
            return Object.assign({
                chart: Object.assign({ type: 'bar', stacked: true }, commonChart),
                series: (data.series || []).map(function(s) { return { name: s.name, data: s.data }; }),
                xaxis: {
                    categories: data.months,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: '#718096', fontSize: '12px' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#718096', fontSize: '11px' },
                        formatter: function(v) { return fmtCompact(v); }
                    }
                },
                plotOptions: { bar: { borderRadius: 4, borderRadiusApplication: 'end', columnWidth: '58%' } },
                fill: { opacity: 1 },
                colors: colors,
                stroke: { show: false }
            }, commonTheme);
        }

        // month mode → horizontal bar (single bucket per category)
        var monthIdx = (data.month || 1) - 1;
        var rows = (data.series || []).map(function(s) { return { name: s.name, value: (s.data || [])[monthIdx] || 0, color: s.color }; });
        rows.sort(function(a, b) { return Math.abs(b.value) - Math.abs(a.value); });
        return Object.assign({
            chart: Object.assign({ type: 'bar', stacked: false }, commonChart),
            series: [{ name: 'มูลค่า', data: rows.map(function(r) { return r.value; }) }],
            xaxis: {
                categories: rows.map(function(r) { return r.name; }),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#718096', fontSize: '11px' },
                    formatter: function(v) { return fmtCompact(v); }
                }
            },
            yaxis: { labels: { style: { colors: '#1a202c', fontSize: '12px' } } },
            plotOptions: { bar: { borderRadius: 4, borderRadiusApplication: 'end', horizontal: true, distributed: true, barHeight: '68%' } },
            colors: rows.map(function(r) { return r.color; }),
            fill: { opacity: 1 },
            stroke: { show: false }
        }, commonTheme);
    }

    function renderChips(data) {
        \$totalsChips.empty();
        if (!data.totals || data.totals.length === 0) return;
        data.totals.forEach(function(t) {
            var chip = $('<span class="movement-legend__chip"></span>');
            chip.append($('<span class="movement-legend__dot"></span>').css('background', t.color));
            chip.append($('<span class="movement-legend__chip-name"></span>').text(t.name));
            chip.append($('<span class="movement-legend__chip-value"></span>').text(fmt(t.value)));
            if (typeof t.percent === 'number' && t.percent !== 0) {
                chip.append($('<span class="movement-legend__chip-pct"></span>').text(t.percent.toFixed(1) + '%'));
            }
            \$totalsChips.append(chip);
        });
    }

    function render(data) {
        lastChartData = data || lastChartData;
        updateHint();
        if (!data || data.is_empty) {
            \$empty.prop('hidden', false);
            \$chart.attr('aria-busy', 'false');
            if (chartInstance) { try { chartInstance.updateSeries([]); } catch (e) {} }
            renderChips({ totals: [] });
            \$grandTotal.text('0.00');
            return;
        }
        \$empty.prop('hidden', true);
        \$chart.attr('aria-busy', 'false');
        var options = buildOptions(data);
        if (!chartInstance) {
            chartInstance = new ApexCharts(document.querySelector('#mainWarehouseChart'), options);
            chartInstance.render();
        } else {
            chartInstance.updateOptions(options, false, !reducedMotion);
        }
        renderChips(data);
        \$grandTotal.text(fmt(data.total));
    }

    // ─── Hint about click-to-drill ───
    var \$hint = $('#movementHint');
    function updateHint() {
        if (!\$hint.length) return;
        var canDrill = (state.direction === 'IN' || state.direction === 'OUT');
        if (canDrill) {
            \$hint.find('span').text('คลิกแท่งกราฟเพื่อดูรายการพัสดุของประเภทนั้น');
            \$hint.find('i').removeClass('bi-info-circle').addClass('bi-hand-index-thumb');
            \$hint.css('opacity', '');
        } else {
            \$hint.find('span').text('เลือกทิศทาง "รับเข้า" หรือ "จ่ายออก" เพื่อดูรายการพัสดุรายตัว');
            \$hint.find('i').removeClass('bi-hand-index-thumb').addClass('bi-info-circle');
            \$hint.css('opacity', '0.85');
        }
    }

    function setLoading(on) {
        \$body.attr('data-loading', on ? 'true' : 'false');
        \$skeleton.prop('hidden', !on);
        \$chart.attr('aria-busy', on ? 'true' : 'false');
    }

    var loadTimer = null;
    function load() {
        clearTimeout(loadTimer);
        loadTimer = setTimeout(function() {
            setLoading(true);
            var params = {
                warehouse_id: warehouseId,
                year: state.year,
                direction: state.direction
            };
            if (state.view === 'month') params.month = state.month;
            $.get(endpoint, params)
                .done(function(data) {
                    setLoading(false);
                    render(data);
                })
                .fail(function() {
                    setLoading(false);
                    \$empty.prop('hidden', false);
                    \$empty.find('.movement-empty__title').text('โหลดข้อมูลไม่สำเร็จ');
                    \$empty.find('.movement-empty__caption').text('กรุณาลองใหม่อีกครั้ง');
                });
        }, 150);
    }

    // ─── Filter events ───
    \$yearSelect.on('change', function() {
        state.year = parseInt(this.value, 10);
        load();
    });

    \$monthSelect.on('change', function() {
        state.month = parseInt(this.value, 10);
        if (state.view === 'month') load();
    });

    $('.seg-control').on('click', '.seg-control__item', function() {
        var \$btn = $(this);
        if (\$btn.hasClass('is-active') || \$btn.is(':disabled')) return;
        var \$group = \$btn.closest('.seg-control');
        var seg = \$group.data('seg');
        var value = \$btn.data('value');
        \$group.find('.seg-control__item').removeClass('is-active').attr('aria-checked', 'false');
        \$btn.addClass('is-active').attr('aria-checked', 'true');

        if (seg === 'direction') {
            state.direction = value;
            load();
        } else if (seg === 'view') {
            state.view = value;
            if (value === 'month') {
                \$monthSelect.prop('hidden', false);
                state.month = parseInt(\$monthSelect.val(), 10);
            } else {
                \$monthSelect.prop('hidden', true);
                state.month = null;
            }
            load();
        }
    });

    // Keyboard nav for segmented (← →)
    $('.seg-control').on('keydown', '.seg-control__item', function(e) {
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        e.preventDefault();
        var \$items = $(this).closest('.seg-control').find('.seg-control__item');
        var idx = \$items.index(this);
        var nextIdx = e.key === 'ArrowRight' ? (idx + 1) % \$items.length : (idx - 1 + \$items.length) % \$items.length;
        \$items.eq(nextIdx).focus().trigger('click');
    });

    // Warehouse filter (preserve existing behavior)
    $('#warehouseFilter').on('change', function() { $('#form-warehouse').submit(); });

    // Initial render from server data
    render(initialData);

    // ═══════════════ Movement drill-down modal ═══════════════
    var modalEl = document.getElementById('movementItemsModal');
    var bsModal = modalEl && window.bootstrap ? new bootstrap.Modal(modalEl, { focus: true }) : null;
    var \$modalBody = $('#movementItemsBody');
    var \$modalSummary = $('#movementItemsSummary');
    var \$modalCount = $('#movementItemsCount');
    var \$modalTotal = $('#movementItemsTotal');
    var \$modalCatName = $('#movementItemsCatName');
    var \$modalCatDot = $('#movementItemsCatDot');
    var \$modalPeriod = $('#movementItemsPeriod');
    var \$modalWarehouse = $('#movementItemsWarehouse');
    var \$modalDirBadge = $('#movementItemsDirBadge');
    var \$modalDirLabel = $('#movementItemsDirLabel');
    var \$modalFootNote = $('#movementItemsFootNote');
    var \$modalTitle = $('#movementItemsModalTitle');

    function dirIconClass(dir) {
        return dir === 'OUT' ? 'bi-arrow-up-right-circle' : 'bi-arrow-down-left-circle';
    }
    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function handleBarClick(data, cfg, evt) {
        if (!cfg || cfg.seriesIndex == null || cfg.dataPointIndex == null) return;
        var series = data.series || [];
        var monthIdx, seriesEntry;

        if (data.mode === 'year') {
            // stacked: seriesIndex = category, dataPointIndex = month (0-11)
            seriesEntry = series[cfg.seriesIndex];
            monthIdx = cfg.dataPointIndex;
        } else {
            // month mode: distributed horizontal — rows were re-sorted in buildOptions
            // Replicate that sort to map dataPointIndex → series entry
            monthIdx = (data.month || 1) - 1;
            var rows = series.map(function(s, i) {
                return { i: i, name: s.name, value: (s.data || [])[monthIdx] || 0 };
            });
            rows.sort(function(a, b) { return Math.abs(b.value) - Math.abs(a.value); });
            var pick = rows[cfg.dataPointIndex];
            if (pick) seriesEntry = series[pick.i];
        }
        if (!seriesEntry) return;
        // bail if the bar has no value (no items to drill into)
        var clickedVal = (seriesEntry.data || [])[monthIdx] || 0;
        if (Math.abs(clickedVal) < 0.005) return;

        var monthLabels = data.months || [];
        lastTriggerEl = (evt && evt.target) ? evt.target : null;
        openItemsModal({
            year: data.year,
            month: monthIdx + 1,
            direction: state.direction,
            categoryCode: seriesEntry.code,
            categoryName: seriesEntry.name,
            categoryColor: seriesEntry.color,
            periodHint: (monthLabels[monthIdx] || '') + ' ' + data.year
        });
    }

    function setModalHeader(opts) {
        \$modalCatName.text(opts.categoryName || '—');
        \$modalCatDot.css('background', opts.categoryColor || 'var(--ink-4)');
        \$modalPeriod.text(opts.periodHint || '—');
        \$modalWarehouse.text(opts.warehouseName || 'ทั้งหมด');
        var dir = opts.direction || 'IN';
        \$modalDirBadge.attr('data-direction', dir);
        \$modalDirBadge.find('i')
            .removeClass('bi-arrow-down-left-circle bi-arrow-up-right-circle')
            .addClass(dirIconClass(dir));
        \$modalDirLabel.text(dir === 'OUT' ? 'จ่ายออก' : 'รับเข้า');
        \$modalFootNote.text(dir === 'OUT'
            ? 'มูลค่าจ่ายออกอ้างราคาทุนของล็อตเดียวกันที่บันทึกตอนรับเข้า'
            : 'มูลค่ารับเข้าตามราคาต่อหน่วยในใบรับ ณ วันที่บันทึก');
    }

    function renderSkeleton() {
        var rows = '';
        for (var i = 0; i < 6; i++) {
            rows += '<div class="movement-items-modal__skel-row">' +
                '<span class="movement-items-modal__skel-block movement-items-modal__skel-idx"></span>' +
                '<span class="movement-items-modal__skel-block movement-items-modal__skel-name"></span>' +
                '<span class="movement-items-modal__skel-block movement-items-modal__skel-num"></span>' +
                '<span class="movement-items-modal__skel-block movement-items-modal__skel-num"></span>' +
                '</div>';
        }
        \$modalBody.html('<div class="movement-items-modal__skeleton">' + rows + '</div>');
        \$modalBody.attr('aria-busy', 'true');
        \$modalSummary.prop('hidden', true);
    }

    function renderState(opts) {
        var iconClass = opts.error ? 'is-error' : '';
        var icon = opts.icon || (opts.error ? 'bi-exclamation-triangle' : 'bi-inbox');
        var actionHtml = '';
        if (opts.action) {
            actionHtml = '<button type="button" class="movement-items-modal__state-action" id="movementItemsRetry">' +
                escapeHtml(opts.action) + '</button>';
        }
        \$modalBody.html(
            '<div class="movement-items-modal__state">' +
                '<span class="movement-items-modal__state-icon ' + iconClass + '"><i class="bi ' + icon + '"></i></span>' +
                '<p class="movement-items-modal__state-title">' + escapeHtml(opts.title) + '</p>' +
                '<p class="movement-items-modal__state-caption">' + escapeHtml(opts.caption || '') + '</p>' +
                actionHtml +
            '</div>'
        );
        \$modalBody.attr('aria-busy', 'false');
    }

    function renderItems(items) {
        if (!items || items.length === 0) {
            renderState({
                title: 'ไม่พบรายการพัสดุ',
                caption: 'ในเดือน-ประเภทที่เลือก ยังไม่มีการเคลื่อนไหวที่บันทึกไว้'
            });
            return;
        }
        function thumbHtml(img, name) {
            if (img) {
                return '<span class="movement-items-modal__thumb">' +
                    '<img src="' + escapeHtml(img) + '" alt="' + escapeHtml(name) + '" loading="lazy" ' +
                    'onerror="this.parentNode.classList.add(\\'is-empty\\');this.remove();">' +
                '</span>';
            }
            return '<span class="movement-items-modal__thumb is-empty" aria-hidden="true">' +
                '<i class="bi bi-box-seam"></i>' +
            '</span>';
        }
        var tbody = '';
        var cards = '';
        items.forEach(function(it, i) {
            var idx = i + 1;
            var qty = (it.qty || 0).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            var val = (it.value || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var unit = it.unit || '';
            tbody += '<tr>' +
                '<td class="col-idx">' + idx + '</td>' +
                '<td class="col-thumb">' + thumbHtml(it.img, it.name) + '</td>' +
                '<td class="col-code">' + escapeHtml(it.code) + '</td>' +
                '<td class="col-name">' + escapeHtml(it.name) + '</td>' +
                '<td class="col-unit">' + escapeHtml(unit || '—') + '</td>' +
                '<td class="is-num">' + qty + '</td>' +
                '<td class="is-num is-value">' + val + '</td>' +
                '</tr>';
            cards += '<div class="movement-items-modal__card">' +
                '<span class="movement-items-modal__card-idx">' + idx + '</span>' +
                thumbHtml(it.img, it.name).replace('movement-items-modal__thumb', 'movement-items-modal__thumb movement-items-modal__thumb--card') +
                '<div class="movement-items-modal__card-main">' +
                    '<div class="movement-items-modal__card-name">' + escapeHtml(it.name) + '</div>' +
                    '<div class="movement-items-modal__card-meta">' + escapeHtml(it.code) + '</div>' +
                '</div>' +
                '<div class="movement-items-modal__card-fig">' +
                    '<span class="movement-items-modal__card-qty">' + qty +
                        '<span class="movement-items-modal__card-qty-unit">' + escapeHtml(unit || '') + '</span>' +
                    '</span>' +
                    '<span class="movement-items-modal__card-value">' + val + ' ฿</span>' +
                '</div>' +
            '</div>';
        });

        var table = '<table class="movement-items-modal__table"><thead><tr>' +
            '<th class="col-idx">#</th>' +
            '<th class="col-thumb" aria-label="รูปภาพ"></th>' +
            '<th>รหัสพัสดุ</th>' +
            '<th>ชื่อพัสดุ</th>' +
            '<th>หน่วย</th>' +
            '<th class="is-num">จำนวน</th>' +
            '<th class="is-num">มูลค่า (฿)</th>' +
            '</tr></thead><tbody>' + tbody + '</tbody></table>' +
            '<div class="movement-items-modal__cards">' + cards + '</div>';
        \$modalBody.html(table);
        \$modalBody.attr('aria-busy', 'false');
    }

    var lastQueryParams = null;
    function loadItems(params) {
        lastQueryParams = params;
        renderSkeleton();
        $.ajax({
            url: itemsEndpoint,
            method: 'GET',
            data: {
                warehouse_id: warehouseId,
                year: params.year,
                month: params.month,
                direction: params.direction,
                category: params.categoryCode
            },
            dataType: 'json'
        }).done(function(res) {
            if (!res || res.status !== 'success') {
                renderState({
                    error: true,
                    icon: 'bi-exclamation-triangle',
                    title: 'โหลดข้อมูลไม่สำเร็จ',
                    caption: (res && res.message) ? res.message : 'กรุณาลองใหม่อีกครั้ง',
                    action: 'ลองอีกครั้ง'
                });
                return;
            }
            var s = res.summary || {};
            // refresh header from server-side authoritative labels
            \$modalPeriod.text(s.period_label || params.periodHint || '—');
            \$modalWarehouse.text(s.warehouse_name || 'ทั้งหมด');
            \$modalCatName.text(s.category_name || params.categoryName || '—');
            \$modalCatDot.css('background', s.category_color || params.categoryColor || 'var(--ink-4)');
            // summary numbers
            \$modalCount.text((s.count || 0).toLocaleString('th-TH'));
            \$modalTotal.text((s.total_value || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            \$modalSummary.prop('hidden', false);
            renderItems(res.items || []);
        }).fail(function() {
            renderState({
                error: true,
                icon: 'bi-wifi-off',
                title: 'เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ',
                caption: 'ตรวจสอบการเชื่อมต่อแล้วลองใหม่',
                action: 'ลองอีกครั้ง'
            });
        });
    }

    function openItemsModal(opts) {
        setModalHeader(opts);
        \$modalCount.text('0');
        \$modalTotal.text('0.00');
        \$modalSummary.prop('hidden', true);
        if (bsModal) bsModal.show();
        loadItems(opts);
    }

    // Retry from error state
    \$modalBody.on('click', '#movementItemsRetry', function() {
        if (lastQueryParams) loadItems(lastQueryParams);
    });

    // Restore focus to triggering bar on close
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            // ApexCharts redraws on update; safest to refocus the chart container
            try { \$chart.find('svg').first().focus(); } catch (e) {}
            lastTriggerEl = null;
        });
    }


    // ═══════════════ KPI Offcanvas controller ═══════════════
    var kpiOcEl = document.getElementById('kpiOffcanvas');
    var kpiOcContent = document.getElementById('kpiOffcanvasContent');
    var kpiOcTitle = document.getElementById('kpiOffcanvasTitle');
    var kpiOcCount = document.getElementById('kpiOffcanvasCount');
    var kpiOcWarehouse = document.getElementById('kpiOffcanvasWarehouse');
    var kpiOcInstance = kpiOcEl && window.bootstrap ? new bootstrap.Offcanvas(kpiOcEl) : null;
    var kpiOcLastReq = null; // จำ params ล่าสุดเพื่อ retry
    var kpiOcSearchTimer = null;
    var warehouseLabel = ($('#chart-badge').text() || 'ทั้งหมด').trim();

    function escapeAttr(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function renderKpiOcSkeleton() {
        var rows = '';
        for (var i = 0; i < 6; i++) {
            rows += '<div class="kpi-oc__skel-row">' +
                '<span class="kpi-oc__skel-block kpi-oc__skel-thumb"></span>' +
                '<span class="kpi-oc__skel-block kpi-oc__skel-main"></span>' +
                '<span class="kpi-oc__skel-block kpi-oc__skel-fig"></span>' +
            '</div>';
        }
        kpiOcContent.innerHTML = '<div class="kpi-oc__skeleton">' + rows + '</div>';
    }

    function renderKpiOcError(msg) {
        kpiOcContent.innerHTML =
            '<div class="kpi-oc__error">' +
                '<i class="bi bi-wifi-off" aria-hidden="true"></i>' +
                '<p class="kpi-oc__error-title">โหลดข้อมูลไม่สำเร็จ</p>' +
                '<p class="kpi-oc__error-caption">' + escapeAttr(msg || 'ตรวจสอบการเชื่อมต่อแล้วลองใหม่') + '</p>' +
                '<button type="button" class="kpi-oc__retry" id="kpiOcRetry">ลองอีกครั้ง</button>' +
            '</div>';
    }

    function loadKpiOc(endpoint, q) {
        kpiOcLastReq = { endpoint: endpoint, q: q || '' };
        renderKpiOcSkeleton();
        $.ajax({
            url: endpoint,
            method: 'GET',
            data: { q: q || '' },
            dataType: 'json'
        }).done(function(res) {
            if (!res || res.status !== 'success') {
                renderKpiOcError(res && res.message);
                return;
            }
            kpiOcContent.innerHTML = res.content || '';
            // อัปเดต count chip ใน head
            var n = (typeof res.total_count === 'number') ? res.total_count : 0;
            kpiOcCount.textContent = n.toLocaleString('th-TH') + ' รายการ';
        }).fail(function(xhr) {
            renderKpiOcError(xhr && xhr.statusText);
        });
    }

    function openKpi(card) {
        var endpoint = card.getAttribute('data-oc-endpoint');
        var title = card.getAttribute('data-oc-title') || 'รายการ';
        if (!endpoint || !kpiOcInstance) return;

        kpiOcTitle.textContent = title;
        kpiOcCount.textContent = '...';
        kpiOcWarehouse.textContent = warehouseLabel;
        kpiOcInstance.show();
        loadKpiOc(endpoint, '');
    }

    // คลิก KPI card → เปิด offcanvas (intercept), ปล่อย ctrl/cmd/middle ให้ browser ทำงานเอง
    $(document).on('click', '.kpi-card', function(e) {
        var card = this;
        // อนุญาตเปิดแท็บใหม่
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return;
        if (card.classList.contains('is-disabled')) {
            e.preventDefault();
            return;
        }
        if (!card.hasAttribute('data-oc-endpoint')) return;
        e.preventDefault();
        openKpi(card);
    });

    // Search ใน offcanvas — debounce 250ms
    $(document).on('input', '[data-oc-search]', function() {
        var val = this.value;
        if (!kpiOcLastReq) return;
        clearTimeout(kpiOcSearchTimer);
        kpiOcSearchTimer = setTimeout(function() {
            loadKpiOc(kpiOcLastReq.endpoint, val);
        }, 250);
    });

    // Retry จาก error state
    $(document).on('click', '#kpiOcRetry', function() {
        if (kpiOcLastReq) loadKpiOc(kpiOcLastReq.endpoint, kpiOcLastReq.q);
    });

    // เปลี่ยน filter คลัง → ปิด offcanvas (form submit reload หน้าอยู่แล้ว — แค่ป้องกัน flash)
    $('#warehouseFilter').on('change', function() {
        if (kpiOcInstance && kpiOcEl.classList.contains('show')) kpiOcInstance.hide();
    });

    // Clear last request เมื่อปิด — กัน race condition
    if (kpiOcEl) {
        kpiOcEl.addEventListener('hidden.bs.offcanvas', function() {
            kpiOcLastReq = null;
            clearTimeout(kpiOcSearchTimer);
        });
    }
});
JS;
$this->registerJs($js, View::POS_READY);
?>
