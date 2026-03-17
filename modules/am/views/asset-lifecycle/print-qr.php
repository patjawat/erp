<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\am\models\Asset[] $assets */

$this->title = 'พิมพ์ QR ครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'จัดการทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ครุภัณฑ์', 'url' => ['/am/equip/index']];
$this->params['breadcrumbs'][] = $this->title;

$assetName = function ($a) {
    return $a->AssetitemName() ?: $a->asset_name ?: $a->code;
};
?>
<style>
.print-qr-sheet { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; max-width: 210mm; margin: 0 auto; }
@media print { .no-print { display: none !important; } .print-qr-sheet { break-inside: avoid; } }
.sticker { border: 1px solid #ddd; padding: 10px; text-align: center; font-size: 12px; }
.sticker .qr-wrap { margin-bottom: 6px; }
.sticker .qr-wrap img { width: 80px; height: 80px; }
.sticker .code { font-weight: bold; font-size: 14px; }
.sticker .name, .sticker .dept, .sticker .price { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sticker .price { font-weight: 600; color: #0d6efd; font-size: 12px; }
</style>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3 no-print">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted">เลือกครุภัณฑ์ที่ต้องการพิมพ์สติกเกอร์ QR (ส่ง ids ผ่าน URL: ?ids=1,2,3 หรือจากหน้ารายการ)</p>
                    <form method="get" action="<?= Url::to(['print-qr']) ?>" class="row g-2">
                        <div class="col-auto">
                            <input type="text" name="ids" class="form-control" placeholder="รหัส ID คั่นด้วย comma เช่น 1,2,3" style="min-width: 220px;">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">แสดงสติกเกอร์</button>
                        </div>
                        <?php if (!empty($assets)): ?>
                        <div class="col-12 mt-2">
                            <button type="button" class="btn btn-success" onclick="window.print();">
                                <i class="fa-solid fa-print me-1"></i> พิมพ์
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($assets)): ?>
    <div class="print-qr-sheet mt-3">
        <?php foreach ($assets as $a): ?>
        <div class="sticker">
            <div class="qr-wrap">
                <?php
                if (!empty($a->qr_code_path)) {
                    echo Html::img($a->qr_code_path, ['alt' => $a->code, 'style' => 'width:80px;height:80px;']);
                } else {
                    $src = $a->QrCode();
                    if (is_string($src) && $src !== '') {
                        echo Html::img($src, ['alt' => $a->code, 'style' => 'width:80px;height:80px;']);
                    } else {
                        echo '<span class="text-muted small">ไม่มี QR</span>';
                    }
                }
                ?>
            </div>
            <div class="code"><?= Html::encode($a->code) ?></div>
            <div class="name"><?= Html::encode($assetName($a)) ?></div>
            <div class="price"><?= isset($a->price) && $a->price !== '' ? number_format((float) $a->price, 2) . ' บาท' : '-' ?></div>
            <div class="dept"><?= Html::encode($a->departmentName() ?: '-') ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
