<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\plan\models\PlanAnnualAttachment;

/** @var yii\web\View $this */
/** @var int $year */
/** @var app\modules\plan\models\PlanAnnualLedger $ledger */
/** @var app\modules\plan\models\PlanAnnualAttachment[] $reserve */
/** @var app\modules\plan\models\PlanAnnualAttachment[] $commitment */

$this->title = 'ข้อมูลสภาพคล่อง (ยกมา / แนบ 1-2)';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'แผนประจำปี', 'url' => ['/plan/annual', 'year' => $year]];
$this->params['breadcrumbs'][] = $this->title;

$num = fn($v) => $v > 0 ? number_format((float) $v, 2) : '';

/** แถวรายการแนบ (existing + 3 แถวว่าง) */
$attachRows = function (string $kind, array $items) use ($num) {
    $html = '';
    $i = 0;
    $render = function ($idx, $name, $amount, $note) use ($kind, $num) {
        return '<tr>'
            . '<td class="text-center text-body-secondary" style="width:34px">' . ($idx + 1) . '</td>'
            . '<td><input type="text" class="form-control form-control-sm" name="' . $kind . '[' . $idx . '][name]" value="' . Html::encode($name) . '"></td>'
            . '<td style="width:160px"><input type="text" inputmode="decimal" class="form-control form-control-sm text-end att-amount" name="' . $kind . '[' . $idx . '][amount]" value="' . $num($amount) . '" placeholder="0.00"></td>'
            . '<td style="width:220px"><input type="text" class="form-control form-control-sm" name="' . $kind . '[' . $idx . '][note]" value="' . Html::encode($note) . '"></td>'
            . '</tr>';
    };
    foreach ($items as $it) {
        $html .= $render($i++, $it->name, $it->amount, (string) $it->note);
    }
    for ($k = 0; $k < 3; $k++) {
        $html .= $render($i++, '', 0, '');
    }
    return $html;
};
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><i class="bi bi-wallet2"></i><?= Html::encode($this->title) ?></h4>
</div>
<div class="small text-body-secondary">ปีงบประมาณ <?= $year ?> — ใช้คำนวณบล็อกสภาพคล่องในหน้าแผนประจำปี</div>
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
    <a href="<?= Url::to(['/plan/annual', 'year' => $year]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-table me-1"></i>กลับหน้าแผนประจำปี</a>
    <form method="get" class="d-flex gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบประมาณ (พ.ศ.)</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:120px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
</div></div>

<?= Html::beginForm(['liquidity-save'], 'post') ?>
<?= Html::hiddenInput('year', $year) ?>

