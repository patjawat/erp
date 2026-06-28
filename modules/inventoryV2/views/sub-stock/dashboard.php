<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\modules\inventoryV2\models\StockOrder;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/apexcharts', ['position' => View::POS_HEAD]);

$pendingDisbursementCount = (int) ($pendingDisbursementCount ?? ($pendingReceiveCount ?? 0));
$criticalCount = (int) ($criticalCount ?? 0);
$monthlyValue = (float) ($monthlyValue ?? 0);
$expiringSoonCount = (int) ($expiringSoonCount ?? 0);
$pendingIssueList = $pendingIssueList ?? ($incomingList ?? []);
$chartData = $chartData ?? ['categories' => [], 'series' => []];
$warehouses = $warehouses ?? [];
$currentWarehouseId = $currentWarehouseId ?? null;
$subWarehouseIds = $subWarehouseIds ?? [];
$usageHistory = $usageHistory ?? [];
$permissionBlocked = (bool) ($permissionBlocked ?? false);
$permissionMessage = $permissionMessage ?? 'หน่วยงานของคุณยังไม่ได้กำหนดแผนก/ฝ่ายที่มีสิทธิเบิก ในคลังย่อย';
$permissionSettingUrl = ['/inventory-v2/default/setting'];
$canCreateRequisition = (bool) ($canCreateRequisition ?? false);

$currentWarehouseName = 'คลังย่อยทั้งหมด';
if ($currentWarehouseId && $warehouses) {
    foreach ($warehouses as $w) {
        if ((int)$w->id === (int)$currentWarehouseId) {
            $currentWarehouseName = $w->warehouse_name;
            break;
        }
    }
}

$this->title = $permissionBlocked ? 'คลังย่อยยังไม่พร้อมใช้งาน' : 'ภาพรวม' . $currentWarehouseName;
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$reportBalanceUrl = ['/inventory-v2/sub-stock/balance'];
if (!empty($subWarehouseIds) && count($subWarehouseIds) === 1) {
    $reportBalanceUrl['warehouse_id'] = $subWarehouseIds[0];
}

$issueUrl = ['/inventory-v2/sub-stock/issue'];
if ($currentWarehouseId) {
    $issueUrl['warehouse_id'] = (int) $currentWarehouseId;
}

$pendingRequisitionUrl = [
    '/inventory-v2/requisition/index',
    'RequisitionSearch' => ['status' => StockOrder::STATUS_APPROVED],
];
if ($currentWarehouseId) {
    $pendingRequisitionUrl['warehouse_id'] = (int) $currentWarehouseId;
    $pendingRequisitionUrl['RequisitionSearch']['sub_warehouse_id'] = (int) $currentWarehouseId;
}

$thaiMonths = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
$thaiDows   = ['อา.','จ.','อ.','พ.','พฤ.','ศ.','ส.'];
$nowTs   = time();
$todayLabel = $thaiDows[(int)date('w', $nowTs)] . ' ' . (int)date('j', $nowTs) . ' ' . $thaiMonths[(int)date('n', $nowTs) - 1] . ' ' . (date('Y', $nowTs) + 543);
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->render('_page_head', [
    'icon'  => 'bi-grid-1x2-fill',
    'title' => $this->title,
    'metas' => [$permissionBlocked ? 'รอตั้งค่าสิทธิเบิก' : $currentWarehouseName, $todayLabel],
    'extra' => $this->render('_health_badge', [
        'criticalCount' => $criticalCount,
        'pendingDisbursementCount' => $pendingDisbursementCount,
        'expiringSoonCount' => $expiringSoonCount,
    ]),
]) ?>
<?php $this->endBlock(); ?>

<?php
$subStockActionMenu = $permissionBlocked
    ? Html::a('<i class="bi bi-gear-fill me-1"></i> ตั้งค่าคลังสินค้า', $permissionSettingUrl, [
        'class' => 'btn btn-warning text-dark rounded-pill px-3',
    ])
    : $this->render('_menu_sub_stock', [
        'active' => 'dashboard',
        'currentWarehouseId' => $currentWarehouseId,
    ]);
