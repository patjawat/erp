<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แถวรายการของ setting-quick offcanvas (ใช้ทั้งตอนโหลด panel และตอนค้นหา/รีเฟรช)
 *
 * @var array $cfg config จาก controller
 *   - entity     string  group|type|category
 *   - items      array   โมเดล categorise ที่จะแสดง
 *   - updateUrl  array|null route base ปุ่มแก้ไข (null = ไม่มีปุ่ม เช่น กลุ่ม)
 *   - deleteUrl  array|null route base ปุ่มลบ
 *   - toggleUrl  array|null route base สลับเปิด/ปิดใช้งาน (null = ไม่มีสวิตช์)
 *   - label      string  ชื่อเอนทิตี (ใช้ใน title tooltip/modal)
 */
$items = $cfg['items'] ?? [];
$updateUrl = $cfg['updateUrl'] ?? null;
$deleteUrl = $cfg['deleteUrl'] ?? null;
$toggleUrl = $cfg['toggleUrl'] ?? null;
$label = $cfg['label'] ?? '';
$entity = $cfg['entity'] ?? '';
?>
<?php foreach ($items as $item): ?>
    <?php
    // subtitle: รหัส + (หมวดหมู่แสดงประเภทแม่ด้วย)
    $subtitle = Html::encode($item->code);
    if ($entity === 'category' && $item->assetType) {
        $subtitle .= ' · ' . Html::encode($item->assetType->title);
    }
    // _t กัน browser cache ผล GET ของ .open-modal (ดูเหตุผลใน asset-category/_quick_list_items.php)
    $editNonce = uniqid('', true);
    ?>
    <?php
        // หมวด: พร้อมใช้งาน = มีรหัส + เปิดใช้งาน · ไม่งั้น "ร่าง" (ผูกกับครุภัณฑ์ไม่ได้)
        $isActive = $item->active === null || (int) $item->active !== 0;
        $isDraft = $entity === 'category'
            && (trim((string) $item->code) === '' || !$isActive);
        $showToggle = $toggleUrl !== null && $entity === 'category';
    ?>
    <div class="list-group-item d-flex align-items-center justify-content-between gap-2">
        <div class="min-w-0">
            <div class="fw-semibold text-truncate">
                <?= Html::encode($item->title) ?>
                <?php if ($isDraft): ?>
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1">ร่าง</span>
                <?php endif; ?>
            </div>
            <div class="small text-muted font-monospace"><?= $subtitle !== '' ? $subtitle : '—' ?></div>
        </div>
        <div class="d-flex align-items-center gap-1 flex-shrink-0">
            <?php if ($showToggle): ?>
                <div class="form-check form-switch m-0 me-1" title="<?= $isActive ? 'เปิดใช้งานอยู่ — คลิกเพื่อปิด (เป็นร่าง)' : 'ปิดอยู่ (ร่าง) — คลิกเพื่อเปิดใช้งาน' ?>">
                    <input type="checkbox" role="switch" class="form-check-input js-setting-quick__toggle"
                        data-url="<?= Url::to(array_merge($toggleUrl, ['id' => $item->id])) ?>"
                        <?= $isActive ? 'checked' : '' ?>>
                </div>
            <?php endif; ?>
            <?php if ($updateUrl !== null): ?>
                <?= Html::a('<i class="bi bi-pencil"></i>', array_merge($updateUrl, [
                    'id' => $item->id,
                    '_t' => $editNonce,
                    'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข' . $label,
                ]), ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
            <?php endif; ?>
            <?php if ($deleteUrl !== null): ?>
                <?= Html::a('<i class="bi bi-trash"></i>', array_merge($deleteUrl, ['id' => $item->id]), [
                    'class' => 'btn btn-sm btn-outline-danger js-setting-quick__delete',
                    'title' => 'ลบ',
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
<?php if (empty($items)): ?>
    <p class="text-muted small text-center py-3 mb-0">ไม่พบรายการที่ตรงกับเงื่อนไข</p>
<?php endif; ?>
