<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();

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
<?php echo $this->render('@app/modules/me/menu', ['active' => 'dashboard']) ?>

<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="position-relative p-4 text-white overflow-hidden h-100 d-flex flex-column justify-content-center rounded-4"
    style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
    <div class="position-absolute bottom-0 start-0 bg-info opacity-25 rounded-circle"
        style="width: 200px; height: 200px; filter: blur(50px); transform: translate(-30%, 30%);"></div>
    
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
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
                <div class="d-flex align-items-center gap-1 px-3 py-1 rounded-pill shadow-sm"
                    style="background: linear-gradient(to right, #f59e0b, #fb923c);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" data-lucide="star"
                        class="lucide lucide-star text-white fill-white">
                        <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-white text-opacity-75 text-sm fw-medium mb-4"><?= $me->positionName() ?> • <span
                    class="text-white fw-bold text-uppercase" style="letter-spacing: 0.05em;">Rank: Gold</span>
            </p>
            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-4 bg-white bg-opacity-10 text-white text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" class="lucide lucide-map-pin"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg><span><?= $me->departmentName() ?></span>
                </div>
                <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-4 bg-white bg-opacity-10 text-white text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" style="color: #fca5a5; fill: #fca5a5;" class="lucide lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg><span>ได้รับคำชมแล้ว: <span class="fw-black">0 ครั้ง</span></span>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-10 border border-white border-opacity-10 p-4 position-relative rounded-4"
            style="min-width: 180px; backdrop-filter: blur(12px);">
            <p class="text-white text-opacity-75 mb-2 d-flex align-items-center gap-2 fw-bold"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock"><path d="M12 6v6l4 2"></path><circle cx="12" cy="12" r="10"></circle></svg> บันทึกเวลาเข้างาน</p>
            <span id="current-time" class="text-white fw-black mb-4 lh-1" style="font-size: 2.25rem; letter-spacing: -0.05em;">00:00:00</span>
            <button id="btn-clock-in" class="btn bg-white w-100 py-2 fw-black border-0 shadow-lg d-flex align-items-center justify-content-center gap-2 hover-scale position-relative z-1" style="color: #2563eb; border-radius: 16px; font-size: 0.875rem;">Check-in <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" class="lucide lucide-arrow-up-right"><path d="M7 7h10v10"></path><path d="M7 17 17 7"></path></svg></button>
        </div>
    </div>
</div>

