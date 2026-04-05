<?php
/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'บริการ';
$this->params['mobileSubtitle'] = 'เลือกบริการที่ต้องการใช้งาน';

$services = [
    [
        'icon'  => 'clock',
        'label' => 'ลงเวลาเข้า-ออก',
        'desc'  => 'บันทึกเวลาเข้างานและออกงาน',
        'url'   => \yii\helpers\Url::to(['/mobile/default/attendance']),
    ],
    [
        'icon'  => 'car',
        'label' => 'จองรถราชการ',
        'desc'  => 'จองรถสำหรับการเดินทางราชการ',
        'url'   => \yii\helpers\Url::to(['/mobile/default/booking-vehicle']),
    ],
    [
        'icon'  => 'calendar',
        'label' => 'จองห้องประชุม',
        'desc'  => 'ตรวจสอบและจองห้องประชุม',
        'url'   => \yii\helpers\Url::to(['/mobile/default/booking-meeting']),
    ],
    [
        'icon'  => 'user-check',
        'label' => 'ขอลาออนไลน์',
        'desc'  => 'ส่งคำขอลาประเภทต่างๆ',
        'url'   => \yii\helpers\Url::to(['/mobile/default/leave-request']),
    ],
    [
        'icon'  => 'wrench',
        'label' => 'แจ้งซ่อม',
        'desc'  => 'แจ้งซ่อมอุปกรณ์หรือสถานที่',
        'url'   => \yii\helpers\Url::to(['/mobile/default/maintenance-request']),
    ],
    [
        'icon'  => 'package',
        'label' => 'ขอเบิกพัสดุ',
        'desc'  => 'ขอเบิกวัสดุสำนักงาน',
        'url'   => '#',
    ],
    [
        'icon'  => 'qr-code',
        'label' => 'ตรวจสอบครุภัณฑ์',
        'desc'  => 'สแกนหรือตรวจสอบครุภัณฑ์',
        'url'   => \yii\helpers\Url::to(['/mobile/default/scan']),
    ],
    [
        'icon'  => 'file-text',
        'label' => 'เอกสารราชการ',
        'desc'  => 'ดูและติดตามเอกสาร',
        'url'   => '#',
    ],
    [
        'icon'  => 'alert-circle',
        'label' => 'แจ้งปัญหาระบบ',
        'desc'  => 'แจ้งปัญหาการใช้งานระบบ',
        'url'   => '#',
    ],
];
?>
<style>
.service-menu-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    text-align: center;
    text-decoration: none;
    color: inherit;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    background: #fff;
    padding: 1.25rem 0.75rem;
    min-height: 7.5rem;
    transition: box-shadow 0.2s ease, transform 0.15s ease;
}
.service-menu-card:hover { color: inherit; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
.service-menu-card:active { transform: scale(0.98); }
.service-menu-card .service-icon {
    width: 2.5rem;
    height: 2.5rem;
    margin-bottom: 0.5rem;
    flex-shrink: 0;
}
.service-menu-card .service-icon svg { stroke: var(--mobile-primary, #0d6efd); }
.service-menu-card .service-name { font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.25rem; line-height: 1.3; }
.service-menu-card .service-desc { font-size: 0.75rem; color: #6c757d; line-height: 1.35; margin: 0; }
</style>

<div class="row g-3">
    <?php foreach ($services as $s): ?>
        <div class="col-6">
            <a href="<?= \yii\helpers\Html::encode($s['url']) ?>" class="service-menu-card">
                <i data-lucide="<?= \yii\helpers\Html::encode($s['icon']) ?>" class="service-icon"></i>
                <span class="service-name"><?= \yii\helpers\Html::encode($s['label']) ?></span>
                <p class="service-desc"><?= \yii\helpers\Html::encode($s['desc']) ?></p>
            </a>
        </div>
    <?php endforeach; ?>
</div>
