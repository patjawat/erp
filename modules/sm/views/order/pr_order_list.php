<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between">
                    <p><i class="bi bi-plus-circle-fill text-black-50"></i> รายการขอซื้อขอจ้าง</p>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="bi bi-plus-circle-fill  text-primary me-2"></i> สร้างใบขอซื้อ-ขอจ้าง', ['/sm/order/create'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">เลขที่ใบขอ</th>
                                <th scope="col">วันที่</th>
                                <th scope="col">ผู้ขอ/เรื่อง</th>
                                <th scope="col">หน่วยงาน</th>
                                <th scope="col">สถานะ</th>
                                <th scope="col">มูลค่า</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataProvider->getModels() as $model): ?>
                            <tr class="align-middle">
                                <td><?= Html::a($model->code, ['/sm/order/view', 'id' => $model->id]) ?></td>
                                <td>
                                    <p class="m-0">
                                        <?php echo Yii::$app->thaiFormatter->asDate($model->created_at, 'medium') ?>
                                    </p>
                                    <span class="fs-13 text-muted-">
                                        <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'php:H:i:s') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="main-img-user avatar-sm">
                                                <?= Html::img('@web/img/patjwat2.png', ['class' => 'avatar avatar-sm bg-primary text-white']) ?>
                                            </div>
                                        </div>
                                        <div class="d-flex align-middle">
                                            <div class="d-inline-block">
                                                <span class="mb-1">ปัจวัฒน์ ศรีบุญเรือง</span>
                                                <p class="mb-0 fs-13 text-muted">ขอซื้อวัสดุฑ์คอมพิวเตอร์</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>ศูนย์คอมพิวเตอร์</td>
                                <td><label
                                        class="badge rounded-pill text-primary-emphasis bg-warning-subtle p-2 fs-6 text-truncate fw-semibold"><i
                                            class="fa-regular fa-clock"></i> รออนุมัติ</label></td>
                                <td>3,000</td>
                            </tr>
                            <?php endforeach; ?>
                           
                        </tbody>
                    </table>
                </div>
            </div>
        </div>