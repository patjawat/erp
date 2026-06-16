<?php

use app\components\ThaiDateHelper;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\mobile\services\MobileMaintenanceService;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\helpdesk2\models\Helpdesk $model */
/** @var MobileMaintenanceService $service */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'รายละเอียดงานแจ้งซ่อม';
$this->params['mobileSubtitle'] = 'ติดตามสถานะและประวัติการซ่อม';

$dataJson    = is_array($model->data_json ?? null) ? $model->data_json : [];
$problemLbl  = (string) ($dataJson['problem_type_label'] ?? 'งานซ่อม');
$location    = (string) ($dataJson['location'] ?? '');
$description = (string) ($dataJson['description'] ?? '');
$assetName   = (string) ($dataJson['asset_name'] ?? '');
$assetCode   = (string) ($dataJson['asset_code'] ?? ($model->asset_number ?? ''));
$repairNo    = (string) ($model->repair_number ?? '');
$status      = (string) ($model->status ?? '');
$info        = $service->statusInfo($status);
$step        = (int) $info['step'];
$tone        = (string) $info['tone'];
$statusLabel = (string) $info['label'];

$createdAtText = '-';
if (!empty($model->created_at)) {
    try {
        $createdAtText = ThaiDateHelper::formatThaiDate((string) $model->created_at, 'long')
            . ' ' . date('H:i', strtotime((string) $model->created_at)) . ' น.';
    } catch (\Throwable $e) { $createdAtText = (string) $model->created_at; }
}

$photos = Uploads::find()->where(['ref' => $model->ref, 'name' => 'repair_request'])->all();
$workPhotos = Uploads::find()->where(['ref' => $model->ref, 'name' => 'repair_work_photo'])->all();

$rating = trim((string) ($model->rating ?? ''));
$hasRated = ($rating !== '' && $rating !== '0');
$ratingScore   = $hasRated ? (int) $rating : 0;
$ratingComment = (string) ($dataJson['rating_comment'] ?? '');
$ratingAt      = (string) ($dataJson['rating_at'] ?? '');

// แก้ไข: wizard ส่งซ่อมใหม่ยังไม่รองรับ edit mode → ซ่อนปุ่มแก้ไขในเวอร์ชั่นนี้
// (workflow เดิมไม่ได้อนุญาต edit หลังส่ง ปกติงานซ่อมจะแก้ผ่าน "ขอยกเลิก" แล้วแจ้งใหม่)
$canRate   = $service->canRate($model);
$canCancel = $service->canCancel($model);

$rateUrl   = Url::to(['/mobile/default/maintenance-rate', 'id' => $model->id]);
$cancelUrl = Url::to(['/mobile/default/maintenance-cancel', 'id' => $model->id]);
$listUrl   = Url::to(['/mobile/default/maintenance-request']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

// Hero overlay
ob_start(); ?>
<div class="mv-hero-overlay-shell">
    <div class="mv-hero-overlay" aria-label="ภาพรวมงานแจ้งซ่อม">
        <div class="mv-hero-top">
            <div>
                <div class="mv-hero-type"><?= Html::encode($problemLbl) ?></div>
                <?php if ($repairNo): ?>
                    <div class="mv-hero-no">เลขที่ <?= Html::encode($repairNo) ?></div>
                <?php endif; ?>
            </div>
            <span class="mv-status-pill" data-tone="<?= Html::encode($tone) ?>">
                <?= Html::encode($statusLabel) ?>
            </span>
        </div>
        <?php if ($createdAtText !== '-'): ?>
            <div class="mv-hero-time">
                <i data-lucide="clock-3" aria-hidden="true"></i>
                แจ้งเมื่อ <?= Html::encode($createdAtText) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $overlayHtml = ob_get_clean(); ?>

<style>
/* ───── Maintenance view (mv-) ───── */
.mv-root { margin: -1rem -1rem 0; display: flex; flex-direction: column; }
.mv-scroll { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 9.5rem); }

