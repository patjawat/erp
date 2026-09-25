<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $year */
/** @var app\modules\plan\models\PlanAnnualLedger $ledger */
/** @var float $reserveSum */
/** @var float $commitmentSum */

$this->title = 'ข้อมูลสภาพคล่อง (เงินคงเหลือยกมา)';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'แผนประจำปี', 'url' => ['/plan/annual', 'year' => $year]];
$this->params['breadcrumbs'][] = $this->title;

$num = fn($v) => $v > 0 ? number_format((float) $v, 2) : '';
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

    <!-- แนบ 1 + แนบ 2 ย้ายไปหน้าภาระผูกพัน & รอจัดสรร (บรรทัดตามแบบฟอร์มเขต 3 ปี) -->
    <div class="col-lg-7">
        <div class="card border h-100">
            <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-paperclip me-1"></i>แนบ 1 / แนบ 2 ปี <?= $year ?></div>
            <div class="card-body">
                <div class="d-flex justify-content-between border-bottom py-2"><span>เงินกองทุนรอการจัดสรร (4)</span><span class="fw-semibold"><?= number_format((float) $reserveSum, 2) ?></span></div>
                <div class="d-flex justify-content-between border-bottom py-2"><span>ภาระผูกพันของหน่วยงาน (5)</span><span class="fw-semibold"><?= number_format((float) $commitmentSum, 2) ?></span></div>
                <p class="small text-body-secondary mt-3 mb-2">กรอกแยกรายบรรทัดตามแบบฟอร์มเขต 3 ปีคู่กัน ที่หน้า "ภาระผูกพัน &amp; รอจัดสรร"</p>
                <a href="<?= Url::to(['/plan/annual/commitment', 'year' => $year]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>ไปหน้าภาระผูกพัน &amp; รอจัดสรร</a>
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
  document.addEventListener('input', function(e){
    if(e.target.classList.contains('pos-amount')) recalcPos();
  });
  recalcPos();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
