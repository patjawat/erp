<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 2px;
    background-color: #e2e8f0;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -2.5rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background-color: white;
    border: 2px solid var(--bs-primary);
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-date {
    font-size: 0.875rem;
    color: var(--bg-secondary);
}

.timeline-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-body {
    font-size: 0.875rem;
}
</style>
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge status-in-progress fs-6"><?=$item->repairStatus?->title ?? '-'?></span>
            <div>
                <span class="text-secondary">วันที่แจ้ง: <?=$model->viewCreateDateTime()?></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">ข้อมูลการแจ้งซ่อม</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">ประเภทอุปกรณ์:</dt>
                    <dd class="col-sm-8"><?=$model->deviceType->title ?? '-'?></dd>

                    <dt class="col-sm-4">รหัสอุปกรณ์:</dt>
                    <dd class="col-sm-8"><?=$model->asset_number?></dd>

                    <dt class="col-sm-4">ปัญหา:</dt>
                    <dd class="col-sm-8"><?=$model->title?></dd>

                    <dt class="col-sm-4">สถานที่:</dt>
                    <dd class="col-sm-8"><?=$model->data_json['location']?></dd>

                    <dt class="col-sm-4">ความเร่งด่วน:</dt>
                    <dd class="col-sm-8"><?=$model->viewUrgent()['view']?></dd>

                    <dt class="col-sm-4">ผู้แจ้ง:</dt>
                    <dd class="col-sm-8"><?=$model->emp->getInfo()['avatar']?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">ข้อมูลการซ่อม</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4">ผู้รับผิดชอบ:</dt>
                    <dd class="col-sm-8"><?=$model->StackTeam()?></dd>

                    <dt class="col-sm-4">วันที่รับเรื่อง:</dt>
                    <dd class="col-sm-8"><?=$model->viewReceiveDate()?></dd>

                </dl>
                <div id="showFormFormStatus"></div>
            </div>
        </div>
    </div>

    <div class="col-12">

    </div>


    <div class="container">
        <div class="modern-tabs mb-5">
            <ul class="nav" id="filledTabs" role="tablist">
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
                <div class="tab-pane fade" id="repairParts" role="tabpanel">
                    <!-- <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>รหัสอะไหล่</th>
                                            <th>รายการ</th>
                                            <th>จำนวน</th>
                                            <th>ราคาต่อหน่วย</th>
                                            <th>รวม</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>AC-GAS-002</td>
                                            <td>น้ำยาแอร์ R32</td>
                                            <td>1</td>
                                            <td>500</td>
                                            <td>500</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">รวมทั้งสิ้น:</th>
                                            <th>2,500 บาท</th>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>


    <div class="col-12">

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

use yii\helpers\Url;
$urlFormServiceRecord = Url::to(['/helpdesk/service-record/create','helpdesk_id' => $model->id]);
$urlTimeline = Url::to(['/helpdesk/service-record/timeline','helpdesk_id' => $model->id]);

$urlFormTeam = Url::to(['/helpdesk/team/create','helpdesk_id' => $model->id]);
$urllistTeam = Url::to(['/helpdesk/team/list','helpdesk_id' => $model->id]);
$urllistTeam = Url::to(['/helpdesk/team/list','helpdesk_id' => $model->id]);
$urlFormStatus= Url::to(['/helpdesk/service/update-status','id' => $model->id]);
$js = <<<JS

loadFormServiceRecord()
loadTimeline()
loadFormTeam()
loadListTeam()
loadFormStatus()

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

JS;

$this->registerJs($js);
?>