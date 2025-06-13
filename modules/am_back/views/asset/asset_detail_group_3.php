<?php
use yii\helpers\Html;
use app\components\AppHelper;

$assetName = (isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-') . ' รหัส : <code>' . $model->code . '</code>';
?>


<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            <div class="position-relative p-2 d-flex">
                <div class="dropdown edit-field-half-left">
                    <div class="btn-icon btn-icon-sm btn-icon-soft-primary dropdown-toggle me-0 edit-field-icon"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-sliders fs-6"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item select-photo">
                            <i class="fa-solid fa-file-image me-2 fs-5"></i>
                            <span>อัพโหลดภาพ</span>
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    <?= Html::img($model->showImg(), ['class' => 'avatar-profile object-fit-cover rounded m-auto border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 320px;']) ?>
                </div>
                <input type="file" id="my_file" style="display: none;" />
                <a href="#" class="select-photo"></a>
            </div>

            <?php if (isset($model->Retire()['progress'])): ?>
                <div class="container px-5">

                    <div class="progress progress-sm mt-3 w-100">
                        <div class="progress-bar" role="progressbar"
                        <?= "style='width:" . $model->Retire()['progress'] . '%; background-color:' . $model->Retire()['color'] . ";  '" ?>
                        aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-2" style="width:100%;">
                    <div>
                        <i class="fa-regular fa-clock"></i> <span class="fw-semibold">เหลือ</span> :
                        <?= AppHelper::CountDown($model->Retire()['date'])[0] != '-' ? AppHelper::CountDown($model->Retire()['date']) : 'หมดอายุการใช้งาน' ?>
                    </div>|<div>
                        <i class="fa-solid fa-calendar-xmark"></i> <span class="fw-semibold">ครบ <?= isset($model->data_json['service_life']) ? $model->data_json['service_life'] : '' ?> ปี</span>
                        <span class="text-danger"><?= $model->Retire()['date']; ?></span>
                    </div>
                </div>
                </div>
                <?php endif; ?>
                
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-none h-75">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-item-middle">
                        <div>
                            <h5 class="card-title mb-0 position-relative" style="margin-left: 26px;">
                                <i class="fa-solid fa-circle-info"
                                    style="position: absolute;font-size: 47px;margin-left: -32px;margin-top: -4px;color: #2196F3;"></i>
                                <?php

                                    try {
                                        echo Html::a('&nbsp;' . (isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-'), ['/sm/asset-item/view', 'id' => $model->assetItem->id], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]);
                                    } catch (\Throwable $th) {
                                        // throw $th;
                                    }
                                ?>
                            </h5>
                        </div>
                        <div>
                        <?php if ($model->asset_status != 5 || $model->asset_status != 4): ?>
                            <?= Html::a('<i class="fa-solid fa-triangle-exclamation"></i> แจ้งซ่อม', ['/helpdesk/repair/create', 'code' => $model->code, 'send_type' => 'asset', 'container' => 'ma-container', 'title' => '<i class="fa-solid fa-circle-info fs-3 text-danger"></i>  ส่งซ่อม' . $assetName], ['class' => 'open-modal btn btn-danger rounded-pill shadow', 'data' => ['size' => 'modal-lg']]) ?>
                            <?php endif; ?>
                            <?= Html::a('<i class="fa-solid fa-qrcode"></i> QR-Code', ['qrcode', 'id' => $model->id], ['class' => 'open-modal btn btn-success rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
                            <?= Html::a('<i class="fa-solid fa-chart-line"></i> ค่าเสื่อม', ['depreciation', 'id' => $model->id], ['class' => 'open-modal btn btn-primary rounded-pill shadow', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning rounded-pill shadow']) ?>
                            <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete', 'id' => $model->id], ['class' => 'btn btn-secondary rounded-pill shadow delete-asset']) ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <?= $this->render('asset_detail_table', ['model' => $model]) ?>
                        </div>
                       
                    </div>
                </div>
                <!-- End Card body -->
            </div>
            <!-- End card -->
        </div>
    </div>
</div>
