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
<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <?=$model->viewServiceRecordInfo()?>
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

                            <dt class="col-sm-4">วันที่แจ้ง:</dt>
                            <dd class="col-sm-8"><?=$model->viewCreateDateTime()?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">สถานะการซ่อม</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 65%;"
                                    aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="ms-2">65%</span>
                        </div>

                        <dl class="row mb-0">
                    <dt class="col-sm-4">ผู้รับผิดชอบ:</dt>
                    <dd class="col-sm-8"><?=$model->StackTeam()?></dd>

                    <dt class="col-sm-4">วันที่รับเรื่อง:</dt>
                    <dd class="col-sm-8"><?=$model->viewReceiveDate()?></dd>

                    <dt class="col-sm-4">วันที่เสร็จสิ้น:</dt>
                    <dd class="col-sm-8">17/10/2023</dd>

                    <dt class="col-sm-4">สถานะปัจจุบัน:</dt>
                    <dd class="col-sm-8">
                      <?=$model->repairStatus?->title ?? '-'?>
                    </dd>

                    
                </dl>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">ความคืบหน้า</h6>
                    </div>
                    <div class="card-body">
                        <div id="showTimeline"></div>
                    </div>
                </div>
            </div>

            <!-- <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">ติดต่อช่าง</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="contactMessage" class="form-label">ข้อความ</label>
                            <textarea class="form-control" id="contactMessage" rows="3"
                                placeholder="พิมพ์ข้อความถึงช่างที่รับผิดชอบงานซ่อมนี้..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>
                                ส่งข้อความ
                            </button>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>

<?php

use yii\helpers\Url;
$urlTimeline = Url::to(['/helpdesk2/service-record/timeline','helpdesk_id' => $model->id]);
$js = <<<JS

loadTimeline()

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