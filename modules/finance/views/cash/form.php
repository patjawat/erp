<?php

use app\modules\finance\models\FinanceCashCategory;
use app\modules\finance\models\FinanceCashTxn;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $type IN|OUT ของหน้ารายการที่ modal นี้อยู่ */
/** @var array $tree ผังบัญชีของประเภทนี้ (asArray: id, parent_id, level, name) */
/** @var app\modules\finance\models\FinanceReceiptBook[] $receiptBooks */

$isIn = $type === FinanceCashCategory::TYPE_IN;
$typeLabel = FinanceCashCategory::typeLabel($type);

$treeForJs = array_map(static fn ($n) => [
    'id' => (int) $n['id'],
    'parent_id' => $n['parent_id'] !== null ? (int) $n['parent_id'] : 0,
    'name' => $n['name'],
], $tree);
$treeJson = Json::htmlEncode($treeForJs);
$methodsJson = Json::htmlEncode(FinanceCashTxn::PAY_METHODS);

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$saveUrl = Url::to(['save']);
$getUrl = Url::to(['get']);
?>
<div class="modal fade" id="txnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header <?= $isIn ? 'bg-success' : 'bg-warning' ?> text-white">
                <h5 class="modal-title" id="txnModalTitle"><i class="bi bi-cash-coin me-1"></i> บันทึก<?= Html::encode($typeLabel) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="txn-alert"></div>
                <input type="hidden" name="id" id="txn-id">
                <input type="hidden" name="FinanceCashTxn[txn_type]" id="txn-type" value="<?= Html::encode($type) ?>">
                <input type="hidden" name="FinanceCashTxn[category_id]" id="txn-category_id">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="txn-grp">กลุ่ม <span class="text-danger">*</span></label>
                        <select class="form-select" id="txn-grp"><option value="">— เลือกกลุ่ม —</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="txn-cat">หมวด <span class="text-danger">*</span></label>
                        <select class="form-select" id="txn-cat"><option value="">— เลือกหมวด —</option></select>
                    </div>
                    <div class="col-12" id="txn-acc-wrap" hidden>
                        <label class="form-label" for="txn-acc">หัวข้อบัญชี (ระดับย่อย) <span class="text-danger">*</span></label>
                        <select class="form-select" id="txn-acc"><option value="">— เลือกหัวข้อบัญชี —</option></select>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-light border py-2 small mb-1">หัวข้อบัญชีที่เลือก:
                            <span id="txn-cat-preview" class="fw-semibold text-primary">— ยังไม่ได้เลือก —</span></div>
                        <div class="text-danger small" data-err="category_id"></div>
                    </div>

                    <?php if ($isIn && !empty($receiptBooks)): ?>
                    <div class="col-12">
                        <label class="form-label" for="txn-book">เล่มใบเสร็จที่เบิก <span class="text-body-secondary small">(ช่วยเติมเลขให้)</span></label>
                        <select class="form-select" id="txn-book">
                            <option value="">— เลือกเล่ม (หรือกรอกเลขที่ใบเสร็จเอง) —</option>
                            <?php foreach ($receiptBooks as $bk): ?>
                                <option value="<?= Html::encode($bk->book_no) ?>"><?= Html::encode($bk->book_no) ?> (<?= (int) $bk->number_from ?>–<?= (int) $bk->number_to ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">เลือกเล่ม → เลขที่ใบเสร็จจะขึ้นต้น "เล่ม/" ให้กรอกต่อเฉพาะเลข</div>
                    </div>
                    <?php elseif ($isIn): ?>
                    <div class="col-12"><div class="alert alert-light border py-2 small mb-0"><i class="bi bi-info-circle me-1"></i>ยังไม่มีเล่มใบเสร็จที่เบิกให้คุณ — กรอกเลขที่ใบเสร็จเองได้ (เบิกเล่มที่เมนู “ทะเบียนใบเสร็จ”)</div></div>
                    <?php endif; ?>

                    <div class="col-md-4">
                        <label class="form-label" for="txn-year">ปีงบประมาณ <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="txn-year" name="FinanceCashTxn[fiscal_year]">
                        <div class="text-danger small" data-err="fiscal_year"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="txn-date">วันที่ออกใบเสร็จ/เอกสาร <span class="text-danger">*</span></label>
                        <?= DatepickerThai::widget([
                            'name' => 'FinanceCashTxn[doc_date]',
                            'value' => '',
                            'options' => ['id' => 'txn-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
                        ]) ?>
                        <div class="text-danger small" data-err="doc_date"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="txn-docno">เลขที่ใบเสร็จ/เอกสาร</label>
                        <input type="text" class="form-control" id="txn-docno" name="FinanceCashTxn[doc_no]" maxlength="64" placeholder="เช่น 606/500">
                        <div class="text-danger small" data-err="doc_no"></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block"><?= $isIn ? 'การรับ' : 'การจ่าย' ?></label>
                        <div id="txn-methods" class="d-flex flex-wrap gap-3"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="txn-amount">จำนวนเงิน <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-end" id="txn-amount" name="FinanceCashTxn[amount]" inputmode="decimal" placeholder="0.00">
                        <div class="text-danger small" data-err="amount"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="txn-party"><?= $isIn ? 'รับจาก' : 'จ่ายให้' ?></label>
                        <input type="text" class="form-control" id="txn-party" name="FinanceCashTxn[party_name]" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="txn-note">หมายเหตุ</label>
                        <textarea class="form-control" id="txn-note" name="FinanceCashTxn[note]" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="txn-submit"><i class="bi bi-save me-1"></i> บันทึก</button>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
(function () {
    const tree = {$treeJson};
    const methods = {$methodsJson};
    const TYPE = '{$type}';
    const SAVE_URL = '{$saveUrl}';
    const GET_URL = '{$getUrl}';
    const CSRF = { param: '{$csrfParam}', token: '{$csrfToken}' };

    const byId = {}, childrenOf = {};
    tree.forEach(n => { byId[n.id] = n; (childrenOf[n.parent_id || 0] = childrenOf[n.parent_id || 0] || []).push(n); });

    const modalEl = document.getElementById('txnModal');
    const modal = new bootstrap.Modal(modalEl);
    const $ = id => document.getElementById(id);
    const grp = $('txn-grp'), cat = $('txn-cat'), acc = $('txn-acc'), accWrap = $('txn-acc-wrap');
    const hidden = $('txn-category_id'), preview = $('txn-cat-preview'), alertBox = $('txn-alert');

    // สร้าง radio วิธีรับ/จ่าย
    const mBox = $('txn-methods');
    Object.entries(methods).forEach(([val, label]) => {
        const w = document.createElement('div'); w.className = 'form-check form-check-inline';
        w.innerHTML = '<input class="form-check-input" type="radio" name="FinanceCashTxn[pay_method]" id="m-' + val + '" value="' + val + '">' +
                      '<label class="form-check-label" for="m-' + val + '">' + label + '</label>';
        mBox.appendChild(w);
    });

    function fill(sel, parentId, ph) {
        sel.innerHTML = '<option value="">' + ph + '</option>';
        (childrenOf[parentId || 0] || []).forEach(n => {
            const o = document.createElement('option'); o.value = n.id; o.textContent = n.name; sel.appendChild(o);
        });
    }
    const hasChildren = id => (childrenOf[id] || []).length > 0;

    function recompute() {
        let leaf = '';
        if (!accWrap.hidden && acc.value) leaf = acc.value;
        else if (cat.value && !hasChildren(cat.value)) leaf = cat.value;
        hidden.value = leaf;
        preview.textContent = leaf && byId[leaf] ? byId[leaf].name : '— ยังไม่ได้เลือก —';
    }
    fill(grp, 0, '— เลือกกลุ่ม —');
    grp.addEventListener('change', () => { fill(cat, grp.value, '— เลือกหมวด —'); accWrap.hidden = true; acc.innerHTML=''; recompute(); });
    cat.addEventListener('change', () => {
        if (cat.value && hasChildren(cat.value)) { fill(acc, cat.value, '— เลือกหัวข้อบัญชี —'); accWrap.hidden = false; }
        else { accWrap.hidden = true; acc.innerHTML=''; }
        recompute();
    });
    acc.addEventListener('change', recompute);

    function setDate(v) {
        const d = $('txn-date'); d.value = v || '';
        d.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function clearErrors() {
        alertBox.classList.add('d-none'); alertBox.textContent = '';
        document.querySelectorAll('#txnModal [data-err]').forEach(e => e.textContent = '');
    }
    function setChain(chain) {
        grp.value = chain.group || '';
        fill(cat, chain.group, '— เลือกหมวด —');
        if (chain.category) {
            cat.value = chain.category;
            if (hasChildren(chain.category)) { fill(acc, chain.category, '— เลือกหัวข้อบัญชี —'); accWrap.hidden = false; if (chain.account) acc.value = chain.account; }
        }
        recompute();
    }

    function openCreate(year) {
        clearErrors();
        $('txnModalTitle').innerHTML = '<i class="bi bi-plus-circle me-1"></i> บันทึกรายการ';
        $('txn-id').value = '';
        $('txn-year').value = year || '';
        setDate(''); $('txn-docno').value=''; $('txn-amount').value=''; $('txn-party').value=''; $('txn-note').value='';
        (document.querySelector('input[name="FinanceCashTxn[pay_method]"][value="cash"]')||{}).checked = true;
        grp.value=''; fill(cat, 0, '— เลือกหมวด —'); accWrap.hidden = true; acc.innerHTML=''; recompute();
        modal.show();
    }
    function openEdit(id) {
        clearErrors();
        fetch(GET_URL + '?id=' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => {
                $('txnModalTitle').innerHTML = '<i class="bi bi-pencil me-1"></i> แก้ไขรายการ';
                $('txn-id').value = d.id; $('txn-year').value = d.fiscal_year;
                setDate(d.doc_date); $('txn-docno').value = d.doc_no || '';
                $('txn-amount').value = d.amount || ''; $('txn-party').value = d.party_name || ''; $('txn-note').value = d.note || '';
                const pm = document.querySelector('input[name="FinanceCashTxn[pay_method]"][value="' + (d.pay_method||'') + '"]');
                if (pm) pm.checked = true;
                setChain(d.chain || {});
                modal.show();
            });
    }

    $('txn-submit').addEventListener('click', function () {
        clearErrors();
        const fd = new FormData();
        fd.append(CSRF.param, CSRF.token);
        fd.append('id', $('txn-id').value);
        fd.append('FinanceCashTxn[txn_type]', TYPE);
        fd.append('FinanceCashTxn[category_id]', hidden.value);
        fd.append('FinanceCashTxn[fiscal_year]', $('txn-year').value);
        fd.append('FinanceCashTxn[doc_date]', $('txn-date').value);
        fd.append('FinanceCashTxn[doc_no]', $('txn-docno').value);
        fd.append('FinanceCashTxn[amount]', $('txn-amount').value);
        fd.append('FinanceCashTxn[party_name]', $('txn-party').value);
        fd.append('FinanceCashTxn[note]', $('txn-note').value);
        const pm = document.querySelector('input[name="FinanceCashTxn[pay_method]"]:checked');
        fd.append('FinanceCashTxn[pay_method]', pm ? pm.value : '');
        this.disabled = true;
        fetch(SAVE_URL, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(res => {
                this.disabled = false;
                if (res.ok) { location.reload(); return; }
                if (res.message) { alertBox.textContent = res.message; alertBox.classList.remove('d-none'); }
                if (res.errors) Object.entries(res.errors).forEach(([f, msgs]) => {
                    const el = document.querySelector('#txnModal [data-err="' + f + '"]');
                    if (el) el.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
                });
            }).catch(() => { this.disabled = false; alertBox.textContent = 'บันทึกไม่สำเร็จ กรุณาลองใหม่'; alertBox.classList.remove('d-none'); });
    });

    document.querySelectorAll('[data-cash-add]').forEach(b => b.addEventListener('click', () => openCreate(b.dataset.year)));
    document.querySelectorAll('[data-cash-edit]').forEach(b => b.addEventListener('click', () => openEdit(b.dataset.id)));
})();
JS);

// เลือกเล่มใบเสร็จ → เติมคำนำหน้า "เล่ม/" ในช่องเลขที่ใบเสร็จ
$this->registerJs(<<<'JS2'
(function () {
    var b = document.getElementById('txn-book'), d = document.getElementById('txn-docno');
    if (!b || !d) return;
    b.addEventListener('change', function () {
        if (!b.value) return;
        var cur = d.value || '', after = cur.indexOf('/') >= 0 ? cur.split('/').pop() : '';
        d.value = b.value + '/' + after; d.focus();
    });
})();
JS2);
?>
