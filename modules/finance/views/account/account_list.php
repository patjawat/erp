<?php

use app\modules\finance\models\FinanceCashAccount;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $type bank|treasury */
/** @var string $title */
/** @var string $active */
/** @var FinanceCashAccount[] $accounts */
/** @var int $year */
/** @var string|null $bank */

$isBank = $type === FinanceCashAccount::TYPE_BANK;
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-bank2" aria-hidden="true"></i><?= Html::encode($title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ทะเบียนบัญชี + ยอดคงเหลือรายปีงบ (กรอกเอง ยกไปปีถัดไป) — ลากเพื่อจัดลำดับ<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$reorderUrl = Url::to(['reorder']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>

<?= $this->render('@app/modules/finance/views/cash/_menu', ['active' => 'account']) ?>
<?= $this->render('_account_menu', ['active' => $active]) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form method="get" class="d-flex flex-wrap gap-2 align-items-end mb-0">
            <?php if ($isBank): ?>
                <div>
                    <label class="form-label mb-0 small">ธนาคาร</label>
                    <select class="form-select form-select-sm" name="bank" onchange="this.form.submit()">
                        <option value="">— ทุกธนาคาร —</option>
                        <?php foreach (FinanceCashAccount::BANKS as $b): ?>
                            <option value="<?= Html::encode($b) ?>" <?= $bank === $b ? 'selected' : '' ?>><?= Html::encode($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div>
                <label class="form-label mb-0 small">ยอดคงเหลือปีงบ</label>
                <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:110px" onchange="this.form.submit()">
            </div>
        </form>
        <button type="button" class="btn btn-primary btn-sm" data-acc-add><i class="bi bi-plus-lg me-1"></i> เพิ่มบัญชี</button>
    </div>
    <div class="card-body">
        <p class="text-body-secondary small"><i class="bi bi-grip-vertical"></i> ลากแถวเพื่อเปลี่ยนลำดับการแสดงผล</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width:36px"></th><th>เลขที่บัญชี</th><th>ชื่อบัญชี</th>
                        <th class="text-end">ยอดคงเหลือ <?= $year ?></th>
                        <th>ประเภท</th><?php if ($isBank): ?><th>ธนาคาร</th><th>การรับเงิน</th><?php endif; ?>
                        <th class="text-center" style="width:120px">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="acc-rows">
                    <?php foreach ($accounts as $a): $bal = $a->balanceFor($year); ?>
                        <tr draggable="true" data-id="<?= $a->id ?>">
                            <td class="text-center text-body-secondary" style="cursor:grab"><i class="bi bi-grip-vertical"></i></td>
                            <td><?= Html::encode($a->code ?: '-') ?></td>
                            <td><?= Html::encode($a->name) ?><?= $a->branch ? ' <small class="text-body-secondary">(' . Html::encode($a->branch) . ')</small>' : '' ?></td>
                            <td class="text-end fw-semibold"><?= number_format($bal, 2) ?></td>
                            <td><small><?= Html::encode($a->deposit_type ?: '-') ?></small></td>
                            <?php if ($isBank): ?>
                                <td><small><?= Html::encode($a->bank_name ?: '-') ?></small></td>
                                <td>
                                    <?php if ($a->is_promptpay): ?><span class="badge bg-info-subtle text-info-emphasis">พร้อมเพย์</span><?php endif; ?>
                                    <?php if ($a->is_credit): ?><span class="badge bg-secondary-subtle text-secondary-emphasis">บัตรเครดิต</span><?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-acc-edit
                                    data-id="<?= $a->id ?>" data-code="<?= Html::encode($a->code) ?>" data-name="<?= Html::encode($a->name) ?>"
                                    data-bank="<?= Html::encode($a->bank_name) ?>" data-branch="<?= Html::encode($a->branch) ?>"
                                    data-deposit="<?= Html::encode($a->deposit_type) ?>" data-pp="<?= $a->is_promptpay ?>" data-cc="<?= $a->is_credit ?>"
                                    data-bal="<?= $bal ?>"><i class="bi bi-pencil"></i></button>
                                <?= Html::beginForm(['delete'], 'post', ['class' => 'd-inline']) ?>
                                <?= Html::hiddenInput('id', $a->id) ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบบัญชีนี้?')"><i class="bi bi-trash"></i></button>
                                <?= Html::endForm() ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$accounts): ?><tr><td colspan="<?= $isBank ? 8 : 6 ?>" class="text-center text-body-secondary py-5">ยังไม่มีบัญชี</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal เพิ่ม/แก้ไขบัญชี -->
