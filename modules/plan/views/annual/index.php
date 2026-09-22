<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $year */
/** @var int $cmpYear */
/** @var int[] $actualYears */
/** @var int[] $planYears */
/** @var array $groups */
/** @var array $incomeTot */
/** @var array $expenseTypes */
/** @var array $expenseTot */
/** @var array $net */
/** @var array $compare */
/** @var array $liquidity */

$this->title = 'แผนประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$allYears = array_merge($actualYears, $planYears);
$nCols = count($allYears) + 1;
$fmt = fn($v) => number_format((float) $v, 2);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar3"></i><?= Html::encode($this->title) ?>
    </h4>
</div>
<div class="small text-body-secondary">ภาพรวมแผนรับ-จ่ายล่วงหน้า 3 ปี เทียบผลจริงย้อนหลัง 3 ปี · รายจ่ายดึงจากแผนรายจ่ายอัตโนมัติ</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'annual']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3"><div class="card-body d-flex flex-wrap justify-content-between align-items-end gap-2">
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= Url::to(['excel', 'year' => $year]) ?>" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
        <a href="<?= Url::to(['income', 'year' => $year]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>แก้ไขแผนรายรับ</a>
        <a href="<?= Url::to(['liquidity', 'year' => $year]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-wallet2 me-1"></i>ยกมา / แนบ 1-2</a>
    </div>
    <form method="get" class="d-flex gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบเริ่มแผน (พ.ศ.)</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:120px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
</div></div>

