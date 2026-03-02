<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\UserHelper;
use app\components\ApproveHelper;
use app\components\ThaiDateHelper;

$totalNotification = ApproveHelper::Info()['total'];
$me = UserHelper::GetEmployee();

// $this->registerJsFile('@web/owl/owl.carousel.min.js', ['depends' => [yii\web\JqueryAsset::className()]]);
// $this->registerCssFile('@web/owl/owl.carousel.min.css');
$days = ThaiDateHelper::formatThaiDate(Date('Y-m-d'), 'long');

$this->title = 'MyDashboard';
$this->params['breadcrumbs'][] = ['label' => 'MyDashboard', 'url' => ['/me']];

$me = UserHelper::GetEmployee();
$this->title = 'ภาพรวมของ' . $me->fullname();
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => $me->fullname(), 'url' => ['/me']];
?>
<style>
/* การ์ดสไตล์ทันสมัย */
.hover {
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); /* Animation แบบนุ่มนวลพิเศษ */
    overflow: hidden;
}

/* เมื่อ Hover ให้เกิด Effect */
.hover:hover {
    transform: translateY(-4px) scale(1.01); /* ยกตัวขึ้นและขยายเล็กน้อย */
}

/* เพิ่มเติม: ทำให้รูปภาพใน Card ซูมเล็กน้อยเมื่อ Hover */
.hover:hover .card-img-top {
    transform: scale(1.05);
    transition: transform 0.6s ease;
}

.card-img-top {
    transition: transform 0.6s ease;
}
</style>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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


