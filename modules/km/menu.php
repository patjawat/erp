<?php

use yii\helpers\Url;

/**
 * KM page-nav — pill แนวนอนตามมาตรฐาน UI กลาง (active=primary / ที่เหลือ=outline-secondary)
 *
 * แถวบน = สลับโซนภายใน "งานคุณภาพ" (มาตรฐานโรงพยาบาล ↔ คลังกิจกรรม KM)
 *          เพราะ navbar ธีมเป็นเมนูแบน ไม่มี dropdown จึงใช้ page-nav ทำโครง 2 ชั้น
 * แถวล่าง = เมนูย่อยภายในคลังกิจกรรม KM
 *
 * @var string $active คีย์เมนูย่อยที่กำลังเปิด
 */
$active = $active ?? '';

// เมนูย่อยภายในคลังกิจกรรม KM (page-nav pill)
$items = [
    ['key' => 'overview', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/km/default/index']],
    ['key' => 'activity', 'label' => 'ทะเบียนกิจกรรม', 'icon' => 'bi-collection', 'url' => ['/km/activity/index']],
    ['key' => 'category', 'label' => 'หมวดหมู่', 'icon' => 'bi-tags', 'url' => ['/km/category/index']],
];
?>
<?= $this->render('@app/modules/km/_zone_nav', ['active' => 'km']) ?>
<?php if (count($items) > 1): ?>
<nav class="km-nav" aria-label="เมนูคลังกิจกรรม KM">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <?php foreach ($items as $item): ?>
            <a href="<?= Url::to($item['url']) ?>"
               class="btn btn-sm rounded-pill <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi <?= $item['icon'] ?> me-1" aria-hidden="true"></i><?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
<?php endif; ?>
