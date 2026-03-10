<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string|int|null $id */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ข้อมูลครุภัณฑ์';
$this->params['mobileSubtitle'] = 'รายละเอียดครุภัณฑ์';

$id = $id ?? (\Yii::$app->request->get('id'));
$assetCode = $id ? 'AST-' . $id : '—';
$assetName = $id ? 'คอมพิวเตอร์ตั้งโต๊ะ Dell OptiPlex' : '—';
$assetStatus = $id ? 'ใช้งานได้' : '—';
$assetLocation = $id ? 'ชั้น 3 ห้อง 301' : '—';
?>
<style>
.asset-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.asset-image-wrap { height: 10rem; border-radius: 12px; background: #e9ecef; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.asset-image-wrap img { max-width: 100%; max-height: 100%; object-fit: contain; }
.asset-detail-row { padding: 0.75rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; justify-content: space-between; align-items: center; }
.asset-detail-row:last-child { border-bottom: 0; }
</style>

<div class="d-flex flex-column gap-3">
    <?php if (!$id): ?>
        <div class="card asset-card">
            <div class="card-body text-center py-4">
                <i data-lucide="qr-code" style="width: 3rem; height: 3rem; color: #adb5bd;" class="mb-3"></i>
                <h6 class="fw-semibold mb-2">สแกน QR เพื่อดูข้อมูลครุภัณฑ์</h6>
                <p class="small text-body-secondary mb-3">ใช้ปุ่มสแกนด้านล่างเพื่อสแกน QR Code บนครุภัณฑ์</p>
                <a href="<?= Html::encode(Url::to(['/mobile/default/scan'])) ?>" class="btn btn-primary" style="border-radius: 12px;">
                    <i data-lucide="scan" class="me-1" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i>
                    เปิดสแกน QR
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card asset-card">
            <div class="card-body">
                <div class="asset-image-wrap mb-3">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 48' fill='%23adb5bd'%3E%3Crect width='64' height='40' rx='4'/%3E%3Crect x='24' y='40' width='16' height='8' rx='1'/%3E%3C/svg%3E" alt="">
                </div>
                <h5 class="fw-semibold mb-1"><?= Html::encode($assetName) ?></h5>
                <p class="small text-body-secondary mb-3">รหัส: <?= Html::encode($assetCode) ?></p>
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($assetStatus) ?></span>
            </div>
        </div>
        <div class="card asset-card">
            <div class="card-body p-0">
                <div class="asset-detail-row px-3">
                    <span class="text-body-secondary">สถานที่</span>
                    <span class="fw-medium"><?= Html::encode($assetLocation) ?></span>
                </div>
                <div class="asset-detail-row px-3">
                    <span class="text-body-secondary">รหัสครุภัณฑ์</span>
                    <span class="fw-medium"><?= Html::encode($assetCode) ?></span>
                </div>
                <div class="asset-detail-row px-3">
                    <span class="text-body-secondary">สถานะ</span>
                    <span class="fw-medium"><?= Html::encode($assetStatus) ?></span>
                </div>
            </div>
        </div>
        <a href="<?= Html::encode(Url::to(['/mobile/default/maintenance-request'])) ?>?asset=<?= (int) $id ?>" class="btn btn-outline-primary w-100" style="border-radius: 12px;">
            <i data-lucide="wrench" class="me-1" style="width: 1.125rem; height: 1.125rem; vertical-align: -0.2em;"></i>
            แจ้งซ่อมครุภัณฑ์นี้
        </a>
    <?php endif; ?>
</div>
