<?php
/**
 * Activity feed — ประวัติการตัดจ่าย/การใช้งาน (Mobile-first card list)
 * ไม่ใช้ table ตามหลัก mobile-first
 *
 * @var array $usageHistory
 * @var int|null $currentWarehouseId
 */
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="activity-feed card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 px-3 px-md-4 rounded-top-4">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary"
                  style="width: 36px; height: 36px;">
                <i class="bi bi-activity"></i>
            </span>
            <div>
                <h6 class="mb-0 fw-bold text-body">ประวัติการตัดจ่าย</h6>
                <div class="text-muted small">
                    คลัง: <?= Html::encode($currentWarehouseId !== null ? '#' . $currentWarehouseId : 'ทั้งหมด') ?>
                    <?php if (!empty($_GET['helpdesk_id'])): ?>
                        <span class="ms-1">· helpdesk_id: <?= Html::encode((string) $_GET['helpdesk_id']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <span class="badge text-bg-light text-muted rounded-pill"><?= count($usageHistory) ?> รายการ</span>
    </div>

    <?php if (!empty($usageHistory)): ?>
        <ul class="list-unstyled mb-0 activity-feed__list">
            <?php foreach ($usageHistory as $i => $o): ?>
                <?php
                $dj = $o->data_json;
                if (is_string($dj)) {
                    $dj = json_decode($dj, true) ?: [];
                }
                $jobType = (string) ($dj['job_type'] ?? '');
                $reference = (string) ($dj['reference'] ?? '');

                if ($jobType === '' && isset($dj['repair_number']) && (string) $dj['repair_number'] !== '') {
                    $jobType = 'ซ่อม #' . (string) $dj['repair_number'];
                }
                if ($reference === '' && isset($dj['helpdesk_id']) && (string) $dj['helpdesk_id'] !== '') {
                    $reference = 'helpdesk_id=' . (string) $dj['helpdesk_id'];
                }

                $totalQty = 0.0;
                $itemCount = 0;
                $details = is_array($o->stockDetails) ? $o->stockDetails : [];
                foreach ($details as $d) {
                    $totalQty += (float) ($d->qty ?? 0);
                    $itemCount++;
                }

                $ts = $o->order_date ? strtotime($o->order_date) : null;
                $dateLabel = $ts ? date('d/m/Y', $ts) : '-';
                $timeLabel = $ts ? date('H:i', $ts) : '';

                $sourceType = (string) ($o->source_type ?? '');
                $isRepair = $jobType !== '' && strpos($jobType, 'ซ่อม') === 0;
                if ($isRepair) {
                    $dotColor = 'warning';
                    $sourceLabel = 'งานซ่อม';
                    $iconName = 'bi-wrench-adjustable';
                } elseif ($sourceType === 'USAGE') {
                    $dotColor = 'primary';
                    $sourceLabel = 'ตัดจ่ายใช้งาน';
                    $iconName = 'bi-box-arrow-up-right';
                } else {
                    $dotColor = 'secondary';
                    $sourceLabel = $sourceType !== '' ? $sourceType : 'การใช้งาน';
                    $iconName = 'bi-arrow-right-circle';
                }
                ?>
                <li class="activity-feed__item d-flex gap-3 px-3 px-md-4 py-3 position-relative">
                    <div class="activity-feed__rail">
                        <span class="activity-feed__dot bg-<?= $dotColor ?>">
                            <i class="bi <?= $iconName ?>"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-baseline gap-2 mb-1">
                            <span class="fw-semibold text-body font-monospace"><?= Html::encode($o->order_no) ?></span>
                            <span class="badge rounded-pill bg-<?= $dotColor ?> bg-opacity-10 text-<?= $dotColor ?> fw-semibold">
                                <?= Html::encode($sourceLabel) ?>
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-x-3 gap-y-1 text-muted small">
                            <span><i class="bi bi-calendar3 me-1"></i><?= Html::encode($dateLabel) ?> <?= Html::encode($timeLabel) ?></span>
                            <?php if ($jobType !== ''): ?>
                                <span><i class="bi bi-briefcase me-1"></i><?= Html::encode($jobType) ?></span>
                            <?php endif; ?>
                            <?php if ($reference !== ''): ?>
                                <span><i class="bi bi-link-45deg me-1"></i><?= Html::encode($reference) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($_GET['helpdesk_id'])): ?>
                            <div class="text-muted small mt-1 opacity-75">
                                source_type: <?= Html::encode($sourceType !== '' ? $sourceType : '-') ?> ·
                                status: <?= Html::encode((string) ($o->status ?? '-')) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold text-body" style="font-size: 1.05rem;"><?= number_format($totalQty, 2) ?></div>
                        <div class="text-muted small"><?= $itemCount ?> รายการ</div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="px-3 px-md-4 py-5 text-center activity-feed__empty">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-muted mb-3"
                 style="width: 56px; height: 56px;">
                <i class="bi bi-inbox fs-3"></i>
            </div>
            <div class="fw-semibold text-body mb-1">ยังไม่มีประวัติการตัดจ่าย</div>
            <div class="text-muted small mb-3">เมื่อมีการบันทึกตัดจ่ายพัสดุ รายการจะปรากฏที่นี่</div>
            <a href="<?= Url::to(['/inventory-v2/sub-stock/issue']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-box-arrow-up-right me-1"></i>บันทึกการจ่ายแรก
            </a>
        </div>
    <?php endif; ?>
</div>
