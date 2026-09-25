<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\plan\components\IcuEvaluator;

/** @var yii\web\View $this */
/** @var int $year */
/** @var string $level */
/** @var int $m */
/** @var array $opening */
/** @var array $rows */
/** @var array $tierCount */
/** @var int $evaluatedCount */
/** @var array $th */

$this->title = 'ICU 3 มิติ';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$fmt = fn ($v) => $v === null ? '–' : number_format((float) $v, 2);
$pct = fn ($v) => number_format((float) $v, 1) . '%';
// สีตามระบบเขต (bg, text, border)
$tierStyle = [
    'normal' => ['#ecfdf5', '#065f46', '#a7f3d0'],
    'watchlist' => ['#fffbeb', '#b45309', '#fde68a'],
    'medium' => ['#fef3c7', '#92400e', '#f59e0b'],
    'high' => ['#ffedd5', '#c2410c', '#fb923c'],
    'critical' => ['#fee2e2', '#991b1b', '#ef4444'],
];
$badge = function (array $res) use ($tierStyle) {
    [$bg, $fg, $bd] = $tierStyle[$res['tier']];
    return '<span class="badge rounded-pill" style="background:' . $bg . ';color:' . $fg . ';border:1px solid ' . $bd . '">Case ' . $res['case'] . ' · ' . Html::encode(IcuEvaluator::TIERS[$res['tier']][0]) . '</span>';
};
$flag = fn (bool $bad) => $bad
    ? '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">ผิดปกติ</span>'
    : '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">ปกติ</span>';
$sel = $m ? $rows[$m] : null;
$res = $sel['res'] ?? null;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><i class="bi bi-heart-pulse"></i>ICU 3 มิติ — ผลการกำกับและประเมินสัญญาณเตือนภัยล่วงหน้า 3 ด้าน</h4>
</div>
<div class="small text-body-secondary">1. รายรับสุทธิ 2. เงินบำรุงลดลง 3. ภาระผูกพันเพิ่มขึ้น — เมทริกซ์ 8 Case ตามเกณฑ์ระบบแผนเงินบำรุง สป.สธ. (เมนู 2.3)</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'icu']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3"><div class="card-body">
    <form method="get" class="d-flex flex-wrap gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบประมาณ (พ.ศ.)</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:110px"></div>
        <div><label class="form-label mb-0 small">ระดับ รพ. (เกณฑ์)</label>
            <?= Html::dropDownList('level', $level, IcuEvaluator::LEVELS, ['class' => 'form-select form-select-sm']) ?></div>
        <div><label class="form-label mb-0 small">งวดเดือนที่ประเมิน</label>
            <select name="m" class="form-select form-select-sm">
                <?php foreach ($rows as $r): ?>
                    <option value="<?= $r['m'] ?>" <?= $r['m'] === $m ? 'selected' : '' ?> <?= $r['evaluated'] ? '' : 'disabled' ?>><?= Html::encode($r['label']) ?><?= $r['evaluated'] ? '' : ' (ยังไม่มีข้อมูล)' ?></option>
                <?php endforeach; ?>
            </select></div>
        <button type="submit" class="btn btn-sm btn-primary">ประเมิน</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" data-bs-toggle="collapse" data-bs-target="#icuGuide"><i class="bi bi-question-circle me-1"></i>คู่มือ เกณฑ์ ICU 3 มิติ</button>
    </form>
</div></div>

<div class="collapse mb-3" id="icuGuide"><div class="card border"><div class="card-body small">
    <div class="row g-3">
        <div class="col-md-4"><div class="fw-semibold mb-1">มิติ 1 รายรับสุทธิเป็นลบ</div>รายรับของงวด − รายจ่ายของงวด &lt; 0</div>
        <div class="col-md-4"><div class="fw-semibold mb-1">มิติ 2 เงินบำรุงลดลง</div>เงินบำรุงคงเหลือติดลบ หรือลดลงจากสิ้นงวดก่อนเกิน <?= $th['cashDrop'] ?>% (รพช. 10% / รพท. 7% / รพศ. 5%)</div>
        <div class="col-md-4"><div class="fw-semibold mb-1">มิติ 3 ภาระผูกพันเพิ่มขึ้น</div>ภาระผูกพันเกิน <?= $th['commitRatio'] ?>% ของเงินบำรุงคงเหลือ (รพช. 40% / อื่น 50%) หรือ ภาระผูกพันโตเกิน 15% และเกิน 30% ของคงเหลือ</div>
    </div>
    <hr>
    <div class="row g-2">
        <div class="col-md-6">Case 1 ไม่ติดมิติใด = Normal · Case 2/3/4 ติดมิติ 1/2/3 อย่างเดียว = Watchlist (เฝ้าระวัง)</div>
        <div class="col-md-6">Case 5 (มิติ 1+2) · Case 6 (1+3) = Medium · Case 7 (2+3) = High · Case 8 ครบ 3 มิติ = Critical (Financial ICU)</div>
    </div>
    <hr>
    <div class="text-body-secondary">แหล่งข้อมูล: รายรับ/รายจ่ายจากหน้ารับ-จ่ายเงินบำรุง (ตามวันที่เอกสาร) · เงินบำรุงคงเหลือ = ยอดยกมาต้นปี + รับ − จ่าย สะสม · ภาระผูกพัน = เจ้าหนี้ที่อนุมัติแล้วแต่ยังจ่ายไม่ครบ ณ สิ้นเดือน — ถ้าตัวเลขในระบบยังไม่ครบ (เช่น ภาระผูกพันค่าตอบแทนค้างจ่าย) กรอกทับได้ที่ "ปรับยอดรายเดือน"</div>
