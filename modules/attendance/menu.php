<?php
use yii\helpers\Url;

// เมนูผู้ดูแลระบบลงเวลา (admin/hr/attendance) — หน้าของผู้ใช้ทั่วไป (ลงเวลา/ประวัติของฉัน) อยู่ใต้ /me ไม่ใช้เมนูนี้
$items = [
    'dashboard' => [['/attendance/default/index'], 'bi-speedometer2', 'ภาพรวม'],
    'confirm' => [['/attendance/checkin/confirm'], 'bi-check2-square', 'ตรวจสอบลงเวลา'],
    'report' => [['/attendance/checkin/report'], 'bi-people', 'ทั้งหน่วยงาน'],
    'monthly' => [['/attendance/checkin/monthly'], 'bi-calendar3', 'สรุปรายเดือน'],
    'location' => [['/attendance/location/index'], 'bi-geo-alt', 'จุดลงเวลา'],
    'schedule' => [['/attendance/schedule/index'], 'bi-calendar-week', 'ตั้งค่าเวลาทำงาน'],
    'import' => [['/attendance/checkin/import-form'], 'bi-upload', 'นำเข้า CSV'],
];
?>
<div class="d-flex flex-wrap gap-2">
    <?php foreach ($items as $key => [$url, $icon, $label]): $on = ($active ?? '') === $key; ?>
    <a href="<?= Url::to($url) ?>" class="btn <?= $on ? 'btn-primary' : 'btn-outline-primary' ?>"<?= $on ? ' aria-current="page"' : '' ?>>
        <i class="bi <?= $icon ?>" aria-hidden="true"></i> <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>