<div class="row g-4 mb-4">
    <div class="col-12 col-xl-6">
        <div class="position-relative p-4 text-white overflow-hidden shadow-lg h-100 d-flex flex-column justify-content-center rounded-4" style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);">
            <div class="position-absolute bottom-0 start-0 bg-info opacity-25 rounded-circle" style="width: 200px; height: 200px; filter: blur(50px); transform: translate(-30%, 30%);"></div>
            <div class="d-flex flex-column flex-md-row align-items-center gap-4 position-relative z-1">
                <div class="position-relative">
                    <div class="position-absolute top-0 start-0 translate-middle p-1 rounded-3 shadow-lg border border-2 border-white" style="background: linear-gradient(to top right, #fbbf24, #fef08a); transform: rotate(-12deg) !important; z-index: 10;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trophy" style="color: #92400e;" class="lucide lucide-trophy">
                            <path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978"></path>
                            <path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978"></path>
                            <path d="M18 9h1.5a1 1 0 0 0 0-5H18"></path>
                            <path d="M4 22h16"></path>
                            <path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z"></path>
                            <path d="M6 9H4.5a1 1 0 0 1 0-5H6"></path>
                        </svg>
                    </div>
                    <?= Html::img($me->showAvatar(),[
                        "class" => "shadow-lg object-fit-cover border border-4 border-white border-opacity-25 rounded-5",
                        "width" => 128,
                        "height" => 128,
                    ])?>
                    <div class="position-absolute bottom-0 end-0 bg-success border border-4 border-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-color: #1e40af !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle text-white">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-grow-1 text-center text-md-start">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-3 mb-2">
                        <h2 class="fw-black m-0 tracking-tight text-white" style="font-size: 1.875rem;"><?= $me->fullname?></h2>
                        <div class="d-flex align-items-center gap-1 px-3 py-1 rounded-pill shadow-sm" style="background: linear-gradient(to right, #f59e0b, #fb923c);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star text-white fill-white">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg>
                            <span class="text-white fw-black text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">ดาวเด่นโรงพยาบาล</span>
                        </div>
                    </div>
                    <p class="text-white text-opacity-75 text-sm fw-medium mb-4"><?= $me->positionName() ?> • <span class="text-white fw-bold text-uppercase" style="letter-spacing: 0.05em;">Rank: Gold</span></p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-4 bg-white bg-opacity-10 text-white text-xs"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" class="lucide lucide-map-pin">
                                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg><span><?= $me->departmentName() ?></span></div>
                        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-4 bg-white bg-opacity-10 text-white text-xs"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" style="color: #fca5a5; fill: #fca5a5;" class="lucide lucide-heart">
                                <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                            </svg><span>ได้รับคำขอบคุณแล้ว: <span class="fw-black">28 ครั้ง</span></span></div>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-10 border border-white border-opacity-10 p-4 position-relative" style="min-width: 180px; backdrop-filter: blur(12px);">
                    <p class="text-white text-opacity-75 mb-2 d-flex align-items-center gap-2 fw-bold" style="font-size: 0.75rem;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock">
                            <path d="M12 6v6l4 2"></path>
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg> บันทึกเวลาเข้างาน</p>
                    <span class="text-white fw-black mb-4 lh-1" style="font-size: 2.25rem; letter-spacing: -0.05em;">08:30</span>
                    <button id="btn-clock-in" class="btn bg-white w-100 py-2 fw-black border-0 shadow-lg d-flex align-items-center justify-content-center gap-2 hover-scale position-relative z-1" style="color: #2563eb; border-radius: 16px; font-size: 0.875rem;">Check-in <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-up-right" class="lucide lucide-arrow-up-right">
                            <path d="M7 7h10v10"></path>
                            <path d="M7 17 17 7"></path>
                        </svg></button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="background-color: #fff;">
            <div class="position-absolute top-0 end-0 p-3 opacity-10" style="pointer-events: none;"><svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trophy" class="lucide lucide-trophy text-secondary">
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
                <div class="d-flex align-items-center justify-content-center rounded-4 shadow-sm" style="width: 48px; height: 48px; background-color: #fffbeb; color: #f59e0b;"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star fill-current">
                        <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                    </svg></div>
            </div>
            <div class="d-flex flex-column gap-4 position-relative z-1">
                <div>
                    <div class="d-flex justify-content-between text-uppercase fw-black mb-2" style="font-size: 0.7rem;"><span class="text-muted">ความก้าวหน้า (Gold)</span><span style="color: #d97706;">62%</span></div>
                    <div class="progress rounded-pill" style="height: 10px; background-color: #f1f5f9; padding: 2px;">
                        <div class="progress-bar rounded-pill" role="progressbar" style="width: 62%; background: linear-gradient(to right, #fbbf24, #f97316); box-shadow: 0 0 10px rgba(245, 158, 11, 0.3);"></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-light" style="background-color: #f8fafc;">
                            <p class="text-muted fw-black text-uppercase mb-1" style="font-size: 0.65rem;">สะสมดาว</p>
                            <div class="d-flex align-items-center gap-1 fw-black text-dark" style="font-size: 1.125rem;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" style="color: #fbbf24; fill: #fbbf24;" class="lucide lucide-star">
                                    <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                </svg> 45</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4 border border-light" style="background-color: #f8fafc;">
                            <p class="text-muted fw-black text-uppercase mb-1" style="font-size: 0.65rem;">แลกรางวัล</p>
                            <div class="d-flex align-items-center gap-1 fw-black" style="font-size: 1.125rem; color: #2563eb;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trophy" class="lucide lucide-trophy">
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


                    <div class="hover bg-success-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-success shadow-sm mb-2" style="width: 32px; height: 32px;">
                            <i data-lucide="user-minus"></i>  
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">ระบบลา</span></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="hover bg-primary-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-primary shadow-sm mb-2" style="width: 32px; height: 32px;">
                            <i data-lucide="wrench"></i></div>
                        <div><span class="text-xs text-muted fw-bold d-block">แจ้งซ่อม</span></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="hover bg-info-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-info shadow-sm mb-2" style="width: 32px; height: 32px;">
                            <i data-lucide="car-front"></i> 
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">จองรถ</span></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="hover bg-warning-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-warning shadow-sm mb-2" style="width: 32px; height: 32px;">
                            <i data-lucide="calendar-days"></i> 
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">จองห้องประชุม</span></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="hover bg-info-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-info shadow-sm mb-2" style="width: 32px; height: 32px;">
                            <i data-lucide="graduation-cap"></i>  
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">อบรม/ดูงาน</span></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="hover bg-warning-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                        <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-warning shadow-sm mb-2" style="width: 32px; height: 32px;">
                            <i data-lucide="shopping-cart"></i>  
                        </div>
                        <div><span class="text-xs text-muted fw-bold d-block">ขอซื้อ/ขอจ้าง</span></div>
                    </div>
                </div>
            </div>
    </div>


    <!-- <div class="col-12 col-xl-3">
                    <div class="card h-100 p-4 border-0 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 m-0"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="activity" class="lucide lucide-activity text-primary"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path></svg> สุขภาพล่าสุด</h4>
                            <span class="text-xs fw-bold text-muted text-uppercase">2 ชม. ที่แล้ว</span>
                        </div>
                        <div class="row g-2">
                                                        <div class="col-6">
                                <div class="bg-success-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-success shadow-sm mb-2" style="width: 32px; height: 32px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" class="lucide lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg></div>
                                    <div><span class="text-xs text-muted fw-bold d-block">Heart Rate</span><span class="h6 font-black text-dark m-0 d-flex align-items-baseline gap-1">72 <small class="text-xs fw-normal text-muted">bpm</small></span></div>
                                </div>
                            </div>
                                                        <div class="col-6">
                                <div class="bg-primary-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-primary shadow-sm mb-2" style="width: 32px; height: 32px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="activity" class="lucide lucide-activity"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path></svg></div>
                                    <div><span class="text-xs text-muted fw-bold d-block">Pressure</span><span class="h6 font-black text-dark m-0 d-flex align-items-baseline gap-1">120/80 </span></div>
                                </div>
                            </div>
                                                        <div class="col-6">
                                <div class="bg-info-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-info shadow-sm mb-2" style="width: 32px; height: 32px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="scale" class="lucide lucide-scale"><path d="M12 3v18"></path><path d="m19 8 3 8a5 5 0 0 1-6 0zV7"></path><path d="M3 7h1a17 17 0 0 0 8-2 17 17 0 0 0 8 2h1"></path><path d="m5 8 3 8a5 5 0 0 1-6 0zV7"></path><path d="M7 21h10"></path></svg></div>
                                    <div><span class="text-xs text-muted fw-bold d-block">BMI</span><span class="h6 font-black text-dark m-0 d-flex align-items-baseline gap-1">22.4 </span></div>
                                </div>
                            </div>
                                                        <div class="col-6">
                                <div class="bg-warning-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-warning shadow-sm mb-2" style="width: 32px; height: 32px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="thermometer" class="lucide lucide-thermometer"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"></path></svg></div>
                                    <div><span class="text-xs text-muted fw-bold d-block">Temp</span><span class="h6 font-black text-dark m-0 d-flex align-items-baseline gap-1">36.5° </span></div>
                                </div>
                            </div>
                                                    </div>
                    </div>
                </div> -->

