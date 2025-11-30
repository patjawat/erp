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
    <div class="col-lg-8">
       

        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
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
                        </svg></div>
                    <h6 class="fw-bold mb-0 text-dark">รายละเอียดการลา</h6>
                </div>
                <div class="row g-4">
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">ประเภทการลา</small>
                        <p class="fw-bold text-dark mb-0 fs-5"><?= $model->leaveType->title ?></p>
                    </div>
                    <div class="col-sm-6"><small class="text-muted d-block mb-1">จำนวนวัน</small>
                        <p class="fw-semibold text-dark mb-0"><?= $model->total_days ?> วัน</p>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border border-light-subtle">
                            <div class="d-flex gap-2 text-muted small mb-2"><svg xmlns="http://www.w3.org/2000/svg"
                                    width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-calendar" aria-hidden="true">
                                    <path d="M8 2v4"></path>
                                    <path d="M16 2v4"></path>
                                    <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                    <path d="M3 10h18"></path>
                                </svg> ช่วงเวลาที่ลา</div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                               <?php echo $model->showLeaveDate() ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12"><small class="text-muted d-block mb-1">เหตุผลการลา</small>
                        <p class="text-dark mb-0 bg-white p-3 rounded-3 border border-light-subtle"><?php echo $model->data_json['reason'] ?? '-' ?></p>
                    </div>
                    <div class="col-12"><small class="text-muted d-block mb-2">ข้อมูลการติดต่อ</small>
                        <div class="d-flex flex-column gap-2 text-sm text-secondary">
                            <div class="d-flex gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-map-pin flex-shrink-0 text-muted" aria-hidden="true">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>

                                </svg><span><?=$model->data_json['address'] ?? '-' ?></span></div>
                            <div class="d-flex gap-2 align-items-center"><svg xmlns="http://www.w3.org/2000/svg"
                                    width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-phone flex-shrink-0 text-muted" aria-hidden="true">
                                    <path
                                        d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384">
                                    </path>
                                </svg><span class="fw-medium text-dark"><?=$model->data_json['phone'] ?? '-' ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-3 h-100">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 opacity-10 p-2"><svg xmlns="http://www.w3.org/2000/svg"
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
                        class="opacity-75 d-block mb-1 text-white">วันลาพักผ่อนสะสม</small>
                    <h2 class="display-6 fw-bold mb-0  text-white">0 <span
                            class="fs-6 fw-normal opacity-75  text-white">วัน</span></h2>
                    <div
                        class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center small">
                        <span class="opacity-75  text-white">สิทธิคงเหลือ</span><span class="fw-bold  text-white">10
                            วัน</span>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 d-flex align-items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg"
                            width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-clock text-primary" aria-hidden="true">
                            <path d="M12 6v6l4 2"></path>
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>สถานะดำเนินการ</h6>
                    <div class="position-relative ps-2">
                        <div class="position-absolute top-0 bottom-0 start-0 border-start border-2 border-light ms-2"
                            style="z-index: 0;"></div>
                        <div class="d-flex flex-column gap-4 position-relative">
                            <div class="d-flex gap-3 align-items-start bg-white z-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border border-2
                                                                    bg-white border-primary text-primary shadow
                                                                " style="width: 32px; height: 32px;"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-user-check" aria-hidden="true">
                                        <path d="m16 11 2 2 4-4"></path>
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                    </svg></div>
                                <div>
                                    <p class="mb-0 small fw-bold text-dark">หน.เห็นชอบ</p><small
                                        class="text-muted d-block" style="font-size: 0.75rem;">Head of Dept</small><span
                                        class="badge bg-warning text-dark mt-1 rounded-pill fw-normal px-2"
                                        style="font-size: 0.65rem;">รออนุมัติ</span>
                                </div>
                            </div>
                            <div class="d-flex gap-3 align-items-start bg-white z-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border border-2
                                                                    bg-light border-light text-muted
                                                                " style="width: 32px; height: 32px;"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-building" aria-hidden="true">
                                        <path d="M12 10h.01"></path>
                                        <path d="M12 14h.01"></path>
                                        <path d="M12 6h.01"></path>
                                        <path d="M16 10h.01"></path>
                                        <path d="M16 14h.01"></path>
                                        <path d="M16 6h.01"></path>
                                        <path d="M8 10h.01"></path>
                                        <path d="M8 14h.01"></path>
                                        <path d="M8 6h.01"></path>
                                        <path d="M9 22v-3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"></path>
                                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                    </svg></div>
                                <div>
                                    <p class="mb-0 small fw-bold text-muted">หน.กลุ่มงาน</p><small
                                        class="text-muted d-block" style="font-size: 0.75rem;">Group Head</small>
                                </div>
                            </div>
                            <div class="d-flex gap-3 align-items-start bg-white z-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border border-2
                                                                    bg-light border-light text-muted
                                                                " style="width: 32px; height: 32px;"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-file-text" aria-hidden="true">
                                        <path
                                            d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z">
                                        </path>
                                        <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                        <path d="M10 9H8"></path>
                                        <path d="M16 13H8"></path>
                                        <path d="M16 17H8"></path>
                                    </svg></div>
                                <div>
                                    <p class="mb-0 small fw-bold text-muted">จนท.ตรวจสอบ</p><small
                                        class="text-muted d-block" style="font-size: 0.75rem;">Officer Check</small>
                                </div>
                            </div>
                            <div class="d-flex gap-3 align-items-start bg-white z-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border border-2
                                                                    bg-light border-light text-muted
                                                                " style="width: 32px; height: 32px;"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="lucide lucide-badge-check" aria-hidden="true">
                                        <path
                                            d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z">
                                        </path>
                                        <path d="m9 12 2 2 4-4"></path>
                                    </svg></div>
                                <div>
                                    <p class="mb-0 small fw-bold text-muted">ผอ.อนุมัติ</p><small
                                        class="text-muted d-block" style="font-size: 0.75rem;">Director Approval</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<table class="table border-0 table-striped-columns mt-3">
    <tbody>
        <tr>
            <td>เรื่อง : </td>
            <td><span class="text-pink">ขอ<?php echo ($model->leaveType->title ?? '-') ?></span></td>

            <td>เขียนเมื่อ : </td>
            <td><span class="text-pink"><?php echo $model->viewCreated() ?></span></td>
        </tr>
        <tr>
            <td>ระหว่างวันที่ : </td>
            <td>
                <i class="fa-solid fa-calendar-check"></i> <?php echo $model->showLeaveDate() ?>
            </td>

            <td>เป็นเวลา : </td>
            <td>
                <span class="badge rounded-pill badge-soft-danger text-primary fs-13 "><?php echo $model->total_days ?>
                    วัน</span>
            </td>
        </tr>

        <tr>
            <td>เหตุผล : </td>
            <td colspan="4"><?php echo $model->data_json['reason'] ?? '-' ?></td>


        </tr>
        <tr>
            <td>ระหว่างลาติดต่อ : </td>
            <td><?php echo $model->data_json['address'] ?? '-' ?></td>
            <td>โทรศัพท์ : </td>
            <td><?php echo $model->data_json['phone'] ?? '-' ?></td>
        </tr>
        <tr>
            <?php if ($model->status == 'Cancel'): ?>
            <td>สถานะ : </td>
            <td><?php echo $model->viewStatus() ?></td>
            <td>ผู้ดำเนินการยกเลิก : </td>
            <td>
                <?php
                    echo ($model->data_json['cancel_fullname'] ?? '-') .
                        (
                            isset($model->data_json['cancel_date'])
                            ? ' วันเวลา ' . (Yii::$app->thaiFormatter->asDateTime($model->data_json['cancel_date'], 'medium') ?? '')
                            : ''
                        );
                    ?>
            </td>
            <?php else: ?>
            <td>สถานะ : </td>
            <td colspan="4"><?php echo $model->viewStatus() ?></td>
            <?php endif ?>
        </tr>

        <tr>
            <td>ไฟล์แนบ : </td>
            <td>
                <?php echo $model->listClipFile() ?>
                <?php // echo Html::a('<i class="bi bi-clock-history"></i> ดูไฟล์แนบ', ['/hr/leave/view-history','emp_id' => $model->emp_id,'title' =>'ประวัติการลา'], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-xl']]) 
                ?>
            </td>
            <td>วันลาพักผ่อนสะสม : </td>
            <td><?php echo $model->sumLeavePermission()['sum'] ?></td>
        </tr>


    </tbody>
</table>