<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();
/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */

$this->title = 'รายละเอียดการพัฒนาบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'การพัฒนาบุคลากร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container-fluid">

    <!-- สถานะการพัฒนา -->


    <!-- ข้อมูลทั่วไป -->
    <div class="row">
        <div class="col-md-3">
            <div class="card h-100 bg-light">
                <div class="card-body text-center">
                    <div class="avatar-circle mb-3 mx-auto">
                        <?= isset($model->createdByEmp) ? Html::img($model->createdByEmp->showAvatar(), ['class' => 'avatar avatar-xl border border-primary-subtl border-1 lazyload', 'data' => ['sizes' => 'auto', 'src' => $model->createdByEmp->showAvatar()]]) : Html::img('/path/to/default/avatar.png', ['class' => 'avatar avatar-xl border border-primary-subtl border-1 lazyload']) ?>
                    </div>
                    <h5><?= $model->createdByEmp->fullname ?? 'ไม่ระบุ' ?></h5>
                    <p class="text-muted mb-1"><?= $model->createdByEmp->position->name ?? 'ไม่ระบุตำแหน่ง' ?></p>
                    <p class="text-muted"><?= $model->createdByEmp->department->name ?? 'ไม่ระบุแผนก' ?></p>
                    <p>ผู้บันทึกข้อมูล <?=$model->viewCreated()?></p>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card h-100">
                <div class="card-header bg-light p-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <strong><i class="bi bi-info-circle me-2"></i>รายละเอียดการพัฒนา</strong>
                        <?= $me->id == $model->emp_id ? Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข'.$this->title,['/me/development/update','id' => $model->id,'title' => '<i class="bi bi-mortarboard-fill me-2"></i>แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร','title' => '<i class="bi bi-mortarboard-fill me-2"></i>แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร'],['class' => 'btn btn-primary rounded-pill shadow open-modal-x','data' => ['size' => 'modal-xl']]) : ''?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">เลขที่เอกสาร:</div>
                        <div class="col-md-9"><?= $model->document_id ?? 'ไม่ระบุ' ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">หัวข้อ:</div>
                        <div class="col-md-9 fw-bold"><?= $model->topic ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ระยะเวลา:</div>
                        <div class="col-md-9">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?=$model->showDateRange()?>
                            <span class="badge bg-secondary ms-2">
                                <?= isset($model->data_json['time_slot']) ? $model->data_json['time_slot'] : 'ไม่ระบุ' ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ประเภทการพัฒนา:</div>
                        <div class="col-md-9">
                            <?= isset($model->data_json['development_type_name']) ? $model->data_json['development_type_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ระดับการพัฒนา:</div>
                        <div class="col-md-9">
                            <?= isset($model->data_json['development_level_name']) ? $model->data_json['development_level_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 text-muted">ลักษณะ:</div>
                        <div class="col-md-9">
                            <?= isset($model->data_json['development_go_type_name']) ? $model->data_json['development_go_type_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 text-muted">การเบิกเงิน:</div>
                        <div class="col-md-9">
                            <?= isset($model->data_json['claim_type_name']) ? $model->data_json['claim_type_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- สถานที่และหน่วยงาน -->
    <div class="card mt-4">
        <div class="card-header bg-light p-2">
            <strong><i class="bi bi-geo-alt me-2"></i>สถานที่และหน่วยงาน</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">สถานที่จัด:</div>
                        <div class="col-md-8">
                            <?= isset($model->data_json['location']) ? $model->data_json['location'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">จังหวัด:</div>
                        <div class="col-md-8">
                            <?= isset($model->data_json['province_name']) ? $model->data_json['province_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 text-muted">ประเภทสถานที่:</div>
                        <div class="col-md-8">
                            <?php
                                    $locationType = isset($model->data_json['location_org_type']) ? $model->data_json['location_org_type'] : 'ไม่ระบุ';
                                    $typeClass = [
                                        'ในจังหวัด' => 'bg-success',
                                        'ต่างจังหวัด' => 'bg-info',
                                        'ต่างประเทศ' => 'bg-warning',
                                    ][$locationType] ?? 'bg-secondary';
                                    ?>
                            <span class="badge <?= $typeClass ?>"><?= $locationType ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">หน่วยงานที่จัด:</div>
                        <div class="col-md-8">
                            <?= isset($model->data_json['location_org']) ? $model->data_json['location_org'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ผู้ที่เกี่ยวข้อง -->
    <div class="card mt-4">
        <div class="card-header bg-light p-2">
            <strong><i class="bi bi-people me-2"></i>บุคลากรที่เกี่ยวข้อง</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">หัวหน้า:</div>
                        <div class="col-md-8">
                            <?php if($model->leader): ?>
                            <?= $model->leader->getAvatar(false) ?>

                            <?php else: ?>
                            <span class="text-muted">ไม่ระบุ</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">ผู้ปฏิบัติหน้าที่แทน:</div>
                        <div class="col-md-8">
                            <?php if($model->assignedTo): ?>
                            <?= $model->assignedTo->getAvatar(false) ?>

                            <?php else: ?>
                            <span class="text-muted">ไม่ระบุ</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ข้อมูลการเดินทาง -->
    <div class="card mt-4">
        <div class="card-header bg-light p-2">
            <strong><i class="bi bi-car-front me-2"></i>ข้อมูลการเดินทาง</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">วันที่เดินทาง:</div>
                        <div class="col-md-8">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?=$model->showVehicleDateRange()?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">พาหนะเดินทาง:</div>
                        <div class="col-md-8">
                            <i class="bi bi-car-front-fill me-1"></i>
                            <?= isset($model->data_json['vehicle_type_name']) ? $model->data_json['vehicle_type_name'] : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ค่าใช้จ่าย -->
<?=$this->render('expense_type',['model' => $model]);?>
<!-- คณะเดินทาง -->
<?=$this->render('member',['model' => $model]);?>
    <!-- การอนุมัติ -->
    <?php if ($model->status != 'Pending'): ?>
    <div class="card mt-4">
        <div class="card-header bg-light p-2">
            <strong><i class="bi bi-clipboard-check me-2"></i>ข้อมูลการอนุมัติ</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">วันที่อนุมัติ:</div>
                        <div class="col-md-8">
                            <?php $model->created_at ? Yii::$app->formatter->asDate($model->created_at, 'php:d/m/Y H:i:s') : 'ไม่ระบุ' ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">ผู้อนุมัติ:</div>
                        <div class="col-md-8">
                            <?php if(isset($model->approvedBy) && $model->approvedBy): ?>
                            <div class="d-flex align-items-center">
                                <div class="avatar-small me-2">
                                    <i class="bi bi-person-check"></i>
                                </div>
                                <?= $model->approvedBy->fullname ?>
                            </div>
                            <?php else: ?>
                            <span class="text-muted">ไม่ระบุ</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php if ($model->status == 'Rejected'): ?>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">เหตุผลที่ไม่อนุมัติ:</div>
                        <div class="col-md-8">
                            <div class="alert alert-danger">
                                <?= isset($model->data_json['reject_reason']) ? $model->data_json['reject_reason'] : 'ไม่ระบุเหตุผล' ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($model->status == 'Approved' || $model->status == 'Completed'): ?>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">หมายเหตุ:</div>
                        <div class="col-md-8">
                            <?= isset($model->data_json['approve_note']) ? $model->data_json['approve_note'] : 'ไม่มีหมายเหตุ' ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>



    <!-- ประวัติการแก้ไข -->
    <!-- <div class="card mt-4">
                <div class="card-header bg-light p-2">
                    <strong><i class="bi bi-clock-history me-2"></i>ประวัติการดำเนินการ</strong>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">สร้างรายการ</h6>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i><?= Yii::$app->formatter->asDate($model->created_at, 'php:d/m/Y H:i:s') ?>
                                    โดย <?= $model->createdBy->fullname ?? 'ไม่ระบุ' ?>
                                </small>
                            </div>
                        </div>
                        <?php if ($model->updated_at != $model->created_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">ปรับปรุงข้อมูล</h6>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i><?= Yii::$app->formatter->asDate($model->updated_at, 'php:d/m/Y H:i:s') ?>
                                    โดย <?= $model->updatedBy->fullname ?? 'ไม่ระบุ' ?>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->created_at): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?= $model->status == 'Approved' || $model->status == 'Completed' ? 'bg-success' : 'bg-danger' ?>"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0"><?= $model->status == 'Approved' || $model->status == 'Completed' ? 'อนุมัติรายการ' : 'ปฏิเสธรายการ' ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i><?= Yii::$app->formatter->asDate($model->created_at, 'php:d/m/Y H:i:s') ?>
                                    โดย <?= $model->approvedBy->fullname ?? 'ไม่ระบุ' ?>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($model->status == 'Completed'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-0">เสร็จสิ้นการพัฒนา</h6>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i><?= isset($model->data_json['completed_at']) ? Yii::$app->formatter->asDate($model->data_json['completed_at'], 'php:d/m/Y H:i:s') : 'ไม่ระบุ' ?>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div> -->

    <!-- ปุ่มดำเนินการ -->
    <!-- <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-center gap-2">
                        <?php if ($model->status == 'Pending'): ?>
                            <?= Html::a('<i class="bi bi-check-circle me-1"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                                'class' => 'btn btn-success rounded-pill px-4 py-2',
                                'data' => [
                                    'confirm' => 'คุณต้องการอนุมัติรายการนี้ใช่หรือไม่?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                            <?= Html::a('<i class="bi bi-x-circle me-1"></i> ไม่อนุมัติ', ['reject', 'id' => $model->id], [
                                'class' => 'btn btn-danger rounded-pill px-4 py-2',
                                'data' => [
                                    'confirm' => 'คุณต้องการไม่อนุมัติรายการนี้ใช่หรือไม่?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                        <?php if ($model->status == 'Approved'): ?>
                            <?= Html::a('<i class="bi bi-check-circle-fill me-1"></i> ยืนยันเสร็จสิ้นการพัฒนา', ['complete', 'id' => $model->id], [
                                'class' => 'btn btn-info rounded-pill px-4 py-2',
                                'data' => [
                                    'confirm' => 'คุณต้องการยืนยันว่าการพัฒนานี้เสร็จสิ้นแล้วใช่หรือไม่?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                        <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์', ['print', 'id' => $model->id], [
                            'class' => 'btn btn-secondary rounded-pill px-4 py-2',
                            'target' => '_blank'
                        ]) ?>
                        <?= Html::a('<i class="bi bi-trash me-1"></i> ลบ', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-outline-danger rounded-pill px-4 py-2',
                            'data' => [
                                'confirm' => 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div> -->

</div>
<!-- 
        <div class="d-flex align-items-center">
                                            <div class="avatar-small me-2">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            ทดสอบ
                                        </div> -->

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    background-color: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.avatar-small {
    width: 30px;
    height: 30px;
    background-color: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.timeline:before {
    content: '';
    position: absolute;
    left: -23px;
    width: 2px;
    height: 100%;
    background-color: #dee2e6;
}
</style>

<?php  echo $this->render('timeline_approve', ['model' => $model]) ?>