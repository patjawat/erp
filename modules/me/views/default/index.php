<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\UserHelper;
use app\components\ApproveHelper;
use app\components\ThaiDateHelper;

$me = UserHelper::GetEmployee();
$notify = ApproveHelper::Info();
$canViewExecutiveDashboard = Yii::$app->user->can('executiveDashboardView')
    || Yii::$app->user->can('financeView')
    || Yii::$app->user->can('admin');

$this->registerCss(<<<'CSS'
.appreciation-action {
    color: #475569;
    font-weight: 600;
    text-decoration: none;
    transition: color 180ms ease, transform 180ms ease;
}
.appreciation-action:hover,
.appreciation-action:focus-visible {
    color: #1d4ed8;
    transform: translateY(-2px);
}
.appreciation-action__icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .5rem;
    border: 1px solid rgba(148, 163, 184, .12);
    box-shadow: 0 8px 20px rgba(51, 65, 85, .16);
    font-size: 1.25rem;
}
.appreciation-action--thanks .appreciation-action__icon {
    color: #9b3f36;
    background: linear-gradient(145deg, #ffe4dc, #f6b9a8);
}
.appreciation-action--reward .appreciation-action__icon {
    color: #75500d;
    background: linear-gradient(145deg, #fff1c9, #ebcb72);
}
.appreciation-action--activity .appreciation-action__icon {
    color: #315d3b;
    background: linear-gradient(145deg, #e0f1dc, #a9d1a9);
}
.appreciation-action .d-block.small {
    font-size: .9rem;
}
.appreciation-status-card .small {
    font-size: .86rem;
}
.appreciation-feed-link {
    color: #64748b;
    font-size: .88rem;
    font-weight: 600;
    text-decoration: none;
}
.appreciation-feed-link:hover { color: #2563eb; }
.appreciation-metric {
    text-align: center;
}
.appreciation-metric + .appreciation-metric {
    border-left: 1px solid #e2e8f0;
}
.appreciation-heart {
    width: 42px;
    height: 42px;
    color: #fff;
    background: linear-gradient(145deg, #fb7185, #e83e5b);
    box-shadow: 0 7px 16px rgba(232, 62, 91, .25);
}
.appreciation-growth {
    position: relative;
    text-align: center;
    padding: .15rem 0 .2rem;
}
.appreciation-growth__plant {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin-bottom: .25rem;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, #fff9d9 0 18%, #f9d98c 55%, #efb85b 100%);
    box-shadow: 0 8px 18px rgba(183, 126, 38, .2);
    font-size: 1.55rem;
}
.appreciation-growth .progress {
    background: #edf0e7;
}
.appreciation-growth .progress-bar {
    background: linear-gradient(90deg, #ef9f62, #e4bd4f 52%, #79b879);
}
.erp-quick-services {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    grid-template-rows: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.erp-quick-service {
    min-width: 0;
}
.erp-quick-service > a > div {
    justify-content: center !important;
    align-items: center;
    text-align: center;
    gap: .65rem;
}
.erp-quick-service > a > div > .bg-primary-subtle {
    width: 60px !important;
    height: 60px !important;
    margin-bottom: 0 !important;
    border-radius: 16px !important;
}
.erp-quick-service > a > div > .bg-primary-subtle svg {
    width: 29px;
    height: 29px;
}
.erp-quick-service .text-xs {
    width: 100%;
    color: var(--bs-body-color) !important;
    font-size: 1rem !important;
    line-height: 1.35;
    text-align: center;
}
.erp-profile-panel {
    gap: 1.5rem;
}
@media (min-width: 1200px) {
    .erp-dashboard-primary-row {
        min-height: 470px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .appreciation-action { transition: none; }
}
CSS);

// งานมอบหมายที่ยังไม่ปิด — ป้องกันหน้าพังในเครื่องที่ยังไม่ได้รัน migration ของโมดูล task
$myOpenTaskCount = 0;
try {
    if (Yii::$app->db->getTableSchema('{{%task}}') !== null) {
        $myOpenTaskCount = (int) \app\modules\task\models\Task::find()
            ->where(['assignee_emp_id' => $me->id])
            ->andWhere(['status' => \app\modules\task\models\Task::OPEN_STATUSES])
            ->count();
    }
} catch (\Throwable $e) {
    $myOpenTaskCount = 0;
}

$pendingApproveItems = [
    [
        'key' => 'task',
        'label' => 'งานมอบหมาย',
        'url' => ['/task'],
        'icon' => 'fa-list-check',
        'count' => $myOpenTaskCount,
    ],
    [
        'key' => 'leave',
        'label' => 'การลา',
        'url' => ['/approve-v2/leave'],
        'icon' => 'fa-calendar-heart',
        'count' => (int) ($notify['leave']['total'] ?? 0),
    ],
    [
        'key' => 'booking_car',
        'label' => 'ขอใช้รถ',
        'url' => ['/approve-v2/vehicle'],
        'icon' => 'fa-car-side',
        'count' => (int) ($notify['booking_car']['total'] ?? 0),
    ],
    [
        'key' => 'purchase',
        'label' => 'จัดซื้อจัดจ้าง',
        'url' => ['/approve-v2/purchase'],
        'icon' => 'fa-cart-shopping',
        'count' => (int) ($notify['purchase']['total'] ?? 0),
    ],
    [
        'key' => 'development',
        'label' => 'อบรม/ดูงาน',
        'url' => ['/approve-v2/development'],
        'icon' => 'fa-graduation-cap',
        'count' => (int) ($notify['development']['total'] ?? 0),
    ],
    [
        'key' => 'requisitionV2',
        'label' => 'ขออนุมัติเบิกวัสดุ',
        'url' => ['/approve-v2/main-stock/requisition-v2'],
        'icon' => 'fa-clipboard-check',
        'count' => (int) ($notify['requisitionV2']['total'] ?? 0),
    ],
    [
        'key' => 'assetMove',
        'label' => 'โอนพัสดุ',
        'url' => ['/approve-v2/asset-move'],
        'icon' => 'fa-truck-ramp-box',
        'count' => (int) ($notify['assetMove']['total'] ?? 0),
    ],
];

$pendingApproveCount = 0;
foreach ($pendingApproveItems as $pendingApproveItem) {
    $pendingApproveCount += (int) ($pendingApproveItem['count'] ?? 0);
}

$this->title = 'ภาพรวมของ' . $me->fullname();
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => $me->fullname(), 'url' => ['/me']];
?>

<?php $this->beginBlock('page-title'); ?>
<div
    class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/me/menu', ['active' => 'dashboard', 'notify' => $notify]) ?>

<?php $this->endBlock(); ?>

<div class="container-fluid px-3 px-md-4">
<?php
$upcomingHealth = $me->getUpcomingHealthAppointments();
if (!empty($upcomingHealth)): ?>
<div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 d-flex align-items-start gap-3" role="alert">
    <div class="bg-info bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
    </div>
    <div class="flex-grow-1">
        <h6 class="alert-heading fw-bold mb-2">การนัดตรวจสุขภาพ</h6>
        <p class="mb-2 small">คุณมีการนัดหมายตรวจสุขภาพดังนี้:</p>
        <ul class="mb-0 ps-3">
            <?php foreach ($upcomingHealth as $app): ?>
            <li class="mb-1">
                <a href="<?= Url::to(['/me/health/view', 'id' => $app->id]) ?>" class="open-modal text-decoration-none fw-medium" data-size="modal-xl">
                    วันที่ <?= ThaiDateHelper::formatThaiDate($app->appointment_date, 'medium') ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <a href="<?= Url::to(['/me/health/view', 'id' => $upcomingHealth[0]->id]) ?>" class="btn btn-sm btn-info mt-2 open-modal" data-size="modal-xl">ดูรายละเอียด</a>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 erp-dashboard-primary-row">
    <div class="col-12 col-xl-6">
        <div class="position-relative p-4 text-white overflow-hidden h-100 d-flex flex-column rounded-3 erp-profile-panel">

            <div class="d-flex flex-column flex-md-row align-items-center gap-4 position-relative z-1">

                <div class="position-relative group" style="cursor: pointer;" onclick="document.getElementById('avatar-upload').click();">
                    <div class="position-absolute top-0 start-0 translate-middle p-1 rounded-3 shadow-lg border border-2 border-white"
                        style="background: linear-gradient(to top right, #fbbf24, #fef08a); transform: rotate(-12deg) !important; z-index: 10;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="trophy" style="color: #92400e;" class="lucide lucide-trophy">
                            <path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"></path>
                            <path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"></path>
                            <path d="M18 9h1.5a1 1 0 0 0 0-5H18"></path>
                            <path d="M4 22h16"></path>
                            <path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"></path>
                            <path d="M6 9H4.5a1 1 0 0 1 0-5H6"></path>
                        </svg>
                    </div>

                    <div class="position-relative overflow-hidden rounded-5 shadow-lg border border-4 border-white border-opacity-25" style="width: 128px; height: 128px;">
                        <?= Html::img($me->showAvatar(), [
                            "id" => "avatar-preview",
                            "class" => "object-fit-cover w-100 h-100 transition-all",
                        ]) ?>
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-black bg-opacity-40 opacity-0 hover-opacity-100 transition-all shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera">
                                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                                <circle cx="12" cy="13" r="3" />
                            </svg>
                        </div>
                    </div>

                    <input type="file" id="avatar-upload" class="d-none" accept="image/*" onchange="previewImage(this)">

                    <div class="position-absolute bottom-0 end-0 bg-success border border-4 border-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px; border-color: #1e40af !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            data-lucide="check-circle" class="lucide lucide-check-circle text-white">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg>
                    </div>
                </div>

                <div class="flex-grow-1 text-center text-md-start">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-3 mb-2">
                        <h2 class="fw-black m-0 tracking-tight text-white" style="font-size: 1.875rem;">
                            <?= $me->fullname ?></h2>
                        <div class="d-none"
                            style="background: linear-gradient(to right, #f59e0b, #fb923c);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" data-lucide="star"
                                class="lucide lucide-star text-white fill-white">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-white text-opacity-75 text-sm fw-medium mb-1"><?= $me->positionName() ?></p>
                        <p class="text-white fw-bold text-sm mb-0">ระดับ: <?= Html::encode($appreciationStatus['levelName'] ?? 'เริ่มต้น') ?></p>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-4 bg-white bg-opacity-10 text-white text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" class="lucide lucide-map-pin">
                                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg><span><?= $me->departmentName() ?></span>
                        </div>
                        <div class="d-none">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" style="color: #fca5a5; fill: #fca5a5;" class="lucide lucide-heart">
                                <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                            </svg><span>ได้รับคำขอบคุณแล้ว: <span class="fw-black"><?= (int) ($appreciationReceivedCount ?? 0) ?> ครั้ง</span></span>
                        </div>
                    </div>
                </div>

                <?php $todayCheckinCount = isset($todayCheckinCount) ? (int)$todayCheckinCount : 0; ?>
                <div class="d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-10 border border-white border-opacity-10 p-4 position-relative rounded-4"
                    style="min-width: 180px; backdrop-filter: blur(12px);">
                    <p class="text-white text-opacity-75 mb-2 d-flex align-items-center gap-2 fw-bold"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock">
                            <path d="M12 6v6l4 2"></path>
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg> ลงเวลาเข้า-ออก</p>
                    <span id="current-time" class="text-white fw-black mb-2 lh-1 d-block" style="font-size: 2.75rem; letter-spacing: 0.02em;">00:00:00</span>
                    <?php if ($todayCheckinCount > 0): ?>
                    <p class="text-white text-opacity-90 small mb-3">ลงเวลาแล้ว <span id="today-checkin-count" class="fw-bold"><?= $todayCheckinCount ?></span> ครั้งวันนี้</p>
                    <?php else: ?>
                    <p class="text-white text-opacity-75 small mb-3"><span id="today-checkin-count" class="d-none">0</span>ยังไม่ได้ลงเวลาวันนี้</p>
                    <?php endif; ?>
                    <a id="btn-clock-in" href="<?= Url::to(['/attendance/default/checkin-modal']) ?>" data-size="modal-md" class="open-modal btn bg-white w-100 py-2 fw-black border-0 shadow-lg d-flex align-items-center justify-content-center gap-2 hover-scale position-relative z-1 text-decoration-none" style="color: #2563eb; border-radius: 16px; font-size: 0.875rem;">ลงเวลา <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" class="lucide lucide-arrow-up-right">
                            <path d="M7 7h10v10"></path>
                            <path d="M7 17 17 7"></path>
                        </svg></a>
                    <a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="text-white text-opacity-90 small text-decoration-none mt-2 d-block">ประวัติ</a>
                </div>
            </div>

            <div class="erp-profile-tools position-relative z-1">
            <?php
            $serviceProfileTarget = $serviceProfileCurrent ?? $serviceProfileDraft ?? null;
            $serviceProfileUrl = ($serviceProfileActionCount ?? 0) > 0
                ? ['/service-profile/default/index', 'scope' => 'action']
                : ($serviceProfileTarget
                ? ['/service-profile/default/view', 'id' => $serviceProfileTarget->id]
                : ['/service-profile/default/index']);
            ?>
            <nav class="department-management-entry" aria-label="การบริหารหน่วยงาน">
                <div class="department-management-entry__panel">
                    <div class="department-management-entry__actions">
                        <?php if ($canViewExecutiveDashboard): ?>
                        <a href="<?= Url::to(['/executive/dashboard']) ?>" class="department-management-action department-management-action--executive" title="Dashboard ข้อมูลผู้บริหาร">
                            <span class="department-management-action__icon"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></span>
                            <span>ผู้บริหาร</span>
                        </a>
                        <?php endif; ?>
                        <a href="<?= Url::to($serviceProfileUrl) ?>" class="department-management-action department-management-action--profile">
                            <span class="department-management-action__icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
                            <span>Service Profile</span>
                            <?php if (($serviceProfileActionCount ?? 0) > 0): ?>
                                <span class="badge text-bg-danger"><?= (int)$serviceProfileActionCount ?></span>
                            <?php endif; ?>
                            <?php if ($serviceProfileDraft && !$serviceProfileCurrent): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis">กำลังจัดทำ</span>
                            <?php endif; ?>
                        </a>
                        <button type="button" class="department-management-action department-management-action--plan" disabled aria-disabled="true">
                            <span class="department-management-action__icon"><i class="bi bi-list-check" aria-hidden="true"></i></span>
                            <span>Action Plan</span>
                        </button>
                        <a href="<?= Url::to(['/iac-risk/default/index']) ?>" class="department-management-action department-management-action--risk" title="การควบคุมภายในและการบริหารความเสี่ยง">
                            <span class="department-management-action__icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
                            <span>IAC&amp;Risk</span>
                        </a>
                    </div>
                </div>
            </nav>
            </div>
        </div>

        <style>
            .transition-all {
                transition: all 0.3s ease;
            }

            .hover-opacity-100:hover {
                opacity: 1 !important;
            }

            .erp-profile-panel {
                background:
                    radial-gradient(circle at 85% 12%, rgb(255 255 255 / 14%), transparent 28%),
                    linear-gradient(145deg, #2149b7 0%, #2f70dc 100%);
            }

            .erp-profile-tools {
                margin-top: auto;
                overflow: hidden;
                border: 1px solid rgb(255 255 255 / 24%);
                border-radius: 1rem;
                background: rgb(96 165 250 / 22%);
                box-shadow: inset 0 1px 0 rgb(255 255 255 / 8%);
            }

            .department-management-entry__panel {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: .75rem 1rem;
            }

            .department-management-entry__actions {
                display: grid;
                flex: 1 1 auto;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: .5rem;
            }

            .department-management-action {
                min-width: 0;
                min-height: 5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: .375rem;
                padding: .625rem .5rem;
                border: 1px solid transparent;
                border-radius: .75rem;
                font-weight: 700;
                line-height: 1.2;
                text-align: center;
                text-decoration: none;
                box-shadow: none;
                transition: transform 180ms ease-out, box-shadow 180ms ease-out;
            }

            .department-management-action--profile {
                color: #1246a0;
                background: #fff;
                border-color: #fff;
            }

            .department-management-action--executive {
                color: #123d7a;
                background: #cfe2ff;
                border-color: #b9d5ff;
            }

            .department-management-action--plan {
                color: #17458d;
                background: #e5efff;
                border-color: #d5e5ff;
            }

            .department-management-action--risk {
                color: #17458d;
                background: #dbeafe;
                border-color: #c6dcff;
            }

            .department-management-action__icon {
                width: 2.25rem;
                height: 2.25rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 auto;
                border-radius: 50%;
                background: transparent;
                font-size: 1.5rem;
            }

            .department-management-action__icon i {
                color: currentColor;
            }

            a.department-management-action:hover,
            a.department-management-action:focus-visible {
                color: #0d3b8b;
                transform: translateY(-2px);
                box-shadow: 0 .5rem 1rem rgb(4 25 70 / 24%);
            }

            .department-management-action:focus-visible {
                outline: 3px solid #fff;
                outline-offset: 3px;
            }

            .department-management-action:disabled {
                opacity: 1;
                cursor: not-allowed;
                box-shadow: none;
            }

            @media (max-width: 767.98px) {
                .department-management-entry__panel {
                    align-items: stretch;
                    flex-direction: column;
                }

                .department-management-entry__actions {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .department-management-action {
                    width: 100%;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .department-management-action {
                    transition: none;
                }
            }

            .group:hover #avatar-preview {
                transform: scale(1.1);
                filter: blur(2px);
            }
        </style>

        <script>
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('avatar-preview').src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                    // คุณสามารถเพิ่ม Code AJAX เพื่อส่งรูปไปบันทึกที่ Server ตรงนี้ได้เลย
                }
            }
        </script>
    </div>

    <div class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm h-100 appreciation-status-card">
            <div class="card-body p-3 p-md-4 d-flex flex-column">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="appreciation-heart d-inline-flex align-items-center justify-content-center rounded-circle"><i class="bi bi-heart-fill" aria-hidden="true"></i></span>
                            <div><h2 class="h6 fw-bold mb-0">สถานะคำขอบคุณ</h2><div class="small text-muted"><?= !empty($appreciationStatus['year']) ? Html::encode($appreciationStatus['year']->name) : 'ยังไม่เปิดรอบคะแนน' ?></div></div>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="small text-muted">ระดับ</div>
                        <div class="fw-bold lh-sm" style="font-size:1.35rem;color:<?= Html::encode($appreciationStatus['levelColor'] ?? '#2563eb') ?>"><?= Html::encode($appreciationStatus['levelName'] ?? 'เริ่มต้น') ?></div>
                    </div>
                </div>
                <div class="appreciation-growth mb-2">
                    <span class="appreciation-growth__plant" aria-hidden="true">🌱</span>
                    <div class="small fw-semibold text-dark mb-1"><?= !empty($appreciationStatus['nextLevelName']) ? 'กำลังเติบโตสู่ '.Html::encode($appreciationStatus['nextLevelName']) : 'เติบโตถึงระดับปัจจุบันแล้ว' ?></div>
                    <div class="d-flex justify-content-between gap-2 small mb-1"><span class="text-muted">เส้นทางของคุณ</span><span class="fw-bold text-success"><?= (int)($appreciationStatus['progress'] ?? 0) ?>%</span></div>
                    <div class="progress rounded-pill" style="height:8px"><div class="progress-bar rounded-pill" role="progressbar" style="width:<?= (int)($appreciationStatus['progress'] ?? 0) ?>%" aria-valuenow="<?= (int)($appreciationStatus['progress'] ?? 0) ?>" aria-valuemin="0" aria-valuemax="100"></div></div>
                    <?php if(!empty($appreciationStatus['nextLevelName'])): ?><div class="small text-muted mt-1">อีก <?= number_format($appreciationStatus['pointsToNext']) ?> คะแนน ต้นอ่อนจะเติบโตขึ้น</div><?php endif; ?>
                </div>
                <dl class="row g-0 mb-3 small py-2">
                    <div class="col-4 appreciation-metric px-2"><dt class="text-muted fw-normal"><i class="bi bi-star-fill text-warning me-1" aria-hidden="true"></i>สะสม</dt><dd class="fw-bold fs-6 mb-0 mt-1"><?= number_format($appreciationStatus['earned'] ?? 0) ?></dd></div>
                    <div class="col-4 appreciation-metric px-3"><dt class="text-muted fw-normal"><i class="bi bi-wallet2 text-primary me-1" aria-hidden="true"></i>คงเหลือ</dt><dd class="fw-bold fs-6 text-primary mb-0 mt-1"><?= number_format($appreciationStatus['balance'] ?? 0) ?></dd></div>
                    <div class="col-4 appreciation-metric px-3"><dt class="text-muted fw-normal"><i class="bi bi-gift-fill text-success me-1" aria-hidden="true"></i>รางวัล</dt><dd class="fw-bold fs-6 mb-0 mt-1"><?= number_format($appreciationStatus['rewardsCount'] ?? 0) ?></dd></div>
                </dl>
                <div class="row g-0 text-center border-top pt-3 pb-1">
                    <div class="col-4"><?= Html::a('<span class="appreciation-action__icon"><i class="bi bi-heart-fill"></i></span><span class="d-block small">ส่งคำขอบคุณ</span>', ['/appreciation/default/create'], ['class'=>'appreciation-action appreciation-action--thanks d-inline-flex flex-column align-items-center open-modal','data'=>['size'=>'modal-lg']]) ?></div>
                    <div class="col-4"><?= Html::a('<span class="appreciation-action__icon"><i class="bi bi-gift"></i></span><span class="d-block small">แลกของ</span>', ['/appreciation/reward/index'], ['class'=>'appreciation-action appreciation-action--reward d-inline-flex flex-column align-items-center']) ?></div>
                    <div class="col-4"><?= Html::a('<span class="appreciation-action__icon"><i class="bi bi-calendar-check"></i></span><span class="d-block small">ร่วมกิจกรรม</span>', ['/appreciation/activity/index'], ['class'=>'appreciation-action appreciation-action--activity d-inline-flex flex-column align-items-center']) ?></div>
                </div>
                <div class="text-center mt-3">
                    <?= Html::a('<i class="bi bi-heart-fill me-1 text-danger"></i> ดูฟีดคำขอบคุณทั้งหมด <i class="bi bi-arrow-right ms-1"></i>', ['/appreciation/default/index'], ['class'=>'appreciation-feed-link']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-none">
        <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden"
            style="background-color: #fff;">
            <div class="position-absolute top-0 end-0 p-3 opacity-10" style="pointer-events: none;"><svg
                    xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    data-lucide="trophy" class="lucide lucide-trophy text-secondary">
                    <path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"></path>
                    <path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"></path>
                    <path d="M18 9h1.5a1 1 0 0 0 0-5H18"></path>
                    <path d="M4 22h16"></path>
                    <path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"></path>
                    <path d="M6 9H4.5a1 1 0 0 1 0-5H6"></path>
                </svg></div>
            <div class="d-flex align-items-start justify-content-between position-relative z-1 mb-4">
                <div>
                    <h2 class="fw-black text-dark mb-1" style="font-size: 1.25rem;">บุคลากรทรงคุณค่า</h2>
                    <p class="text-muted fst-italic mb-0" style="font-size: 0.75rem;">อีก 750 คะแนน เป็น Platinum</p>
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-4 shadow-sm"
                    style="width: 48px; height: 48px; background-color: #fffbeb; color: #f59e0b;"> <svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        data-lucide="star" class="lucide lucide-star fill-current">
                        <path
                            d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                        </path>
                    </svg></div>
            </div>
            <div class="d-flex flex-column gap-4 position-relative z-1">
                <div>
                    <div class="d-flex justify-content-between text-uppercase fw-black mb-2" style="font-size: 0.7rem;">
                        <span class="text-muted">ความก้าวหน้า (Gold)</span><span style="color: #d97706;">62%</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 10px; background-color: #f1f5f9; padding: 2px;">
                        <div class="progress-bar rounded-pill" role="progressbar"
                            style="width: 62%; background: linear-gradient(to right, #fbbf24, #f97316); box-shadow: 0 0 10px rgba(245, 158, 11, 0.3);">
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-light" style="background-color: #f8fafc;">
                            <p class="text-muted fw-black text-uppercase mb-1" style="font-size: 0.65rem;">สะสมดาว</p>
                            <div class="d-flex align-items-center gap-1 fw-black text-dark"
                                style="font-size: 1.125rem;"><svg xmlns="http://www.w3.org/2000/svg" width="16"
                                    height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" data-lucide="star"
                                    style="color: #fbbf24; fill: #fbbf24;" class="lucide lucide-star">
                                    <path
                                        d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                                    </path>
                                </svg> 45</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-light" style="background-color: #f8fafc;">
                            <p class="text-muted fw-black text-uppercase mb-1" style="font-size: 0.65rem;">แลกรางวัล</p>
                            <div class="d-flex align-items-center gap-1 fw-black"
                                style="font-size: 1.125rem; color: #2563eb;"><svg xmlns="http://www.w3.org/2000/svg"
                                    width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trophy"
                                    class="lucide lucide-trophy">
                                    <path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"></path>
                                    <path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"></path>
                                    <path d="M18 9h1.5a1 1 0 0 0 0-5H18"></path>
                                    <path d="M4 22h16"></path>
                                    <path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"></path>
                                    <path d="M6 9H4.5a1 1 0 0 1 0-5H6"></path>
                                </svg> 1,250</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-3">
        <div class="erp-quick-services h-100">
            <div class="erp-quick-service">
                <a href="<?= Url::to(['/leave']) ?>" class="text-decoration-none text-body d-block h-100">
                    <div class="card rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2" style="width: 42px; height: 42px;">
                            <i data-lucide="calendar-heart"></i>
                        </div>
                        <span class="text-xs text-muted fw-bold d-block">การลางาน</span>
                    </div>
                </a>
            </div>
            <div class="erp-quick-service">
                <a href="<?= Url::to(['/me/repair-v2']) ?>" class="text-decoration-none text-body d-block h-100">
                    <div class="card rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2" style="width: 42px; height: 42px;">
                            <i data-lucide="wrench"></i>
                        </div>
                        <span class="text-xs text-muted fw-bold d-block">แจ้งซ่อม</span>
                    </div>
                </a>
            </div>
            <div class="erp-quick-service">
                <a href="<?= Url::to(['/me/booking-vehicle/calendar']) ?>" class="text-decoration-none text-body d-block h-100">
                    <div class="card rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2" style="width: 42px; height: 42px;">
                            <i data-lucide="car-front"></i>
                        </div>
                        <span class="text-xs text-muted fw-bold d-block">จองรถ</span>
                    </div>
                </a>
            </div>
            <div class="erp-quick-service">
                <a href="<?= Url::to(['/me/booking-meeting/calendar']) ?>" class="text-decoration-none text-body d-block h-100">
                    <div class="card rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2" style="width: 42px; height: 42px;">
                            <i data-lucide="calendar-days"></i>
                        </div>
                        <span class="text-xs text-muted fw-bold d-block">จองห้องประชุม</span>
                    </div>
                </a>
            </div>
            <div class="erp-quick-service">
                <a href="<?= Url::to(!empty(env('DEVELOPMENT_USER_URL')) ? env('DEVELOPMENT_USER_URL') : ['/me/development']) ?>" class="text-decoration-none text-body d-block h-100">
                    <div class="card rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2" style="width: 42px; height: 42px;">
                            <i data-lucide="graduation-cap"></i>
                        </div>
                        <span class="text-xs text-muted fw-bold d-block">อบรม/ดูงาน</span>
                    </div>
                </a>
            </div>
            <div class="erp-quick-service">
                <a href="<?= Url::to(['/me/purchase']) ?>" class="text-decoration-none text-body d-block h-100">
                    <div class="card rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2" style="width: 42px; height: 42px;">
                            <i data-lucide="shopping-cart"></i>
                        </div>
                        <span class="text-xs text-muted fw-bold d-block">ขอซื้อ/ขอจ้าง</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 d-none">
    <div class="col-12">
        <div class="row g-3">
            <div class="col-12">

                <div class="mb-3 d-flex align-items-center justify-content-between my-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-4 border border-danger-subtle shadow-sm"
                            style="width: 48px; height: 48px; background-color: #fff1f2; color: #f43f5e;"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" data-lucide="heart-handshake"
                                class="lucide lucide-heart-handshake">
                                <path
                                    d="M19.414 14.414C21 12.828 22 11.5 22 9.5a5.5 5.5 0 0 0-9.591-3.676.6.6 0 0 1-.818.001A5.5 5.5 0 0 0 2 9.5c0 2.3 1.5 4 3 5.5l5.535 5.362a2 2 0 0 0 2.879.052 2.12 2.12 0 0 0-.004-3 2.124 2.124 0 1 0 3-3 2.124 2.124 0 0 0 3.004 0 2 2 0 0 0 0-2.828l-1.881-1.882a2.41 2.41 0 0 0-3.409 0l-1.71 1.71a2 2 0 0 1-2.828 0 2 2 0 0 1 0-2.828l2.823-2.762">
                                </path>
                            </svg></div>
                        <div>
                            <h3 class="fw-black text-dark mb-0" style="font-size: 1.125rem;">พลังแห่งคำขอบคุณ
                                (Appreciation Wall)</h3>
                            <p class="text-muted fst-italic fw-medium mb-0" style="font-size: 0.75rem;">
                                ส่งพลังบวกให้เพื่อนร่วมงาน (+50 แต้มสะสมต่อคำขอบคุณ)</p>
                        </div>
                    </div>
                </div>
                <div class="card border border-primary-subtle p-3 d-flex flex-column align-items-center justify-content-center text-center hover-shadow transition-all"
                    style="background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                    <div class="bg-white rounded-4 shadow-sm d-flex align-items-center justify-content-center mb-3 transition-transform hover-scale"
                        style="width: 56px; height: 56px; color: #3b82f6;"><svg xmlns="http://www.w3.org/2000/svg"
                            width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="smile"
                            class="lucide lucide-smile">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                            <line x1="9" x2="9.01" y1="9" y2="9"></line>
                            <line x1="15" x2="15.01" y1="9" y2="9"></line>
                        </svg></div>
                    <h4 class="fw-black mb-1" style="color: #1e3a8a; font-size: 1.125rem;">วันนี้คุณขอบคุณใครหรือยัง?
                    </h4>
                    <p class="fw-medium px-2 mb-3" style="color: #64748b; font-size: 0.75rem;">คำชื่นชมเล็กๆ น้อยๆ
                        ช่วยสร้างกำลังใจอันยิ่งใหญ่ให้เพื่อนร่วมงานของเราได้นะครับ</p>
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                        <?= Html::a('เริ่มส่งคำขอบคุณเลย', ['/appreciation/default/create'], ['class' => 'btn fw-black shadow-sm hover-scale', 'style' => 'background-color: #4f46e5; color: white; border-radius: 12px; font-size: 0.75rem; padding: 10px 24px;']) ?>
                        <?= Html::a('ดูฟีด', ['/appreciation/default/index'], ['class' => 'btn btn-outline-primary fw-bold', 'style' => 'border-radius: 12px; font-size: 0.75rem;']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row g-3 mb-5">
    <div class="col-12 col-lg-9">
        <section class="mt-5">
            <div class="d-flex flex-column flex-lg-row flex-wrap justify-content-between gap-3 mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 42px; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-inbox-icon lucide-inbox">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                            <path
                                d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex gap-2 align-items-center">
                            <h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">หนังสือราชการที่รอการจัดการ</h3>
                            <span id="unread-document-badge" class="badge bg-danger-subtle text-danger fw-bold"
                                style="font-size: 0.7rem;<?= (empty($unreadDocumentCount) || (int) $unreadDocumentCount <= 0) ? ' display: none;' : '' ?>">
                                ยังไม่ได้อ่าน <span id="unread-document-count"><?= (int) ($unreadDocumentCount ?? 0) ?></span> ฉบับ
                            </span>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">
                            รายการหนังสือรับเข้าจากระบบสารบรรณที่ส่งถึงคุณ
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-shrink-0 flex-wrap">
                    <a href="<?= Url::to(['/me/documents']) ?>"
                        class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm border">
                        <i class="fa-solid fa-angles-right"></i> ดูหนังสือทั้งหมด
                    </a>
                   
                    
                </div>
            </div>

            <div id="viewDocument"></div>

        </section>

    </div>
    <div class="col-12 col-lg-3">
        <?= $this->render('list_member_team', [
            'me' => $me,
            'probationCaseCount' => $probationCaseCount ?? 0,
            'probationTaskCount' => $probationTaskCount ?? 0,
        ]) ?>
    </div>
</div>
</div>

<?php if ((!empty($unreadDocumentCount) && (int) $unreadDocumentCount > 0) || $pendingApproveCount > 0): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <?php if (!empty($unreadDocumentCount) && (int) $unreadDocumentCount > 0): ?>
    <div id="unread-document-toast" class="toast border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header bg-danger text-white border-0">
            <i class="fa-solid fa-bell me-2"></i>
            <strong class="me-auto">แจ้งเตือนหนังสือราชการ</strong>
            <small class="text-white text-opacity-75">ตอนนี้</small>
            <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                <i class="fa-regular fa-envelope fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-dark">คุณมีหนังสือที่ยังไม่ได้อ่าน <span class="text-danger"><?= (int) $unreadDocumentCount ?></span> ฉบับ</div>
                <a href="<?= Url::to(['/me/documents', 'DocumentSearch' => ['q_status' => 'unread', 'date_filter' => '', 'date_start' => '', 'date_end' => '']]) ?>" class="small text-primary text-decoration-none fw-semibold">
                    ดูทั้งหมด <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
    $toastIdx = 0;
    foreach ($pendingApproveItems as $item):
        if ((int) ($item['count'] ?? 0) <= 0) continue;
        $toastIdx++;
    ?>
    <div id="pending-approve-toast-<?= Html::encode((string) ($item['key'] ?? $toastIdx)) ?>" class="toast pending-approve-toast border-0 shadow-lg mt-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
        <div class="toast-header bg-warning text-dark border-0">
            <i class="fa-solid <?= Html::encode($item['icon'] ?? 'fa-clipboard-check') ?> me-2"></i>
            <strong class="me-auto">รออนุมัติ: <?= Html::encode($item['label'] ?? 'รายการอนุมัติ') ?></strong>
            <small class="text-dark text-opacity-75">ตอนนี้</small>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body d-flex align-items-center gap-3">
            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                <i class="fa-solid <?= Html::encode($item['icon'] ?? 'fa-clipboard-check') ?> fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-dark"><?= Html::encode($item['label'] ?? 'รายการอนุมัติ') ?> รออนุมัติ <span class="text-warning"><?= (int) ($item['count'] ?? 0) ?></span> รายการ</div>
                <a href="<?= Url::to($item['url'] ?? ['/approve-v2']) ?>" class="small text-primary text-decoration-none fw-semibold">
                    ไปที่หน้าอนุมัติ <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php

$documentUrl = Url::to(['/me/documents/show-home']);
$urlUpload = Url::to(["/filemanager/uploads/single"]);
$ref = $me->ref;
$userId = $me->id;

$js = <<< JS
    loadDocumentMe();

    // แสดง toast หนังสือที่ยังไม่ได้อ่าน + รายการรออนุมัติ (แยกตามประเภท)
    (function () {
        if (!window.bootstrap || !bootstrap.Toast) return;
        var unread = document.getElementById('unread-document-toast');
        if (unread) {
            new bootstrap.Toast(unread, { autohide: false }).show();
        }
        document.querySelectorAll('.pending-approve-toast').forEach(function (el) {
            new bootstrap.Toast(el, { autohide: false }).show();
        });
    })();

    // ลดจำนวน "ยังไม่ได้อ่าน" เมื่อกดเปิดอ่านหนังสือ
    $('body').on('click', '.view-document', function () {
        var \$badge = $('#unread-document-badge');
        var \$count = $('#unread-document-count');
        var n = parseInt(\$count.text(), 10) || 0;
        n = Math.max(0, n - 1);
        \$count.text(n);
        if (n <= 0) {
            \$badge.hide();
        }
    });
    
    //หนังสือ
    async function  loadDocumentMe(){
        await $.ajax({
            type: "get",
            url: "$documentUrl",
            dataType: "json",
            data:{
                list:true,
                callback:'me'
            },
            beforeSend: function(){
                $('#viewDocument').html('<p>กำลังโหลดหนังสือ</p>');
            },
            success: function (res) {
                    $('#viewDocument').html(res.content);
            }
        });
    }


    $(document).ready(function() {
    function updateClock() {
        const now = new Date();
        
        // ดึงค่า ชั่วโมง : นาที : วินาที
        // .padStart(2, '0') เพื่อให้แสดงเลข 0 ข้างหน้าถ้าหลักเดียว เช่น 08:05:09
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        // แสดงผลในรูปแบบ HH:mm:ss
        const currentTimeString = `\${hours}:\${minutes}:\${seconds}`;
        
        // อัปเดตลงใน HTML
        $('#current-time').text(currentTimeString);
    }

    // เรียกใช้ฟังก์ชันทันทีที่หน้าเว็บโหลดเสร็จ
    updateClock();

    // ตั้งเวลาให้ทำงานทุกๆ 1 วินาที
    setInterval(updateClock, 1000);

    // เลือกเข้า/ออกไปทำใน modal ลงเวลา (.open-modal) แล้ว — การ์ดนี้เหลือปุ่มลงเวลาปุ่มเดียว
    });
    
// --- ส่วนงานอัปโหลดรูปภาพ ---
    $('#avatar-upload').on('change', function (e) {
        const file = this.files[0];
        const imgPreview = $('#avatar-preview'); 
        const container = $('.group'); 
        
        console.log('1. เริ่มกระบวนการ Upload');
        
        if (!file) return;

        // 1. ตรวจสอบขนาดไฟล์
        if (file.size > 2000000) {
            alert("ไฟล์มีขนาดใหญ่เกินไป (ห้ามเกิน 2MB)");
            $(this).val(''); 
            return;
        }

        // 2. Preview รูปทันที
        const reader = new FileReader();
        reader.onload = function (e) {
            imgPreview.attr('src', e.target.result);
            container.css('opacity', '0.6'); // จางลงเพื่อบอกสถานะกำลังโหลด
        };
        reader.readAsDataURL(file);

        // 3. เตรียมข้อมูล
        const formData = new FormData();
        formData.append("avatar", file);
        formData.append("id", "$userId");
        formData.append("ref", "$ref");
        formData.append("name", 'avatar');
        // เพิ่ม CSRF Token ป้องกัน Error 400/403

        console.log('2. ยิง AJAX ไปที่:', "$urlUpload");

        // 4. ส่งข้อมูล
        $.ajax({
            url: "$urlUpload",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                console.log('3. Server Response:', res);
                // เช็ค res.img หรือ res.url ตามที่ API ส่งกลับมา
                if (res.img || res.success) {
                    const finalUrl = res.img || res.url;
                    $('.avatar-profile, #avatar-preview').attr('src', finalUrl);
                } else {
                    alert("เกิดข้อผิดพลาดจาก Server: " + (res.message || "ไม่สามารถบันทึกได้"));
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert("ไม่สามารถติดต่อ Server ได้ (Error: " + xhr.status + ")");
            },
            complete: function() {
                container.css('opacity', '1'); // คืนค่าความชัด
            }
        });
    });

JS;
$this->registerJS($js);
?>
