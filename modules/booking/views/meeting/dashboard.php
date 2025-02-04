<?php
use yii\helpers\Url;
$this->title = "Dashboard"
?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-chart-pie fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>


<div class="row">
    <div class="col-3">
            <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold">0</span>
                        <div class="relative">
                        <i class="fa-solid fa-calendar-day fs-1"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <h6>จำนวนการใช้บริการรวมวันนี้</h6>
                    </div>
                </div>
            </div>
    </div>
    <div class="col-3">
            <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold">0</span>
                        <div class="relative">
                            <i class="fa-regular fa-calendar fs-1"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <h6> จำนวนการใช้บริการรวม เดือนนี้</h6>
                    </div>
                </div>
            </div>
    </div>
    <div class="col-3">
    <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold">0</span>
                        <div class="relative">
                        <i class="fa-solid fa-calendar-check fs-1"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <h6> จำนวนการใช้บริการรวม ปีนี้</h6>
                    </div>
            </div>
        </div>
    </div>
    <div class="col-3">
    <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold">0</span>
                        <div class="relative">
                        <i class="fa-regular fa-calendar-check fs-1"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <h6>อัตราการอนุมัติต่อการขอใช้ทั้งหมด</h6>
                    </div>
                </div>
            </div>
    </div>
</div>

<div class="row">
    <div class="col-6">
    <div class="card">
            <div class="card-body">
                <h6><i class="fa-solid fa-calendar-day"></i> รายการประชุมวันนี้ </h6>
                <table class="table table-striped table-bordered">
                    <tbody class="align-middle table-group-divider">
                        <tr class="text-center" style="background:#dcdcdc;">
                            <th width="110px">ห้องประชุม</th>
                            <th>รายการ</th>
                            <th width="150px">วันที่ และเวลา</th>
                            <th width="80px">สถานะ</th>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td>ประชุม กกบ.รพร.</td>
                            <td class="text-center p-1">4 ก.พ. 2568 <br>
                                14:00-14:00 น.</td>
                            <td class="text-center"><span class="badge badge-success">อนุมัติ</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
    <div class="col-6">
    <div class="card">
            <div class="card-body">
            <h6><i class="fa-regular fa-calendar-plus"></i> รายการประชุมพรุ่งนี้ </h6>
                <table class="table table-striped table-bordered">
                    <tbody class="align-middle table-group-divider">
                        <tr class="text-center" style="background:#dcdcdc;">
                            <th width="110px">ห้องประชุม</th>
                            <th>รายการ</th>
                            <th width="150px">วันที่ และเวลา</th>
                            <th width="80px">สถานะ</th>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td>ประชุม กกบ.รพร.</td>
                            <td class="text-center p-1">4 ก.พ. 2568 <br>
                                14:00-14:00 น.</td>
                            <td class="text-center"><span class="badge badge-success">อนุมัติ</span></td>
                        </tr>
                    </tbody>
                </table>
 
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <?php echo $this->render('list_room',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ])?>
    </div>
</div>

<h6 class="text-center">ข้อมูลแผนภูมิภาพการใช้บริการห้องประชุม</h6>
<div class="row">
<div class="col-6">
    <div class="card">
        <div class="card-body">
            <h6>การใช้บริการห้องประชุมแยกเป็นรายเดือน (จำแนกตามวันอนุมัติ)</h6>
        </div>
    </div>
    
</div>
<div class="col-6">
<div class="card">
        <div class="card-body">
            <h6>การใช้บริการห้องประชุมแยกเป็นห้อง (จำแนกตามวันอนุมัติ)</h6>
        </div>
    </div>
    
</div>
</div>