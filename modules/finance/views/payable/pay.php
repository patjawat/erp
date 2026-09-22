<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $rows */

$this->title = 'จ่ายชำระเจ้าหนี้';
$this->params['breadcrumbs'][] = ['label' => 'บัญชี', 'url' => ['/accounting/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เจ้าหนี้ค้างชำระ', 'url' => ['/accounting/payable/aging']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo 'ดึงบิลค้างมาจ่าย — เลือกจ่ายบางบิล/บางส่วนได้ (ปรับยอด หรือใส่ 0 บิลที่ยังไม่จ่าย)';
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/accounting/menu', ['active' => 'aging']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
$today = date('d/m/') . ((int) date('Y') + 543);
$thDate = function ($d) {
    if (!$d) {
        return '–';
    }
    $t = date_create($d);
    return $t ? $t->format('d/m/') . ((int) $t->format('Y') + 543) : $d;
};
?>

<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<?= Html::beginForm(['pay'], 'post', ['id' => 'pay-run-form']) ?>
<div class="card border mb-3"><div class="card-body">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">วันที่จ่าย</label>
            <input type="text" class="form-control form-control-sm" name="settle_date" value="<?= $today ?>" placeholder="วว/ดด/ปปปป">
        </div>
        <div class="col-md-6">
            <label class="form-label small mb-1">หมายเหตุ (เลขเช็ค/รอบจ่าย ฯลฯ)</label>
            <input type="text" class="form-control form-control-sm" name="note" placeholder="ไม่บังคับ">
        </div>
        <div class="col-md-3 text-md-end">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-all"><i class="bi bi-eraser me-1"></i>ล้างยอดทั้งหมด</button>
        </div>
    </div>
</div></div>

<section class="card border shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0">บิลค้างชำระ (อนุมัติแล้ว)</h5>
        <span class="text-body-secondary small"><?= count($rows) ?> รายการ</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>เลขทะเบียน</th>
                    <th>เจ้าหนี้</th>
                    <th>ใบแจ้งหนี้</th>
                    <th class="text-center">ครบกำหนด</th>
                    <th class="text-end">คงค้าง</th>
                    <th class="text-end" style="width:180px">จ่ายครั้งนี้</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="text-center text-body-secondary py-4"><i class="bi bi-check2-circle me-1"></i>ไม่มีบิลค้างชำระ</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): $out = (float) $r['outstanding']; ?>
                    <tr>
                        <td><?= Html::a(Html::encode($r['payable_no'] ?: '#' . $r['id']), ['view', 'id' => $r['id']], ['class' => 'fw-semibold', 'target' => '_blank']) ?></td>
                        <td><?= Html::encode($r['vendor_name_snapshot']) ?></td>
                        <td class="text-body-secondary"><?= Html::encode($r['invoice_no']) ?></td>
                        <td class="text-center"><?= $thDate($r['due_date']) ?></td>
                        <td class="text-end fw-semibold"><?= $fmt($out) ?></td>
                        <td class="text-end p-1">
                            <input type="text" inputmode="decimal"
                                class="form-control form-control-sm text-end pay-amount"
                                name="pay[<?= $r['id'] ?>]"
                                value="<?= $fmt($out) ?>"
                                data-max="<?= $out ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if ($rows): ?>
            <tfoot class="table-primary fw-bold">
                <tr>
                    <td colspan="5" class="text-end">รวมจ่ายครั้งนี้</td>
                    <td class="text-end"><span id="pay-total">0.00</span></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
    <?php if ($rows): ?>
    <div class="card-footer d-flex justify-content-end">
        <?= Html::submitButton('<i class="bi bi-cash-stack me-1"></i> บันทึกจ่ายชำระ', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php endif; ?>
</section>
<?= Html::endForm() ?>

<?php
$js = <<<JS
(function(){
  const parse = v => parseFloat(String(v).replace(/[,\\s]/g,'')) || 0;
  const fmt = n => n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
  function recalc(){
    let t = 0;
    document.querySelectorAll('.pay-amount').forEach(el => t += parse(el.value));
    const el = document.getElementById('pay-total'); if(el) el.textContent = fmt(t);
  }
  document.addEventListener('input', e => { if(e.target.classList.contains('pay-amount')) recalc(); });
  // กันจ่ายเกินคงค้าง (client-side hint)
  document.addEventListener('blur', e => {
    if(e.target.classList.contains('pay-amount')){
      const max = parse(e.target.dataset.max);
      if(parse(e.target.value) > max){ e.target.value = fmt(max); recalc(); }
    }
  }, true);
  const clr = document.getElementById('btn-clear-all');
  if(clr) clr.addEventListener('click', () => { document.querySelectorAll('.pay-amount').forEach(el => el.value = ''); recalc(); });
  recalc();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
