<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\booking\models\Meeting;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\MeetingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Meetings';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<div class="container">
    <?=$this->render('menu')?>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">การจองทั้งหมด</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-semibold mb-0">12</h2>
                            <small class="text-success">+2 จากเดือนที่แล้ว</small>
                        </div>
                        <div class="icon-circle">
                            <i class="bi bi-calendar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">ห้องประชุมทั้งหมด</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-semibold mb-0">4</h2>
                            <small class="text-muted">ใน 7 วันข้างหน้า</small>
                        </div>
                        <div class="icon-circle">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">ผู้ใช้งานทั้งหมด</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-semibold mb-0">8</h2>
                            <small class="text-muted">+12 จากเดือนที่แล้ว</small>
                        </div>
                        <div class="icon-circle">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">การจองที่รอการอนุมัติ</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-semibold mb-0">8</h2>
                            <small class="text-muted">ต้องการการอนุมัติ</small>
                        </div>
                        <div class="icon-circle">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-7">

            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-1">การจองที่รอการอนุมัติ</h3>
                        <p class="card-subtitle text-muted">รายการจองห้องประชุมที่รอการอนุมัติ</p>
                    </div>
                    <a href="/admin/bookings" class="btn btn-primary">ดูทั้งหมด</a>
                </div>

                <ul class="list-group list-group-flush">
                    <!-- รายการที่ 1 -->
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0">นายสมชาย ใจดี</h6>
                                <small class="text-muted">ห้องประชุมใหญ่</small>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-calendar-event me-1"></i>10 เม.ย. 2025<br />
                                    <i class="bi bi-clock me-1"></i>09:00 - 12:00 น.
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <button class="btn btn-outline-success btn-sm" title="อนุมัติ">
                                    <i class="bi bi-check-circle-fill"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" title="ปฏิเสธ">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                        </div>
                    </li>

                   
                    <!-- เพิ่มรายการอื่น ๆ ตามแบบเดียวกัน -->
                </ul>
            </div>
        </div>
        <div class="col-5">


            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">สถิติการใช้ห้องประชุม</h3>
                    <p class="card-subtitle text-muted mb-4">สถิติการใช้ห้องประชุมในเดือนนี้</p>

                    <!-- ห้องประชุมใหญ่ -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <strong class="fw-semibold">ห้องประชุมใหญ่</strong>
                            <span class="text-muted small">80%</span>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 80%;"
                                aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>จำนวนการจอง: 24 ครั้ง</span>
                            <span>จำนวนชั่วโมง: 48 ชั่วโมง</span>
                        </div>
                    </div>

                    <!-- ห้องประชุมกลาง -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <strong class="fw-semibold">ห้องประชุมกลาง</strong>
                            <span class="text-muted small">60%</span>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 60%;"
                                aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>จำนวนการจอง: 18 ครั้ง</span>
                            <span>จำนวนชั่วโมง: 36 ชั่วโมง</span>
                        </div>
                    </div>

                    <!-- ห้องประชุมเล็ก -->
                    <div>
                        <div class="d-flex justify-content-between">
                            <strong class="fw-semibold">ห้องประชุมเล็ก</strong>
                            <span class="text-muted small">40%</span>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 40%;"
                                aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>จำนวนการจอง: 12 ครั้ง</span>
                            <span>จำนวนชั่วโมง: 24 ชั่วโมง</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php Pjax::end(); ?>

</div>