foreach (['action', 'page-action'] as $actionBlock) {
    $this->beginBlock($actionBlock);
    echo $subStockActionMenu;
    $this->endBlock();
}
?>

<div class="container-fluid py-3 py-md-4 px-3 px-md-4 sub-stock-dashboard">

    <?php if ($permissionBlocked): ?>
        <section class="sub-stock-blocked" aria-labelledby="sub-stock-blocked-heading">
            <div class="sub-stock-blocked__icon" aria-hidden="true">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div class="sub-stock-blocked__content">
                <span class="sub-stock-blocked__eyebrow">ต้องตั้งค่าสิทธิก่อนเข้าใช้งาน</span>
                <h2 id="sub-stock-blocked-heading"><?= Html::encode($permissionMessage) ?></h2>
                <p>กรุณาไปที่ตั้งค่าคลังสินค้า และกำหนดแผนก/ฝ่ายที่มีสิทธิเบิกให้เรียบร้อยก่อนเข้าใช้งานคลังย่อย</p>
                <div class="sub-stock-blocked__note">
                    หากคุณไม่มีสิทธิ์ตั้งค่า โปรดแจ้งผู้ดูแลระบบคลังสินค้าให้กำหนดแผนก/ฝ่ายของคุณในคลังย่อยที่ต้องใช้งาน
                </div>
                <div class="sub-stock-blocked__actions">
                    <?= Html::a('<i class="bi bi-gear-fill me-1"></i> ไปตั้งค่าคลังสินค้า', $permissionSettingUrl, [
                        'class' => 'btn btn-warning text-dark rounded-pill px-4',
                    ]) ?>
                    <?= Html::a('<i class="bi bi-grid-1x2-fill me-1"></i> กลับเมนูคลังสินค้า', ['/inventory-v2/default/index'], [
                        'class' => 'btn btn-outline-secondary rounded-pill px-4',
                    ]) ?>
                </div>
            </div>
        </section>
    <?php else: ?>

    <?= $this->render('_critical_strip', [
        'criticalCount' => $criticalCount,
        'subWarehouseIds' => $subWarehouseIds,
    ]) ?>

    <section aria-labelledby="kpi-heading" class="sub-stock-dashboard__kpi">
        <h2 id="kpi-heading" class="visually-hidden">สรุปสถานะคลังย่อย</h2>
        <div class="kpi-grid">
            <?= $this->render('_kpi_card', [
                'label' => 'ต่ำกว่าจุดวิกฤต',
                'value' => number_format($criticalCount),
                'unit' => 'รายการ',
                'icon' => 'bi-exclamation-triangle',
                'color' => $criticalCount > 0 ? 'danger' : 'success',
                'hint' => $criticalCount > 0 ? 'ควรรีบขอเบิก' : 'อยู่ในเกณฑ์ปลอดภัย',
                'url' => $reportBalanceUrl,
            ]) ?>
            <?= $this->render('_kpi_card', [
                'label' => 'มูลค่าที่ได้รับเดือนนี้',
                'value' => number_format($monthlyValue, 0),
                'unit' => 'บาท',
                'icon' => 'bi-wallet2',
                'color' => 'primary',
                'hint' => 'รวมจากคลังหลักทั้งเดือน',
            ]) ?>
            <?= $this->render('_kpi_card', [
                'label' => 'รอคลังหลักจ่าย',
                'value' => number_format($pendingDisbursementCount),
                'unit' => 'ใบ',
                'icon' => 'bi-truck',
                'color' => $pendingDisbursementCount > 0 ? 'warning' : 'success',
                'hint' => $pendingDisbursementCount > 0 ? 'หัวหน้าอนุมัติแล้ว ยังไม่ตัดสต็อก' : 'ไม่มีใบที่รอจ่าย',
                'url' => $pendingRequisitionUrl,
            ]) ?>
            <?= $this->render('_kpi_card', [
                'label' => 'ใกล้หมดอายุ',
                'value' => number_format($expiringSoonCount),
                'unit' => 'รายการ',
                'icon' => 'bi-calendar-event',
                'color' => $expiringSoonCount > 0 ? 'warning' : 'success',
                'hint' => $expiringSoonCount > 0 ? 'ตรวจสอบ Lot ก่อนใช้' : 'ไม่มีรายการเสี่ยง',
                'url' => $reportBalanceUrl,
            ]) ?>
        </div>
    </section>

    <div class="sub-stock-dashboard__grid">
        <section class="surface-card" id="section-incoming" aria-labelledby="incoming-heading">
            <div class="surface-card__head">
                <div class="surface-card__head-text">
                    <h2 id="incoming-heading" class="surface-card__title">ใบขอเบิกที่รอคลังหลักจ่าย</h2>
                    <span class="surface-card__caption">หัวหน้าอนุมัติแล้ว รอคลังหลักดำเนินการจ่าย</span>
                </div>
                <?php if (!empty($pendingIssueList)): ?>
                    <a href="<?= Url::to($pendingRequisitionUrl) ?>" class="surface-card__link">
                        ทะเบียนทั้งหมด <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>
            <div class="surface-card__body">
                <?php if (!empty($pendingIssueList)): ?>
                    <ul class="incoming-list">
                        <?php foreach ($pendingIssueList as $item):
                            $orderTs = !empty($item['order_date']) ? strtotime($item['order_date']) : null;
                            $orderLabel = $orderTs ? date('d/m/Y', $orderTs) : '-';
                        ?>
                            <li>
                                <a href="<?= Url::to(['/inventory-v2/requisition/view', 'id' => $item['id'] ?? null]) ?>"
                                   class="incoming-item open-modal"
                                   data-size="modal-xl"
                                   data-pjax="0">
                                    <span class="incoming-item__icon" aria-hidden="true">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </span>
                                    <div class="incoming-item__body">
                                        <div class="incoming-item__title">
                                            <?= Html::encode($item['doc_no'] ?? '-') ?>
                                        </div>
                                        <div class="incoming-item__meta">
                                            <span><i class="bi bi-calendar3" aria-hidden="true"></i>ขอเบิก <?= $orderLabel ?></span>
                                            <span><i class="bi bi-list-check" aria-hidden="true"></i><?= (int)($item['detail_count'] ?? 0) ?> รายการ</span>
                                            <?php if (!empty($item['main_warehouse_name'])): ?>
                                                <span class="incoming-item__meta-truncate"><i class="bi bi-buildings" aria-hidden="true"></i><?= Html::encode($item['main_warehouse_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <i class="bi bi-chevron-right incoming-item__chevron" aria-hidden="true"></i>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-block">
                        <div class="empty-block__icon">
                            <i class="bi bi-inbox" aria-hidden="true"></i>
                        </div>
                        <div class="empty-block__title">ไม่มีใบที่รอคลังหลักจ่าย</div>
                        <div class="empty-block__caption">เมื่อหัวหน้าอนุมัติใบขอเบิก รายการจะแสดงที่นี่จนกว่าคลังหลักจะจ่ายของ</div>
                        <?php if ($canCreateRequisition): ?>
                        <a href="<?= Url::to(['/inventory-v2/requisition/create']) ?>" class="empty-block__action">
                            <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
                            <span>สร้างใบขอเบิกใหม่</span>
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="surface-card" aria-labelledby="trend-heading">
            <div class="surface-card__head">
                <div class="surface-card__head-text">
                    <h2 id="trend-heading" class="surface-card__title">แนวโน้ม 7 วัน</h2>
                    <span class="surface-card__caption">มูลค่าที่ได้รับจากคลังหลัก</span>
                </div>
                <button type="button"
                        class="chart-toggle d-lg-none"
                        data-bs-toggle="collapse"
                        data-bs-target="#trendChartCollapse"
                        aria-expanded="false"
                        aria-controls="trendChartCollapse"
                        aria-label="แสดง/ซ่อนกราฟ">
                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </button>
            </div>
            <div class="collapse d-lg-block" id="trendChartCollapse">
                <div class="surface-card__body surface-card__body--chart">
                    <div id="usageApexChart"></div>
                </div>
            </div>
        </section>
    </div>

    <div id="section-history">
        <?= $this->render('use_history', [
            'usageHistory' => $usageHistory,
            'currentWarehouseId' => $currentWarehouseId,
        ]) ?>
    </div>
    <?php endif; ?>
</div>

<?php if (!$permissionBlocked): ?>
    <a href="<?= Url::to($issueUrl) ?>" class="sub-stock-fab" aria-label="ตัดจ่ายคลังย่อย">
        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
        <span>ตัดจ่ายครั้งย่อย</span>
    </a>
<?php endif; ?>

<style>
.sub-stock-dashboard {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd;
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --primary-line: rgba(13, 110, 253, 0.22);
    --success: #15803d;
    --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.10);
    --danger:  #b91c1c;
    --danger-soft: rgba(185, 28, 28, 0.10);
    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15,23,42,0.04), 0 1px 1px rgba(15,23,42,0.03);
    --shadow-2: 0 6px 18px rgba(15,23,42,0.06), 0 2px 4px rgba(15,23,42,0.04);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms;
    --t-mid: 180ms;
    color: var(--ink-1);
}

