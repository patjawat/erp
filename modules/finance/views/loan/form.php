<?php

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanAccount;
use app\modules\finance\models\FinanceLoanExpenseType;
use app\modules\finance\models\FinanceLoanItemKind;
use app\modules\hr\models\Employees;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var FinanceLoan $model */
/** @var app\modules\finance\models\FinanceLoanItem[] $items */
/** @var string $title */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-person-vcard" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ยอดเงินยืมคิดจากบรรทัดประมาณการ และวันครบกำหนดคำนวณจากประเภทค่าใช้จ่าย<?php $this->endBlock();

$kinds = FinanceLoanItemKind::options();
$kindHints = FinanceLoanItemKind::inputHints();
$expenseTypes = FinanceLoanExpenseType::find()->where(['is_active' => true])->orderBy(['sort_order' => SORT_ASC])->all();
$dueRules = [];
foreach ($expenseTypes as $type) {
    $dueRules[$type->id] = ['days' => (int) $type->due_days, 'basis' => $type->due_basis, 'text' => 'ส่งใช้ภายใน ' . (int) $type->due_days . ' วัน นับจาก' . $type->basisLabel()];
}

// เจ้าหน้าที่ที่ยังปฏิบัติงาน บวกคนที่ผูกไว้แล้วในใบนี้ เผื่อผู้ยืมลาออกไปหลังจากยืม
$employeeQuery = Employees::find()->where(['status' => '1']);
if ($model->borrower_emp_id) {
    $employeeQuery->orWhere(['id' => $model->borrower_emp_id]);
}
$employees = [];
$employeeMeta = [];
foreach ($employeeQuery->all() as $emp) {
    $employees[$emp->id] = $emp->fullname();
    $employeeMeta[$emp->id] = ['name' => $emp->fullname(), 'position' => (string) $emp->positionName()];
}
asort($employees);

$rows = $items ?: [];
?>

<?php $form = ActiveForm::begin(['id' => 'loan-form', 'options' => ['class' => 'needs-validation']]); ?>
<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

<div class="d-flex justify-content-end gap-2 mb-3">
    <?= Html::a('ยกเลิก', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> บันทึกใบยืม', ['class' => 'btn btn-primary']) ?>
</div>

<section class="card border mb-3" aria-labelledby="contract-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="contract-heading">ข้อมูลสัญญา</h5></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-3">
            <?= $form->field($model, 'contract_no')->textInput(['maxlength' => true]) ?>
            <div class="form-text">ระบบเสนอเลขถัดไปของปีให้ แก้เองได้</div>
        </div>
        <div class="col-md-2"><?= $form->field($model, 'fiscal_year')->textInput(['inputmode' => 'numeric']) ?></div>
        <div class="col-md-3">
            <label class="form-label">สถานะ</label>
            <div class="form-control-plaintext">
                <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= Html::encode($model->statusLabel()) ?></span>
            </div>
            <div class="form-text">เปลี่ยนด้วยปุ่มในหน้ารายละเอียด เพื่อให้ตามได้ว่าใครเดินขั้นตอนเมื่อไร</div>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'expense_type_id')->dropDownList(
                ArrayHelper::map($expenseTypes, 'id', 'name'),
                ['prompt' => '== กรุณาเลือก ==', 'id' => 'loan-expense-type']
            ) ?>
        </div>
        <div class="col-md-5"><?= $form->field($model, 'account_id')->dropDownList(FinanceLoanAccount::options(), ['prompt' => '== กรุณาเลือก ==']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'request_document_no')->textInput(['maxlength' => true, 'placeholder' => 'เช่น ลย 0033.301/1557']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'request_document_date')->input('date') ?></div>
    </div></div>
</section>

<section class="card border mb-3" aria-labelledby="borrower-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="borrower-heading">ผู้ยืมและวัตถุประสงค์</h5></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-5">
            <?= $form->field($model, 'borrower_emp_id')->widget(Select2::class, [
                'data' => $employees,
                'options' => ['placeholder' => 'ค้นหาชื่อบุคลากร', 'id' => 'loan-borrower-emp'],
                'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
            ])->label('เลือกจากทะเบียนบุคลากร') ?>
            <div class="form-text">ผูกไว้เพื่อให้ระบบแจ้งเตือนทาง Telegram ได้ ไม่บังคับ</div>
        </div>
        <div class="col-md-4"><?= $form->field($model, 'borrower_name')->textInput(['maxlength' => true, 'id' => 'loan-borrower-name']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'borrower_position')->textInput(['maxlength' => true, 'id' => 'loan-borrower-position']) ?></div>
        <div class="col-12"><?= $form->field($model, 'purpose')->textarea(['rows' => 3, 'placeholder' => 'ชื่อโครงการหรือเรื่องที่ขอยืมเงินไปใช้']) ?></div>
    </div></div>
