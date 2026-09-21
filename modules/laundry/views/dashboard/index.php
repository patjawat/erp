<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\modules\hr\models\Organization;

/**
 * @var View $this
 * @var int $fiscalYear
 * @var int[] $years
 * @var string $start
 * @var string $end
 * @var float $recvKg
 * @var int $recvStops
 * @var int $rounds
 * @var int $batchesDone
 * @var int $intoClean
 * @var int $issued
 * @var string[] $monthLabels
 * @var float[] $monthKg
 * @var array $backlog  label => qty
 * @var array $cleanByType
 * @var array $topUnits
 */
$this->title = 'ภาพรวมงานซักฟอก';
$this->registerJsFile('@web/apexcharts/apexcharts.min.js', ['position' => View::POS_HEAD]);

// ชื่อหน่วยงานสำหรับ topUnits
$deptNames = [];
$deptIds = array_filter(array_column($topUnits, 'department_id'));
if ($deptIds) {
    $deptNames = Organization::find()->select(['name', 'id'])->where(['id' => $deptIds])->indexBy('id')->column();
}

$cards = [
    ['label' => 'รับผ้า (กก.)', 'value' => number_format($recvKg, 1), 'sub' => number_format($recvStops) . ' รายการเก็บ', 'icon' => 'bi-basket3', 'tone' => 'success'],
    ['label' => 'รอบซัก–อบเสร็จ', 'value' => number_format($batchesDone), 'sub' => number_format($rounds) . ' รอบเก็บผ้า', 'icon' => 'bi-moisture', 'tone' => 'info'],
    ['label' => 'เข้าคลังหลัก (ชิ้น)', 'value' => number_format($intoClean), 'sub' => 'ผ้าสะอาดพร้อมจ่าย', 'icon' => 'bi-building-check', 'tone' => 'primary'],
    ['label' => 'เบิกจ่าย (ชิ้น)', 'value' => number_format($issued), 'sub' => 'จ่ายให้หน่วยงาน', 'icon' => 'bi-box-arrow-right', 'tone' => 'warning'],
];
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'dashboard']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-speedometer2 me-2"></i><?= Html::encode($this->title) ?></h1>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="text-body-secondary small mb-0">ปีงบประมาณ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), [
                'class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()',
            ]) ?>
        <?= Html::endForm() ?>
    </div>
    <p class="text-body-secondary small mb-3">ช่วงข้อมูล: <?= Html::encode($start) ?> ถึง <?= Html::encode($end) ?></p>

    <!-- KPI cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($cards as $c): ?>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div style="min-width:0">
                            <div class="text-body-secondary small text-truncate"><?= Html::encode($c['label']) ?></div>
                            <div class="fs-3 fw-bold lh-1 my-1"><?= $c['value'] ?></div>
                            <div class="text-body-secondary small text-truncate"><?= Html::encode($c['sub']) ?></div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-<?= $c['tone'] ?> bg-opacity-10 text-<?= $c['tone'] ?>" style="width:52px;height:52px;flex:0 0 52px">
                            <i class="bi <?= $c['icon'] ?> fs-3"></i>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <!-- Monthly kg chart -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="bi bi-bar-chart-line me-2"></i>น้ำหนักผ้ารับเข้า รายเดือน (กก.)</h2>
                    <div id="lnd-month-chart" style="min-height:300px"></div>
                </div>
            </div>
        </div>
        <!-- Backlog -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="bi bi-hourglass-split me-2"></i>ผ้าค้างในระบบ (ชิ้น)</h2>
                    <?php $hasBacklog = array_sum($backlog) > 0; ?>
                    <?php if ($hasBacklog): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($backlog as $label => $qty): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span><?= Html::encode($label) ?></span>
                                    <span class="fw-semibold <?= $qty > 0 ? 'text-warning-emphasis' : 'text-body-secondary' ?>"><?= number_format($qty) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-body-secondary py-5">
                            <i class="bi bi-check2-circle fs-1 d-block mb-2"></i>ไม่มีผ้าค้างในระบบ
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Clean stock by type -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="bi bi-building me-2"></i>คลังหลักคงเหลือ ตามประเภทผ้า</h2>
                    <?php if ($cleanByType): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                <?php foreach ($cleanByType as $r): ?>
                                    <tr>
                                        <td><?= Html::encode($r['name']) ?></td>
                                        <td class="text-end fw-semibold"><?= number_format((int) $r['qty']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-body-secondary py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีผ้าในคลังหลัก</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top units -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3"><i class="bi bi-hospital me-2"></i>หน่วยงานที่รับผ้ามากสุด (กก.)</h2>
                    <?php if ($topUnits): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                <?php foreach ($topUnits as $u): $id = (int) $u['department_id']; ?>
                                    <tr>
                                        <td><?= Html::encode($deptNames[$id] ?? ('หน่วยงาน #' . $id)) ?></td>
                                        <td class="text-end fw-semibold"><?= number_format((float) $u['kg'], 1) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-body-secondary py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีข้อมูลรับผ้าในปีงบนี้</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$monthLabelsJson = json_encode($monthLabels, JSON_UNESCAPED_UNICODE);
$monthKgJson = json_encode($monthKg);
$js = <<<JS
(function(){
  if (typeof ApexCharts === 'undefined') return;
  var el = document.querySelector('#lnd-month-chart');
  if (!el) return;
  new ApexCharts(el, {
    chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
    series: [{ name: 'กก.', data: $monthKgJson }],
    xaxis: { categories: $monthLabelsJson },
    colors: ['#0d6efd'],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    dataLabels: { enabled: false },
    grid: { borderColor: 'rgba(0,0,0,.08)' },
    tooltip: { y: { formatter: function(v){ return Number(v).toLocaleString() + ' กก.'; } } }
  }).render();
})();
JS;
$this->registerJs($js, View::POS_END);