.sub-stock-blocked {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    gap: 20px;
    align-items: start;
    max-width: 920px;
    min-height: 280px;
    margin: 8px auto 0;
    padding: 28px;
    border: 1px solid rgba(180, 83, 9, 0.24);
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(255, 251, 235, 0.92) 0%, rgba(255, 255, 255, 0.98) 48%, rgba(239, 246, 255, 0.9) 100%);
    box-shadow: var(--shadow-2);
}

.sub-stock-blocked__icon {
    width: 64px;
    height: 64px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #92400e;
    background: rgba(245, 158, 11, 0.16);
    font-size: 28px;
}

.sub-stock-blocked__content {
    min-width: 0;
}

.sub-stock-blocked__eyebrow {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 3px 10px;
    border-radius: 999px;
    color: #92400e;
    background: rgba(245, 158, 11, 0.14);
    font-size: 0.82rem;
    font-weight: 700;
}

.sub-stock-blocked h2 {
    margin: 12px 0 8px;
    color: var(--ink-1);
    font-size: 1.45rem;
    font-weight: 800;
    line-height: 1.35;
}

.sub-stock-blocked p {
    max-width: 680px;
    margin: 0;
    color: var(--ink-2);
    line-height: 1.7;
}

.sub-stock-blocked__note {
    max-width: 680px;
    margin-top: 14px;
    padding: 12px 14px;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    color: #5f4b16;
    background: rgba(255, 251, 235, 0.9);
    line-height: 1.65;
}