/* Hero overlay — pattern เดียวกับ .app-stats */
.mv-hero-overlay-shell {
    position: relative; z-index: 2;
    margin: -1.75rem var(--space-md) 0;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12), 0 2px 6px rgba(15, 23, 42, 0.04);
    padding: var(--space-md);
    animation: mv-overlay-in 360ms cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes mv-overlay-in {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.mv-hero-overlay { display: flex; flex-direction: column; gap: var(--space-xs); }
.mv-hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: var(--space-sm); }
.mv-hero-type { font-size: var(--fs-md); font-weight: 700; color: var(--ink); line-height: 1.3; word-break: break-word; }
.mv-hero-no   { font-size: var(--fs-xs); color: var(--ink-3); margin-top: 4px; font-weight: 600; letter-spacing: 0.02em; }
.mv-hero-time { display: inline-flex; align-items: center; gap: 4px; font-size: var(--fs-xs); color: var(--ink-3); }
.mv-hero-time svg { width: 12px; height: 12px; color: var(--ink-4); }

.mv-status-pill {
    flex-shrink: 0; border-radius: 999px; padding: 4px 12px;
    font-size: var(--fs-2xs); font-weight: 700; line-height: 1.4;
}
.mv-status-pill[data-tone="warning"]   { background: var(--warning-soft);  color: var(--warning); }
.mv-status-pill[data-tone="info"]      { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.mv-status-pill[data-tone="success"]   { background: var(--success-soft);  color: var(--success); }
.mv-status-pill[data-tone="danger"]    { background: var(--danger-soft);   color: var(--danger-strong); }
.mv-status-pill[data-tone="secondary"] { background: color-mix(in oklch, var(--ink-4) 15%, transparent); color: var(--ink-3); }

/* ── Cards ── */
.mv-body { padding: var(--space-md); display: flex; flex-direction: column; gap: var(--space-md); }
.mv-card {
    background: var(--surface);
    border-radius: 16px;
    padding: var(--space-md);
    box-shadow: var(--shadow-sm);
}
.mv-card-head {
    display: flex; align-items: center; gap: var(--space-xs);
    padding-bottom: var(--space-xs); margin-bottom: var(--space-sm);
    border-bottom: 1px solid var(--ink-line);
}
.mv-card-title {
    font-size: var(--fs-md); font-weight: 700; color: var(--ink);
    margin: 0; display: inline-flex; align-items: center; gap: var(--space-xs);
}
.mv-card-title svg { width: 1.125rem; height: 1.125rem; color: var(--mobile-primary); }
.mv-dl { display: grid; grid-template-columns: minmax(7rem, 35%) 1fr; gap: var(--space-xs) var(--space-md); font-size: var(--fs-sm); }
.mv-dl dt { color: var(--ink-4); font-weight: 500; margin: 0; }
.mv-dl dd { color: var(--ink); font-weight: 600; margin: 0; word-break: break-word; text-wrap: pretty; }
.mv-desc {
    background: var(--surface-2); border-radius: 12px;
    padding: var(--space-sm) var(--space-md);
    font-size: var(--fs-sm); color: var(--ink); line-height: 1.55;
    white-space: pre-wrap; word-break: break-word;
}

/* ── Status timeline ── */
.mv-timeline { list-style: none; padding: 0; margin: 0; position: relative; }
.mv-timeline::before {
    content: ''; position: absolute;
    left: calc(0.875rem - 1px); top: 1rem; bottom: 1rem;
    width: 2px; background: var(--ink-line); border-radius: 999px;
}
.mv-timeline-item {
    position: relative; display: flex; gap: var(--space-sm);
    padding: var(--space-xs) 0;
    opacity: 0; animation: mv-step-in 320ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
.mv-timeline-item:nth-child(1) { animation-delay: 40ms; }
.mv-timeline-item:nth-child(2) { animation-delay: 100ms; }
.mv-timeline-item:nth-child(3) { animation-delay: 160ms; }
.mv-timeline-item:nth-child(4) { animation-delay: 220ms; }
@keyframes mv-step-in {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}
.mv-timeline-dot {
    flex-shrink: 0;
    width: 1.75rem; height: 1.75rem; border-radius: 50%;
    background: var(--surface);
    border: 2px solid var(--ink-line);
    display: flex; align-items: center; justify-content: center;
    color: var(--ink-4);
    z-index: 1;
    transition: background 240ms cubic-bezier(0.22, 1, 0.36, 1),
                border-color 240ms cubic-bezier(0.22, 1, 0.36, 1),
                color 240ms cubic-bezier(0.22, 1, 0.36, 1);
}
.mv-timeline-dot svg { width: 0.875rem; height: 0.875rem; }
.mv-timeline-item.is-done .mv-timeline-dot {
    background: var(--success); border-color: var(--success); color: #fff;
}
.mv-timeline-item.is-current .mv-timeline-dot {
    background: var(--mobile-primary); border-color: var(--mobile-primary); color: #fff;
    box-shadow: 0 0 0 4px color-mix(in oklch, var(--mobile-primary) 16%, transparent);
}
.mv-timeline-item.is-cancelled .mv-timeline-dot {
    background: var(--danger); border-color: var(--danger); color: #fff;
}
.mv-timeline-meta { flex: 1; padding-top: 2px; }
.mv-timeline-label { font-size: var(--fs-sm); font-weight: 700; color: var(--ink-3); line-height: 1.4; }
.mv-timeline-item.is-current .mv-timeline-label { color: var(--mobile-primary); }
.mv-timeline-item.is-done .mv-timeline-label    { color: var(--ink); }
.mv-timeline-sub { font-size: var(--fs-xs); color: var(--ink-4); margin-top: 2px; line-height: 1.4; }

/* ── Photos ── */
.mv-photos {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-xs);
}
.mv-photo {
    aspect-ratio: 1 / 1; border-radius: 12px; overflow: hidden;
    background: var(--surface-2); display: block;
    transition: transform 160ms cubic-bezier(0.22, 1, 0.36, 1);
}
.mv-photo:active { transform: scale(0.97); }
.mv-photo img { width: 100%; height: 100%; object-fit: cover; }
.mv-empty { text-align: center; padding: var(--space-md) 0; color: var(--ink-4); font-size: var(--fs-sm); }

/* ── Rating shown ── */
.mv-rating-shown { display: flex; flex-direction: column; gap: var(--space-xs); align-items: flex-start; }
.mv-stars { display: inline-flex; gap: 2px; color: #f59e0b; }
.mv-stars svg { width: 1.25rem; height: 1.25rem; fill: currentColor; }
.mv-rating-text { font-size: var(--fs-sm); color: var(--ink-2); line-height: 1.5; }
.mv-rating-time { font-size: var(--fs-xs); color: var(--ink-4); }

/* ── Action bar ── */
.mv-actions {
    position: fixed; left: 0; right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    background: var(--surface); padding: var(--space-md);
    box-shadow: 0 -4px 16px color-mix(in oklch, var(--ink) 8%, transparent);
    border-top: 1px solid var(--ink-line);
    z-index: 1031;
    display: flex; flex-direction: column; gap: var(--space-xs);
}
.mv-actions-row { display: flex; gap: var(--space-xs); }
.mv-actions .btn {
    min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    gap: var(--space-2xs); flex: 1;
    transition: opacity 200ms cubic-bezier(0.22, 1, 0.36, 1),
                background-color 180ms ease-out, transform 120ms ease-out;
}
.mv-actions .btn:active { transform: scale(0.985); }
.mv-actions .btn-edit   { background: var(--mobile-primary); color: #fff; border: 0; }
.mv-actions .btn-rate   { background: #f59e0b; color: #fff; border: 0; }
.mv-actions .btn-cancel { background: var(--danger-soft); color: var(--danger-strong); border: 0; }
.mv-actions .btn-back   { background: var(--surface-2); color: var(--ink-2); border: 0; }
.mv-actions .btn.is-busy { opacity: 0.7; pointer-events: none; }

@media (prefers-reduced-motion: reduce) {
    .mv-hero-overlay-shell { animation: none !important; transform: none !important; }
    .mv-timeline-item { animation: none !important; opacity: 1 !important; transform: none !important; }
    .mv-actions .btn, .mv-photo, .mv-timeline-dot { transition: none !important; }
}
</style>

<div class="mv-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'        => 'wrench',
        'title'       => $this->params['mobileTitle'],
        'subtitle'    => $this->params['mobileSubtitle'],
        'overlayHtml' => $overlayHtml,
    ]) ?>

    <div class="app-scroll has-overlay mv-scroll">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success rounded-3 mx-3 mt-3 mb-0" role="status"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger rounded-3 mx-3 mt-3 mb-0" role="alert"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
        <?php endif; ?>

        <div class="mv-body">

            <!-- Status timeline -->
            <section class="mv-card" aria-labelledby="mv-card-status">
                <header class="mv-card-head">
                    <h2 class="mv-card-title" id="mv-card-status">
                        <i data-lucide="activity"></i>
                        สถานะการซ่อม
                    </h2>
                </header>
                <?php
                $steps = [
                    1 => ['label' => 'รอรับเรื่อง',  'sub' => 'รอเจ้าหน้าที่รับเรื่อง'],
                    2 => ['label' => 'รับเรื่องแล้ว', 'sub' => 'อยู่ในคิวซ่อม'],
                    3 => ['label' => 'กำลังซ่อม',    'sub' => 'ช่างกำลังดำเนินการ'],
                    4 => ['label' => 'ซ่อมเสร็จแล้ว', 'sub' => 'ส่งคืนผู้ใช้งานเรียบร้อย'],
                ];
                $isCancelled = ($step === 0 && in_array($status, ['5', '6', 'Cancel', 'Reject'], true));
                ?>
                <ul class="mv-timeline">
                    <?php foreach ($steps as $sNum => $sData):
                        $isDone    = !$isCancelled && $step > $sNum;
                        $isCurrent = !$isCancelled && $step === $sNum;
                        $cls = $isCurrent ? 'is-current' : ($isDone ? 'is-done' : '');
                    ?>
                        <li class="mv-timeline-item <?= $cls ?>">
                            <span class="mv-timeline-dot" aria-hidden="true">
                                <?php if ($isDone): ?>
                                    <i data-lucide="check"></i>
                                <?php else: ?>
                                    <span style="font-size:0.75rem;font-weight:700;line-height:1;"><?= $sNum ?></span>
                                <?php endif; ?>
                            </span>
                            <div class="mv-timeline-meta">
                                <div class="mv-timeline-label"><?= Html::encode($sData['label']) ?></div>
                                <div class="mv-timeline-sub"><?= Html::encode($sData['sub']) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($isCancelled): ?>
                        <li class="mv-timeline-item is-cancelled">
                            <span class="mv-timeline-dot" aria-hidden="true">
                                <i data-lucide="x"></i>
                            </span>
                            <div class="mv-timeline-meta">
                                <div class="mv-timeline-label" style="color: var(--danger-strong);"><?= Html::encode($statusLabel) ?></div>
                                <div class="mv-timeline-sub">งานนี้ถูกยกเลิกจากระบบแล้ว</div>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </section>

            <!-- ข้อมูลคำขอ -->
            <section class="mv-card" aria-labelledby="mv-card-info">
                <header class="mv-card-head">
                    <h2 class="mv-card-title" id="mv-card-info">
                        <i data-lucide="file-text"></i>
                        ข้อมูลคำขอ
                    </h2>
                </header>
                <dl class="mv-dl">
                    <dt>ประเภทปัญหา</dt>
                    <dd><?= Html::encode($problemLbl) ?></dd>

                    <?php if ($location): ?>
                        <dt>สถานที่</dt>
                        <dd><?= Html::encode($location) ?></dd>
                    <?php endif; ?>

                    <?php if ($assetName || $assetCode): ?>
                        <dt>ครุภัณฑ์</dt>
                        <dd><?= Html::encode($assetName ?: $assetCode) ?>
                            <?php if ($assetCode && $assetName): ?>
                                <span style="color: var(--ink-4); font-weight: 500;">(<?= Html::encode($assetCode) ?>)</span>
                            <?php endif; ?>
                        </dd>
                    <?php endif; ?>

                    <?php if ($createdAtText !== '-'): ?>
                        <dt>วันที่แจ้ง</dt>
                        <dd><?= Html::encode($createdAtText) ?></dd>
                    <?php endif; ?>
                </dl>
            </section>

            <!-- รายละเอียดปัญหา -->
            <?php if ($description): ?>
                <section class="mv-card" aria-labelledby="mv-card-desc">
                    <header class="mv-card-head">
                        <h2 class="mv-card-title" id="mv-card-desc">
                            <i data-lucide="message-square-text"></i>
                            รายละเอียดปัญหา
                        </h2>
                    </header>
                    <div class="mv-desc"><?= Html::encode($description) ?></div>
                </section>
            <?php endif; ?>

            <!-- รูปภาพประกอบ -->
            <?php if (!empty($photos)): ?>
                <section class="mv-card" aria-labelledby="mv-card-photos">
                    <header class="mv-card-head">
                        <h2 class="mv-card-title" id="mv-card-photos">
                            <i data-lucide="image"></i>
                            รูปภาพประกอบ
                        </h2>
                    </header>
                    <div class="mv-photos">
                        <?php foreach ($photos as $photo):
                            $thumbUrl = '';
                            try { $thumbUrl = FileManagerHelper::getImg($photo->id); } catch (\Throwable $e) {}
                        ?>
                            <a class="mv-photo" href="<?= Html::encode($thumbUrl) ?>" target="_blank" rel="noopener noreferrer">
                                <img src="<?= Html::encode($thumbUrl) ?>" alt="<?= Html::encode($photo->file_name) ?>">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- รูปภาพการซ่อม (เพิ่มโดยทีมช่าง) -->
            <?php if (!empty($workPhotos)): ?>
                <section class="mv-card" aria-labelledby="mv-card-work">
                    <header class="mv-card-head">
                        <h2 class="mv-card-title" id="mv-card-work">
                            <i data-lucide="check-circle-2"></i>
                            รูปการซ่อม
                        </h2>
                    </header>
                    <div class="mv-photos">
                        <?php foreach ($workPhotos as $photo):
                            $thumbUrl = '';
                            try { $thumbUrl = FileManagerHelper::getImg($photo->id); } catch (\Throwable $e) {}
                        ?>
                            <a class="mv-photo" href="<?= Html::encode($thumbUrl) ?>" target="_blank" rel="noopener noreferrer">
                                <img src="<?= Html::encode($thumbUrl) ?>" alt="<?= Html::encode($photo->file_name) ?>">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- คะแนนที่เคยให้ -->
            <?php if ($hasRated): ?>
                <section class="mv-card" aria-labelledby="mv-card-rating">
                    <header class="mv-card-head">
                        <h2 class="mv-card-title" id="mv-card-rating">
                            <i data-lucide="star"></i>
                            คะแนนความพึงพอใจ
                        </h2>
                    </header>
                    <div class="mv-rating-shown">
                        <div class="mv-stars" aria-label="คะแนน <?= (int) $ratingScore ?> จาก 5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i data-lucide="star" <?php if ($i > $ratingScore): ?>style="fill: transparent; color: var(--ink-line);"<?php endif; ?>></i>
                            <?php endfor; ?>
                        </div>
                        <?php if ($ratingComment): ?>
                            <div class="mv-rating-text"><?= Html::encode($ratingComment) ?></div>
                        <?php endif; ?>
                        <?php if ($ratingAt): ?>
                            <div class="mv-rating-time">ลงคะแนนเมื่อ <?= Html::encode($ratingAt) ?></div>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action bar -->
    <div class="mv-actions" role="toolbar" aria-label="การกระทำกับงานแจ้งซ่อม">
        <?php if ($canRate || $canCancel): ?>
            <div class="mv-actions-row">
                <?php if ($canRate): ?>
                    <button type="button" class="btn btn-rate" id="mv-rate-btn">
                        <i data-lucide="star" aria-hidden="true"></i>
                        <span>ลงคะแนน</span>
                    </button>
                <?php endif; ?>
                <?php if ($canCancel): ?>
                    <button type="button" class="btn btn-cancel" id="mv-cancel-btn">
                        <i data-lucide="x-circle" aria-hidden="true"></i>
                        <span>ขอยกเลิก</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <a href="<?= Html::encode($listUrl) ?>" class="btn btn-back">
            <i data-lucide="arrow-left" aria-hidden="true"></i>
            <span>กลับรายการแจ้งซ่อม</span>
        </a>
    </div>
</div>

<?php
$canRateJs   = $canRate ? '1' : '0';
$canCancelJs = $canCancel ? '1' : '0';
$js = <<<JS
(function(){
    var CSRF_PARAM = "{$csrfParam}";
    var CSRF_TOKEN = "{$csrfToken}";
    var RATE_URL   = "{$rateUrl}";
    var CANCEL_URL = "{$cancelUrl}";
    var CAN_RATE   = {$canRateJs};
    var CAN_CANCEL = {$canCancelJs};

    function postJson(url, payload, onDone) {
        var fd = new FormData();
        fd.append(CSRF_PARAM, CSRF_TOKEN);
        Object.keys(payload || {}).forEach(function(k){ fd.append(k, payload[k]); });
        fetch(url, {
            method: 'POST', body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then(function(r){ return r.json().catch(function(){ return { status: 'error' }; }); })
        .then(onDone)
        .catch(function(){ onDone({ status: 'error', message: 'การเชื่อมต่อขัดข้อง' }); });
    }

    // ─── Rate flow ───
    if (CAN_RATE) {
        var rateBtn = document.getElementById('mv-rate-btn');
        if (rateBtn) {
            rateBtn.addEventListener('click', function(){
                if (typeof Swal === 'undefined') return;
                var starsRow = '<div id="mv-rate-stars" style="display:flex;justify-content:center;gap:.5rem;font-size:2rem;color:#f59e0b;">'
                    + '<button type="button" data-score="1" aria-label="1 ดาว">☆</button>'
                    + '<button type="button" data-score="2" aria-label="2 ดาว">☆</button>'
                    + '<button type="button" data-score="3" aria-label="3 ดาว">☆</button>'
                    + '<button type="button" data-score="4" aria-label="4 ดาว">☆</button>'
                    + '<button type="button" data-score="5" aria-label="5 ดาว">☆</button>'
                    + '</div>'
                    + '<input id="mv-rate-score" type="hidden" value="0">'
                    + '<textarea id="mv-rate-comment" class="swal2-textarea" placeholder="ความเห็นเพิ่มเติม (ไม่บังคับ)" style="display:flex;margin:1rem auto 0;"></textarea>';
                Swal.fire({
                    title: 'ให้คะแนนงานซ่อมนี้',
                    html: starsRow,
                    showCancelButton: true,
                    confirmButtonText: 'บันทึกคะแนน',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    focusConfirm: false,
                    didOpen: function(){
                        var hidden = document.getElementById('mv-rate-score');
                        document.querySelectorAll('#mv-rate-stars button').forEach(function(b){
                            b.style.background = 'transparent';
                            b.style.border = '0';
                            b.style.color = 'inherit';
                            b.style.cursor = 'pointer';
                            b.style.transition = 'transform 120ms ease-out';
                            b.addEventListener('click', function(){
                                var s = parseInt(this.dataset.score, 10);
                                hidden.value = s;
                                document.querySelectorAll('#mv-rate-stars button').forEach(function(other){
                                    var so = parseInt(other.dataset.score, 10);
                                    other.textContent = so <= s ? '★' : '☆';
                                });
                                this.style.transform = 'scale(1.18)';
                                setTimeout(function(){ b.style.transform = ''; }, 140);
                            });
                        });
                    },
                    preConfirm: function(){
                        var s = parseInt(document.getElementById('mv-rate-score').value, 10) || 0;
                        var c = document.getElementById('mv-rate-comment').value || '';
                        if (s < 1 || s > 5) {
                            Swal.showValidationMessage('กรุณาเลือกคะแนน 1 ถึง 5 ดาว');
                            return false;
                        }
                        return { score: s, comment: c };
                    }
                }).then(function(r){
                    if (!r.isConfirmed) return;
                    rateBtn.classList.add('is-busy');
                    Swal.fire({ title: 'กำลังบันทึกคะแนน', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
                    postJson(RATE_URL, { score: r.value.score, comment: r.value.comment }, function(res){
                        if (res && res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกคะแนนเรียบร้อย',
                                text: 'ขอบคุณสำหรับ feedback',
                                timer: 1500, showConfirmButton: false,
                            }).then(function(){ window.location.reload(); });
                        } else {
                            rateBtn.classList.remove('is-busy');
                            Swal.fire({ icon: 'error', title: 'บันทึกคะแนนไม่สำเร็จ', text: (res && res.message) || 'กรุณาลองใหม่', confirmButtonColor: '#d33' });
                        }
                    });
                });
            });
        }
    }

    // ─── Cancel flow ───
    if (CAN_CANCEL) {
        var cancelBtn = document.getElementById('mv-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(){
                if (typeof Swal === 'undefined') {
                    if (window.confirm('ยืนยันการยกเลิกงานแจ้งซ่อมนี้?')) {
                        postJson(CANCEL_URL, {}, function(res){
                            if (res && res.status === 'success') window.location.reload();
                            else alert((res && res.message) || 'ยกเลิกไม่สำเร็จ');
                        });
                    }
                    return;
                }
                Swal.fire({
                    title: 'ยืนยันการยกเลิกงานซ่อม?',
                    text: 'หลังกดยืนยันแล้วจะส่งคืน "ศูนย์ซ่อม" และไม่สามารถย้อนกลับได้',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-x-circle me-1"></i> ยืนยันยกเลิก',
                    cancelButtonText: 'ไม่ใช่ตอนนี้',
                }).then(function(r){
                    if (!r.isConfirmed) return;
                    cancelBtn.classList.add('is-busy');
                    Swal.fire({ title: 'กำลังยกเลิก', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
                    postJson(CANCEL_URL, {}, function(res){
                        if (res && res.status === 'success') {
                            Swal.fire({
                                icon: 'success', title: 'ยกเลิกเรียบร้อย',
                                timer: 1400, showConfirmButton: false,
                            }).then(function(){ window.location.reload(); });
                        } else {
                            cancelBtn.classList.remove('is-busy');
                            Swal.fire({ icon: 'error', title: 'ยกเลิกไม่สำเร็จ', text: (res && res.message) || 'กรุณาลองใหม่', confirmButtonColor: '#d33' });
                        }
                    });
                });
            });
        }
    }
})();
JS;
$this->registerJs($js, View::POS_END);
