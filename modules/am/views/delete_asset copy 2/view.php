<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'รายละเอียดครุภัณฑ์ ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนทรัพย์สิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('../default/menu', ['active' => 'asset']) ?>
<?php $this->endBlock(); ?>

<style>
    .field-asset-q {
        margin-bottom: 0px !important;
    }
</style>

<?php Pjax::begin(['id' => 'am-container', 'timeout' => 50000]); ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div class="d-flex align-items-center gap-2 text-secondary small">
        <a href="/dev/asset/index" class="text-decoration-none text-secondary hover-text-primary" style="cursor: pointer;">ทรัพย์สิน</a>
        <span>/</span>
        <span class="text-dark fw-medium">รายละเอียด</span>
    </div>

    <div class="d-flex gap-2">

        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button"
                id="dropdownNewButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i id="dropdownNewIcon" class="fa-solid fa-circle-chevron-down"></i> สร้างใหม่
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li> <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> สร้างใหม่', ['create'], ['class' => 'dropdown-item']) ?></li>
                <li><?= Html::a('<i class="fa-solid fa-copy me-2"></i> สร้างใหม่จากสำเนานี้', ['create', 'id' => $model->id], ['class' => 'dropdown-item']) ?></li>

            </ul>
        </div>
        <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                'method' => 'post',
            ],
        ]) ?>
        <button class="btn btn-white border shadow-sm text-secondary d-flex align-items-center gap-2 btn-sm px-3 py-2 bg-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                <path d="M21 21v.01"></path>
                <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                <path d="M3 12h.01"></path>
                <path d="M12 3h.01"></path>
                <path d="M12 16v.01"></path>
                <path d="M16 12h1"></path>
                <path d="M21 12v.01"></path>
                <path d="M12 21v-1"></path>
            </svg>
            QR Code
        </button>
        <a href="/dev/asset/index" class="btn btn-white border shadow-sm text-secondary d-flex align-items-center gap-2 btn-sm px-3 py-2 bg-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M19 12H5"></path>
            </svg>
            ย้อนกลับ
        </a>
    </div>

</div>

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

                <div class="row g-3 py-3 border-top border-bottom border-light-subtle my-3">
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small mb-1">วันที่ได้มา</div>
                        <div class="fw-medium text-dark"><?= Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small mb-1">อายุการใช้งาน</div>
                        <div class="fw-medium text-dark">5 ปี</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small mb-1">สถานะ</div>
                        <?= $model->viewstatus() ?>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary small mb-1">ผู้รับผิดชอบ</div>
                        <?= $model->getOwner() ?>
                        <!-- <div class="fw-medium text-dark">ศูนย์คอมพิวเตอร์</div> -->
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ทะเบียนคุม', ['depreciation', 'id' => $model->id], ['class' => 'btn btn-white border w-50 text-secondary fw-medium" open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    <button class="btn btn-primary w-50 fw-medium shadow-sm">ส่งซ่อม / แจ้งปัญหา</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card" style="overflow: hidden;">

    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs mb-3" id="assetTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-0 border-0 border-bottom border-2 text-dark fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" id="details-tab" data-bs-toggle="tab" data-bs-target="#tab-details" type="button" role="tab" aria-selected="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                        <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                        <path d="M10 9H8"></path>
                        <path d="M16 13H8"></path>
                        <path d="M16 17H8"></path>
                    </svg>
                    รายละเอียด
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 border-0 border-bottom border-2 text-secondary fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#tab-maintenance" type="button" role="tab" aria-selected="false" tabindex="-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z"></path>
                    </svg>
                    ประวัติซ่อมบำรุง
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 border-0 border-bottom border-2 text-secondary fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" id="depreciation-tab" data-bs-toggle="tab" data-bs-target="#tab-depreciation" type="button" role="tab" aria-selected="false" tabindex="-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 17h6v-6"></path>
                        <path d="m22 17-8.5-8.5-5 5L2 7"></path>
                    </svg>
                    ค่าเสื่อมราคา
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0 border-0 border-bottom border-2 text-secondary fw-medium px-4 py-3 d-flex align-items-center gap-2 tab-btn" id="files-tab" data-bs-toggle="tab" data-bs-target="#tab-files" type="button" role="tab" aria-selected="false" tabindex="-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"></path>
                    </svg>
                    เอกสารแนบ
                </button>
            </li>

            <?php if ($model->isCar()): ?>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="pill" href="#assetItems"><i class="fa-solid fa-list-check"></i>
                        ครุภัณฑ์ภายใน</a>
                </li>
            <?php endif; ?>


            <!-- ถ้าเป็นเครื่องมือแพทย์ -->
            <?php if ($model->isMedical()): ?>
                <li class="nav-item">
                    <div class="btn-group">
                        <a class="nav-link" data-bs-toggle="pill" href="#calibration">
                            <i class="fa-solid fa-weight-scale"></i> สอบเทียบ</a>
                        <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"
                            aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                        <ul class="dropdown-menu">

                            <a class="dropdown-item open-modal" href="/am/asset-detail?name=calibration_items" data-size="modal-lg">
                                <i class="fa-solid fa-circle-plus me-2"></i>สร้างใหม่ </a>
                </li>
                <li><a class="dropdown-item open-modal" href="/am/asset-detail?name=calibration_items" data-size="modal-lg"><i
                            class="fa-solid fa-gear fs-6 me-2"></i> ตั้งค่า</a> </li>
        </ul>
    </div>
    </li>

