<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\components\RichText;
use app\modules\pm\components\KpiStatus;
use app\modules\pm\models\KpiIndicator;

/** @var app\modules\pm\models\KpiIndicator $model */
/** @var app\modules\pm\models\KpiIndicatorYear[] $years */
/** @var string|null $unitName @var app\modules\pm\models\KpiIndicatorYear|null $latest @var bool $canManage */

$this->title = 'รายละเอียดตัวชี้วัด';
$this->beginBlock('page-title'); ?>รายละเอียดตัวชี้วัด<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'kpi']) ?><?php $this->endBlock();
app\assets\RichTextAsset::register($this);

$num = static fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format((float) $v, 4, '.', ''), '0'), '.');
$rt = static fn ($val) => trim((string) $val) !== '' ? RichText::render($val) : '<span class="text-body-secondary">-</span>';

$labels = []; $targetData = []; $actualData = [];
foreach ($years as $y) {
    $labels[] = (int) $y->fiscal_year;
    $targetData[] = $y->target_value !== null ? (float) $y->target_value : null;
    $actualData[] = $y->actual_value !== null ? (float) $y->actual_value : null;
}
$hasChart = count(array_filter($actualData, fn($v) => $v !== null)) >= 1 || count(array_filter($targetData, fn($v) => $v !== null)) >= 1;

$latestStatus = $latest ? KpiStatus::evaluate($latest->target_value, $latest->actual_value, $model->operator) : KpiStatus::NODATA;
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1"><?= Html::encode(RichText::plain($model->name, 300)) ?></h1>
        <span class="badge rounded-pill" style="background:<?= Html::encode($model->group->color ?? '#6c757d') ?>1a;color:<?= Html::encode($model->group->color ?? '#6c757d') ?>"><?= Html::encode($model->group->name ?? '-') ?></span>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canManage): ?>
            <?= Html::a('<i class="bi bi-pencil-square me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> พิมพ์</button>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <h6 class="fw-bold mb-3">แนวโน้มหลายปี (เป้าหมาย vs ผลจริง)</h6>
            <?php if ($hasChart): ?>
                <div id="kpi-trend"></div>
            <?php else: ?>
                <div class="text-center text-muted py-5">ยังไม่มีข้อมูลรายปี</div>
            <?php endif; ?>
        </div></div>

        <div class="card border-0 shadow-sm"><div class="card-body p-0">
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">ปีงบประมาณ</th><th class="text-end">เป้าหมาย</th><th class="text-end">ผลจริง</th><th class="text-center pe-4">สถานะ</th></tr></thead>
                <tbody>
                <?php foreach ($years as $y): $st = KpiStatus::evaluate($y->target_value, $y->actual_value, $model->operator); ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= (int) $y->fiscal_year ?></td>
                        <td class="text-end" style="font-variant-numeric:tabular-nums"><?= Html::encode($num($y->target_value)) ?></td>
                        <td class="text-end" style="font-variant-numeric:tabular-nums"><?= Html::encode($num($y->actual_value)) ?></td>
                        <td class="text-center pe-4"><span class="badge <?= KpiStatus::badgeClass($st) ?>"><?= Html::encode(KpiStatus::label($st)) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$years): ?><tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีข้อมูลรายปี</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>

    <div class="col-lg-4">
        <?php /* สรุปผลประเมิน */ ?>
        <div class="card border-0 shadow-sm mb-3 border-start border-4 border-<?= $latestStatus === KpiStatus::PASS ? 'success' : ($latestStatus === KpiStatus::GAP ? 'danger' : 'secondary') ?>">
            <div class="card-body">
                <h6 class="fw-bold mb-2">สรุปผลประเมิน</h6>
                <?php if ($latest): ?>
                    <div class="mb-2"><span class="badge <?= KpiStatus::badgeClass($latestStatus) ?> fs-6"><?= Html::encode(KpiStatus::label($latestStatus)) ?></span>
                        <span class="text-muted small ms-1">ปี <?= (int) $latest->fiscal_year ?></span></div>
                    <div class="small">เป้าหมาย: <span class="fw-semibold"><?= Html::encode($num($latest->target_value)) ?></span> <?= Html::encode($model->unit) ?></div>
                    <div class="small">ผลจริง: <span class="fw-semibold"><?= Html::encode($num($latest->actual_value)) ?></span> <?= Html::encode($model->unit) ?></div>
                <?php else: ?>
                    <div class="text-body-secondary small">ยังไม่มีผลจริงบันทึกไว้</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm"><div class="card-body">
            <h6 class="fw-bold mb-3">รายละเอียดตัวชี้วัด</h6>
            <dl class="row small mb-0">
                <dt class="col-5 text-muted">หน่วยวัด</dt><dd class="col-7"><?= Html::encode($model->unit ?: '-') ?></dd>
                <dt class="col-5 text-muted">ทิศทาง</dt><dd class="col-7"><?= Html::encode(KpiIndicator::operatorList()[$model->operator] ?? '-') ?></dd>
                <dt class="col-5 text-muted">ความถี่</dt><dd class="col-7"><?= Html::encode(KpiIndicator::frequencyList()[$model->frequency] ?? '-') ?></dd>
                <dt class="col-5 text-muted">ผู้รับผิดชอบ</dt><dd class="col-7"><?= Html::encode($model->owner_name ?: '-') ?></dd>
                <dt class="col-5 text-muted">หน่วยงาน</dt><dd class="col-7"><?= Html::encode($unitName ?: '-') ?></dd>
            </dl>
            <hr class="my-3">
            <div class="mb-2"><div class="text-muted small mb-1">คำนิยาม</div><div class="erp-richtext"><?= $rt($model->definition) ?></div></div>
            <div class="mb-2"><div class="text-muted small mb-1">สูตรคำนวณ</div><div class="erp-richtext"><?= $rt($model->formula) ?></div></div>
            <div class="mb-2"><div class="text-muted small mb-1">วิธีประเมินผล</div><div class="erp-richtext"><?= $rt($model->evaluation_method) ?></div></div>
            <div class="mb-0"><div class="text-muted small mb-1">แหล่งข้อมูล</div><div class="erp-richtext"><?= $rt($model->data_source) ?></div></div>
        </div></div>
    </div>
</div>

<?php if ($hasChart): ?>
<?php
$chart = Json::encode([
    'labels' => $labels,
    'target' => $targetData,
    'actual' => $actualData,
]);
$this->registerJs(<<<JS
(function(){
    if (typeof ApexCharts === 'undefined') return;
    var d = $chart;
    var el = document.getElementById('kpi-trend');
    if (!el) return;
    new ApexCharts(el, {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        series: [
            { name: 'เป้าหมาย', data: d.target },
            { name: 'ผลจริง', data: d.actual }
        ],
        xaxis: { categories: d.labels },
        stroke: { width: [2, 3], dashArray: [6, 0], curve: 'straight' },
        colors: ['#f59e0b', '#2563eb'],
        markers: { size: 4 },
        legend: { position: 'top' },
        tooltip: { shared: true }
    }).render();
})();
JS);
?>
<?php endif; ?>
