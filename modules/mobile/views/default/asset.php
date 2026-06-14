<?php

use app\components\ThaiDateHelper;
use app\modules\am\models\Asset;
use app\modules\mobile\services\MobileAssetHistoryService;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string|int|null $id */
/** @var string|null $code */
/** @var Asset|null $asset */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = $asset ? 'ข้อมูลครุภัณฑ์' : 'ตรวจครุภัณฑ์';
$this->params['mobileSubtitle'] = $asset ? 'รายละเอียดและการแจ้งซ่อม' : 'สแกน QR เพื่อเปิดข้อมูลในระบบ';

$lifecycleLabels = [
    Asset::LIFECYCLE_RECEIVED => 'รับเข้า',
    Asset::LIFECYCLE_ACTIVE => 'ใช้งาน',
    Asset::LIFECYCLE_REPAIR => 'ส่งซ่อม',
    Asset::LIFECYCLE_DISPOSED => 'จำหน่าย',
];
$lifecycleTones = [
    Asset::LIFECYCLE_RECEIVED => 'primary',
    Asset::LIFECYCLE_ACTIVE => 'success',
    Asset::LIFECYCLE_REPAIR => 'warning',
    Asset::LIFECYCLE_DISPOSED => 'danger',
];

$scanUrl = Url::to(['/mobile/default/scan']);
$servicesUrl = Url::to(['/mobile/default/services']);
$hasSearchCode = $code !== null && trim((string) $code) !== '';
$searchedCode = trim((string) ($code ?? ''));

$clip = static function (string $value, int $width = 24): string {
    $value = trim($value);
    if ($value === '') {
        return '-';
    }
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($value, 0, $width, '...', 'UTF-8');
    }

    return strlen($value) > $width ? substr($value, 0, max(0, $width - 3)) . '...' : $value;
};

$assetName = '';
$assetCode = '';
$status = Asset::LIFECYCLE_ACTIVE;
$statusLabel = 'พร้อมใช้งาน';
$statusTone = 'success';
$department = '';
$location = '';
$receiveDate = '';
$typeName = '';
$serialNumber = '';
$photoUrl = null;
$qrPath = '';
$repairUrl = '';
$detailRows = [];
$assetHistoryGroups = [];

$formatActivityDate = static function (?string $value, string $modifier = ''): string {
    $timestamp = trim((string) $value) !== '' ? strtotime((string) $value) : time();
    if ($timestamp === false) {
        $timestamp = time();
    }
    if ($modifier !== '') {
        $shifted = strtotime($modifier, $timestamp);
        if ($shifted !== false) {
            $timestamp = $shifted;
        }
    }

    $dateValue = date('Y-m-d', $timestamp);
    try {
        $dateLabel = ThaiDateHelper::formatThaiDate($dateValue, 'medium');
    } catch (\Throwable $e) {
        $dateLabel = $dateValue;
    }

    return $dateLabel . ' เวลา ' . date('H:i', $timestamp) . ' น.';
};

