<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceReceiptBook;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var FinanceReceiptBook[] $books */
/** @var string|null $status */
/** @var string|null $emp */
/** @var array $employees id => name */

$this->title = 'ทะเบียนคุมใบเสร็จรับเงิน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = 'ทะเบียนใบเสร็จ';

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-receipt-cutoff" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>รับเล่มเข้า → เบิกจ่ายให้เจ้าหน้าที่ → ใช้บันทึกรายรับ (คุมใช้แล้ว/คงเหลือ/เลขซ้ำ-ข้าม)<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();
?>

<?= $this->render('@app/modules/finance/views/cash/_menu', ['active' => 'receipt']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <form method="get" class="d-flex flex-wrap gap-2 align-items-end mb-0">
            <div><label class="form-label mb-0 small">สถานะ</label>
                <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    <?php foreach (FinanceReceiptBook::STATUS_LABELS as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= Html::encode($v) ?></option>
                    <?php endforeach; ?>
                </select></div>
            <div><label class="form-label mb-0 small">เบิกให้</label>
                <select class="form-select form-select-sm" name="emp" onchange="this.form.submit()" style="max-width:220px">
                    <option value="">— ทุกคน —</option>
                    <?php foreach ($employees as $eid => $ename): ?>
                        <option value="<?= $eid ?>" <?= (string) $emp === (string) $eid ? 'selected' : '' ?>><?= Html::encode($ename) ?></option>
                    <?php endforeach; ?>
                </select></div>
        </form>
        <button type="button" class="btn btn-primary btn-sm" data-rb-add><i class="bi bi-plus-lg me-1"></i> รับเล่มเข้า</button>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr>
                <th>เลขเล่ม</th><th>ช่วงเลข</th><th>ประเภท</th><th>เบิกให้</th><th>สถานะ</th>
                <th class="text-center">ใช้แล้ว/ทั้งหมด</th><th class="text-end">คงเหลือ</th><th class="text-center">ตรวจ</th><th class="text-center" style="width:130px">จัดการ</th>
            </tr></thead>
            <tbody>
                <?php foreach ($books as $b): $u = $b->usage(); ?>
                    <tr>
                        <td class="fw-semibold"><?= Html::encode($b->book_no) ?></td>
                        <td><?= (int) $b->number_from ?>–<?= (int) $b->number_to ?></td>
                        <td><small><?= Html::encode($b->receipt_type ?: '-') ?></small></td>
                        <td><small><?= Html::encode($b->issuedTo ? $b->issuedTo->fullname() : '-') ?></small></td>
                        <td><span class="badge bg-<?= $b->status === 'issued' ? 'primary' : ($b->status === 'completed' ? 'success' : ($b->status === 'cancelled' ? 'secondary' : 'info')) ?>-subtle text-<?= $b->status === 'issued' ? 'primary' : ($b->status === 'completed' ? 'success' : ($b->status === 'cancelled' ? 'secondary' : 'info')) ?>-emphasis"><?= Html::encode($b->statusLabel()) ?></span></td>
                        <td class="text-center"><?= $u['used'] ?> / <?= $u['total'] ?>
                            <div class="progress mt-1" style="height:5px"><div class="progress-bar" style="width:<?= $u['total'] ? round($u['used'] / $u['total'] * 100) : 0 ?>%"></div></div>
                        </td>
                        <td class="text-end fw-semibold"><?= $u['remaining'] ?></td>
                        <td class="text-center">
                            <?php if ($u['duplicates']): ?><span class="badge bg-danger" title="เลขซ้ำ: <?= Html::encode(implode(',', $u['duplicates'])) ?>">ซ้ำ <?= count($u['duplicates']) ?></span><?php endif; ?>
                            <?php if ($u['outOfRange']): ?><span class="badge bg-warning text-dark" title="นอกช่วง: <?= Html::encode(implode(',', $u['outOfRange'])) ?>">นอกช่วง</span><?php endif; ?>
                            <?php if (!$u['duplicates'] && !$u['outOfRange']): ?><i class="bi bi-check-circle text-success"></i><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= Url::to(['view', 'id' => $b->id]) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-rb-edit
                                data-id="<?= $b->id ?>" data-book="<?= Html::encode($b->book_no) ?>" data-from="<?= (int) $b->number_from ?>" data-to="<?= (int) $b->number_to ?>"
                                data-type="<?= Html::encode($b->receipt_type) ?>" data-received="<?= $b->received_date ? Html::encode(AppHelper::convertToThai($b->received_date)) : '' ?>"
                                data-emp="<?= (int) $b->issued_to_emp_id ?>" data-issued="<?= $b->issued_date ? Html::encode(AppHelper::convertToThai($b->issued_date)) : '' ?>"
                                data-status="<?= Html::encode($b->status) ?>" data-note="<?= Html::encode($b->note) ?>"><i class="bi bi-pencil"></i></button>
                            <?= Html::beginForm(['delete'], 'post', ['class' => 'd-inline']) ?>
                            <?= Html::hiddenInput('id', $b->id) ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบเล่มนี้?')"><i class="bi bi-trash"></i></button>
                            <?= Html::endForm() ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$books): ?><tr><td colspan="9" class="text-center text-body-secondary py-5">ยังไม่มีเล่มใบเสร็จ — กด “รับเล่มเข้า”</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal รับเล่ม/แก้ไข -->
<div class="modal fade" id="rbModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <?= Html::beginForm(['save'], 'post', ['class' => 'modal-content']) ?>
        <div class="modal-header"><h5 class="modal-title" id="rbTitle">รับเล่มใบเสร็จเข้า</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body row g-3">
            <?= Html::hiddenInput('id', '', ['id' => 'rb-id']) ?>
            <div class="col-md-4"><label class="form-label">เลขที่เล่ม <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="book_no" id="rb-book" required maxlength="32" placeholder="เช่น 606"></div>
            <div class="col-md-4"><label class="form-label">เลขที่เริ่ม <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="number_from" id="rb-from" required></div>
            <div class="col-md-4"><label class="form-label">เลขที่สิ้นสุด <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="number_to" id="rb-to" required></div>
            <div class="col-md-6"><label class="form-label">ประเภทใบเสร็จ</label>
                <input type="text" class="form-control" name="receipt_type" id="rb-type" maxlength="120" placeholder="เช่น ใบเสร็จรับเงินทั่วไป"></div>
            <div class="col-md-6"><label class="form-label">วันที่รับเข้า</label>
                <?= DatepickerThai::widget(['name' => 'received_date', 'value' => '', 'options' => ['id' => 'rb-received', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?></div>
            <hr class="my-1">
            <div class="col-md-6"><label class="form-label">เบิกให้ (เจ้าหน้าที่)</label>
                <select class="form-select" name="issued_to_emp_id" id="rb-emp">
                    <option value="">— ยังไม่เบิก —</option>
                    <?php foreach ($employees as $eid => $ename): ?><option value="<?= $eid ?>"><?= Html::encode($ename) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-6"><label class="form-label">วันที่เบิก</label>
                <?= DatepickerThai::widget(['name' => 'issued_date', 'value' => '', 'options' => ['id' => 'rb-issued', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?></div>
            <div class="col-md-6"><label class="form-label">สถานะ</label>
                <select class="form-select" name="status" id="rb-status">
                    <?php foreach (FinanceReceiptBook::STATUS_LABELS as $k => $v): ?><option value="<?= $k ?>"><?= Html::encode($v) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-6"><label class="form-label">หมายเหตุ</label>
                <input type="text" class="form-control" name="note" id="rb-note" maxlength="255"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" class="btn btn-primary">บันทึก</button></div>
        <?= Html::endForm() ?>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    const modal = new bootstrap.Modal(document.getElementById('rbModal'));
    const v = (id, val) => { const e = document.getElementById(id); if (e) { e.value = val; if (e.tagName === 'INPUT') e.dispatchEvent(new Event('change', { bubbles: true })); } };
    document.querySelectorAll('[data-rb-add]').forEach(b => b.addEventListener('click', function () {
        document.getElementById('rbTitle').textContent = 'รับเล่มใบเสร็จเข้า';
        ['rb-id','rb-book','rb-from','rb-to','rb-type','rb-received','rb-issued','rb-note'].forEach(i => v(i, ''));
        v('rb-emp', ''); v('rb-status', 'received');
        modal.show();
    }));
    document.querySelectorAll('[data-rb-edit]').forEach(b => b.addEventListener('click', function () {
        const d = this.dataset;
        document.getElementById('rbTitle').textContent = 'แก้ไขเล่ม ' + d.book;
        v('rb-id', d.id); v('rb-book', d.book); v('rb-from', d.from); v('rb-to', d.to);
        v('rb-type', d.type || ''); v('rb-received', d.received || ''); v('rb-issued', d.issued || '');
        v('rb-note', d.note || ''); v('rb-emp', d.emp && d.emp !== '0' ? d.emp : ''); v('rb-status', d.status || 'received');
        modal.show();
    }));
})();
JS);
?>