.sub-stock-blocked__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
}

.sub-stock-blocked__actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
}

/* Surface card */
.sub-stock-dashboard .surface-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 100%;
}
.sub-stock-dashboard .surface-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid var(--line);
    background: var(--surface);
}
.sub-stock-dashboard .surface-card__head-text { min-width: 0; }
.sub-stock-dashboard .surface-card__title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--ink-1);
    margin: 0;
    line-height: 1.3;
}
.sub-stock-dashboard .surface-card__caption {
    display: block;
    font-size: 0.78rem;
    color: var(--ink-3);
    margin-top: 0.15rem;
    line-height: 1.3;
}
.sub-stock-dashboard .surface-card__link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    color: var(--ink-2);
    text-decoration: none;
    font-size: 0.8rem;
    font-weight: 500;
    padding: 0.35rem 0.55rem;
    border-radius: var(--radius-xs);
    transition: background-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
    white-space: nowrap;
}
.sub-stock-dashboard .surface-card__link:hover {
    background: var(--surface-hover);
    color: var(--primary-ink);
}
.sub-stock-dashboard .surface-card__body {
    padding: 0.75rem 1.1rem 1.1rem;
    flex: 1;
}
.sub-stock-dashboard .surface-card__body--chart {
    padding: 0.5rem 0.5rem 0.5rem;
}