<?php endif; ?>

</ul>
</div>

<div class="card-body p-0">
    <div class="tab-content" id="assetTabsContent">

        <div class="tab-pane fade show active p-4" id="tab-details" role="tabpanel" aria-labelledby="details-tab">
            <div class="row g-5">
                <div class="col-12 col-md-6">
                    <h6 class="fw-bold text-dark mb-4 border-start border-4 border-primary ps-3">ข้อมูลทั่วไป</h6>
                    <dl class="row mb-0 text-sm" style="font-size: 0.9rem;">
                        <dt class="col-sm-4 text-secondary fw-normal mb-3">รหัสทรัพย์สิน</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->code ?></dd>

                        <dt class="col-sm-4 text-secondary fw-normal mb-3">ชื่อรายการ</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->asset_name ?></dd>

                        <dt class="col-sm-4 text-secondary fw-normal mb-3">ยี่ห้อ / รุ่น</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->data_json['brand'] ?? 'ไม่ระบุ' ?>/<?= $model->data_json['asset_model'] ?? 'ไม่ระบุ' ?></dd>

                        <dt class="col-sm-4 text-secondary fw-normal mb-3">Serial Number</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->data_json['serial_number'] ?? 'ไม่ระบุ' ?></dd>

                        <dt class="col-sm-4 text-secondary fw-normal mb-3">สถานที่ตั้ง</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= isset($model->data_json['location']) ? $model->data_json['location'] : '-' ?></dd>
                        <dt class="col-sm-4 text-secondary fw-normal mb-3">หน่วยงานรับผิดชอบ</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->departmentName() ?></dd>
                    </dl>
                </div>

                <div class="col-12 col-md-6">
                    <h6 class="fw-bold text-dark mb-4 border-start border-4 border-success ps-3">ข้อมูลการได้มา</h6>
                    <dl class="row mb-0 text-sm">
                        <dt class="col-sm-4 text-secondary fw-normal mb-3">วันที่รับ</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?></dd>

                        <dt class="col-sm-4 text-secondary fw-normal mb-3">วิธีได้มา</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->method_get ?></dd>

                        <dt class="col-sm-4 text-secondary fw-normal mb-3">ผู้จำหน่าย</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->vendor?->title ?? '-' ?></dd>

                        <dt class="col-sm-4 text-secondary fw-normal mb-3">วิธีการได้มา</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->purchaseName?->title ?? '-' ?></dd>
                    </dl>
                </div>
            </div>

            <div class="border-top pt-4 mt-2">
                <h6 class="fw-bold text-dark mb-3">คุณลักษณะเฉพาะ / รายละเอียดเพิ่มเติม</h6>
                <div class="p-3 bg-light rounded text-secondary small">
                    <?= $model->data_json['asset_options'] ?? '-' ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade p-4" id="tab-maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold text-dark mb-0">ประวัติการซ่อมบำรุง</h6>
                <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-1 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    บันทึกการซ่อม
                </button>
            </div>

            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="px-4 py-3 fw-medium">วันที่แจ้ง</th>
                            <th class="px-4 py-3 fw-medium">รายการ / อาการ</th>
                            <th class="px-4 py-3 fw-medium">ผู้ดำเนินการ</th>
                            <th class="px-4 py-3 fw-medium text-end">ค่าใช้จ่าย</th>
                            <th class="px-4 py-3 fw-medium text-center">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <tr>
                            <td class="px-4 py-3 text-dark fw-medium">2566-12-10</td>
                            <td class="px-4 py-3">
                                <div class="fw-medium text-dark">เปลี่ยนแบตเตอรี่</div>
                                <div class="text-muted small">เปลี่ยนแบตเตอรี่เนื่องจากเสื่อมสภาพ</div>
                            </td>
                            <td class="px-4 py-3 text-secondary">ร้านอมร อิเล็คโทรนิคส์</td>
                            <td class="px-4 py-3 text-end fw-medium">฿450.00</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium d-inline-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <path d="M22 4 12 14.01l-3-3"></path>
                                    </svg>
                                    Completed
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-dark fw-medium">2567-05-15</td>
                            <td class="px-4 py-3">
                                <div class="fw-medium text-dark">ซ่อมหน้าจอแสดงผล</div>
                                <div class="text-muted small">หน้าจอแสดงผลติดๆ ดับๆ ส่งซ่อมศูนย์</div>
                            </td>
                            <td class="px-4 py-3 text-secondary">Omron Thailand</td>
                            <td class="px-4 py-3 text-end fw-medium">฿1,200.00</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium d-inline-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <path d="M22 4 12 14.01l-3-3"></path>
                                    </svg>
                                    Completed
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 text-dark fw-medium">2567-02-20</td>
                            <td class="px-4 py-3">
                                <div class="fw-medium text-dark">อัปเกรด RAM</div>
                                <div class="text-muted small">เพิ่ม RAM จาก 8GB เป็น 16GB</div>
                            </td>
                            <td class="px-4 py-3 text-secondary">JIB Computer Group</td>
                            <td class="px-4 py-3 text-end fw-medium">฿1,500.00</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-medium d-inline-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <path d="M22 4 12 14.01l-3-3"></path>
                                    </svg>
                                    Completed
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade p-4" id="tab-depreciation" role="tabpanel" aria-labelledby="depreciation-tab">
            <div class="text-muted text-center py-5">ส่วนนี้กำลังพัฒนา...</div>
        </div>

        <div class="tab-pane fade p-4" id="tab-files" role="tabpanel" aria-labelledby="files-tab">
            <div class="text-muted text-center py-5">ยังไม่มีเอกสารแนบ</div>
        </div>

    </div>
