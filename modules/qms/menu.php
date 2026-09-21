<?php

use yii\helpers\Url;

/**
 * QMS page-nav — ปุ่มมุมมนปกติ (สไตล์เดียวกับ HA12/KM/ร้องเรียน)
 * active=btn-primary / ที่เหลือ=btn-outline-primary
 * @var string $active คีย์เมนูที่กำลังเปิด
 */
$active = $active ?? '';

// ระดับโซน (มาตรฐานโรงพยาบาล / คลังกิจกรรม KM) → บล็อก page-action มุมขวาหัวข้อหน้า
// จัดวางแนวเดียวกับงาน HRD
$this->beginBlock('page-action');
echo $this->render('@app/modules/km/_zone_action', ['active' => 'qms']);
$this->endBlock();

$items = [
    ['key' => 'overview',   'label' => 'ภาพรวม',      'icon' => 'bi-speedometer2',           'url' => ['/qms/default/index']],
    ['key' => 'standards',  'label' => 'มาตรฐาน',     'icon' => 'bi-shield-check',           'url' => ['/qms/default/standards']],
    ['key' => 'indicators', 'label' => 'ตัวชี้วัด',    'icon' => 'bi-graph-up',               'url' => ['/qms/default/indicators']],
    ['key' => 'plans',      'label' => 'แผนงาน',      'icon' => 'bi-calendar2-check',        'url' => ['/qms/default/plans']],
    ['key' => 'evidence',   'label' => 'หลักฐาน',     'icon' => 'bi-folder2-open',           'url' => ['/qms/default/evidence']],
    ['key' => 'risk',       'label' => 'ความเสี่ยง',   'icon' => 'bi-exclamation-triangle',   'url' => ['/qms/default/risk']],
    ['key' => 'audit',      'label' => 'ตรวจประเมิน',  'icon' => 'bi-clipboard2-check',       'url' => ['/qms/default/audit']],
    ['key' => 'report',     'label' => 'รายงาน',      'icon' => 'bi-bar-chart-line',         'url' => ['/qms/default/report']],
];
?>
<nav class="qms-nav" aria-label="เมนู QMS">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <?php foreach ($items as $item): ?>
            <a href="<?= Url::to($item['url']) ?>"
               class="btn <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2"
               <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
                <i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i><span><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