<div class="card border mb-3"><div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0 annual-table">
        <thead class="table-light text-center">
            <tr>
                <th rowspan="2" style="min-width:260px">รายการ</th>
                <th colspan="<?= count($actualYears) ?>">ผลจริงย้อนหลัง</th>
                <th colspan="<?= count($planYears) ?>">แผน</th>
            </tr>
            <tr>
                <?php foreach ($actualYears as $ay): ?><th style="width:120px"><?= $ay ?></th><?php endforeach; ?>
                <?php foreach ($planYears as $py): ?><th class="text-primary" style="width:130px"><?= $py ?></th><?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <!-- รายรับ -->
            <tr class="table-secondary"><td colspan="<?= $nCols ?>" class="fw-bold">รายรับ</td></tr>
            <?php foreach ($groups as $g): ?>
                <tr class="table-light">
                    <td class="fw-semibold"><?= Html::encode($g['name']) ?></td>
                    <?php foreach ($allYears as $y): ?><td class="text-end fw-semibold"><?= $fmt($g['sub'][$y] ?? 0) ?></td><?php endforeach; ?>
                </tr>
                <?php foreach ($g['rows'] as $row): ?>
                    <tr>
                        <td class="ps-4"><?= Html::encode($row['name']) ?></td>
                        <?php foreach ($allYears as $y): ?><td class="text-end text-body-secondary"><?= $fmt($row['vals'][$y] ?? 0) ?><?php if (!empty($row['valsIsPlan'][$y])): ?><span class="badge text-bg-light border text-warning-emphasis fw-normal ms-1" title="ยังไม่มีรับจริง — แสดงยอดตามแผนที่เคยตั้งไว้">แผน</span><?php endif; ?></td><?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr class="table-primary fw-bold">
                <td class="text-end">รวมรายรับ</td>
                <?php foreach ($allYears as $y): ?><td class="text-end"><?= $fmt($incomeTot[$y] ?? 0) ?></td><?php endforeach; ?>
            </tr>

            <!-- รายจ่าย -->
            <tr class="table-secondary"><td colspan="<?= $nCols ?>" class="fw-bold">รายจ่าย <span class="fw-normal small text-body-secondary">(ดึงจากแผนรายจ่าย — อ่านอย่างเดียว)</span></td></tr>
            <?php if (!$expenseTypes): ?>
                <tr><td colspan="<?= $nCols ?>" class="text-center text-body-secondary py-3">ยังไม่มีข้อมูลแผนรายจ่าย — เพิ่มได้ที่เมนู <a href="<?= Url::to(['/plan/overview']) ?>">แผนรายจ่าย</a></td></tr>
            <?php endif; ?>
            <?php foreach ($expenseTypes as $type): ?>
                <tr class="table-light">
                    <td class="fw-semibold"><?= Html::encode($type['title']) ?></td>
                    <?php foreach ($allYears as $y): ?><td class="text-end fw-semibold"><?= $fmt($type['vals'][$y] ?? 0) ?></td><?php endforeach; ?>
                </tr>
                <?php foreach ($type['cats'] as $cat): ?>
                    <tr>
                        <td class="ps-4"><?= Html::encode($cat['title']) ?></td>
                        <?php foreach ($allYears as $y): ?><td class="text-end text-body-secondary"><?= $fmt($cat['vals'][$y] ?? 0) ?></td><?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr class="table-primary fw-bold">
                <td class="text-end">รวมรายจ่าย</td>
                <?php foreach ($allYears as $y): ?><td class="text-end"><?= $fmt($expenseTot[$y] ?? 0) ?></td><?php endforeach; ?>
            </tr>

            <!-- ===== บล็อกสภาพคล่อง (ตามแบบฟอร์มแผนเงินบำรุง สป.สธ.) ===== -->
            <tr class="fw-semibold border-top border-2">
                <td class="text-end">รับสูง (ต่ำ) กว่าจ่ายสุทธิ</td>
                <?php foreach ($allYears as $y): ?><td class="text-end <?= ($liquidity[$y]['net'] ?? 0) < 0 ? 'text-danger' : '' ?>"><?= $fmt($liquidity[$y]['net'] ?? 0) ?></td><?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-end text-body-secondary">บวก เงินคงเหลือสะสมยกมา</td>
                <?php foreach ($allYears as $y): ?><td class="text-end text-body-secondary"><?= $fmt($liquidity[$y]['opening'] ?? 0) ?></td><?php endforeach; ?>
            </tr>
            <tr class="fw-semibold table-light">
                <td class="text-end">เงินคงเหลือทั้งสิ้น (1)</td>
                <?php foreach ($allYears as $y): ?><td class="text-end"><?= $fmt($liquidity[$y]['closing'] ?? 0) ?></td><?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-end text-body-secondary">หัก เงินกองทุนรอการจัดสรร (4)</td>
                <?php foreach ($allYears as $y): ?><td class="text-end text-body-secondary"><?= $fmt($liquidity[$y]['reserve'] ?? 0) ?></td><?php endforeach; ?>
            </tr>
            <tr>
                <td class="text-end text-body-secondary">หัก ภาระผูกพัน (5)</td>
                <?php foreach ($allYears as $y): ?><td class="text-end text-body-secondary"><?= $fmt($liquidity[$y]['commitment'] ?? 0) ?></td><?php endforeach; ?>
            </tr>
            <tr class="fw-bold border-top border-3 border-primary-subtle" style="background:var(--bs-primary-bg-subtle)">
                <td class="text-end">เงินคงเหลือหลังหัก (4)(5) — สภาพคล่องแท้จริง</td>
                <?php foreach ($allYears as $y): $a = $liquidity[$y]['after'] ?? 0; ?>
                    <td class="text-end <?= $a < 0 ? 'text-danger' : 'text-success' ?>"><?= $fmt($a) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr class="small">
                <td class="text-end text-body-secondary">อัตราส่วนรายได้/ค่าใช้จ่าย (I/E)</td>
                <?php foreach ($allYears as $y): $ie = $liquidity[$y]['ie'] ?? null; ?>
                    <td class="text-end text-body-secondary <?= ($ie !== null && $ie < 1) ? 'text-danger' : '' ?>">
                        <?= $ie === null ? '–' : number_format($ie, 2) ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div></div>

