<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\helpdesk2\models\Helpdesk $model */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ส่งคำขอซ่อมเรียบร้อย';
$this->params['mobileSubtitle'] = 'ทีมช่างได้รับการแจ้งเตือนแล้ว';

$dataJson = is_array($model->data_json ?? null) ? $model->data_json : [];
$repairNo = (string) ($model->repair_number ?? '');
$assetNo  = (string) ($model->asset_number ?? '');
$assetName = (string) ($dataJson['asset_name'] ?? '');
$location = (string) ($dataJson['location'] ?? '');
$urgencyTxt = '';
try { $urgencyTxt = (string) ($model->viewUrgent()['title'] ?? ''); } catch (\Throwable $e) {}
$deviceTxt = '';
try { $deviceTxt = (string) ($model->deviceType->title ?? ''); } catch (\Throwable $e) {}

$createdAtText = '-';
if (!empty($model->created_at)) {
    try {
        $createdAtText = ThaiDateHelper::formatThaiDate((string) $model->created_at, 'long')
            . ' ' . date('H:i', strtotime((string) $model->created_at)) . ' น.';
    } catch (\Throwable $e) { $createdAtText = (string) $model->created_at; }
}

$assetUrl   = $assetNo !== '' ? Url::to(['/mobile/default/asset', 'code' => $assetNo]) : '';
$historyUrl = Url::to(['/mobile/default/maintenance-request']);
$homeUrl    = Url::to(['/mobile/default/services']);

// Hero overlay (success state)
ob_start(); ?>
<div class="rs-hero-overlay-shell">
    <div class="rs-hero">
        <span class="rs-medal" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
        </span>
        <div class="min-w-0 flex-grow-1">
            <h2 class="rs-title">ส่งคำขอซ่อมเรียบร้อย</h2>
            <p class="rs-sub">ทีมช่างได้รับการแจ้งเตือนแล้ว ระบบจะติดตามสถานะให้</p>
            <span class="rs-status-pill" data-tone="warning">
                <i data-lucide="hourglass" aria-hidden="true"></i>
                รอดำเนินการ
            </span>
        </div>
    </div>
</div>
<?php $overlayHtml = ob_get_clean(); ?>

<style>
.rs-root { margin: -1rem -1rem 0; display: flex; flex-direction: column; }
.rs-scroll { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 8rem); }

