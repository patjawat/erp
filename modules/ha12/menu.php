<?php

use yii\helpers\Url;

/**
 * เมนู HA12-PCT (จัดวางแนวเดียวกับ KM/Complaint)
 *
 * - ระดับโซน "งานคุณภาพ" → บล็อก page-action มุมขวาหัวข้อหน้า (ใช้ _zone_action ร่วมกัน)
 * - เมนูย่อย → ปุ่ม btn-outline-primary / active=btn-primary
 *
 * @var string $active คีย์เมนูย่อยที่กำลังเปิด (overview | review | med | round | report)
 */
$active = $active ?? '';

// ระดับโซน → page-action (มุมขวาหัวข้อหน้า)
$this->beginBlock('page-action');
echo $this->render('@app/modules/km/_zone_action', ['active' => 'ha12']);
$this->endBlock();

$items = [
    ['key' => 'overview', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/ha12/default/index']],
    ['key' => 'review', 'label' => 'การทบทวน', 'icon' => 'bi-journal-check', 'url' => ['/ha12/review/index']],
    ['key' => 'med', 'label' => 'ความคลาดเคลื่อนยา', 'icon' => 'bi-capsule', 'url' => ['/ha12/med/index']],
    ['key' => 'mrec', 'label' => 'เวชระเบียน', 'icon' => 'bi-journal-medical', 'url' => ['/ha12/mrec/index']],
    ['key' => 'indicator', 'label' => 'ตัวชี้วัด', 'icon' => 'bi-graph-up', 'url' => ['/ha12/indicator/index']],
    ['key' => 'round', 'label' => 'รอบประเมิน PCT', 'icon' => 'bi-clipboard2-data', 'url' => ['/ha12/round/index']],
    ['key' => 'report', 'label' => 'รายงาน', 'icon' => 'bi-file-earmark-bar-graph', 'url' => ['/ha12/report/index']],
];
?>
<nav class="ha12-subnav d-flex flex-wrap align-items-center gap-2" aria-label="เมนู HA12-PCT">
    <?php foreach ($items as $item): ?>
        <a href="<?= Url::to($item['url']) ?>"
           class="btn <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2"
           <?= $active === $item['key'] ? 'aria-current="page"' : '' ?>>
            <i class="bi <?= $item['icon'] ?>" aria-hidden="true"></i><span><?= $item['label'] ?></span>
        </a>
    <?php endforeach; ?>
</nav>