</div></div></div>

<?php if ($opening['amount'] === null): ?>
    <div class="alert alert-warning small"><i class="bi bi-exclamation-triangle me-1"></i>ยังไม่มีเงินบำรุงคงเหลือยกมาต้นปีงบ <?= $year ?> — ระบบจึงคำนวณเงินคงเหลือรายเดือนไม่ได้ กรอกได้ที่
        <a href="<?= Url::to(['/plan/annual/liquidity', 'year' => $year]) ?>">ข้อมูลสภาพคล่อง</a> หรือกรอกทับรายเดือนที่ "ปรับยอดรายเดือน" ด้านล่าง</div>
<?php else: ?>
    <div class="small text-body-secondary mb-2">เงินบำรุงคงเหลือยกมาต้นปีงบ <?= $year ?>: <span class="fw-semibold text-body"><?= $fmt($opening['amount']) ?></span> บาท (จาก<?= Html::encode($opening['source']) ?>)</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <?php foreach (IcuEvaluator::TIERS as $tk => [$tl]): [$bg, $fg, $bd] = $tierStyle[$tk]; ?>
        <div class="col-6 col-md"><div class="card h-100" style="background:<?= $bg ?>;border-color:<?= $bd ?>"><div class="card-body py-2 d-flex justify-content-between align-items-center">
            <span class="small fw-semibold" style="color:<?= $fg ?>"><?= Html::encode($tl) ?></span>
            <span class="fs-4 fw-bold" style="color:<?= $fg ?>"><?= $tierCount[$tk] ?></span>
        </div></div></div>
    <?php endforeach; ?>
    <div class="col-6 col-md"><div class="card border h-100"><div class="card-body py-2 d-flex justify-content-between align-items-center">
        <span class="small fw-semibold">งวดที่ประเมินแล้ว</span><span class="fs-4 fw-bold"><?= $evaluatedCount ?>/12</span>
    </div></div></div>
</div>

<?php if ($res): [$bg, $fg, $bd] = $tierStyle[$res['tier']]; ?>
    <div class="card mb-3" style="border:2px solid <?= $bd ?>">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2" style="background:<?= $bg ?>;color:<?= $fg ?>">
            <span class="fw-semibold">ผลประเมินงวด <?= Html::encode($sel['label']) ?></span>
            <?= $badge($res) ?>
        </div>
        <div class="card-body">
            <div class="fw-semibold mb-1" style="color:<?= $fg ?>"><?= Html::encode($res['meaning']) ?></div>
            <div class="small fw-semibold mt-2">มาตรการกำกับ &amp; Action</div>
            <ul class="small mb-3"><?php foreach ($res['actions'] as $a): ?><li><?= Html::encode($a) ?></li><?php endforeach; ?></ul>
            <div class="row g-3">
                <div class="col-md-4"><div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between"><span class="fw-semibold">1. รายรับสุทธิ</span><?= $flag($res['d1']) ?></div>
                    <div class="fs-5 fw-bold mt-1 <?= $res['net'] < 0 ? 'text-danger' : 'text-success' ?>"><?= $fmt($res['net']) ?></div>
                    <div class="small text-body-secondary">รับ <?= $fmt($sel['rev']) ?> − จ่าย <?= $fmt($sel['exp']) ?></div>
                </div></div>
                <div class="col-md-4"><div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between"><span class="fw-semibold">2. เงินบำรุงลดลง</span><?= $flag($res['d2']) ?></div>
                    <div class="fs-5 fw-bold mt-1"><?= $pct($res['cashDropPct']) ?></div>
                    <div class="small text-body-secondary">คงเหลือ <?= $fmt($sel['cashPrev']) ?> → <?= $fmt($sel['cash']) ?> (เกณฑ์ลดลงไม่เกิน <?= $th['cashDrop'] ?>%)</div>
                </div></div>
                <div class="col-md-4"><div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between"><span class="fw-semibold">3. ภาระผูกพัน</span><?= $flag($res['d3']) ?></div>
                    <div class="fs-5 fw-bold mt-1"><?= $pct($res['comRatioPct']) ?> <span class="small fw-normal">ของคงเหลือ</span></div>
                    <div class="small text-body-secondary"><?= $fmt($sel['comPrev']) ?> → <?= $fmt($sel['com']) ?> (โต <?= $pct($res['comGrowthPct']) ?>; เกณฑ์ ≤ <?= $th['commitRatio'] ?>%)</div>
                </div></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-light border text-center text-body-secondary">ยังไม่มีงวดที่ประเมินได้ในปีงบ <?= $year ?> — ต้องมีรายการรับ-จ่ายเงินบำรุงในเดือนนั้น และมียอดเงินบำรุงคงเหลือยกมา</div>
