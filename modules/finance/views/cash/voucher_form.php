<?php

use app\modules\finance\models\FinanceCashVoucher;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $tree ผังบัญชีฝั่งจ่าย (asArray) */
/** @var array $accounts id => label */
/** @var array $vendors รายชื่อผู้ขาย (จากทะเบียนพัสดุ) สำหรับ datalist */

// สร้าง option หมวด (leaf) แบบ optgroup ตามกลุ่ม สำหรับ dropdown บรรทัด
$childrenOf = [];
foreach ($tree as $n) {
    $childrenOf[(int) ($n['parent_id'] ?? 0)][] = $n;
}
$isLeaf = fn ($id) => empty($childrenOf[(int) $id]);
$leafOptions = '';
foreach ($childrenOf[0] ?? [] as $group) {
    $opts = '';
    $stack = $childrenOf[(int) $group['id']] ?? [];
    // เก็บ leaf ทุกตัวใต้กลุ่ม (รองรับได้ทั้ง 2-3 ระดับ)
    $collect = function ($nodes) use (&$collect, $childrenOf, $isLeaf) {
        $out = [];
        foreach ($nodes as $nd) {
            if ($isLeaf($nd['id'])) {
                $out[] = $nd;
            } else {
                $out = array_merge($out, $collect($childrenOf[(int) $nd['id']] ?? []));
            }
        }
        return $out;
    };
    foreach ($collect($stack) as $leaf) {
        $opts .= '<option value="' . (int) $leaf['id'] . '">' . Html::encode($leaf['name']) . '</option>';
    }
    if ($opts !== '') {
        $leafOptions .= '<optgroup label="' . Html::encode($group['name']) . '">' . $opts . '</optgroup>';
    }
}

