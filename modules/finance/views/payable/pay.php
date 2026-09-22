<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $mode */
/** @var array $vendors */
/** @var string $vendor */
/** @var array $rows */
/** @var string $today */
/** @var array $accounts */
/** @var array $accountMeta */
/** @var array $templates */

$this->title = 'จ่ายชำระเจ้าหนี้';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'เจ้าหนี้ค้างชำระ', 'url' => ['/finance/payable/aging']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'aging']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
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

<?php if (($mode ?? '') === 'vendors'): ?>
    <!-- ===== เลือกเจ้าหนี้ ===== -->
    <div class="alert alert-info d-flex gap-2"><i class="bi bi-info-circle"></i><span>เลือกเจ้าหนี้ที่ต้องการจ่าย แล้วติ๊กบิลที่จะจ่ายในรอบนี้ (จ่ายบางบิล/บางส่วนได้)</span></div>
    <section class="card border shadow-sm">
        <div class="card-header bg-body"><h5 class="mb-0">เจ้าหนี้ที่มีบิลค้างชำระ</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>เจ้าหนี้</th><th class="text-center">จำนวนบิล</th><th class="text-center">ครบกำหนดเร็วสุด</th><th class="text-end">ยอดค้างรวม</th><th></th></tr></thead>
                <tbody>
                    <?php if (!$vendors): ?>
                        <tr><td colspan="5" class="text-center text-body-secondary py-4">ไม่มีเจ้าหนี้ค้างชำระ</td></tr>
                    <?php endif; ?>
                    <?php foreach ($vendors as $v): ?>
                        <tr>
                            <td class="fw-semibold"><?= Html::encode($v['vendor']) ?></td>
                            <td class="text-center"><?= $v['bills'] ?></td>
                            <td class="text-center"><?= $thDate($v['earliest_due']) ?></td>
                            <td class="text-end fw-semibold"><?= $fmt($v['outstanding']) ?></td>
                            <td class="text-end">
                                <a href="<?= Url::to(['pay', 'vendor' => $v['vendor']]) ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-cash-stack me-1"></i>จ่าย
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php else: ?>
    <!-- ===== เลือกบิล + ฟอร์มจ่าย ===== -->
    <div class="mb-3"><a href="<?= Url::to(['pay']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>เลือกเจ้าหนี้อื่น</a></div>

    <?= Html::beginForm(['pay'], 'post', ['id' => 'pay-form']) ?>
    <?= Html::hiddenInput('vendor', $vendor) ?>

    <?= Html::hiddenInput('pay_method', 'cheque') ?>
    <div class="card border mb-3">
        <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-building me-1"></i><?= Html::encode($vendor) ?></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label small mb-1">วันที่จ่าย</label>
                    <input type="text" class="form-control form-control-sm" name="pay_date" value="<?= $today ?>" placeholder="วว/ดด/ปปปป"></div>
                <div class="col-md-5"><label class="form-label small mb-1">บัญชีจ่าย</label>
                    <select class="form-select form-select-sm" name="cash_account_id" id="cash-account">
                        <option value="">— เลือกบัญชีจ่าย —</option>
                        <?php foreach (($accounts ?? []) as $aid => $alabel): ?>
                            <option value="<?= $aid ?>"
                                data-bank="<?= Html::encode($accountMeta[$aid]['bank'] ?? '') ?>"
                                data-branch="<?= Html::encode($accountMeta[$aid]['branch'] ?? '') ?>"><?= Html::encode($alabel) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-md-2"><label class="form-label small mb-1">เลขที่เช็ค</label>
                    <input type="text" class="form-control form-control-sm" name="cheque_no" placeholder="เลขที่เช็ค"></div>
                <div class="col-md-2"><label class="form-label small mb-1">เล่มเช็ค</label>
                    <input type="text" class="form-control form-control-sm" name="cheque_book_no" placeholder="เล่มที่"></div>

                <div class="col-md-3"><label class="form-label small mb-1">ธนาคาร</label>
                    <input type="text" class="form-control form-control-sm" name="bank_name" id="bank-name" value="กรุงไทย"></div>
                <div class="col-md-3"><label class="form-label small mb-1">สาขา</label>
                    <input type="text" class="form-control form-control-sm" name="bank_branch" id="bank-branch" value="ด่านซ้าย"></div>
                <div class="col-md-4"><label class="form-label small mb-1">แม่แบบเช็ค (สำหรับพิมพ์)</label>
                    <select class="form-select form-select-sm" name="template_id">
                        <option value="">— ไม่ระบุ (ใช้แม่แบบที่ใช้งาน) —</option>
                        <?php foreach (($templates ?? []) as $tid => $tlabel): ?>
                            <option value="<?= $tid ?>"><?= Html::encode($tlabel) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_ac_payee" id="ac-payee" value="1" checked>
                        <label class="form-check-label small" for="ac-payee">พิมพ์ A/C PAYEE ONLY</label>
                    </div>
                </div>

                <div class="col-md-4"><label class="form-label small mb-1">เลขที่หนังสือ</label>
                    <input type="text" class="form-control form-control-sm" name="doc_no" placeholder="ลย 0033.301.05/..."></div>
                <div class="col-md-8"><label class="form-label small mb-1">เรื่อง</label>
                    <input type="text" class="form-control form-control-sm" name="subject" placeholder="ชำระเงินค่า..."></div>
            </div>
        </div>
    </div>

    <section class="card border shadow-sm">
        <div class="card-header bg-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">บิลค้างชำระ</h5>
            <span class="text-body-secondary small"><?= count($rows) ?> บิล</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th style="width:40px" class="text-center">จ่าย</th><th>เลขที่ใบส่งของ</th><th class="text-center">ครบกำหนด</th><th class="text-end">คงค้าง</th><th class="text-end" style="width:180px">จ่ายครั้งนี้</th></tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="5" class="text-center text-body-secondary py-4">ไม่มีบิลค้าง</td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): $out = (float) $r['outstanding']; ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" class="form-check-input pay-check" checked data-id="<?= $r['id'] ?>"></td>
                            <td><?= Html::encode($r['invoice_no'] ?: ($r['payable_no'] ?: '#' . $r['id'])) ?></td>
                            <td class="text-center"><?= $thDate($r['due_date']) ?></td>
                            <td class="text-end fw-semibold"><?= $fmt($out) ?></td>
                            <td class="text-end p-1">
                                <input type="text" inputmode="decimal" class="form-control form-control-sm text-end pay-amount"
                                    name="pay[<?= $r['id'] ?>]" value="<?= $fmt($out) ?>" data-max="<?= $out ?>" data-id="<?= $r['id'] ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if ($rows): ?>
                <tfoot class="table-primary fw-bold"><tr><td colspan="4" class="text-end">ยอดจ่ายครั้งนี้ (เช็ค)</td><td class="text-end"><span id="pay-total">0.00</span></td></tr></tfoot>
                <?php endif; ?>
            </table>
        </div>
        <?php if ($rows): ?>
        <div class="card-footer d-flex justify-content-end">
            <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกจ่าย + ออกเช็ค/หนังสือนำส่ง', ['class' => 'btn btn-primary']) ?>
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
  document.addEventListener('blur', e => {
    if(e.target.classList.contains('pay-amount')){
      const max = parse(e.target.dataset.max);
      if(parse(e.target.value) > max){ e.target.value = fmt(max); recalc(); }
    }
  }, true);
  // ติ๊กออก = ใส่ยอด 0
  document.querySelectorAll('.pay-check').forEach(chk => chk.addEventListener('change', function(){
    const inp = document.querySelector('.pay-amount[data-id="'+this.dataset.id+'"]');
    if(inp){ inp.value = this.checked ? fmt(parse(inp.dataset.max)) : '0.00'; inp.disabled = !this.checked; recalc(); }
  }));
  recalc();
  // เลือกบัญชีจ่าย → เติมธนาคาร/สาขาอัตโนมัติ
  const acc = document.getElementById('cash-account');
  if(acc){ acc.addEventListener('change', function(){
    const o = this.options[this.selectedIndex];
    const bn = document.getElementById('bank-name'), bb = document.getElementById('bank-branch');
    if(o && o.dataset.bank && bn) bn.value = o.dataset.bank;
    if(o && o.dataset.branch && bb) bb.value = o.dataset.branch;
  }); }
})();
JS;
    $this->registerJs($js, \yii\web\View::POS_END);
    ?>
<?php endif; ?>