<style>
    .transition-all { transition: all 0.3s ease; }
    .hover-opacity-100:hover { opacity: 1 !important; }
    .group:hover #avatar-preview { transform: scale(1.1); filter: blur(2px); }
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
                <div class="row g-3">
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
        <div class="row g-2">
            <div class="col-6">


                <div
                    class="hover bg-body rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                    <a href="<?= Url::to(['/me/leave']) ?>">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2"
                            style="width: 42px; height: 42px;">
                            <i data-lucide="calendar-heart"></i>
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">ระบบลา</span></div>
                </div>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Url::to(['/me/repair-v2']) ?>">
                    <div
                        class="hover bg-body rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2"
                            style="width: 42px; height: 42px;">
                            <i data-lucide="wrench"></i>
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">แจ้งซ่อม</span></div>
                    </div>
                </a>
            </div>
            <div class="col-6">
                <a href="<?= Url::to(['/me/booking-vehicle/calendar']) ?>">

                    <div
                        class="hover bg-body rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2"
                            style="width: 42px; height: 42px;">
                            <i data-lucide="car-front"></i>
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">จองรถ</span></div>
                </a>
            </div>
        </div>
        <div class="col-6">
            <a href="<?= Url::to(['/me/booking-meeting/calendar']) ?>">

                <div
                    class="hover bg-body rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                    <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2"
                        style="width: 42px; height: 42px;">
                        <i data-lucide="calendar-days"></i>
                    </div>
                    <div><span class="text-xs text-muted fw-bold d-block">จองห้องประชุม</span></div>
                </div>
            </a>
        </div>
        <div class="col-6">
            <a href="<?= Url::to(['/me/development']) ?>">

                <div
                    class="hover bg-body rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                    <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2"
                        style="width: 42px; height: 42px;">
                        <i data-lucide="graduation-cap"></i>
                    </div>
                    <div><span class="text-xs text-muted fw-bold d-block">อบรม/ดูงาน</span></div>
                </div>
            </a>
        </div>
        <div class="col-6">
            <a href="<?= Url::to(['/me/purchase']) ?>">
                <div
                    class="hover bg-body rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                    <div class="bg-primary-subtle rounded-3 d-flex align-items-center justify-content-center shadow-sm mb-2"
                        style="width: 42px; height: 42px;">
                        <i data-lucide="shopping-cart"></i>
                    </div>
                    <div><span class="text-xs text-muted fw-bold d-block">ขอซื้อ/ขอจ้าง</span></div>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-12">

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center"
                            style="width: 42px; height: 42px;">
                            <i data-lucide="calendar-range"></i>
                        </div>
                        <div class="lh-sm">
                            <h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">ภาพรวมการลา</h3>
                            <p class="text-muted mb-0" style="font-size: 0.75rem;">
                                สะสมคะแนนเพื่ออัปเกรดระดับและแลกรางวัล</p>
                        </div>
                    </div>
                    <a href="<?= Url::to(['/me/leave']) ?>"
                        class="btn btn-primary rounded-4 fw-black shadow-sm d-flex align-items-center gap-2 px-3 py-2 hover-scale">
                        ดูทั้งหมด <i data-lucide="chevrons-right"></i>
                    </a>
                </div>
                <div
                    class="card border-0 shadow-sm p-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div class="d-flex flex-row justify-content-between">
                        <div class="d-flex flex-column">
                            <h6 class="mb-1 text-dark">สิทธิลาพักผ่อน</h6>
                            <p class="text-muted mb-4">ภาพรวมของการลาพักผ่อน</p>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center"
                            style="width: 42px; height: 42px;">
                            <i data-lucide="calendar-heart"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-1 fw-black text-primary"
                                style="font-size: 0.75rem;">

                                <?php echo $searchModel->sumLeavePermission()['sum'] ?> /
                                <?php echo $searchModel->sumLeavePermission()['total'] ?>

                                <?php
                                $leaveData = $searchModel->sumLeavePermission();

                                // ตรวจสอบว่า total ต้องไม่เป็น 0 หรือ null
                                $percenUseDay = ($leaveData['total'] > 0)
                                    ? number_format(($leaveData['sum'] / $leaveData['total']) * 100, 2)
                                    : "0.00";

                                ?>
                            </div>
                            <span class="text-muted fw-bold" style="font-size: 0.65rem;"><?= $percenUseDay ?>%</span>
                        </div>
                        <div class="progress rounded-pill mb-4" style="height: 6px; background-color: #f1f5f9;">
                            <div class="progress-bar rounded-pill bg-primary" role="progressbar"
                                style="width: <?= $percenUseDay ?>%;">
                            </div>
                        </div>
                        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เริ่มการลา', ['/me/leave/create', 'title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn w-100 rounded-4 fw-bold py-2 open-modal', 'data' => ['size' => 'modal-lg'], 'style' => 'font-size: 0.75rem; background-color: #f8fafc; color: #64748b; border: none;']) ?>

                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">

                <div class="mb-3 d-flex align-items-center justify-content-between">
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
                            <h3 class="fw-black text-dark mb-0" style="font-size: 1.125rem;">กำแพงแห่งคำขอบคุณ
                                (Appreciation Wall)</h3>
                            <p class="text-muted fst-italic fw-medium mb-0" style="font-size: 0.75rem;">
                                ส่งพลังบวกให้เพื่อนร่วมงาน (+50 แต้มสะสมต่อคำชม)</p>
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
                    <button class="btn fw-black shadow-sm hover-scale"
                        style="background-color: #4f46e5; color: white; border-radius: 12px; font-size: 0.75rem; padding: 10px 24px;">เริ่มส่งคำขอบคุณเลย</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mb-5">
    <div class="col-12 col-lg-9">

        <section class="mt-5">
            <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center"
                        style="width: 42px; height: 42px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-inbox-icon lucide-inbox">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                            <path
                                d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">หนังสือราชการที่รอการจัดการ</h3>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">
                            รายการหนังสือรับเข้าจากระบบสารบรรณที่ส่งถึงคุณ
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 overflow-auto hide-scrollbar pb-2 mb-2 align-items-center justify-content-between mb-4">
                    <a href="<?= Url::to(['/me/documents']) ?>"
                        class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm border">
                        ดูทั้งหมด
                    </a>

                    <button
                        class="btn btn-white text-muted text-nowrap rounded-pill px-3 py-1 border hover-bg-light">ด่วนที่สุด</button>
                    <button
                        class="btn btn-white text-muted text-nowrap rounded-pill px-3 py-1 border hover-bg-light">บันทึกข้อความ</button>
                    <button
                        class="btn btn-white text-muted text-nowrap rounded-pill px-3 py-1 border hover-bg-light">หนังสือภายนอก</button>
                    <button
                        class="btn btn-white text-muted text-nowrap rounded-pill px-3 py-1 border hover-bg-light">คำสั่ง</button>
                </div>
            </div>

            <div id="viewDocument"></div>

        </section>

    </div>
    <div class="col-12 col-lg-3">
        <?= $this->render('list_member_team', ['me' => $me]) ?>
    </div>
</div>

<?php

$documentUrl = Url::to(['/me/documents/show-home']);
$urlUpload = Url::to(["/filemanager/uploads/single"]); 
$ref = $me->ref;
$userId = $me->id;

$js = <<< JS
    loadDocumentMe();
    
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
    
    // โบนัส: คลิกปุ่ม Check-in แล้วแสดง Alert เวลาที่กด
    $('#btn-clock-in').on('click', function() {
        const timeAtClick = $('#current-time').text();
        alert('บันทึกเวลาเข้างานสำเร็จที่เวลา: ' + timeAtClick);
    });
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