if ($asset) {
    $assetData = is_array($asset->data_json ?? null) ? $asset->data_json : [];
    $assetName = trim((string) ($asset->AssetitemName() ?: ($asset->name ?? $asset->code ?? 'ครุภัณฑ์')));
    $assetCode = trim((string) ($asset->code ?? ''));
    $status = (string) ($asset->lifecycle_status ?? Asset::LIFECYCLE_ACTIVE);
    $statusLabel = $lifecycleLabels[$status] ?? ($status !== '' ? $status : 'ใช้งาน');
    $statusTone = $lifecycleTones[$status] ?? 'primary';
    $department = trim((string) ($asset->departmentName() ?: ''));
    $location = trim((string) ($assetData['location'] ?? ''));
    $typeName = trim((string) ($asset->type_name ?? $assetData['asset_type_text'] ?? $assetData['asset_type']['title'] ?? ''));
    $serialNumber = trim((string) ($asset->serial_number ?? $assetData['serial_number'] ?? ''));
    $qrPath = trim((string) ($asset->qr_code_path ?? ''));
    // ส่งซ่อม: ใช้ wizard mobile-first ใหม่ (mirror flow ของ /me/repair-v2/create)
    // wizard รับ asset_number + send_type=asset → prefill รหัสครุภัณฑ์ + auto-pick แผนกช่าง
    $repairUrl = Url::to(['/mobile/default/repair-request', 'asset_number' => $assetCode, 'send_type' => 'asset']);

    if (!empty($asset->receive_date)) {
        try {
            $receiveDate = ThaiDateHelper::formatThaiDate($asset->receive_date, 'medium');
        } catch (\Throwable $e) {
            try {
                $receiveDate = \Yii::$app->formatter->asDate($asset->receive_date);
            } catch (\Throwable $e2) {
                $receiveDate = (string) $asset->receive_date;
            }
        }
    }

    try {
        $showImg = $asset->ShowImg();
        if (!empty($showImg['image']) && !empty($showImg['isFile'])) {
            $photoUrl = (string) $showImg['image'];
        }
    } catch (\Throwable $e) {
        $photoUrl = null;
    }

    $pushDetail = static function (string $icon, string $label, ?string $value) use (&$detailRows): void {
        $value = trim((string) $value);
        $detailRows[] = [
            'icon' => $icon,
            'label' => $label,
            'value' => $value !== '' ? $value : '-',
            'isEmpty' => $value === '',
        ];
    };
    $pushDetail('hash', 'รหัสครุภัณฑ์', $assetCode);
    $pushDetail('building-2', 'หน่วยงาน', $department);
    $pushDetail('map-pin', 'สถานที่ตั้ง', $location);
    $pushDetail('calendar-check', 'วันที่รับเข้า', $receiveDate);
    if ($typeName !== '') {
        $pushDetail('layers-3', 'ประเภท', $typeName);
    }
    if ($serialNumber !== '') {
        $pushDetail('barcode', 'หมายเลขเครื่อง', $serialNumber);
    }

    // ── ดึงประวัติทรัพย์สินจากข้อมูลจริง 6 หมวด ──
    $historyService = new MobileAssetHistoryService();
    $historyData    = $historyService->gather($asset, 5);

    $assetHistoryGroups = [
        ['key' => 'maintenance', 'icon' => 'wrench',          'title' => 'ประวัติซ่อมบำรุง', 'tone' => 'success'],
        ['key' => 'pm',          'icon' => 'clipboard-check', 'title' => 'ประวัติการ PM',    'tone' => 'success'],
        ['key' => 'calibration', 'icon' => 'ruler',           'title' => 'ประวัติ CAL',      'tone' => 'pending'],
        ['key' => 'borrow',      'icon' => 'repeat',          'title' => 'ประวัติการยืมคืน', 'tone' => 'success'],
        ['key' => 'move',        'icon' => 'arrow-left-right','title' => 'ประวัติการเคลื่อนย้าย', 'tone' => 'document'],
        ['key' => 'document',    'icon' => 'paperclip',       'title' => 'เอกสารแนบ',         'tone' => 'document'],
    ];
    foreach ($assetHistoryGroups as &$g) {
        $g['data'] = $historyData[$g['key']] ?? ['items' => [], 'total' => 0];
    }
    unset($g);
}

// ── Hero overlay (slot ของ _hero_shell): ใส่ summary-card content ทับโค้ง hero ──
// asset mode: medal + ชื่อ + รหัส + status pill + QR
// empty mode: status panel ("พร้อมสแกน" / "ไม่พบครุภัณฑ์") พร้อม hint
$statusIcon = $status === Asset::LIFECYCLE_REPAIR
    ? 'wrench'
    : ($status === Asset::LIFECYCLE_DISPOSED ? 'archive-x' : 'circle-check');
ob_start(); ?>
<div class="asset-hero-overlay-shell">
    <?php if ($asset): ?>
        <div class="asset-summary-head">
            <span class="asset-medal" aria-hidden="true">
                <i data-lucide="package-check"></i>
            </span>
            <div class="min-w-0 flex-grow-1">
                <h2 class="asset-title" id="asset-summary-title">
                    <?= Html::encode($assetName !== '' ? $assetName : 'ครุภัณฑ์') ?>
                </h2>
                <p class="asset-code"><?= Html::encode($assetCode !== '' ? $assetCode : '-') ?></p>
                <span class="asset-status-pill" data-tone="<?= Html::encode($statusTone) ?>">
                    <i data-lucide="<?= Html::encode($statusIcon) ?>" aria-hidden="true"></i>
                    <?= Html::encode($statusLabel) ?>
                </span>
            </div>
            <?php if ($qrPath !== ''): ?>
                <span class="asset-qr" aria-label="QR Code ของครุภัณฑ์">
                    <?= Html::img($qrPath, ['alt' => 'QR Code ' . $assetCode]) ?>
                </span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="asset-summary-head">
            <span class="asset-medal" aria-hidden="true">
                <i data-lucide="<?= $hasSearchCode ? 'package-x' : 'qr-code' ?>"></i>
            </span>
            <div class="min-w-0 flex-grow-1">
                <h2 class="asset-title"><?= Html::encode($hasSearchCode ? 'ไม่พบครุภัณฑ์' : 'พร้อมสแกน QR') ?></h2>
                <p class="asset-code">
                    <?= Html::encode($hasSearchCode ? ('ค้นหา: ' . $clip($searchedCode, 24)) : 'รหัสจะปรากฏหลังสแกน') ?>
                </p>
                <span class="asset-status-pill" data-tone="<?= $hasSearchCode ? 'danger' : 'primary' ?>">
                    <i data-lucide="<?= $hasSearchCode ? 'search-x' : 'scan-line' ?>" aria-hidden="true"></i>
                    <?= $hasSearchCode ? 'ลองสแกนอีกครั้ง' : 'รอข้อมูล' ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $overlayHtml = ob_get_clean(); ?>


