<?php

use yii\helpers\Html;
use app\components\RichText;
use app\modules\pm\components\KpiStatus;

/** @var array $byGroup  groupName => KpiRow[] */
/** @var int $year @var int[] $years @var array $unitNames @var string $hospitalName */

$this->title = 'รายงานตัวชี้วัด ปีงบประมาณ ' . $year;
$this->beginBlock('page-title'); ?>รายงานตัวชี้วัด<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'overview']) ?><?php $this->endBlock();

$num = static fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format((float) $v, 4, '.', ''), '0'), '.');

$this->registerCss(<<<'CSS'
@media print {
    .navbar, .pm-module-menu, .no-print, .app-header, header, .btn { display: none !important; }
    .kpi-report { margin: 0; }
    .card { border: 0 !important; box-shadow: none !important; }
    table { font-size: 12px; }
}
.kpi-report table th, .kpi-report table td { white-space: nowrap; }
.kpi-report .kpi-name { white-space: normal; min-width: 240px; }
CSS);
?>

<div class="kpi-report">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h4 mb-1"><?= Html::encode($hospitalName) ?></h1>
            <div class="text-body-secondary">รายงานสรุปตัวชี้วัด ประจำปีงบประมาณ พ.ศ. <?= (int) $year ?></div>
        </div>
        <button type="button" class="btn btn-outline-secondary no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i> พิมพ์</button>
    </div>

    <?php if (!$byGroup): ?>
        <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">ยังไม่มีข้อมูลตัวชี้วัด</div></div>
    <?php endif; ?>

    <?php foreach ($byGroup as $groupName => $rows): ?>
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <h5 class="fw-bold mb-3"><?= Html::encode($groupName) ?> <span class="text-muted fw-normal fs-6">(<?= count($rows) ?>)</span></h5>
            <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light"><tr>
                    <th class="kpi-name">ตัวชี้วัด</th>
                    <th>หน่วยงาน</th>
                    <th class="text-center">หน่วย</th>
                    <?php foreach ($years as $fy): ?><th class="text-end"><?= (int) $fy ?></th><?php endforeach; ?>
                    <th class="text-end">เป้า <?= (int) $year ?></th>
                    <th class="text-center">สถานะ</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="kpi-name"><?= Html::encode(RichText::plain($r->name, 300)) ?></td>
                        <td class="small"><?= Html::encode($r->orgUnitId ? ($unitNames[$r->orgUnitId] ?? '-') : '-') ?></td>
                        <td class="text-center small"><?= Html::encode($r->unit ?: '-') ?></td>
                        <?php foreach ($years as $fy): ?>
                            <td class="text-end" style="font-variant-numeric:tabular-nums"><?= Html::encode($num($r->series[$fy] ?? null)) ?></td>
                        <?php endforeach; ?>
                        <td class="text-end" style="font-variant-numeric:tabular-nums"><?= Html::encode($num($r->target)) ?></td>
                        <td class="text-center"><span class="badge <?= KpiStatus::badgeClass($r->status) ?>"><?= Html::encode(KpiStatus::label($r->status)) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div></div>
    <?php endforeach; ?>
</div>
