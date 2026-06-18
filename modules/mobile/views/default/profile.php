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

// Build overlay (user card) — renders between hero and scroll body
ob_start();
?>
<div class="profile-overlay">
    <div class="card profile-card profile-user-card">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar" id="profile-avatar">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= Html::encode($avatarUrl) ?>" alt="" width="72" height="72" id="profile-avatar-img">
                    <?php else: ?>
                        <img src="" alt="" width="72" height="72" id="profile-avatar-img" style="display:none;">
                        <i data-lucide="user" style="width: 2.25rem; height: 2.25rem;" id="profile-avatar-placeholder"></i>
                    <?php endif; ?>
                </div>
                <button type="button" class="profile-avatar-edit" id="profile-avatar-edit" aria-label="เปลี่ยนรูปโปรไฟล์">
                    <i data-lucide="camera"></i>
                </button>
                <input type="file" id="profile-avatar-input" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
            </div>
            <div class="flex-grow-1 min-w-0">
                <h6 class="fw-semibold mb-1 text-truncate"><?= Html::encode($userName) ?></h6>
                <p class="small text-body-secondary mb-0 text-truncate">แผนก: <?= Html::encode($departmentName) ?></p>
                <p class="small text-body-secondary mb-0 text-truncate"><?= Html::encode($userEmail) ?></p>
            </div>
        </div>
    </div>
</div>
<?php
$overlayHtml = ob_get_clean();
?>
<style>
/* Full-bleed escape so the shared .app-shell can reach screen edges (matches news.php pattern). */
.profile-root {
    margin-left: -1rem; margin-right: -1rem;
    margin-top: -1rem;
    display: flex; flex-direction: column;
}
.profile-body { padding: 0 var(--space-md); display: flex; flex-direction: column; gap: var(--space-md); }

/* Overlay card sits over the hero curve like .app-stats. */
.profile-overlay {
    position: relative; z-index: 2;
    margin: -1.75rem var(--space-md) 0;
}
.profile-user-card { box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12), 0 2px 6px rgba(15, 23, 42, 0.04); }

.profile-card { border: 0; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.profile-avatar-wrap { position: relative; flex-shrink: 0; }
.profile-avatar { width: 4.5rem; height: 4.5rem; border-radius: 50%; background: rgba(13, 110, 253, 0.12); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-avatar i { color: var(--mobile-primary); }
.profile-avatar-edit {
    position: absolute; right: -2px; bottom: -2px;
    width: 1.75rem; height: 1.75rem; border-radius: 50%;
    background: var(--mobile-primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #fff; cursor: pointer;
    box-shadow: 0 2px 6px rgba(13,110,253,0.35);
    padding: 0;
}
.profile-avatar-edit:hover, .profile-avatar-edit:focus { background: var(--mobile-primary-dark); color: #fff; }
.profile-avatar-edit svg { width: 0.9rem; height: 0.9rem; }
.profile-avatar.is-uploading::after {
    content: ''; position: absolute; inset: 0; border-radius: 50%;
    background: rgba(255,255,255,0.6) url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%230d6efd' stroke-width='2.5' stroke-linecap='round'><circle cx='12' cy='12' r='9' opacity='.25'/><path d='M21 12a9 9 0 0 0-9-9'><animateTransform attributeName='transform' type='rotate' from='0 12 12' to='360 12 12' dur='0.8s' repeatCount='indefinite'/></path></svg>") center/40% no-repeat;
}
.profile-menu-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.875rem 1rem; text-decoration: none; color: inherit; border-bottom: 1px solid rgba(0,0,0,0.06); transition: background 0.15s ease; }
.profile-menu-item:last-child { border-bottom: 0; }
.profile-menu-item:hover { background: rgba(0,0,0,0.02); color: inherit; }
.profile-menu-item i:first-child { width: 1.25rem; height: 1.25rem; color: #6c757d; flex-shrink: 0; }
.profile-menu-item .chevron { width: 1rem; height: 1rem; color: #adb5bd; margin-left: auto; }
</style>

<div class="profile-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'        => 'user-round',
        'title'       => 'ส่วนตัว',
        'subtitle'    => 'โปรไฟล์และตั้งค่า',
        'overlayHtml' => $overlayHtml,
    ]) ?>

    <div class="app-scroll has-overlay">
        <div class="profile-body">

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
    </div>

</div>

<?php
$uploadUrl = Url::to(['/mobile/default/avatar-upload']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$js = <<<JS
(function () {
    const editBtn = document.getElementById('profile-avatar-edit');
    const input   = document.getElementById('profile-avatar-input');
    const avatar  = document.getElementById('profile-avatar');
    const imgEl   = document.getElementById('profile-avatar-img');
    const phEl    = document.getElementById('profile-avatar-placeholder');
    if (!editBtn || !input || !avatar) return;

    editBtn.addEventListener('click', function (e) {
        e.preventDefault();
        input.click();
    });

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;

        if (!/^image\\//.test(file.type)) {
            if (window.Swal) Swal.fire({icon:'error', title:'ไฟล์ไม่ถูกต้อง', text:'กรุณาเลือกไฟล์รูปภาพ'});
            input.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            if (window.Swal) Swal.fire({icon:'error', title:'ไฟล์ใหญ่เกินไป', text:'ขนาดไฟล์ต้องไม่เกิน 5MB'});
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (ev) {
            if (imgEl) {
                imgEl.src = ev.target.result;
                imgEl.style.display = '';
            }
            if (phEl) phEl.style.display = 'none';
        };
        reader.readAsDataURL(file);

        const fd = new FormData();
        fd.append('avatar', file);
        fd.append('{$csrfParam}', '{$csrfToken}');

        avatar.classList.add('is-uploading');

        fetch('{$uploadUrl}', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            avatar.classList.remove('is-uploading');
            if (resp && resp.status === 'success') {
                if (resp.avatar_url && imgEl) {
                    imgEl.src = resp.avatar_url;
                    imgEl.style.display = '';
                    if (phEl) phEl.style.display = 'none';
                }
                if (window.Swal) {
                    Swal.fire({icon:'success', title:resp.message || 'เปลี่ยนรูปโปรไฟล์เรียบร้อย', showConfirmButton:false, timer:1400});
                }
            } else {
                if (window.Swal) {
                    Swal.fire({icon:'error', title:'เกิดข้อผิดพลาด', text:(resp && resp.message) || 'อัปโหลดไม่สำเร็จ'});
                }
            }
        })
        .catch(function () {
            avatar.classList.remove('is-uploading');
            if (window.Swal) Swal.fire({icon:'error', title:'เครือข่ายขัดข้อง', text:'กรุณาลองใหม่อีกครั้ง'});
        })
        .finally(function () {
            input.value = '';
        });
    });
})();
JS;
$this->registerJs($js);
?>
