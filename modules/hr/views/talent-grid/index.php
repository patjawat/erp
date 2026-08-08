<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\hr\models\TalentGrid;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var int $depId */
/** @var app\modules\hr\models\Organization[] $departments */
/** @var array<int, TalentGrid[]> $boxes */
/** @var app\modules\hr\models\Employees[] $unplaced */
/** @var int $totalEmployees */

$this->title = 'ตารางจำแนกศักยภาพบุคลากร 9 Box';
echo $this->render('@app/modules/hr/views/workforce/_styles');
echo $this->render('_styles');
$this->beginBlock('page-title'); echo Html::encode($this->title); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'talent']); $this->endBlock();

$boxMeta = TalentGrid::boxMeta();
$zoneMeta = TalentGrid::zoneMeta();

$counts = [];
foreach ($boxMeta as $boxNo => $meta) {
    $counts[$boxNo] = count($boxes[$boxNo] ?? []);
}
$placedTotal = array_sum($counts);

$zoneCounts = array_fill_keys(array_keys($zoneMeta), 0);
foreach ($boxMeta as $boxNo => $meta) {
    $zoneCounts[$meta['zone']] += $counts[$boxNo];
}

// การ์ดสรุปกลุ่มที่ HR ต้องตัดสินใจก่อน: ผู้สืบทอดตำแหน่ง ศักยภาพสูง กำลังหลัก และกลุ่มเสี่ยง
$cards = [
    ['label' => 'ผู้นำในอนาคต (Box 9)', 'icon' => 'bi-star-fill', 'accent' => '#15803d',
        'value' => $counts[9], 'hint' => 'กลุ่มเป้าหมายของแผนสืบทอดตำแหน่ง'],
    ['label' => 'บุคลากรศักยภาพสูง (Box 8)', 'icon' => 'bi-rocket-takeoff-fill', 'accent' => '#0f766e',
        'value' => $counts[8], 'hint' => 'ต้องเร่งรัดการพัฒนา'],
    ['label' => 'กำลังหลักขององค์กร (Box 5)', 'icon' => 'bi-people-fill', 'accent' => '#2457a7',
        'value' => $counts[5], 'hint' => 'ฐานกำลังหลักในการปฏิบัติงาน'],
    ['label' => 'กลุ่มเสี่ยง (Box 1+2)', 'icon' => 'bi-exclamation-octagon-fill', 'accent' => '#b42318',
        'value' => $counts[1] + $counts[2], 'hint' => 'ต้องดำเนินการโดยเร็ว'],
];