.rs-hero-overlay-shell {
    position: relative; z-index: 2;
    margin: -1.75rem var(--space-md) 0;
    background: #fff;
    border-radius: 16px;
    box-shadow:
        0 12px 28px rgba(34, 197, 94, 0.18),
        0 2px 6px rgba(15, 23, 42, 0.05);
    padding: var(--space-md);
    animation: rs-overlay-in 360ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes rs-overlay-in {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.rs-hero { display: flex; align-items: flex-start; gap: var(--space-sm); }
.rs-medal {
    width: 3rem; height: 3rem; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    background: var(--success-soft); color: var(--success);
    animation: rs-check-pop 520ms cubic-bezier(0.22, 1, 0.36, 1);
}
.rs-medal svg { width: 1.5rem; height: 1.5rem; }
@keyframes rs-check-pop {
    0%   { transform: scale(0); opacity: 0; }
    55%  { transform: scale(1.12); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
.rs-medal svg path {
    stroke-dasharray: 22;
    stroke-dashoffset: 22;
    animation: rs-check-draw 480ms cubic-bezier(0.22, 1, 0.36, 1) 200ms forwards;
}
@keyframes rs-check-draw {
    to { stroke-dashoffset: 0; }
}
.rs-title { margin: 0; font-size: var(--fs-lg); font-weight: 800; color: var(--ink); line-height: 1.25; text-wrap: balance; }
.rs-sub   { margin: 4px 0 0; font-size: var(--fs-sm); color: var(--ink-3); line-height: 1.45; }
.rs-status-pill {
    display: inline-flex; align-items: center; gap: 4px;
    margin-top: var(--space-sm);
    border-radius: 999px; padding: 4px 12px;
    font-size: var(--fs-2xs); font-weight: 700; line-height: 1.4;
}
.rs-status-pill[data-tone="warning"] { background: var(--warning-soft); color: var(--warning); }
.rs-status-pill svg { width: 12px; height: 12px; }

/* ── Body ── */
.rs-body { padding: var(--space-md); display: flex; flex-direction: column; gap: var(--space-md); }
.rs-card {
    background: var(--surface);
    border-radius: 16px;
    padding: var(--space-md);
    box-shadow: var(--shadow-sm);
}
.rs-card-head {
    display: flex; align-items: center; gap: var(--space-xs);
    padding-bottom: var(--space-xs); margin-bottom: var(--space-sm);
    border-bottom: 1px solid var(--ink-line);
}
.rs-card-title {
    font-size: var(--fs-md); font-weight: 700; color: var(--ink);
    margin: 0; display: inline-flex; align-items: center; gap: var(--space-xs);
}
.rs-card-title svg { width: 1.125rem; height: 1.125rem; color: var(--mobile-primary); }

.rs-number {
    text-align: center;
    padding: var(--space-md) 0;
}
.rs-number-label { font-size: var(--fs-xs); color: var(--ink-4); font-weight: 600; }
.rs-number-value {
    display: block; margin-top: 4px;
    font-family: ui-monospace, Menlo, monospace;
    font-size: clamp(var(--fs-lg), 5.2vw, var(--fs-xl));
    font-weight: 800; color: var(--mobile-primary);
    letter-spacing: 0.02em;
    word-break: break-all;
}

.rs-dl { display: grid; grid-template-columns: minmax(7rem, 35%) 1fr; gap: var(--space-xs) var(--space-md); font-size: var(--fs-sm); }
.rs-dl dt { color: var(--ink-4); font-weight: 500; margin: 0; }
.rs-dl dd { color: var(--ink); font-weight: 700; margin: 0; word-break: break-word; text-wrap: pretty; }

.rs-actions {
    position: fixed; left: 0; right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    background: var(--surface); padding: var(--space-md);
    box-shadow: 0 -4px 16px color-mix(in oklch, var(--ink) 8%, transparent);
    border-top: 1px solid var(--ink-line);
    z-index: 1031;
    display: flex; flex-direction: column; gap: var(--space-xs);
}
.rs-actions-row { display: flex; gap: var(--space-xs); }
.rs-actions .btn {
    min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    gap: var(--space-2xs); flex: 1;
    transition: opacity 200ms cubic-bezier(0.22, 1, 0.36, 1),
                background-color 180ms ease-out, transform 120ms ease-out;
}
.rs-actions .btn:active { transform: scale(0.985); }
.rs-actions .btn-primary { background: var(--mobile-primary); color: #fff; border: 0; }
.rs-actions .btn-secondary { background: var(--surface-2); color: var(--ink-2); border: 0; }

@media (prefers-reduced-motion: reduce) {
    .rs-hero-overlay-shell { animation: none !important; transform: none !important; }
    .rs-medal { animation: none !important; }
    .rs-medal svg path { animation: none !important; stroke-dashoffset: 0; }
    .rs-actions .btn { transition: none !important; }
}
</style>

<div class="rs-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'        => 'check-circle-2',
        'title'       => $this->params['mobileTitle'],
        'subtitle'    => $this->params['mobileSubtitle'],
        'overlayHtml' => $overlayHtml,
    ]) ?>

    <div class="app-scroll has-overlay rs-scroll">

        <div class="rs-body">

            <?php if ($repairNo !== ''): ?>
                <section class="rs-card" aria-labelledby="rs-card-no">
                    <header class="rs-card-head">
                        <h2 class="rs-card-title" id="rs-card-no">
                            <i data-lucide="hash"></i>
                            เลขที่คำขอซ่อม
                        </h2>
                    </header>
                    <div class="rs-number">
                        <span class="rs-number-label">บันทึกในระบบเรียบร้อย</span>
                        <span class="rs-number-value"><?= Html::encode($repairNo) ?></span>
                    </div>
                </section>
            <?php endif; ?>

            <section class="rs-card" aria-labelledby="rs-card-info">
                <header class="rs-card-head">
                    <h2 class="rs-card-title" id="rs-card-info">
                        <i data-lucide="file-text"></i>
                        ข้อมูลคำขอ
                    </h2>
                </header>
                <dl class="rs-dl">
                    <?php if ($assetNo !== '' || $assetName !== ''): ?>
                        <dt>ครุภัณฑ์</dt>
                        <dd>
                            <?= Html::encode($assetName !== '' ? $assetName : $assetNo) ?>
                            <?php if ($assetName !== '' && $assetNo !== ''): ?>
                                <span style="color: var(--ink-4); font-weight: 500;">(<?= Html::encode($assetNo) ?>)</span>
                            <?php endif; ?>
                        </dd>
                    <?php endif; ?>

                    <?php if ($deviceTxt !== ''): ?>
                        <dt>ประเภทอุปกรณ์</dt>
                        <dd><?= Html::encode($deviceTxt) ?></dd>
                    <?php endif; ?>

                    <?php if ($location !== ''): ?>
                        <dt>สถานที่</dt>
                        <dd><?= Html::encode($location) ?></dd>
                    <?php endif; ?>

                    <?php if ($urgencyTxt !== ''): ?>
                        <dt>ความเร่งด่วน</dt>
                        <dd><?= Html::encode($urgencyTxt) ?></dd>
                    <?php endif; ?>

                    <dt>วันที่แจ้ง</dt>
                    <dd><?= Html::encode($createdAtText) ?></dd>

                    <dt>สถานะ</dt>
                    <dd style="color: var(--warning);">รอดำเนินการ</dd>
                </dl>
            </section>

            <?php if (!empty($model->title)): ?>
                <section class="rs-card" aria-labelledby="rs-card-desc">
                    <header class="rs-card-head">
                        <h2 class="rs-card-title" id="rs-card-desc">
                            <i data-lucide="message-square-text"></i>
                            รายละเอียดที่แจ้ง
                        </h2>
                    </header>
                    <p style="margin: 0; white-space: pre-wrap; word-break: break-word; font-size: var(--fs-sm); color: var(--ink); line-height: 1.55;">
                        <?= Html::encode((string) $model->title) ?>
                    </p>
                </section>
            <?php endif; ?>

        </div>
    </div>

    <div class="rs-actions" role="toolbar" aria-label="หลังส่งคำขอซ่อม">
        <div class="rs-actions-row">
            <a href="<?= Html::encode($historyUrl) ?>" class="btn btn-secondary">
                <i data-lucide="history"></i>
                <span>ดูประวัติส่งซ่อม</span>
            </a>
            <?php if ($assetUrl !== ''): ?>
                <a href="<?= Html::encode($assetUrl) ?>" class="btn btn-secondary">
                    <i data-lucide="package"></i>
                    <span>กลับครุภัณฑ์</span>
                </a>
            <?php endif; ?>
        </div>
        <a href="<?= Html::encode($homeUrl) ?>" class="btn btn-primary">
            <i data-lucide="home"></i>
            <span>กลับหน้าหลัก</span>
        </a>
    </div>
</div>