/* Layout grid */
.sub-stock-dashboard__kpi { margin-bottom: 1.25rem; }
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.85rem;
}
@media (max-width: 991.98px) {
    .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 380px) {
    .kpi-grid { grid-template-columns: 1fr; }
}

.sub-stock-dashboard__grid {
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
    gap: 0.85rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 991.98px) {
    .sub-stock-dashboard__grid { grid-template-columns: 1fr; }
}

/* KPI card */
.sub-stock-dashboard .kpi-card {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding: 0.95rem 1.05rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    color: var(--ink-1);
    text-decoration: none;
    transition: border-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
}
.sub-stock-dashboard .kpi-card--link:hover {
    border-color: var(--line-strong);
    box-shadow: var(--shadow-2);
}
.sub-stock-dashboard .kpi-card--link:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.sub-stock-dashboard .kpi-card__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}
.sub-stock-dashboard .kpi-card__text { min-width: 0; flex: 1; }
.sub-stock-dashboard .kpi-card__label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-2);
    line-height: 1.3;
}
.sub-stock-dashboard .kpi-card__value-row {
    display: flex;
    align-items: baseline;
    gap: 0.35rem;
    margin-top: 0.4rem;
    flex-wrap: wrap;
}
.sub-stock-dashboard .kpi-card__value {
    font-size: 1.55rem;
    font-weight: 700;
    color: var(--ink-1);
    line-height: 1.15;
    font-variant-numeric: tabular-nums;
}
.sub-stock-dashboard .kpi-card__unit {
    font-size: 0.78rem;
    color: var(--ink-3);
    font-weight: 500;
}
.sub-stock-dashboard .kpi-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    font-size: 1rem;
    flex-shrink: 0;
}
.sub-stock-dashboard .kpi-card__icon--primary { background: var(--primary-soft); color: var(--primary-ink); }
.sub-stock-dashboard .kpi-card__icon--success { background: var(--success-soft); color: var(--success); }
.sub-stock-dashboard .kpi-card__icon--warning { background: var(--warning-soft); color: var(--warning); }
.sub-stock-dashboard .kpi-card__icon--danger  { background: var(--danger-soft);  color: var(--danger);  }
.sub-stock-dashboard .kpi-card__icon--info    { background: var(--primary-soft); color: var(--primary-ink); }
.sub-stock-dashboard .kpi-card__foot {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--ink-3);
    line-height: 1.3;
    padding-top: 0.4rem;
    border-top: 1px dashed var(--line);
}
.sub-stock-dashboard .kpi-card__trend--success { color: var(--success); font-weight: 600; }
.sub-stock-dashboard .kpi-card__trend--danger  { color: var(--danger);  font-weight: 600; }
.sub-stock-dashboard .kpi-card__trend--warning { color: var(--warning); font-weight: 600; }
.sub-stock-dashboard .kpi-card__trend--primary { color: var(--primary-ink); font-weight: 600; }
.sub-stock-dashboard .kpi-card__hint { color: var(--ink-3); }
.sub-stock-dashboard .kpi-card__more { margin-left: auto; color: var(--ink-4); transition: color var(--t-fast) var(--ease), transform var(--t-fast) var(--ease); }
.sub-stock-dashboard .kpi-card--link:hover .kpi-card__more { color: var(--primary-ink); transform: translateX(2px); }

