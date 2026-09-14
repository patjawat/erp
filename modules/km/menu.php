<?php

use yii\helpers\Url;

/**
 * เมนูคลังกิจกรรม KM (จัดวางแนวเดียวกับงาน HRD)
 *
 * - ระดับโซน (มาตรฐานโรงพยาบาล / คลังกิจกรรม KM + ตั้งค่า) → บล็อก page-action มุมขวาหัวข้อหน้า
 *   ลงทะเบียนจากที่นี่เลย ทุก view ที่ render เมนูนี้จึงได้แถบบนอัตโนมัติ ไม่ต้องแก้ทีละหน้า
 * - เมนูย่อย → segmented pill สี (แนวเดียวกับ workforce-nav ของ HRD) ในเนื้อหน้า
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

$this->registerCss(<<<'CSS'
.km-subnav{display:flex;flex-wrap:wrap;gap:.4rem;width:max-content;max-width:100%;padding:.35rem;background:var(--bs-tertiary-bg);border:1px solid var(--bs-border-color);border-radius:.8rem;overflow-x:auto}
.km-subnav__item{display:inline-flex;align-items:center;gap:.45rem;min-height:42px;padding:.5rem 1rem;border:1px solid var(--sub);border-radius:.55rem;background:var(--bs-body-bg);color:var(--sub);text-decoration:none;font-weight:600;white-space:nowrap}
.km-subnav__item:hover{background:var(--sub-soft);border-color:var(--sub);color:var(--sub)}
.km-subnav__item.is-active{background:var(--sub-soft);border-color:var(--sub);font-weight:700;box-shadow:0 0 0 1px var(--sub)}
.km-subnav__item--overview{--sub:#475467;--sub-soft:#f2f4f7}
.km-subnav__item--activity{--sub:#0f766e;--sub-soft:#f0fdfa}
CSS);
?>
<nav class="km-subnav" aria-label="เมนูคลังกิจกรรม KM">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($item['url']) ?>"
           class="km-subnav__item km-subnav__item--<?= $item['key'] ?><?= $active === $item['key'] ? ' is-active' : '' ?>"
           <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
            <i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i><span><?= $item['label'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
