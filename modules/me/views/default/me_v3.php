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

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="layout-grid"></i>  
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/me/menu', ['active' => 'dashboard']) ?>

<?php $this->endBlock(); ?>



<!-- Welcome Section -->
<div class="mb-4">
    <div class="card shadow-sm rounded-4">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div>
                <h2 class="h6 text-dark mb-1">สวัสดี, <?= $me->fname ?></h2>
                <p class="text-muted mb-0">ยินดีต้อนรับกลับมา! นี่คือข้อมูลสรุปของคุณสำหรับวันนี้</p>
            </div>
            <div class="mt-3 mt-md-0">
                <div class="bg-light rounded-3 px-3 py-2 d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="me-2" style="width:20px;height:20px;color:#6366f1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="fw-medium text-primary" id="current-date"><?= $days ?></span>
                </div>
            </div>
        </div>
    </div>
</div>






<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
                        <div class="position-absolute top-0 end-0 p-3 opacity-10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary">
                                <path d="M12 6v6l4 2"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                        </div>
                        <div class="card-body p-4 position-relative" style="z-index: 1;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold text-dark mb-0">ลงเวลางาน</h5>
                                <span class="badge rounded-pill bg-light text-secondary fw-bold text-uppercase d-flex align-items-center gap-2 px-3 py-2">
                                    <span class="rounded-circle bg-secondary" style="width: 8px; height: 8px;"></span>ยังไม่เข้างาน
                                </span>
                            </div>
                            <div class="bg-light border border-dashed rounded-4 p-3 mb-4">
                                <div class="d-flex gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary mt-1">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <p class="small text-muted mb-0">Nexus Headquarters, Rama 9, Bangkok (สำนักงานใหญ่)</p>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 py-3 rounded-4 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m10 17 5-5-5-5"></path>
                                    <path d="M15 12H3"></path>
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                </svg>
                                ลงชื่อเข้างาน
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold text-dark mb-0">สรุปวันลาคงเหลือ</h5>
                            <button class="btn btn-sm btn-light text-primary fw-bold px-3">รายละเอียด</button>
                        </div>
                        <div class="d-flex align-items-center gap-4 flex-grow-1">
                            <div class="position-relative d-flex align-items-center justify-content-center" style="width: 112px; height: 112px;">
                                <svg viewBox="0 0 36 36" class="w-100 h-100">
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="3" />
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#3b82f6" stroke-width="3" stroke-dasharray="73, 100" />
                                </svg>
                                <div class="position-absolute text-center">
                                    <div class="h4 fw-bold mb-0">11</div>
                                    <div class="small text-muted text-uppercase" style="font-size: 10px;">วัน</div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">ลาพักร้อน</span>
                                        <span class="text-muted">11/15</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 73.3%"></div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">ลาป่วย</span>
                                        <span class="text-muted">28/30</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 93.3%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">ลากิจ</span>
                                        <span class="text-muted">6/7</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 85.7%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold text-dark mb-4">บริการของพนักงาน</h5>
                <div class="row g-3 row-cols-2 row-cols-sm-4">
                    <div class="col">
                        <button class="btn btn-light w-100 border-0 rounded-4 p-3 d-flex flex-column align-items-center transition">
                            <div class="rounded-4 bg-success-subtle p-3 mb-2 text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 19h6"></path>
                                    <path d="M16 2v4"></path>
                                    <path d="M19 16v6"></path>
                                    <path d="M21 12.598V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8.5"></path>
                                    <path d="M3 10h18"></path>
                                    <path d="M8 2v4"></path>
                                </svg>
                            </div>
                            <span class="small fw-medium text-secondary">ส่งใบลา</span>
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-light w-100 border-0 rounded-4 p-3 d-flex flex-column align-items-center">
                            <div class="rounded-4 bg-primary-subtle p-3 mb-2 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                                    <circle cx="7" cy="17" r="2"></circle>
                                    <path d="M9 17h6"></path>
                                    <circle cx="17" cy="17" r="2"></circle>
                                </svg>
                            </div>
                            <span class="small fw-medium text-secondary">จองรถบริษัท</span>
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-light w-100 border-0 rounded-4 p-3 d-flex flex-column align-items-center">
                            <div class="rounded-4 bg-info-subtle p-3 mb-2 text-info text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <span class="small fw-medium text-secondary text-nowrap">จองห้องประชุม</span>
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn btn-light w-100 border-0 rounded-4 p-3 d-flex flex-column align-items-center">
                            <div class="rounded-4 bg-warning-subtle p-3 mb-2 text-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"></path>
                                </svg>
                            </div>
                            <span class="small fw-medium text-secondary">แจ้งซ่อมไอที</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">ประวัติการลงเวลาล่าสุด</h5>
                    <button class="btn btn-link btn-sm text-decoration-none text-primary">ดูทั้งหมด</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="text-muted small">
                            <tr>
                                <th class="border-0 font-weight-normal">วันที่</th>
                                <th class="border-0 font-weight-normal">เข้างาน</th>
                                <th class="border-0 font-weight-normal">ออกงาน</th>
                                <th class="border-0 font-weight-normal">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="small">
                                <td class="py-3 text-secondary">2024-05-20</td>
                                <td class="py-3 fw-bold text-dark">08:55</td>
                                <td class="py-3 fw-bold text-dark">18:05</td>
                                <td class="py-3"><span class="badge rounded-pill bg-success-subtle text-success px-2 py-1">ปกติ</span></td>
                            </tr>
                            <tr class="small">
                                <td class="py-3 text-secondary">2024-05-19</td>
                                <td class="py-3 fw-bold text-dark">09:15</td>
                                <td class="py-3 fw-bold text-dark">18:30</td>
                                <td class="py-3"><span class="badge rounded-pill bg-warning-subtle text-warning px-2 py-1">สาย</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="vstack gap-4">
                <div class="card border-0 shadow-lg rounded-4 text-white bg-primary p-4" style="background: linear-gradient(135deg, #2563eb, #4338ca);">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-info">
                            <path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"></path>
                        </svg>
                        <h5 class="fw-bold mb-0">Nexus AI Assistant</h5>
                    </div>
                    <p class="small text-white-50 mb-4">สอบถามข้อมูลสวัสดิการ นโยบายบริษัท หรือการใช้งานระบบได้ทันที</p>
                    <div class="position-relative">
                        <input type="text" class="form-control bg-white bg-opacity-25 border-0 text-white placeholder-white py-2.5 rounded-3" placeholder="พิมพ์คำถามของคุณ..." style="padding-right: 40px;">
                        <button class="btn btn-link text-white position-absolute end-0 top-50 translate-middle-y">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m22 2-7 20-4-9-9-4Z"></path>
                                <path d="M22 2 11 13"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold text-dark mb-0">การแจ้งเตือน</h5>
                        <span class="badge rounded-pill bg-danger-subtle text-danger px-2 py-1">2 ใหม่</span>
                    </div>
                    <div class="vstack gap-3">
                        <div class="d-flex gap-3 p-2 rounded-3 hover-bg-light cursor-pointer">
                            <div class="text-success mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 small">อนุมัติคำขอลาพักร้อน</h6>
                                <p class="small text-muted mb-1">คำขอลาพักร้อนวันที่ 25-26 พ.ค. ได้รับการอนุมัติแล้ว</p>
                                <span class="text-muted" style="font-size: 10px;">2 ชม. ที่แล้ว</span>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-outline-light text-muted w-100 mt-4 py-2 border rounded-3 small">ดูแจ้งเตือนทั้งหมด</button>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold text-dark mb-4">รอดำเนินการ (Approvals)</h5>
                    <div class="vstack gap-3">
                        <div class="p-3 bg-light border border-light rounded-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 small text-truncate" style="max-width: 150px;">ขอซื้ออุปกรณ์สำนักงาน</h6>
                                <span class="badge bg-warning-subtle text-warning text-uppercase" style="font-size: 9px;">รออนุมัติ</span>
                            </div>
                            <div class="small text-muted mb-3 d-flex align-items-center gap-1">
                                <span>วิภา พรหมมา</span> • <span>2024-05-18</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm flex-grow-1 fw-bold rounded-3">อนุมัติ</button>
                                <button class="btn btn-outline-secondary btn-sm flex-grow-1 fw-bold rounded-3 bg-white">ปฏิเสธ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Stats Cards -->


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