</section>

<section class="card border mb-3" aria-labelledby="schedule-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="schedule-heading">กำหนดเวลา</h5></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-3"><?= $form->field($model, 'borrowed_at')->input('date') ?></div>
        <div class="col-md-3"><?= $form->field($model, 'received_at')->input('date', ['id' => 'loan-received-at']) ?></div>
        <div class="col-md-3"><?= $form->field($model, 'activity_start_at')->input('date') ?></div>
        <div class="col-md-3">
            <?= $form->field($model, 'activity_end_at')->input('date', ['id' => 'loan-activity-end']) ?>
            <div class="form-text">ใช้เป็นจุดตั้งต้นนับวันครบกำหนด</div>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'due_at')->input('date', ['id' => 'loan-due-at']) ?>
        </div>
        <div class="col-md-8 d-flex flex-column justify-content-center">
            <?= $form->field($model, 'due_is_manual')->checkbox(['id' => 'loan-due-manual'])->label('กำหนดวันคืนเอง ไม่ให้ระบบคำนวณทับ') ?>
            <div class="alert alert-light border mb-0 py-2 px-3 small" id="loan-due-hint" role="status">
                <?= Html::encode($model->dueRuleText()) ?>
            </div>
        </div>
    </div></div>
</section>

<section class="card border mb-3" aria-labelledby="estimate-heading">
    <div class="card-header bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0" id="estimate-heading">ประมาณการค่าใช้จ่าย</h5>
            <div class="text-body-secondary small">ยอดเงินยืมคิดจากผลรวมของบรรทัดเหล่านี้ · หน่วย: บาท</div>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="loan-item-add">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> เพิ่มบรรทัด
        </button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="loan-item-table">
            <thead>
                <tr>
                    <th style="min-width:190px">ประเภทรายการ</th>
                    <th style="min-width:180px">ชื่อรายการที่จะพิมพ์</th>
                    <th class="text-end" style="min-width:110px">จำนวน</th>
                    <th class="text-end" style="min-width:110px">จำนวนหน่วย</th>
                    <th class="text-end" style="min-width:120px">อัตราต่อหน่วย</th>
                    <th class="text-end" style="min-width:130px">เป็นเงิน</th>
                    <th style="width:44px"><span class="visually-hidden">ลบ</span></th>
                </tr>
            </thead>
            <tbody id="loan-item-body">
            <?php foreach ($rows as $index => $item): ?>
                <tr class="loan-item-row">
                    <td>
                        <input type="hidden" name="LoanItem[<?= $index ?>][id]" value="<?= Html::encode($item->id) ?>">
                        <?= Html::dropDownList("LoanItem[{$index}][item_kind_id]", $item->item_kind_id, $kinds, ['class' => 'form-select form-select-sm', 'prompt' => '— เลือก —', 'data-role' => 'kind']) ?>
                    </td>
                    <td><?= Html::textInput("LoanItem[{$index}][label]", $item->label, ['class' => 'form-control form-control-sm', 'maxlength' => 255, 'placeholder' => 'เว้นว่าง = ใช้ชื่อประเภท', 'data-role' => 'label']) ?></td>
                    <td><?= Html::textInput("LoanItem[{$index}][persons]", $item->persons, ['class' => 'form-control form-control-sm text-end', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'persons']) ?></td>
                    <td><?= Html::textInput("LoanItem[{$index}][units]", $item->units, ['class' => 'form-control form-control-sm text-end', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'units']) ?></td>
                    <td><?= Html::textInput("LoanItem[{$index}][rate]", $item->rate, ['class' => 'form-control form-control-sm text-end', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'rate']) ?></td>
                    <td><?= Html::textInput("LoanItem[{$index}][amount]", $item->amount, ['class' => 'form-control form-control-sm text-end fw-semibold', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'amount']) ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-role="remove" aria-label="ลบบรรทัดนี้"><i class="bi bi-trash" aria-hidden="true"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-body-tertiary">
                    <th colspan="5" class="text-end">รวมเป็นเงินยืมทั้งสิ้น</th>
                    <th class="text-end font-monospace fs-6" id="loan-item-total">0.00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer bg-body text-body-secondary small" id="loan-item-empty" hidden>
        ยังไม่มีบรรทัดประมาณการ กด “เพิ่มบรรทัด” เพื่อเริ่มกรอก
    </div>
