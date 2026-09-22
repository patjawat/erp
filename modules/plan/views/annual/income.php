<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $year */
/** @var int[] $actualYears */
/** @var int[] $planYears */
/** @var array $groups */
/** @var array $totA */
/** @var array $totP */

$this->title = 'แผนรายรับ';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'แผนประจำปี', 'url' => ['/plan/annual', 'year' => $year]];
$this->params['breadcrumbs'][] = $this->title;

$nCols = count($actualYears) + count($planYears) + 1;
$fmt = fn($v) => number_format((float) $v, 2);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-cash-coin"></i><?= Html::encode($this->title) ?>
    </h4>
</div>
<div class="small text-body-secondary">ตั้งยอดแผนรายรับล่วงหน้า 3 ปี (คีย์เอง) เทียบผลจริงย้อนหลัง 3 ปี</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'income']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3"><div class="card-body d-flex flex-wrap justify-content-between align-items-end gap-2">
    <a href="<?= Url::to(['/plan/annual', 'year' => $year]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-table me-1"></i>ดูแผนประจำปีรวม</a>
    <form method="get" class="d-flex gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบเริ่มแผน (พ.ศ.)</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:120px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
</div></div>

<?= Html::beginForm(['income-save'], 'post') ?>
<?= Html::hiddenInput('year', $year) ?>

<div class="card border"><div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light text-center">
            <tr>
                <th rowspan="2" style="min-width:260px">หมวดรายรับ</th>
                <th colspan="<?= count($actualYears) ?>">ผลจริงย้อนหลัง</th>
                <th colspan="<?= count($planYears) ?>">แผน</th>
            </tr>
            <tr>
                <?php foreach ($actualYears as $ay): ?><th style="width:120px"><?= $ay ?></th><?php endforeach; ?>
                <?php foreach ($planYears as $py): ?><th class="text-primary" style="width:140px"><?= $py ?></th><?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!$groups): ?>
                <tr><td colspan="<?= $nCols ?>" class="text-center text-body-secondary py-3">ยังไม่มีหมวดรายรับ — เพิ่มได้ที่ <a href="<?= Url::to(['/finance/cash/category']) ?>">จัดการผังบัญชี</a></td></tr>
            <?php endif; ?>
            <?php foreach ($groups as $gi => $g): ?>
                <tr class="table-light">
                    <td class="fw-semibold"><?= Html::encode($g['name']) ?></td>
                    <?php foreach ($actualYears as $ay): ?><td class="text-end fw-semibold"><?= $fmt($g['subA'][$ay] ?? 0) ?></td><?php endforeach; ?>
                    <?php foreach ($planYears as $py): ?><td class="text-end fw-semibold text-warning-emphasis" data-sub="<?= $gi ?>" data-year="<?= $py ?>"><?= $fmt($g['subP'][$py] ?? 0) ?></td><?php endforeach; ?>
                </tr>
                <?php foreach ($g['rows'] as $row): ?>
                    <tr>
                        <td class="ps-4"><?= Html::encode($row['name']) ?></td>
                        <?php foreach ($actualYears as $ay): ?>
                            <td class="text-end text-body-secondary">
                                <?= $fmt($row['actual'][$ay] ?? 0) ?><?php if (!empty($row['actualIsPlan'][$ay])): ?><span class="badge text-bg-light border text-warning-emphasis fw-normal ms-1" title="ยังไม่มีรับจริง — แสดงยอดตามแผนที่เคยตั้งไว้">แผน</span><?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php foreach ($planYears as $py): ?>
                            <td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end plan-input"
                                name="plan[<?= $row['id'] ?>][<?= $py ?>]" data-group="<?= $gi ?>" data-year="<?= $py ?>"
                                value="<?= ($row['plan'][$py] ?? 0) > 0 ? $fmt($row['plan'][$py]) : '' ?>" placeholder="0.00"></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr class="table-primary fw-bold">
                <td class="text-end">รวมรายรับ</td>
                <?php foreach ($actualYears as $ay): ?><td class="text-end"><?= $fmt($totA[$ay] ?? 0) ?></td><?php endforeach; ?>
                <?php foreach ($planYears as $py): ?><td class="text-end" data-total="1" data-year="<?= $py ?>"><?= $fmt($totP[$py] ?? 0) ?></td><?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div></div>

<div class="d-flex justify-content-end mt-3">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกแผนรายรับ', ['class' => 'btn btn-primary']) ?>
</div>
<?= Html::endForm() ?>

<?php
// รวมยอดสด: พิมพ์ในช่องแผนแล้วยอดกลุ่ม + รวมรายรับ (ต่อปี) ขยับตามทันที
// รวมเฉพาะช่อง input จริง ๆ ต่อปี ไม่นับแถวยอดกลุ่มซ้ำ → กันปัญหา "ผลรวมไม่ตรง/นับซ้ำ"
$this->registerJs(<<<'JS'
(function () {
    var form = document.querySelector('form[action*="income-save"]') || document.querySelector('.plan-input') && document.querySelector('.plan-input').closest('form');
    if (!form) { return; }
    function num(v) { return parseFloat(String(v).replace(/[, ]/g, '')) || 0; }
    function money(n) { return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function recalc() {
        var years = {};   // year -> total
        var subs = {};    // group|year -> subtotal
        form.querySelectorAll('input.plan-input').forEach(function (el) {
            var y = el.getAttribute('data-year');
            var g = el.getAttribute('data-group');
            var v = num(el.value);
            years[y] = (years[y] || 0) + v;
            subs[g + '|' + y] = (subs[g + '|' + y] || 0) + v;
        });
        form.querySelectorAll('td[data-sub]').forEach(function (td) {
            var key = td.getAttribute('data-sub') + '|' + td.getAttribute('data-year');
            td.textContent = money(subs[key] || 0);
        });
        form.querySelectorAll('td[data-total]').forEach(function (td) {
            td.textContent = money(years[td.getAttribute('data-year')] || 0);
        });
    }
    form.addEventListener('input', function (e) {
        if (e.target && e.target.classList.contains('plan-input')) { recalc(); }
    });
})();
JS);
?>
