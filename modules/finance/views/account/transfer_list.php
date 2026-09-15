<?php

use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $year */
/** @var app\modules\finance\models\FinanceCashAccount[] $accounts */
/** @var array $accountList id => label */

$this->title = 'โอนเงินข้ามบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-arrow-left-right" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>บันทึกการโอนเงินระหว่างบัญชี + ทะเบียนคุมรายเดือน<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$saveUrl = Url::to(['transfer-save']);
$registerUrl = Url::to(['register']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$accListJson = Json::htmlEncode($accountList);
$months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
?>

<?= $this->render('@app/modules/finance/views/cash/_menu', ['active' => 'account']) ?>
<?= $this->render('_account_menu', ['active' => 'transfer']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border"><div class="card-body table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark"><tr>
            <th style="width:40px">#</th><th>รายการ</th><th class="text-end">จำนวนเงินคงเหลือ (บาท)</th><th class="text-center" style="width:200px">รายละเอียดเพิ่มเติม</th>
        </tr></thead>
        <tbody>
            <?php foreach ($accounts as $i => $a): $bal = $a->balanceFor($year); $label = trim(($a->code ? $a->code . ' ' : '') . '(' . $a->name . ')'); ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= Html::encode($a->code ?: '') ?><br><small class="text-body-secondary"><?= Html::encode($a->name) ?></small></td>
                    <td class="text-end fw-semibold"><?= number_format($bal, 2) ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-success" data-transfer data-id="<?= $a->id ?>" data-label="<?= Html::encode($label) ?>" data-balance="<?= $bal ?>"><i class="bi bi-arrow-right-circle me-1"></i>โอนเงิน</button>
                        <button type="button" class="btn btn-sm btn-outline-info" data-register data-id="<?= $a->id ?>" data-label="<?= Html::encode($label) ?>"><i class="bi bi-journal-text me-1"></i>ทะเบียนคุม</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$accounts): ?><tr><td colspan="4" class="text-center text-body-secondary py-5">ยังไม่มีบัญชี — เพิ่มที่เมนู "บัญชีธนาคาร"</td></tr><?php endif; ?>
        </tbody>
    </table>
</div></div>

<!-- Modal โอนเงิน -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-success text-white"><h5 class="modal-title">โอนเงินออกจากบัญชี</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="alert alert-danger d-none" id="tf-alert"></div>
            <div class="alert alert-warning">โอนเงิน จาก : <span class="fw-semibold" id="tf-from-label"></span></div>
            <input type="hidden" id="tf-from"><input type="hidden" id="tf-bal">
            <div class="d-flex justify-content-between mb-3"><span>เงินในบัญชีคงเหลือ</span><span class="fw-semibold" id="tf-bal-show">0.00</span></div>
            <div class="mb-2"><label class="form-label">โอนไปยัง บัญชี <span class="text-danger">*</span></label>
                <select class="form-select" id="tf-to"><option value="">== กรุณาเลือก ==</option></select>
                <div class="text-danger small" data-err="to_account_id"></div></div>
            <div class="mb-2"><label class="form-label">จำนวนเงิน <span class="text-danger">*</span></label>
                <input type="text" class="form-control text-end" id="tf-amount" inputmode="decimal">
                <div class="text-danger small" data-err="amount"></div></div>
            <div class="mb-2"><label class="form-label">เลขที่เอกสารอ้างอิง</label><input type="text" class="form-control" id="tf-ref" maxlength="64"></div>
            <div class="mb-2"><label class="form-label">วันที่โอน <span class="text-danger">*</span></label>
                <?= DatepickerThai::widget(['name' => 'transfer_date', 'value' => '', 'options' => ['id' => 'tf-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
                <div class="text-danger small" data-err="transfer_date"></div></div>
            <div class="mb-2"><label class="form-label">หมายเหตุ</label><textarea class="form-control" id="tf-note" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="button" class="btn btn-primary" id="tf-submit"><i class="bi bi-save me-1"></i>บันทึก</button></div>
    </div></div>
</div>

<!-- Modal ทะเบียนคุม -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">ทะเบียนคุม บัญชีเงินฝาก</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="fw-semibold" id="rg-label"></p><input type="hidden" id="rg-id">
            <div class="row g-2">
                <div class="col-6"><label class="form-label">เดือน</label>
                    <select class="form-select" id="rg-month">
                        <?php for ($m = 1; $m <= 12; $m++): ?><option value="<?= $m ?>" <?= $m == (int) date('n') ? 'selected' : '' ?>><?= $months[$m] ?></option><?php endfor; ?>
                    </select></div>
                <div class="col-6"><label class="form-label">ปี (พ.ศ.)</label>
                    <input type="number" class="form-control" id="rg-year" value="<?= (int) date('n') >= 10 ? (int) date('Y') + 544 : (int) date('Y') + 543 ?>"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="button" class="btn btn-success" id="rg-export"><i class="bi bi-file-earmark-excel me-1"></i>ส่งออก Excel</button></div>
    </div></div>
</div>

<?php
$this->registerJs(<<<JS
(function () {
    const ACCS = {$accListJson};
    const SAVE = '{$saveUrl}';
    const REGISTER = '{$registerUrl}';
    const CSRF = { p: '{$csrfParam}', t: '{$csrfToken}' };
    const money = v => (parseFloat(String(v).replace(/[, ]/g, '')) || 0);
    const fmt = v => v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const \$ = id => document.getElementById(id);

    const tfModal = new bootstrap.Modal(\$('transferModal'));
    const rgModal = new bootstrap.Modal(\$('registerModal'));

    document.querySelectorAll('[data-transfer]').forEach(b => b.addEventListener('click', function () {
        const d = this.dataset;
        \$('tf-alert').classList.add('d-none');
        document.querySelectorAll('#transferModal [data-err]').forEach(e => e.textContent = '');
        \$('tf-from').value = d.id; \$('tf-from-label').textContent = d.label;
        \$('tf-bal').value = d.balance; \$('tf-bal-show').textContent = fmt(money(d.balance));
        const to = \$('tf-to'); to.innerHTML = '<option value="">== กรุณาเลือก ==</option>';
        Object.entries(ACCS).forEach(([id, label]) => { if (id !== d.id) { const o = document.createElement('option'); o.value = id; o.textContent = label; to.appendChild(o); } });
        \$('tf-amount').value = ''; \$('tf-ref').value = ''; \$('tf-note').value = '';
        const dt = \$('tf-date'); dt.value = ''; dt.dispatchEvent(new Event('change', { bubbles: true }));
        tfModal.show();
    }));

    \$('tf-submit').addEventListener('click', function () {
        document.querySelectorAll('#transferModal [data-err]').forEach(e => e.textContent = '');
        const fd = new FormData();
        fd.append(CSRF.p, CSRF.t);
        fd.append('from_account_id', \$('tf-from').value);
        fd.append('to_account_id', \$('tf-to').value);
        fd.append('amount', money(\$('tf-amount').value));
        fd.append('doc_ref', \$('tf-ref').value);
        fd.append('transfer_date', \$('tf-date').value);
        fd.append('note', \$('tf-note').value);
        this.disabled = true;
        fetch(SAVE, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(res => {
            this.disabled = false;
            if (res.ok) { location.reload(); return; }
            if (res.message) { \$('tf-alert').textContent = res.message; \$('tf-alert').classList.remove('d-none'); }
            if (res.errors) Object.entries(res.errors).forEach(([f, m]) => { const el = document.querySelector('#transferModal [data-err="' + f + '"]'); if (el) el.textContent = Array.isArray(m) ? m[0] : m; });
        }).catch(() => { this.disabled = false; });
    });

    document.querySelectorAll('[data-register]').forEach(b => b.addEventListener('click', function () {
        \$('rg-id').value = this.dataset.id; \$('rg-label').textContent = this.dataset.label; rgModal.show();
    }));
    \$('rg-export').addEventListener('click', function () {
        const url = REGISTER + '?id=' + \$('rg-id').value + '&y=' + \$('rg-year').value + '&m=' + \$('rg-month').value;
        window.location = url;
    });
})();
JS);
?>