$columnHeads = [
    TalentGrid::LEVEL_LOW => 'ผลการปฏิบัติงาน<br>ระดับต่ำ',
    TalentGrid::LEVEL_MEDIUM => 'ผลการปฏิบัติงาน<br>ระดับปานกลาง',
    TalentGrid::LEVEL_HIGH => 'ผลการปฏิบัติงาน<br>ระดับสูง',
];
$rowHeads = [
    TalentGrid::LEVEL_HIGH => 'ศักยภาพสูง',
    TalentGrid::LEVEL_MEDIUM => 'ศักยภาพปานกลาง',
    TalentGrid::LEVEL_LOW => 'ศักยภาพต่ำ',
];
?>
<div class="workforce-shell">
    <?= $this->render('@app/modules/hr/views/workforce/_menu', ['active' => 'talent']) ?>

    <header class="workforce-head">
        <div>
            <h1>ตารางจำแนกศักยภาพบุคลากร 9 Box</h1>
            <p>จำแนกบุคลากรตามผลการปฏิบัติงานและศักยภาพ เพื่อประกอบการวางแผนสืบทอดตำแหน่งและการพัฒนารายบุคคล</p>
        </div>
    </header>

    <form class="tg-toolbar" method="get" action="<?= Url::to(['/hr/talent-grid/index']) ?>">
        <div>
            <label for="tg-fy">ปีงบประมาณ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), [
                'class' => 'form-select', 'id' => 'tg-fy', 'onchange' => 'this.form.submit()',
            ]) ?>
        </div>
        <div>
            <label for="tg-dep">หน่วยงาน</label>
            <?= Html::dropDownList('dep', $depId, \yii\helpers\ArrayHelper::map($departments, 'id', 'name'), [
                'class' => 'form-select', 'id' => 'tg-dep', 'prompt' => 'ทุกหน่วยงาน', 'onchange' => 'this.form.submit()',
            ]) ?>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="text-muted small">
                จัดวางแล้ว <strong><?= number_format($placedTotal) ?></strong> / <?= number_format($totalEmployees) ?> คน
            </span>
            <?= Html::a('<i class="bi bi-person-plus"></i> จัดวางบุคลากร', ['/hr/talent-grid/form', 'fy' => $fiscalYear], [
                'class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg',
            ]) ?>
        </div>
    </form>

    <?php if ($unplaced !== []): ?>
        <div class="tg-unplaced">
            <i class="bi bi-info-circle"></i>
            <span>ยังไม่ได้จัดวาง <strong><?= number_format(count($unplaced)) ?></strong> คน เช่น
                <?= Html::encode(implode(', ', array_map(
                    static fn ($employee): string => $employee->fullname(),
                    array_slice($unplaced, 0, 5)
                ))) ?><?= count($unplaced) > 5 ? ' และอีก ' . number_format(count($unplaced) - 5) . ' คน' : '' ?>
            </span>
        </div>
    <?php endif ?>

    <div class="tg-cards">
        <?php foreach ($cards as $card): ?>
            <div class="tg-card" style="--tg-accent:<?= $card['accent'] ?>">
                <span class="tg-card__label"><i class="<?= $card['icon'] ?>"></i><?= Html::encode($card['label']) ?></span>
                <span class="tg-card__value"><?= number_format($card['value']) ?></span>
                <span class="tg-card__hint"><?= Html::encode($card['hint']) ?></span>
            </div>
        <?php endforeach ?>
    </div>

    <div class="tg-layout">
        <section class="tg-panel">
            <h2>ตารางกริด 9 ช่อง</h2>
            <div class="tg-matrix">
                <div class="tg-matrix__corner"></div>
                <?php foreach ($columnHeads as $head): ?>
                    <div class="tg-colhead"><?= $head ?></div>
                <?php endforeach ?>

                <?php foreach ($rowHeads as $potential => $rowLabel): ?>
                    <div class="tg-rowhead"><span><?= Html::encode($rowLabel) ?></span></div>
                    <?php foreach (array_keys($columnHeads) as $performance): ?>
                        <?php
                        $boxNo = TalentGrid::boxNo((int) $performance, (int) $potential);
                        $meta = $boxMeta[$boxNo];
                        $rows = $boxes[$boxNo] ?? [];
                        ?>
                        <div class="tg-cell tg-cell--<?= $meta['zone'] ?>" title="<?= Html::encode($meta['action']) ?>">
                            <div class="tg-cell__head">
                                <span class="tg-cell__title">
                                    Box <?= $boxNo ?> · <?= Html::encode($meta['name']) ?>
                                    <small><?= Html::encode($meta['criteria']) ?></small>
                                </span>
                                <?= Html::a('+', ['/hr/talent-grid/form', 'fy' => $fiscalYear, 'performance' => $performance, 'potential' => $potential], [
                                    'class' => 'tg-cell__add open-modal',
                                    'data-size' => 'modal-lg',
                                    'title' => 'เพิ่มบุคลากรเข้ากล่องนี้',
                                ]) ?>
                            </div>
                            <?php if ($rows === []): ?>
                                <div class="tg-cell__empty">ยังไม่มีรายชื่อ</div>
                            <?php else: ?>
                                <div class="tg-people">
                                    <?php foreach ($rows as $row): ?>
                                        <?= Html::a(
                                            Html::encode($row->employee->fullname()),
                                            ['/hr/talent-grid/form', 'id' => $row->id],
                                            [
                                                'class' => 'tg-person open-modal',
                                                'data-size' => 'modal-lg',
                                                'title' => trim(($row->employee->empDepartment->name ?? '') . ' ' . ($row->note ? '· ' . $row->note : '')),
                                            ]
                                        ) ?>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>
                            <span class="tg-cell__count"><?= number_format($counts[$boxNo]) ?> คน</span>
                        </div>
                    <?php endforeach ?>
                <?php endforeach ?>
            </div>

            <div class="tg-legend">
                <?php foreach ($zoneMeta as $zone): ?>
                    <span><i style="background:<?= $zone['color'] ?>"></i><?= Html::encode($zone['label']) ?> · <?= Html::encode($zone['hint']) ?></span>
                <?php endforeach ?>
            </div>
        </section>

        <aside class="tg-side">
            <section class="tg-panel">
                <h2>สัดส่วนตามกลุ่ม</h2>
                <div id="tg-zone-chart"></div>
            </section>
            <section class="tg-panel">
                <h2>จำนวนบุคลากรราย Box</h2>
                <div id="tg-box-chart"></div>
            </section>
        </aside>
    </div>
</div>
<?php
$zoneLabels = [];
$zoneSeries = [];
$zoneColors = [];
foreach ($zoneMeta as $key => $zone) {
    $zoneLabels[] = $zone['label'];
    $zoneSeries[] = $zoneCounts[$key];
    $zoneColors[] = $zone['color'];
}
$boxLabels = [];
$boxSeries = [];
$boxColors = [];
foreach ($boxMeta as $boxNo => $meta) {
    $boxLabels[] = (string) $boxNo;
    $boxSeries[] = $counts[$boxNo];
    $boxColors[] = $zoneMeta[$meta['zone']]['color'];
}
$json = static fn ($value): string => json_encode($value, JSON_UNESCAPED_UNICODE);

$this->registerJs(<<<JS
(function () {
    if (typeof ApexCharts === 'undefined') { return; }
    new ApexCharts(document.querySelector('#tg-zone-chart'), {
        chart: { type: 'donut', height: 260 },
        series: {$json($zoneSeries)},
        labels: {$json($zoneLabels)},
        colors: {$json($zoneColors)},
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        noData: { text: 'ยังไม่มีข้อมูลการจัดวาง' },
        tooltip: { y: { formatter: function (v) { return v + ' คน'; } } }
    }).render();

    new ApexCharts(document.querySelector('#tg-box-chart'), {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        series: [{ name: 'จำนวนคน', data: {$json($boxSeries)} }],
        xaxis: { categories: {$json($boxLabels)}, title: { text: 'Box' } },
        yaxis: { labels: { formatter: function (v) { return Math.round(v); } } },
        colors: {$json($boxColors)},
        plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '55%' } },
        legend: { show: false },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (v) { return v + ' คน'; } } }
    }).render();
})();
JS);