<style>
.asset-root {
    margin: -1rem -1rem 0;
}
.asset-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 7rem);
}
.asset-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    padding: var(--space-md);
}
.asset-back,
.asset-actions .btn,
.asset-empty-actions .btn {
    min-height: 2.75rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    font-weight: 800;
}
.asset-back {
    width: fit-content;
    box-shadow: 0 1px 0 var(--ink-line);
}
.asset-back svg,
.asset-actions svg,
.asset-empty-actions svg {
    width: 1.125rem;
    height: 1.125rem;
}
.asset-photo-card,
.asset-summary-card,
.asset-history-card,
.asset-detail-card,
.asset-empty-card {
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.asset-photo-shell {
    position: relative;
    min-height: 14rem;
    aspect-ratio: 4 / 3;
    max-height: 22rem;
    background: var(--surface-2);
    overflow: hidden;
}
.asset-photo-link,
.asset-photo-placeholder {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
}
.asset-photo-link {
    color: inherit;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
}
.asset-photo {
    position: relative;
    z-index: 1;
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    background: var(--surface);
}
.asset-photo-skeleton {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(90deg,
            color-mix(in oklch, var(--surface-3) 84%, white) 0%,
            color-mix(in oklch, var(--surface) 92%, var(--mobile-primary-soft)) 45%,
            color-mix(in oklch, var(--surface-3) 84%, white) 100%);
    background-size: 220% 100%;
    opacity: .75;
    transition: opacity 180ms cubic-bezier(0.16, 1, 0.3, 1);
    animation: asset-photo-shimmer 1200ms ease-in-out infinite;
}
.asset-photo-shell.is-loaded .asset-photo-skeleton {
    opacity: 0;
}
.asset-photo-hint {
    position: absolute;
    right: var(--space-sm);
    bottom: var(--space-sm);
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    padding: .4rem .65rem;
    border-radius: 999px;
    background: color-mix(in oklch, var(--ink) 76%, transparent);
    color: #fff;
    font-size: var(--fs-xs);
    font-weight: 800;
    box-shadow: 0 8px 18px color-mix(in oklch, var(--ink) 18%, transparent);
    pointer-events: none;
}
.asset-photo-hint svg {
    width: .9rem;
    height: .9rem;
}
.asset-photo-placeholder {
    flex-direction: column;
    gap: var(--space-sm);
    padding: var(--space-xl) var(--space-md);
    color: var(--ink-3);
    text-align: center;
}
.asset-placeholder-icon,
.asset-empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: var(--cat-asset-bg);
    color: var(--cat-asset-fg);
}
.asset-placeholder-icon {
    width: 4rem;
    height: 4rem;
}
.asset-placeholder-icon svg {
    width: 2rem;
    height: 2rem;
}
.asset-summary-card {
    padding: var(--space-md);
}
/* Hero overlay (slot ของ _hero_shell.overlayHtml) — ลอยทับโค้ง hero ใน fixed shell
   pattern เดียวกับ .app-stats / .lv-hero-overlay-shell */
