<?php

use yii\helpers\Html;
?>
<style>
    .card-img-top {
        max-height: 220px;
        min-height: 220px;
    }

    .status-active {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .list-inline > li {
        margin-bottom: 5px;
    }
</style>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-5 g-4 mb-4">
    <?php foreach ($dataProvider->getModels() as $key => $model): ?>
        <!-- <div class="col-md-6 col-lg-4">
                        <div class="card property-card">
                            <div class="position-relative">
                                <?= $model->viewstatus() ?>
                                 <?= Html::img($model->showImg()['image'], ['class' => 'card-img-top p-2', 'style' => 'height:180px']) ?>
                              
                            </div>
                            <div class="card-header">
                                <h5 class="card-title mb-1"><?= $model->AssetitemName() ?></h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-danger">฿ 3,200,000</span>
                                    <span class="badge badge-success">พื้นที่ 120 ตร.ม.</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="section-title">
                                    <i class="bi bi-house-door me-2"></i><?= $model->code; ?>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ประเภท:</span>
                                    <span class="info-value"><?= $model->AssetTypeName(); ?></span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">วิธีได้มา:</span>
                                    <span class="info-value"><?= $model->method_get ?></span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ประเภทเงิน:</span>
                                    <span class="info-value"><?= $model->budget_type ?></span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">ประจำหน่วยงาน:</span>
                                    <span class="info-value">
                                         <span class="fw-semibold">ประจำหน่วยงาน</span>
                            <?php if (isset($model->data_json['department_name']) && $model->data_json['department_name'] == ''): ?>
                            <?= isset($model->data_json['department_name_old']) ? $model->data_json['department_name_old'] : '' ?>
                            <?php else: ?>
                            <?= isset($model->data_json['department_name']) ? $model->data_json['department_name'] : '' ?>
                            <?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="property-features">
                                    <span class="feature-badge"><i class="bi bi-door-open"></i>1 ห้องนอน</span>
                                    <span class="feature-badge"><i class="bi bi-droplet"></i>2 ห้องน้ำ</span>
                                    <span class="feature-badge"><i class="bi bi-shop"></i>พื้นที่ค้าขาย</span>
                                </div>
                                
                                <div class="property-info">
                                    <span class="info-label">สถานะ:</span>
                                    <span class="badge bg-warning text-dark">รอปรับปรุง</span>
                                </div>
                            </div>
                                <div class="card-footer d-flex justify-content-between">
            <a class="btn btn-outline-primary" href="/am/land/view?id=1"><i class="fa-solid fa-eye"></i> ดูรายละเอียด</a>            <a class="btn btn-primary" href="/am/land/update?id=1"><i class="fa-solid fa-pen-to-square"></i> แก้ไข</a>        </div>
                        </div>
                    </div> -->

        <div class="col">
            <div class="card h-100">
                <div class="equipment-card-img">
                    <?= Html::img($model->showImg()['image'], ['class' => 'card-img-top']) ?>

                    <!-- <span class="status-badge status-active">ใช้งานอยู่</span> -->
                    <?= $model->viewstatus() ?>
                    </rect>
                </div>
                <div class="card-body pb-0 mb-0 position-relative">
                    <p class="text-center mt-4"><?= $model->asset_name ?></p>
                    <div>
                        <ul class="list-inline fs-13 pb-0 mb-0r">
                            <li>
                                <i class="fa-solid fa-angle-right"></i> <span
                                    class="">เลขครุภัณฑ์</span>
                                <span class="text-danger"><?= $model->code ?><span>
                            </li>
                            <li>
                                <i class="fa-solid fa-angle-right"></i>
                                <span class="">วันที่รับเข้า</span>
                                <?= Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?>
                            </li>
                            <li>
                                <i class="fa-solid fa-angle-right"></i>
                                <span class="">วิธีได้มา : </span> <?= $model->method_get ?>
                            </li>
                            <li>
                                <i class="fa-solid fa-angle-right"></i>
                                <span class="">ประเภทเงิน</span> :
                                <?= $model->budget_type ?>
                            </li>

                            <li class="text-truncate"><i class="fa-solid fa-angle-right"></i>
                                <span class="">ประจำหน่วยงาน</span>
                                <?php if (isset($model->data_json['department_name']) && $model->data_json['department_name'] == ''): ?>
                                    <?= isset($model->data_json['department_name_old']) ? $model->data_json['department_name_old'] : '' ?>
                                <?php else: ?>
                                    <?= isset($model->data_json['department_name']) ? $model->data_json['department_name'] : '' ?>
                                <?php endif; ?>
                            </li>
                            <li>
                                <div class="d-flex justify-content-between">

                                    <div>
                                        <i class="fa-solid fa-angle-right"></i>
                                        <span class="fw-semibold">มูลค่า</span> :
                                        <span
                                            class="fw-semibold"><?= isset($model->price) ? number_format($model->price, 2) : '' ?></span>
                                        บาท
                                    </div>
                                </div>
                            </li>


                        </ul>

                    </div>
                    <div class="position-absolute top-0 start-50 translate-middle w-100 px-3">

                        <div class="d-flex justify-content-between total font-weight-bold bg-secondary-subtle rounded p-2 w-100 border-start border-end border-2 border-primary">
                            <?= $model->getOwner() ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-1">
                    <div class="d-flex justify-content-between">
                        <?= Html::a('<i class="bi bi-eye"></i> รายละเอียด', ['/am/asset/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary view-btn']) ?>
                        <div>
                            <?= Html::a(' <i class="bi bi-pencil"></i>', ['/am/asset/update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-warning edit-btn']) ?>
                            <!-- <button class="btn btn-sm btn-outline-warning edit-btn" data-id="1">
                                        <i class="bi bi-pencil"></i>
                                    </button> -->

                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="1">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>