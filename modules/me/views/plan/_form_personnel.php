<?php

use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\plan\components\PersonnelPlanTaxonomy;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */
/** @var app\modules\plan\models\PlanOrderItem[] $items */
/** @var int $lockDept */
/** @var string $lockDeptName */
/** @var array<int,string> $departmentOptions */
/** @var app\modules\plan\models\PlanOrderRevision|null $baselineRevision */
/** @var string|null $returnUrl */

$returnUrl = $returnUrl ?? null;

$categories = (new Query())
    ->select(['code', 'title'])
    ->from('categorise')
    ->where(['name' => 'plan_category', 'category_id' => 'PER'])
    ->orderBy(['sort' => SORT_ASC, 'code' => SORT_ASC])
    ->all();
$categoryOptions = [];
foreach ($categories as $category) {
    $categoryOptions[$category['code']] = $category['title'];
}

$expenseRows = (new Query())
    ->select(['code', 'category_id', 'title'])
    ->from('categorise')
    ->where(['name' => 'plan_item'])
    ->andWhere(['category_id' => array_keys($categoryOptions)])
    ->orderBy(['sort' => SORT_ASC, 'code' => SORT_ASC])
    ->all();
$expensesByCategory = [];
$expenseTypeIds = [];
foreach ($expenseRows as $expense) {
    $types = PersonnelPlanTaxonomy::employeeTypeIds((string) $expense['code']);
    $expensesByCategory[$expense['category_id']][] = [
        'id' => $expense['code'],
        'text' => $expense['title'],
        'type_ids' => $types,
        'all_types' => PersonnelPlanTaxonomy::appliesToAllEmployees((string) $expense['code']),
    ];
    $expenseTypeIds[$expense['code']] = $types;
}

$selectedCategory = (string) $model->plan_category_id;
$selectedExpenses = [];
foreach ($expensesByCategory[$selectedCategory] ?? [] as $expense) {
    $selectedExpenses[$expense['id']] = $expense['text'];
}

$employeeTypeNames = (new Query())
    ->select(['title', 'id'])
    ->from('employee_type')
    ->where(['active' => 1])
    ->indexBy('id')
    ->column();

// ตรวจว่าข้อมูลตั้งค่าที่หน้านี้ต้องใช้มีครบไหม (เครื่องที่ยังไม่ได้ตั้งค่าจะเห็น dropdown ว่างโดยไม่รู้สาเหตุ)
$setupIssues = [];
if (!$categoryOptions) {
    $setupIssues[] = [
        'text' => 'ยังไม่มี "รายการคำขอบุคลากร" (หมวดแผนใต้ประเภทรายจ่ายบุคลากร)',
        'url'  => ['/plan/plan-category/index'],
        'link' => 'ตั้งค่าหมวดแผน',
    ];
} elseif (!$expenseRows) {
    $setupIssues[] = [
        'text' => 'ยังไม่มี "ประเภทค่าใช้จ่าย" ใต้รายการคำขอบุคลากร',
        'url'  => ['/plan/plan-item/index'],
        'link' => 'ตั้งค่าประเภทค่าใช้จ่าย',
    ];
}
if (!$employeeTypeNames) {
    $setupIssues[] = [
        'text' => 'ยังไม่มีประเภทบุคลากรที่เปิดใช้งาน (ทะเบียนประเภทบุคลากร)',
        'url'  => null,
        'link' => null,
    ];
}
$canSetup = !Yii::$app->user->isGuest && (Yii::$app->user->can('plan') || Yii::$app->user->can('admin'));

$monthCols = [
    'month_10' => 'ต.ค.', 'month_11' => 'พ.ย.', 'month_12' => 'ธ.ค.',
    'month_1' => 'ม.ค.', 'month_2' => 'ก.พ.', 'month_3' => 'มี.ค.',
    'month_4' => 'เม.ย.', 'month_5' => 'พ.ค.', 'month_6' => 'มิ.ย.',
    'month_7' => 'ก.ค.', 'month_8' => 'ส.ค.', 'month_9' => 'ก.ย.',
];

$form = ActiveForm::begin(['id' => 'me-personnel-form']);
?>

