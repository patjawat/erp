<?php
/**
 * Compact disbursement activity feed for dashboard and issue page.
 *
 * @var array $usageHistory
 * @var int|null $currentWarehouseId
 */

use yii\helpers\Html;
use yii\helpers\Url;

$visibleCount = count($usageHistory);
$historyUrl = ['/inventory-v2/sub-stock/use-history'];
if (!empty($currentWarehouseId)) {
    $historyUrl['warehouse_id'] = (int) $currentWarehouseId;
}

$formatQty = static function ($value): string {
    $formatted = number_format((float) $value, 2);
    return rtrim(rtrim($formatted, '0'), '.');
};

$formatMoney = static function ($value): string {
    return number_format((float) $value, 2);
};
?>

<section class="activity-feed" aria-labelledby="activity-feed-heading">
    <div class="activity-feed__head">
        <div class="activity-feed__title-wrap">
            <span class="activity-feed__mark">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
            </span>
            <div>
                <h2 id="activity-feed-heading">ประวัติการตัดจ่าย</h2>
                <p>
                    <?= $visibleCount >= 20 ? 'แสดง 20 รายการล่าสุด' : 'รายการล่าสุดของคลังย่อย' ?>
                    <?php if (!empty($currentWarehouseId)): ?>
                        <span>คลัง #<?= Html::encode((string) $currentWarehouseId) ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <div class="activity-feed__actions">
            <span class="activity-feed__count"><?= number_format($visibleCount) ?> รายการ</span>
            <a href="<?= Url::to($historyUrl) ?>" class="btn btn-outline-primary btn-sm activity-feed__all">
                <i class="bi bi-list-ul" aria-hidden="true"></i>
                <span>แสดงทั้งหมด</span>
            </a>
        </div>
    </div>

    <?php if (!empty($usageHistory)): ?>
        <div class="activity-feed__list">
            <?php foreach ($usageHistory as $index => $order): ?>
                <?php
                $data = $order->data_json ?? [];
                if (is_string($data)) {
                    $data = json_decode($data, true) ?: [];
                }
                $data = is_array($data) ? $data : [];

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
                $sourceLabel = $isRepair
                    ? 'งานซ่อม'
                    : ($sourceType === 'USAGE' ? 'ตัดจ่ายใช้งาน' : ($sourceType !== '' ? $sourceType : 'ตัดจ่าย'));
                $sourceClass = $isRepair ? 'is-repair' : ($sourceType === 'USAGE' ? 'is-usage' : 'is-default');

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

                <article class="activity-feed__item" style="--i: <?= (int) ($index % 10) ?>">
                    <div class="activity-feed__icon <?= Html::encode($sourceClass) ?>">
                        <i class="bi <?= $isRepair ? 'bi-wrench-adjustable' : 'bi-box-arrow-up-right' ?>" aria-hidden="true"></i>
                    </div>

                    <div class="activity-feed__body">
                        <div class="activity-feed__line">
                            <strong><?= Html::encode($order->order_no ?: 'ไม่มีเลขเอกสาร') ?></strong>
                            <span><?= Html::encode($sourceLabel) ?></span>
                        </div>
                        <div class="activity-feed__meta">
                            <span>
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                <?= $dateTs ? Html::encode(date('d/m/Y H:i', $dateTs)) : '-' ?>
                            </span>
                            <span>
                                <i class="bi bi-box-seam" aria-hidden="true"></i>
                                <?= count($details) ?> รายการวัสดุ
                            </span>
                            <?php if ($reference !== ''): ?>
                                <span>
                                    <i class="bi bi-link-45deg" aria-hidden="true"></i>
                                    <?= Html::encode($reference) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($jobType !== ''): ?>
                            <div class="activity-feed__note"><?= Html::encode($jobType) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="activity-feed__numbers">
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
                                'class' => 'btn btn-sm btn-outline-primary activity-feed__detail open-modal',
                                'data' => ['size' => 'modal-xl'],
                                'title' => 'ดูรายละเอียดใบตัดจ่าย',
                            ]
                        ) ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="activity-feed__empty">
            <div class="activity-feed__empty-icon">
                <i class="bi bi-inbox" aria-hidden="true"></i>
            </div>
            <h3>ยังไม่มีประวัติการตัดจ่าย</h3>
            <p>เมื่อมีการตัดจ่าย รายการล่าสุดจะแสดงที่นี่ทันที</p>
            <a href="<?= Url::to(['/inventory-v2/sub-stock/issue']) ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                <span>ไปหน้าตัดจ่าย</span>
            </a>
        </div>
    <?php endif; ?>
</section>

