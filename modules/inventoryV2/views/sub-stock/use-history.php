<?php
/**
 * Full disbursement history with server-side filtering and summary.
 *
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $filters
 * @var array $summary
 * @var array $warehouseOptions
 * @var array $sourceTypeOptions
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'ประวัติการตัดจ่าย';
$this->params['breadcrumbs'][] = ['label' => 'คลังย่อย', 'url' => ['/inventory-v2/sub-stock/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$orders = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$currentWarehouseId = !empty($filters['warehouse_id']) ? (int) $filters['warehouse_id'] : null;
$currentWarehouseName = $currentWarehouseId !== null && isset($warehouseOptions[$currentWarehouseId])
    ? (string) $warehouseOptions[$currentWarehouseId]
    : null;

$sourceLabels = [
    'USAGE' => 'ตัดจ่ายใช้งาน',
    'REQUEST' => 'ใบขอเบิก',
    'OUT' => 'ตัดจ่าย',
    'REPAIR' => 'งานซ่อม',
];

$decodeData = static function ($order): array {
    $data = $order->data_json ?? [];
    if (is_string($data)) {
        $data = json_decode($data, true) ?: [];
    }
    return is_array($data) ? $data : [];
};

$orderMeta = static function ($order) use ($decodeData, $sourceLabels): array {
    $data = $decodeData($order);
    $jobType = trim((string) ($data['job_type'] ?? ''));
    $reference = trim((string) ($order->ref ?? ''));

    if ($jobType === '' && !empty($data['repair_number'])) {
        $jobType = 'ซ่อม #' . (string) $data['repair_number'];
    }

    if ($reference === '' && !empty($data['reference'])) {
        $reference = (string) $data['reference'];
    }

    if ($reference === '' && !empty($data['helpdesk_id'])) {
        $reference = 'helpdesk_id=' . (string) $data['helpdesk_id'];
    }

    $sourceType = (string) ($order->source_type ?? '');
    $isRepair = $jobType !== '' && strpos($jobType, 'ซ่อม') === 0;

    return [
        'sourceLabel' => $isRepair ? 'งานซ่อม' : ($sourceLabels[$sourceType] ?? ($sourceType !== '' ? $sourceType : 'ตัดจ่าย')),
        'sourceClass' => $isRepair ? 'is-repair' : ($sourceType === 'USAGE' ? 'is-usage' : 'is-default'),
        'jobType' => $jobType,
        'reference' => $reference,
    ];
};

$formatQty = static function ($value): string {
    $formatted = number_format((float) $value, 2);
    return rtrim(rtrim($formatted, '0'), '.');
};

$formatMoney = static function ($value): string {
    return number_format((float) $value, 2);
};
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->render('_page_head', [
    'icon'  => 'bi-clock-history',
    'title' => $this->title,
    'currentWarehouseName' => $currentWarehouseName,
]) ?>
<?php $this->endBlock(); ?>


<div class="use-history-page">
    <div class="history-head">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p>ค้นหาและสรุปมูลค่าการตัดจ่ายตามช่วงเวลา คลังย่อย และรายการที่เกี่ยวข้อง</p>
        </div>
        <div class="history-head__nav">
            <?= $this->render('_menu_sub_stock', [
                'active' => 'use-history',
                'currentWarehouseId' => $currentWarehouseId,
            ]) ?>
        </div>
    </div>

    <section class="history-filter" aria-label="ตัวกรองประวัติการตัดจ่าย">
        <form method="get" action="<?= Url::to(['/inventory-v2/sub-stock/use-history']) ?>" class="history-filter__form">
            <div class="history-field history-field--search">
                <label for="history-q">ค้นหา</label>
                <div class="history-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        id="history-q"
                        type="search"
                        name="q"
                        value="<?= Html::encode($filters['q'] ?? '') ?>"
                        placeholder="เลขเอกสาร, งานซ่อม, helpdesk_id, รหัสหรือชื่อวัสดุ"
                    >
                </div>
            </div>

            <div class="history-field">
                <label for="history-date-from">ตั้งแต่วันที่</label>
                <input id="history-date-from" type="date" name="date_from" value="<?= Html::encode($filters['date_from'] ?? '') ?>">
            </div>

            <div class="history-field">
                <label for="history-date-to">ถึงวันที่</label>
                <input id="history-date-to" type="date" name="date_to" value="<?= Html::encode($filters['date_to'] ?? '') ?>">
            </div>

            <div class="history-field">
                <label for="history-warehouse">คลังย่อย</label>
                <select id="history-warehouse" name="warehouse_id">
                    <option value="0">ทุกคลังที่มีสิทธิ</option>
                    <?php foreach ($warehouseOptions as $id => $name): ?>
                        <option value="<?= (int) $id ?>" <?= (int) ($filters['warehouse_id'] ?? 0) === (int) $id ? 'selected' : '' ?>>
                            <?= Html::encode($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="history-field">
                <label for="history-source-type">ประเภท</label>
                <select id="history-source-type" name="source_type">
                    <option value="">ทุกประเภท</option>
                    <?php foreach ($sourceTypeOptions as $sourceType): ?>
                        <?php $sourceType = (string) $sourceType; ?>
                        <option value="<?= Html::encode($sourceType) ?>" <?= ($filters['source_type'] ?? '') === $sourceType ? 'selected' : '' ?>>
                            <?= Html::encode($sourceLabels[$sourceType] ?? $sourceType) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="history-filter__actions">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                    <span>ค้นหาประวัติ</span>
                </button>
                <a href="<?= Url::to(['/inventory-v2/sub-stock/use-history']) ?>" class="btn btn-outline-secondary">ล้างตัวกรอง</a>
            </div>
        </form>
    </section>

    <section class="history-summary" aria-label="สรุปตามผลค้นหา">
        <div class="history-summary__item">
            <span>เอกสาร</span>
            <strong><?= number_format((int) ($summary['order_count'] ?? 0)) ?></strong>
        </div>
        <div class="history-summary__item">
            <span>รายการวัสดุ</span>
            <strong><?= number_format((int) ($summary['line_count'] ?? 0)) ?></strong>
        </div>
        <div class="history-summary__item">
            <span>จำนวนรวม</span>
            <strong><?= Html::encode($formatQty($summary['total_qty'] ?? 0)) ?></strong>
        </div>
        <div class="history-summary__item history-summary__item--value">
            <span>มูลค่ารวม</span>
            <strong><?= Html::encode($formatMoney($summary['total_value'] ?? 0)) ?></strong>
            <em>บาท</em>
        </div>
    </section>

    <section class="history-results" aria-label="รายการประวัติการตัดจ่าย">
        <div class="history-results__bar">
            <div>
                <h2>รายการที่พบ</h2>
                <span><?= number_format((int) $dataProvider->getTotalCount()) ?> รายการ</span>
            </div>
            <span class="history-page-size">แสดง <?= (int) $pagination->getPageSize() ?> รายการต่อหน้า</span>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="history-list">
                <?php foreach ($orders as $index => $order): ?>
                    <?php
                    $meta = $orderMeta($order);
                    $details = $order->stockDetails ?: [];
                    $totalQty = 0.0;
                    $totalValue = 0.0;
                    foreach ($details as $detail) {
                        $qty = (float) ($detail->qty ?? 0);
                        $price = (float) ($detail->unit_price ?? 0);
                        $totalQty += $qty;
                        $totalValue += $qty * $price;
                    }
                    $dateTs = $order->order_date ? strtotime($order->order_date) : false;
                    ?>
                    <article class="history-row" style="--i: <?= (int) ($index % 12) ?>">
                        <div class="history-row__main">
                            <div class="history-row__icon <?= Html::encode($meta['sourceClass']) ?>">
                                <i class="bi <?= $meta['sourceClass'] === 'is-repair' ? 'bi-wrench-adjustable' : 'bi-box-arrow-up-right' ?>" aria-hidden="true"></i>
                            </div>
                            <div class="history-row__copy">
                                <div class="history-row__title">
                                    <strong><?= Html::encode($order->order_no ?: 'ไม่มีเลขเอกสาร') ?></strong>
                                    <span><?= Html::encode($meta['sourceLabel']) ?></span>
                                </div>
                                <div class="history-row__meta">
                                    <span>
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        <?= $dateTs ? Html::encode(date('d/m/Y H:i', $dateTs)) : '-' ?>
                                    </span>
                                    <?php if (!empty($order->mainWarehouse)): ?>
                                        <span>
                                            <i class="bi bi-building" aria-hidden="true"></i>
                                            <?= Html::encode($order->mainWarehouse->warehouse_name) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($meta['reference'] !== ''): ?>
                                        <span>
                                            <i class="bi bi-link-45deg" aria-hidden="true"></i>
                                            <?= Html::encode($meta['reference']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($meta['jobType'] !== ''): ?>
                                    <div class="history-row__note"><?= Html::encode($meta['jobType']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($details)): ?>
                                    <div class="history-items">
                                        <?php foreach (array_slice($details, 0, 3) as $detail): ?>
                                            <span>
                                                <?= Html::encode($detail->item->title ?? $detail->item_code) ?>
                                                <b><?= Html::encode($formatQty($detail->qty ?? 0)) ?></b>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($details) > 3): ?>
                                            <span>+<?= count($details) - 3 ?> รายการ</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="history-row__numbers">
                            <div>
                                <span>จำนวน</span>
                                <strong><?= Html::encode($formatQty($totalQty)) ?></strong>
                            </div>
                            <div>
                                <span>มูลค่า</span>
                                <strong><?= Html::encode($formatMoney($totalValue)) ?></strong>
                            </div>
                            <?= Html::a(
                                '<i class="bi bi-eye" aria-hidden="true"></i><span>ดูรายละเอียด</span>',
                                ['/inventory-v2/issue/view-modal', 'id' => $order->id],
                                [
                                    'class' => 'btn btn-outline-primary btn-sm open-modal history-row__action',
                                    'data' => ['size' => 'modal-xl'],
                                    'title' => 'ดูรายละเอียดใบตัดจ่าย',
                                ]
                            ) ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="history-pagination-wrap">
                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination history-pagination mb-0'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
                    'activePageCssClass' => 'active',
                    'disabledPageCssClass' => 'disabled',
                    'maxButtonCount' => 5,
                ]) ?>
            </div>
        <?php else: ?>
            <div class="history-empty">
                <div class="history-empty__icon">
                    <i class="bi bi-inbox" aria-hidden="true"></i>
                </div>
                <h2>ไม่พบประวัติการตัดจ่าย</h2>
                <p>ลองปรับคำค้นหา ช่วงวันที่ หรือเลือกคลังย่อยอื่นที่มีสิทธิ</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
.use-history-page {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #667085;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --line: rgba(15, 23, 42, 0.09);
    --line-strong: rgba(15, 23, 42, 0.16);
    --primary: #0d6efd;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --success: #15803d;
    --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.11);
    --radius-lg: 10px;
    --radius-md: 8px;
    color: var(--ink-1);
    padding: 0.25rem 0 2rem;
}

.history-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.history-head h1,
.history-results__bar h2,
.history-empty h2 {
    margin: 0;
    color: var(--ink-1);
    font-weight: 700;
    letter-spacing: 0;
    text-wrap: balance;
}

.history-head h1 {
    font-size: clamp(1.35rem, 1.1rem + 0.8vw, 1.9rem);
}

.history-head p {
    margin: 0.35rem 0 0;
    color: var(--ink-2);
    max-width: 68ch;
    line-height: 1.55;
    text-wrap: pretty;
}

.history-filter__actions .btn,
.history-row__action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    min-height: 38px;
    border-radius: var(--radius-md);
    white-space: nowrap;
}

.history-head__nav {
    display: flex;
    justify-content: flex-end;
    min-width: min(100%, 620px);
}

.history-filter,
.history-summary,
.history-results {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-lg);
    box-shadow: 0 6px 18px rgba(17, 24, 39, 0.05);
}

.history-filter {
    padding: 1rem;
}

.history-filter__form {
    display: grid;
    grid-template-columns: minmax(240px, 1.6fr) repeat(4, minmax(140px, 1fr)) auto;
    gap: 0.75rem;
    align-items: end;
}

.history-field {
    min-width: 0;
}

.history-field label {
    display: block;
    margin-bottom: 0.35rem;
    color: var(--ink-2);
    font-size: 0.82rem;
    font-weight: 600;
}

.history-field input,
.history-field select {
    width: 100%;
    min-height: 40px;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-md);
    color: var(--ink-1);
    background: var(--surface);
    padding: 0.48rem 0.65rem;
    outline: none;
    transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
}

.history-field input::placeholder {
    color: #5f6f85;
}

.history-field input:focus,
.history-field select:focus {
    border-color: rgba(13, 110, 253, 0.55);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12);
}

.history-search {
    position: relative;
}

.history-search i {
    position: absolute;
    left: 0.7rem;
    top: 50%;
    color: var(--ink-3);
    transform: translateY(-50%);
}

.history-search input {
    padding-left: 2.1rem;
}

.history-filter__actions {
    display: flex;
    gap: 0.5rem;
}

.history-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-top: 1rem;
    overflow: hidden;
}

.history-summary__item {
    min-width: 0;
    padding: 0.95rem 1rem;
    border-right: 1px solid var(--line);
    background: linear-gradient(180deg, var(--surface), var(--surface-2));
}

.history-summary__item:last-child {
    border-right: 0;
}

.history-summary__item span,
.history-summary__item em {
    display: block;
    color: var(--ink-2);
    font-size: 0.78rem;
    font-style: normal;
    font-weight: 600;
}

.history-summary__item strong {
    display: block;
    margin-top: 0.22rem;
    color: var(--ink-1);
    font-size: clamp(1.15rem, 1rem + 0.55vw, 1.55rem);
    font-weight: 750;
    line-height: 1.15;
}

.history-summary__item--value {
    background: linear-gradient(180deg, rgba(21, 128, 61, 0.08), rgba(13, 110, 253, 0.06));
}

.history-results {
    margin-top: 1rem;
    overflow: hidden;
}

.history-results__bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border-bottom: 1px solid var(--line);
    background: var(--surface-2);
}

.history-results__bar h2 {
    font-size: 1rem;
}

.history-results__bar span,
.history-page-size {
    color: var(--ink-2);
    font-size: 0.82rem;
    font-weight: 600;
}

.history-list {
    display: grid;
}

.history-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(260px, auto);
    gap: 1rem;
    align-items: center;
    padding: 0.95rem 1rem;
    border-bottom: 1px solid var(--line);
    background: var(--surface);
    animation: historyRowIn 220ms cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: calc(var(--i, 0) * 20ms);
}

.history-row:last-child {
    border-bottom: 0;
}

.history-row:hover {
    background: var(--surface-2);
}

.history-row__main {
    display: flex;
    gap: 0.75rem;
    min-width: 0;
}

.history-row__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    color: var(--primary);
    background: var(--primary-soft);
}

.history-row__icon.is-repair {
    color: var(--warning);
    background: var(--warning-soft);
}

.history-row__icon.is-usage {
    color: var(--success);
    background: var(--success-soft);
}

.history-row__copy {
    min-width: 0;
}

.history-row__title {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
}

.history-row__title strong {
    color: var(--ink-1);
    font-size: 0.98rem;
    font-weight: 700;
}

.history-row__title span {
    display: inline-flex;
    align-items: center;
    min-height: 24px;
    padding: 0.18rem 0.52rem;
    border-radius: 999px;
    color: var(--ink-2);
    background: var(--surface-3);
    font-size: 0.76rem;
    font-weight: 650;
}

.history-row__meta,
.history-items {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem 0.7rem;
    margin-top: 0.42rem;
}

.history-row__meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    min-width: 0;
    color: var(--ink-2);
    font-size: 0.8rem;
}

.history-row__note {
    margin-top: 0.38rem;
    color: var(--warning);
    font-size: 0.82rem;
    font-weight: 650;
}

.history-items span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    max-width: 100%;
    padding: 0.2rem 0.5rem;
    border-radius: var(--radius-md);
    color: var(--ink-2);
    background: var(--surface-2);
    font-size: 0.78rem;
}

.history-items b {
    color: var(--ink-1);
}

.history-row__numbers {
    display: grid;
    grid-template-columns: repeat(2, minmax(84px, 1fr)) auto;
    gap: 0.65rem;
    align-items: center;
}

.history-row__numbers div {
    min-width: 0;
    text-align: right;
}

.history-row__numbers span {
    display: block;
    color: var(--ink-3);
    font-size: 0.72rem;
    font-weight: 650;
}

.history-row__numbers strong {
    display: block;
    color: var(--ink-1);
    font-size: 0.94rem;
    font-weight: 750;
}

.history-pagination-wrap {
    display: flex;
    justify-content: flex-end;
    padding: 0.9rem 1rem;
    border-top: 1px solid var(--line);
    background: var(--surface-2);
}

.history-pagination .page-link {
    color: var(--ink-2);
    border-color: var(--line-strong);
}

.history-pagination .active .page-link {
    color: #fff;
    background: var(--primary);
    border-color: var(--primary);
}

.history-empty {
    padding: 2.4rem 1rem;
    text-align: center;
}

.history-empty__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin-bottom: 0.75rem;
    border-radius: var(--radius-lg);
    color: var(--ink-2);
    background: var(--surface-3);
}

.history-empty h2 {
    font-size: 1.05rem;
}

.history-empty p {
    margin: 0.35rem auto 0;
    max-width: 48ch;
    color: var(--ink-2);
}

@keyframes historyRowIn {
    from {
        opacity: 0.72;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 1199.98px) {
    .history-filter__form {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .history-field--search,
    .history-filter__actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 767.98px) {
    .history-head,
    .history-results__bar {
        flex-direction: column;
        align-items: stretch;
    }

    .history-filter__form,
    .history-summary,
    .history-row {
        grid-template-columns: 1fr;
    }

    .history-filter__actions {
        flex-direction: column;
    }

    .history-filter__actions .btn {
        width: 100%;
    }

    .history-head__nav {
        justify-content: stretch;
    }

    .history-head__nav .inventory-nav-sub,
    .history-head__nav .inventory-nav-sub > div {
        width: 100%;
    }

    .history-summary__item {
        border-right: 0;
        border-bottom: 1px solid var(--line);
    }

    .history-summary__item:last-child {
        border-bottom: 0;
    }

    .history-row__numbers {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: stretch;
    }

    .history-row__numbers div {
        text-align: left;
    }

    .history-row__action {
        grid-column: 1 / -1;
        width: 100%;
    }

    .history-pagination-wrap {
        justify-content: center;
        overflow-x: auto;
    }
}

@media (prefers-reduced-motion: reduce) {
    .history-row {
        animation: none;
    }

    .history-field input,
    .history-field select {
        transition: none;
    }
}
</style>
