<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset|null $asset */
/** @var mixed $assetId */
/** @var app\modules\am\models\DepreciationProfile[] $profiles */
/** @var array $scopeOptions */

$this->title = 'เปลี่ยนเกณฑ์ค่าเสื่อมของทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="replace"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= Html::a('<i data-lucide="history"></i> ประวัติการเปลี่ยนแปลง', ['history', 'asset_id' => $assetId], ['class' => 'btn btn-outline-secondary']) ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $cls): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $cls ?>"><?= Yii::$app->session->getFlash($flash) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="card mb-3">
        <div class="card-body">
            <?= Html::beginForm(['form'], 'get', ['class' => 'row g-2 align-items-end']) ?>
                <div class="col-md-4">
                    <label class="form-label">ค้นทรัพย์สิน (รหัส/ID)</label>
                    <input type="number" name="asset_id" class="form-control" value="<?= Html::encode($assetId) ?>" required>
                </div>
                <div class="col-md-2"><?= Html::submitButton('<i data-lucide="search"></i> ค้นหา', ['class' => 'btn btn-outline-primary']) ?></div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php if ($assetId && !$asset): ?>
        <div class="alert alert-warning">ไม่พบทรัพย์สิน id = <?= Html::encode($assetId) ?></div>
    <?php endif; ?>

    <?php if ($asset): ?>
        <div class="card">
            <div class="card-header fw-semibold"><?= Html::encode($asset->code) ?> — <?= Html::encode($asset->asset_name) ?></div>
            <div class="card-body">
                <div class="row mb-3 small text-muted">
                    <div class="col-md-3">เกณฑ์ปัจจุบัน: <?= $asset->depreciation_profile_id ?: '-' ?></div>
                    <div class="col-md-3">อายุ (เดือน): <?= $asset->useful_life_months ?: '-' ?></div>
                    <div class="col-md-3">อัตรา: <?= $asset->depreciation_rate !== null ? $asset->depreciation_rate . '%' : '-' ?></div>
                    <div class="col-md-3">เริ่มคิด: <?= $asset->depreciation_start_date ?: '-' ?></div>
                </div>

                <?= Html::beginForm(['change'], 'post', ['class' => 'row g-3']) ?>
                    <?= Html::hiddenInput('asset_id', $asset->id) ?>
                    <div class="col-md-4">
                        <label class="form-label">เกณฑ์ใหม่</label>
                        <select name="new_depreciation_profile_id" class="form-select" required>
                            <option value="">— เลือกเกณฑ์ —</option>
                            <?php foreach ($profiles as $p): ?>
                                <option value="<?= $p->id ?>"><?= Html::encode($p->code . ' — ' . $p->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">วันที่มีผล</label>
                        <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">ขอบเขตการเปลี่ยน</label>
                        <select name="change_scope" class="form-select" required>
                            <?php foreach ($scopeOptions as $k => $label): ?>
                                <option value="<?= $k ?>"><?= Html::encode($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เลขที่เอกสารอ้างอิง</label>
                        <input type="text" name="document_reference" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เหตุผล</label>
                        <input type="text" name="reason" class="form-control">
                    </div>
                    <div class="col-12">
                        <div class="alert alert-warning small mb-2">
                            <i data-lucide="alert-triangle"></i> งวดที่ปิดแล้วจะคงค่าเดิม ระบบจะไม่คำนวณประวัติใหม่ทั้งหมด — งวดที่ยังไม่ปิดจะใช้เกณฑ์ใหม่เมื่อคำนวณครั้งถัดไป
                        </div>
                        <?= Html::submitButton('<i data-lucide="save"></i> บันทึกการเปลี่ยนเกณฑ์', [
                            'class' => 'btn btn-primary',
                            'data' => ['confirm' => 'ยืนยันเปลี่ยนเกณฑ์ค่าเสื่อมของทรัพย์สินนี้?'],
                        ]) ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    <?php endif; ?>
</div>
