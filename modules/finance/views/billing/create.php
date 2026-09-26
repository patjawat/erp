<?php

use app\modules\finance\components\ThaiDate;
use app\modules\finance\models\FinancePayable;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $vendor */
/** @var array $vendors [บริษัท => จำนวนบิลรอวาง] */
/** @var FinancePayable[] $bills */
/** @var array $form */
/** @var int[] $selected */

$this->title = 'เพิ่มการรับวางบิล';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนรับวางบิล', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/views/_ap_menu', ['active' => 'billing']);
$this->endBlock();

$fmt = fn($v) => number_format((float) $v, 2);
?>

<?php if ($flash = Yii::$app->session->getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= Html::encode($flash) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?= Html::beginForm(['create'], 'post', ['id' => 'billing-form']) ?>
<div class="card border shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">วันที่รับวางบิล <span class="text-danger">*</span></label>
                <?= DatepickerThai::widget([
                    'name' => 'billing_date',
                    'value' => $form['billing_date'],
                    'options' => ['class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.', 'required' => true],
                ]) ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="vendor-select">บริษัท <span class="text-danger">*</span></label>
                <select class="form-select" name="vendor_name" id="vendor-select" required>
                    <option value="">— เลือกบริษัท —</option>
                    <?php foreach ($vendors as $name => $n): ?>
                        <option value="<?= Html::encode($name) ?>" <?= $name === $vendor ? 'selected' : '' ?>><?= Html::encode($name) ?> (<?= $n ?> บิล)</option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$vendors): ?><div class="form-text">ไม่มีบิลที่รับเอกสารแล้วและรอวางบิล</div><?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">เลขที่ใบวางบิลของบริษัท</label>
                <input type="text" name="vendor_ref" class="form-control" maxlength="60" value="<?= Html::encode($form['vendor_ref']) ?>" placeholder="ถ้ามี">
            </div>
            <div class="col-md-4">
                <label class="form-label">ผู้วางบิล (ตัวแทนบริษัท)</label>
                <input type="text" name="deliverer_name" class="form-control" maxlength="150" value="<?= Html::encode($form['deliverer_name']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">ผู้รับวางบิล</label>
                <input type="text" name="receiver_name" class="form-control" maxlength="150" value="<?= Html::encode($form['receiver_name']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">หมายเหตุ</label>
                <input type="text" name="note" class="form-control" maxlength="255" value="<?= Html::encode($form['note']) ?>">
            </div>
        </div>
    </div>
</div>

<section class="card border shadow-sm">
    <div class="card-header bg-body d-flex justify-content-between align-items-center">
        <h5 class="mb-0">เลือกบิลที่นำมาวาง</h5>
        <span class="small text-body-secondary">รวมที่เลือก <strong id="sel-total">0.00</strong> บาท</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th style="width:40px" class="text-center"><input type="checkbox" class="form-check-input" id="check-all"></th>
                    <th>เลขทะเบียน</th><th>เลขที่ใบแจ้งหนี้</th><th>เอกสารต้นทาง</th><th class="text-center">วันที่ใบแจ้งหนี้</th>
                    <th class="text-center">เครดิต</th><th class="text-end">ยอดเงิน</th></tr>
            </thead>
            <tbody>
                <?php if ($vendor === ''): ?>
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">เลือกบริษัทก่อน แล้วรายการบิลจะแสดงที่นี่</td></tr>
                <?php elseif (!$bills): ?>
                    <tr><td colspan="7" class="text-center text-body-secondary py-4">บริษัทนี้ไม่มีบิลรอวางบิล</td></tr>
                <?php endif; ?>
                <?php foreach ($bills as $p): ?>
                    <tr>
                        <td class="text-center"><input type="checkbox" class="form-check-input bill-check" name="ids[]" value="<?= $p->id ?>"
                            data-amount="<?= (float) $p->net_amount ?>" <?= in_array($p->id, $selected, true) ? 'checked' : '' ?>></td>
                        <td><?= Html::encode($p->payable_no) ?></td>
                        <td><?= Html::encode($p->invoice_no ?: '-') ?></td>
                        <td class="small text-body-secondary"><?= Html::encode($p->source_document_no ?: '-') ?></td>
                        <td class="text-center"><?= ThaiDate::date($p->invoice_date) ?></td>
                        <td class="text-center"><?= (int) $p->credit_days ?> วัน</td>
                        <td class="text-end fw-semibold"><?= $fmt($p->net_amount) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">ยกเลิก</a>
        <button type="submit" class="btn btn-primary" <?= $bills ? '' : 'disabled' ?>><i class="bi bi-save me-1"></i>บันทึกรับวางบิล</button>
    </div>
</section>
<?= Html::endForm() ?>

<?php
$createUrl = Url::to(['create']);
$this->registerJs(<<<JS
(function(){
  const fmt = n => n.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
  function recalc(){
    let t = 0;
    document.querySelectorAll('.bill-check:checked').forEach(c => t += parseFloat(c.dataset.amount) || 0);
    document.getElementById('sel-total').textContent = fmt(t);
  }
  document.querySelectorAll('.bill-check').forEach(c => c.addEventListener('change', recalc));
  const all = document.getElementById('check-all');
  if (all) all.addEventListener('change', function(){ document.querySelectorAll('.bill-check').forEach(c => c.checked = all.checked); recalc(); });
  // เปลี่ยนบริษัท → โหลดบิลของบริษัทนั้น
  document.getElementById('vendor-select').addEventListener('change', function(){
    window.location = '{$createUrl}' + (this.value ? ('?vendor=' + encodeURIComponent(this.value)) : '');
  });
  recalc();
})();
JS, \yii\web\View::POS_END);
?>
