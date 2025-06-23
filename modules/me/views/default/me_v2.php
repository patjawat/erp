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
$days = ThaiDateHelper::formatThaiDate(Date('Y-m-d'),'long');

$this->title = 'MyDashboard';
$this->params['breadcrumbs'][] = ['label' => 'MyDashboard', 'url' => ['/me']];
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gauge fs-4 text-primaryr"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>


                <!-- Welcome Section -->
                <div class="mb-4">
                    <div class="card shadow-sm rounded-4">
                        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h2 class="h6 fw-semibold text-dark mb-1">สวัสดี, <?=$me->fname?></h2>
                                <p class="text-muted mb-0">ยินดีต้อนรับกลับมา! นี่คือข้อมูลสรุปของคุณสำหรับวันนี้</p>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <div class="bg-light rounded-3 px-3 py-2 d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="me-2" style="width:20px;height:20px;color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="fw-medium text-primary" id="current-date"><?=$days?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
                    <!-- Attendance Stats -->
                    <div class="col">
                        <div class="card shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fs-6 fw-medium text-muted mb-0">การมาทำงาน</h3>
                                    <div class="p-2 bg-success bg-opacity-10 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="me-0" style="width:20px;height:20px;color:#16a34a" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline">
                                    <span class="fs-3 fw-bold text-dark">22</span>
                                    <span class="ms-2 text-muted">/ 23 วัน</span>
                                </div>
                                <div class="mt-2 d-flex align-items-center small">
                                    <span class="text-success fw-medium">95.7%</span>
                                    <span class="ms-1 text-muted">ของเดือนนี้</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Leave Stats -->
                    <div class="col">
                        <div class="card shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fs-6 fw-medium text-muted mb-0">วันลาคงเหลือ</h3>
                                    <div class="p-2 bg-primary bg-opacity-10 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="me-0" style="width:20px;height:20px;color:#2563eb" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline">
                                    <span class="fs-3 fw-bold text-dark"><?php echo $searchModel->sumLeavePermission()['use_days']?></span>
                                    <span class="ms-2 text-muted">/ <?php echo $searchModel->sumLeavePermission()['sum']?> วัน</span>
                                </div>
                                <div class="mt-2 d-flex align-items-center small">
                                    <span class="text-primary fw-medium">80%</span>
                                    <span class="ms-1 text-muted">วันลาพักผ่อนคงเหลือ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pending Approvals -->
                    <div class="col">
                        <div class="card shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fs-6 fw-medium text-muted mb-0">รออนุมัติ</h3>
                                    <div class="p-2 bg-warning bg-opacity-10 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="me-0" style="width:20px;height:20px;color:#d97706" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline">
                                    <span class="fs-3 fw-bold text-dark">3</span>
                                    <span class="ms-2 text-muted">รายการ</span>
                                </div>
                                <div class="mt-2 d-flex align-items-center small">
                                    <span class="text-warning fw-medium">1 รายการ</span>
                                    <span class="ms-1 text-muted">เร่งด่วน</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Unread Documents -->
                    <div class="col">
                        <div class="card shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="fs-6 fw-medium text-muted mb-0">หนังสือที่ยังไม่อ่าน</h3>
                                    <div class="p-2 bg-danger bg-opacity-10 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="me-0" style="width:20px;height:20px;color:#dc2626" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="d-flex align-items-baseline">
                                    <span class="fs-3 fw-bold text-dark">5</span>
                                    <span class="ms-2 text-muted">ฉบับ</span>
                                </div>
                                <div class="mt-2 d-flex align-items-center small">
                                    <span class="text-danger fw-medium">2 ฉบับ</span>
                                    <span class="ms-1 text-muted">ด่วนที่สุด</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content Grid -->
                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-lg-8 d-flex flex-column gap-4">
                        <!-- Attendance Chart -->
                        <div class="card shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h3 class="h5 fw-semibold text-dark mb-0">สถิติการเข้าทำงาน</h3>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary active">รายวัน</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary">รายสัปดาห์</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary">รายเดือน</button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <svg class="w-100" style="height:200px" viewBox="0 0 800 200">
                                        <!-- Grid Lines -->
                                        <line x1="50" y1="170" x2="750" y2="170" stroke="#e5e7eb" stroke-width="1"></line>
                                        <line x1="50" y1="130" x2="750" y2="130" stroke="#e5e7eb" stroke-width="1"></line>
                                        <line x1="50" y1="90" x2="750" y2="90" stroke="#e5e7eb" stroke-width="1"></line>
                                        <line x1="50" y1="50" x2="750" y2="50" stroke="#e5e7eb" stroke-width="1"></line>
                                        <!-- Y-axis Labels -->
                                        <text x="40" y="170" text-anchor="end" font-size="12" fill="#6b7280">0</text>
                                        <text x="40" y="130" text-anchor="end" font-size="12" fill="#6b7280">3</text>
                                        <text x="40" y="90" text-anchor="end" font-size="12" fill="#6b7280">6</text>
                                        <text x="40" y="50" text-anchor="end" font-size="12" fill="#6b7280">9</text>
                                        <!-- X-axis Labels -->
                                        <text x="100" y="190" text-anchor="middle" font-size="12" fill="#6b7280">จ</text>
                                        <text x="200" y="190" text-anchor="middle" font-size="12" fill="#6b7280">อ</text>
                                        <text x="300" y="190" text-anchor="middle" font-size="12" fill="#6b7280">พ</text>
                                        <text x="400" y="190" text-anchor="middle" font-size="12" fill="#6b7280">พฤ</text>
                                        <text x="500" y="190" text-anchor="middle" font-size="12" fill="#6b7280">ศ</text>
                                        <text x="600" y="190" text-anchor="middle" font-size="12" fill="#6b7280">ส</text>
                                        <text x="700" y="190" text-anchor="middle" font-size="12" fill="#6b7280">อา</text>
                                        <!-- Check-in Line (Blue) -->
                                        <path d="M100,90 L200,110 L300,70 L400,90 L500,80 L600,170 L700,170" stroke="#4f46e5" stroke-width="3" fill="none"></path>
                                        <circle cx="100" cy="90" r="4" fill="#4f46e5"></circle>
                                        <circle cx="200" cy="110" r="4" fill="#4f46e5"></circle>
                                        <circle cx="300" cy="70" r="4" fill="#4f46e5"></circle>
                                        <circle cx="400" cy="90" r="4" fill="#4f46e5"></circle>
                                        <circle cx="500" cy="80" r="4" fill="#4f46e5"></circle>
                                        <circle cx="600" cy="170" r="4" fill="#4f46e5"></circle>
                                        <circle cx="700" cy="170" r="4" fill="#4f46e5"></circle>
                                        <!-- Check-out Line (Purple) -->
                                        <path d="M100,130 L200,150 L300,120 L400,140 L500,130 L600,170 L700,170" stroke="#7c3aed" stroke-width="3" fill="none"></path>
                                        <circle cx="100" cy="130" r="4" fill="#7c3aed"></circle>
                                        <circle cx="200" cy="150" r="4" fill="#7c3aed"></circle>
                                        <circle cx="300" cy="120" r="4" fill="#7c3aed"></circle>
                                        <circle cx="400" cy="140" r="4" fill="#7c3aed"></circle>
                                        <circle cx="500" cy="130" r="4" fill="#7c3aed"></circle>
                                        <circle cx="600" cy="170" r="4" fill="#7c3aed"></circle>
                                        <circle cx="700" cy="170" r="4" fill="#7c3aed"></circle>
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-center gap-4">
                                    <div class="d-flex align-items-center">
                                        <span class="d-inline-block rounded-circle me-2" style="width:12px;height:12px;background:#4f46e5"></span>
                                        <span class="small text-muted">เวลาเข้างาน</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="d-inline-block rounded-circle me-2" style="width:12px;height:12px;background:#7c3aed"></span>
                                        <span class="small text-muted">เวลาออกงาน</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- To-Do List -->
                        <div class="card shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h3 class="h5 fw-semibold text-dark mb-0">รายการที่ต้องทำ</h3>
                                    <button id="add-task-btn" type="button" class="btn btn-sm btn-primary d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        เพิ่มรายการ
                                    </button>
                                </div>
                                <!-- Add Task Form (Hidden by default) -->
                                <div id="add-task-form" class="mb-4 p-3 bg-light rounded-3 d-none">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <input type="text" id="task-title" class="form-control" placeholder="ชื่อรายการ">
                                        </div>
                                        <div class="col-6">
                                            <label for="task-date" class="form-label small text-muted mb-1">วันที่</label>
                                            <input type="date" id="task-date" class="form-control">
                                        </div>
                                        <div class="col-6">
                                            <label for="task-time" class="form-label small text-muted mb-1">เวลา</label>
                                            <input type="time" id="task-time" class="form-control">
                                        </div>
                                        <div class="col-12">
                                            <label for="task-priority" class="form-label small text-muted mb-1">ความสำคัญ</label>
                                            <select id="task-priority" class="form-select">
                                                <option value="low">ต่ำ</option>
                                                <option value="medium">ปานกลาง</option>
                                                <option value="high">สูง</option>
                                            </select>
                                        </div>
                                        <div class="col-12 d-flex justify-content-end gap-2">
                                            <button id="cancel-task-btn" type="button" class="btn btn-sm btn-secondary">ยกเลิก</button>
                                            <button id="save-task-btn" type="button" class="btn btn-sm btn-primary">บันทึก</button>
                                        </div>
                                    </div>
                                </div>
                                <div id="task-list" class="vstack gap-3">
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <input type="checkbox" class="form-check-input mt-1">
                                            <div>
                                                <p class="fw-medium text-dark mb-1">ประชุมทีมพัฒนาระบบ</p>
                                                <div class="d-flex align-items-center small text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>วันนี้</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="ms-3 me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>13:30 น.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge bg-danger">สูง</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <input type="checkbox" class="form-check-input mt-1">
                                            <div>
                                                <p class="fw-medium text-dark mb-1">ส่งรายงานประจำเดือน</p>
                                                <div class="d-flex align-items-center small text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>พรุ่งนี้</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="ms-3 me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>16:00 น.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark">ปานกลาง</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <input type="checkbox" class="form-check-input mt-1">
                                            <div>
                                                <p class="fw-medium text-dark mb-1">ตรวจสอบอีเมลจากส่วนกลาง</p>
                                                <div class="d-flex align-items-center small text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>วันนี้</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="ms-3 me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>10:00 น.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge bg-success">ต่ำ</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">
                                        <div class="d-flex align-items-start gap-3">
                                            <input type="checkbox" class="form-check-input mt-1" checked>
                                            <div>
                                                <p class="fw-medium text-dark mb-1 text-decoration-line-through">อัพเดทข้อมูลในระบบ</p>
                                                <div class="d-flex align-items-center small text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="me-1" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>เมื่อวาน</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="badge bg-secondary">เสร็จสิ้น</span>
                                    </div>
                                </div>
                                <div class="mt-4 text-center">
                                    <button class="btn btn-link p-0 text-primary">ดูรายการทั้งหมด</button>
                                </div>
                            </div>
                        </div>
                        <!-- Official Documents -->
                         <div class="card shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h3 class="h5 fw-semibold text-dark mb-0">หนังสือราชการล่าสุด</h3>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary active">ทั้งหมด</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary">ยังไม่อ่าน</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary">ด่วน</button>
                                    </div>
                                </div>
                                <div class="vstack gap-3">
                        <div id="viewDocument"></div>
              </div>
                                <div class="mt-4 text-center">
                                    <?=html::a('ดูทั้งหมด',['/me/documents'],['class' => 'btn btn-link p-0 text-primary'])?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- Right Column -->
                    <div class="col-lg-4 d-flex flex-column gap-4">
                        <!-- Profile Summary -->
                        <div class="card shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="h5 fw-semibold text-dark mb-0">ข้อมูลส่วนตัว</h3>
                                    <button class="btn btn-link p-0 text-primary">แก้ไข</button>
                                </div>
                                <div class="d-flex flex-column align-items-center mb-3">
                                    <div class="rounded-circle bg-indigo bg-opacity-25 mb-2" style="width:96px;height:96px;overflow:hidden;">
                                        <img src="<?=$me->ShowAvatar()?>" alt="Profile" class="img-fluid rounded-circle">
                                    </div>
                                    <h6 class="fw-semibold text-dark mb-0"><?=$me->fullname?></h6>
                                    <div class="small text-muted"><?=$me->positionName()?></div>
                                </div>
                                <div class="vstack gap-2">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope me-2 text-muted"></i>
                                        <span class="small text-muted"><?=$me->email?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-telephone me-2 text-muted"></i>
                                        <span class="small text-muted"><?=$me->phone?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-building me-2 text-muted"></i>
                                        <span class="small text-muted"><?=$me->departmentName()?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar me-2 text-muted"></i>
                                        <span class="small text-muted">
                                            <?php 
                                        try {
                                            echo Yii::$app->thaiFormatter->asDate($me->joinDate(), 'medium');
                                        } catch (\Throwable $th) {

                                        }
                                        ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Leave Stats -->
                        <div class="card shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="h5 fw-semibold text-dark mb-0">สถิติการลา</h3>
                                    <button class="btn btn-link p-0 text-primary">ขอลา</button>
                                </div>
                                <div class="vstack gap-3">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="small fw-medium text-muted">ลาพักผ่อน</span>
                                            <span class="small text-muted">8/10 วัน</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" style="width: 80%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="small fw-medium text-muted">ลาป่วย</span>
                                            <span class="small text-muted">2/30 วัน</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: 6.7%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="small fw-medium text-muted">ลากิจ</span>
                                            <span class="small text-muted">1/10 วัน</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: 10%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="small fw-medium text-muted mb-2">ประวัติการลาล่าสุด</h6>
                                    <div class="vstack gap-1">
                                        <div class="d-flex align-items-center justify-content-between small">
                                            <span class="text-muted">ลาพักผ่อน</span>
                                            <span class="text-muted">15-16 ธ.ค. 2565</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between small">
                                            <span class="text-muted">ลาป่วย</span>
                                            <span class="text-muted">5 พ.ย. 2565</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between small">
                                            <span class="text-muted">ลากิจ</span>
                                            <span class="text-muted">20 ต.ค. 2565</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pending Approvals -->
                        <div class="card shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="h5 fw-semibold text-dark mb-0">รายการรออนุมัติ</h3>
                                    <span class="badge bg-soft-danger">3 รายการ</span>
                                </div>
                                <div class="vstack gap-2">
                                    <div class="p-3 border border-warning bg-warning bg-opacity-10 rounded-3 mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fw-medium text-dark mb-0">คำขอลาพักผ่อน</h6>
                                            <span class="badge bg-warning text-dark">รอดำเนินการ</span>
                                        </div>
                                        <div class="small text-muted mt-1">จาก: นางสาวสมหญิง รักดี (เจ้าหน้าที่ธุรการ)</div>
                                        <div class="small text-muted">วันที่: 15-17 ม.ค. 2566</div>
                                        <div class="mt-2 d-flex gap-2">
                                            <button class="btn btn-success btn-sm">อนุมัติ</button>
                                            <button class="btn btn-danger btn-sm">ไม่อนุมัติ</button>
                                        </div>
                                    </div>
                                    <div class="p-3 border border-warning bg-warning bg-opacity-10 rounded-3 mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fw-medium text-dark mb-0">คำขอเบิกวัสดุสำนักงาน</h6>
                                            <span class="badge bg-warning text-dark">รอดำเนินการ</span>
                                        </div>
                                        <div class="small text-muted mt-1">จาก: นายสมศักดิ์ มีทรัพย์ (เจ้าหน้าที่พัสดุ)</div>
                                        <div class="small text-muted">วันที่: 5 ม.ค. 2566</div>
                                        <div class="mt-2 d-flex gap-2">
                                            <button class="btn btn-success btn-sm">อนุมัติ</button>
                                            <button class="btn btn-danger btn-sm">ไม่อนุมัติ</button>
                                        </div>
                                    </div>
                                    <div class="p-3 border border-danger bg-danger bg-opacity-10 rounded-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="fw-medium text-dark mb-0">คำขอใช้ห้องประชุม</h6>
                                            <span class="badge bg-danger">เร่งด่วน</span>
                                        </div>
                                        <div class="small text-muted mt-1">จาก: นายวิชัย ใจเย็น (ผู้อำนวยการกอง)</div>
                                        <div class="small text-muted">วันที่: วันนี้ เวลา 13:00-16:00 น.</div>
                                        <div class="mt-2 d-flex gap-2">
                                            <button class="btn btn-success btn-sm">อนุมัติ</button>
                                            <button class="btn btn-danger btn-sm">ไม่อนุมัติ</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Messages -->
                        <div class="card shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h3 class="h5 fw-semibold text-dark mb-0">ข้อความล่าสุด</h3>
                                    <span class="badge bg-danger">5 ข้อความใหม่</span>
                                </div>
                                <div class="vstack gap-2">
                                    <div class="d-flex align-items-start p-3 border rounded-3 bg-primary bg-opacity-10">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center text-primary fw-bold" style="width:40px;height:40px;">ว</div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-medium text-dark mb-0">วิชัย ใจเย็น</h6>
                                                <span class="small text-muted">09:45</span>
                                            </div>
                                            <div class="small text-muted mt-1">ขอเชิญประชุมด่วนเรื่องระบบใหม่ ห้องประชุม 2 เวลา 13:00 น.</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start p-3 border rounded-3 bg-primary bg-opacity-10">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-purple bg-opacity-25 d-flex align-items-center justify-content-center text-purple fw-bold" style="width:40px;height:40px;">ส</div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-medium text-dark mb-0">สมหญิง รักดี</h6>
                                                <span class="small text-muted">เมื่อวาน</span>
                                            </div>
                                            <div class="small text-muted mt-1">ส่งเอกสารการลาให้แล้วนะคะ รบกวนช่วยตรวจสอบด้วยค่ะ</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start p-3 border rounded-3">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-success bg-opacity-25 d-flex align-items-center justify-content-center text-success fw-bold" style="width:40px;height:40px;">ป</div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-medium text-dark mb-0">ประภา สุขใจ</h6>
                                                <span class="small text-muted">2 วันที่แล้ว</span>
                                            </div>
                                            <div class="small text-muted mt-1">รายงานสรุปประจำเดือนเสร็จเรียบร้อยแล้วค่ะ</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 text-center">
                                    <button class="btn btn-link p-0 text-primary">ดูทั้งหมด</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



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

<?php // Pjax::end(); ?>