.asset-hero-overlay-shell {
    position: relative; z-index: 2;
    margin: -1.75rem var(--space-md) 0;
    background: #fff;
    border-radius: 16px;
    box-shadow:
        0 12px 28px rgba(13, 110, 253, 0.14),
        0 2px 6px rgba(15, 23, 42, 0.05);
    padding: var(--space-md);
    animation: asset-hero-overlay-in 360ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes asset-hero-overlay-in {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.asset-hero-overlay-shell .asset-title {
    font-size: clamp(var(--fs-md), 4.4vw, var(--fs-lg));
    text-wrap: balance;
}
.asset-hero-overlay-shell .asset-medal {
    box-shadow: 0 4px 10px color-mix(in oklch, var(--mobile-primary) 22%, transparent);
    transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
}
.asset-hero-overlay-shell:hover .asset-medal { transform: scale(1.04) rotate(-2deg); }
.asset-hero-overlay-shell .asset-qr {
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}
@media (prefers-reduced-motion: reduce) {
    .asset-hero-overlay-shell { animation: none !important; transform: none !important; }
    .asset-hero-overlay-shell .asset-medal { transition: none !important; }
    .asset-hero-overlay-shell:hover .asset-medal { transform: none !important; }
}
.asset-summary-head {
    display: flex;
    align-items: flex-start;
    gap: var(--space-sm);
}
.asset-medal {
    width: 3rem;
    height: 3rem;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: var(--cat-asset-bg);
    color: var(--cat-asset-fg);
}
.asset-medal svg {
    width: 1.4rem;
    height: 1.4rem;
}
.asset-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 800;
    line-height: 1.3;
    text-wrap: pretty;
}
.asset-code {
    margin: .2rem 0 0;
    color: var(--ink-4);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-xs);
    font-weight: 700;
    overflow-wrap: anywhere;
}
.asset-status-pill {
    width: fit-content;
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    margin-top: var(--space-sm);
    border-radius: 999px;
    padding: .4rem .7rem;
    font-size: var(--fs-xs);
    font-weight: 800;
}
.asset-status-pill[data-tone="primary"] { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.asset-status-pill[data-tone="success"] { background: var(--success-soft); color: var(--success); }
.asset-status-pill[data-tone="warning"] { background: var(--warning-soft); color: var(--warning); }
.asset-status-pill[data-tone="danger"] { background: var(--danger-soft); color: var(--danger-strong); }
.asset-status-pill svg {
    width: .95rem;
    height: .95rem;
}
.asset-qr {
    width: 4.5rem;
    min-width: 4.5rem;
    height: 4.5rem;
    margin-left: auto;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--ink-line);
    background: var(--surface);
    overflow: hidden;
}
.asset-qr img {
    width: 4rem;
    height: 4rem;
    object-fit: contain;
    display: block;
}
.asset-detail-head {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    padding: var(--space-md);
    border-bottom: 1px solid var(--ink-line);
}
.asset-detail-head svg {
    width: 1.125rem;
    height: 1.125rem;
    color: var(--mobile-primary);
}
.asset-detail-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.25;
}
.asset-history-card {
    padding: var(--space-md);
}
.asset-history-head {
    display: flex;
    align-items: flex-start;
    gap: var(--space-sm);
    margin-bottom: var(--space-sm);
}
.asset-history-head-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}
.asset-history-head-icon svg {
    width: 1.15rem;
    height: 1.15rem;
}
.asset-history-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.25;
}
.asset-history-subtitle {
    margin: 2px 0 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.4;
}
.asset-activity-list {
    position: relative;
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.asset-activity-list::before {
    content: "";
    position: absolute;
    left: 1.35rem;
    top: .7rem;
    bottom: .7rem;
    width: 2px;
    border-radius: 999px;
    background: color-mix(in oklch, var(--mobile-primary) 16%, var(--ink-line));
}
.asset-activity-item {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    align-items: start;
    gap: var(--space-sm);
    padding: var(--space-sm);
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    background: color-mix(in oklch, var(--surface) 94%, var(--surface-2));
    box-shadow: 0 1px 0 color-mix(in oklch, var(--ink) 4%, transparent);
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.asset-activity-medal {
    width: 2.7rem;
    height: 2.7rem;
    border-radius: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    box-shadow: 0 0 0 6px var(--surface);
}
.asset-activity-medal svg {
    width: 1.1rem;
    height: 1.1rem;
}
.asset-activity-content {
    min-width: 0;
}
.asset-activity-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-xs);
}
.asset-activity-title {
    display: block;
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 800;
    line-height: 1.25;
}
.asset-activity-desc {
    display: block;
    margin-top: .35rem;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 600;
    line-height: 1.45;
}
.asset-activity-time {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    margin-top: .45rem;
    color: var(--ink-4);
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.3;
}
.asset-activity-time svg {
    width: .85rem;
    height: .85rem;
    flex: 0 0 auto;
}
.asset-activity-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    min-height: 1.65rem;
    padding: .25rem .55rem;
    border-radius: 999px;
    font-size: var(--fs-2xs);
    font-weight: 900;
    line-height: 1.1;
}
.asset-activity-item[data-tone="success"] .asset-activity-medal,
.asset-activity-status[data-tone="success"] {
    background: var(--success-soft);
    color: var(--success);
}
.asset-activity-item[data-tone="pending"] .asset-activity-medal,
.asset-activity-status[data-tone="pending"] {
    background: var(--warning-soft);
    color: var(--warning);
}
.asset-activity-item[data-tone="returned"] .asset-activity-medal,
.asset-activity-status[data-tone="returned"] {
    background: var(--danger-soft);
    color: var(--danger-strong);
}
.asset-activity-item[data-tone="document"] .asset-activity-medal,
.asset-activity-status[data-tone="document"] {
    background: var(--cat-document-bg);
    color: var(--cat-document-fg);
}
.asset-detail-list {
    display: flex;
    flex-direction: column;
}
.asset-detail-row {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    gap: var(--space-sm);
    padding: var(--space-md);
    border-bottom: 1px solid var(--ink-line);
}
.asset-detail-row:last-child {
    border-bottom: 0;
}
.asset-detail-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--surface-2);
    color: var(--ink-3);
}
.asset-detail-icon svg {
    width: 1.05rem;
    height: 1.05rem;
}
.asset-label {
    color: var(--ink-4);
    font-size: var(--fs-xs);
    font-weight: 800;
    line-height: 1.3;
}
.asset-value {
    margin-top: 2px;
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 800;
    line-height: 1.45;
    overflow-wrap: anywhere;
}
.asset-value.is-muted {
    color: var(--ink-4);
}

