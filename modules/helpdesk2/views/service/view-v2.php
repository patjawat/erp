<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\Order;
use app\widgets\datepicker\DatepickerThai;
use app\modules\helpdesk2\helpers\HelpdeskSlaHelper;
use app\modules\helpdesk2\models\HelpdeskDetail;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = '';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนงานซ่อม', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนงานซ่อม';

?>


<!-- Header -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-6">
                <h3 class="fw-bold mb-1">เลขที่ใบสั่งซ่อม: <span class="text-primary"><?= $model->repair_number ?></span></h3>
                <p class="text-muted mb-0">ปรับปรุงล่าสุดเมื่อ: <?= $model->viewUpdated()['date'] ?> | <?= $model->viewUpdated()['time'] ?></p>
            </div>
            <div class="col-12 col-md-6 text-start text-md-end mt-2 mt-md-0">
                <span class="me-2">สถานะ <?= $model->viewStatus() ?></span>
                <?= HelpdeskSlaHelper::renderBadge($model) ?>
                <button class="btn btn-outline-secondary me-2"><i class="bi bi-printer"></i></button>
                <button class="btn btn-outline-danger"><i class="bi bi-x-circle"></i> ยกเลิกงาน</button>
            </div>
        </div>

    </div>
</div>

<div class="row g-3 mt-3">
    <!-- 1. ข้อมูลผู้แจ้งและอุปกรณ์ (Context) -->
    <div class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">ข้อมูลผู้แจ้งซ่อม</h6>
                </div>
                <div class="small text-muted">
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
                        <span class="fw-medium"><?= Html::encode((is_array($model->data_json) ? ($model->data_json['location'] ?? '-') : '-')) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">ความเร่งด่วน:</span>
                        <span class="fw-medium"><?= $model->viewUrgent()['view'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">ช่องทางซ่อม:</span>
                        <span class="fw-medium"><?= Html::encode($model->viewRepairChannelLabel()) ?></span>
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

    <!-- 2. ทีมช่างผู้รับผิดชอบ -->
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-people"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ทีมช่างผู้รับผิดชอบ</h6>
            </div>
            <div class="card-body p-4">
                <?php
                $team = HelpdeskDetail::find()
                    ->where(['name' => 'repair_team', 'helpdesk_id' => $model->id])
                    ->all();
                ?>
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="text-muted small">
                            มอบหมายช่างโดยการกดปุ่มด้านล่าง แล้วเลือกจากรายการช่าง
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?= Html::a(
                            '+ เพิ่มรายชื่อ',
                            ['/helpdesk/team/create', 'helpdesk_id' => $model->id, 'title' => 'มอบหมายช่าง'],
                            ['class' => 'btn btn-outline-primary w-100 open-modal', 'data' => ['size' => 'modal-md']]
                        ) ?>
                    </div>
                </div>

                <div class="mt-3">
                    <?php if (empty($team)): ?>
                        <div class="text-muted small">ยังไม่มีช่างที่ถูกมอบหมาย</div>
                    <?php else: ?>
                        <?php foreach ($team as $t): ?>
                            <?php $emp = $t->emp; ?>
                            <div class="d-inline-block bg-light border rounded-pill px-3 py-1 me-2 mb-2">
                                <small>
                                    <?= Html::encode($emp?->fullname ?? '-') ?>
                                    <?php if ($emp): ?>
                                        <span class="text-muted"> (<?= Html::encode($emp->departmentName() ?? '-') ?>)</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. อะไหล่และค่าใช้จ่าย (Resources) -->
    <div class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm mb-4 h-100">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">บันทึกค่าใช้จ่าย</h6>
            </div>
            <div class="card-body">

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

        <?php if ($model->isExternalRepair()): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-building"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">รายละเอียดส่งซ่อมภายนอก</h6>
            </div>
            <div class="card-body">
                <?= $model->getExternalRepairDetailHtml() ?>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-receipt"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">สรุปค่าใช้จ่ายแนบบิล</h6>
            </div>
            <div class="card-body">
                <?= $model->getExternalRepairBillsHtml() ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-camera"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">รูปภาพงานซ่อม</h6>
            </div>
            <div class="card-body">
                <?= $model->getRepairWorkPhotosHtml() ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">การตรวจรับงาน</h6>
            </div>
            <div class="card-body">
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


</div>
