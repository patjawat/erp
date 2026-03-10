<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'การแจ้งเตือน';
$this->params['mobileSubtitle'] = 'รายการแจ้งเตือนทั้งหมด';

$items = [
    [
        'icon' => 'check-circle',
        'iconColor' => 'success',
        'title' => 'คำขอลาของคุณได้รับการอนุมัติแล้ว',
        'desc' => 'จองห้องประชุม ห้อง A — 15 มี.ค. 2568',
        'time' => 'วันนี้ 09:30',
    ],
    [
        'icon' => 'clock',
        'iconColor' => 'warning',
        'title' => 'มีงานที่รอการอนุมัติจากคุณ',
        'desc' => 'คำขอลาของนายสมชาย ต้องการการอนุมัติ',
        'time' => 'วันนี้ 08:15',
    ],
    [
        'icon' => 'car',
        'iconColor' => 'info',
        'title' => 'การจองรถราชการของคุณได้รับการยืนยัน',
        'desc' => 'กก 1234 กรุงเทพ — 10 มี.ค. 2568 เวลา 08:00',
        'time' => 'เมื่อวาน 14:20',
    ],
    [
        'icon' => 'file-check',
        'iconColor' => 'success',
        'title' => 'คำขอลาของคุณได้รับการอนุมัติแล้ว',
        'desc' => 'ขอลาป่วย 3 วัน — 12–14 มี.ค. 2568',
        'time' => 'เมื่อวาน 11:00',
    ],
];
?>
<style>
.notif-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.notif-item { padding: 0.875rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; align-items: flex-start; gap: 0.75rem; text-decoration: none; color: inherit; }
.notif-item:last-child { border-bottom: 0; }
.notif-item:hover { color: inherit; background: rgba(0,0,0,0.02); }
.notif-item .notif-icon { width: 2.25rem; height: 2.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notif-item .notif-icon i { width: 1.125rem; height: 1.125rem; }
.notif-item .notif-icon.text-success { background: rgba(25, 135, 84, 0.12); }
.notif-item .notif-icon.text-warning { background: rgba(255, 193, 7, 0.2); }
.notif-item .notif-icon.text-info { background: rgba(13, 202, 240, 0.15); }
</style>

<div class="d-flex flex-column gap-3">
    <p class="small text-body-secondary mb-0">รายการแจ้งเตือนล่าสุด — ตัวอย่างการแสดงผล</p>

    <div class="card notif-card">
        <div class="card-body p-0">
            <?php foreach ($items as $item): ?>
                <a href="#" class="notif-item px-3">
                    <div class="notif-icon text-<?= $item['iconColor'] ?>">
                        <i data-lucide="<?= $item['icon'] ?>"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="fw-medium small d-block"><?= Html::encode($item['title']) ?></span>
                        <p class="mb-0 small text-body-secondary"><?= Html::encode($item['desc']) ?></p>
                        <span class="small text-body-secondary"><?= Html::encode($item['time']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="text-center py-2">
        <a href="<?= Html::encode(Url::to(['/mobile/default/index'])) ?>" class="btn btn-outline-primary" style="border-radius: 12px;">
            <i data-lucide="arrow-left" class="me-1" style="width: 1rem; height: 1rem; vertical-align: -0.2em;"></i>
            กลับหน้าหลัก
        </a>
    </div>
</div>