<?php endif; ?>

<div class="card border mb-3">
    <div class="card-header bg-body-tertiary fw-semibold">ผลรายงวด ปีงบประมาณ <?= $year ?></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0" style="font-size:.88rem">
            <thead class="table-light text-center align-middle">
                <tr>
                    <th>งวด</th><th>รายรับ</th><th>รายจ่าย</th><th>1. รายรับสุทธิ</th>
                    <th>เงินบำรุงสิ้นงวด</th><th>2. ลดลง</th>
                    <th>ภาระผูกพัน</th><th>3. % ของคงเหลือ</th><th>ระดับความเสี่ยง</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): $x = $r['res']; ?>
                <tr class="<?= $r['m'] === $m ? 'table-active' : '' ?>">
                    <td class="text-nowrap"><a href="<?= Url::to(['index', 'year' => $year, 'level' => $level, 'm' => $r['m']]) ?>" class="text-decoration-none"><?= Html::encode($r['label']) ?></a></td>
                    <td class="text-end"><?= $r['rev'] ? $fmt($r['rev']) : '' ?></td>
                    <td class="text-end"><?= $r['exp'] ? $fmt($r['exp']) : '' ?></td>
                    <td class="text-end <?= $x && $x['d1'] ? 'text-danger fw-semibold' : '' ?>"><?= $x ? $fmt($x['net']) : '' ?></td>
                    <td class="text-end"><?= $x ? $fmt($r['cash']) : '' ?><?= $x && $r['cashOverride'] ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis" title="กรอกทับ">ปรับ</span>' : '' ?></td>
                    <td class="text-end <?= $x && $x['d2'] ? 'text-danger fw-semibold' : '' ?>"><?= $x ? $pct($x['cashDropPct']) : '' ?></td>
                    <td class="text-end"><?= $x ? $fmt($r['com']) : '' ?><?= $x && $r['comOverride'] ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis" title="กรอกทับ">ปรับ</span>' : '' ?></td>
                    <td class="text-end <?= $x && $x['d3'] ? 'text-danger fw-semibold' : '' ?>"><?= $x ? $pct($x['comRatioPct']) : '' ?></td>
                    <td class="text-center"><?= $x ? $badge($x) : '<span class="text-body-secondary small">รอข้อมูล</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border mb-3">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-sliders me-1"></i>ปรับยอดรายเดือน (กรอกทับค่าที่ระบบคำนวณ)</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#icuOverride">แสดง/ซ่อน</button>
    </div>
    <div class="collapse" id="icuOverride">
        <?= Html::beginForm(['save'], 'post') ?>
        <?= Html::hiddenInput('year', $year) ?>
        <?= Html::hiddenInput('level', $level) ?>
        <div class="card-body pb-0 small text-body-secondary">เว้นว่าง = ใช้ค่าที่ระบบคำนวณ (แสดงเป็นตัวจาง) · ยอดเงินบำรุงที่กรอกทับจะยกไปคำนวณเดือนถัดไป</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light text-center"><tr><th>งวด</th><th style="width:200px">เงินบำรุงคงเหลือสิ้นงวด</th><th style="width:200px">ภาระผูกพันสิ้นงวด</th><th>หมายเหตุ</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td class="text-nowrap"><?= Html::encode($r['label']) ?></td>
                        <td><input type="text" inputmode="decimal" class="form-control form-control-sm text-end" name="ov[<?= $r['m'] ?>][cash]"
                            value="<?= $r['cashOverride'] ? number_format((float) $r['cash'], 2, '.', '') : '' ?>" placeholder="<?= $r['cashAuto'] !== null ? number_format((float) $r['cashAuto'], 2) : '' ?>"></td>
                        <td><input type="text" inputmode="decimal" class="form-control form-control-sm text-end" name="ov[<?= $r['m'] ?>][com]"
                            value="<?= $r['comOverride'] ? number_format((float) $r['com'], 2, '.', '') : '' ?>" placeholder="<?= $r['comAuto'] !== null ? number_format((float) $r['comAuto'], 2) : '' ?>"></td>
                        <td><input type="text" class="form-control form-control-sm" name="ov[<?= $r['m'] ?>][note]" value="<?= Html::encode((string) $r['note']) ?>" maxlength="255"></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end"><?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกยอดปรับ', ['class' => 'btn btn-sm btn-primary']) ?></div>
        <?= Html::endForm() ?>
    </div>
</div>