$whtRates = [];
foreach (FinanceCashVoucher::WHT_TYPES as $k => $info) {
    $whtRates[$k] = $info['rate'];
}

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$saveUrl = Url::to(['voucher-save']);
$getUrl = Url::to(['voucher-get']);
$whtRatesJson = Json::htmlEncode($whtRates);
?>
<div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="voucherModalTitle"><i class="bi bi-receipt me-1"></i> บันทึกรายจ่าย (ใบสำคัญจ่าย)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="v-alert"></div>
                <input type="hidden" id="v-id">

                <div class="row g-3 mb-2">
                    <div class="col-md-3">
                        <label class="form-label" for="v-year">ปีงบประมาณ <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="v-year">
                        <div class="text-danger small" data-err="fiscal_year"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="v-date">วันที่จ่าย <span class="text-danger">*</span></label>
                        <?= DatepickerThai::widget(['name' => 'pay_date', 'value' => '', 'options' => ['id' => 'v-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
                        <div class="text-danger small" data-err="pay_date"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="v-docno">เลขใบสำคัญ</label>
                        <input type="text" class="form-control" id="v-docno" maxlength="64">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="v-cheque">เลขที่เช็ค</label>
                        <input type="text" class="form-control" id="v-cheque" maxlength="64">
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block">การจ่าย <span class="text-danger">*</span></label>
                        <?php foreach (FinanceCashVoucher::PAY_METHODS as $val => $label): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="v-method" id="vm-<?= $val ?>" value="<?= $val ?>">
                                <label class="form-check-label" for="vm-<?= $val ?>"><?= Html::encode($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-danger small" data-err="pay_method"></div>
                    </div>
                </div>

                <!-- ตารางบรรทัดรายจ่าย -->
                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="v-lines">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>รายการรายจ่าย <span class="text-danger">*</span></th>
                                <th style="width:140px">บค. (ถ้ามี)</th>
                                <th style="width:160px" class="text-end">จำนวนเงิน</th>
                                <th style="width:44px"></th>
                            </tr>
                        </thead>
                        <tbody id="v-lines-body"></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="v-add-line"><i class="bi bi-plus-lg me-1"></i> เพิ่มรายการ</button>
                <div class="text-danger small mb-2" data-err="lines"></div>

                <div class="row g-3">
                    <div class="col-md-6"></div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between py-1"><span>รวมสุทธิที่เสียภาษี</span><span class="fw-semibold" id="v-subtotal">0.00</span></div>
                        <div class="d-flex justify-content-between py-1 align-items-center">
                            <label class="mb-0" for="v-vat">ภาษีมูลค่าเพิ่ม (VAT)</label>
                            <span class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="v-vat7" title="คิด 7%">7%</button>
                                <input type="text" class="form-control form-control-sm text-end" id="v-vat" style="width:120px" value="0.00">
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-top"><span>จำนวนรวมทั้งสิ้น</span><span class="fw-semibold" id="v-total">0.00</span></div>
                        <div class="d-flex justify-content-between py-1 align-items-center">
                            <label class="mb-0" for="v-wht">หักภาษี ณ ที่จ่าย</label>
                            <span class="d-flex gap-2 align-items-center">
                                <select class="form-select form-select-sm" id="v-wht" style="width:150px">
                                    <option value="">-- ไม่หักภาษี --</option>
                                    <?php foreach (FinanceCashVoucher::WHT_TYPES as $val => $info): ?>
                                        <option value="<?= $val ?>"><?= Html::encode($info['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span id="v-wht-amt" class="text-danger">0.00</span>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-top fs-5"><span class="fw-bold">จำนวนเงินที่จ่ายจริง</span><span class="fw-bold text-success" id="v-net">0.00</span></div>
                    </div>
                </div>

                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="v-account">จ่ายจากบัญชี <span class="text-danger">*</span></label>
                        <select class="form-select" id="v-account">
                            <option value="">== กรุณาเลือก ==</option>
                            <?php foreach ($accounts as $aid => $alabel): ?>
                                <option value="<?= (int) $aid ?>"><?= Html::encode($alabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="text-danger small" data-err="account_id"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="v-payee">จ่ายให้ (ถ้ามี)</label>
                        <input type="text" class="form-control" id="v-payee" maxlength="255" list="v-vendor-list" placeholder="พิมพ์ค้นหาจากทะเบียนผู้ขาย หรือพิมพ์เอง">
                        <datalist id="v-vendor-list">
                            <?php foreach ($vendors as $vt): ?><option value="<?= Html::encode($vt) ?>"></option><?php endforeach; ?>
                        </datalist>
                        <div class="form-text">ต่อทะเบียนผู้ขายจากระบบพัสดุ (<?= count($vendors) ?> ราย)</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="v-note">หมายเหตุ</label>
                        <textarea class="form-control" id="v-note" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="v-submit"><i class="bi bi-save me-1"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

<template id="v-line-template">
    <tr>
        <td class="v-idx"></td>
        <td><select class="form-select form-select-sm v-cat"><option value="">== กรุณาเลือก ==</option><?= $leafOptions ?></select></td>
        <td><input type="text" class="form-control form-control-sm v-bc" maxlength="64"></td>
        <td><input type="text" class="form-control form-control-sm text-end v-amt" inputmode="decimal" value=""></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger v-del"><i class="bi bi-trash"></i></button></td>
    </tr>
</template>

<?php
$this->registerJs(<<<JS
(function () {
    const WHT = {$whtRatesJson};
    const CSRF = { param: '{$csrfParam}', token: '{$csrfToken}' };
    const SAVE_URL = '{$saveUrl}';
    const GET_URL = '{$getUrl}';
    const money = v => (parseFloat(String(v).replace(/[, ]/g, '')) || 0);
    const fmt = v => v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const $ = id => document.getElementById(id);

    const modalEl = $('voucherModal');
    const modal = new bootstrap.Modal(modalEl);
    const body = $('v-lines-body');
    const tpl = $('v-line-template');
    const alertBox = $('v-alert');

    function renumber() { [...body.children].forEach((tr, i) => tr.querySelector('.v-idx').textContent = i + 1); }
    function addLine(data) {
        const tr = tpl.content.firstElementChild.cloneNode(true);
        body.appendChild(tr);
        if (data) {
            tr.querySelector('.v-cat').value = data.category_id || '';
            tr.querySelector('.v-bc').value = data.bc_ref || '';
            tr.querySelector('.v-amt').value = data.amount ? fmt(data.amount) : '';
        }
        tr.querySelector('.v-del').addEventListener('click', () => { tr.remove(); renumber(); recalc(); });
        tr.querySelector('.v-amt').addEventListener('input', recalc);
        renumber();
        return tr;
    }
    function recalc() {
        let sub = 0;
        body.querySelectorAll('.v-amt').forEach(a => sub += money(a.value));
        const vat = money($('v-vat').value);
        const total = sub + vat;
        const rate = WHT[$('v-wht').value] || 0;
        const wht = sub * rate / 100;
        const net = total - wht;
        $('v-subtotal').textContent = fmt(sub);
        $('v-total').textContent = fmt(total);
        $('v-wht-amt').textContent = fmt(wht);
        $('v-net').textContent = fmt(net);
    }
    $('v-add-line').addEventListener('click', () => addLine());
    $('v-vat').addEventListener('input', recalc);
    $('v-wht').addEventListener('change', recalc);
    $('v-vat7').addEventListener('click', () => {
        let sub = 0; body.querySelectorAll('.v-amt').forEach(a => sub += money(a.value));
        $('v-vat').value = fmt(sub * 7 / 100); recalc();
    });

    function setDate(v) { const d = $('v-date'); d.value = v || ''; d.dispatchEvent(new Event('change', { bubbles: true })); }
    function clearErrors() { alertBox.classList.add('d-none'); alertBox.textContent = ''; document.querySelectorAll('#voucherModal [data-err]').forEach(e => e.textContent = ''); }
    function reset() {
        clearErrors(); body.innerHTML = '';
        $('v-id').value = ''; $('v-docno').value = ''; $('v-cheque').value = '';
        $('v-payee').value = ''; $('v-note').value = ''; $('v-vat').value = '0.00';
        $('v-wht').value = ''; $('v-account').value = ''; setDate('');
        (document.querySelector('input[name="v-method"]:checked') || {}).checked = false;
    }

    function openCreate(year) {
        reset(); $('voucherModalTitle').innerHTML = '<i class="bi bi-plus-circle me-1"></i> บันทึกรายจ่าย (ใบสำคัญจ่าย)';
        $('v-year').value = year || ''; addLine(); recalc(); modal.show();
    }
    function openEdit(id) {
        reset();
        fetch(GET_URL + '?id=' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(d => {
            $('voucherModalTitle').innerHTML = '<i class="bi bi-pencil me-1"></i> แก้ไขใบสำคัญจ่าย';
            $('v-id').value = d.id; $('v-year').value = d.fiscal_year; setDate(d.pay_date);
            $('v-docno').value = d.doc_no || ''; $('v-cheque').value = d.cheque_no || '';
            $('v-account').value = d.account_id || ''; $('v-payee').value = d.payee_name || '';
            $('v-note').value = d.note || ''; $('v-vat').value = fmt(d.vat_amount || 0); $('v-wht').value = d.wht_type || '';
            const pm = document.querySelector('input[name="v-method"][value="' + (d.pay_method || '') + '"]'); if (pm) pm.checked = true;
            (d.lines || []).forEach(addLine);
            if (!(d.lines || []).length) addLine();
            recalc(); modal.show();
        });
    }

    $('v-submit').addEventListener('click', function () {
        clearErrors();
        const fd = new FormData();
        fd.append(CSRF.param, CSRF.token);
        fd.append('id', $('v-id').value);
        fd.append('fiscal_year', $('v-year').value);
        fd.append('pay_date', $('v-date').value);
        fd.append('doc_no', $('v-docno').value);
        fd.append('cheque_no', $('v-cheque').value);
        fd.append('account_id', $('v-account').value);
        fd.append('payee_name', $('v-payee').value);
        fd.append('note', $('v-note').value);
        fd.append('vat_amount', money($('v-vat').value));
        fd.append('wht_type', $('v-wht').value);
        const pm = document.querySelector('input[name="v-method"]:checked');
        fd.append('pay_method', pm ? pm.value : '');
        let i = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const cid = tr.querySelector('.v-cat').value;
            const amt = money(tr.querySelector('.v-amt').value);
            if (cid && amt > 0) {
                fd.append('lines[' + i + '][category_id]', cid);
                fd.append('lines[' + i + '][bc_ref]', tr.querySelector('.v-bc').value);
                fd.append('lines[' + i + '][amount]', amt);
                i++;
            }
        });
        this.disabled = true;
        fetch(SAVE_URL, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(res => {
            this.disabled = false;
            if (res.ok) { location.reload(); return; }
            if (res.message) { alertBox.textContent = res.message; alertBox.classList.remove('d-none'); }
            if (res.errors) Object.entries(res.errors).forEach(([f, msgs]) => {
                const el = document.querySelector('#voucherModal [data-err="' + f + '"]');
                if (el) el.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
            });
        }).catch(() => { this.disabled = false; alertBox.textContent = 'บันทึกไม่สำเร็จ กรุณาลองใหม่'; alertBox.classList.remove('d-none'); });
    });

    document.querySelectorAll('[data-cash-add]').forEach(b => b.addEventListener('click', () => openCreate(b.dataset.year)));
    document.querySelectorAll('[data-cash-edit]').forEach(b => b.addEventListener('click', () => openEdit(b.dataset.id)));
})();
JS);
?>