<div class="row g-3">
    <!-- เงินคงเหลือยกมา + แยกประเภท -->
    <div class="col-lg-5">
        <div class="card border h-100">
            <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-piggy-bank me-1"></i>เงินคงเหลือ</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small mb-1">เงินคงเหลือสะสมยกมา <span class="text-body-secondary">(กรอกเฉพาะปีเริ่มต้น — ปีถัดไประบบยกยอดให้)</span></label>
                    <input type="text" inputmode="decimal" class="form-control form-control-sm text-end" name="ledger[carry_forward]" value="<?= $num($ledger->carry_forward) ?>" placeholder="0.00">
                </div>
                <div class="fw-semibold small text-body-secondary mb-2">เงินคงเหลือทั้งสิ้น แยกประเภท (2)</div>
                <?php
                $posFields = [
                    'cash' => 'เงินสด',
                    'deposit_treasury' => 'เงินฝากคลัง',
                    'deposit_fixed' => 'เงินฝากธนาคาร — ประจำ',
                    'deposit_saving' => 'เงินฝากธนาคาร — ออมทรัพย์',
                    'deposit_current' => 'เงินฝากธนาคาร — กระแสรายวัน',
                ];
                foreach ($posFields as $f => $label): ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <label class="form-label small mb-0 flex-grow-1"><?= $label ?></label>
                        <input type="text" inputmode="decimal" class="form-control form-control-sm text-end pos-amount" name="ledger[<?= $f ?>]" value="<?= $num($ledger->$f) ?>" placeholder="0.00" style="width:150px">
                    </div>
                <?php endforeach; ?>
                <div class="d-flex align-items-center gap-2 border-top pt-2">
                    <span class="fw-semibold small flex-grow-1 text-end">รวมเงินคงเหลือทั้งสิ้น (2)</span>
                    <span class="fw-bold text-end" id="posTotal" style="width:150px">0.00</span>
                </div>
                <div class="mt-3">
                    <label class="form-label small mb-1">หมายเหตุ</label>
                    <textarea class="form-control form-control-sm" name="ledger[note]" rows="2"><?= Html::encode((string) $ledger->note) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- แนบ 1 + แนบ 2 -->
    <div class="col-lg-7">
        <div class="card border mb-3">
            <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-paperclip me-1"></i>แนบ 1 — เงินกองทุนรอการจัดสรร (4)</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light"><tr><th style="width:34px">#</th><th>รายการ</th><th style="width:160px">จำนวนเงิน</th><th style="width:220px">หมายเหตุ</th></tr></thead>
                    <tbody class="att-body" data-kind="reserve"><?= $attachRows(PlanAnnualAttachment::KIND_RESERVE, $reserve) ?></tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary att-add" data-kind="reserve"><i class="bi bi-plus-lg me-1"></i>เพิ่มแถว</button>
                <span class="small">รวม (4): <span class="fw-bold att-total" data-kind="reserve">0.00</span></span>
            </div>
        </div>

        <div class="card border">
            <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-paperclip me-1"></i>แนบ 2 — ภาระผูกพันของหน่วยงาน (5)</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light"><tr><th style="width:34px">#</th><th>รายการ</th><th style="width:160px">จำนวนเงิน</th><th style="width:220px">หมายเหตุ</th></tr></thead>
                    <tbody class="att-body" data-kind="commitment"><?= $attachRows(PlanAnnualAttachment::KIND_COMMITMENT, $commitment) ?></tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary att-add" data-kind="commitment"><i class="bi bi-plus-lg me-1"></i>เพิ่มแถว</button>
                <span class="small">รวม (5): <span class="fw-bold att-total" data-kind="commitment">0.00</span></span>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mt-3">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกข้อมูลสภาพคล่อง', ['class' => 'btn btn-primary']) ?>
</div>
<?= Html::endForm() ?>

<?php
$js = <<<JS
(function(){
  const parse = v => parseFloat(String(v).replace(/[,\\s]/g,'')) || 0;
  function recalcPos(){
    let t = 0;
    document.querySelectorAll('.pos-amount').forEach(el => t += parse(el.value));
    const el = document.getElementById('posTotal'); if(el) el.textContent = t.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
  }
  function recalcAtt(kind){
    let t = 0;
    document.querySelectorAll('.att-body[data-kind="'+kind+'"] .att-amount').forEach(el => t += parse(el.value));
    document.querySelectorAll('.att-total[data-kind="'+kind+'"]').forEach(el => el.textContent = t.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}));
  }
  document.addEventListener('input', function(e){
    if(e.target.classList.contains('pos-amount')) recalcPos();
    if(e.target.classList.contains('att-amount')) recalcAtt(e.target.closest('.att-body').dataset.kind);
  });
  document.querySelectorAll('.att-add').forEach(btn => btn.addEventListener('click', function(){
    const kind = this.dataset.kind;
    const body = document.querySelector('.att-body[data-kind="'+kind+'"]');
    const rows = body.querySelectorAll('tr');
    const idx = rows.length;
    const tr = rows[rows.length-1].cloneNode(true);
    tr.querySelectorAll('input').forEach(inp => {
      inp.value = '';
      inp.name = inp.name.replace(/\\[(\\d+)\\]/, '['+idx+']');
    });
    tr.querySelector('td').textContent = idx+1;
    body.appendChild(tr);
  }));
  recalcPos(); recalcAtt('reserve'); recalcAtt('commitment');
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