</div>
</div>




<!-- old -->

<div class="asset-view">
    <?php echo $this->render('./asset_detail', [
        'model' => $model,
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider
    ]) ?>
</div>

<!-- Tabs for Additional Information -->
<div class="card mb-4">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs mb-3" id="equipmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs"
                    type="button" role="tab" aria-selected="true">รายละเอียดทางเทคนิค</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance"
                    type="button" role="tab" aria-selected="false" tabindex="-1">ประวัติการซ่อมบำรุง</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="depreciation-tab" data-bs-toggle="tab" data-bs-target="#depreciation"
                    type="button" role="tab" aria-selected="false" tabindex="-1">ค่าเสื่อมราคา</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents"
                    type="button" role="tab" aria-selected="false" tabindex="-1">เอกสารที่เกี่ยวข้อง</button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="equipmentTabsContent">
            <!-- Technical Specifications Tab -->

            <!-- <div class="tab-pane fade active show" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                <h5 class="card-title fw-bold">คุณลักษณะเฉพาะ</h5>
                <?= $model->data_json['asset_options'] ?? '-' ?>
            </div>

            <!-- Maintenance History Tab -->
            <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">ประวัติการซ่อมบำรุง</h5>
                    <!-- <button class="btn btn-sm btn-primary no-print" data-bs-toggle="modal"
                        data-bs-target="#addMaintenanceModal">
                        <i class="bi bi-plus-circle"></i> เพิ่มประวัติการซ่อม
                    </button> -->
                </div>
                <div class="table-responsive">
                    <?= $this->render('repair_history', ['model' => $model]) ?>
                </div>
            </div>

            <!-- Depreciation Tab -->
            <div class="tab-pane fade" id="depreciation" role="tabpanel" aria-labelledby="depreciation-tab">
                <?= $this->render('depreciation', ['model' => $model]) ?>
            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">เอกสารที่เกี่ยวข้อง</h5>
                    <button class="btn btn-sm btn-primary no-print" data-bs-toggle="modal"
                        data-bs-target="#addDocumentModal">
                        <i class="bi bi-plus-circle"></i> เพิ่มเอกสาร
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ชื่อเอกสาร</th>
                                <th>ประเภท</th>
                                <th>วันที่อัปโหลด</th>
                                <th>ผู้อัปโหลด</th>
                                <th class="no-print">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>ใบส่งของ-ใบกำกับภาษี</td>
                                <td><span class="badge bg-secondary">เอกสารการจัดซื้อ</span></td>
                                <td>15/01/2565</td>
                                <td>นางสาวสมศรี มีทรัพย์</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> ดู</button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i>
                                        ดาวน์โหลด</button>
                                </td>
                            </tr>
                            <tr>
                                <td>คู่มือการใช้งาน</td>
                                <td><span class="badge bg-info">คู่มือ</span></td>
                                <td>15/01/2565</td>
                                <td>นางสาวสมศรี มีทรัพย์</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> ดู</button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i>
                                        ดาวน์โหลด</button>
                                </td>
                            </tr>
                            <tr>
                                <td>ใบรับประกันสินค้า</td>
                                <td><span class="badge bg-warning text-dark">การรับประกัน</span></td>
                                <td>15/01/2565</td>
                                <td>นางสาวสมศรี มีทรัพย์</td>
                                <td class="no-print">
                                    <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> ดู</button>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i>
                                        ดาวน์โหลด</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer History -->
<?php // $this->render('transfer_history')
?>

<?php
$js = <<< JS


var dropdownNew = document.getElementById('dropdownNewButton');
var iconNew = document.getElementById('dropdownNewIcon');

dropdownNew.addEventListener('show.bs.dropdown', function () {
    iconNew.classList.replace('fa-circle-chevron-down', 'fa-circle-chevron-up');
});
dropdownNew.addEventListener('hide.bs.dropdown', function () {
    iconNew.classList.replace('fa-circle-chevron-up', 'fa-circle-chevron-down');
});

$('.delete-asset').click(function (e) { 
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'ลบข้อมูลสำเร็จ!',
                            text: 'รายการถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000, // ตั้งค่าให้ Swal ปิดอัตโนมัติหลัง 1 วินาที
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '/am/asset'; // Redirect หลังจาก timer หมด
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            res.message || 'ไม่สามารถลบข้อมูลได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        'error'
                    );
                }
            });
        }
    });
});


JS;
$this->registerJS($js);

?>
<?php Pjax::end(); ?>