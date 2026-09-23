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
$this->params['breadcrumbs'][] = 'ทะเบียนคุมใบเสร็จ';

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-receipt-cutoff" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>รับเล่มเข้า → เบิกจ่ายให้เจ้าหน้าที่ → ใช้บันทึกรายรับ (คุมใช้แล้ว/คงเหลือ/เลขซ้ำ-ข้าม)<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'receipt']);
$this->endBlock();

$badgeCls = fn ($s) => $s === 'issued' ? 'primary' : ($s === 'completed' ? 'success' : ($s === 'cancelled' ? 'secondary' : 'info'));
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
        <button type="button" class="btn btn-primary btn-sm" data-rb-add><i class="bi bi-box-seam me-1"></i> รับเล่มเข้า</button>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr>
                <th>เลขเล่ม</th><th>ช่วงเลข</th><th>ประเภท</th><th>เบิกให้</th><th>สถานะ</th>
                <th class="text-center">ใช้แล้ว/ทั้งหมด</th><th class="text-end">คงเหลือ</th><th class="text-center">ตรวจ</th><th class="text-center" style="width:170px">จัดการ</th>
            </tr></thead>
            <tbody>
                <?php foreach ($books as $b): $u = $b->usage(); ?>
                    <tr>
                        <td class="fw-semibold"><?= Html::encode($b->book_no) ?></td>
                        <td><?= (int) $b->number_from ?>–<?= (int) $b->number_to ?></td>
                        <td><small><?= Html::encode($b->receipt_type ?: '-') ?></small></td>
                        <td><small><?= Html::encode($b->issuedTo ? $b->issuedTo->fullname() : '-') ?></small></td>
                        <td><span class="badge bg-<?= $badgeCls($b->status) ?>-subtle text-<?= $badgeCls($b->status) ?>-emphasis"><?= Html::encode($b->statusLabel()) ?></span></td>
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
                            <a href="<?= Url::to(['view', 'id' => $b->id]) ?>" class="btn btn-sm btn-outline-info" title="ดู"><i class="bi bi-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-success" title="เบิกจ่าย" data-rb-issue
                                data-id="<?= $b->id ?>" data-book="<?= Html::encode($b->book_no) ?>" data-range="<?= (int) $b->number_from ?>–<?= (int) $b->number_to ?>"
                                data-emp="<?= (int) $b->issued_to_emp_id ?>" data-issued="<?= $b->issued_date ? Html::encode(AppHelper::convertToThai($b->issued_date)) : '' ?>"
                                data-status="<?= Html::encode($b->status) ?>"><i class="bi bi-box-arrow-right"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" title="แก้ข้อมูลเล่ม" data-rb-edit
                                data-id="<?= $b->id ?>" data-book="<?= Html::encode($b->book_no) ?>" data-from="<?= (int) $b->number_from ?>" data-to="<?= (int) $b->number_to ?>"
                                data-type="<?= Html::encode($b->receipt_type) ?>" data-received="<?= $b->received_date ? Html::encode(AppHelper::convertToThai($b->received_date)) : '' ?>"
                                data-note="<?= Html::encode($b->note) ?>"><i class="bi bi-pencil"></i></button>
                            <?php if ($u['used'] === 0): ?>
                                <?= Html::beginForm(['delete'], 'post', ['class' => 'd-inline']) ?>
                                <?= Html::hiddenInput('id', $b->id) ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="ลบ (ยังไม่มีการใช้)" onclick="return confirm('ลบเล่มนี้? (ยังไม่มีการใช้เลขในเล่ม)')"><i class="bi bi-trash"></i></button>
                                <?= Html::endForm() ?>
                            <?php else: ?>
                                <span class="btn btn-sm btn-outline-secondary disabled" title="ลบไม่ได้ — มีการใช้เลขในเล่มแล้ว (ใช้สถานะ ยกเลิก แทน)"><i class="bi bi-shield-lock"></i></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$books): ?><tr><td colspan="9" class="text-center text-body-secondary py-5">ยังไม่มีเล่มใบเสร็จ — กด “รับเล่มเข้า”</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ฟอร์ม 1: รับเล่มเข้า / แก้ข้อมูลเล่ม -->