<div class="modal fade" id="accModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <?= Html::beginForm(['save'], 'post', ['class' => 'modal-content']) ?>
        <div class="modal-header"><h5 class="modal-title" id="accModalTitle"><?= Html::encode($title) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <?= Html::hiddenInput('id', '', ['id' => 'acc-id']) ?>
            <?= Html::hiddenInput('account_type', $type) ?>
            <?php if ($isBank): ?>
                <div class="mb-2">
                    <label class="form-label">ธนาคาร <span class="text-danger">*</span></label>
                    <select class="form-select" name="bank_name" id="acc-bank">
                        <option value="">-- กรุณาเลือก --</option>
                        <?php foreach (FinanceCashAccount::BANKS as $b): ?><option value="<?= Html::encode($b) ?>"><?= Html::encode($b) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label">ประเภทบัญชี</label>
                        <select class="form-select" name="deposit_type" id="acc-deposit">
                            <option value="">-- เลือก --</option>
                            <?php foreach (FinanceCashAccount::DEPOSIT_TYPES as $d): ?><option value="<?= Html::encode($d) ?>"><?= Html::encode($d) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6"><label class="form-label">สาขา</label><input type="text" class="form-control" name="branch" id="acc-branch"></div>
                </div>
            <?php endif; ?>
            <div class="mb-2"><label class="form-label">เลขที่บัญชี</label><input type="text" class="form-control" name="code" id="acc-code" maxlength="32"></div>
            <div class="mb-2"><label class="form-label">ชื่อบัญชี <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="acc-name" required maxlength="255"></div>
            <?php if ($isBank): ?>
                <div class="mb-2">
                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="is_promptpay" value="1" id="acc-pp"><label class="form-check-label" for="acc-pp">บัญชีพร้อมเพย์</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="is_credit" value="1" id="acc-cc"><label class="form-check-label" for="acc-cc">รับเงินบัตรเครดิต</label></div>
                </div>
            <?php endif; ?>
            <hr>
            <div class="row g-2">
                <div class="col-6"><label class="form-label">ยอดคงเหลือ ณ ปีงบ</label><input type="number" class="form-control" name="balance_year" id="acc-byear" value="<?= $year ?>"></div>
                <div class="col-6"><label class="form-label">จำนวนเงิน</label><input type="text" class="form-control text-end" name="balance_amount" id="acc-bamt" inputmode="decimal"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" class="btn btn-primary">บันทึก</button></div>
        <?= Html::endForm() ?>
    </div>
</div>

<?php
$this->registerJs(<<<JS
(function () {
    const REORDER = '{$reorderUrl}';
    const CSRF_PARAM = '{$csrfParam}';
    const CSRF = '{$csrfToken}';
    const modalEl = document.getElementById('accModal');
    const modal = new bootstrap.Modal(modalEl);
    const v = (id, val) => { const e = document.getElementById(id); if (e) e.value = val; };
    const chk = (id, val) => { const e = document.getElementById(id); if (e) e.checked = !!val; };

    document.querySelectorAll('[data-acc-add]').forEach(b => b.addEventListener('click', function () {
        document.getElementById('accModalTitle').textContent = 'เพิ่มบัญชี';
        v('acc-id',''); v('acc-bank',''); v('acc-deposit',''); v('acc-branch',''); v('acc-code',''); v('acc-name','');
        chk('acc-pp',false); chk('acc-cc',false); v('acc-bamt','');
        modal.show();
    }));
    document.querySelectorAll('[data-acc-edit]').forEach(b => b.addEventListener('click', function () {
        const d = this.dataset;
        document.getElementById('accModalTitle').textContent = 'แก้ไข: ' + d.name;
        v('acc-id',d.id); v('acc-bank',d.bank||''); v('acc-deposit',d.deposit||''); v('acc-branch',d.branch||'');
        v('acc-code',d.code||''); v('acc-name',d.name||''); chk('acc-pp',d.pp==='1'); chk('acc-cc',d.cc==='1');
        v('acc-bamt', d.bal && d.bal !== '0' ? d.bal : '');
        modal.show();
    }));

    // drag reorder
    const tbody = document.getElementById('acc-rows');
    let dragEl = null;
    tbody.querySelectorAll('tr[draggable]').forEach(tr => {
        tr.addEventListener('dragstart', () => { dragEl = tr; tr.classList.add('table-active'); });
        tr.addEventListener('dragend', () => { tr.classList.remove('table-active'); saveOrder(); });
        tr.addEventListener('dragover', e => {
            e.preventDefault();
            const t = e.currentTarget;
            if (dragEl && t !== dragEl) {
                const rect = t.getBoundingClientRect();
                const after = (e.clientY - rect.top) > rect.height / 2;
                tbody.insertBefore(dragEl, after ? t.nextSibling : t);
            }
        });
    });
    function saveOrder() {
        const ids = [...tbody.querySelectorAll('tr[data-id]')].map(tr => tr.dataset.id);
        const fd = new FormData();
        fd.append(CSRF_PARAM, CSRF);
        ids.forEach(id => fd.append('ids[]', id));
        fetch(REORDER, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    }
})();
JS);
?>
