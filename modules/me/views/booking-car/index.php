<?php
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
if($searchModel->car_type == 'general'){
    $this->title = ' ทะเบียนขอใช้รถทั่วไป'; 
}

if($searchModel->car_type == 'ambulance'){
    $this->title = 'ทะเบียนขอใช้รถพยาบาล'; 
}
?>


<?php $this->beginBlock('icon'); ?>
<?php if($searchModel->car_type == 'general'):?>
<i class="fa-solid fa-car fs-1"></i>
<?php endif;?>
<?php if($searchModel->car_type == 'ambulance'):?>
<i class="fa-solid fa-truck-medical fs-1"></i>
<?php endif;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
ระบบขอใช้ยานพาหนะ
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>



    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-truck me-2"></i>ระบบขอใช้รถยนต์
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="user-view-btn">หน้าผู้ใช้งาน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="admin-view-btn">หน้าผู้ดูแลระบบ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-person-circle me-1"></i>นายสมชาย ใจดี
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="#">
                            <i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content Area -->
    <div class="container my-4">
        <!-- User View -->
        <div id="user-view">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>ขอใช้รถยนต์</h3>
                <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestVehicleModal">
                    <i class="bi bi-plus-circle me-1"></i>สร้างคำขอใหม่
                </button> -->
                <?php echo Html::a('<i class="bi bi-plus-circle me-1"></i>สร้างคำขอใหม่',['/me/booking-car/create2','title' => 'แบบขอใช้รถยนต์'],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']])?>
            </div>

            <!-- Request History Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span
                class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>เลขที่</th>
                                    <th>วันที่ขอใช้</th>
                                    <th>จุดหมาย</th>
                                    <th>ประเภทรถ</th>
                                    <th>ลักษณะการใช้</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>REQ-20250101-001</td>
                                    <td>1 - 3 ม.ค. 2568</td>
                                    <td>โรงพยาบาลศิริราช</td>
                                    <td>รถพยาบาล</td>
                                    <td>ค้างคืน</td>
                                    <td><span class="badge bg-warning">รออนุมัติ</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>REQ-20250105-002</td>
                                    <td>5 ม.ค. 2568</td>
                                    <td>สำนักงานสาธารณสุขจังหวัด</td>
                                    <td>รถยนต์ส่วนตัว</td>
                                    <td>ไปกลับ</td>
                                    <td><span class="badge bg-success">อนุมัติแล้ว</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin View (Hidden by default) -->
        <div id="admin-view" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>จัดการขอใช้รถยนต์</h3>
                <div>
                    <button class="btn btn-outline-primary me-2">
                        <i class="bi bi-calendar-week me-1"></i>ปฏิทินการใช้รถ
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>ส่งออกรายงาน
                    </button>
                </div>
            </div>

            <!-- Vehicle Calendar View -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">ปฏิทินการใช้รถ</h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary disabled">มกราคม 2568</button>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%">รถ / วันที่</th>
                                    <th>1 ม.ค. 68</th>
                                    <th>2 ม.ค. 68</th>
                                    <th>3 ม.ค. 68</th>
                                    <th>4 ม.ค. 68</th>
                                    <th>5 ม.ค. 68</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-light">รถพยาบาล กข-1234</td>
                                    <td colspan="3" class="bg-warning bg-opacity-25">สมชาย (รพ.ศิริราช)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="table-light">รถตู้ ขค-5678</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="bg-success bg-opacity-25">วิชัย (สสจ.)</td>
                                </tr>
                                <tr>
                                    <td class="table-light">รถกระบะ งจ-9012</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="bg-primary bg-opacity-25">สมศรี (ประชุม)</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">คำขอรออนุมัติ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>เลขที่</th>
                                    <th>ผู้ขอ</th>
                                    <th>วันที่ขอใช้</th>
                                    <th>จุดหมาย</th>
                                    <th>ประเภทรถ</th>
                                    <th>ลักษณะการใช้</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>REQ-20250101-001</td>
                                    <td>นายสมชาย ใจดี</td>
                                    <td>1 - 3 ม.ค. 2568</td>
                                    <td>โรงพยาบาลศิริราช</td>
                                    <td>รถพยาบาล</td>
                                    <td>ค้างคืน</td>
                                    <td>
                                        <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveModal">
                                            <i class="bi bi-check-circle me-1"></i>อนุมัติ
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-circle me-1"></i>ปฏิเสธ
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>REQ-20250107-004</td>
                                    <td>นางสาวสมศรี รักดี</td>
                                    <td>7 ม.ค. 2568</td>
                                    <td>กรมอนามัย</td>
                                    <td>รถยนต์ราชการ</td>
                                    <td>ไปกลับ</td>
                                    <td>
                                        <button class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-check-circle me-1"></i>อนุมัติ
                                        </button>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="bi bi-x-circle me-1"></i>ปฏิเสธ
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Vehicle Modal -->
    <div class="modal fade" id="requestVehicleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แบบขอใช้รถยนต์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">วันที่เริ่มต้น</label>
                                <input type="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">เวลาออกเดินทาง</label>
                                <input type="time" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เวลากลับโดยประมาณ</label>
                                <input type="time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">สถานที่ไป</label>
                            <input type="text" class="form-control" placeholder="ระบุสถานที่" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">วัตถุประสงค์</label>
                            <textarea class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ลักษณะการใช้</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tripType" id="tripType1" value="dayTrip" checked>
                                    <label class="form-check-label" for="tripType1">
                                        ไปกลับ
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tripType" id="tripType2" value="overnight">
                                    <label class="form-check-label" for="tripType2">
                                        ค้างคืน
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ประเภทรถที่ต้องการใช้</label>
                                <select class="form-select" id="vehicleType" required>
                                    <option value="" selected disabled>เลือกประเภทรถ</option>
                                    <option value="official">รถยนต์ราชการ</option>
                                    <option value="personal">รถยนต์ส่วนตัว</option>
                                    <option value="ambulance">รถพยาบาล</option>
                                </select>
                            </div>
                        </div>
                        <div id="officialCarOptions">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">เลือกรถ</label>
                                    <select class="form-select">
                                        <option value="" selected disabled>เลือกรถ</option>
                                        <option value="1">รถตู้ ขค-5678</option>
                                        <option value="2">รถกระบะ งจ-9012</option>
                                        <option value="3">รถเก๋ง จฉ-3456</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">เลือกพนักงานขับรถ</label>
                                    <select class="form-select">
                                        <option value="" selected disabled>เลือกพนักงานขับรถ</option>
                                        <option value="1">นายสมหมาย ขับดี</option>
                                        <option value="2">นายใจเย็น รอบคอบ</option>
                                        <option value="3">นายรวดเร็ว ปลอดภัย</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="ambulanceOptions" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">เลือกรถพยาบาล</label>
                                    <select class="form-select">
                                        <option value="" selected disabled>เลือกรถพยาบาล</option>
                                        <option value="1">รถพยาบาล กข-1234</option>
                                        <option value="2">รถพยาบาล คง-5678</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">เลือกพนักงานขับรถ</label>
                                    <select class="form-select">
                                        <option value="" selected disabled>เลือกพนักงานขับรถ</option>
                                        <option value="1">นายแพทย์สมหมาย</option>
                                        <option value="2">นางพยาบาลรวดเร็ว</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ผู้ร่วมเดินทาง</label>
                            <textarea class="form-control" rows="2" placeholder="ระบุชื่อ-นามสกุล ตำแหน่ง คั่นด้วยเครื่องหมาย , (ถ้ามี)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary">ส่งคำขอ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">อนุมัติการขอใช้รถ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p>นายสมชาย ใจดี ขอใช้รถพยาบาลไปโรงพยาบาลศิริราช วันที่ 1 - 3 ม.ค. 2568</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">จัดสรรรถ</label>
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>วันที่</th>
                                    <th>รถ</th>
                                    <th>พนักงานขับรถ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1 ม.ค. 2568</td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option selected>รถพยาบาล กข-1234</option>
                                            <option>รถพยาบาล คง-5678</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option selected>นายแพทย์สมหมาย</option>
                                            <option>นางพยาบาลรวดเร็ว</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2 ม.ค. 2568</td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option selected>รถพยาบาล กข-1234</option>
                                            <option>รถพยาบาล คง-5678</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option>นายแพทย์สมหมาย</option>
                                            <option selected>นางพยาบาลรวดเร็ว</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3 ม.ค. 2568</td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option>รถพยาบาล กข-1234</option>
                                            <option selected>รถพยาบาล คง-5678</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option selected>นายแพทย์สมหมาย</option>
                                            <option>นางพยาบาลรวดเร็ว</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-success">ยืนยันการอนุมัติ</button>
                </div>
            </div>
        </div>
    </div>


    

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span
                    class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <p>
                <?php echo html::a('<i class="fa-solid fa-car-side"></i> ขอใช้รถยนต์ทั่วไป',['/me/booking-car/select-type','title' => '<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลขอใช้รถยนต์'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
            </p>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th scope="col">ผู้ขออนุมัติการลา</th>
                    <th scope="col">วันที่ไป</th>
                    <th scope="col">เวลาไป</th>
                    <th scope="col">ถึงวันที่</th>
                    <th scope="col">เวลากลับ</th>
                    <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
                    <th scope="col">สถานะ</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr>
                    <td><?php echo $item->reason;?></td>
                    <td><?php // echo $item->car->Avatar();?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?></td>
                    <td><?php echo $item->time_start?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($item->date_end, 'medium')?></td>
                    <td><?php echo $item->time_end?></td>
                    <td><?php echo $item->leader_id?></td>
                    <td><?php echo $item->bookingStatus->title ?? '-'?></td>
                    <td class="text-center">
                        <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/me/booking-car/view','id' => $item->id],['class' => 'open-modalx','data' => ['size' => 'modal-xl']])?>
                        <?php echo Html::a('<i class="fa-solid fa-pencil fa-2x text-warning"></i>',['/me/booking-car/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>

    </div>
</div>

<?php // Pjax::end(); ?>
<?php
$js = <<< JS

// Toggle between user and admin views
document.getElementById('user-view-btn').addEventListener('click', function() {
            document.getElementById('user-view').style.display = 'block';
            document.getElementById('admin-view').style.display = 'none';
            document.getElementById('user-view-btn').classList.add('active');
            document.getElementById('admin-view-btn').classList.remove('active');
        });
        
        document.getElementById('admin-view-btn').addEventListener('click', function() {
            document.getElementById('user-view').style.display = 'none';
            document.getElementById('admin-view').style.display = 'block';
            document.getElementById('user-view-btn').classList.remove('active');
            document.getElementById('admin-view-btn').classList.add('active');
        });
        
        // Toggle vehicle options based on selection
        document.getElementById('vehicleType').addEventListener('change', function() {
            const officialCarOptions = document.getElementById('officialCarOptions');
            const ambulanceOptions = document.getElementById('ambulanceOptions');
            
            if (this.value === 'ambulance') {
                officialCarOptions.style.display = 'none';
                ambulanceOptions.style.display = 'block';
            } else if (this.value === 'official') {
                officialCarOptions.style.display = 'block';
                ambulanceOptions.style.display = 'none';
            } else {
                officialCarOptions.style.display = 'none';
                ambulanceOptions.style.display = 'none';
            }
        });

JS;
$this->registerJs($js);
?>