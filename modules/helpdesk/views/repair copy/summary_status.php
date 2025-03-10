<?php
use yii\web\View;
use yii\helpers\Html;
?>
<div class="row">
            <div class="col-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p>ร้องขอ</p>
                                <h2 class="mb-0 mt-1"><?=$model->SummaryStatus(1)?></h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-warning-subtle rounded p-3">
                                        <i class="fa-solid fa-user-check text-warning fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p>รับเรื่อง</p>
                                <h2 class="mb-0 mt-1"><?=$model->SummaryStatus(2)?></h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-warning-subtle rounded p-3">
                                        <i class="fa-solid fa-user-check text-warning fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                            <p>ดำเนินการ</p>
                                        <h2 class="mb-0 mt-1"><?=$model->SummaryStatus(3)?></h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-primary-subtle rounded p-3">
                                        <i class="fa-solid fa-person-digging text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                            <p>ยกเลิก</p>
                                        <h2 class="mb-0 mt-1"><?=$model->SummaryStatus(5)?></h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-danger-subtle rounded p-3">
                                    <i class="fa-solid fa-circle-minus text-danger fs-4"></i>
                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                            <p>เสร็จสิ้น</p>
                                <h2 class="mb-0 mt-1"><?=$model->SummaryStatus(4)?></h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-success-subtle rounded p-3">
                                        <i class="fa-regular fa-circle-check text-success fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


