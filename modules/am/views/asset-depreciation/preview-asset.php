<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\modules\am\services\DepreciationProfileResolver;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset|null $asset */
/** @var array|null $schedule */
/** @var mixed $assetId */

$this->title = 'ทดลองคำนวณค่าเสื่อม';
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="flask-conical"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <div class="card mb-3">
        <div class="card-body">
            <?= Html::beginForm(['preview-asset'], 'get', ['class' => 'row g-2 align-items-end']) ?>
                <div class="col-md-6">
                    <label class="form-label">ค้นครุภัณฑ์ (รหัส หรือชื่อ)</label>
                    <?= Select2::widget([
                        'name' => 'asset_id',
                        'value' => $assetId,
                        'initValueText' => $asset ? trim($asset->code . ' — ' . $asset->asset_name) : '',
                        'options' => ['placeholder' => 'พิมพ์รหัส หรือชื่อครุภัณฑ์...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
                            'ajax' => [
                                'url' => Url::to(['/am/asset-depreciation/asset-search']),
                                'dataType' => 'json',
                                'delay' => 250,
                                'data' => new JsExpression('function(params){ return {q: params.term}; }'),
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= Html::submitButton('<i data-lucide="calculator"></i> คำนวณ', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php if ($assetId && !$asset): ?>
        <div class="alert alert-warning">ไม่พบทรัพย์สิน id = <?= Html::encode($assetId) ?></div>
    <?php endif; ?>

    <?php if ($asset && $schedule): ?>
        <?php $resolved = $schedule['resolved'] ?? []; ?>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card"><div class="card-body">
                    <h6><?= Html::encode($asset->code) ?> — <?= Html::encode($asset->asset_name) ?></h6>
                    <table class="table table-sm mb-0">
                        <tr><th>ราคาทุน</th><td class="text-end"><?= number_format((float)$asset->price, 2) ?></td></tr>
                        <tr><th>ฐานค่าเสื่อม</th><td class="text-end"><?= number_format($schedule['depreciable_base'], 2) ?></td></tr>
                        <tr><th>มูลค่าซาก</th><td class="text-end"><?= number_format($schedule['salvage'], 2) ?></td></tr>
                        <tr><th>วันเริ่มคิด</th><td class="text-end"><?= $schedule['params']['acquisition_date'] ?? '-' ?></td></tr>
                        <tr><th>แหล่งเกณฑ์</th><td class="text-end">
                            <?php $srcLabels = [
                                DepreciationProfileResolver::SOURCE_ASSET => 'ทรัพย์สินรายชิ้น',
                                DepreciationProfileResolver::SOURCE_ITEM => 'รายการ',
                                DepreciationProfileResolver::SOURCE_CATEGORY => 'หมวด',
                                DepreciationProfileResolver::SOURCE_TYPE => 'ประเภทหลัก',
                            ]; ?>
                            <?= $resolved['source_type'] ? ($srcLabels[$resolved['source_type']] ?? $resolved['source_type']) : 'ไม่มีเกณฑ์ (ใช้ค่าจากทรัพย์สิน)' ?>
                        </td></tr>
                    </table>
                </div></div>
            </div>
        </div>

        <?php if (!$schedule['can_calculate']): ?>
            <div class="alert alert-warning"><?= Html::encode($schedule['message']) ?></div>
        <?php else: ?>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>งวดที่</th><th>เดือน</th><th class="text-end">วัน</th>
                                <th class="text-end">อัตรา%</th><th class="text-end">มูลค่าต้นงวด</th>
                                <th class="text-end">ค่าเสื่อม</th><th class="text-end">สะสม</th><th class="text-end">มูลค่าสุทธิ</th>
                                <th>สูตร</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedule['schedule'] as $r): ?>
                                <tr>
                                    <td><?= $r['month_index'] ?></td>
                                    <td><?= $r['period_date'] ?></td>
                                    <td class="text-end"><?= $r['days_used'] ?></td>
                                    <td class="text-end"><?= $r['rate_percent'] !== null ? number_format($r['rate_percent'], 2) : '-' ?></td>
                                    <td class="text-end"><?= number_format($r['beginning_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['depreciation'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['accumulated_depreciation'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['remaining_value'], 2) ?></td>
                                    <td class="small text-muted"><?= Html::encode($r['formula']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
