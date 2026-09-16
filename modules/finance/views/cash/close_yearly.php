<?php

use app\modules\finance\models\FinanceCashCategory;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $fy */
/** @var array $types [IN=>[groups], OUT=>[groups]] each group {name, rows:[{id,name,amount}], subtotal} */
/** @var float $totIn @var float $totOut @var float $net */
/** @var array $H reconciliation+composition */
/** @var float $balance1 @var float $balanceAfter @var float $comp2 */
/** @var bool $hasSaved */

$this->title = 'ปิดบัญชีประจำปี';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-journal-check" aria-hidden="true"></i><?= Html::encode($this->title) ?> ปีงบ <?= $fy ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>worksheet ปิดงบสิ้นปี — แก้ยอดหมวดได้ + reconciliation (กด “ซิงค์ข้อมูล” ดึงยอดจริงมาเติม)<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$fmt = fn ($v) => number_format((float) $v, 2);
$num = fn ($v) => (float) $v != 0.0 ? number_format((float) $v, 2) : '';
$gi = 0; // running group index สำหรับ JS
?>

<?= $this->render('_menu', ['active' => 'close']) ?>
<?= $this->render('_close_menu', ['active' => 'yearly']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3"><div class="card-body d-flex flex-wrap justify-content-between align-items-end gap-2">
    <form method="get" class="d-flex gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบประมาณ</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $fy ?>" style="width:120px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['close-yearly', 'year' => $fy, 'sync' => 1]) ?>" class="btn btn-sm btn-info" onclick="return confirm('ดึงยอดจริงจากรายการมาเติมช่อง? (ยังไม่บันทึกจนกว่าจะกดบันทึก)')"><i class="bi bi-arrow-repeat me-1"></i>ซิงค์ข้อมูล</a>
        <a href="<?= Url::to(['close-excel', 'report' => 'yearly', 'year' => $fy]) ?>" class="btn btn-sm btn-success"><i class="bi bi-file-earmark-excel me-1"></i>ส่งออก Excel</a>
    </div>
</div></div>

<?php if (!$hasSaved): ?>
    <div class="alert alert-info py-2 small"><i class="bi bi-info-circle me-1"></i>ยังไม่เคยบันทึกปิดบัญชีปีนี้ — ช่องหมวดเติมยอดจริงจากรายการให้แล้ว ปรับได้แล้วกด “บันทึก”</div>
<?php endif; ?>

<?= Html::beginForm(['close-yearly-save'], 'post', ['id' => 'yc-form']) ?>
<?= Html::hiddenInput('fiscal_year', $fy) ?>
<div class="d-flex justify-content-end mb-2"><?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกปิดบัญชี', ['class' => 'btn btn-primary']) ?></div>

<div class="card border"><div class="table-responsive"><table class="table table-bordered table-sm align-middle mb-0">
    <thead class="table-dark text-center"><tr><th>รายการ</th><th style="width:220px">จำนวนเงินปิดบัญชี</th></tr></thead>
    <tbody>
        <?php foreach ([FinanceCashCategory::TYPE_IN => 'รายรับ', FinanceCashCategory::TYPE_OUT => 'รายจ่าย'] as $type => $label):
            $cls = $type === FinanceCashCategory::TYPE_IN ? 'yc-in' : 'yc-out'; ?>
            <tr class="table-secondary"><td colspan="2" class="fw-bold"><?= $label ?></td></tr>
            <?php foreach ($types[$type] as $g): $gi++; ?>
                <tr class="table-light">
                    <td class="fw-semibold"><?= Html::encode($g['name']) ?></td>
                    <td class="text-end fw-semibold" id="sub-g<?= $gi ?>"><?= $fmt($g['subtotal']) ?></td>
                </tr>
                <?php foreach ($g['rows'] as $row): ?>
                    <tr>
                        <td class="ps-4"><small><?= Html::encode($row['name']) ?></small></td>
                        <td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end <?= $cls ?>"
                            data-grp="g<?= $gi ?>" name="item[<?= $row['id'] ?>]" value="<?= $num($row['amount']) ?>" placeholder="0.00"></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <tr class="table-primary fw-bold">
                <td class="text-end">รวม<?= $label ?></td>
                <td class="text-end" id="tot-<?= $type ?>"><?= $fmt($type === FinanceCashCategory::TYPE_IN ? $totIn : $totOut) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr class="table-warning fw-bold"><td class="text-end">รายรับสูง (ต่ำกว่า) รายจ่ายสุทธิ</td><td class="text-end" id="yc-net"><?= $fmt($net) ?></td></tr>
        <tr><td class="text-end">บวกเงินคงเหลือสะสมยกมา</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end" id="yc-carried" name="carried_forward" value="<?= $num($H['carried_forward']) ?>"></td></tr>
        <tr class="fw-bold"><td class="text-end">เงินคงเหลือทั้งสิ้น (1)</td><td class="text-end" id="yc-b1"><?= $fmt($balance1) ?></td></tr>
        <tr><td class="text-end">กองทุนรอการจัดสรร (4)</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end" id="yc-fund" name="fund_pending" value="<?= $num($H['fund_pending']) ?>"></td></tr>
        <tr><td class="text-end">ภาระผูกพัน (5)</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end" id="yc-oblig" name="obligation" value="<?= $num($H['obligation']) ?>"></td></tr>
        <tr><td class="text-end">หักก่อหนี้ภาระผูกพันพัสดุ (6)</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end" id="yc-purch" name="purchase_obligation" value="<?= $num($H['purchase_obligation']) ?>"></td></tr>
        <tr class="table-warning fw-bold"><td class="text-end">เงินคงเหลือหลังหักตาม ข้อ (4) และ (5) (6)</td><td class="text-end" id="yc-after"><?= $fmt($balanceAfter) ?></td></tr>

        <tr class="table-secondary"><td colspan="2" class="fw-bold">เงินคงเหลือทั้งสิ้น ประกอบด้วย</td></tr>
        <tr><td class="ps-4">เงินสด / เทียบเท่าเงินสด</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end yc-comp" id="yc-cash" name="cash_amount" value="<?= $num($H['cash_amount']) ?>"></td></tr>
        <tr><td class="ps-4">เงินฝากคลัง</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end yc-comp" id="yc-treasury" name="treasury_amount" value="<?= $num($H['treasury_amount']) ?>"></td></tr>
        <tr><td class="ps-4">เงินฝากธนาคาร — ประเภทประจำ</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end yc-comp" id="yc-fixed" name="bank_fixed" value="<?= $num($H['bank_fixed']) ?>"></td></tr>
        <tr><td class="ps-4">เงินฝากธนาคาร — ประเภทออมทรัพย์</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end yc-comp" id="yc-savings" name="bank_savings" value="<?= $num($H['bank_savings']) ?>"></td></tr>
        <tr><td class="ps-4">เงินฝากธนาคาร — ประเภทกระแสรายวัน</td><td class="p-1"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end yc-comp" id="yc-current" name="bank_current" value="<?= $num($H['bank_current']) ?>"></td></tr>
        <tr class="table-warning fw-bold"><td class="text-end">รวม เงินคงเหลือทั้งสิ้น (2)</td><td class="text-end" id="yc-comp2"><?= $fmt($comp2) ?></td></tr>
    </tbody>
</table></div></div>

<div class="d-flex justify-content-end mt-3"><?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกปิดบัญชี', ['class' => 'btn btn-primary']) ?></div>
<?= Html::endForm() ?>

<?php
$this->registerJs(<<<'JS'
(function () {
    const money = v => (parseFloat(String(v).replace(/[, ]/g, '')) || 0);
    const fmt = v => v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const $ = id => document.getElementById(id);
    const sum = sel => [...document.querySelectorAll(sel)].reduce((a, e) => a + money(e.value), 0);

    function recalc() {
        // group subtotals
        const grp = {};
        document.querySelectorAll('#yc-form input[data-grp]').forEach(i => {
            grp[i.dataset.grp] = (grp[i.dataset.grp] || 0) + money(i.value);
        });
        Object.entries(grp).forEach(([g, v]) => { const c = $('sub-' + g); if (c) c.textContent = fmt(v); });

        const totIn = sum('.yc-in'), totOut = sum('.yc-out');
        $('tot-IN').textContent = fmt(totIn);
        $('tot-OUT').textContent = fmt(totOut);
        const net = totIn - totOut;
        $('yc-net').textContent = fmt(net);
        const carried = money($('yc-carried').value);
        const b1 = net + carried;
        $('yc-b1').textContent = fmt(b1);
        const after = b1 - money($('yc-fund').value) - money($('yc-oblig').value) - money($('yc-purch').value);
        $('yc-after').textContent = fmt(after);
        const comp2 = sum('.yc-comp');
        $('yc-comp2').textContent = fmt(comp2);
    }
    document.querySelectorAll('#yc-form input').forEach(i => i.addEventListener('input', recalc));
    recalc();
})();
JS);
?>