/* Critical strip */
.sub-stock-dashboard .critical-strip {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.85rem 1.1rem;
    background: var(--danger-soft);
    border: 1px solid rgba(185, 28, 28, 0.18);
    border-left: 3px solid var(--danger);
    border-radius: var(--radius);
    margin-bottom: 1rem;
}
.sub-stock-dashboard .critical-strip__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: rgba(185, 28, 28, 0.16);
    color: var(--danger);
    font-size: 1.05rem;
    flex-shrink: 0;
}
.sub-stock-dashboard .critical-strip__body { flex: 1; min-width: 0; }
.sub-stock-dashboard .critical-strip__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--ink-1);
    line-height: 1.3;
}
.sub-stock-dashboard .critical-strip__caption {
    font-size: 0.78rem;
    color: var(--ink-2);
    margin-top: 0.1rem;
    line-height: 1.3;
}
.sub-stock-dashboard .critical-strip__action {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    background: var(--surface);
    border: 1px solid rgba(185, 28, 28, 0.28);
    border-radius: 999px;
    color: var(--danger);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    min-height: 36px;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
    white-space: nowrap;
}
.sub-stock-dashboard .critical-strip__action:hover {
    background: rgba(185, 28, 28, 0.05);
    border-color: var(--danger);
}
.sub-stock-dashboard .critical-strip__action:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.18);
}
.sub-stock-dashboard .critical-strip__count {
    font-variant-numeric: tabular-nums;
    font-weight: 700;
}

/* Incoming list */
.sub-stock-dashboard .incoming-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.sub-stock-dashboard .incoming-item {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.75rem 0.85rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    color: var(--ink-1);
    text-decoration: none;
    min-height: 64px;
    transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
}
.sub-stock-dashboard .incoming-item:hover {
    background: var(--surface-hover);
    border-color: var(--line-strong);
}
.sub-stock-dashboard .incoming-item:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.sub-stock-dashboard .incoming-item__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    background: var(--surface-3);
    color: var(--ink-2);
    font-size: 1.05rem;
    flex-shrink: 0;
}
.sub-stock-dashboard .incoming-item__body { flex: 1; min-width: 0; }
.sub-stock-dashboard .incoming-item__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--ink-1);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    letter-spacing: -0.01em;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.sub-stock-dashboard .incoming-item__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 0.85rem;
    font-size: 0.76rem;
    color: var(--ink-3);
    margin-top: 0.2rem;
    line-height: 1.3;
}
.sub-stock-dashboard .incoming-item__meta span { display: inline-flex; align-items: center; gap: 0.25rem; }
.sub-stock-dashboard .incoming-item__meta-truncate {
    max-width: 14rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.sub-stock-dashboard .incoming-item__chevron {
    color: var(--ink-4);
    flex-shrink: 0;
    transition: color var(--t-fast) var(--ease), transform var(--t-fast) var(--ease);
}
.sub-stock-dashboard .incoming-item:hover .incoming-item__chevron {
    color: var(--primary-ink);
    transform: translateX(2px);
}

/* Empty block */
.sub-stock-dashboard .empty-block {
    text-align: center;
    padding: 2.5rem 1rem;
}
.sub-stock-dashboard .empty-block__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: var(--surface-3);
    color: var(--ink-3);
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
}
.sub-stock-dashboard .empty-block__title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--ink-1);
    margin-bottom: 0.2rem;
}
.sub-stock-dashboard .empty-block__caption {
    font-size: 0.82rem;
    color: var(--ink-3);
    margin-bottom: 1rem;
    line-height: 1.4;
}
.sub-stock-dashboard .empty-block__action {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.95rem;
    background: var(--primary);
    color: #fff;
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    min-height: 38px;
    transition: background-color var(--t-fast) var(--ease);
}
.sub-stock-dashboard .empty-block__action:hover { background: var(--primary-ink); }
.sub-stock-dashboard .empty-block__action:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--primary-soft);
}

