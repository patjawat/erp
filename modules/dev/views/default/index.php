<?php
/* @var $this yii\web\View */
use yii\helpers\Url;

$this->title = 'Hospital ERP NextGen';

// 1. ลงทะเบียน Bootstrap 5 (CDN)
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js');

// 2. Custom CSS
$this->registerCss("
    @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap');
    
    body { font-family: 'Sarabun', sans-serif; background-color: #f8fafc; color: #475569; }
    
    /* Utilities */
    .bg-glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
    .font-black { font-weight: 800; }
    .fw-medium { font-weight: 500; }
    .rounded-4 { border-radius: 1rem !important; }
    .rounded-5 { border-radius: 1.5rem !important; }
    .shadow-hover:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transform: translateY(-2px); transition: all 0.3s ease; }
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* Menu & Interactions */
    .menu-item { min-width: 100px; height: 80px; transition: all 0.2s; color: #64748b; }
    .menu-item:hover { background-color: #f1f5f9; color: #2563eb; }
    .menu-item.active { background-color: #eff6ff; color: #2563eb; }
    .menu-active-bar { height: 4px; width: 40%; background-color: #2563eb; border-radius: 10px 10px 0 0; position: absolute; bottom: 0; }
    .search-input:focus { box-shadow: none; border-color: #bfdbfe; background-color: #fff; }
    
    /* Custom Gradients & Animations */
    .animate-in { animation: fadeIn 0.6s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .hover-scale:hover { transform: scale(1.05); transition: transform 0.2s; }
");

$user_name = Yii::$app->user->identity->profile->firstname ?? 'เดชา สายบุญตั้ง'; 
?>

<div id="root">
    <div class="d-flex flex-column min-vh-100">
        
        <header class="sticky-top bg-glass border-bottom px-4 py-3" style="z-index: 1020; border-color: #e2e8f0 !important;">
            <div class="container-fluid d-flex align-items-center justify-content-between" style="max-width: 1600px;">
                <div class="d-flex align-items-center gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-white shadow-sm" 
                             style="width: 40px; height: 40px; background: linear-gradient(135deg, #2563eb, #4f46e5);">
                            <i data-lucide="layout-grid" width="20" height="20"></i>
                        </div>
                        <div class="d-none d-sm-block lh-1">
                            <h1 class="h6 font-black text-dark m-0">HOSPITAL ERP</h1>
                            <span class="text-xs fw-bold text-muted text-uppercase" style="letter-spacing: 1px;">Enterprise System</span>
                        </div>
                    </div>
                    <div class="d-none d-lg-flex align-items-center bg-light rounded-pill px-3 py-2 border border-transparent" style="width: 380px;">
                        <i data-lucide="search" class="text-muted" width="16" height="16"></i>
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent shadow-none ms-2 text-muted search-input" placeholder="ค้นหาเลขที่หนังสือ หรือ หัวข้อเรื่อง...">
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="d-none d-sm-flex align-items-center gap-2 btn btn-sm bg-success-subtle text-success fw-bold rounded-3 border-0" style="font-size: 10px;">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 6px; height: 6px; animation-duration: 1.5s;" role="status"></span> Server Online
                    </button>
                    <button class="btn btn-light rounded-circle p-2 position-relative text-secondary">
                        <i data-lucide="bell" width="20" height="20"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-white rounded-circle" style="width: 10px; height: 10px; margin-left: -8px; margin-top: 8px;"></span>
                    </button>
                    <div class="vr mx-2 text-muted"></div>
                    <div class="d-flex align-items-center gap-3 cursor-pointer p-1 rounded">
                        <div class="text-end d-none d-md-block lh-sm">
                            <p class="mb-0 text-xs fw-bold text-dark"><?= $user_name ?></p>
                            <p class="mb-0 text-xs text-muted fst-italic">General Admin</p>
                        </div>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=0F172A&color=fff" class="rounded-3" width="36" height="36">
                    </div>
                </div>
            </div>
        </header>

        <div class="sticky-top bg-white border-bottom shadow-sm" style="top: 73px; z-index: 1010; border-color: #e2e8f0 !important;">
            <div class="container-fluid d-flex align-items-center" style="height: 96px; max-width: 1600px;">
                <div class="flex-grow-1 d-flex align-items-center overflow-auto hide-scrollbar px-2 gap-2 h-100">
                    <a href="#" class="menu-item active d-flex flex-column align-items-center justify-content-center rounded-4 text-decoration-none position-relative">
                        <div class="mb-2 p-2 rounded-3 bg-primary text-white shadow-sm"><i data-lucide="layout-dashboard" width="20" height="20"></i></div>
                        <span class="text-xs fw-bold">Dashboard</span>
                        <div class="menu-active-bar"></div>
                    </a>
                    <?php 
                    $menus = [['icon'=>'users', 'name'=>'บุคลากร'], ['icon'=>'car', 'name'=>'จองรถ'], ['icon'=>'calendar', 'name'=>'ห้องประชุม'], ['icon'=>'box', 'name'=>'คลังพัสดุ'], ['icon'=>'briefcase', 'name'=>'ทรัพย์สิน'], ['icon'=>'file-text', 'name'=>'งานสารบรรณ'], ['icon'=>'clipboard-list', 'name'=>'แผนงาน'], ['icon'=>'graduation-cap', 'name'=>'อบรม'], ['icon'=>'log-out', 'name'=>'ระบบลา'], ['icon'=>'shopping-cart', 'name'=>'จัดซื้อ'], ['icon'=>'wrench', 'name'=>'ซ่อมบำรุง'], ['icon'=>'monitor', 'name'=>'คอมพิวเตอร์']];
                    foreach($menus as $m): ?>
                    <a href="#" class="menu-item d-flex flex-column align-items-center justify-content-center rounded-4 text-decoration-none">
                        <div class="mb-2 p-2 rounded-3 bg-light text-secondary"><i data-lucide="<?= $m['icon'] ?>" width="20" height="20"></i></div>
                        <span class="text-xs fw-bold"><?= $m['name'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex align-items-center px-3 h-100 border-start bg-white">
                    <button class="btn btn-outline-secondary border-dashed d-flex flex-column align-items-center justify-content-center rounded-3" style="width: 64px; height: 64px; border-style: dashed;">
                        <i data-lucide="grid" width="20" height="20"></i><span class="text-xs fw-bold mt-1">ทั้งหมด</span>
                    </button>
                </div>
            </div>
        </div>

        <main class="flex-grow-1 p-4 w-100 mx-auto animate-in pb-5" style="max-width: 1600px;">
            
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="h4 font-black text-dark mb-1">Overview Dashboard</h2>
                    <div class="d-flex align-items-center gap-2 text-muted text-xs fw-medium">
                        <span>ข้อมูลภาพรวมรายบุคคล</span><span class="bg-secondary rounded-circle" style="width: 4px; height: 4px;"></span><span class="text-primary fw-bold">ปีงบประมาณ 2569</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border rounded-3 text-xs fw-bold text-muted shadow-sm hover-shadow"><i data-lucide="filter" width="14" height="14" class="me-1"></i> กรองข้อมูล</button>
                    <button class="btn btn-primary rounded-3 text-xs fw-bold shadow-sm d-flex align-items-center"><i data-lucide="layout-grid" width="14" height="14" class="me-1"></i> จัดการหน้าหลัก</button>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-6">
                    <div class="position-relative p-4 text-white overflow-hidden shadow-lg h-100 d-flex flex-column justify-content-center" 
                         style="background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border-radius: 40px;">
                        <div class="position-absolute bottom-0 start-0 bg-info opacity-25 rounded-circle" style="width: 200px; height: 200px; filter: blur(50px); transform: translate(-30%, 30%);"></div>
                        <div class="d-flex flex-column flex-md-row align-items-center gap-4 position-relative z-1">
                            <div class="position-relative">
                                <div class="position-absolute top-0 start-0 translate-middle p-1 rounded-3 shadow-lg border border-2 border-white" style="background: linear-gradient(to top right, #fbbf24, #fef08a); transform: rotate(-12deg) !important; z-index: 10;">
                                    <i data-lucide="trophy" width="20" height="20" style="color: #92400e;"></i>
                                </div>
                                                            <img src="https://picsum.photos/120/120?random=50" class="shadow-lg object-fit-cover border border-4 border-white border-opacity-25" 
                                    style="width: 128px; height: 128px; border-radius: 32px;">
                                <div class="position-absolute bottom-0 end-0 bg-success border border-4 border-primary rounded-circle d-flex align-items-center justify-content-center" 
                                    style="width: 32px; height: 32px; border-color: #1e40af !important;">
                                    <i data-lucide="check-circle" width="14" height="14" class="text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 text-center text-md-start">
                                <div class="d-flex flex-column flex-md-row align-items-center gap-3 mb-2">
                                    <h2 class="fw-black m-0 tracking-tight text-white" style="font-size: 1.875rem;">เดชา สายบุญตั้ง</h2>
                                    <div class="d-flex align-items-center gap-1 px-3 py-1 rounded-pill shadow-sm" style="background: linear-gradient(to right, #f59e0b, #fb923c);">
                                        <i data-lucide="star" width="12" height="12" class="text-white fill-white"></i>
                                        <span class="text-white fw-black text-uppercase" style="font-size: 10px; letter-spacing: 0.05em;">ดาวเด่นโรงพยาบาล</span>
                                    </div>
                                </div>
                                <p class="text-white text-opacity-75 text-sm fw-medium mb-4">นักวิชาการคอมพิวเตอร์ ชำนาญการ • <span class="text-white fw-bold text-uppercase" style="letter-spacing: 0.05em;">Rank: Gold</span></p>
                                <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-4 bg-white bg-opacity-10 text-white text-xs"><i data-lucide="map-pin" width="14" height="14"></i><span>ศูนย์คอมพิวเตอร์</span></div>
                                    <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-4 bg-white bg-opacity-10 text-white text-xs"><i data-lucide="heart" width="14" height="14" style="color: #fca5a5; fill: #fca5a5;"></i><span>ได้รับคำขอบคุณแล้ว: <span class="fw-black">28 ครั้ง</span></span></div>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-center justify-content-center bg-white bg-opacity-10 border border-white border-opacity-10 p-4 position-relative" style="min-width: 180px; border-radius: 32px; backdrop-filter: blur(12px);">
                                <p class="text-white text-opacity-75 mb-2 d-flex align-items-center gap-2 fw-bold" style="font-size: 0.75rem;"><i data-lucide="clock" width="14" height="14"></i> บันทึกเวลาเข้างาน</p>
                                <span class="text-white fw-black mb-4 lh-1" style="font-size: 2.25rem; letter-spacing: -0.05em;">08:30</span>
                                <button id="btn-clock-in" class="btn bg-white w-100 py-2 fw-black border-0 shadow-lg d-flex align-items-center justify-content-center gap-2 hover-scale position-relative z-1" style="color: #2563eb; border-radius: 16px; font-size: 0.875rem;">Check-in <i data-lucide="arrow-up-right" width="16" height="16"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                  <div class="col-12 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="border-radius: 32px; background-color: #fff;">
                        <div class="position-absolute top-0 end-0 p-3 opacity-10" style="pointer-events: none;"><i data-lucide="trophy" width="120" height="120" class="text-secondary"></i></div>
                        <div class="d-flex align-items-start justify-content-between position-relative z-1 mb-4">
                            <div><h2 class="fw-black text-dark mb-1" style="font-size: 1.25rem;">บุคลากรทรงคุณค่า</h2><p class="text-muted fst-italic mb-0" style="font-size: 0.75rem;">อีก 750 คะแนน เป็น Platinum</p></div>
                            <div class="d-flex align-items-center justify-content-center rounded-4 shadow-sm" style="width: 48px; height: 48px; background-color: #fffbeb; color: #f59e0b;"> <i data-lucide="star" width="24" height="24" class="fill-current"></i></div>
                        </div>
                        <div class="d-flex flex-column gap-4 position-relative z-1">
                            <div>
                                <div class="d-flex justify-content-between text-uppercase fw-black mb-2" style="font-size: 0.7rem;"><span class="text-muted">ความก้าวหน้า (Gold)</span><span style="color: #d97706;">62%</span></div>
                                <div class="progress rounded-pill" style="height: 10px; background-color: #f1f5f9; padding: 2px;"><div class="progress-bar rounded-pill" role="progressbar" style="width: 62%; background: linear-gradient(to right, #fbbf24, #f97316); box-shadow: 0 0 10px rgba(245, 158, 11, 0.3);"></div></div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6"><div class="p-3 rounded-4 border border-light" style="background-color: #f8fafc;"><p class="text-muted fw-black text-uppercase mb-1" style="font-size: 0.65rem;">สะสมดาว</p><div class="d-flex align-items-center gap-1 fw-black text-dark" style="font-size: 1.125rem;"><i data-lucide="star" width="16" height="16" style="color: #fbbf24; fill: #fbbf24;"></i> 45</div></div></div>
                                <div class="col-6"><div class="p-3 rounded-4 border border-light" style="background-color: #f8fafc;"><p class="text-muted fw-black text-uppercase mb-1" style="font-size: 0.65rem;">แลกรางวัล</p><div class="d-flex align-items-center gap-1 fw-black" style="font-size: 1.125rem; color: #2563eb;"><i data-lucide="trophy" width="16" height="16"></i> 1,250</div></div></div>
                            </div>
                        </div>
                    </div>
                </div>        

                <div class="col-12 col-xl-3">
                    <div class="card-custom h-100 p-4 border-0 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="h6 fw-bold text-dark d-flex align-items-center gap-2 m-0"><i data-lucide="activity" width="18" height="18" class="text-primary"></i> สุขภาพล่าสุด</h4>
                            <span class="text-xs fw-bold text-muted text-uppercase">2 ชม. ที่แล้ว</span>
                        </div>
                        <div class="row g-2">
                            <?php $healths = [['bg'=>'success', 'icon'=>'heart', 'label'=>'Heart Rate', 'val'=>'72', 'unit'=>'bpm'], ['bg'=>'primary', 'icon'=>'activity', 'label'=>'Pressure', 'val'=>'120/80', 'unit'=>''], ['bg'=>'info', 'icon'=>'scale', 'label'=>'BMI', 'val'=>'22.4', 'unit'=>''], ['bg'=>'warning', 'icon'=>'thermometer', 'label'=>'Temp', 'val'=>'36.5°', 'unit'=>'']];
                            foreach($healths as $h): ?>
                            <div class="col-6">
                                <div class="bg-<?= $h['bg'] ?>-subtle rounded-4 p-3 h-100 d-flex flex-column justify-content-between cursor-pointer border border-transparent shadow-hover">
                                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-center text-<?= $h['bg'] ?> shadow-sm mb-2" style="width: 32px; height: 32px;"><i data-lucide="<?= $h['icon'] ?>" width="16" height="16"></i></div>
                                    <div><span class="text-xs text-muted fw-bold d-block"><?= $h['label'] ?></span><span class="h6 font-black text-dark m-0 d-flex align-items-baseline gap-1"><?= $h['val'] ?> <?php if($h['unit']): ?><small class="text-xs fw-normal text-muted"><?= $h['unit'] ?></small><?php endif; ?></span></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-9">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background-color: #e0e7ff; color: #4f46e5;"><i data-lucide="target" width="20" height="20"></i></div>
                            <div class="lh-sm"><h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">ภารกิจพิเศษ (HR Quests)</h3><p class="text-muted mb-0" style="font-size: 0.75rem;">สะสมคะแนนเพื่ออัปเกรดระดับและแลกรางวัล</p></div>
                        </div>
                        <a href="#" class="text-decoration-none fw-bold d-flex align-items-center gap-1" style="font-size: 0.75rem; color: #3b82f6;">ดูภารกิจทั้งหมด <i data-lucide="arrow-right" width="14" height="14"></i></a>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="border-radius: 24px; background: white;">
                                <div><div class="d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 48px; height: 48px; background-color: #fff7ed; color: #f97316;"><i data-lucide="zap" width="24" height="24"></i></div><h5 class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">เช็คอินตรงเวลา 5 วันรวด</h5><p class="text-muted mb-4" style="font-size: 0.65rem;">รักษาวินัยการทำงานอย่างต่อเนื่อง</p></div>
                                <div><div class="d-flex justify-content-between align-items-center mb-2"><div class="d-flex align-items-center gap-1 fw-black text-primary" style="font-size: 0.75rem;"><i data-lucide="star" width="12" height="12" class="fill-current"></i> +100</div><span class="text-muted fw-bold" style="font-size: 0.65rem;">80%</span></div><div class="progress rounded-pill mb-4" style="height: 6px; background-color: #f1f5f9;"><div class="progress-bar rounded-pill bg-primary" role="progressbar" style="width: 80%;"></div></div><button class="btn w-100 rounded-4 fw-bold py-2" style="font-size: 0.75rem; background-color: #f8fafc; color: #64748b; border: none;">เริ่มทำภารกิจ</button></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="border-radius: 24px; background: white;">
                                <div class="position-absolute top-0 end-0 p-4 text-success"><i data-lucide="check-circle-2" width="20" height="20"></i></div>
                                <div><div class="d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 48px; height: 48px; background-color: #eff6ff; color: #3b82f6;"><i data-lucide="book-open" width="24" height="24"></i></div><h5 class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">ผ่านการอบรม PDPA</h5><p class="text-muted mb-4" style="font-size: 0.65rem;">เรียนรู้และทำแบบทดสอบผ่านเกณฑ์</p></div>
                                <div><div class="d-flex justify-content-between align-items-center mb-2"><div class="d-flex align-items-center gap-1 fw-black text-primary" style="font-size: 0.75rem;"><i data-lucide="star" width="12" height="12" class="fill-current"></i> +250</div><span class="text-muted fw-bold" style="font-size: 0.65rem;">100%</span></div><div class="progress rounded-pill mb-4" style="height: 6px; background-color: #f1f5f9;"><div class="progress-bar rounded-pill bg-success" role="progressbar" style="width: 100%;"></div></div><button class="btn w-100 rounded-4 fw-bold py-2" style="font-size: 0.75rem; background-color: #ecfdf5; color: #10b981; border: none; cursor: default;">รับคะแนนแล้ว</button></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card border-0 shadow-sm h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden" style="border-radius: 24px; background: white;">
                                <div><div class="d-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 48px; height: 48px; background-color: #ecfdf5; color: #10b981;"><i data-lucide="award" width="24" height="24"></i></div><h5 class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">ส่งแบบประเมินผลงาน</h5><p class="text-muted mb-4" style="font-size: 0.65rem;">กรอกข้อมูลรายไตรมาสที่ 1/2569</p></div>
                                <div><div class="d-flex justify-content-between align-items-center mb-2"><div class="d-flex align-items-center gap-1 fw-black text-primary" style="font-size: 0.75rem;"><i data-lucide="star" width="12" height="12" class="fill-current"></i> +150</div><span class="text-muted fw-bold" style="font-size: 0.65rem;">20%</span></div><div class="progress rounded-pill mb-4" style="height: 6px; background-color: #f1f5f9;"><div class="progress-bar rounded-pill bg-primary" role="progressbar" style="width: 20%;"></div></div><button class="btn w-100 rounded-4 fw-bold py-2" style="font-size: 0.75rem; background-color: #f8fafc; color: #64748b; border: none;">เริ่มทำภารกิจ</button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-3">
                    <div class="d-flex flex-column gap-4 h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-4 shadow-sm border border-light d-flex align-items-center justify-content-center text-warning" style="width: 40px; height: 40px;"><i data-lucide="gift" width="20" height="20"></i></div>
                            <div class="lh-sm"><h3 class="h6 fw-bold text-dark m-0">แลกของรางวัล</h3><p class="text-xs text-muted m-0">ใช้คะแนนสะสม</p></div>
                        </div>
                        <div class="card border-0 shadow-lg position-relative overflow-hidden p-4 flex-grow-1 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 32px; color: white;">
                            <div class="position-absolute" style="bottom: -24px; right: -24px; opacity: 0.05; pointer-events: none;"><i data-lucide="gift" width="120" height="120" class="text-white"></i></div>
                            <div class="position-relative z-1 d-flex flex-column gap-3 h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge text-dark fw-black text-uppercase px-2 py-1 rounded-2" style="background-color: #f59e0b; font-size: 9px;">แนะนำ</span>
                                    <div class="d-flex align-items-center gap-1 fw-bold" style="color: #fbbf24; font-size: 0.75rem;"><i data-lucide="star" width="14" height="14" class="fill-current"></i> 800 pts</div>
                                </div>
                                <div class="mt-2"><h4 class="text-white fw-bold mb-1 lh-sm" style="font-size: 1.125rem;">บัตรกำนัล Starbucks 200.-</h4><p class="text-white fst-italic mb-0 fw-medium" style="font-size: 0.75rem;">เพิ่มความสดชื่นก่อนเริ่มงาน</p></div>
                                <div class="mt-auto pt-2"><button class="btn bg-white w-100 border-0 shadow-sm fw-black text-dark hover-scale" style="border-radius: 16px; font-size: 0.75rem; padding: 12px;">แลกรับของรางวัล</button></div>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center justify-content-between cursor-pointer hover-scale" style="border-radius: 24px; background-color: #fff;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 36px; height: 36px;"><i data-lucide="calendar" width="18" height="18"></i></div>
                                <div class="lh-sm"><p class="text-xs fw-bold text-dark m-0">วันลาพักร้อนพิเศษ</p><p class="text-xs text-muted m-0">ใช้ 2,000 คะแนน</p></div>
                            </div>
                            <i data-lucide="chevron-right" width="16" height="16" class="text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-4 border border-danger-subtle shadow-sm" style="width: 48px; height: 48px; background-color: #fff1f2; color: #f43f5e;"><i data-lucide="heart-handshake" width="24" height="24"></i></div>
                            <div><h3 class="fw-black text-dark mb-0" style="font-size: 1.125rem;">กำแพงแห่งคำขอบคุณ (Appreciation Wall)</h3><p class="text-muted fst-italic fw-medium mb-0" style="font-size: 0.75rem;">ส่งพลังบวกให้เพื่อนร่วมงาน (+50 แต้มสะสมต่อคำขอบคุณ)</p></div>
                        </div>
                        <button class="btn btn-danger rounded-4 fw-black shadow-sm d-flex align-items-center gap-2 px-3 py-2 hover-scale" style="font-size: 0.75rem; background-color: #f43f5e; border: none;"><i data-lucide="plus" width="16" height="16"></i> ส่งคำขอบคุณ</button>
                    </div>
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <div class="card border-light shadow-sm h-100 p-4 position-relative hover-shadow transition-all" style="border-radius: 32px; background: white;">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex position-relative ms-2">
                                            <img src="https://picsum.photos/40/40?random=11" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; z-index: 1;">
                                            <div class="rounded-circle border border-2 border-white d-flex align-items-center justify-content-center shadow-sm position-relative" style="width: 40px; height: 40px; background-color: #f43f5e; color: white; margin-left: -12px; z-index: 2;"><i data-lucide="heart" width="14" height="14" class="fill-current"></i></div>
                                            <img src="https://picsum.photos/40/40?random=50" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; margin-left: -12px; z-index: 1;">
                                        </div>
                                        <div><p class="mb-0 fw-bold text-muted" style="font-size: 0.7rem;"><span class="text-dark">พยาบาลสมศรี</span> ชื่นชม <span style="color: #f43f5e;">เดชา</span></p><p class="text-muted fw-medium text-uppercase mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">2 ชม. ที่แล้ว</p></div>
                                    </div>
                                    <span class="badge rounded-pill d-flex align-items-center gap-1 px-2 py-1 fw-black" style="background-color: #ffedd5; color: #ea580c; font-size: 0.65rem;"><i data-lucide="zap" width="10" height="10" class="fill-current"></i> Problem Solver</span>
                                </div>
                                <div class="p-3 rounded-4 mb-3 border border-light position-relative" style="background-color: #f8fafc;">
                                    <div class="position-absolute" style="top: -8px; left: -8px; color: #fecdd3; opacity: 0.5;"><i data-lucide="message-square" width="24" height="24" class="fill-current"></i></div>
                                    <p class="text-muted fst-italic mb-0 position-relative z-1" style="font-size: 0.8rem; line-height: 1.5;">"ขอบคุณคุณเดชาที่ช่วยกู้คืนข้อมูลไฟล์พัสดุที่เกือบหายไปเมื่อวานนี้ รวดเร็วและเป็นมืออาชีพมากค่ะ!"</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 fw-black" style="font-size: 0.75rem; color: #fb7185;"><div class="p-2 rounded-3" style="background-color: #fff1f2;"><i data-lucide="heart" width="16" height="16"></i></div> 12 คนชื่นชอบ</button>
                                    <span class="badge rounded-pill border d-flex align-items-center gap-1 px-2 py-1 fw-black shadow-sm" style="background-color: #fffbeb; border-color: #fef3c7; color: #f59e0b; font-size: 0.75rem;"><i data-lucide="star" width="10" height="10" class="fill-current"></i> +50 Points</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card border-light shadow-sm h-100 p-4 position-relative hover-shadow transition-all" style="border-radius: 32px; background: white;">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex position-relative ms-2">
                                            <img src="https://picsum.photos/40/40?random=12" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; z-index: 1;">
                                            <div class="rounded-circle border border-2 border-white d-flex align-items-center justify-content-center shadow-sm position-relative" style="width: 40px; height: 40px; background-color: #f43f5e; color: white; margin-left: -12px; z-index: 2;"><i data-lucide="heart" width="14" height="14" class="fill-current"></i></div>
                                            <img src="https://picsum.photos/40/40?random=13" class="rounded-circle border border-2 border-white shadow-sm position-relative" style="width: 40px; height: 40px; margin-left: -12px; z-index: 1;">
                                        </div>
                                        <div><p class="mb-0 fw-bold text-muted" style="font-size: 0.7rem;"><span class="text-dark">นพ.วิชัย</span> ชื่นชม <span style="color: #f43f5e;">คุณวิภา</span></p><p class="text-muted fw-medium text-uppercase mb-0" style="font-size: 0.6rem; letter-spacing: 0.5px;">5 ชม. ที่แล้ว</p></div>
                                    </div>
                                    <span class="badge rounded-pill d-flex align-items-center gap-1 px-2 py-1 fw-black" style="background-color: #dbeafe; color: #2563eb; font-size: 0.65rem;"><i data-lucide="heart-handshake" width="10" height="10" class="fill-current"></i> Team Player</span>
                                </div>
                                <div class="p-3 rounded-4 mb-3 border border-light position-relative" style="background-color: #f8fafc;">
                                    <div class="position-absolute" style="top: -8px; left: -8px; color: #fecdd3; opacity: 0.5;"><i data-lucide="message-square" width="24" height="24" class="fill-current"></i></div>
                                    <p class="text-muted fst-italic mb-0 position-relative z-1" style="font-size: 0.8rem; line-height: 1.5;">"ขอบคุณที่ช่วยประสานงานเคสฉุกเฉินได้อย่างราบรื่นครับ ทีมเวิร์คยอดเยี่ยมมาก"</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 fw-black" style="font-size: 0.75rem; color: #fb7185;"><div class="p-2 rounded-3" style="background-color: #fff1f2;"><i data-lucide="heart" width="16" height="16" class="fill-current text-danger"></i></div> 24 คนชื่นชอบ</button>
                                    <span class="badge rounded-pill border d-flex align-items-center gap-1 px-2 py-1 fw-black shadow-sm" style="background-color: #fffbeb; border-color: #fef3c7; color: #f59e0b; font-size: 0.75rem;"><i data-lucide="star" width="10" height="10" class="fill-current"></i> +50 Points</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="card border border-primary-subtle p-4 d-flex flex-column align-items-center justify-content-center text-center hover-shadow transition-all h-100" style="border-radius: 32px; background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);">
                                <div class="bg-white rounded-4 shadow-sm d-flex align-items-center justify-content-center mb-3 transition-transform hover-scale" style="width: 56px; height: 56px; color: #3b82f6;"><i data-lucide="smile" width="28" height="28"></i></div>
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
                    <div class="bg-primary-subtle text-primary rounded-4 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;"><i data-lucide="inbox" width="20" height="20"></i></div>
                    <div><h3 class="fw-black text-dark mb-0" style="font-size: 1rem;">หนังสือราชการที่รอการจัดการ</h3><p class="text-muted mb-0" style="font-size: 0.75rem;">รายการหนังสือรับเข้าจากระบบสารบรรณที่ส่งถึงคุณ</p></div>
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
                            <div class="col-auto py-3 ps-4 pe-3"><div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #fef2f2; color: #dc2626;"><i data-lucide="file-warning" width="20" height="20"></i></div></div>
                            <div class="col py-3 px-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.65rem;">บันทึกข้อความ</span><div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><i data-lucide="hash" width="10" height="10"></i> ลย 0033.012/ว 79</div></div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">ขอเชิญประชุมคณะกรรมการบริหารจัดการระบบคอมพิวเตอร์และเครือข่าย ครั้งที่ 1/2569</h6>
                                <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><i data-lucide="building-2" width="10" height="10"></i> จาก: ฝ่ายบริหารงานทั่วไป</span><span class="d-flex align-items-center gap-1"><i data-lucide="user" width="10" height="10"></i> ถึง: ทุกหน่วยงาน</span></div>
                            </div>
                            <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                                <div class="text-end d-none d-md-block"><span class="badge bg-danger text-white rounded-pill fw-bold px-2" style="font-size: 0.6rem;">ด่วนที่สุด</span><div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><i data-lucide="clock" width="10" height="10"></i> 15 นาทีที่แล้ว</div></div>
                                <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i data-lucide="check-circle" width="16" height="16"></i></button>
                                <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <i data-lucide="chevron-right" width="12" height="12"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
                        <div class="row g-0 align-items-center">
                            <div class="position-absolute start-0 top-0 bottom-0 bg-warning" style="width: 4px; background-color: #f97316 !important;"></div>
                            <div class="col-auto py-3 ps-4 pe-3"><div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #fff7ed; color: #f97316;"><i data-lucide="file-text" width="20" height="20"></i></div></div>
                            <div class="col py-3 px-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #eff6ff; color: #1d4ed8; font-size: 0.65rem;">หนังสือภายนอก</span><div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><i data-lucide="hash" width="10" height="10"></i> สธ 0202.3/1244</div></div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">แจ้งแนวทางปฏิบัติการเบิกจ่ายงบประมาณกองทุนสุขภาพประจำปีงบประมาณ 2569</h6>
                                <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><i data-lucide="building-2" width="10" height="10"></i> จาก: สสจ.เลย</span><span class="d-flex align-items-center gap-1"><i data-lucide="users" width="10" height="10"></i> ถึง: กลุ่มงานแผนงานฯ</span></div>
                            </div>
                            <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                                <div class="text-end d-none d-md-block"><span class="badge text-white rounded-pill fw-bold px-2" style="background-color: #f97316; font-size: 0.6rem;">ด่วนมาก</span><div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><i data-lucide="clock" width="10" height="10"></i> 1 ชม. ที่แล้ว</div></div>
                                <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i data-lucide="check-circle" width="16" height="16"></i></button>
                                <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <i data-lucide="chevron-right" width="12" height="12"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
                        <div class="row g-0 align-items-center">
                            <div class="position-absolute start-0 top-0 bottom-0 bg-success" style="width: 4px; background-color: #10b981 !important;"></div>
                            <div class="col-auto py-3 ps-4 pe-3"><div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #ecfdf5; color: #10b981;"><i data-lucide="files" width="20" height="20"></i></div></div>
                            <div class="col py-3 px-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #f1f5f9; color: #64748b; font-size: 0.65rem;">ประกาศ</span><div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><i data-lucide="hash" width="10" height="10"></i> รพ.05/2569</div></div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">เรื่อง การปรับปรุงเวลาการเข้า-ออกอาคารจอดรถสำหรับเจ้าหน้าที่ช่วงเวลาเร่งด่วน</h6>
                                <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><i data-lucide="building-2" width="10" height="10"></i> จาก: ฝ่ายอาคารสถานที่</span><span class="d-flex align-items-center gap-1"><i data-lucide="users" width="10" height="10"></i> ถึง: บุคลากรทุกคน</span></div>
                            </div>
                            <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                                <div class="text-end d-none d-md-block"><span class="badge rounded-pill fw-bold px-2 text-white" style="background-color: #10b981; font-size: 0.6rem;">ปกติ</span><div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><i data-lucide="clock" width="10" height="10"></i> วันนี้ 08:30</div></div>
                                <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i data-lucide="check-circle" width="16" height="16"></i></button>
                                <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <i data-lucide="chevron-right" width="12" height="12"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="card border border-light shadow-sm hover-shadow transition-all overflow-hidden p-0" style="border-radius: 16px;">
                        <div class="row g-0 align-items-center">
                            <div class="position-absolute start-0 top-0 bottom-0 bg-primary" style="width: 4px;"></div>
                            <div class="col-auto py-3 ps-4 pe-3"><div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background-color: #eff6ff; color: #2563eb;"><i data-lucide="mail" width="20" height="20"></i></div></div>
                            <div class="col py-3 px-2">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge rounded-2 fw-bold px-2 py-1" style="background-color: #f1f5f9; color: #64748b; font-size: 0.65rem;">คำสั่ง</span><div class="d-flex align-items-center gap-1 text-muted fw-bold" style="font-size: 0.65rem;"><i data-lucide="hash" width="10" height="10"></i> รพ.112/2569</div></div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.85rem;">แต่งตั้งคณะทำงานพิจารณาจัดซื้อจัดจ้างระบบบริหารจัดการข้อมูลสุขภาพ (HIS)</h6>
                                <div class="d-flex flex-wrap gap-3 text-muted" style="font-size: 0.65rem;"><span class="d-flex align-items-center gap-1"><i data-lucide="building-2" width="10" height="10"></i> จาก: กลุ่มภารกิจอำนวยการ</span><span class="d-flex align-items-center gap-1"><i data-lucide="users" width="10" height="10"></i> ถึง: คณะกรรมการฯ</span></div>
                            </div>
                            <div class="col-auto py-3 pe-4 ps-2 d-flex align-items-center gap-3">
                                <div class="text-end d-none d-md-block"><span class="badge bg-primary text-white rounded-pill fw-bold px-2" style="font-size: 0.6rem;">ด่วน</span><div class="d-flex align-items-center justify-content-end gap-1 text-muted mt-1" style="font-size: 0.6rem;"><i data-lucide="clock" width="10" height="10"></i> เมื่อวานนี้</div></div>
                                <button class="btn btn-light rounded-circle p-2 border-0 text-muted hover-text-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><i data-lucide="check-circle" width="16" height="16"></i></button>
                                <button class="btn btn-primary rounded-3 fw-bold d-flex align-items-center gap-1 px-3 py-1 shadow-sm" style="font-size: 0.75rem;">เปิดอ่าน <i data-lucide="chevron-right" width="12" height="12"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between p-3 rounded-4 mt-3 border border-light" style="background-color: #f8fafc; border-radius: 20px;">
                    <div class="d-flex align-items-center gap-2 mb-2 mb-sm-0">
                        <span class="spinner-grow spinner-grow-sm text-primary" style="width: 8px; height: 8px; animation-duration: 2s;" role="status"></span>
                        <p class="text-muted fw-bold mb-0" style="font-size: 0.75rem;">พบหนังสือใหม่ <span class="text-primary">2 รายการ</span> ที่ยังไม่ได้ดำเนินการ</p>
                    </div>
                    <a href="#" class="text-decoration-none fw-bold d-flex align-items-center gap-1 hover-text-dark" style="font-size: 0.75rem; color: #2563eb;">เข้าสู่ระบบงานสารบรรณเต็มรูปแบบ <i data-lucide="arrow-right" width="14" height="14"></i></a>
                </div>
            </section>

        </main>

        <footer class="text-center py-4 border-top bg-white">
            <p class="text-muted text-xs fw-bold text-uppercase letter-spacing-2 m-0">Hospital ERP NextGen</p>
            <p class="text-muted text-xs fst-italic mt-1 m-0" style="font-size: 9px;">Designed for Performance & Aesthetics (Bootstrap 5 Version)</p>
        </footer>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
    const btnClockIn = document.getElementById('btn-clock-in');
    if(btnClockIn){
        btnClockIn.addEventListener('click', function() {
            if(confirm('ยืนยันการลงเวลาเข้างาน?')) {
                this.innerHTML = 'กำลังบันทึก...';
                this.classList.add('opacity-50', 'disabled');
                this.disabled = true;
                setTimeout(() => {
                    this.innerHTML = 'บันทึกแล้ว <i data-lucide="check" width="14" height="14" class="ms-1"></i>';
                    this.classList.remove('btn-white', 'text-primary', 'opacity-50', 'disabled');
                    this.classList.add('btn-success', 'text-white');
                    this.disabled = false;
                    lucide.createIcons();
                    alert('ลงเวลาสำเร็จ! (Mockup)');
                }, 1000);
            }
        });
    }
</script>