</section>

<section class="card border mb-3" aria-labelledby="note-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="note-heading">หมายเหตุ</h5></div>
    <div class="card-body"><?= $form->field($model, 'note')->textarea(['rows' => 2])->label(false) ?></div>
</section>

<div class="d-flex justify-content-end gap-2 mb-4">
    <?= Html::a('ยกเลิก', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> บันทึกใบยืม', ['class' => 'btn btn-primary']) ?>
</div>

<template id="loan-item-template">
    <tr class="loan-item-row">
        <td>
            <input type="hidden" name="LoanItem[__ROW__][id]" value="">
            <?= Html::dropDownList('LoanItem[__ROW__][item_kind_id]', null, $kinds, ['class' => 'form-select form-select-sm', 'prompt' => '— เลือก —', 'data-role' => 'kind']) ?>
        </td>
        <td><?= Html::textInput('LoanItem[__ROW__][label]', null, ['class' => 'form-control form-control-sm', 'maxlength' => 255, 'placeholder' => 'เว้นว่าง = ใช้ชื่อประเภท', 'data-role' => 'label']) ?></td>
        <td><?= Html::textInput('LoanItem[__ROW__][persons]', null, ['class' => 'form-control form-control-sm text-end', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'persons']) ?></td>
        <td><?= Html::textInput('LoanItem[__ROW__][units]', null, ['class' => 'form-control form-control-sm text-end', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'units']) ?></td>
        <td><?= Html::textInput('LoanItem[__ROW__][rate]', null, ['class' => 'form-control form-control-sm text-end', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'rate']) ?></td>
        <td><?= Html::textInput('LoanItem[__ROW__][amount]', null, ['class' => 'form-control form-control-sm text-end fw-semibold', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'data-role' => 'amount']) ?></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" data-role="remove" aria-label="ลบบรรทัดนี้"><i class="bi bi-trash" aria-hidden="true"></i></button>
        </td>
    </tr>
</template>

<?php ActiveForm::end(); ?>

<?php
$hintsJson = Json::htmlEncode($kindHints);
$rulesJson = Json::htmlEncode($dueRules);
$employeesJson = Json::htmlEncode($employeeMeta);
$startIndex = count($rows);

$this->registerJs(<<<JS
(function () {
    var hints = {$hintsJson};
    var dueRules = {$rulesJson};
    var employees = {$employeesJson};
    var nextIndex = {$startIndex};

    var body = document.getElementById('loan-item-body');
    var template = document.getElementById('loan-item-template');
    var totalCell = document.getElementById('loan-item-total');
    var emptyNote = document.getElementById('loan-item-empty');

    var money = function (value) {
        var n = parseFloat(String(value).replace(/,/g, ''));
        return isNaN(n) ? 0 : n;
    };
    var format = function (n) {
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // ปรับช่องกรอกให้ตรงกับรายการที่เลือก เช่น ค่าชดเชยพาหนะไม่มีช่องจำนวนคน
    // แต่มีช่องกิโลเมตร ส่วนค่าน้ำมันรถราชการเป็นยอดก้อนเดียวไม่มีทั้งสองช่อง
    var applyHint = function (row) {
        var kindId = row.querySelector('[data-role="kind"]').value;
        var hint = hints[kindId];
        var persons = row.querySelector('[data-role="persons"]');
        var units = row.querySelector('[data-role="units"]');
        if (!hint) {
            persons.disabled = false;
            units.disabled = false;
            persons.placeholder = '';
            units.placeholder = '';
            return;
        }
        persons.disabled = !hint.persons;
        units.disabled = !hint.units;
        persons.placeholder = hint.persons ? hint.personUnit : '—';
        units.placeholder = hint.units ? hint.unit : '—';
        if (!hint.persons) { persons.value = ''; }
        if (!hint.units) { units.value = ''; }
    };

    var recalcRow = function (row) {
        var rate = money(row.querySelector('[data-role="rate"]').value);
        var amount = row.querySelector('[data-role="amount"]');
        if (rate <= 0) { return; }
        var persons = money(row.querySelector('[data-role="persons"]').value) || 1;
        var units = money(row.querySelector('[data-role="units"]').value) || 1;
        amount.value = (rate * persons * units).toFixed(2);
    };

    var recalcTotal = function () {
        var total = 0;
        body.querySelectorAll('.loan-item-row').forEach(function (row) {
            total += money(row.querySelector('[data-role="amount"]').value);
        });
        totalCell.textContent = format(total);
        emptyNote.hidden = body.querySelectorAll('.loan-item-row').length > 0;
    };

    var addRow = function () {
        var html = template.innerHTML.replace(/__ROW__/g, nextIndex++);
        var wrapper = document.createElement('tbody');
        wrapper.innerHTML = html.trim();
        var row = wrapper.querySelector('tr');
        body.appendChild(row);
        applyHint(row);
        recalcTotal();
        row.querySelector('[data-role="kind"]').focus();
    };

    document.getElementById('loan-item-add').addEventListener('click', addRow);

    body.addEventListener('click', function (event) {
        var button = event.target.closest('[data-role="remove"]');
        if (!button) { return; }
        button.closest('tr').remove();
        recalcTotal();
    });

    body.addEventListener('change', function (event) {
        var row = event.target.closest('.loan-item-row');
        if (!row) { return; }
        if (event.target.dataset.role === 'kind') { applyHint(row); }
        recalcRow(row);
        recalcTotal();
    });

    body.addEventListener('input', function (event) {
        var row = event.target.closest('.loan-item-row');
        if (!row) { return; }
        if (['persons', 'units', 'rate'].indexOf(event.target.dataset.role) >= 0) { recalcRow(row); }
        recalcTotal();
    });

    body.querySelectorAll('.loan-item-row').forEach(applyHint);
    recalcTotal();

    // เติมชื่อและตำแหน่งให้เมื่อเลือกบุคลากร แต่ยังพิมพ์ทับได้
    // เพราะบางใบยืมผู้ยืมเป็นคนนอกทะเบียน หรือชื่อในสัญญาต่างจากในทะเบียน
    var empSelect = document.getElementById('loan-borrower-emp');
    if (empSelect) {
        empSelect.addEventListener('change', function () {
            var meta = employees[this.value];
            if (!meta) { return; }
            var nameField = document.getElementById('loan-borrower-name');
            var positionField = document.getElementById('loan-borrower-position');
            if (nameField && !nameField.value.trim()) { nameField.value = meta.name; }
            if (positionField && !positionField.value.trim()) { positionField.value = meta.position; }
        });
    }

    // แสดงกติกาวันครบกำหนดและคำนวณให้ดูล่วงหน้า ฝั่งเซิร์ฟเวอร์คำนวณซ้ำตอนบันทึกอยู่ดี
    var typeSelect = document.getElementById('loan-expense-type');
    var hint = document.getElementById('loan-due-hint');
    var dueField = document.getElementById('loan-due-at');
    var manualBox = document.getElementById('loan-due-manual');
    var anchors = { activity_end: 'loan-activity-end', received: 'loan-received-at' };

    var previewDue = function () {
        if (!typeSelect || !dueField) { return; }
        var rule = dueRules[typeSelect.value];
        if (!rule) {
            hint.textContent = 'ยังไม่ได้เลือกประเภทค่าใช้จ่าย';
            return;
        }
        if (manualBox && manualBox.checked) {
            hint.textContent = rule.text + ' (กำหนดเอง ระบบไม่คำนวณทับ)';
            dueField.readOnly = false;
            return;
        }
        var anchorField = document.getElementById(anchors[rule.basis] || '');
        var anchor = anchorField ? anchorField.value : '';
        if (!anchor) {
            hint.textContent = rule.text + ' — ยังไม่ได้กรอกวันดังกล่าว จึงยังไม่มีวันครบกำหนด';
            dueField.value = '';
            return;
        }
        var date = new Date(anchor + 'T00:00:00');
        date.setDate(date.getDate() + rule.days);
        dueField.value = date.toISOString().slice(0, 10);
        hint.textContent = rule.text;
    };

    [typeSelect, manualBox, document.getElementById('loan-activity-end'), document.getElementById('loan-received-at')]
        .forEach(function (el) { if (el) { el.addEventListener('change', previewDue); } });
})();
JS);
