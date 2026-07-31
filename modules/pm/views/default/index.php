<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\pm\models\Projects;

/** @var yii\web\View $this */
/** @var int $year */
/** @var array $years */
/** @var array $byStatus */
/** @var array $budgetByStatus */
/** @var array $byDept */
/** @var int $total */
/** @var float $budgetSum */
/** @var Projects[] $recent */

$this->title = 'ภาพรวม';
$this->beginBlock('page-title'); ?>แผนงาน/โครงการ<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'overview']) ?><?php $this->endBlock();

$fmt = Yii::$app->formatter;
$statusList = Projects::statusList();
?>
<div class="pm-overview container-fluid">

    <div class="d-flex flex-wrap align-items-center mb-3 gap-2">
        <form method="get" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0">ปีงบประมาณ</label>
            <select name="thai_year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <?php $opts = array_unique(array_merge([$year], array_map('intval', $years))); rsort($opts); ?>
                <?php foreach ($opts as $y): ?>
                    <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">โครงการทั้งหมด</div>
                <div class="fs-3 fw-bold"><?= $fmt->asInteger($total) ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">งบประมาณรวม (บาท)</div>
                <div class="fs-4 fw-bold text-primary"><?= $fmt->asDecimal($budgetSum, 2) ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">อนุมัติแล้ว</div>
                <div class="fs-3 fw-bold text-success"><?= $fmt->asInteger($byStatus[Projects::STATUS_APPROVED] ?? 0) ?></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">ร่าง/รออนุมัติ</div>
                <div class="fs-3 fw-bold text-warning"><?= $fmt->asInteger(($byStatus[Projects::STATUS_DRAFT] ?? 0) + ($byStatus[Projects::STATUS_PROPOSED] ?? 0)) ?></div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <!-- สถานะโครงการ (จำนวน + งบประมาณ) -->
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header fw-semibold">สถานะโครงการ (ปี <?= $year ?>)</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>สถานะ</th><th class="text-end">จำนวน</th><th class="text-end">งบประมาณ (บาท)</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($statusList as $st => $label): ?>
                            <tr>
                                <td><span class="badge <?= (new Projects(['status' => $st]))->statusBadgeClass() ?> me-1">&nbsp;</span><?= Html::encode($label) ?></td>
                                <td class="text-end"><?= $fmt->asInteger($byStatus[$st] ?? 0) ?></td>
                                <td class="text-end"><?= $fmt->asDecimal($budgetByStatus[$st] ?? 0, 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold border-top">
                                <td>รวม</td>
                                <td class="text-end"><?= $fmt->asInteger($total) ?></td>
                                <td class="text-end"><?= $fmt->asDecimal($budgetSum, 0) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- งบประมาณตามหน่วยงาน -->
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header fw-semibold">งบประมาณตามหน่วยงาน (ปี <?= $year ?>)</div>
                <div class="card-body">
                    <?php if (!$byDept): ?>
                        <div class="text-center text-muted py-4">ยังไม่มีข้อมูล</div>
                    <?php else: ?>
                        <?php $maxBudget = max(array_map(fn($d) => $d['budget'], $byDept)) ?: 1; ?>
                        <?php foreach ($byDept as $d): ?>
                            <?php $pct = round($d['budget'] / $maxBudget * 100); ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-semibold text-truncate" style="max-width:60%" title="<?= Html::encode($d['name']) ?>"><?= Html::encode($d['name']) ?></span>
                                    <span><span class="text-muted me-2"><?= $fmt->asInteger($d['count']) ?> โครงการ</span><span class="fw-bold text-primary"><?= $fmt->asDecimal($d['budget'], 0) ?> บ.</span></span>
                                </div>
                                <div class="progress" style="height:8px">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- โครงการล่าสุด -->
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">โครงการล่าสุด (ปี <?= $year ?>)</span>
            <?= Html::a('ดูทั้งหมด', ['/pm/projects/index'], ['class' => 'small text-decoration-none']) ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <tbody>
                <?php if (!$recent): ?>
                    <tr><td class="text-center text-muted py-4">ยังไม่มีโครงการ</td></tr>
                <?php endif; ?>
                <?php foreach ($recent as $p): ?>
                    <tr>
                        <td>
                            <?= Html::a(Html::encode($p->name), ['/pm/projects/view', 'id' => $p->id], ['class' => 'text-decoration-none fw-semibold']) ?>
                            <div class="small text-muted"><?= Html::encode($p->departmentPath()) ?></div>
                        </td>
                        <td class="text-end" style="width:140px"><?= $fmt->asDecimal($p->budget_total, 0) ?> บ.</td>
                        <td style="width:130px"><span class="badge <?= $p->statusBadgeClass() ?>"><?= Html::encode($p->statusLabel()) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