/* Chart toggle */
.sub-stock-dashboard .chart-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--ink-3);
    cursor: pointer;
    transition: background-color var(--t-fast) var(--ease), transform var(--t-mid) var(--ease);
}
.sub-stock-dashboard .chart-toggle:hover {
    background: var(--surface-hover);
    color: var(--ink-1);
}
.sub-stock-dashboard .chart-toggle[aria-expanded="true"] {
    transform: rotate(180deg);
    color: var(--primary-ink);
}

/* Floating action button */
.sub-stock-fab {
    position: fixed;
    right: 1.25rem;
    bottom: 1.25rem;
    z-index: 1040;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 1.2rem;
    background: #0d6efd;
    color: #ffffff;
    border-radius: 999px;
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 8px 24px rgba(13, 110, 253, 0.30), 0 2px 6px rgba(15, 23, 42, 0.12);
    min-height: 52px;
    transition: background-color 120ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 120ms cubic-bezier(0.16, 1, 0.3, 1);
}
.sub-stock-fab:hover {
    background: #0a58ca;
    color: #ffffff;
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.35), 0 3px 8px rgba(15, 23, 42, 0.15);
}
.sub-stock-fab:active { background: #0a58ca; }
.sub-stock-fab:focus-visible {
    outline: none;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25), 0 8px 24px rgba(13, 110, 253, 0.30);
}
.sub-stock-fab i { font-size: 1.15rem; line-height: 1; }
@media (max-width: 575.98px) {
    .sub-stock-blocked {
        grid-template-columns: 1fr;
        gap: 14px;
        min-height: 0;
        padding: 20px;
    }

    .sub-stock-blocked__icon {
        width: 56px;
        height: 56px;
        font-size: 24px;
    }

    .sub-stock-blocked h2 {
        font-size: 1.18rem;
    }

    .sub-stock-blocked__actions .btn {
        width: 100%;
        justify-content: center;
    }

    .sub-stock-fab {
        right: 1rem;
        bottom: 1rem;
        padding: 0.75rem 1rem;
        font-size: 0.88rem;
        min-height: 48px;
    }
    .sub-stock-fab span {
        display: none;
    }
    .sub-stock-fab { padding: 0; width: 56px; height: 56px; justify-content: center; }
    .sub-stock-fab i { font-size: 1.35rem; }
}

/* Section flash — single soft pulse, no shadow loop */
.sub-stock-dashboard .section-flash {
    animation: sub-stock-section-flash 240ms var(--ease);
}
@keyframes sub-stock-section-flash {
    0%   { background-color: var(--primary-soft); }
    100% { background-color: transparent; }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .sub-stock-dashboard *,
    .sub-stock-dashboard *::before,
    .sub-stock-dashboard *::after {
        animation: none !important;
        transition: opacity 80ms ease-out, color 80ms ease-out !important;
    }
    .sub-stock-fab { transition: none; }
    .sub-stock-dashboard .chart-toggle[aria-expanded="true"] { transform: none; }
    .sub-stock-dashboard .incoming-item:hover .incoming-item__chevron,
    .sub-stock-dashboard .kpi-card--link:hover .kpi-card__more {
        transform: none;
    }
}

/* Mobile refinements */
@media (max-width: 575.98px) {
    .sub-stock-dashboard .kpi-card { padding: 0.85rem 0.95rem; }
    .sub-stock-dashboard .kpi-card__value { font-size: 1.35rem; }
    .sub-stock-dashboard .surface-card__head { padding: 0.75rem 0.95rem; }
    .sub-stock-dashboard .surface-card__body { padding: 0.65rem 0.95rem 0.95rem; }
    .sub-stock-dashboard .critical-strip {
        padding: 0.75rem 0.85rem;
        gap: 0.65rem;
    }
    .sub-stock-dashboard .critical-strip__action {
        padding: 0.4rem 0.7rem;
        font-size: 0.8rem;
    }
}