</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background-color: #e0e7ff; color: #4f46e5;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="target" class="lucide lucide-target">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg></div>
                <div class="lh-sm">
                    <h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">ภารกิจพิเศษ (HR Quests)</h3>
                    <p class="text-muted mb-0" style="font-size: 0.75rem;">สะสมคะแนนเพื่ออัปเกรดระดับและแลกรางวัล</p>
                </div>
            </div>
            <a href="#" class="text-decoration-none fw-bold d-flex align-items-center gap-1" style="font-size: 0.75rem; color: #3b82f6;">ดูภารกิจทั้งหมด <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" class="lucide lucide-arrow-right">
                    <path d="M5 12h14"></path>
                    <path d="m12 5 7 7-7 7"></path>
                </svg></a>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="border-radius: 24px; background: white;">
                    <div>
                        <div class="d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 48px; height: 48px; background-color: #fff7ed; color: #f97316;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="zap" class="lucide lucide-zap">
                                <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                            </svg></div>
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">เช็คอินตรงเวลา 5 วันรวด</h5>
                        <p class="text-muted mb-4" style="font-size: 0.65rem;">รักษาวินัยการทำงานอย่างต่อเนื่อง</p>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-1 fw-black text-primary" style="font-size: 0.75rem;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star fill-current">
                                    <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                </svg> +100</div><span class="text-muted fw-bold" style="font-size: 0.65rem;">80%</span>
                        </div>
                        <div class="progress rounded-pill mb-4" style="height: 6px; background-color: #f1f5f9;">
                            <div class="progress-bar rounded-pill bg-primary" role="progressbar" style="width: 80%;"></div>
                        </div><button class="btn w-100 rounded-4 fw-bold py-2" style="font-size: 0.75rem; background-color: #f8fafc; color: #64748b; border: none;">เริ่มทำภารกิจ</button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="border-radius: 24px; background: white;">
                    <div class="position-absolute top-0 end-0 p-4 text-success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle-2" class="lucide lucide-check-circle-2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 48px; height: 48px; background-color: #eff6ff; color: #3b82f6;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="book-open" class="lucide lucide-book-open">
                                <path d="M12 7v14"></path>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                            </svg></div>
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">ผ่านการอบรม PDPA</h5>
                        <p class="text-muted mb-4" style="font-size: 0.65rem;">เรียนรู้และทำแบบทดสอบผ่านเกณฑ์</p>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-1 fw-black text-primary" style="font-size: 0.75rem;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star fill-current">
                                    <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                </svg> +250</div><span class="text-muted fw-bold" style="font-size: 0.65rem;">100%</span>
                        </div>
                        <div class="progress rounded-pill mb-4" style="height: 6px; background-color: #f1f5f9;">
                            <div class="progress-bar rounded-pill bg-success" role="progressbar" style="width: 100%;"></div>
                        </div><button class="btn w-100 rounded-4 fw-bold py-2" style="font-size: 0.75rem; background-color: #ecfdf5; color: #10b981; border: none; cursor: default;">รับคะแนนแล้ว</button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="border-radius: 24px; background: white;">
                    <div>
                        <div class="d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 48px; height: 48px; background-color: #ecfdf5; color: #10b981;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="award" class="lucide lucide-award">
                                <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
                                <circle cx="12" cy="8" r="6"></circle>
                            </svg></div>
                        <h5 class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">ส่งแบบประเมินผลงาน</h5>
                        <p class="text-muted mb-4" style="font-size: 0.65rem;">กรอกข้อมูลรายไตรมาสที่ 1/2569</p>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-1 fw-black text-primary" style="font-size: 0.75rem;"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star fill-current">
                                    <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                </svg> +150</div><span class="text-muted fw-bold" style="font-size: 0.65rem;">20%</span>
                        </div>
                        <div class="progress rounded-pill mb-4" style="height: 6px; background-color: #f1f5f9;">
                            <div class="progress-bar rounded-pill bg-primary" role="progressbar" style="width: 20%;"></div>
                        </div><button class="btn w-100 rounded-4 fw-bold py-2" style="font-size: 0.75rem; background-color: #f8fafc; color: #64748b; border: none;">เริ่มทำภารกิจ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-3">
        <div class="d-flex flex-column gap-4 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white rounded-4 shadow-sm border border-light d-flex align-items-center justify-content-center text-warning" style="width: 40px; height: 40px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="gift" class="lucide lucide-gift">
                        <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                        <path d="M12 8v13"></path>
                        <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                        <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                    </svg></div>
                <div class="lh-sm">
                    <h3 class="h6 fw-bold text-dark m-0">แลกของรางวัล</h3>
                    <p class="text-xs text-muted m-0">ใช้คะแนนสะสม</p>
                </div>
            </div>
            <div class="card border-0 shadow-lg position-relative overflow-hidden p-4 flex-grow-1 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white;">
                <div class="position-absolute" style="bottom: -24px; right: -24px; opacity: 0.05; pointer-events: none;"><svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="gift" class="lucide lucide-gift text-white">
                        <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                        <path d="M12 8v13"></path>
                        <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                        <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                    </svg></div>
                <div class="position-relative z-1 d-flex flex-column gap-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge text-dark fw-black text-uppercase px-2 py-1 rounded-2" style="background-color: #f59e0b; font-size: 9px;">แนะนำ</span>
                        <div class="d-flex align-items-center gap-1 fw-bold" style="color: #fbbf24; font-size: 0.75rem;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star fill-current">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg> 800 pts</div>
                    </div>
                    <div class="mt-2">
                        <h4 class="text-white fw-bold mb-1 lh-sm" style="font-size: 1.125rem;">บัตรกำนัล Starbucks 200.-</h4>
                        <p class="text-white fst-italic mb-0 fw-medium" style="font-size: 0.75rem;">เพิ่มความสดชื่นก่อนเริ่มงาน</p>
                    </div>
                    <div class="mt-auto pt-2"><button class="btn bg-white w-100 border-0 shadow-sm fw-black text-dark hover-scale" style="border-radius: 16px; font-size: 0.75rem; padding: 12px;">แลกรับของรางวัล</button></div>
                </div>
            </div>
            <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center justify-content-between cursor-pointer hover-scale" style="border-radius: 24px; background-color: #fff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 36px; height: 36px;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="calendar" class="lucide lucide-calendar">
                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                            <path d="M3 10h18"></path>
                        </svg></div>
                    <div class="lh-sm">
                        <p class="text-xs fw-bold text-dark m-0">วันลาพักร้อนพิเศษ</p>
                        <p class="text-xs text-muted m-0">ใช้ 2,000 คะแนน</p>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" class="lucide lucide-chevron-right text-muted">
                    <path d="m9 18 6-6-6-6"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="mb-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-4 border border-danger-subtle shadow-sm" style="width: 48px; height: 48px; background-color: #fff1f2; color: #f43f5e;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart-handshake" class="lucide lucide-heart-handshake">
                        <path d="M19.414 14.414C21 12.828 22 11.5 22 9.5a5.5 5.5 0 0 0-9.591-3.676.6.6 0 0 1-.818.001A5.5 5.5 0 0 0 2 9.5c0 2.3 1.5 4 3 5.5l5.535 5.362a2 2 0 0 0 2.879.052 2.12 2.12 0 0 0-.004-3 2.124 2.124 0 1 0 3-3 2.124 2.124 0 0 0 3.004 0 2 2 0 0 0 0-2.828l-1.881-1.882a2.41 2.41 0 0 0-3.409 0l-1.71 1.71a2 2 0 0 1-2.828 0 2 2 0 0 1 0-2.828l2.823-2.762"></path>
                    </svg></div>
                <div>
                    <h3 class="fw-black text-dark mb-0" style="font-size: 1.125rem;">กำแพงแห่งคำขอบคุณ (Appreciation Wall)</h3>
                    <p class="text-muted fst-italic fw-medium mb-0" style="font-size: 0.75rem;">ส่งพลังบวกให้เพื่อนร่วมงาน (+50 แต้มสะสมต่อคำขอบคุณ)</p>
                </div>
            </div>
            <button class="btn btn-danger rounded-4 fw-black shadow-sm d-flex align-items-center gap-2 px-3 py-2 hover-scale" style="font-size: 0.75rem; background-color: #f43f5e; border: none;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="plus" class="lucide lucide-plus">
                    <path d="M5 12h14"></path>
                    <path d="M12 5v14"></path>
                </svg> ส่งคำขอบคุณ</button>
        </div>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="card border-light shadow-sm h-100 p-4 position-relative hover-shadow transition-all" style="background: white;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex position-relative ms-2">
                                <img src="https://picsum.photos/40/40?random=11" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; z-index: 1;">
                                <div class="rounded-circle border border-2 border-white d-flex align-items-center justify-content-center shadow-sm position-relative" style="width: 40px; height: 40px; background-color: #f43f5e; color: white; margin-left: -12px; z-index: 2;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" class="lucide lucide-heart fill-current">
                                        <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                                    </svg></div>
                                <img src="https://picsum.photos/40/40?random=50" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; margin-left: -12px; z-index: 1;">
                            </div>
                            <div>
                                <p class="mb-0 fw-bold text-muted" style="font-size: 0.7rem;"><span class="text-dark">พยาบาลสมศรี</span> ชื่นชม <span style="color: #f43f5e;">เดชา</span></p>
                                <p class="text-muted fw-medium text-uppercase mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">2 ชม. ที่แล้ว</p>
                            </div>
                        </div>
                        <span class="badge rounded-pill d-flex align-items-center gap-1 px-2 py-1 fw-black" style="background-color: #ffedd5; color: #ea580c; font-size: 0.65rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="zap" class="lucide lucide-zap fill-current">
                                <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                            </svg> Problem Solver</span>
                    </div>
                    <div class="p-3 rounded-4 mb-3 border border-light position-relative" style="background-color: #f8fafc;">
                        <div class="position-absolute" style="top: -8px; left: -8px; color: #fecdd3; opacity: 0.5;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="message-square" class="lucide lucide-message-square fill-current">
                                <path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"></path>
                            </svg></div>
                        <p class="text-muted fst-italic mb-0 position-relative z-1" style="font-size: 0.8rem; line-height: 1.5;">"ขอบคุณคุณเดชาที่ช่วยกู้คืนข้อมูลไฟล์พัสดุที่เกือบหายไปเมื่อวานนี้ รวดเร็วและเป็นมืออาชีพมากค่ะ!"</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 fw-black" style="font-size: 0.75rem; color: #fb7185;">
                            <div class="p-2 rounded-3" style="background-color: #fff1f2;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" class="lucide lucide-heart">
                                    <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                                </svg></div> 12 คนชื่นชอบ
                        </button>
                        <span class="badge rounded-pill border d-flex align-items-center gap-1 px-2 py-1 fw-black shadow-sm" style="background-color: #fffbeb; border-color: #fef3c7; color: #f59e0b; font-size: 0.75rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star fill-current">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg> +50 Points</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card border-light shadow-sm h-100 p-4 position-relative hover-shadow transition-all" style="background: white;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex position-relative ms-2">
                                <img src="https://picsum.photos/40/40?random=12" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; z-index: 1;">
                                <div class="rounded-circle border border-2 border-white d-flex align-items-center justify-content-center shadow-sm position-relative" style="width: 40px; height: 40px; background-color: #f43f5e; color: white; margin-left: -12px; z-index: 2;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" class="lucide lucide-heart fill-current">
                                        <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                                    </svg></div>
                                <img src="https://picsum.photos/40/40?random=13" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; margin-left: -12px; z-index: 1;">
                            </div>
                            <div>
                                <p class="mb-0 fw-bold text-muted" style="font-size: 0.7rem;"><span class="text-dark">นพ.วิชัย</span> ชื่นชม <span style="color: #f43f5e;">คุณวิภา</span></p>
                                <p class="text-muted fw-medium text-uppercase mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">5 ชม. ที่แล้ว</p>
                            </div>
                        </div>
                        <span class="badge rounded-pill d-flex align-items-center gap-1 px-2 py-1 fw-black" style="background-color: #dbeafe; color: #2563eb; font-size: 0.65rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart-handshake" class="lucide lucide-heart-handshake fill-current">
                                <path d="M19.414 14.414C21 12.828 22 11.5 22 9.5a5.5 5.5 0 0 0-9.591-3.676.6.6 0 0 1-.818.001A5.5 5.5 0 0 0 2 9.5c0 2.3 1.5 4 3 5.5l5.535 5.362a2 2 0 0 0 2.879.052 2.12 2.12 0 0 0-.004-3 2.124 2.124 0 1 0 3-3 2.124 2.124 0 0 0 3.004 0 2 2 0 0 0 0-2.828l-1.881-1.882a2.41 2.41 0 0 0-3.409 0l-1.71 1.71a2 2 0 0 1-2.828 0 2 2 0 0 1 0-2.828l2.823-2.762"></path>
                            </svg> Team Player</span>
                    </div>
                    <div class="p-3 rounded-4 mb-3 border border-light position-relative" style="background-color: #f8fafc;">
                        <div class="position-absolute" style="top: -8px; left: -8px; color: #fecdd3; opacity: 0.5;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="message-square" class="lucide lucide-message-square fill-current">
                                <path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"></path>
                            </svg></div>
                        <p class="text-muted fst-italic mb-0 position-relative z-1" style="font-size: 0.8rem; line-height: 1.5;">"ขอบคุณที่ช่วยประสานงานเคสฉุกเฉินได้อย่างราบรื่นครับ ทีมเวิร์คยอดเยี่ยมมาก"</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 fw-black" style="font-size: 0.75rem; color: #fb7185;">
                            <div class="p-2 rounded-3" style="background-color: #fff1f2;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" class="lucide lucide-heart fill-current text-danger">
                                    <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                                </svg></div> 24 คนชื่นชอบ
                        </button>
                        <span class="badge rounded-pill border d-flex align-items-center gap-1 px-2 py-1 fw-black shadow-sm" style="background-color: #fffbeb; border-color: #fef3c7; color: #f59e0b; font-size: 0.75rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="star" class="lucide lucide-star fill-current">
                                <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                            </svg> +50 Points</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card border border-primary-subtle p-4 d-flex flex-column align-items-center justify-content-center text-center hover-shadow transition-all h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                    <div class="bg-white rounded-4 shadow-sm d-flex align-items-center justify-content-center mb-3 transition-transform hover-scale" style="width: 56px; height: 56px; color: #3b82f6;"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="smile" class="lucide lucide-smile">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                            <line x1="9" x2="9.01" y1="9" y2="9"></line>
                            <line x1="15" x2="15.01" y1="9" y2="9"></line>
                        </svg></div>
                    <h4 class="fw-black mb-1" style="color: #1e3a8a; font-size: 1.125rem;">วันนี้คุณขอบคุณใครหรือยัง?</h4>
                    <p class="fw-medium px-2 mb-4" style="color: #64748b; font-size: 0.75rem;">คำชื่นชมเล็กๆ น้อยๆ ช่วยสร้างกำลังใจอันยิ่งใหญ่ให้เพื่อนร่วมงานของเราได้นะครับ</p>
                    <button class="btn fw-black shadow-sm hover-scale" style="background-color: #4f46e5; color: white; border-radius: 12px; font-size: 0.75rem; padding: 10px 24px;">เริ่มส่งคำขอบคุณเลย</button>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="mt-5">
    <div class="d-flex align-items-center gap-3 mb-3">
        <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="inbox" class="lucide lucide-inbox">
                <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
            </svg></div>
        <div>
            <h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">หนังสือราชการที่รอการจัดการ</h3>
            <p class="text-muted mb-0" style="font-size: 0.75rem;">รายการหนังสือรับเข้าจากระบบสารบรรณที่ส่งถึงคุณ</p>
        </div>
    </div>
    <div class="d-flex gap-2 overflow-auto hide-scrollbar pb-2 mb-2">
        <button class="btn btn-primary rounded-pill fw-bold text-nowrap px-3 py-1 shadow-sm border-0" style="font-size: 0.75rem; padding-left: 20px; padding-right: 20px;">ทั้งหมด</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">ด่วนที่สุด</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">บันทึกข้อความ</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">หนังสือภายนอก</button>
        <button class="btn btn-white text-muted fw-bold text-nowrap rounded-pill px-3 py-1 border hover-bg-light" style="font-size: 0.75rem;">คำสั่ง</button>
    </div>
    <div class="d-flex flex-column gap-2">
        <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
            <div class="row g-0 align-items-center">
                <div class="position-absolute start-0 top-0 bottom-0 bg-danger" style="width: 4px;"></div>
                <div class="col-auto py-3 ps-4 pe-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #fef2f2; color: #dc2626;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="file-warning" class="lucide lucide-file-warning">
                            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                            <path d="M12 9v4"></path>
                            <path d="M12 17h.01"></path>
                        </svg></div>
                </div>
                <div class="col py-3 px-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.65rem;">บันทึกข้อความ</span>
                        <div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="hash" class="lucide lucide-hash">
                                <line x1="4" x2="20" y1="9" y2="9"></line>
                                <line x1="4" x2="20" y1="15" y2="15"></line>
                                <line x1="10" x2="8" y1="3" y2="21"></line>
                                <line x1="16" x2="14" y1="3" y2="21"></line>
                            </svg> ลย 0033.012/ว 79</div>
                    </div>
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">ขอเชิญประชุมคณะกรรมการบริหารจัดการระบบคอมพิวเตอร์และเครือข่าย ครั้งที่ 1/2569</h6>
                    <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="building-2" class="lucide lucide-building-2">
                                <path d="M10 12h4"></path>
                                <path d="M10 8h4"></path>
                                <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
                                <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
                                <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
                            </svg> จาก: ฝ่ายบริหารงานทั่วไป</span><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="user" class="lucide lucide-user">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg> ถึง: ทุกหน่วยงาน</span></div>
                </div>
                <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block"><span class="badge bg-danger text-white rounded-pill fw-bold px-2" style="font-size: 0.6rem;">ด่วนที่สุด</span>
                        <div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock">
                                <path d="M12 6v6l4 2"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg> 15 นาทีที่แล้ว</div>
                    </div>
                    <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg></button>
                    <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" class="lucide lucide-chevron-right">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg></button>
                </div>
            </div>
        </div>
        <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
            <div class="row g-0 align-items-center">
                <div class="position-absolute start-0 top-0 bottom-0 bg-warning" style="width: 4px; background-color: #f97316 !important;"></div>
                <div class="col-auto py-3 ps-4 pe-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #fff7ed; color: #f97316;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="file-text" class="lucide lucide-file-text">
                            <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                            <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                            <path d="M10 9H8"></path>
                            <path d="M16 13H8"></path>
                            <path d="M16 17H8"></path>
                        </svg></div>
                </div>
                <div class="col py-3 px-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.65rem;">หนังสือภายนอก</span>
                        <div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="hash" class="lucide lucide-hash">
                                <line x1="4" x2="20" y1="9" y2="9"></line>
                                <line x1="4" x2="20" y1="15" y2="15"></line>
                                <line x1="10" x2="8" y1="3" y2="21"></line>
                                <line x1="16" x2="14" y1="3" y2="21"></line>
                            </svg> สธ 0202.3/1244</div>
                    </div>
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">แจ้งแนวทางปฏิบัติการเบิกจ่ายงบประมาณกองทุนสุขภาพประจำปีงบประมาณ 2569</h6>
                    <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="building-2" class="lucide lucide-building-2">
                                <path d="M10 12h4"></path>
                                <path d="M10 8h4"></path>
                                <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
                                <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
                                <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
                            </svg> จาก: สสจ.เลย</span><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="users" class="lucide lucide-users">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg> ถึง: กลุ่มงานแผนงานฯ</span></div>
                </div>
                <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block"><span class="badge text-white rounded-pill fw-bold px-2" style="background-color: #f97316; font-size: 0.6rem;">ด่วนมาก</span>
                        <div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock">
                                <path d="M12 6v6l4 2"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg> 1 ชม. ที่แล้ว</div>
                    </div>
                    <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg></button>
                    <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" class="lucide lucide-chevron-right">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg></button>
                </div>
            </div>
        </div>
        <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
            <div class="row g-0 align-items-center">
                <div class="position-absolute start-0 top-0 bottom-0 bg-success" style="width: 4px; background-color: #10b981 !important;"></div>
                <div class="col-auto py-3 ps-4 pe-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #ecfdf5; color: #10b981;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="files" class="lucide lucide-files">
                            <path d="M15 2h-4a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8"></path>
                            <path d="M16.706 2.706A2.4 2.4 0 0 0 15 2v5a1 1 0 0 0 1 1h5a2.4 2.4 0 0 0-.706-1.706z"></path>
                            <path d="M5 7a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h8a2 2 0 0 0 1.732-1"></path>
                        </svg></div>
                </div>
                <div class="col py-3 px-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #f1f5f9; color: #64748b; font-size: 0.65rem;">ประกาศ</span>
                        <div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="hash" class="lucide lucide-hash">
                                <line x1="4" x2="20" y1="9" y2="9"></line>
                                <line x1="4" x2="20" y1="15" y2="15"></line>
                                <line x1="10" x2="8" y1="3" y2="21"></line>
                                <line x1="16" x2="14" y1="3" y2="21"></line>
                            </svg> รพ.05/2569</div>
                    </div>
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">เรื่อง การปรับปรุงเวลาการเข้า-ออกอาคารจอดรถสำหรับเจ้าหน้าที่ช่วงเวลาเร่งด่วน</h6>
                    <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="building-2" class="lucide lucide-building-2">
                                <path d="M10 12h4"></path>
                                <path d="M10 8h4"></path>
                                <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
                                <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
                                <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
                            </svg> จาก: ฝ่ายอาคารสถานที่</span><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="users" class="lucide lucide-users">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg> ถึง: บุคลากรทุกคน</span></div>
                </div>
                <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block"><span class="badge rounded-pill fw-bold px-2 text-white" style="background-color: #10b981; font-size: 0.6rem;">ปกติ</span>
                        <div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock">
                                <path d="M12 6v6l4 2"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg> วันนี้ 08:30</div>
                    </div>
                    <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg></button>
                    <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" class="lucide lucide-chevron-right">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg></button>
                </div>
            </div>
        </div>
        <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
            <div class="row g-0 align-items-center">
                <div class="position-absolute start-0 top-0 bottom-0 bg-primary" style="width: 4px;"></div>
                <div class="col-auto py-3 ps-4 pe-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #eff6ff; color: #2563eb;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="mail" class="lucide lucide-mail">
                            <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path>
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        </svg></div>
                </div>
                <div class="col py-3 px-2">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #f1f5f9; color: #64748b; font-size: 0.65rem;">คำสั่ง</span>
                        <div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="hash" class="lucide lucide-hash">
                                <line x1="4" x2="20" y1="9" y2="9"></line>
                                <line x1="4" x2="20" y1="15" y2="15"></line>
                                <line x1="10" x2="8" y1="3" y2="21"></line>
                                <line x1="16" x2="14" y1="3" y2="21"></line>
                            </svg> รพ.112/2569</div>
                    </div>
                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">แต่งตั้งคณะทำงานพิจารณาจัดซื้อจัดจ้างระบบบริหารจัดการข้อมูลสุขภาพ (HIS)</h6>
                    <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="building-2" class="lucide lucide-building-2">
                                <path d="M10 12h4"></path>
                                <path d="M10 8h4"></path>
                                <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
                                <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
                                <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
                            </svg> จาก: กลุ่มภารกิจอำนวยการ</span><span class="d-flex align-items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="users" class="lucide lucide-users">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg> ถึง: คณะกรรมการฯ</span></div>
                </div>
                <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block"><span class="badge bg-primary text-white rounded-pill fw-bold px-2" style="font-size: 0.6rem;">ด่วน</span>
                        <div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock">
                                <path d="M12 6v6l4 2"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg> เมื่อวานนี้</div>
                    </div>
                    <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle">
                            <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                            <path d="m9 11 3 3L22 4"></path>
                        </svg></button>
                    <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" class="lucide lucide-chevron-right">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg></button>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between p-3 rounded-4 mt-3 border border-light" style="background-color: #f8fafc; border-radius: 20px;">
        <div class="d-flex align-items-center gap-2 mb-2 mb-sm-0">
            <span class="spinner-grow spinner-grow-sm text-primary" style="width: 8px; height: 8px; animation-duration: 2s;" role="status"></span>
            <p class="text-muted fw-bold mb-0" style="font-size: 0.75rem;">พบหนังสือใหม่ <span class="text-primary">2 รายการ</span> ที่ยังไม่ได้ดำเนินการ</p>
        </div>
        <a href="#" class="text-decoration-none fw-bold d-flex align-items-center gap-1 hover-text-dark" style="font-size: 0.75rem; color: #2563eb;">เข้าสู่ระบบงานสารบรรณเต็มรูปแบบ <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" class="lucide lucide-arrow-right">
                <path d="M5 12h14"></path>
                <path d="m12 5 7 7-7 7"></path>
            </svg></a>
    </div>
