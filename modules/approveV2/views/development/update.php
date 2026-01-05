<?php
use yii\helpers\Html;
?>

<div class="card h-100">
                <div class="card-header bg-light p-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <strong><i class="bi bi-info-circle me-2"></i>รายละเอียดการพัฒนา</strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">เลขที่เอกสาร:</div>
                        <div class="col-md-9"><?= isset($model->development) ? ($model->development->document_id ?? 'ไม่ระบุ') : 'ไม่ระบุ' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">หัวข้อ:</div>
                        <div class="col-md-9 fw-bold"><?= isset($model->development) ? $model->development->topic : 'ไม่ระบุ' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ระยะเวลา:</div>
                        <div class="col-md-9">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= isset($model->development) ? $model->development->showDateRange() : 'ไม่ระบุ' ?>
                            <span class="badge bg-secondary ms-2">
                                <?= isset($model->development->data_json['time_slot']) ? $model->development->data_json['time_slot'] : 'ไม่ระบุ' ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ประเภทการพัฒนา:</div>
                        <div class="col-md-9">
                            <?= isset($model->development->data_json['development_type_name']) ? $model->development->data_json['development_type_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ระดับการพัฒนา:</div>
                        <div class="col-md-9">
                            <?= isset($model->development->data_json['development_level_name']) ? $model->development->data_json['development_level_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ลักษณะ:</div>
                        <div class="col-md-9">
                            <?= isset($model->development->data_json['development_go_type_name']) ? $model->development->data_json['development_go_type_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 text-muted">การเบิกเงิน:</div>
                        <div class="col-md-9">
                            <?= isset($model->development->data_json['claim_type_name']) ? $model->development->data_json['claim_type_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                </div>
            </div>

<?php 
echo $this->render('@app/modules/approve/views/approve/level_approve',[
    'model' => $model->development,'name' => 'development',
    ])
    ?>