<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แถบปุ่มของหน้าตั้งค่ากรอบอัตรากำลัง
 *
 * ใช้ผ่านบล็อก page-action เท่านั้น — ธีม v4 ไม่เรนเดอร์บล็อก navbar_menu
 * รูปแบบปุ่มตามมาตรฐานเดียวกับแถบเมนูโมดูลอื่น (ปุ่มกลม active=primary ที่เหลือ outline)
 *
 * @var string $active
 */

$active = $active ?? '';

$items = [
    'settings' => ['ตั้งค่าทั้งหมด', 'bi-grid', ['/settings']],
    'workforce-profile' => ['โปรไฟล์โรงพยาบาล', 'bi-hospital', ['/settings/workforce-profile']],
    'workforce-standard' => ['เกณฑ์กรอบอัตรากำลัง', 'bi-rulers', ['/settings/workforce-standard']],
    'workforce-map' => ['จับคู่ตำแหน่ง', 'bi-link-45deg', ['/settings/workforce-standard/map']],
];
?>
<div class="d-flex flex-wrap align-items-center gap-2">
    <?php foreach ($items as $key => [$label, $icon, $url]): ?>
        <?= Html::a(
            '<i class="bi ' . $icon . '"></i><span class="d-none d-md-inline ms-1">' . Html::encode($label) . '</span>',
            Url::to($url),
            [
                'class' => 'btn btn-sm rounded-pill d-inline-flex align-items-center '
                    . ($active === $key ? 'btn-primary' : 'btn-outline-secondary'),
                'aria-current' => $active === $key ? 'page' : null,
                'title' => $label,
                'data-pjax' => '0',
            ]
        ) ?>
    <?php endforeach; ?>
</div>