/* ── ประวัติทรัพย์สิน: 6 หมวด collapsible ── */
.asset-history-groups { display: flex; flex-direction: column; gap: var(--space-xs); }
.asset-history-group {
    border: 1px solid var(--ink-line);
    border-radius: 14px;
    background: var(--surface);
    overflow: hidden;
    opacity: 0; transform: translateY(4px);
    animation: asset-grp-in 320ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
    animation-delay: calc(var(--asset-grp-i, 0) * 40ms + 80ms);
}
@keyframes asset-grp-in { to { opacity: 1; transform: translateY(0); } }
.asset-history-group-head {
    display: flex; align-items: center; gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    cursor: pointer;
    list-style: none;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: background 180ms cubic-bezier(0.22, 1, 0.36, 1);
}
.asset-history-group-head::-webkit-details-marker { display: none; }
.asset-history-group-head:hover { background: var(--surface-2); }
.asset-history-group-icon {
    width: 2.25rem; height: 2.25rem; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    flex: 0 0 auto;
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
}
.asset-history-group-head[data-tone="success"]  .asset-history-group-icon { background: var(--success-soft); color: var(--success); }
.asset-history-group-head[data-tone="pending"]  .asset-history-group-icon { background: var(--warning-soft); color: var(--warning); }
.asset-history-group-head[data-tone="document"] .asset-history-group-icon { background: color-mix(in oklch, var(--ink-4) 14%, transparent); color: var(--ink-3); }
.asset-history-group-icon svg { width: 1.05rem; height: 1.05rem; }
.asset-history-group-meta { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.asset-history-group-title { font-size: var(--fs-sm); font-weight: 800; color: var(--ink); line-height: 1.25; }
.asset-history-group-count { font-size: var(--fs-xs); color: var(--ink-3); }
.asset-history-chevron {
    width: 1rem; height: 1rem; color: var(--ink-4);
    flex-shrink: 0;
    transition: transform 240ms cubic-bezier(0.22, 1, 0.36, 1);
}
.asset-history-group[open] .asset-history-chevron { transform: rotate(180deg); }

.asset-history-items {
    list-style: none; margin: 0; padding: 0 var(--space-md) var(--space-sm);
    display: flex; flex-direction: column; gap: var(--space-2xs);
    border-top: 1px solid var(--ink-line);
}
.asset-history-item {
    padding: var(--space-xs) 0;
    border-bottom: 1px dashed color-mix(in oklch, var(--ink-line) 70%, transparent);
    opacity: 0; transform: translateY(3px);
    animation: asset-item-in 280ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
    animation-delay: calc(var(--item-i, 0) * 30ms);
}
.asset-history-item:last-child { border-bottom: 0; }
@keyframes asset-item-in { to { opacity: 1; transform: translateY(0); } }
.asset-history-item-row { display: flex; align-items: baseline; justify-content: space-between; gap: var(--space-xs); }
.asset-history-item-title {
    font-size: var(--fs-sm); font-weight: 700; color: var(--ink);
    line-height: 1.35; word-break: break-word; flex: 1; min-width: 0;
}
.asset-history-item-meta {
    flex-shrink: 0; font-size: var(--fs-2xs); color: var(--ink-3);
    background: var(--surface-2); padding: 2px 8px; border-radius: 999px;
    font-weight: 700; letter-spacing: 0.02em;
}
.asset-history-item-desc {
    margin: 4px 0 0; font-size: var(--fs-xs); color: var(--ink-3); line-height: 1.5;
}
.asset-history-item-time {
    display: inline-flex; align-items: center; gap: 4px;
    margin-top: 4px;
    font-size: var(--fs-2xs); color: var(--ink-4);
}
.asset-history-item-time svg { width: 10px; height: 10px; }
.asset-history-empty {
    margin: 0; padding: var(--space-sm) var(--space-md);
    font-size: var(--fs-xs); color: var(--ink-4); text-align: center;
    border-top: 1px solid var(--ink-line);
}
.asset-history-more {
    margin: var(--space-xs) var(--space-md);
    padding: var(--space-2xs) var(--space-xs);
    font-size: var(--fs-2xs); color: var(--mobile-primary);
    background: var(--mobile-primary-soft);
    border-radius: 8px; text-align: center;
}

@media (prefers-reduced-motion: reduce) {
    .asset-history-group, .asset-history-item { animation: none !important; opacity: 1 !important; transform: none !important; }
    .asset-history-chevron, .asset-history-group-head { transition: none !important; }
}

.asset-actions {
    position: sticky;
    bottom: 0;
    z-index: calc(var(--z-sticky) - 1);
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: var(--space-xs) var(--space-sm);
    margin: var(--space-sm) calc(var(--space-md) * -1) calc(var(--space-md) * -1);
    padding: var(--space-sm) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + var(--space-sm));
    background: color-mix(in oklch, var(--surface) 94%, transparent);
    backdrop-filter: blur(12px);
    box-shadow: 0 -1px 0 var(--ink-line), 0 -8px 22px color-mix(in oklch, var(--ink) 6%, transparent);
}
.asset-actions-hint {
    grid-column: 1 / -1;
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    margin: 0;
    padding: .35rem .55rem;
    border-radius: 10px;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    font-size: var(--fs-2xs);
    font-weight: 700;
    line-height: 1.4;
    overflow-wrap: anywhere;
}
.asset-actions-hint svg {
    width: .9rem; height: .9rem;
    flex-shrink: 0;
}
.asset-actions-code {
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-weight: 800;
    color: var(--mobile-primary-dark, var(--mobile-primary));
    background: rgba(255,255,255,0.55);
    padding: 1px 6px;
    border-radius: 6px;
    letter-spacing: 0.01em;
}
.asset-repair-btn {
    position: relative;
    overflow: hidden;
    padding-right: 2.5rem !important;
}
.asset-repair-arrow {
    position: absolute;
    right: var(--space-sm);
    top: 50%;
    transform: translateY(-50%);
    width: 1.05rem !important; height: 1.05rem !important;
    transition: transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
    opacity: 0.85;
}
.asset-repair-btn:hover .asset-repair-arrow,
.asset-repair-btn:focus-visible .asset-repair-arrow {
    transform: translate(3px, -50%);
    opacity: 1;
}
.asset-repair-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg,
        transparent 30%,
        rgba(255,255,255,0.18) 50%,
        transparent 70%);
    background-size: 200% 100%;
    background-position: -100% 0;
    pointer-events: none;
    transition: background-position 600ms cubic-bezier(0.22, 1, 0.36, 1);
}
.asset-repair-btn:hover::before { background-position: 100% 0; }
@media (prefers-reduced-motion: reduce) {
    .asset-repair-arrow { transition: none !important; }
    .asset-repair-btn:hover .asset-repair-arrow { transform: translateY(-50%) !important; }
    .asset-repair-btn::before { transition: none !important; }
}
.asset-empty-card {
    padding: var(--space-2xl) var(--space-md);
    text-align: center;
}
.asset-empty-icon {
    width: 4.25rem;
    height: 4.25rem;
    margin-bottom: var(--space-md);
}
.asset-empty-icon svg {
    width: 2.1rem;
    height: 2.1rem;
}
.asset-empty-title {
    margin: 0 0 var(--space-2xs);
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 800;
}
.asset-empty-text {
    margin: 0 auto;
    max-width: 34ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.55;
}
.asset-empty-code {
    display: inline-flex;
    max-width: 100%;
    margin-top: var(--space-sm);
    padding: .45rem .65rem;
    border-radius: 10px;
    background: var(--surface-2);
    color: var(--ink-2);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-xs);
    font-weight: 800;
    overflow-wrap: anywhere;
}
.asset-empty-actions {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-sm);
    margin-top: var(--space-lg);
}
.asset-photo-modal .modal-dialog {
    max-width: min(96vw, 720px);
    margin: var(--space-md) auto;
}
.asset-photo-modal .modal-content {
    border: 0;
    border-radius: 16px;
    overflow: hidden;
}
.asset-photo-modal .modal-header {
    border-bottom: 1px solid var(--ink-line);
}
.asset-photo-modal .modal-title {
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 800;
    line-height: 1.35;
}
.asset-photo-modal img {
    width: 100%;
    height: auto;
    display: block;
    background: var(--surface);
}
@media (hover: hover) {
    .asset-back,
    .asset-actions .btn,
    .asset-empty-actions .btn,
    .asset-photo-link {
        transition:
            transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
            box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1),
            border-color 160ms cubic-bezier(0.16, 1, 0.3, 1),
            background 160ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    .asset-back:hover,
    .asset-actions .btn:hover,
    .asset-empty-actions .btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .asset-photo-link:hover {
        transform: scale(1.006);
    }
}
@media (max-width: 360px) {
    .asset-actions {
        grid-template-columns: 1fr;
    }
    .asset-summary-head {
        align-items: flex-start;
    }
    .asset-qr {
        width: 4rem;
        min-width: 4rem;
        height: 4rem;
    }
    .asset-qr img {
        width: 3.5rem;
        height: 3.5rem;
    }
}
@media (min-width: 768px) {
    .asset-body {
        width: min(100%, 760px);
        margin: 0 auto;
    }
    .asset-photo-shell {
        aspect-ratio: 16 / 9;
        max-height: 26rem;
    }
    .asset-activity-item {
        padding: var(--space-md);
    }
}
@media (prefers-reduced-motion: reduce) {
    .asset-back,
    .asset-photo-link,
    .asset-photo-skeleton,
    .asset-photo-card,
    .asset-summary-card,
    .asset-history-card,
    .asset-detail-card,
    .asset-empty-card,
    .asset-actions .btn,
    .asset-empty-actions .btn,
    .asset-activity-item {
        animation: none !important;
        transition: none !important;
    }
    .asset-back:hover,
    .asset-actions .btn:hover,
    .asset-empty-actions .btn:hover,
    .asset-photo-link:hover {
        transform: none !important;
    }
}
@media (prefers-reduced-motion: no-preference) {
    .asset-back,
    .asset-photo-card,
    .asset-summary-card,
    .asset-history-card,
    .asset-detail-card,
    .asset-empty-card,
    .asset-actions {
        animation: asset-item-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--asset-i, 0) * 35ms);
    }
    .asset-activity-item {
        animation: asset-activity-in 260ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(120ms + var(--activity-i, 0) * 45ms);
    }
}
@keyframes asset-photo-shimmer {
    from { background-position: 220% 0; }
    to { background-position: -220% 0; }
}
@keyframes asset-item-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes asset-activity-in {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="asset-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'        => $asset ? 'package-check' : 'package-search',
        'title'       => $this->params['mobileTitle'],
        'subtitle'    => $asset ? ($assetName !== '' ? $clip($assetName, 56) : $this->params['mobileSubtitle']) : $this->params['mobileSubtitle'],
        'overlayHtml' => $overlayHtml,
    ]) ?>

    <div class="app-scroll has-overlay asset-scroll">
        <div class="asset-body">
            <a href="<?= Html::encode($servicesUrl) ?>" class="btn btn-outline-secondary asset-back" style="--asset-i: 0">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปบริการ</span>
            </a>

            <?php if (!$asset): ?>
                <section class="asset-empty-card" style="--asset-i: 1" role="status">
                    <span class="asset-empty-icon" aria-hidden="true">
                        <i data-lucide="<?= $hasSearchCode ? 'package-x' : 'scan-line' ?>"></i>
                    </span>
                    <h2 class="asset-empty-title">
                        <?= $hasSearchCode ? 'ไม่พบครุภัณฑ์' : 'สแกน QR เพื่อดูข้อมูลครุภัณฑ์' ?>
                    </h2>
                    <p class="asset-empty-text">
                        <?= $hasSearchCode
                            ? 'รหัสนี้ไม่มีในระบบ หรือรายการอาจถูกยกเลิกแล้ว'
                            : 'ใช้กล้องโทรศัพท์สแกน QR Code บนครุภัณฑ์เพื่อเปิดรายละเอียดและแจ้งซ่อมได้ทันที' ?>
                    </p>
                    <?php if ($hasSearchCode): ?>
                        <span class="asset-empty-code"><?= Html::encode($searchedCode) ?></span>
                    <?php endif; ?>
                    <div class="asset-empty-actions">
                        <a href="<?= Html::encode($scanUrl) ?>" class="btn btn-primary">
                            <i data-lucide="scan" aria-hidden="true"></i>
                            <span><?= $hasSearchCode ? 'สแกนใหม่' : 'เปิดสแกน QR' ?></span>
                        </a>
                    </div>
                </section>
            <?php else: ?>
                <section class="asset-photo-card" style="--asset-i: 1" aria-label="รูปครุภัณฑ์">
                    <div class="asset-photo-shell" data-asset-photo-shell>
                        <?php if ($photoUrl): ?>
                            <a href="#asset-photo-modal"
                               class="asset-photo-link"
                               data-bs-toggle="modal"
                               data-bs-target="#asset-photo-modal"
                               aria-label="เปิดรูปครุภัณฑ์ขนาดใหญ่">
                                <span class="asset-photo-skeleton" aria-hidden="true"></span>
                                <?= Html::img($photoUrl, [
                                    'alt' => $assetName,
                                    'class' => 'asset-photo',
                                    'loading' => 'lazy',
                                    'decoding' => 'async',
                                    'data' => ['asset-photo' => true],
                                ]) ?>
                                <span class="asset-photo-hint" aria-hidden="true">
                                    <i data-lucide="expand"></i>
                                    แตะเพื่อขยาย
                                </span>
                            </a>
                        <?php else: ?>
                            <div class="asset-photo-placeholder">
                                <span class="asset-placeholder-icon" aria-hidden="true">
                                    <i data-lucide="image-off"></i>
                                </span>
                                <span>ยังไม่มีรูปภาพครุภัณฑ์</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if (!empty($assetHistoryGroups)): ?>
                    <section class="asset-history-card" style="--asset-i: 2" aria-labelledby="asset-history-title">
                        <header class="asset-history-head">
                            <span class="asset-history-head-icon" aria-hidden="true">
                                <i data-lucide="history"></i>
                            </span>
                            <span class="min-w-0">
                                <h2 class="asset-history-title" id="asset-history-title">ประวัติทรัพย์สิน</h2>
                                <span class="asset-history-subtitle">รวมเหตุการณ์ทุกประเภทของทรัพย์สินจากข้อมูลจริงในระบบ</span>
                            </span>
                        </header>
                        <div class="asset-history-groups">
                            <?php foreach ($assetHistoryGroups as $gIdx => $group):
                                $gData  = $group['data'];
                                $items  = $gData['items'] ?? [];
                                $total  = (int) ($gData['total'] ?? 0);
                                $isEmpty = empty($items);
                            ?>
                                <details class="asset-history-group" <?= $total > 0 && $gIdx === 0 ? 'open' : '' ?>
                                         style="--asset-grp-i: <?= (int) $gIdx ?>">
                                    <summary class="asset-history-group-head" data-tone="<?= Html::encode($group['tone']) ?>">
                                        <span class="asset-history-group-icon" aria-hidden="true">
                                            <i data-lucide="<?= Html::encode($group['icon']) ?>"></i>
                                        </span>
                                        <span class="asset-history-group-meta">
                                            <span class="asset-history-group-title"><?= Html::encode($group['title']) ?></span>
                                            <span class="asset-history-group-count">
                                                <?= $isEmpty ? 'ยังไม่มีรายการ' : ($total . ' รายการ') ?>
                                            </span>
                                        </span>
                                        <i data-lucide="chevron-down" class="asset-history-chevron" aria-hidden="true"></i>
                                    </summary>
                                    <?php if (!$isEmpty): ?>
                                        <ol class="asset-history-items" aria-label="<?= Html::encode($group['title']) ?>">
                                            <?php foreach ($items as $iIdx => $it): ?>
                                                <li class="asset-history-item" style="--item-i: <?= (int) $iIdx ?>">
                                                    <div class="asset-history-item-row">
                                                        <span class="asset-history-item-title"><?= Html::encode($it['title']) ?></span>
                                                        <?php if (!empty($it['meta'])): ?>
                                                            <span class="asset-history-item-meta"><?= Html::encode($it['meta']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($it['desc'])): ?>
                                                        <p class="asset-history-item-desc"><?= Html::encode($it['desc']) ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($it['datetime'])): ?>
                                                        <time class="asset-history-item-time">
                                                            <i data-lucide="clock-3" aria-hidden="true"></i>
                                                            <?= Html::encode($it['datetime']) ?>
                                                        </time>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                        <?php if ($total > count($items)): ?>
                                            <p class="asset-history-more">ดู <?= (int) $total ?> รายการบนเดสก์ท็อปเพื่อข้อมูลเพิ่มเติม</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="asset-history-empty">ยังไม่มีบันทึกในหมวดนี้</p>
                                    <?php endif; ?>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="asset-detail-card" style="--asset-i: <?= !empty($assetHistoryGroups) ? 3 : 2 ?>" aria-labelledby="asset-detail-title">
                    <header class="asset-detail-head">
                        <i data-lucide="list-checks" aria-hidden="true"></i>
                        <h2 class="asset-detail-title" id="asset-detail-title">รายละเอียดครุภัณฑ์</h2>
                    </header>
                    <div class="asset-detail-list">
                        <?php foreach ($detailRows as $row): ?>
                            <div class="asset-detail-row">
                                <span class="asset-detail-icon" aria-hidden="true">
                                    <i data-lucide="<?= Html::encode($row['icon']) ?>"></i>
                                </span>
                                <span class="min-w-0">
                                    <span class="asset-label"><?= Html::encode($row['label']) ?></span>
                                    <span class="asset-value<?= $row['isEmpty'] ? ' is-muted' : '' ?>"><?= Html::encode($row['value']) ?></span>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <div class="asset-actions" style="--asset-i: <?= !empty($assetHistoryGroups) ? 4 : 3 ?>">
                    <?php if ($assetCode !== ''): ?>
                        <p class="asset-actions-hint" aria-hidden="true">
                            <i data-lucide="link-2"></i>
                            ระบบจะนำรหัส <span class="asset-actions-code"><?= Html::encode($assetCode) ?></span> ไปกรอกในฟอร์มแจ้งซ่อมให้อัตโนมัติ
                        </p>
                    <?php endif; ?>
                    <a href="<?= Html::encode($repairUrl) ?>" class="btn btn-primary asset-repair-btn">
                        <i data-lucide="wrench" aria-hidden="true"></i>
                        <span>แจ้งซ่อมครุภัณฑ์นี้</span>
                        <i data-lucide="arrow-right" class="asset-repair-arrow" aria-hidden="true"></i>
                    </a>
                    <a href="<?= Html::encode($scanUrl) ?>" class="btn btn-outline-secondary">
                        <i data-lucide="scan" aria-hidden="true"></i>
                        <span>สแกนอื่น</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($asset && $photoUrl): ?>
        <div class="modal fade asset-photo-modal" id="asset-photo-modal" tabindex="-1" aria-labelledby="asset-photo-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="asset-photo-modal-label"><?= Html::encode($assetName) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body p-0">
                        <img src="<?= Html::encode($photoUrl) ?>" alt="<?= Html::encode($assetName) ?>">
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var img = document.querySelector('[data-asset-photo]');
            var shell = document.querySelector('[data-asset-photo-shell]');
            if (!img || !shell) {
                return;
            }
            function markLoaded() {
                shell.classList.add('is-loaded');
            }
            if (img.complete && img.naturalWidth > 0) {
                markLoaded();
            } else {
                img.addEventListener('load', markLoaded, { once: true });
                img.addEventListener('error', markLoaded, { once: true });
            }
        })();
        </script>
    <?php endif; ?>
</div>
