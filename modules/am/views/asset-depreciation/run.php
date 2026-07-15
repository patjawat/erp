<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\modules\am\models\AccountingPeriod;

/** @var yii\web\View $this */
/** @var AccountingPeriod $period */
/** @var array $result */

$this->title = 'ตรวจสอบผลค่าเสื่อม: ' . $period->name;

$sum = ['dep' => 0, 'accum' => 0, 'nbv' => 0];
foreach ($result['rows'] as $r) {
    $sum['dep'] += $r['depreciation'];
    $sum['accum'] += $r['accumulated'];
    $sum['nbv'] += $r['nbv'];
}

$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = ['label' => 'งวดบัญชี', 'url' => ['/am/accounting-period/index', 'fiscal_year' => $period->fiscal_year]];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="calculator"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= Html::a('<i data-lucide="arrow-left"></i> กลับไปงวดบัญชี', ['/am/accounting-period/index', 'fiscal_year' => $period->fiscal_year], ['class' => 'btn btn-light']) ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $cls): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $cls ?>"><?= Yii::$app->session->getFlash($flash) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <i data-lucide="info"></i> <?= Html::encode($result['message']) ?>
            <span class="badge bg-secondary ms-2">สถานะงวด: <?= AccountingPeriod::statusOptions()[$period->status] ?? $period->status ?></span>
        </div>
        <?php if (!$period->isClosed()): ?>
            <?= Html::beginForm(['save', 'period_id' => $period->id], 'post') ?>
                <?= Html::submitButton('<i data-lucide="save"></i> บันทึกผลคำนวณ', [
                    'class' => 'btn btn-primary',
                    'data' => ['confirm' => 'บันทึกผลคำนวณลงงวดนี้?'],
                ]) ?>
            <?= Html::endForm() ?>
        <?php else: ?>
            <span class="text-muted">งวดปิดแล้ว — แก้ไขด้วยการปรับปรุง/กลับรายการ</span>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>รหัส</th><th>ชื่อ</th>
                        <th class="text-end">วัน</th>
                        <th class="text-end">ค่าเสื่อมงวด</th>
                        <th class="text-end">ค่าเสื่อมสะสม</th>
                        <th class="text-end">มูลค่าสุทธิ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($result['rows'])): ?>
                        <tr><td colspan="7" class="text-center text-muted">ไม่มีทรัพย์สินที่ต้องคิดค่าเสื่อมในงวดนี้</td></tr>
                    <?php else: foreach ($result['rows'] as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= Html::encode($r['code']) ?></td>
                            <td><?= Html::encode($r['name']) ?></td>
                            <td class="text-end"><?= $r['days'] ?></td>
                            <td class="text-end"><?= number_format($r['depreciation'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['accumulated'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['nbv'], 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <?php if (!empty($result['rows'])): ?>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="4" class="text-end">รวม (<?= count($result['rows']) ?> รายการ)</td>
                            <td class="text-end"><?= number_format($sum['dep'], 2) ?></td>
                            <td class="text-end"><?= number_format($sum['accum'], 2) ?></td>
                            <td class="text-end"><?= number_format($sum['nbv'], 2) ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if ($period->isClosed()): ?>
        <div class="card mt-3">
            <div class="card-header fw-semibold"><i data-lucide="wrench"></i> สร้างรายการปรับปรุง (งวดปิดแล้ว)</div>
            <div class="card-body">
                <?= Html::beginForm(['adjustment', 'period_id' => $period->id], 'post', ['class' => 'row g-2 align-items-end']) ?>
                    <div class="col-md-4">
                        <label class="form-label">ค้นครุภัณฑ์ (รหัส หรือชื่อ)</label>
                        <?= Select2::widget([
                            'name' => 'asset_id',
                            'options' => ['placeholder' => 'พิมพ์รหัส หรือชื่อครุภัณฑ์...', 'required' => true],
                            'pluginOptions' => [
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
                    <div class="col-md-3">
                        <label class="form-label">ยอดปรับปรุง (+/-)</label>
                        <input type="number" step="0.0001" name="adjustment_amount" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">หมายเหตุ</label>
                        <input type="text" name="note" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <?= Html::submitButton('<i data-lucide="plus"></i> ปรับปรุง', ['class' => 'btn btn-warning w-100']) ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>
    <?php endif; ?>
</div>
