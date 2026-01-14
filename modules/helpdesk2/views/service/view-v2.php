<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = '';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนงานซ่อม', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนงานซ่อม';

?>


<!-- Header -->
<div class="card">
    <div class="card-body">

        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="fw-bold mb-1">เลขที่ใบสั่งซ่อม: <span class="text-primary"><?= $model->repair_number ?></span></h3>
                <p class="text-muted mb-0">ปรับปรุงล่าสุดเมื่อ: <?= $model->viewUpdated()['date'] ?> | <?= $model->viewUpdated()['time'] ?></p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <span class="me-2">สถานะ <?= $model->viewStatus() ?></span>
                <button class="btn btn-outline-secondary me-2"><i class="bi bi-printer"></i></button>
                <button class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> ยกเลิกงาน</button>
            </div>
        </div>

    </div>
</div>

<div class="row g-4">
    <!-- 1. ข้อมูลผู้แจ้งและอุปกรณ์ (Context) -->
    <div class="col-xl-3">
        <div class="card h-100">
            <div class="card-header p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <i class="bi bi-person-circle"></i> ข้อมูลผู้แจ้งซ่อม
                    </div>
                    <?= $model->viewCreateDateTime() ?>
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-light rounded-3 p-3 me-3">
                        <?php
                        echo Html::img('@web/img/loading.gif', [
                            'class' => 'rounded-4 me-3 shadow lazyload',
                            'width' => '40',
                            'height' => '40',
                            'data' => [
                                'expand' => '-20',
                                'sizes' => 'auto',
                                'src' => $model->emp->getImg()
                            ]
                        ]); ?>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?= $model->emp->fullname ?></h6>
                        <small class="text-muted"><?= $model->emp->departmentName() ?></small>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <p class="fw-bold text-danger mb-1"><i class="fa-solid fa-triangle-exclamation"></i> อาการเสียที่ระบุ:</p>
                    <p class="mb-0 text-dark"><?= $model->title ?></p>
                </div>

                <div class="section-title"><i class="bi bi-geo-alt"></i> สถานที่/ทรัพย์สิน</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">รหัสทรัพย์สิน:</span>
                        <span class="fw-medium"><?= $model->asset_name ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">สถานที่:</span>
                        <span class="fw-medium"><?= $model->data_json['location'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">ความเร่งด่วน:</span>
                        <span class="fw-medium"><?= $model->viewUrgent()['view'] ?></span>
                    </li>

                </ul>
                <div>

                    <div class="section-title">
                        <div class="d-flex justify-content-between">
                            <div><i class="bi bi-geo-alt"></i> วันที่รับเรื่อง </div>
                            <?= $model->viewReceiveDate() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. ฟอร์มปฏิบัติงาน (Action) -->
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <ul class="nav nav-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="pill" type="button">1. บันทึกผลการซ่อม</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="pill" type="button">2. แนบรูปถ่ายงาน</button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ประเภทงานซ่อม</label>
                        <select class="form-select">
                            <option>งานอาคาร/โครงสร้าง</option>
                            <option>งานไฟฟ้า</option>
                            <option>งานประปา</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ระดับความสำคัญหลังตรวจสอบ</label>
                        <select class="form-select">
                            <option>ปกติ</option>
                            <option class="text-danger">เร่งด่วน</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">สาเหตุของปัญหา (Root Cause)</label>
                        <textarea class="form-control" rows="2" placeholder="ระบุสาเหตุที่พบจากการตรวจสอบหน้างาน..."></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">รายละเอียดการแก้ไข</label>
                        <textarea class="form-control" rows="4" placeholder="ระบุขั้นตอนการแก้ไขงานซ่อมอย่างละเอียด..."></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วันที่เริ่มซ่อม</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วันที่ซ่อมเสร็จ</label>
                        <input type="date" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- ผู้รับผิดชอบงาน -->
        <div class="card">
            <div class="card-body p-4">
                <div class="section-title"><i class="bi bi-people"></i> ทีมช่างผู้รับผิดชอบ</div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <select class="form-select">
                            <option>ค้นหาชื่อช่างหรือรหัสพนักงาน...</option>
                            <option>สมชาย ขยันซ่อม (ช่างอาคาร)</option>
                            <option>วิชัย งานดี (ช่างเทคนิค)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-primary w-100">+ เพิ่มรายชื่อ</button>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-inline-block bg-light border rounded-pill px-3 py-1 me-2 mb-2">
                        <small>สมชาย ขยันซ่อม <i class="bi bi-x-circle-fill ms-2 text-muted" style="cursor:pointer"></i></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. อะไหล่และค่าใช้จ่าย (Resources) -->
    <div class="col-xl-3">
        <div class="card mb-4">
            <div class="card-body">
                <div class="section-title"><i class="bi bi-currency-dollar"></i> บันทึกค่าใช้จ่าย</div>

                <!-- ส่วนกรอกอะไหล่ -->
                <div class="mb-3">
                    <label class="form-label small">รายการอะไหล่/วัสดุ</label>
                    <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control" placeholder="ชื่อรายการ">
                        <input type="number" class="form-control" style="max-width: 60px;" placeholder="จำนวน">
                        <button class="btn btn-primary" type="button"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                <div class="section-title small text-muted mb-2">รายการที่เลือก</div>
                <div class="cost-item shadow-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-dark">ลูกล้อบานเลื่อน (คู่)</span>
                        <button class="btn btn-link btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="text-muted" style="font-size: 0.75rem;">ราคา 175.00 x 2</span>
                        <span class="text-primary small fw-bold">350.00 ฿</span>
                    </div>
                </div>

                <div class="cost-item shadow-none">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-dark">น้ำยาหล่อลื่น WD-40</span>
                        <button class="btn btn-link btn-sm text-danger p-0"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="text-muted" style="font-size: 0.75rem;">ราคา 120.00 x 1</span>
                        <span class="text-primary small fw-bold">120.00 ฿</span>
                    </div>
                </div>

                <!-- ส่วนกรอกค่าแรง -->
                <div class="mt-4 mb-3">
                    <label class="form-label small">ค่าแรงปฏิบัติงาน (ถ้ามี)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">฿</span>
                        <input type="number" class="form-control" placeholder="0.00">
                    </div>
                </div>

                <hr class="my-3 border-dashed">

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">รวมค่าอะไหล่:</span>
                    <span class="fw-medium small">470.00 ฿</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">ภาษี (7%):</span>
                    <span class="fw-medium small">32.90 ฿</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2">
                    <span class="fw-bold">ยอดสุทธิ:</span>
                    <span class="fw-bold text-success fs-5">502.90 ฿</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title"><i class="bi bi-shield-check"></i> การตรวจรับงาน</div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check1">
                    <label class="form-check-label small" for="check1">ทดสอบการใช้งานหลังซ่อม</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="check2">
                    <label class="form-check-label small" for="check2">ทำความสะอาดพื้นที่งาน</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">

        <ul class="nav nav-pills" id="filledTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home"
                    type="button" role="tab">
                    บันทึกการซ่อม
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="team-tab" data-bs-toggle="tab" data-bs-target="#team" type="button"
                    role="tab">
                    ผู้ร่วมดำเนินงาน
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="repairParts-tab" data-bs-toggle="tab" data-bs-target="#repairParts"
                    type="button" role="tab">
                    อะไหล่ที่ใช้
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="repairExpenses-tab" data-bs-toggle="tab" data-bs-target="#repairExpenses"
                    type="button" role="tab">
                    ค่าใช้จ่าย
                </button>
            </li>
        </ul>
        <div class="tab-content" id="filledTabsContent">
            <div class="tab-pane fade show active" id="home" role="tabpanel">

                <div class="card">
                    <div class="card-body">
                        <div class="mt-3">
                            <div id="showFormFormServiceRecord"></div>
                        </div>
                        <div id="showTimeline"></div>
                    </div>
                </div>

            </div>
            <div class="tab-pane fade" id="team" role="tabpanel">
                <div id="showFormTeam"></div>
                <div id="showListTeam"></div>
            </div>
            <div class="tab-pane fade" id="repairExpenses" role="tabpanel">
                <div id="showListExpenses"></div>
            </div>
            <div class="tab-pane fade" id="repairParts" role="tabpanel">

            </div>
        </div>
    </div>



    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">รูปภาพประกอบ</h6>
            </div>
            <div class="card-body">
                <?= $model->imageRequest ?>

            </div>
        </div>
    </div>
</div>
<?php

$urlFormServiceRecord = Url::to(['/helpdesk/service-record/create', 'helpdesk_id' => $model->id]);
$urlTimeline = Url::to(['/helpdesk/service-record/timeline', 'helpdesk_id' => $model->id]);
$urlExpenses = Url::to(['/helpdesk/expenses', 'helpdesk_id' => $model->id]);

$urlFormTeam = Url::to(['/helpdesk/team/create', 'helpdesk_id' => $model->id]);
$urllistTeam = Url::to(['/helpdesk/team/list', 'helpdesk_id' => $model->id]);
$urllistTeam = Url::to(['/helpdesk/team/list', 'helpdesk_id' => $model->id]);
$urlFormStatus = Url::to(['/helpdesk/service/update-status', 'id' => $model->id]);
$js = <<<JS

loadFormServiceRecord()
loadTimeline()
loadFormTeam()
loadListTeam()
loadFormStatus()
loadExpenses()

function loadFormStatus()
{
    $.ajax({
        type: "get",
        url: "$urlFormStatus",
        dataType: "json",
        success: function (response) {
            $('#showFormFormStatus').html(response.content)
        }
    });
}


function loadFormServiceRecord()
{
    $.ajax({
        type: "get",
        url: "$urlFormServiceRecord",
        dataType: "json",
        success: function (response) {
            $('#showFormFormServiceRecord').html(response.content)
        }
    });
}

function loadTimeline()
{
    $.ajax({
        type: "get",
        url: "$urlTimeline",
        dataType: "json",
        success: function (response) {
            $('#showTimeline').html(response.content)
        }
    });
}

function loadFormTeam()
{
    $.ajax({
        type: "get",
        url: "$urlFormTeam",
        dataType: "json",
        success: function (response) {
            $('#showFormTeam').html(response.content)
        }
    });
}

function loadListTeam()
{
    $.ajax({
        type: "get",
        url: "$urllistTeam",
        dataType: "json",
        success: function (response) {
            $('#showListTeam').html(response.content)
            console.log('loadsuccess');
            
        }
    });
}
function loadExpenses()
{
    $.ajax({
        type: "get",
        url: "$urlExpenses",
        dataType: "json",
        success: function (response) {
            $('#showListExpenses').html(response.content)
            console.log('loadsuccess');
            
        }
    });
}

JS;

$this->registerJs($js);
?>