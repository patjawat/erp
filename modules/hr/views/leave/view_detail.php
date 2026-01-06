<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\widgets\DetailView;
use app\components\AppHelper;
use app\components\UserHelper;

$me = UserHelper::GetEmployee();
/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
$this->title = 'ระบบลา';
$this->params['breadcrumbs'][] = ['label' => 'Leaves', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex  align-items-center  gap-2 mb-4">
                        <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary"><svg
                                xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-file-text" aria-hidden="true">
                                <path
                                    d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z">
                                </path>
                                <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                <path d="M10 9H8"></path>
                                <path d="M16 13H8"></path>
                                <path d="M16 17H8"></path>
                            </svg>
                        </div>
                        <div>

                            <h6 class="fw-bold mb-0 text-dark">รายละเอียดการลา</h6>
                            <span class="d-flex align-items-center gap-2 text-muted small">เขียนเมื่อ: <?= $model->viewCreated() ?></span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <span><?php echo $model->viewStatus() ?></span>

                    </div>


                </div>
                <div class="row g-4">
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">ประเภทการลา</small>
                        <p class="fw-bold text-dark mb-0 fs-5"><?= $model->leaveType->title ?></p>
                    </div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">จำนวนวัน</small>
                        <p class="fw-bold text-dark mb-0 fs-5"><?= $model->total_days ?> วัน</p>
                    </div>
                    <!-- <div class="col-sm-4">
                        <?php if ($model->status == 'Cancel'): ?>
                            ผู้ดำเนินการยกเลิก :

                            <?php
                            echo ($model->data_json['cancel_fullname'] ?? '-') .
                                (
                                    isset($model->data_json['cancel_date'])
                                    ? ' วันเวลา ' . (Yii::$app->thaiFormatter->asDateTime($model->data_json['cancel_date'], 'medium') ?? '')
                                    : ''
                                );
                            ?>

                        <?php endif; ?>
                    </div> -->
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 border border-light-subtle">
                            <div class="d-flex gap-2 text-muted small">
                                <i class="fa-regular fa-calendar fa-xl me-1"></i> ช่วงเวลาที่ลา
                            </div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <?php echo $model->showLeaveDate() ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded-3 border border-light-subtle">
                            <small class="text-muted d-block mb-1"><i class="fa-regular fa-circle-question fa-xl me-1"></i> เหตุผลการลา</small>
                            <?php echo $model->data_json['reason'] ?? '-' ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border border-light-subtle">
                            <small class="text-muted d-block mb-1"><i class="fa-regular fa-circle-question fa-xl me-1"></i> ข้อมูลการติดต่อ</small>
                            <div class="d-flex gap-2"><span><?= $model->data_json['address'] ?? '-' ?></span></div>
                            <div class="d-flex gap-2 align-items-center">โทร. <span class="fw-medium text-dark"><?= $model->data_json['phone'] ?? '-' ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <?= $this->render('view_summary', ['model' => $model]) ?>
                      
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="d-flex flex-column gap-3 h-100">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 opacity-10 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-file-chart-column-increasing" aria-hidden="true">
                        <path
                            d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z">
                        </path>
                        <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                        <path d="M8 18v-2"></path>
                        <path d="M12 18v-4"></path>
                        <path d="M16 18v-6"></path>
                    </svg></div>
                <div class="card-body p-4 position-relative z-1"><small
                        class="opacity-75 d-block mb-1 text-white h6">สิทธิคงเหลือ</small>
                    <h2 class="display-6 fw-bold mb-0  text-white"><?=$model->sumLeavePermission()['sum'] ?>
                        <span class="fs-6 fw-normal opacity-75  text-white">วัน</span>
                    </h2>
                    <div
                        class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center small">
                        <span class="opacity-75  text-white h6">วันลาพักผ่อนสะสม</span><span class="fw-bold  text-white"><?=$model->sumLeavePermission()['total'] ?>
                            วัน</span>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                <div class="card-body p-4">
                    <?php echo $this->render('@app/modules/approveV2/views/default/level_approve_v2', ['model' => $model, 'name' => 'leave']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
    <div class="p-4">
        <?php echo $this->render('history', ['model' => $model]) ?>
    </div>
    </div>
</div>