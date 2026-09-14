<?php

use yii\helpers\Url;

/**
 * แถบสลับโซน "งานคุณภาพ" (ระดับบนสุด) — สไตล์ segmented control แบบเมนูงาน HRD (workforce-nav)
 * เพื่อให้เห็นลำดับชั้นต่างจาก page-nav pill ของเมนูย่อยด้านล่าง
 *
 * ใช้ร่วมกันทั้ง qms/menu.php และ km/menu.php:
 *   <?= $this->render('@app/modules/km/_zone_nav', ['active' => 'qms']) ?>
 *
 * @var string $active  'qms' | 'km'
 */
$active = $active ?? '';

$zones = [
    'qms' => ['label' => 'มาตรฐานโรงพยาบาล', 'icon' => 'bi-shield-check', 'url' => ['/qms/default/index']],
    'km'  => ['label' => 'คลังกิจกรรม KM',   'icon' => 'bi-collection',    'url' => ['/km/default/index']],
];
?>
<style>
.qzone-nav{display:flex;width:max-content;max-width:100%;gap:.4rem;padding:.4rem;background:#f3f6fa;border:1px solid #d8dee8;border-radius:.85rem;overflow-x:auto;scrollbar-width:thin}
.qzone-nav__item{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;min-height:44px;padding:.55rem 1.2rem;border:1px solid var(--qz);border-radius:.6rem;background:#fff;color:var(--qz);text-decoration:none;font-weight:600;white-space:nowrap}
.qzone-nav__item i{font-size:1.05rem}
.qzone-nav__item--qms{--qz:#2457a7;--qz-soft:#eff6ff}
.qzone-nav__item--km{--qz:#0f766e;--qz-soft:#f0fdfa}
.qzone-nav__item:hover{background:var(--qz-soft);border-color:var(--qz);color:var(--qz)}
.qzone-nav__item:focus-visible{outline:3px solid rgba(36,87,167,.22);outline-offset:1px}
.qzone-nav__item.is-active{background:var(--qz-soft);border-color:var(--qz);font-weight:700;box-shadow:0 0 0 1px var(--qz)}
@media(max-width:575.98px){.qzone-nav__item{padding:.5rem .85rem}}
</style>
<nav class="qzone-nav mb-3" aria-label="เมนูงานคุณภาพ">
    <?php foreach ($zones as $key => $z): ?>
        <a href="<?= Url::to($z['url']) ?>"
           class="qzone-nav__item qzone-nav__item--<?= $key ?><?= $active === $key ? ' is-active' : '' ?>"
           <?= $active === $key ? 'aria-current="page"' : '' ?>>
            <i class="bi <?= $z['icon'] ?>" aria-hidden="true"></i><span><?= $z['label'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