<div class="card bg-body border shadow-sm">
    <div class="card-body p-3 p-lg-4">
        <?php if ($setupIssues): ?>
            <div class="alert alert-warning border mb-4" role="alert">
                <div class="fw-semibold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>ระบบยังตั้งค่าข้อมูลพื้นฐานของแผนบุคลากรไม่ครบ</div>
                <ul class="mb-2 ps-4 small">
                    <?php foreach ($setupIssues as $issue): ?>
                        <li>
                            <?= Html::encode($issue['text']) ?>
                            <?php if ($canSetup && $issue['url']): ?>
                                — <?= Html::a(Html::encode($issue['link']), $issue['url'], ['target' => '_blank']) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="small mb-0">
                    ตัวเลือกในฟอร์มด้านล่างจึงยังว่างอยู่ กรุณาแจ้งผู้ดูแลระบบแผนให้ตั้งค่าก่อน
                    (ถ้าเพิ่งอัปเดตระบบ ให้รัน <code>php yii migrate</code> บนเครื่องแม่ข่ายด้วย)
                </div>
            </div>
        <?php endif; ?>
        <?php if ($baselineRevision): ?>
            <div class="alert alert-info border mb-4" role="status">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                    <div>
                        <div class="fw-semibold"><i class="fa-solid fa-clock-rotate-left me-2"></i>กำลังปรับแผนครึ่งปี รอบที่ <?= (int) $baselineRevision->cycle_no ?></div>
                        <div class="small">ข้อมูลแผนก่อนปรับครบ 12 เดือนถูกเก็บไว้แล้ว</div>
                    </div>
                    <div class="d-flex gap-4 text-end">
                        <div><div class="small">ยอดก่อนปรับ</div><strong><?= number_format((float) $baselineRevision->order_price, 2) ?></strong></div>
                        <div><div class="small">ผลต่างปัจจุบัน</div><strong id="adjustment-difference">0.00</strong></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?= $form->field($model, 'plan_group_id')->hiddenInput()->label(false) ?>

        <div class="row g-3 mb-2">
            <div class="col-md-4">
                <?= $form->field($model, 'thai_year')->input('number', ['readonly' => true])->label('ปีงบประมาณ')->hint('ตามรอบทำแผนที่เปิด') ?>
            </div>
            <div class="col-md-8">
                <?= $form->field($model, 'department_id')->dropDownList($departmentOptions, ['id' => 'me-dept'])->label('หน่วยงาน')->hint('แสดงเฉพาะหน่วยงานที่คุณเป็นหัวหน้า') ?>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <?= $form->field($model, 'plan_category_id')->widget(Select2::class, [
                    'data' => $categoryOptions,
                    'options' => ['id' => 'personnel-category', 'placeholder' => 'เลือกรายการคำขอบุคลากร'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('รายการคำขอบุคลากร') ?>
            </div>
            <div class="col-lg-6">
                <?= $form->field($model, 'plan_item_id')->widget(Select2::class, [
                    'data' => $selectedExpenses,
                    'options' => ['id' => 'personnel-expense-type', 'placeholder' => 'เลือกประเภทค่าใช้จ่าย'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('ประเภทค่าใช้จ่าย') ?>
                <div class="form-text">ประเภทค่าใช้จ่ายเป็นเงื่อนไขกำหนดรายชื่อบุคลากร</div>
            </div>
            <div class="col-lg-6">
                <?= $form->field($model, 'plan_budget_type_id')->widget(Select2::class, [
                    'data' => $model->listBudgetType(),
                    'options' => ['placeholder' => 'เลือกแหล่งของเงิน'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('แหล่งของเงิน') ?>
            </div>
            <div class="col-lg-6">
                <?= $form->field($model, 'description')->textInput(['maxlength' => 255])->label('วัตถุประสงค์ (ถ้ามี)') ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'reference')->textarea(['rows' => 2, 'placeholder' => 'ระบุเอกสารหรือหลักฐานประกอบการพิจารณา'])->label('เอกสาร/ข้อมูลอ้างอิง') ?>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-3 align-items-end mb-4">
            <div class="col-lg-8">
                <label class="form-label" for="employee-type-filter">ประเภทบุคลากรที่ต้องการดึง</label>
                <?= Select2::widget([
                    'name' => 'employee_type_ids',
                    'data' => $employeeTypeNames,
                    'options' => [
                        'id' => 'employee-type-filter',
                        'multiple' => true,
                        'placeholder' => 'เลือกได้มากกว่า 1 ประเภท',
                    ],
                    'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
                ]) ?>
                <div class="form-text">ระบบเลือกค่าแนะนำให้ตามประเภทค่าใช้จ่าย และสามารถปรับก่อนแสดงรายชื่อได้</div>
            </div>
            <div class="col-lg-4 pb-lg-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="all-employee-types">
                    <label class="form-check-label" for="all-employee-types">บุคลากรทุกประเภทในหน่วยงาน</label>
                </div>
            </div>
        </div>

        <section aria-labelledby="personnel-list-heading">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div>
                    <h2 class="h6 mb-1" id="personnel-list-heading">รายชื่อบุคลากร</h2>
                    <p class="text-body-secondary small mb-0" id="pull-info" aria-live="polite">เลือกประเภทค่าใช้จ่าย แล้วแสดงรายชื่อที่ตรงกับเงื่อนไข</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <div class="form-check align-self-center me-2">
                        <input class="form-check-input" type="checkbox" id="pull-include-children">
                        <label class="form-check-label small" for="pull-include-children">รวมหน่วยงานย่อย</label>
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="btn-pull-emp">
                        <i class="fa-solid fa-users me-1" aria-hidden="true"></i> แสดงรายชื่อ
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="add-row">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> เพิ่มรายชื่อ
                    </button>
                </div>
            </div>

            <div class="row g-2 mb-3" aria-live="polite">
                <div class="col-12 col-md-4">
                    <div class="bg-body-tertiary border rounded-3 p-3 h-100">
                        <div class="small text-body-secondary">จำนวนบุคลากร</div>
                        <div class="fw-semibold"><span id="row-count">0</span> คน</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="bg-body-tertiary border rounded-3 p-3 h-100">
                        <div class="small text-body-secondary">วงเงินรวมทั้งปี</div>
                        <div class="fw-semibold font-monospace"><span id="grand-total">0.00</span> บาท</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="bg-body-tertiary border rounded-3 p-3 h-100">
                        <div class="small text-body-secondary">เฉลี่ยต่อเดือน</div>
                        <div class="fw-semibold font-monospace"><span id="monthly-total">0.00</span> บาท</div>
                    </div>
                </div>
            </div>

            <div class="personnel-table-wrap border rounded-3">
                <table class="table table-sm align-middle mb-0" id="emp-table">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th scope="col">ชื่อ-สกุล</th>
                            <th scope="col">ตำแหน่ง</th>
                            <th scope="col">ประเภท</th>
                            <th scope="col" class="text-end">วงเงินประมาณการทั้งปี</th>
                            <th scope="col" class="text-end">เฉลี่ยต่อเดือน</th>
                            <th scope="col" class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $i => $item):
                        $data = is_array($item->data_json) ? $item->data_json : (json_decode((string) $item->data_json, true) ?: []);
                        $legacyAnnual = (float) $item->total_price;
                        if ($legacyAnnual <= 0) {
                            $legacyAnnual = (float) ($data['rate'] ?? 0) * (float) ($data['days'] ?? 1) * (int) ($item->qty ?: 12);
                        }
                        $annualBudget = (float) ($data['annual_budget'] ?? $legacyAnnual);
                        $typeId = (string) ($data['employee_type_id'] ?? '');
                        $typeName = (string) ($data['type_name'] ?? ($employeeTypeNames[$typeId] ?? ''));
                    ?>
                        <tr class="personnel-row">
                            <td data-label="ชื่อ-สกุล">
                                <input type="text" name="items[<?= $i ?>][item_name]" value="<?= Html::encode($item->item_name) ?>" class="form-control form-control-sm person-name" required>
                                <input type="hidden" name="items[<?= $i ?>][emp_id]" value="<?= Html::encode((string) ($data['emp_id'] ?? $item->item_id)) ?>">
                                <input type="hidden" name="items[<?= $i ?>][type_id]" value="<?= Html::encode($typeId) ?>">
                                <input type="hidden" name="items[<?= $i ?>][type_name]" value="<?= Html::encode($typeName) ?>">
                            </td>
                            <td data-label="ตำแหน่ง"><input type="text" name="items[<?= $i ?>][position]" value="<?= Html::encode((string) ($data['position'] ?? $item->title)) ?>" class="form-control form-control-sm"></td>
                            <td data-label="ประเภท" class="type-name text-body-secondary"><?= Html::encode($typeName ?: 'กรอกเอง') ?></td>
                            <td data-label="วงเงินทั้งปี"><input type="text" inputmode="decimal" name="items[<?= $i ?>][annual_budget]" value="<?= number_format($annualBudget, 2) ?>" class="form-control form-control-sm text-end annual-budget"></td>
                            <td data-label="เฉลี่ยต่อเดือน" class="text-end font-monospace monthly-average"><?= number_format($annualBudget / 12, 2) ?></td>
                            <td data-label="จัดการ" class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" aria-label="ลบบุคลากรรายนี้"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-center text-body-secondary p-4<?= $items ? ' d-none' : '' ?>" id="personnel-empty">
                    ยังไม่มีรายชื่อ เลือกประเภทค่าใช้จ่ายแล้วกด “แสดงรายชื่อ” หรือเพิ่มรายชื่อเอง
                </div>
            </div>
        </section>

        <hr class="my-4">

        <section aria-labelledby="monthly-heading">
            <div class="mb-3">
                <h2 class="h6 mb-1" id="monthly-heading">การจัดสรรงบประมาณรายเดือน</h2>
                <p class="text-body-secondary small mb-0">ระบบเฉลี่ยวงเงินทั้งปีเป็น 12 เดือนอัตโนมัติ และปรับเศษทศนิยมในเดือนกันยายน</p>
            </div>
            <div class="row g-2">
                <?php foreach ($monthCols as $attr => $label): ?>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label" for="<?= Html::getInputId($model, $attr) ?>-display"><?= Html::encode($label) ?></label>
                        <?= Html::activeHiddenInput($model, $attr) ?>
                        <?= Html::textInput(null, number_format((float) $model->$attr, 2), [
                            'id' => Html::getInputId($model, $attr) . '-display',
                            'class' => 'form-control month-input text-end',
                            'readonly' => true,
                            'aria-label' => $label . ' จำนวนเงิน',
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?= $form->field($model, 'order_price')->hiddenInput()->label(false) ?>
        </section>

        <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
            <?= Html::a('ยกเลิก', $returnUrl ?: ['index', 'thai_year' => $model->thai_year], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i> บันทึกแผน', ['class' => 'btn btn-primary', 'id' => 'save-personnel-plan']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerCss(<<<CSS
.personnel-table-wrap { overflow: hidden; }
#emp-table th, #emp-table td { padding: .65rem .75rem; vertical-align: middle; }
#emp-table th { font-size: .8rem; font-weight: 600; color: var(--bs-secondary-color); white-space: nowrap; }
#emp-table .annual-budget { min-width: 9rem; font-variant-numeric: tabular-nums; }
#emp-table .monthly-average, .font-monospace { font-variant-numeric: tabular-nums; }
@media (max-width: 991.98px) {
    .personnel-table-wrap { border: 0 !important; overflow: visible; }
    #emp-table, #emp-table tbody, #emp-table tr, #emp-table td { display: block; width: 100%; }
    #emp-table thead { display: none; }
    #emp-table tr { border: var(--bs-border-width) solid var(--bs-border-color); border-radius: var(--bs-border-radius-lg); margin-bottom: .75rem; padding: .75rem; background: var(--bs-body-bg); }
    #emp-table td { border: 0; padding: .35rem 0; display: grid; grid-template-columns: minmax(7rem, 35%) 1fr; gap: .75rem; align-items: center; text-align: left !important; }
    #emp-table td::before { content: attr(data-label); color: var(--bs-secondary-color); font-size: .8rem; }
    #emp-table td[data-label="จัดการ"] { display: flex; justify-content: flex-end; }
    #emp-table td[data-label="จัดการ"]::before { display: none; }
    #emp-table .remove-row { min-width: 44px; min-height: 44px; }
}
CSS);

$pullUrl = Url::to(['pull-employees']);
$expensesJson = Json::htmlEncode($expensesByCategory);
$typeNamesJson = Json::htmlEncode($employeeTypeNames);
$baselineTotal = $baselineRevision ? (float) $baselineRevision->order_price : 'null';
$js = <<<JS
(function () {
    var expensesByCategory = $expensesJson;
    var employeeTypeNames = $typeNamesJson;
    var rowIndex = $('#emp-table tbody tr').length;
    var baselineTotal = $baselineTotal;

    function money(value) {
        return (Math.round((Number(value) || 0) * 100) / 100).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function numericValue(value) {
        return Number(String(value == null ? '' : value).replace(/,/g, '')) || 0;
    }

    function escapeAttribute(value) {
        return $('<div>').text(value || '').html().replace(/"/g, '&quot;');
    }

    function selectedExpense() {
        var category = $('#personnel-category').val();
        var id = $('#personnel-expense-type').val();
        var list = expensesByCategory[category] || [];
        return list.find(function (item) { return String(item.id) === String(id); }) || null;
    }

    function allocate(total) {
        var cents = Math.max(0, Math.round(total * 100));
        var base = Math.floor(cents / 12);
        var values = {};
        for (var month = 1; month <= 12; month++) values[month] = base / 100;
        values[9] = (cents - (base * 11)) / 100;
        return values;
    }

    function recalc() {
        var total = 0;
        $('#emp-table tbody tr').each(function () {
            var annual = Math.max(0, numericValue($(this).find('.annual-budget').val()));
            $(this).find('.monthly-average').text(money(annual / 12));
            total += annual;
        });
        total = Math.round(total * 100) / 100;
        var months = allocate(total);
        Object.keys(months).forEach(function (month) {
            $('#planorder-month_' + month).val(months[month].toFixed(2));
            $('#planorder-month_' + month + '-display').val(money(months[month]));
        });
        $('#planorder-order_price').val(total.toFixed(2));
        $('#row-count').text($('#emp-table tbody tr').length);
        $('#grand-total').text(money(total));
        $('#monthly-total').text(money(total / 12));
        if (baselineTotal !== null) {
            var difference = total - baselineTotal;
            $('#adjustment-difference').text((difference > 0 ? '+' : '') + money(difference));
        }
        $('#personnel-empty').toggleClass('d-none', $('#emp-table tbody tr').length > 0);
        $('#save-personnel-plan').prop('disabled', $('#emp-table tbody tr').length === 0);
    }

    function rowHtml(item) {
        var index = rowIndex++;
        var name = escapeAttribute(item.name);
        var position = escapeAttribute(item.position);
        var typeName = escapeAttribute(item.type_name || 'กรอกเอง');
        var annual = Number(item.annual_budget) || 0;
        return '<tr class="personnel-row">' +
            '<td data-label="ชื่อ-สกุล"><input type="text" name="items[' + index + '][item_name]" value="' + name + '" class="form-control form-control-sm person-name" required>' +
            '<input type="hidden" name="items[' + index + '][emp_id]" value="' + (item.emp_id || '') + '">' +
            '<input type="hidden" name="items[' + index + '][type_id]" value="' + (item.type_id || '') + '">' +
            '<input type="hidden" name="items[' + index + '][type_name]" value="' + typeName + '"></td>' +
            '<td data-label="ตำแหน่ง"><input type="text" name="items[' + index + '][position]" value="' + position + '" class="form-control form-control-sm"></td>' +
            '<td data-label="ประเภท" class="type-name text-body-secondary">' + typeName + '</td>' +
            '<td data-label="วงเงินทั้งปี"><input type="text" inputmode="decimal" name="items[' + index + '][annual_budget]" value="' + money(annual) + '" class="form-control form-control-sm text-end annual-budget"></td>' +
            '<td data-label="เฉลี่ยต่อเดือน" class="text-end font-monospace monthly-average">' + money(annual / 12) + '</td>' +
            '<td data-label="จัดการ" class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" aria-label="ลบบุคลากรรายนี้"><i class="fa-solid fa-trash" aria-hidden="true"></i></button></td></tr>';
    }

    $('#personnel-category').on('change', function () {
        var options = expensesByCategory[$(this).val()] || [];
        var expense = $('#personnel-expense-type');
        expense.empty().append(new Option('', '', false, false));
        options.forEach(function (item) { expense.append(new Option(item.text, item.id, false, false)); });
        expense.val(null).trigger('change');
        $('#pull-info').text(options.length ? 'เลือกประเภทค่าใช้จ่ายเพื่อกำหนดรายชื่อบุคลากร' : 'รายการคำขอนี้ยังไม่มีประเภทค่าใช้จ่ายที่ผูกกับทะเบียนบุคลากร');
    });

    $('#personnel-expense-type').on('change', function () {
        var expense = selectedExpense();
        var allTypes = !!(expense && expense.all_types);
        $('#all-employee-types').prop('checked', allTypes).trigger('change');
        $('#employee-type-filter').val(expense && !allTypes ? expense.type_ids.map(String) : []).trigger('change');
        $('#pull-info').text(expense ? 'พร้อมแสดงรายชื่อบุคลากรที่ตรงกับ “' + expense.text + '”' : 'เลือกประเภทค่าใช้จ่าย');
    });

    $('#all-employee-types').on('change', function () {
        var allTypes = $(this).is(':checked');
        $('#employee-type-filter').prop('disabled', allTypes);
        if (allTypes) $('#employee-type-filter').val([]).trigger('change');
    });

    $('#btn-pull-emp').on('click', function () {
        var expense = selectedExpense();
        if (!expense) {
            $('#pull-info').text('กรุณาเลือกประเภทค่าใช้จ่ายก่อนแสดงรายชื่อ');
            return;
        }
        var button = $(this).prop('disabled', true);
        $('#pull-info').text('กำลังค้นหารายชื่อบุคลากร...');
        $.post('$pullUrl', {
            department_id: $('#me-dept').val(),
            plan_item_id: expense.id,
            employee_type_ids: $('#employee-type-filter').val() || [],
            all_employee_types: $('#all-employee-types').is(':checked') ? 1 : 0,
            include_children: $('#pull-include-children').is(':checked') ? 1 : 0
        }, function (response) {
            if (response.status !== 'success') {
                $('#pull-info').text(response.message || 'ไม่สามารถแสดงรายชื่อได้');
                return;
            }
            var existing = {};
            $('#emp-table tbody input[name*="[emp_id]"]').each(function () {
                if (this.value) existing[String(this.value)] = true;
            });
            var added = 0;
            var skipped = 0;
            response.items.forEach(function (item) {
                if (existing[String(item.emp_id)]) {
                    skipped++;
                    return;
                }
                $('#emp-table tbody').append(rowHtml(item));
                existing[String(item.emp_id)] = true;
                added++;
            });
            var scope = response.child_count > 0 ? ' รวมหน่วยงานย่อย ' + response.child_count + ' หน่วยงาน' : '';
            var duplicateText = skipped ? ' ข้ามรายชื่อที่มีอยู่แล้ว ' + skipped + ' คน' : '';
            $('#pull-info').text(response.items.length ? 'เพิ่มรายชื่อ ' + added + ' คน' + scope + duplicateText : 'ไม่พบบุคลากรที่ตรงกับประเภทค่าใช้จ่ายนี้');
            recalc();
        }, 'json').fail(function () {
            $('#pull-info').text('เชื่อมต่อระบบไม่ได้ กรุณาลองใหม่');
        }).always(function () { button.prop('disabled', false); });
    });

    $('#add-row').on('click', function () {
        var expense = selectedExpense();
        var typeId = expense && expense.type_ids.length === 1 ? expense.type_ids[0] : '';
        $('#emp-table tbody').append(rowHtml({type_id: typeId, type_name: employeeTypeNames[typeId] || 'กรอกเอง'}));
        recalc();
        $('#emp-table tbody tr:last .person-name').trigger('focus');
    });

    $('#emp-table').on('input', '.annual-budget', recalc);
    $('#emp-table').on('focus', '.annual-budget', function () {
        $(this).val(String($(this).val()).replace(/,/g, ''));
    });
    $('#emp-table').on('blur', '.annual-budget', function () {
        $(this).val(money(Math.max(0, numericValue($(this).val()))));
        recalc();
    });
    $('#emp-table').on('click', '.remove-row', function () { $(this).closest('tr').remove(); recalc(); });
    $('#me-personnel-form').on('submit', function () {
        $(this).find('.annual-budget').each(function () {
            $(this).val(Math.max(0, numericValue($(this).val())).toFixed(2));
        });
    });
    recalc();
})();
JS;
$this->registerJs($js);
?>
