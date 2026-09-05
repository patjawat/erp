<?php

use app\modules\qms\models\CycleItem;
use app\modules\qms\models\Evidence;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var int $fiscalYear */
/** @var array $perStandard */
/** @var array $agg */
/** @var int $overallPercent */
/** @var int $pending */
/** @var app\modules\qms\models\Evidence[] $recentEvidence */
/** @var app\modules\qms\models\Cycle[] $upcoming */

$this->title = 'ระบบติดตามมาตรฐานโรงพยาบาล';
$years = range($fiscalYear + 1, $fiscalYear - 2);

// donut สถานะ (conic-gradient)
$statusTones = ['complete' => '#198754', 'in_progress' => '#ffc107', 'none' => '#dc3545', 'na' => '#adb5bd'];
$statusLabels = ['complete' => 'ครบถ้วน', 'in_progress' => 'กำลังดำเนินการ', 'none' => 'ยังขาด', 'na' => 'ไม่เกี่ยวข้อง'];
$totalItems = $agg['complete'] + $agg['in_progress'] + $agg['none'] + $agg['na'];
$stops = [];
$acc = 0;
foreach ($statusTones as $k => $color) {
    $seg = $totalItems > 0 ? ($agg[$k] * 100 / $totalItems) : 0;
    $stops[] = "$color {$acc}% " . ($acc + $seg) . '%';
    $acc += $seg;
}
$donut = 'conic-gradient(' . implode(',', $stops) . ')';

$kpis = [
    ['label' => 'ความพร้อมรวม', 'value' => $overallPercent . '%', 'icon' => 'bi-shield-check', 'tone' => 'success'],
    ['label' => 'รายการติดตาม', 'value' => number_format($agg['countable']), 'icon' => 'bi-clipboard-data', 'tone' => 'primary'],
    ['label' => 'งานค้าง', 'value' => number_format($pending), 'icon' => 'bi-hourglass-split', 'tone' => 'warning'],
    ['label' => 'เกินกำหนด', 'value' => number_format($agg['overdue']), 'icon' => 'bi-exclamation-triangle', 'tone' => 'danger'],
];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ภาพรวมความพร้อมตามมาตรฐาน ปีงบ <?= $fiscalYear ?><?php $this->endBlock(); ?>

