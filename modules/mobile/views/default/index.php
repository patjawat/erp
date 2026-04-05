<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'home';
$this->params['mobileTitle']    = 'บริการออนไลน์';
$this->params['mobileSubtitle'] = 'หน้าหลัก';

$userName = Yii::$app->user->isGuest ? 'ผู้ใช้' : (Yii::$app->user->identity->username ?? 'ผู้ใช้');
$avatarUrl = null;
if (!Yii::$app->user->isGuest && isset(Yii::$app->user->identity->employee) && Yii::$app->user->identity->employee) {
    try {
        $avatarUrl = Yii::$app->user->identity->employee->ShowAvatar();
    } catch (\Throwable $e) {
        $avatarUrl = null;
    }
}
?>
<style>
.home-greeting-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.home-greeting-avatar { width: 3rem; height: 3rem; border-radius: 50%; background: rgba(13, 110, 253, 0.12); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.home-greeting-avatar img { width: 100%; height: 100%; object-fit: cover; }
.home-greeting-avatar i { color: var(--mobile-primary); }
.home-quick-action { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem 0.5rem; min-height: 5rem; transition: box-shadow 0.2s ease, transform 0.15s ease; background: #fff; }
.home-quick-action:hover { color: inherit; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.home-quick-action:active { transform: scale(0.98); }
.home-quick-action .qa-icon { width: 2.25rem; height: 2.25rem; margin-bottom: 0.35rem; color: var(--mobile-primary); }
.home-section-title { font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.75rem; }
.home-request-item { border-radius: 12px; padding: 0.65rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.home-request-item:last-child { border-bottom: 0; }
</style>

<div class="d-flex flex-column gap-3">
    <!-- Greeting + Avatar -->
    <div class="card home-greeting-card">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="home-greeting-avatar flex-shrink-0">
                <?php if ($avatarUrl): ?>
                    <img src="<?= Html::encode($avatarUrl) ?>" alt="" width="48" height="48">
                <?php else: ?>
                    <i data-lucide="user" style="width: 1.75rem; height: 1.75rem;"></i>
                <?php endif; ?>
            </div>
            <div class="min-w-0 flex-grow-1">
                <p class="mb-0 small text-body-secondary">สวัสดี</p>
                <h2 class="h5 fw-semibold mb-0 text-truncate"><?= \yii\bootstrap5\Html::encode($userName) ?></h2>
            </div>
        </div>
    </div>

    <!-- Quick Actions: จองรถ, จองห้องประชุม, แจ้งซ่อม, ขอลา (ซ้าย→ขวา บน→ล่าง) -->
    <div>
        <h3 class="home-section-title d-flex align-items-center gap-2">
            <i data-lucide="zap" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
            บริการด่วน
        </h3>
        <div class="row g-2">
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/booking-vehicle'])) ?>" class="card home-quick-action">
                    <i data-lucide="car" class="qa-icon"></i>
                    <span class="fw-semibold small">จองรถ</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/booking-meeting'])) ?>" class="card home-quick-action">
                    <i data-lucide="calendar" class="qa-icon"></i>
                    <span class="fw-semibold small">จองห้องประชุม</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/maintenance-request'])) ?>" class="card home-quick-action">
                    <i data-lucide="wrench" class="qa-icon"></i>
                    <span class="fw-semibold small">แจ้งซ่อม</span>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Html::encode(Url::to(['/mobile/default/leave-request'])) ?>" class="card home-quick-action">
                    <i data-lucide="calendar-off" class="qa-icon"></i>
                    <span class="fw-semibold small">ขอลา</span>
                </a>
            </div>
            <div class="col-12">
                <a href="<?= Html::encode(Url::to(['/mobile/default/attendance'])) ?>" class="card home-quick-action flex-row justify-content-center gap-2 py-3">
                    <i data-lucide="clock" class="qa-icon mb-0"></i>
                    <span class="fw-semibold small">ลงเวลาเข้า-ออกงาน</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card mobile-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="home-section-title mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="bell" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
                    การแจ้งเตือน
                </h3>
                <a href="<?= Html::encode(Url::to(['/mobile/default/notifications'])) ?>" class="small text-primary text-decoration-none">ดูทั้งหมด</a>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="<?= Html::encode(Url::to(['/mobile/default/notifications'])) ?>" class="d-flex align-items-start gap-2 py-1 text-decoration-none text-dark">
                    <i data-lucide="check-circle" class="text-success flex-shrink-0 mt-1" style="width: 1.125rem; height: 1.125rem;"></i>
                    <div class="min-w-0">
                        <span class="fw-medium small">คำขอลาของคุณได้รับการอนุมัติแล้ว</span>
                        <p class="mb-0 small text-body-secondary">จองห้องประชุม ห้อง A — 15 มี.ค. 2568</p>
                    </div>
                </a>
                <a href="<?= Html::encode(Url::to(['/mobile/default/notifications'])) ?>" class="d-flex align-items-start gap-2 py-1 text-decoration-none text-dark">
                    <i data-lucide="clock" class="text-warning flex-shrink-0 mt-1" style="width: 1.125rem; height: 1.125rem;"></i>
                    <div class="min-w-0">
                        <span class="fw-medium small">มีงานที่รอการอนุมัติจากคุณ</span>
                        <p class="mb-0 small text-body-secondary">คำขอลาของนายสมชาย ต้องการการอนุมัติ</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="card mobile-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="home-section-title mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="megaphone" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
                    ข่าวสารและประกาศ
                </h3>
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="small text-primary text-decoration-none">ดูทั้งหมด</a>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="border-start border-3 border-primary ps-2 py-1 text-decoration-none text-dark">
                    <span class="fw-medium small">ระบบขอลาออนไลน์เปิดให้บริการแล้ว</span>
                    <p class="mb-0 small text-body-secondary">ส่งคำขอลาและติดตามสถานะได้ผ่านแอป</p>
                </a>
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="border-start border-3 border-primary ps-2 py-1 text-decoration-none text-dark">
                    <span class="fw-medium small">ประชุมใหญ่ประจำปี 2568</span>
                    <p class="mb-0 small text-body-secondary">ขอเชิญพนักงานเข้าร่วมประชุมใหญ่ วันศุกร์ที่ 15 มี.ค. 2568 ณ ห้องประชุมใหญ่ ชั้น 3</p>
                </a>
                <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="border-start border-3 border-primary ps-2 py-1 text-decoration-none text-dark">
                    <span class="fw-medium small">ปรับปรุงหน้าจองห้องประชุม</span>
                    <p class="mb-0 small text-body-secondary">เพิ่มการแสดงผลปฏิทินและห้องว่างแบบรายชั่วโมง ให้จองได้สะดวกขึ้น</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity / Recent requests -->
    <div class="card mobile-card mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 class="home-section-title mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="history" style="width: 1.125rem; height: 1.125rem; color: var(--mobile-primary);"></i>
                    คำขอล่าสุด
                </h3>
                <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests'])) ?>" class="small text-primary text-decoration-none">ดูทั้งหมด</a>
            </div>
            <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests', 'type' => 'vehicle'])) ?>" class="home-request-item d-flex align-items-center gap-2 text-decoration-none text-dark">
                <i data-lucide="car" class="text-body-secondary" style="width: 1.25rem; height: 1.25rem;"></i>
                <div class="flex-grow-1 min-w-0">
                    <span class="small fw-medium">จองรถราชการ</span>
                    <p class="mb-0 small text-body-secondary">รออนุมัติ · 10 มี.ค. 2568</p>
                </div>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">รอดำเนินการ</span>
            </a>
            <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests', 'type' => 'meeting'])) ?>" class="home-request-item d-flex align-items-center gap-2 text-decoration-none text-dark">
                <i data-lucide="calendar" class="text-body-secondary" style="width: 1.25rem; height: 1.25rem;"></i>
                <div class="flex-grow-1 min-w-0">
                    <span class="small fw-medium">จองห้องประชุม</span>
                    <p class="mb-0 small text-body-secondary">ห้อง A · 15 มี.ค. 2568</p>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">อนุมัติแล้ว</span>
            </a>
            <p class="small text-body-secondary mb-0 mt-1">ติดตามสถานะคำขอล่าสุดของคุณได้ที่นี่</p>
        </div>
    </div>
</div>