<!-- เทียบแผน-ผล ปีปัจจุบัน -->
<?php
$cmpCells = function (float $plan, float $actual, bool $expenseSense = false) use ($fmt) {
    $diff = $actual - $plan;
    $pct = $plan > 0 ? ($actual / $plan * 100) : null;
    // รายรับ: ผลต่ำกว่าแผน = แย่(แดง); รายจ่าย: ผลสูงกว่าแผน = แย่(แดง)
    $bad = $expenseSense ? ($diff > 0) : ($diff < 0);
    $cls = abs($diff) < 0.005 ? '' : ($bad ? 'text-danger' : 'text-success');
    return '<td class="text-end">' . $fmt($plan) . '</td>'
        . '<td class="text-end">' . $fmt($actual) . '</td>'
        . '<td class="text-end ' . $cls . '">' . $fmt($diff) . '</td>'
        . '<td class="text-end text-body-secondary">' . ($pct === null ? '–' : number_format($pct, 0) . '%') . '</td>';
};
?>
<div class="card border">
    <div class="card-header bg-body-tertiary fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-bar-chart-line"></i> เปรียบเทียบแผน–ผล ปีงบประมาณ <?= $cmpYear ?>
        <span class="small text-body-secondary fw-normal">(อัปเดตตามการบันทึกจริง)</span>
    </div>
    <div class="card-body">
        <!-- รายรับ รายหมวด -->
        <div class="fw-semibold mb-2"><i class="bi bi-cash-coin me-1"></i>รายรับ (รายหมวด — แผน vs รับจริง)</div>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered align-middle mb-0 annual-cmp">
                <thead class="table-light text-center">
                    <tr><th class="text-start">หมวดรายรับ</th><th style="width:150px">แผน</th><th style="width:150px">รับจริง</th><th style="width:140px">ต่าง</th><th style="width:80px">%</th></tr>
                </thead>
                <tbody>
                    <?php if (!$compare['incomeRows']): ?>
                        <tr><td colspan="5" class="text-center text-body-secondary py-3">ยังไม่มีแผน/ผลรายรับปีนี้</td></tr>
                    <?php endif; ?>
                    <?php foreach ($compare['incomeRows'] as $r): ?>
                        <tr><td class="ps-3"><?= Html::encode($r['name']) ?></td><?= $cmpCells($r['plan'], $r['actual']) ?></tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-primary fw-bold">
                    <tr><td class="text-end">รวมรายรับ</td><?= $cmpCells($compare['incPlan'], $compare['incActual']) ?></tr>
                </tfoot>
            </table>
        </div>

        <!-- รายจ่าย รายหมวด -->
        <div class="fw-semibold mb-2"><i class="bi bi-receipt me-1"></i>รายจ่าย (รายหมวด — แผน vs จัดซื้อจริงตามแผน)</div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0 annual-cmp">
                <thead class="table-light text-center">
                    <tr><th class="text-start">หมวดรายจ่าย</th><th style="width:150px">แผน</th><th style="width:150px">จัดซื้อจริง</th><th style="width:140px">ต่าง</th><th style="width:80px">%</th></tr>
                </thead>
                <tbody>
                    <?php if (!$compare['expTypes']): ?>
                        <tr><td colspan="5" class="text-center text-body-secondary py-3">ยังไม่มีแผน/ผลรายจ่ายปีนี้</td></tr>
                    <?php endif; ?>
                    <?php foreach ($compare['expTypes'] as $type): ?>
                        <tr class="table-light">
                            <td class="fw-semibold"><?= Html::encode($type['title']) ?></td>
                            <?= $cmpCells($type['plan'], $type['actual'], true) ?>
                        </tr>
                        <?php foreach ($type['cats'] as $cat): ?>
                            <tr><td class="ps-4"><?= Html::encode($cat['title']) ?></td><?= $cmpCells($cat['plan'], $cat['actual'], true) ?></tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-primary fw-bold">
                    <tr><td class="text-end">รวมรายจ่าย</td><?= $cmpCells($compare['expPlan'], $compare['expActual'], true) ?></tr>
                </tfoot>
            </table>
        </div>
        <div class="small text-body-secondary mt-2">
            <i class="bi bi-info-circle me-1"></i>"จัดซื้อจริง" = มูลค่าที่ตรวจรับแล้วของใบสั่งซื้อที่ผูกกับแผน (orders ตรวจรับ status ≥ 5) — ยังไม่ใช่ยอดจ่ายเงินสดจริง (คนละขั้นในวงจร)
        </div>
    </div>
</div>

<?php
// อ่านตัวเลขง่ายขึ้น: ขยายพื้นที่หน้า + กันตัวเลขตกบรรทัด/ถูกตัด + จัดเลขชิดหลักเท่ากัน
$this->registerCss(<<<CSS
main > .container-fluid { max-width: 1800px !important; }
.annual-table td, .annual-table th, .annual-cmp td, .annual-cmp th { padding: .45rem .7rem; }
.annual-table td.text-end, .annual-cmp td.text-end { font-variant-numeric: tabular-nums; white-space: nowrap; }
.annual-table thead th { white-space: nowrap; }
CSS);
?>
