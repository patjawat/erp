<?php

use yii\helpers\Url;

/**
 * เมนูรับเรื่องร้องเรียน (จัดวางแนวเดียวกับ KM)
 *
 * - ระดับโซน (มาตรฐาน / คลัง KM / รับเรื่องร้องเรียน + ตั้งค่า) → บล็อก page-action มุมขวาหัวข้อหน้า
 * - เมนูย่อย → ปุ่ม btn-outline-primary / active=btn-primary
 *
 * @var string $active คีย์เมนูย่อยที่กำลังเปิด (overview | registry)
 */
$active = $active ?? '';

$this->beginBlock('page-action');
echo $this->render('@app/modules/km/_zone_action', ['active' => 'complaint']);
$this->endBlock();

$items = [
    ['key' => 'overview', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/complaint/default/index']],
    ['key' => 'registry', 'label' => 'ทะเบียนเรื่องร้องเรียน', 'icon' => 'bi-megaphone', 'url' => ['/complaint/complaint/index']],
];
?>
<nav class="complaint-subnav d-flex flex-wrap align-items-center gap-2" aria-label="เมนูรับเรื่องร้องเรียน">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($item['url']) ?>"
           class="btn <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2"
           <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
            <i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i><span><?= $item['label'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
