<?php

use yii\helpers\Html;
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
$this->beginBlock('page-title'); ?>
<div><h4 class="fw-semibold mb-1">แผนงาน/โครงการ</h4><div class="text-muted small">ภาพรวมประจำปีงบประมาณ <?= Html::encode($year) ?></div></div>
<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'overview']) ?><?php $this->endBlock();

$fmt = Yii::$app->formatter;
$statusList = Projects::statusList();
$approved = (int) ($byStatus[Projects::STATUS_APPROVED] ?? 0);
$pending = (int) (($byStatus[Projects::STATUS_DRAFT] ?? 0) + ($byStatus[Projects::STATUS_PROPOSED] ?? 0));

$this->registerCss(<<<CSS
.pm-overview{padding:.25rem 0 1.5rem}
.pm-panel{border:0;border-radius:14px}
.pm-panel .card-header{background:transparent;border-bottom:1px solid var(--bs-border-color-translucent);font-weight:700;font-size:.95rem;padding:.9rem 1.15rem}
.pm-panel .card-body{padding:1.05rem 1.15rem}
.pm-yearbar{display:inline-flex;align-items:center;gap:.5rem;background:var(--bs-tertiary-bg);border:1px solid var(--bs-border-color-translucent);border-radius:10px;padding:.35rem .6rem}
.pm-yearbar select{border:0;background:transparent;font-weight:600;min-width:80px}
.pm-status-row{display:flex;align-items:center;justify-content:space-between;padding:.55rem 0;border-bottom:1px dashed var(--bs-border-color-translucent)}
.pm-status-row:last-child{border-bottom:0}
.pm-dot{width:10px;height:10px;border-radius:50%;display:inline-block;flex:none}
.pm-dept{margin-bottom:1rem}
.pm-dept:last-child{margin-bottom:0}
.pm-dept__bar{height:9px;border-radius:6px;background:var(--bs-secondary-bg);overflow:hidden}
.pm-dept__fill{height:100%;border-radius:6px;background:linear-gradient(90deg,var(--bs-primary),var(--bs-info))}
.pm-recent a{color:inherit}
.pm-num{font-variant-numeric:tabular-nums}
.pm-empty{padding:2.25rem 1rem;text-align:center;color:var(--bs-secondary-color)}
CSS, [], 'pm-overview');
?>
<div class="pm-overview">

    <div class="d-flex flex-wrap align-items-center justify-content-end mb-3">
        <form method="get" class="pm-yearbar">
            <i class="bi bi-calendar3 text-secondary"></i>
            <span class="small text-secondary">ปีงบประมาณ</span>
            <select name="thai_year" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php $opts = array_unique(array_merge([$year], array_map('intval', $years))); rsort($opts); ?>
                <?php foreach ($opts as $y): ?>
                    <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?= $this->render('@app/modules/hr/views/_kpi_cards', [
        'cards' => [
            ['label' => 'โครงการทั้งหมด', 'value' => $total, 'icon' => 'bi-folder2', 'color' => 'primary', 'hint' => 'ปีงบ ' . $year],
            ['label' => 'งบประมาณรวม', 'value' => $fmt->asDecimal($budgetSum, 0), 'icon' => 'bi-cash-stack', 'color' => 'info', 'hint' => 'บาท'],
            ['label' => 'อนุมัติแล้ว', 'value' => $approved, 'icon' => 'bi-check2-circle', 'color' => 'success', 'hint' => 'โครงการที่ผ่านอนุมัติ'],
            ['label' => 'ร่าง/รออนุมัติ', 'value' => $pending, 'icon' => 'bi-pencil-square', 'color' => 'warning', 'hint' => 'ยังไม่อนุมัติ'],
        ],
    ]) ?>

    <div class="row g-3">
        <!-- สถานะโครงการ -->
        <div class="col-lg-5">
            <div class="card pm-panel shadow-sm h-100">
                <div class="card-header"><i class="bi bi-pie-chart me-1 text-primary"></i> สถานะโครงการ</div>
                <div class="card-body">
                    <?php foreach ($statusList as $st => $label): ?>
                        <?php $badge = (new Projects(['status' => $st]))->statusBadgeClass(); ?>
                        <div class="pm-status-row">
                            <span class="d-flex align-items-center gap-2">
                                <span class="pm-dot <?= $badge ?>"></span>
                                <?= Html::encode($label) ?>
                            </span>
                            <span class="text-end">
                                <span class="fw-bold pm-num"><?= $fmt->asInteger($byStatus[$st] ?? 0) ?></span>
                                <span class="text-muted small pm-num ms-2"><?= $fmt->asDecimal($budgetByStatus[$st] ?? 0, 0) ?> บ.</span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex align-items-center justify-content-between pt-3 mt-1 border-top">
                        <span class="fw-bold">รวมทั้งสิ้น</span>
                        <span class="text-end">
                            <span class="fw-bold pm-num"><?= $fmt->asInteger($total) ?></span>
                            <span class="text-primary fw-semibold pm-num ms-2"><?= $fmt->asDecimal($budgetSum, 0) ?> บ.</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- งบประมาณตามหน่วยงาน -->
        <div class="col-lg-7">
            <div class="card pm-panel shadow-sm h-100">
                <div class="card-header"><i class="bi bi-bar-chart-line me-1 text-info"></i> งบประมาณตามหน่วยงาน</div>
                <div class="card-body">
                    <?php if (!$byDept): ?>
                        <div class="pm-empty"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีข้อมูล</div>
                    <?php else: ?>
                        <?php $maxBudget = max(array_map(fn($d) => $d['budget'], $byDept)) ?: 1; ?>
                        <?php foreach ($byDept as $d): ?>
                            <?php $pct = max(3, round($d['budget'] / $maxBudget * 100)); ?>
                            <div class="pm-dept">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-truncate" style="max-width:58%" title="<?= Html::encode($d['name']) ?>"><?= Html::encode($d['name']) ?></span>
                                    <span class="small">
                                        <span class="text-muted me-2"><?= $fmt->asInteger($d['count']) ?> โครงการ</span>
                                        <span class="fw-bold text-primary pm-num"><?= $fmt->asDecimal($d['budget'], 0) ?> บ.</span>
                                    </span>
                                </div>
                                <div class="pm-dept__bar"><div class="pm-dept__fill" style="width: <?= $pct ?>%"></div></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- โครงการล่าสุด -->
    <div class="card pm-panel shadow-sm mt-3 pm-recent">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history me-1 text-secondary"></i> โครงการล่าสุด</span>
            <?= Html::a('ดูทั้งหมด <i class="bi bi-arrow-right"></i>', ['/pm/projects/index'], ['class' => 'small text-decoration-none']) ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <tbody>
                <?php if (!$recent): ?>
                    <tr><td><div class="pm-empty"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีโครงการ</div></td></tr>
                <?php endif; ?>
                <?php foreach ($recent as $p): ?>
                    <tr>
                        <td>
                            <?= Html::a(Html::encode($p->name), ['/pm/projects/view', 'id' => $p->id], ['class' => 'text-decoration-none fw-semibold']) ?>
                            <div class="small text-muted"><?= Html::encode($p->departmentPath()) ?></div>
                        </td>
                        <td class="text-end pm-num" style="width:150px"><?= $fmt->asDecimal($p->budget_total, 0) ?> บ.</td>
                        <td style="width:130px"><span class="badge <?= $p->statusBadgeClass() ?>"><?= Html::encode($p->statusLabel()) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