</section>

<!-- Main Content Grid -->



<?php
$urlRepair = Url::to(['/me/repair']);
$ApproveStockUrl = Url::to(['/me/approve/stock-out']);
$ApprovePurchaseUrl = Url::to(['/me/approve/purchase']);
$ownerAssetUrl = Url::to(['/me/owner']);
$documentUrl = Url::to(['/me/documents/show-home-v2']);
// $urlRepair = Url::to(['/me/repair-me']);
$js = <<< JS

    loadRepairHostory();
    // loadApproveStock();
    loadPurchase();
    loadOwnerAsset();
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
    
    //ประวัติการซ่อม
    async function  loadRepairHostory(){
        await $.ajax({
            type: "get",
            url: "$urlRepair",
            data:{
                "title":"ประวัติการซ่อม",
                "name":"repair",
            },
            dataType: "json",
            success: function (res) {
                if(res.summary > 0){
                    \$('#viewRepair').html(res.content);
                }
            }
        });
    }

     //ขอเบิกวัสดุ
     async function  loadApproveStock(){
        await $.ajax({
            type: "get",
            url: "$ApproveStockUrl",
            dataType: "json",
            success: function (res) {
                if(res.count != 0){
                    \$('#viewApproveStock').html(res.content);
                }else{
                    $('#viewApproveStock').hide()
                }
            }
        });
    }

         //ขออนุมิติจัดซื้อจัดจ้าง
        async  function  loadPurchase(){
            await \$.ajax({
                type: "get",
                url: "$ApprovePurchaseUrl",
                dataType: "json",
                success: function (res) {
                    console.log(res.count)
                    if(res.count != 0){
                        \$('#viewApprovePurchase').html(res.content);
                    }else{
                        $('#viewApprovePurchase').hide();
                    }
                }
            });
    }

    //ทรัพย์สินที่รับผิดขอบ
    async function  loadOwnerAsset(){
       await  \$.ajax({
            type: "get",
            url: "$ownerAssetUrl",
            dataType: "json",
            success: function (res) {
                console.log(res.count)
                if(res.count != 0){
                    \$('#viewOwnerAsset').html(res.content);
                }else{
                    $('#viewOwnerAsset').hide();
                }
            }
        });
    }
    JS;
$this->registerJS($js, yii\web\View::POS_END);
?>

<?php // Pjax::end(); 
?>