<style>
.qms-donut { width: 150px; height: 150px; border-radius: 50%; display: grid; place-items: center; position: relative; }
.qms-donut::before { content: ''; position: absolute; width: 96px; height: 96px; border-radius: 50%; background: var(--bs-body-bg); }
.qms-donut b { position: relative; font-size: 1.4rem; }
</style>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-semibold mb-0"><i class="bi bi-speedometer2 me-1"></i> ภาพรวมผู้บริหาร</h1>
            <div class="text-body-secondary small">ความพร้อมตามมาตรฐาน</div>
        </div>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="small text-body-secondary mb-0">ปีงบ</label>
            <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
        <?= Html::endForm() ?>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/qms/menu', ['active' => 'overview']) ?></div>

    <!-- KPI -->
    <div class="row g-3 mb-3">
        <?php foreach ($kpis as $kpi): ?>
            <div class="col-6 col-xl-3">
                <div class="card border shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $kpi['tone'] ?>-subtle text-<?= $kpi['tone'] ?>-emphasis" style="width:48px;height:48px;">
                            <i class="bi <?= $kpi['icon'] ?> fs-4"></i>
                        </span>
                        <div>
                            <div class="text-body-secondary small"><?= $kpi['label'] ?></div>
                            <div class="h4 fw-bold mb-0"><?= $kpi['value'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <!-- ความพร้อมตามมาตรฐาน -->
        <div class="col-12 col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold">ความพร้อมตามมาตรฐาน</div>
                <div class="card-body">
                    <?php $hasCycle = false; foreach ($perStandard as $row): if (!$row['cycle']) { continue; } $hasCycle = true; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-medium"><?= Html::encode($row['standard']->short_name ?: $row['standard']->code) ?> <span class="text-body-secondary">· <?= Html::encode($row['standard']->name) ?></span></span>
                                <span class="fw-semibold"><?= $row['percent'] ?>%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-<?= $row['percent'] >= 90 ? 'success' : ($row['percent'] >= 50 ? 'primary' : 'warning') ?>" style="width: <?= $row['percent'] ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$hasCycle): ?>
                        <div class="text-center text-body-secondary py-4">
                            <i class="bi bi-calendar-x fs-3"></i>
                            <div>ยังไม่มีมาตรฐานที่เปิดรอบปี <?= $fiscalYear ?></div>
                            <?= Html::a('ไปที่ทะเบียนมาตรฐาน', ['standards', 'fy' => $fiscalYear], ['class' => 'btn btn-sm btn-outline-primary mt-2']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- donut สถานะงาน -->
        <div class="col-12 col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold">สถานะงานตามมาตรฐาน</div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center">
                        <div class="qms-donut" style="background: <?= $donut ?>;">
                            <b><?= number_format($totalItems) ?></b>
                        </div>
                        <div class="flex-grow-1" style="min-width:160px;">
                            <?php foreach ($statusTones as $k => $color): ?>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small"><span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:<?= $color ?>;"></span><?= $statusLabels[$k] ?></span>
                                    <span class="small fw-semibold"><?= number_format($agg[$k]) ?> <span class="text-body-secondary">(<?= $totalItems > 0 ? round($agg[$k] * 100 / $totalItems) : 0 ?>%)</span></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- หลักฐานล่าสุด -->
        <div class="col-12 col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold">หลักฐานล่าสุด</div>
                <?php if (empty($recentEvidence)): ?>
                    <div class="card-body text-center text-body-secondary py-4"><i class="bi bi-inbox fs-3"></i><div>ยังไม่มีหลักฐานในปีนี้</div></div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentEvidence as $ev): $std = $ev->cycleItem->cycle->standard ?? null; ?>
                            <?= Html::a('
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-paperclip text-primary"></i>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="text-truncate">' . Html::encode($ev->title ?: $ev->file_name ?: $ev->url) . '</div>
                                        <div class="small text-body-secondary">' . ($std ? Html::encode($std->short_name ?: $std->code) . ' · ' : '') . Html::encode($ev->sourceLabel()) . '</div>
                                    </div>
                                    <span class="small text-body-secondary">' . ($ev->created_at ? Yii::$app->formatter->asDate($ev->created_at) : '') . '</span>
                                </div>', ['item', 'id' => $ev->cycle_item_id], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- กำหนดตรวจประเมิน -->
        <div class="col-12 col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold">กำหนดการตรวจประเมินที่จะมาถึง</div>
                <?php if (empty($upcoming)): ?>
                    <div class="card-body text-center text-body-secondary py-4"><i class="bi bi-calendar-check fs-3"></i><div>ยังไม่ได้กำหนดวันทบทวน</div></div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcoming as $cy): $days = (int) ceil((strtotime($cy->next_review_date) - time()) / 86400); ?>
                            <div class="list-group-item d-flex align-items-center gap-2">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-medium text-truncate"><?= Html::encode($cy->standard->name ?? $cy->standard->code ?? '') ?></div>
                                    <div class="small text-body-secondary"><?= Yii::$app->formatter->asDate($cy->next_review_date) ?> · ปีงบ <?= (int) $cy->fiscal_year ?></div>
                                </div>
                                <span class="badge rounded-pill text-bg-<?= $days <= 30 ? 'danger' : ($days <= 90 ? 'warning' : 'secondary') ?>">อีก <?= $days ?> วัน</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- การดำเนินการด่วน -->
    <div class="card border shadow-sm mt-3">
        <div class="card-body d-flex flex-wrap gap-2">
            <?= Html::a('<i class="bi bi-shield-check me-1"></i>ทะเบียนมาตรฐาน', ['standards', 'fy' => $fiscalYear], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-folder2-open me-1"></i>คลังหลักฐาน', ['evidence'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('<i class="bi bi-bar-chart-line me-1"></i>รายงาน', ['report'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>
