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
                    <dd class="col-sm-8"><?=$model->fsn_number?></dd>

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
                <dl class="row mb-0">
                    <dt class="col-sm-4">ผู้รับผิดชอบ:</dt>
                    <dd class="col-sm-8">นายช่าง มือดี</dd>

                    <dt class="col-sm-4">วันที่เริ่มซ่อม:</dt>
                    <dd class="col-sm-8">16/10/2023</dd>

                    <dt class="col-sm-4">คาดว่าจะเสร็จ:</dt>
                    <dd class="col-sm-8">17/10/2023</dd>

                    <dt class="col-sm-4">สถานะปัจจุบัน:</dt>
                    <dd class="col-sm-8">
                        <select class="form-select form-select-sm">
                            <option>รอดำเนินการ</option>
                            <option selected="">กำลังดำเนินการ</option>
                            <option>รออะไหล่</option>
                            <option>เสร็จสิ้น</option>
                            <option>ยกเลิก</option>
                        </select>
                    </dd>

                    
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">บันทึกการซ่อม</h6>
            </div>
            <div class="card-body">
                <div id="showTimeline"></div>

                <div class="mt-3">
                    <div id="showFormFormServiceRecord"></div>
                    <!-- <div class="input-group">
                        <input type="text" class="form-control" placeholder="เพิ่มบันทึกการซ่อม...">
                        <button class="btn btn-primary" type="button">บันทึก</button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">อะไหล่ที่ใช้</h6>
                <button class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    เพิ่มอะไหล่
                </button>
            </div>
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
                            <!-- <tr>
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
                            </tr> -->
                        </tbody>
                        <!-- <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">รวมทั้งสิ้น:</th>
                                <th>2,500 บาท</th>
                                <td></td>
                            </tr>
                        </tfoot> -->
                    </table>
                </div>
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

use yii\helpers\Url;
$urlFormServiceRecord = Url::to(['/helpdesk2/service-record/create','helpdesk_id' => $model->id]);
$urlTimeline = Url::to(['/helpdesk2/service-record/timeline','helpdesk_id' => $model->id]);
$js = <<<JS

loadFormServiceRecord()
loadTimeline()
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

JS;

$this->registerJs($js);
?>