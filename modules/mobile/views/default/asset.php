<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\modules\am\models\Asset;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string|int|null $id */
/** @var string|null $code */
/** @var Asset|null $asset */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ข้อมูลครุภัณฑ์';
$this->params['mobileSubtitle'] = $asset ? ($asset->AssetitemName() ?: $asset->code) : 'รายละเอียดครุภัณฑ์';

$lifecycleLabels = [
    Asset::LIFECYCLE_RECEIVED => 'รับเข้า',
    Asset::LIFECYCLE_ACTIVE   => 'ใช้งาน',
    Asset::LIFECYCLE_REPAIR   => 'ส่งซ่อม',
    Asset::LIFECYCLE_DISPOSED => 'จำหน่าย',
];
$lifecycleColors = [
    Asset::LIFECYCLE_RECEIVED => 'primary',
    Asset::LIFECYCLE_ACTIVE   => 'success',
    Asset::LIFECYCLE_REPAIR   => 'warning',
    Asset::LIFECYCLE_DISPOSED => 'secondary',
];
?>
<style>
.asset-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.asset-hero {
    min-height: 8rem;
    background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(13, 110, 253, 0.85) 100%);
    color: #fff;
    padding: 1.25rem;
    display: flex;
    align-items: flex-end;
}
.asset-hero h1 { font-size: 1.35rem; font-weight: 600; margin: 0; color: inherit; }
.asset-hero .asset-code { opacity: 0.9; font-size: 0.875rem; margin-top: 0.25rem; }
.asset-qr-wrap {
    width: 80px; height: 80px;
    border-radius: 12px;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}
.asset-detail-row { padding: 0.75rem 1rem; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; }
.asset-detail-row:last-child { border-bottom: 0; }
.asset-detail-row .label { color: #6c757d; font-size: 0.875rem; }
.asset-detail-row .value { font-weight: 500; text-align: right; }
.asset-empty-state { padding: 2rem 1rem; text-align: center; }
.asset-empty-state .icon-wrap { width: 4rem; height: 4rem; margin: 0 auto 1rem; border-radius: 50%; background: rgba(13, 110, 253, 0.1); display: flex; align-items: center; justify-content: center; }
.asset-empty-state .icon-wrap i { color: var(--bs-primary); }
.asset-photo-section {
    position: relative;
    border-radius: 16px 16px 0 0;
    overflow: hidden;
    background: #f1f3f5;
}
.asset-photo-wrap {
    width: 100%;
    aspect-ratio: 4 / 3;
    max-height: 280px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.asset-photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #fff;
}
.asset-photo-wrap img[data-loaded="false"] {
    opacity: 0;
    transition: opacity 0.25s ease;
}
.asset-photo-wrap img.loaded {
    opacity: 1;
}
.asset-photo-skeleton {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #e9ecef 25%, #f1f3f5 50%, #e9ecef 75%);
    background-size: 200% 100%;
    animation: asset-photo-shimmer 1.2s ease-in-out infinite;
}
@keyframes asset-photo-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.asset-photo-expand-hint {
    position: absolute;
    bottom: 0.5rem;
    right: 0.5rem;
    background: rgba(0,0,0,0.5);
    color: #fff;
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    pointer-events: none;
}
.asset-photo-wrap .placeholder-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #868e96;
    padding: 1.5rem;
}
.asset-photo-wrap .placeholder-box .placeholder-icon {
    width: 3rem;
    height: 3rem;
    opacity: 0.6;
}
.asset-photo-wrap .placeholder-box .placeholder-text {
    font-size: 0.8125rem;
}
.asset-photo-modal .modal-dialog { max-width: 100%; margin: 0.5rem; }
.asset-photo-modal .modal-content { border-radius: 12px; overflow: hidden; }
.asset-photo-modal .modal-body { padding: 0; }
.asset-photo-modal .modal-body img { width: 100%; height: auto; display: block; }
.asset-photo-section + .asset-hero { border-radius: 0; }
.asset-card > .asset-hero:first-child { border-radius: 16px 16px 0 0; }
</style>

