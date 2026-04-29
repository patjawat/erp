<?php
use yii\helpers\Html;
?>

<div class="card border border-light-subtle shadow-sm mb-4" style="border-radius: 12px; border-color: #e5e7eb !important;">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12 col-md-auto">
                <div class="bg-light rounded-3 overflow-hidden border" style="width: 200px; height: 200px;">
                    <?= Html::img($model->showImg()['image'], ['class' => 'w-100 h-100 object-fit-cover']) ?>
                </div>
            </div>

            <div class="col-12 col-md">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold mb-2">ครุภัณฑ์</span>
                        <h3 class="fw-bold text-dark mb-1"><?= $model->asset_name ?></h3>
                        <div class="text-secondary small d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path>
                                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle>
                            </svg>
                            <?= $model->code ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="text-secondary small mb-1">มูลค่าทรัพย์สิน</div>
                        <h3 class="fw-bold text-dark mb-0"><?= number_format($model->price, 2) ?></h3>
                    </div>
                </div>

                <div class="row row-cols-2 row-cols-md-5 g-3 py-3 border-top border-bottom border-light-subtle my-3">
                    <div class="col">
                        <div class="text-secondary small mb-1">หมวดครุภัณฑ์</div>
                        <div class="fw-medium text-dark"><?= Html::encode($model->assetCategory?->title ?? '-') ?></div>
                    </div>
                    <div class="col">
                        <div class="text-secondary small mb-1">วันที่ได้มา</div>
                        <div class="fw-medium text-dark"><?= Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?></div>
                    </div>
                    <div class="col">
                        <div class="text-secondary small mb-1">อายุการใช้งาน</div>
                        <div class="fw-medium text-dark">- ปี</div>
                    </div>
                    <div class="col">
                        <div class="text-secondary small mb-1">สถานะ</div>
                        <?= $model->viewstatus() ?>
                    </div>
                    <div class="col">
                        <div class="text-secondary small mb-1">ผู้รับผิดชอบ</div>
                        <?= $model->getOwner() ?>
                        <!-- <div class="fw-medium text-dark">ศูนย์คอมพิวเตอร์</div> -->
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-sm-6">
                        <?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ทะเบียนคุม', ['/am/asset/depreciation', 'id' => $model->id], ['class' => 'btn btn-white border text-secondary fw-medium open-modal w-100', 'data' => ['size' => 'modal-lg']]) ?>
                    </div>
                    <div class="col-12 col-sm-6">
                        <?= Html::a('<i class="fa-solid fa-triangle-exclamation me-2"></i> ส่งซ่อม / แจ้งปัญหา', ['/me/repair-v2/create', 'asset_number' => $model->code, 'send_type' => 'asset', 'container' => 'ma-container', 'title' => '<i class="fa-solid fa-circle-info fs-3"></i>  ส่งซ่อม'], ['class' => 'btn btn-warning fw-medium shadow-sm open-modal w-100', 'data' => ['size' => 'modal-lg']]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>