<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var array<int, \app\modules\dms\models\Documents> $documents */
/** @var bool $compact */

$documents = $documents ?? [];
$compact = (bool) ($compact ?? false);
?>
<style>
    .official-doc-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        text-decoration: none;
        color: inherit;
        display: block;
        overflow: hidden;
        transition: box-shadow 0.2s ease, transform 0.15s ease;
    }

    .official-doc-card:hover {
        color: inherit;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .official-doc-card:active {
        transform: scale(0.99);
    }

    .official-doc-card.read {
        border-left: 4px solid rgba(108, 117, 125, 0.35);
    }

    .official-doc-card.unread {
        border-left: 4px solid var(--mobile-primary);
    }

    .official-doc-icon {
        width: 2.85rem;
        height: 2.85rem;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .official-doc-icon.read {
        background: rgba(108, 117, 125, 0.10);
        color: #6c757d;
    }

    .official-doc-icon.unread {
        background: rgba(13, 110, 253, 0.12);
        color: var(--mobile-primary);
    }

    .official-doc-title {
        line-height: 1.35;
    }

    .official-doc-meta {
        font-size: 0.8125rem;
        color: #6c757d;
    }

    .official-doc-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .official-doc-hint {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--mobile-primary);
    }
</style>

<div class="d-flex flex-column gap-2">
    <?php foreach ($documents as $item): ?>
        <?php
        $dataJson = is_array($item->data_json) ? $item->data_json : (is_string($item->data_json) && trim($item->data_json) !== '' ? json_decode($item->data_json, true) : []);
        if (!is_array($dataJson)) {
            $dataJson = [];
        }

        $detailId = (int) ($item->detail_id ?? 0);
        $viewUrl = Url::to(['/mobile/default/news-view', 'id' => $detailId]);
        $isRead = !empty($item->doc_read);
        $statusBadgeClass = $isRead
            ? 'bg-success bg-opacity-10 text-success border border-success-subtle'
            : 'bg-warning bg-opacity-10 text-warning border border-warning-subtle';
        $statusLabel = $isRead ? 'อ่านแล้ว' : 'ยังไม่อ่าน';
        $receivedDate = !empty($item->doc_transactions_date) ? ThaiDateHelper::formatThaiDate($item->doc_transactions_date, 'short') : '—';
        $orgTitle = $item->documentOrg->title ?? '-';
        $description = trim((string) ($dataJson['des'] ?? ''));
        $speed = (string) ($item->doc_speed ?? '');
        $secret = (string) ($item->secret ?? '');
        ?>

        <a href="<?= Html::encode($viewUrl) ?>" class="official-doc-card card <?= $isRead ? 'read' : 'unread' ?>">
            <div class="card-body p-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="official-doc-icon <?= $isRead ? 'read' : 'unread' ?>">
                        <i data-lucide="file-text" style="width: 1.35rem; height: 1.35rem;"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="min-w-0">
                                <h6 class="official-doc-title fw-semibold mb-1 text-truncate">
                                    <?= Html::encode($item->topic ?: '-') ?>
                                </h6>
                                <div class="official-doc-meta text-truncate">
                                    <?= Html::encode($orgTitle) ?> · <?= Html::encode($receivedDate) ?>
                                </div>
                            </div>
                            <span class="badge rounded-pill px-2 py-1 flex-shrink-0 <?= Html::encode($statusBadgeClass) ?>">
                                <?= Html::encode($statusLabel) ?>
                            </span>
                        </div>

                        <div class="official-doc-badges mb-2">
                            <span class="badge bg-light text-primary border rounded-pill fw-normal">
                                เลขรับ <?= Html::encode($item->doc_regis_number ?: '-') ?>
                            </span>
                            <span class="badge bg-light text-secondary border rounded-pill fw-normal">
                                เลขที่ <?= Html::encode($item->doc_number ?: '-') ?>
                            </span>
                            <?php if ($speed === 'ด่วนที่สุด'): ?>
                                <span class="badge text-bg-danger rounded-pill">ด่วนที่สุด</span>
                            <?php endif; ?>
                            <?php if ($secret === 'ลับที่สุด'): ?>
                                <span class="badge text-bg-dark rounded-pill">ลับที่สุด</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!$compact && $description !== ''): ?>
                            <p class="small text-body-secondary mb-2">
                                <?= Html::encode($description) ?>
                            </p>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="official-doc-hint">
                                <?= $compact ? 'แตะเพื่อเปิดดู' : 'เปิดดู คอมเมนต์ และส่งต่อ' ?>
                            </span>
                            <i data-lucide="chevron-right" class="text-secondary flex-shrink-0" style="width: 1rem; height: 1rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>