<div class="d-flex flex-column gap-3">
    <?php if (!$asset): ?>
        <div class="card asset-card">
            <div class="card-body asset-empty-state">
                <div class="icon-wrap">
                    <i data-lucide="package-search" class="mi-xl"></i>
                </div>
                <h6 class="fw-semibold mb-2"><?= $code !== null && $code !== '' ? 'ไม่พบครุภัณฑ์' : 'สแกน QR เพื่อดูข้อมูลครุภัณฑ์' ?></h6>
                <?php if ($code !== null && $code !== ''): ?>
                    <p class="small text-body-secondary mb-3">รหัส "<?= Html::encode($code) ?>" ไม่มีในระบบ หรืออาจถูกยกเลิกแล้ว</p>
                <?php else: ?>
                    <p class="small text-body-secondary mb-3">ใช้ปุ่มสแกนด้านล่างเพื่อสแกน QR Code บนครุภัณฑ์</p>
                <?php endif; ?>
                <a href="<?= Html::encode(Url::to(['/mobile/default/scan'])) ?>" class="btn btn-primary" style="border-radius: 12px;">
                    <i data-lucide="scan" class="me-1" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i>
                    <?= $code !== null && $code !== '' ? 'สแกนใหม่' : 'เปิดสแกน QR' ?>
                </a>
            </div>
        </div>
    <?php else:
        $status = $asset->lifecycle_status ?? Asset::LIFECYCLE_ACTIVE;
        $statusLabel = $lifecycleLabels[$status] ?? $status;
        $statusColor = $lifecycleColors[$status] ?? 'secondary';
        $assetName = $asset->AssetitemName() ?: $asset->name ?? $asset->code;
        $location = is_array($asset->data_json) && isset($asset->data_json['location']) ? $asset->data_json['location'] : null;
        $department = $asset->departmentName();
        $showImg = $asset->ShowImg();
        $photoUrl = isset($showImg['image']) && $showImg['image'] !== '' ? $showImg['image'] : null;
    ?>
        <div class="card asset-card overflow-hidden">
            <div class="asset-photo-section">
                <?php if ($photoUrl): ?>
                <a href="#" class="asset-photo-wrap text-decoration-none d-block" data-bs-toggle="modal" data-bs-target="#asset-photo-modal" role="button">
                    <div class="asset-photo-skeleton" id="asset-photo-skeleton"></div>
                    <?= Html::img($photoUrl, [
                        'alt' => $assetName,
                        'class' => 'asset-photo',
                        'loading' => 'lazy',
                        'data-loaded' => 'false',
                    ]) ?>
                    <span class="asset-photo-expand-hint">
                        <i data-lucide="expand" style="width: 0.875rem; height: 0.875rem;"></i>
                        แตะเพื่อขยาย
                    </span>
                </a>
                <?php else: ?>
                <div class="asset-photo-wrap">
                    <div class="placeholder-box">
                        <i data-lucide="image" class="placeholder-icon"></i>
                        <span class="placeholder-text">ไม่มีรูปภาพครุภัณฑ์</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="asset-hero">
                <div class="d-flex align-items-center gap-3 w-100">
                    <?php if (!empty($asset->qr_code_path)): ?>
                        <div class="asset-qr-wrap">
                            <?= Html::img($asset->qr_code_path, ['alt' => $asset->code, 'style' => 'width:72px;height:72px;object-fit:contain;']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex-grow-1 min-w-0">
                        <h1 class="text-truncate"><?= Html::encode($assetName) ?></h1>
                        <div class="asset-code"><?= Html::encode($asset->code) ?></div>
                        <span class="badge bg-<?= $statusColor ?> bg-opacity-10 text-<?= $statusColor ?> border border-<?= $statusColor ?>-subtle rounded-pill fw-medium px-2 py-1 mt-2 d-inline-block"><?= Html::encode($statusLabel) ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="asset-detail-row">
                    <span class="label">หน่วยงาน</span>
                    <span class="value"><?= Html::encode($department ?: '—') ?></span>
                </div>
                <div class="asset-detail-row">
                    <span class="label">สถานที่ตั้ง</span>
                    <span class="value"><?= Html::encode($location ?: '—') ?></span>
                </div>
                <div class="asset-detail-row">
                    <span class="label">รหัสครุภัณฑ์</span>
                    <span class="value"><?= Html::encode($asset->code) ?></span>
                </div>
                <?php if ($asset->receive_date): ?>
                <div class="asset-detail-row">
                    <span class="label">วันที่รับ</span>
                    <span class="value"><?= Yii::$app->formatter->asDate($asset->receive_date) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($photoUrl): ?>
        <div class="modal fade asset-photo-modal" id="asset-photo-modal" tabindex="-1" aria-labelledby="asset-photo-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2 pe-2">
                        <h5 class="modal-title small" id="asset-photo-modal-label"><?= Html::encode($assetName) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                    </div>
                    <div class="modal-body p-0">
                        <img id="asset-photo-full" src="<?= Html::encode($photoUrl) ?>" alt="<?= Html::encode($assetName) ?>" class="w-100">
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <a href="<?= Html::encode(Url::to(['/mobile/default/maintenance-request', 'asset' => $asset->id])) ?>" class="btn btn-outline-primary w-100" style="border-radius: 12px;">
            <i data-lucide="wrench" class="me-1" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i>
            แจ้งซ่อมครุภัณฑ์นี้
        </a>
        <a href="<?= Html::encode(Url::to(['/mobile/default/scan'])) ?>" class="btn btn-outline-secondary w-100" style="border-radius: 12px;">
            <i data-lucide="scan" class="me-1" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i>
            สแกนครุภัณฑ์อื่น
        </a>
        <?php if ($photoUrl): ?>
        <script>
        (function() {
            var img = document.querySelector('.asset-photo');
            var skeleton = document.getElementById('asset-photo-skeleton');
            if (img) {
                if (img.complete && img.naturalWidth > 0) {
                    img.classList.add('loaded');
                    img.setAttribute('data-loaded', 'true');
                    if (skeleton) skeleton.style.display = 'none';
                } else {
                    img.addEventListener('load', function() {
                        img.classList.add('loaded');
                        img.setAttribute('data-loaded', 'true');
                        if (skeleton) skeleton.style.display = 'none';
                    });
                    img.addEventListener('error', function() {
                        if (skeleton) skeleton.style.display = 'none';
                    });
                }
            }
        })();
        </script>
        <?php endif; ?>
    <?php endif; ?>
</div>