/* Health badge — semantic tokens */
.sub-stock-page-head .health-badge {
    --hb-bg: rgba(15, 23, 42, 0.06);
    --hb-fg: #4a5568;
    --hb-dot: #4a5568;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    background: var(--hb-bg);
    color: var(--hb-fg);
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.3;
}
.sub-stock-page-head .health-badge--lg {
    padding: 0.4rem 0.85rem;
    font-size: 0.88rem;
}
.sub-stock-page-head .health-badge__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--hb-dot);
    flex-shrink: 0;
}
.sub-stock-page-head .health-badge--lg .health-badge__dot { width: 10px; height: 10px; }
.sub-stock-page-head .health-badge--critical {
    --hb-bg: rgba(185, 28, 28, 0.10);
    --hb-fg: #b91c1c;
    --hb-dot: #b91c1c;
}
.sub-stock-page-head .health-badge--warning {
    --hb-bg: rgba(180, 83, 9, 0.10);
    --hb-fg: #b45309;
    --hb-dot: #b45309;
}
.sub-stock-page-head .health-badge--good {
    --hb-bg: rgba(13, 110, 253, 0.10);
    --hb-fg: #0a58ca;
    --hb-dot: #0d6efd;
}
.sub-stock-page-head .health-badge--excellent {
    --hb-bg: rgba(21, 128, 61, 0.10);
    --hb-fg: #15803d;
    --hb-dot: #15803d;
}
</style>

<?php
$chartCategoriesJson = json_encode($chartData['categories']);
$chartSeriesJson = json_encode($chartData['series']);
$js = <<<JS
(function() {
    var categories = {$chartCategoriesJson};
    var seriesData = {$chartSeriesJson};
    if (!categories || categories.length === 0) categories = ['-'];
    if (!seriesData || seriesData.length === 0) seriesData = [0];

    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var options = {
        series: [{ name: 'มูลค่าที่ได้รับ (บาท)', data: seriesData }],
        chart: {
            height: 260,
            type: 'area',
            toolbar: { show: false },
            fontFamily: 'inherit',
            animations: { enabled: !prefersReduced, speed: 240, easing: 'easeout' },
            sparkline: { enabled: false }
        },
        colors: ['#0d6efd'],
        dataLabels: { enabled: false },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.28, opacityTo: 0.02, stops: [0, 95] }
        },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
            categories: categories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { fontSize: '11px', colors: '#718096' } }
        },
        yaxis: {
            labels: {
                style: { fontSize: '11px', colors: '#718096' },
                formatter: function(v) { return v >= 1000 ? (v/1000).toFixed(1) + 'k' : v; }
            }
        },
        grid: { borderColor: 'rgba(15,23,42,0.06)', strokeDashArray: 3, padding: { left: 4, right: 4 } },
        markers: { size: 0, hover: { size: 5 } },
        tooltip: {
            theme: 'light',
            y: { formatter: function(v) { return v != null ? Number(v).toLocaleString('th-TH') + ' บาท' : ''; } }
        }
    };

    var el = document.querySelector('#usageApexChart');
    if (!el) return;
    var chart = new ApexCharts(el, options);
    chart.render();

    var collapseEl = document.getElementById('trendChartCollapse');
    if (collapseEl) {
        collapseEl.addEventListener('shown.bs.collapse', function() { chart.render(); });
    }
})();

$('#subWarehouseFilter').on('change', function() {
    $('#form-sub-warehouse').submit();
});

// Smooth scroll-to-section
$(document).on('click', 'a[href*="#section-"]', function(e) {
    var href = this.getAttribute('href');
    var hashIdx = href.indexOf('#section-');
    if (hashIdx === -1) return;
    var hash = href.substring(hashIdx);
    var target = document.querySelector(hash);
    if (!target) return;
    if (href.substring(0, hashIdx) === '' || href.substring(0, hashIdx) === window.location.pathname + window.location.search) {
        e.preventDefault();
    } else {
        return;
    }
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    target.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });
    target.classList.add('section-flash');
    setTimeout(function() { target.classList.remove('section-flash'); }, 280);
});
JS;
$this->registerJs($js, View::POS_READY);
?>
