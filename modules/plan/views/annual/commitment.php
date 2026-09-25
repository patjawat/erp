<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\plan\models\PlanAnnualAttachment;

/** @var yii\web\View $this */
/** @var int $year */
/** @var int[] $years */
/** @var array $amounts [kind][code][fy] => amount */
/** @var array $legacy [kind] => PlanAnnualAttachment[] */

$this->title = 'ภาระผูกพัน & รอจัดสรร';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'แผนประจำปี', 'url' => ['/plan/annual', 'year' => $year]];
$this->params['breadcrumbs'][] = $this->title;

$num = fn ($v) => $v != 0 ? number_format((float) $v, 2) : '';
$sheets = [
    PlanAnnualAttachment::KIND_RESERVE => [
        'title' => 'รายละเอียดแนบ 1 — เงินกองทุนรอการจัดสรร',
        'total' => 'รวมเงินกองทุนรอการจัดสรรทั้งสิ้น (4)',
        'icon' => 'bi-hourglass-split',
    ],
    PlanAnnualAttachment::KIND_COMMITMENT => [
        'title' => 'รายละเอียดแนบ 2 — ภาระผูกพันของหน่วยงาน',
        'total' => 'รวมภาระผูกพันทั้งสิ้น (5)',
        'icon' => 'bi-link-45deg',
    ],
];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><i class="bi bi-paperclip"></i><?= Html::encode($this->title) ?></h4>
</div>
<div class="small text-body-secondary">แนบ 1 / แนบ 2 ตามแบบฟอร์มระบบแผนเงินบำรุง สป.สธ. (เมนู 1.5) สิ้นสุด ณ วันที่ 30 กันยายน พ.ศ. <?= $year ?>–<?= $year + 2 ?> — ยอดรวมใช้หัก (4)(5) ในบล็อกสภาพคล่องหน้าแผนประจำปี</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'commitment']) ?>
<?php $this->endBlock(); ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border mb-3"><div class="card-body d-flex flex-wrap justify-content-between align-items-end gap-2">
    <div class="d-flex gap-2">
        <a href="<?= Url::to(['/plan/annual', 'year' => $year]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-table me-1"></i>กลับหน้าแผนประจำปี</a>
        <a href="<?= Url::to(['/plan/annual/liquidity', 'year' => $year]) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-wallet2 me-1"></i>เงินคงเหลือยกมา</a>
    </div>
    <form method="get" class="d-flex gap-2 align-items-end">
        <div><label class="form-label mb-0 small">ปีงบเริ่มแผน (พ.ศ.)</label>
            <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:120px"></div>
        <button type="submit" class="btn btn-sm btn-primary">ดู</button>
    </form>
</div></div>

<?= Html::beginForm(['commitment-save'], 'post') ?>
<?= Html::hiddenInput('year', $year) ?>

<?php if (!empty($legacy)):
    $lineOpts = [];
    foreach ($sheets as $kind => $_) {
        foreach (PlanAnnualAttachment::lines($kind) as $code => [$t]) {
            $lineOpts[$kind][$code] = $t;
        }
    } ?>
    <div class="card border border-warning mb-3">
        <div class="card-header bg-warning-subtle fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>รายการเดิมที่ยังไม่อยู่ในบรรทัดแบบฟอร์มเขต</div>
        <div class="card-body pb-1">
            <p class="small mb-2">รายการเหล่านี้กรอกไว้ก่อนปรับแบบฟอร์ม ยังนับรวมยอด (4)/(5) อยู่ — เลือกบรรทัดที่จะย้ายยอดเข้าไป (ยอดจะบวกเพิ่มในบรรทัดนั้น) หรือเลือกลบ แล้วกดบันทึก</p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light"><tr><th style="width:80px">แนบ</th><th style="width:70px">ปี</th><th>รายการเดิม</th><th style="width:140px" class="text-end">จำนวนเงิน</th><th style="width:340px">จัดการ</th></tr></thead>
                    <tbody>
                    <?php foreach ($legacy as $kind => $rows): foreach ($rows as $r): ?>
                        <tr>
                            <td><?= $kind === PlanAnnualAttachment::KIND_RESERVE ? 'แนบ 1' : 'แนบ 2' ?></td>
                            <td><?= (int) $r->fiscal_year ?></td>
                            <td><?= Html::encode($r->name) ?><?php if ($r->note): ?><div class="small text-body-secondary"><?= Html::encode($r->note) ?></div><?php endif; ?></td>
                            <td class="text-end"><?= number_format((float) $r->amount, 2) ?></td>
                            <td><?= Html::dropDownList("legacy[{$r->id}]", '', ['' => '— คงไว้ก่อน —'] + ['ย้ายเข้าบรรทัด' => $lineOpts[$kind] ?? []] + ['delete' => '✕ ลบรายการนี้'], ['class' => 'form-select form-select-sm']) ?></td>
                        </tr>
                    <?php endforeach; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php foreach ($sheets as $kind => $sheet):
    $lines = PlanAnnualAttachment::lines($kind);
    $section = null; ?>
    <div class="card border mb-3">
        <div class="card-header bg-body-tertiary fw-semibold"><i class="bi <?= $sheet['icon'] ?> me-1"></i><?= Html::encode($sheet['title']) ?></div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light text-center">
                    <tr><th>รายการ</th><?php foreach ($years as $y): ?><th style="width:170px">จำนวนเงิน ปี <?= $y ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                <?php foreach ($lines as $code => [$title, $sec]):
                    if ($sec !== null && $sec !== $section):
                        $section = $sec; ?>
                        <tr class="table-light"><td colspan="<?= count($years) + 1 ?>" class="fw-semibold small"><?= Html::encode($sec) ?></td></tr>
                    <?php elseif ($sec === null):
                        $section = null;
                    endif; ?>
                    <tr>
                        <td class="<?= $sec !== null ? 'ps-4' : 'fw-semibold' ?>"><?= Html::encode($title) ?></td>
                        <?php foreach ($years as $y): ?>
                            <td><input type="text" inputmode="decimal" class="form-control form-control-sm text-end line-amount"
                                name="line[<?= $kind ?>][<?= $code ?>][<?= $y ?>]" data-kind="<?= $kind ?>" data-year="<?= $y ?>"
                                value="<?= $num($amounts[$kind][$code][$y] ?? 0) ?>" placeholder="0.00"></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-primary fw-bold">
                        <td class="text-end"><?= Html::encode($sheet['total']) ?></td>
                        <?php foreach ($years as $y): ?><td class="text-end line-total" data-kind="<?= $kind ?>" data-year="<?= $y ?>">0.00</td><?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<div class="d-flex justify-content-end mb-3">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกภาระผูกพัน & รอจัดสรร', ['class' => 'btn btn-primary']) ?>
</div>
<?= Html::endForm() ?>

<?php
$js = <<<JS
(function(){
  const parse = v => parseFloat(String(v).replace(/[,\\s]/g,'')) || 0;
  function recalc(){
    document.querySelectorAll('.line-total').forEach(function(cell){
      let t = 0;
      document.querySelectorAll('.line-amount[data-kind="'+cell.dataset.kind+'"][data-year="'+cell.dataset.year+'"]').forEach(el => t += parse(el.value));
      cell.textContent = t.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    });
  }
  document.addEventListener('input', e => { if (e.target.classList.contains('line-amount')) recalc(); });
  recalc();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