<div class="modal fade" id="rbModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <?= Html::beginForm(['save'], 'post', ['class' => 'modal-content']) ?>
        <div class="modal-header bg-primary text-white"><h5 class="modal-title" id="rbTitle">รับเล่มใบเสร็จเข้า</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
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
            <div class="col-12"><label class="form-label">หมายเหตุ</label>
                <input type="text" class="form-control" name="note" id="rb-note" maxlength="255"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" class="btn btn-primary">บันทึก</button></div>
        <?= Html::endForm() ?>
    </div>
</div>

<!-- ฟอร์ม 2: เบิกจ่ายเล่มให้เจ้าหน้าที่ -->
<div class="modal fade" id="issueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <?= Html::beginForm(['issue'], 'post', ['class' => 'modal-content']) ?>
        <div class="modal-header bg-success text-white"><h5 class="modal-title">เบิกจ่ายเล่มใบเสร็จ</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body row g-3">
            <?= Html::hiddenInput('id', '', ['id' => 'is-id']) ?>
            <div class="col-12"><div class="alert alert-light border mb-0 py-2">เล่ม <span class="fw-semibold" id="is-book"></span> <span class="text-body-secondary" id="is-range"></span></div></div>
            <div class="col-md-7"><label class="form-label">เบิกให้ (เจ้าหน้าที่) <span class="text-danger">*</span></label>
                <select class="form-select" name="issued_to_emp_id" id="is-emp">
                    <option value="">— เลือกเจ้าหน้าที่ —</option>
                    <?php foreach ($employees as $eid => $ename): ?><option value="<?= $eid ?>"><?= Html::encode($ename) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-5"><label class="form-label">วันที่เบิก</label>
                <?= DatepickerThai::widget(['name' => 'issued_date', 'value' => '', 'options' => ['id' => 'is-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?></div>
            <div class="col-12"><label class="form-label">สถานะ</label>
                <select class="form-select" name="status" id="is-status">
                    <?php foreach (FinanceReceiptBook::STATUS_LABELS as $k => $v): ?><option value="<?= $k ?>"><?= Html::encode($v) ?></option><?php endforeach; ?>
                </select>
                <div class="form-text">ปกติเลือก “เบิกแล้ว/กำลังใช้” — ใช้ “ยกเลิก” เมื่อคืนเล่ม/เล่มเสีย</div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" class="btn btn-success">บันทึกการเบิก</button></div>
        <?= Html::endForm() ?>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    const rb = new bootstrap.Modal(document.getElementById('rbModal'));
    const is = new bootstrap.Modal(document.getElementById('issueModal'));
    const set = (id, val) => { const e = document.getElementById(id); if (e) { e.value = val; if (e.tagName === 'INPUT') e.dispatchEvent(new Event('change', { bubbles: true })); } };
    const txt = (id, val) => { const e = document.getElementById(id); if (e) e.textContent = val; };

    document.querySelectorAll('[data-rb-add]').forEach(b => b.addEventListener('click', function () {
        document.getElementById('rbTitle').textContent = 'รับเล่มใบเสร็จเข้า';
        ['rb-id','rb-book','rb-from','rb-to','rb-type','rb-received','rb-note'].forEach(i => set(i, ''));
        rb.show();
    }));
    document.querySelectorAll('[data-rb-edit]').forEach(b => b.addEventListener('click', function () {
        const d = this.dataset;
        document.getElementById('rbTitle').textContent = 'แก้ข้อมูลเล่ม ' + d.book;
        set('rb-id', d.id); set('rb-book', d.book); set('rb-from', d.from); set('rb-to', d.to);
        set('rb-type', d.type || ''); set('rb-received', d.received || ''); set('rb-note', d.note || '');
        rb.show();
    }));
    document.querySelectorAll('[data-rb-issue]').forEach(b => b.addEventListener('click', function () {
        const d = this.dataset;
        set('is-id', d.id); txt('is-book', d.book); txt('is-range', '(' + d.range + ')');
        set('is-emp', d.emp && d.emp !== '0' ? d.emp : ''); set('is-date', d.issued || '');
        set('is-status', d.status && d.status !== 'received' ? d.status : 'issued');
        is.show();
    }));
})();
JS);
?>
