<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var bool $isRoomOwner */
$this->params['current_page']   = $current_page ?? 'profile';
$isRoomOwner = $isRoomOwner ?? false;
$this->params['mobileTitle']    = 'ส่วนตัว';
$this->params['mobileSubtitle'] = 'โปรไฟล์และตั้งค่า';

$userName = Yii::$app->user->isGuest ? 'ผู้ใช้ระบบ' : (Yii::$app->user->identity->username ?? 'ผู้ใช้ระบบ');
$userEmail = Yii::$app->user->isGuest ? '—' : (Yii::$app->user->identity->email ?? '—');
$avatarUrl = null;
$departmentName = 'งานบริหารทั่วไป';
if (!Yii::$app->user->isGuest && isset(Yii::$app->user->identity->employee) && Yii::$app->user->identity->employee) {
    try {
        $emp = Yii::$app->user->identity->employee;
        $avatarUrl = $emp->ShowAvatar();
        if (method_exists($emp, 'departmentName') && $emp->departmentName()) {
            $departmentName = $emp->departmentName();
        }
    } catch (\Throwable $e) {
        $avatarUrl = null;
    }
}
?>
<style>
.profile-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.profile-avatar { width: 4.5rem; height: 4.5rem; border-radius: 50%; background: rgba(13, 110, 253, 0.12); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-avatar i { color: var(--mobile-primary); }
.profile-menu-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; text-decoration: none; color: inherit; border-bottom: 1px solid rgba(0,0,0,0.06); transition: background 0.15s ease; }
.profile-menu-item:last-child { border-bottom: 0; }
.profile-menu-item:hover { background: rgba(0,0,0,0.02); color: inherit; }
.profile-menu-item i:first-child { width: 1.25rem; height: 1.25rem; color: #6c757d; flex-shrink: 0; }
.profile-menu-item .chevron { width: 1rem; height: 1rem; color: #adb5bd; margin-left: auto; }
</style>

<div class="d-flex flex-column gap-3">
    <!-- User card: Avatar, Name, Department, Contact -->
    <div class="card profile-card">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="profile-avatar flex-shrink-0">
                <?php if ($avatarUrl): ?>
                    <img src="<?= Html::encode($avatarUrl) ?>" alt="" width="72" height="72">
                <?php else: ?>
                    <i data-lucide="user" style="width: 2.25rem; height: 2.25rem;"></i>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1 min-w-0">
                <h6 class="fw-semibold mb-1 text-truncate"><?= Html::encode($userName) ?></h6>
                <p class="small text-body-secondary mb-0 text-truncate">แผนก: <?= Html::encode($departmentName) ?></p>
                <p class="small text-body-secondary mb-0 text-truncate"><?= Html::encode($userEmail) ?></p>
            </div>
            <a href="#" class="btn btn-outline-primary flex-shrink-0" style="border-radius: 12px;">แก้ไข</a>
        </div>
    </div>

    <!-- Menu: My Requests, Approval Tasks, Notifications, Settings -->
    <div class="card profile-card">
        <div class="card-body p-0">
            <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests'])) ?>" class="profile-menu-item">
                <i data-lucide="clipboard-list"></i>
                <span class="flex-grow-1">คำขอของฉัน</span>
                <i data-lucide="chevron-right" class="chevron"></i>
            </a>
            <a href="<?= Html::encode(Url::to(['/mobile/default/attendance'])) ?>" class="profile-menu-item">
                <i data-lucide="clock"></i>
                <span class="flex-grow-1">ลงเวลาเข้า-ออกงาน</span>
                <i data-lucide="chevron-right" class="chevron"></i>
            </a>
            <?php if ($isRoomOwner): ?>
            <a href="<?= Html::encode(Url::to(['/mobile/default/room-manage'])) ?>" class="profile-menu-item">
                <i data-lucide="layout-grid"></i>
                <span class="flex-grow-1">จัดการห้องประชุม</span>
                <i data-lucide="chevron-right" class="chevron"></i>
            </a>
            <?php endif; ?>
            <a href="#" class="profile-menu-item">
                <i data-lucide="check-square"></i>
                <span class="flex-grow-1">งานที่ต้องอนุมัติ</span>
                <i data-lucide="chevron-right" class="chevron"></i>
            </a>
            <a href="#" class="profile-menu-item">
                <i data-lucide="bell"></i>
                <span class="flex-grow-1">การแจ้งเตือน</span>
                <i data-lucide="chevron-right" class="chevron"></i>
            </a>
            <a href="#" class="profile-menu-item">
                <i data-lucide="settings"></i>
                <span class="flex-grow-1">ตั้งค่า</span>
                <i data-lucide="chevron-right" class="chevron"></i>
            </a>
        </div>
    </div>

    <div class="card profile-card">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i data-lucide="info" style="width: 1.25rem; height: 1.25rem; color: var(--mobile-primary);"></i>
                <h6 class="card-title fw-semibold mb-0">เกี่ยวกับแอป</h6>
            </div>
            <p class="small text-body-secondary mb-0">บริการออนไลน์ v1.0 — แอปใช้งานภายในหน่วยงาน</p>
        </div>
    </div>

    <div class="mb-2">
        <?= Html::beginForm(['/mobile/auth/logout'], 'post', ['class' => 'mb-0']) ?>
        <button type="submit" class="btn btn-outline-danger w-100" style="border-radius: 12px; padding: 0.75rem;">
            <i data-lucide="log-out" style="width: 1.1rem; height: 1.1rem; vertical-align: -0.2em;"></i>
            ออกจากระบบ
        </button>
        <?= Html::endForm() ?>
    </div>
</div>