<style>
.activity-feed {
    --af-ink-1: var(--ink-1, #1a202c);
    --af-ink-2: var(--ink-2, #4a5568);
    --af-ink-3: var(--ink-3, #667085);
    --af-surface: var(--surface, #ffffff);
    --af-surface-2: var(--surface-2, #f7f9fc);
    --af-surface-3: var(--surface-3, #eef2f7);
    --af-line: var(--line, rgba(15, 23, 42, 0.09));
    --af-primary: var(--primary, #0d6efd);
    --af-primary-soft: var(--primary-soft, rgba(13, 110, 253, 0.08));
    --af-success: var(--success, #15803d);
    --af-success-soft: var(--success-soft, rgba(21, 128, 61, 0.10));
    --af-warning: var(--warning, #b45309);
    --af-warning-soft: var(--warning-soft, rgba(180, 83, 9, 0.11));
    background: var(--af-surface);
    border: 1px solid var(--af-line);
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(17, 24, 39, 0.05);
    overflow: hidden;
}

.activity-feed__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1rem;
    background: linear-gradient(180deg, var(--af-surface), var(--af-surface-2));
    border-bottom: 1px solid var(--af-line);
}

.activity-feed__title-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.activity-feed__mark,
.activity-feed__icon,
.activity-feed__empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.activity-feed__mark {
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    color: var(--af-primary);
    background: var(--af-primary-soft);
}

.activity-feed h2,
.activity-feed h3 {
    margin: 0;
    color: var(--af-ink-1);
    font-weight: 700;
    letter-spacing: 0;
    text-wrap: balance;
}

.activity-feed h2 {
    font-size: 1rem;
}

.activity-feed h3 {
    font-size: 1.02rem;
}

.activity-feed p {
    margin: 0.22rem 0 0;
    color: var(--af-ink-2);
    font-size: 0.82rem;
    line-height: 1.45;
}

.activity-feed p span {
    margin-left: 0.4rem;
}

.activity-feed__actions {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.activity-feed__count {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0.22rem 0.58rem;
    border-radius: 999px;
    color: var(--af-ink-2);
    background: var(--af-surface-3);
    font-size: 0.78rem;
    font-weight: 650;
}

.activity-feed__all,
.activity-feed__detail,
.activity-feed__empty .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.38rem;
    min-height: 34px;
    border-radius: 8px;
    white-space: nowrap;
}

.activity-feed__list {
    display: grid;
}

.activity-feed__item {
    display: grid;
    grid-template-columns: 40px minmax(0, 1fr) minmax(245px, auto);
    gap: 0.75rem;
    align-items: center;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--af-line);
    background: var(--af-surface);
    animation: activityItemIn 210ms cubic-bezier(0.22, 1, 0.36, 1) both;
    animation-delay: calc(var(--i, 0) * 18ms);
}

.activity-feed__item:last-child {
    border-bottom: 0;
}

.activity-feed__item:hover {
    background: var(--af-surface-2);
}

.activity-feed__icon {
    width: 40px;
    height: 40px;
    color: var(--af-primary);
    background: var(--af-primary-soft);
}

.activity-feed__icon.is-usage {
    color: var(--af-success);
    background: var(--af-success-soft);
}

.activity-feed__icon.is-repair {
    color: var(--af-warning);
    background: var(--af-warning-soft);
}

.activity-feed__body {
    min-width: 0;
}

.activity-feed__line {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.activity-feed__line strong {
    color: var(--af-ink-1);
    font-size: 0.95rem;
    font-weight: 720;
}

.activity-feed__line span {
    display: inline-flex;
    align-items: center;
    min-height: 23px;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    color: var(--af-ink-2);
    background: var(--af-surface-3);
    font-size: 0.75rem;
    font-weight: 650;
}

.activity-feed__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.42rem 0.68rem;
    margin-top: 0.4rem;
}

.activity-feed__meta span {
    display: inline-flex;
    align-items: center;
    gap: 0.28rem;
    min-width: 0;
    color: var(--af-ink-2);
    font-size: 0.8rem;
}

.activity-feed__note {
    margin-top: 0.35rem;
    color: var(--af-warning);
    font-size: 0.82rem;
    font-weight: 650;
}

.activity-feed__numbers {
    display: grid;
    grid-template-columns: repeat(2, minmax(72px, 1fr)) auto;
    gap: 0.55rem;
    align-items: center;
}

.activity-feed__numbers div {
    text-align: right;
}

.activity-feed__numbers span {
    display: block;
    color: var(--af-ink-3);
    font-size: 0.72rem;
    font-weight: 650;
}

.activity-feed__numbers strong {
    display: block;
    color: var(--af-ink-1);
    font-size: 0.9rem;
    font-weight: 750;
}

.activity-feed__empty {
    padding: 2rem 1rem;
    text-align: center;
}

.activity-feed__empty-icon {
    width: 46px;
    height: 46px;
    margin-bottom: 0.75rem;
    color: var(--af-ink-2);
    background: var(--af-surface-3);
}

.activity-feed__empty p {
    margin: 0.35rem auto 0.85rem;
    max-width: 46ch;
}

@keyframes activityItemIn {
    from {
        opacity: 0.76;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 991.98px) {
    .activity-feed__item {
        grid-template-columns: 40px minmax(0, 1fr);
    }

    .activity-feed__numbers {
        grid-column: 1 / -1;
        grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
        padding-left: 52px;
    }
}

@media (max-width: 575.98px) {
    .activity-feed__head {
        align-items: flex-start;
        flex-direction: column;
    }

    .activity-feed__actions,
    .activity-feed__all {
        width: 100%;
    }

    .activity-feed__actions {
        justify-content: stretch;
    }

    .activity-feed__item {
        grid-template-columns: 1fr;
    }

    .activity-feed__icon {
        display: none;
    }

    .activity-feed__numbers {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding-left: 0;
    }

    .activity-feed__numbers div {
        text-align: left;
    }

    .activity-feed__detail {
        grid-column: 1 / -1;
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .activity-feed__item {
        animation: none;
    }
}
</style>
