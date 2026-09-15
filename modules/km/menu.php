<?php

use yii\helpers\Url;

/**
 * เมนูคลัง KM (จัดวางแนวเดียวกับงาน HRD)
 *
 * - ระดับโซน (มาตรฐานโรงพยาบาล / คลัง KM + ตั้งค่า) → บล็อก page-action มุมขวาหัวข้อหน้า
 *   ลงทะเบียนจากที่นี่เลย ทุก view ที่ render เมนูนี้จึงได้แถบบนอัตโนมัติ ไม่ต้องแก้ทีละหน้า
 * - เมนูย่อย → ปุ่มสไตล์เดียวกับปุ่มระดับโซน (btn-outline-primary / active=btn-primary)
 *   ตามมาตรฐานเมนูของระบบ (เลิกใช้ pill/segmented)
 *
 * @var string $active คีย์เมนูย่อยที่กำลังเปิด (overview | activity)
 */
$active = $active ?? '';

// ระดับโซน → page-action (มุมขวาหัวข้อหน้า)
$this->beginBlock('page-action');
echo $this->render('@app/modules/km/_zone_action', ['active' => 'km']);
$this->endBlock();

$items = [
    ['key' => 'overview', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/km/default/index']],
    ['key' => 'activity', 'label' => 'ทะเบียนกิจกรรม', 'icon' => 'bi-collection', 'url' => ['/km/activity/index']],
];
?>
<nav class="km-subnav d-flex flex-wrap align-items-center gap-2" aria-label="เมนูคลัง KM">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($item['url']) ?>"
           class="btn <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2"
           <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
            <i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i><span><?= $item['label'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
