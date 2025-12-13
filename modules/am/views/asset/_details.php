<?php
$this->title = 'รายละเอียดครุภัณฑ์ ';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนทรัพย์สิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'ครุภัณฑ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package text-primary">
            <path d="m7.5 4.27 9 5.15"></path>
            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
            <path d="m3.3 7 8.7 5 8.7-5"></path>
            <path d="M12 22V12"></path>
        </svg> ระบบบริหารทรัพย์สิน</h4>
</div>
<?php $this->endBlock(); ?>

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