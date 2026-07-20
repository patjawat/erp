<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\am\models\AssetCategory[] $categories */
?>
<?php foreach ($categories as $item): ?>
    <?php
        $isActive = $item->active === null || (int) $item->active !== 0;
        $isDraft = trim((string) $item->code) === '' || !$isActive;
    ?>
    <div class="list-group-item d-flex align-items-center justify-content-between gap-2">
        <div class="min-w-0">
            <div class="fw-semibold text-truncate">
                <?= Html::encode($item->title) ?>
                <?php if ($isDraft): ?>
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1">ร่าง</span>
                <?php endif; ?>
            </div>
            <div class="small text-muted font-monospace">
                <?= trim((string) $item->code) !== '' ? Html::encode($item->code) : '—' ?><?= $item->assetType ? ' · ' . Html::encode($item->assetType->title) : '' ?>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1 flex-shrink-0">
            <div class="form-check form-switch m-0 me-1" title="<?= $isActive ? 'เปิดใช้งานอยู่ — คลิกเพื่อปิด (เป็นร่าง)' : 'ปิดอยู่ (ร่าง) — คลิกเพื่อเปิดใช้งาน' ?>">
                <input type="checkbox" role="switch" class="form-check-input category-quick-list__toggle"
                    data-url="<?= Url::to(['/am/asset-category/toggle-active', 'id' => $item->id]) ?>"
                    <?= $isActive ? 'checked' : '' ?>>
            </div>
            <?php
            // _t กันเบราว์เซอร์ cache ผล GET ของ .open-modal (global handler ไม่ได้ตั้ง cache:false)
            // ต้อง unique จริงๆ ทุกครั้งที่ render — time() ละเอียดแค่ระดับวินาที ถ้ามีการรีเฟรช list ซ้ำในวินาทีเดียวกัน
            // (เช่นตอนกำลังทดสอบเร็วๆ) จะได้ _t ซ้ำกับรอบก่อนหน้า เบราว์เซอร์เลย cache ทับ ใช้ uniqid() แทนให้ชัวร์
            $editUrlNonce = uniqid('', true);
            ?>
            <?= Html::a('<i class="bi bi-pencil"></i>', ['/am/asset-category/update', 'id' => $item->id, '_t' => $editUrlNonce, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไขหมวดหมู่ครุภัณฑ์'], ['class' => 'btn btn-sm btn-outline-secondary open-modal category-quick-list__modal-trigger', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
            <?= Html::a('<i class="bi bi-trash"></i>', ['/am/asset-category/delete', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-danger category-quick-list__delete', 'title' => 'ลบ']) ?>
        </div>
    </div>
<?php endforeach; ?>
<?php if (empty($categories)): ?>
    <p class="text-muted small text-center py-3 mb-0">ไม่พบหมวดหมู่ที่ตรงกับเงื่อนไข</p>
<?php endif; ?>
