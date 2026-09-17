<?php

use yii\helpers\Html;

$this->title = $stage === 'WASH' ? 'เริ่มรอบซัก' : 'เริ่มรอบอบ';
$sourceOptions = [];
foreach ($sources as $source) {
    $available = (float) $source['kg'] - (float) $source['used_kg'];
    $sourceOptions[$source['id']] = $source['label'] . ' · เหลือ ' . number_format($available, 3) . ' กก.';
}
$machineOptions = [];
foreach ($machines as $machine) {
    $machineOptions[$machine['asset_id']] = ($machine['code'] ?: '#' . $machine['asset_id']) . ' · ' . ($machine['asset_name'] ?: '') . ' (' . number_format((float) $machine['capacity_kg'], 3) . ' กก.)';
}
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'processing']) ?>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div><h1 class="h4 fw-bold mb-1"><i class="bi bi-<?= $stage === 'WASH' ? 'droplet' : 'wind' ?> me-2"></i><?= Html::encode($this->title) ?></h1><div class="text-body-secondary">เลือกน้ำหนักต้นทางหนึ่งรายการต่อรอบในหน้าจอระยะแรก</div></div>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับรอบเครื่อง', ['index'], ['class' => 'btn btn-outline-secondary align-self-start']) ?>
    </div>
    <?php if (Yii::$app->session->hasFlash('error')): ?><div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle me-2"></i><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div><?php endif; ?>
    <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body">
        <?= Html::beginForm(['new'], 'get', ['class' => 'row g-3 align-items-end']) ?>
            <div class="col-12 col-sm-3"><label class="form-label">ขั้นตอน</label><?= Html::dropDownList('stage', $stage, ['WASH' => 'ซัก', 'DRY' => 'อบ'], ['class' => 'form-select']) ?></div>
            <div class="col-12 col-sm-3"><label class="form-label">กลุ่มผ้า</label><?= Html::dropDownList('linenClass', $linenClass, ['SOILED' => 'ผ้าเปื้อน', 'INFECTIOUS' => 'ผ้าติดเชื้อ'], ['class' => 'form-select']) ?></div>
            <div class="col-12 col-sm-3"><label class="form-label">แหล่งผ้า</label><?= Html::dropDownList('mode', $mode, ['NORMAL' => 'งานปกติ', 'RECOVERY' => 'ผ้าจากรอบหยุดที่อนุมัติ'], ['class' => 'form-select']) ?></div>
            <div class="col-12 col-sm-3"><?= Html::submitButton('เลือก', ['class' => 'btn btn-outline-primary rounded-3 w-100']) ?></div>
        <?= Html::endForm() ?>
    </div></div>
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body">
        <?php if (!$machines): ?><div class="alert alert-warning">ยังไม่มีเครื่องประเภทนี้ที่พร้อมใช้งาน กรุณาเชื่อมครุภัณฑ์ที่หน้ารอบเครื่อง</div><?php endif; ?>
        <?php if (!$sources): ?><div class="alert alert-info">ยังไม่มีน้ำหนักต้นทางที่ยืนยันแล้วและเหลือให้จัดสรร</div><?php endif; ?>
        <?= Html::beginForm(['start'], 'post', ['class' => 'row g-3']) ?>
            <?= Html::hiddenInput('stage', $stage) ?><?= Html::hiddenInput('linen_class', $linenClass) ?><?= Html::hiddenInput('mode', $mode) ?>
            <div class="col-12 col-lg-6"><label class="form-label fw-semibold">เครื่อง</label><?= Html::dropDownList('asset_id', null, $machineOptions, ['prompt' => 'เลือกเครื่อง', 'class' => 'form-select', 'required' => true]) ?></div>
            <div class="col-12 col-lg-6"><label class="form-label fw-semibold"><?= $mode === 'RECOVERY' ? 'ผ้ากู้จากรอบเครื่องที่หยุด' : ($stage === 'WASH' ? 'น้ำหนักรับผ้าจากรอบเก็บ' : 'รอบซักต้นทาง') ?></label><?= Html::dropDownList('source_id', null, $sourceOptions, ['prompt' => 'เลือกแหล่งผ้า', 'class' => 'form-select', 'required' => true]) ?></div>
            <div class="col-12 col-sm-6"><label class="form-label fw-semibold">น้ำหนักที่จะเข้าเครื่อง (กก.)</label><?= Html::input('number', 'allocated_kg', '', ['class' => 'form-control', 'min' => '0.001', 'step' => '0.001', 'required' => true]) ?></div>
            <div class="col-12 col-sm-6"><label class="form-label fw-semibold">โปรแกรมเครื่อง</label><?= Html::textInput('program', '', ['class' => 'form-control', 'maxlength' => 100]) ?></div>
            <div class="col-12"><?= Html::submitButton('เริ่มรอบ', ['class' => 'btn btn-primary rounded-3', 'disabled' => !$machines || !$sources]) ?></div>
        <?= Html::endForm() ?>
    </div></div>
</div>
