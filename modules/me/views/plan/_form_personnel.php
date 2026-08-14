<?php

use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\me\controllers\PlanController;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */
/** @var app\modules\plan\models\PlanOrderItem[] $items */
/** @var int $lockDept */
/** @var string $lockDeptName */

// รายการค่าใช้จ่ายบุคลากร (plan_item ใต้หมวด PER_*) จัดกลุ่มตามหมวดเพื่อให้เลือกง่าย
$itemRows = (new Query())
    ->select(['item_code' => 'i.code', 'item' => 'i.title', 'cat' => 'c.title'])
    ->from(['i' => 'categorise'])
    ->innerJoin(['c' => 'categorise'], "c.code = i.category_id AND c.name = 'plan_category'")
    ->where(['i.name' => 'plan_item'])
    ->andWhere(['like', 'i.category_id', 'PER%', false])
    ->orderBy(['i.category_id' => SORT_ASC, 'i.code' => SORT_ASC])
    ->all();
$itemGroups = [];
foreach ($itemRows as $r) {
    $itemGroups[$r['cat']][$r['item_code']] = $r['item'];
}

// ประเภทการจ้าง (ใช้เป็นตัวกรองตอนดึงรายชื่อ เช่น ค่าตอบแทนวิชาชีพเอาเฉพาะข้าราชการ)
$empTypes = (new Query())
    ->select(['id', 'title'])->from('employee_type')
    ->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
    ->all();

$suggested = PlanController::suggestedEmpTypes();
$dailyTypes = PlanController::DAILY_EMP_TYPES;
$defaultDays = PlanController::DEFAULT_WORK_DAYS;

$monthCols = [
    'month_10' => 'ต.ค.', 'month_11' => 'พ.ย.', 'month_12' => 'ธ.ค.',
    'month_1'  => 'ม.ค.', 'month_2'  => 'ก.พ.', 'month_3'  => 'มี.ค.',
    'month_4'  => 'เม.ย.', 'month_5'  => 'พ.ค.', 'month_6'  => 'มิ.ย.',
    'month_7'  => 'ก.ค.', 'month_8'  => 'ส.ค.', 'month_9'  => 'ก.ย.',
];

$form = ActiveForm::begin(['id' => 'me-personnel-form']);
?>

<div class="card">
    <div class="card-body">

        <?= $form->field($model, 'plan_group_id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'department_id')->hiddenInput(['id' => 'me-dept', 'value' => $lockDept])->label(false) ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'thai_year')->input('number', ['readonly' => true])->label('ปีงบประมาณ')->hint('ตามรอบทำแผนที่เปิด') ?>
            </div>
            <div class="col-md-9">
                <label class="form-label">หน่วยงาน</label>
                <div class="form-control-plaintext fw-semibold"><?= Html::encode($lockDeptName) ?></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'plan_item_id')->widget(Select2::class, [
                    'data' => $itemGroups,
                    'options' => ['id' => 'me-plan-item', 'placeholder' => '— เลือกรายการค่าใช้จ่าย —'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('รายการค่าใช้จ่าย <span class="text-danger">*</span>', ['encode' => false]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'plan_budget_type_id')->widget(Select2::class, [
                    'data' => $model->listBudgetType(),
                    'options' => ['placeholder' => 'เลือกแหล่งของเงิน'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('แหล่งของเงิน') ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'description')->textInput(['maxlength' => 255])->label('วัตถุประสงค์ (ถ้ามี)') ?>
                <?= $form->field($model, 'reference')->textarea(['rows' => 2, 'placeholder' => 'เอกสาร/หลักฐาน ประกอบการพิจารณา'])->label('เอกสาร/ข้อมูลอ้างอิง') ?>
            </div>
        </div>

        <!-- ดึงรายชื่อบุคลากร -->
        <div class="card bg-light border-0 mb-3">
            <div class="card-body py-3">
                <div class="fw-semibold mb-2"><i class="fa-solid fa-users me-1"></i> ดึงรายชื่อบุคลากรของหน่วยงาน</div>
                <div class="mb-2">
                    <label class="form-label small mb-1">ประเภทการจ้างที่ต้องการ (ไม่เลือก = ทุกประเภท)</label>
                    <div class="d-flex flex-wrap gap-3">
                        <?php foreach ($empTypes as $t): ?>
                            <div class="form-check">
                                <input class="form-check-input emp-type" type="checkbox" value="<?= (int) $t['id'] ?>" id="emp-type-<?= (int) $t['id'] ?>">
                                <label class="form-check-label small" for="emp-type-<?= (int) $t['id'] ?>"><?= Html::encode($t['title']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1">วันทำงาน/เดือน (รายวัน-รายคาบ)</label>
                        <input type="number" step="0.5" min="1" max="31" class="form-control form-control-sm" id="pull-days" value="<?= $defaultDays ?>" style="width:110px">
                    </div>
                    <div class="col-auto">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="pull-include-children">
                            <label class="form-check-label small" for="pull-include-children">รวมหน่วยงานย่อย</label>
                        </div>
                        <button type="button" class="btn btn-sm btn-info text-white" id="btn-pull-emp">
                            <i class="fa-solid fa-user-plus me-1"></i> ดึงรายชื่อ
                        </button>
                    </div>
                    <div class="col">
                        <small class="text-muted" id="pull-info">เลือกรายการค่าใช้จ่ายก่อน ระบบจะติ๊กประเภทการจ้างที่เกี่ยวข้องให้ แล้วปรับเพิ่ม/ลบรายชื่อได้เอง</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h6 class="mb-0">รายชื่อในแผน <span class="badge bg-secondary-subtle text-secondary-emphasis" id="row-count">0</span></h6>
            <div class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm" style="width:200px">
                    <span class="input-group-text">ปรับเพิ่ม %</span>
                    <input type="number" step="0.1" class="form-control" id="raise-pct" placeholder="0">
                    <button type="button" class="btn btn-outline-secondary" id="btn-raise">ปรับ</button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-clear-rows"><i class="fa-solid fa-eraser me-1"></i> ล้างรายชื่อ</button>
                <button type="button" class="btn btn-sm btn-primary" id="add-row"><i class="fa-solid fa-circle-plus me-1"></i> เพิ่มแถวเอง</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="emp-table">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:180px">ชื่อ-สกุล</th>
                        <th style="min-width:150px">ตำแหน่ง</th>
                        <th width="130">ประเภท</th>
                        <th width="120">อัตรา (บาท)</th>
                        <th width="90">วัน/เดือน</th>
                        <th width="90">เดือน</th>
                        <th width="130" class="text-end">รวม</th>
                        <th width="45"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                        <?php
                        $dj = is_array($item->data_json) ? $item->data_json : (json_decode((string) $item->data_json, true) ?: []);
                        $rate  = (float) ($dj['rate'] ?? $item->unit_price);
                        $days  = (float) ($dj['days'] ?? 1);
                        $months = (int) $item->qty;
                        $typeId = (string) ($dj['employee_type_id'] ?? '');
                        $empId  = (string) ($dj['emp_id'] ?? $item->item_id);
                        $pos    = (string) ($dj['position'] ?? $item->title);
                        ?>
                        <tr>
                            <td>
                                <input type="text" name="items[<?= $i ?>][item_name]" value="<?= Html::encode($item->item_name) ?>" class="form-control form-control-sm">
                                <input type="hidden" name="items[<?= $i ?>][emp_id]" value="<?= Html::encode($empId) ?>">
                                <input type="hidden" name="items[<?= $i ?>][type_id]" value="<?= Html::encode($typeId) ?>">
                            </td>
                            <td><input type="text" name="items[<?= $i ?>][position]" value="<?= Html::encode($pos) ?>" class="form-control form-control-sm"></td>
                            <td class="small text-body-secondary type-name"><?= Html::encode($dj['type_name'] ?? '') ?></td>
                            <td><input type="number" step="0.01" name="items[<?= $i ?>][rate]" value="<?= $rate ?>" class="form-control form-control-sm rate"></td>
                            <td><input type="number" step="0.5" name="items[<?= $i ?>][days]" value="<?= $days ?>" class="form-control form-control-sm days"></td>
                            <td><input type="number" step="1" min="0" max="12" name="items[<?= $i ?>][qty]" value="<?= $months ?>" class="form-control form-control-sm months"></td>
                            <td class="text-end line-total"><?= number_format($rate * $days * $months, 2) ?></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fa-solid fa-xmark"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="6" class="text-end">รวมทั้งสิ้น</th>
                        <th class="text-end" id="grand-total">0.00</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">แผนการใช้จ่ายรายเดือน</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-spread">
                <i class="fa-solid fa-arrows-split-up-and-left me-1"></i> เฉลี่ยเท่ากัน 12 เดือน
            </button>
        </div>
        <div class="row g-2">
            <?php foreach ($monthCols as $attr => $label): ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <?= $form->field($model, $attr)->input('number', ['step' => '0.01', 'class' => 'form-control month-input'])->label($label) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'order_price')->input('number', ['step' => '0.01', 'readonly' => true])->label('รวมเป็นจำนวนเงินทั้งสิ้น (บาท)')->hint('คำนวณจากรายชื่อในแผน') ?>
            </div>
        </div>

        <div class="d-flex gap-2 mt-2">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก', ['class' => 'btn btn-success']) ?>
            <?= Html::a('ยกเลิก', ['index', 'thai_year' => $model->thai_year], ['class' => 'btn btn-light']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$pullUrl    = \yii\helpers\Url::to(['pull-employees']);
$suggestJs  = Json::encode($suggested);
$dailyJs    = Json::encode(array_map('strval', $dailyTypes));
$js = <<<JS
(function(){
    var suggested = $suggestJs;
    var dailyTypes = $dailyJs;
    var rowIndex = $('#emp-table tbody tr').length;

    function fmt(n){ return (Math.round(n * 100) / 100).toFixed(2); }

    function recalc(){
        var total = 0;
        $('#emp-table tbody tr').each(function(){
            var tr = $(this);
            var rate = parseFloat(tr.find('.rate').val()) || 0;
            var days = parseFloat(tr.find('.days').val()) || 0;
            var months = parseFloat(tr.find('.months').val()) || 0;
            var line = rate * (days > 0 ? days : 1) * months;
            tr.find('.line-total').text(fmt(line));
            total += line;
        });
        $('#grand-total').text(fmt(total));
        $('#row-count').text($('#emp-table tbody tr').length);
        $('#planorder-order_price').val(fmt(total));
        return total;
    }

    function rowHtml(it){
        var i = rowIndex++;
        return '<tr>' +
            '<td><input type="text" name="items[' + i + '][item_name]" class="form-control form-control-sm" value="' + $('<div>').text(it.name || '').html() + '">' +
                '<input type="hidden" name="items[' + i + '][emp_id]" value="' + (it.emp_id || '') + '">' +
                '<input type="hidden" name="items[' + i + '][type_id]" value="' + (it.type_id || '') + '"></td>' +
            '<td><input type="text" name="items[' + i + '][position]" class="form-control form-control-sm" value="' + $('<div>').text(it.position || '').html() + '"></td>' +
            '<td class="small text-body-secondary type-name">' + $('<div>').text(it.type_name || '').html() +
                (it.note ? '<div class="text-warning-emphasis">' + $('<div>').text(it.note).html() + '</div>' : '') + '</td>' +
            '<td><input type="number" step="0.01" name="items[' + i + '][rate]" class="form-control form-control-sm rate" value="' + (it.rate || 0) + '"></td>' +
            '<td><input type="number" step="0.5" name="items[' + i + '][days]" class="form-control form-control-sm days" value="' + (it.days || 1) + '"></td>' +
            '<td><input type="number" step="1" min="0" max="12" name="items[' + i + '][qty]" class="form-control form-control-sm months" value="' + (it.months === undefined ? 12 : it.months) + '"></td>' +
            '<td class="text-end line-total">0.00</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fa-solid fa-xmark"></i></button></td>' +
        '</tr>';
    }

    // เลือกรายการค่าใช้จ่าย -> ติ๊กประเภทการจ้างที่เกี่ยวข้องให้อัตโนมัติ (ปรับเองต่อได้)
    $('#me-plan-item').on('change', function(){
        var types = suggested[$(this).val()] || [];
        if (!types.length) return;
        $('.emp-type').prop('checked', false);
        types.forEach(function(t){ $('#emp-type-' + t).prop('checked', true); });
    });

    $('#btn-pull-emp').on('click', function(){
        var types = $('.emp-type:checked').map(function(){ return this.value; }).get();
        var days  = parseFloat($('#pull-days').val()) || $defaultDays;
        $('#pull-info').text('กำลังดึงข้อมูล...');
        $.post('$pullUrl', {
            department_id: $('#me-dept').val(),
            thai_year: $('#planorder-thai_year').val(),
            employee_type_ids: types,
            include_children: $('#pull-include-children').is(':checked') ? 1 : 0,
            days_per_month: days
        }, function(res){
            if (res.status !== 'success') { $('#pull-info').text(res.message || 'เกิดข้อผิดพลาด'); return; }
            if (!res.items.length) { $('#pull-info').text('ไม่พบบุคลากรตามเงื่อนไขที่เลือก'); return; }
            var exists = {};
            $('#emp-table tbody input[name*="[emp_id]"]').each(function(){ if (this.value) exists[this.value] = true; });
            var added = 0, skipped = 0;
            res.items.forEach(function(it){
                if (exists[String(it.emp_id)]) { skipped++; return; }
                $('#emp-table tbody').append(rowHtml(it));
                added++;
            });
            recalc();
            var scopeTxt = res.child_count > 0 ? (' (รวม ' + res.child_count + ' หน่วยย่อย)') : '';
            var skipTxt = skipped > 0 ? (' ข้ามที่มีอยู่แล้ว ' + skipped + ' คน') : '';
            $('#pull-info').html('<i class="fa-solid fa-check text-success me-1"></i>เพิ่ม ' + added + ' คน' + scopeTxt + skipTxt + ' — แก้อัตรา/จำนวนเดือน หรือลบรายชื่อที่ไม่เกี่ยวข้องได้');
        }, 'json').fail(function(){ $('#pull-info').text('เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'); });
    });

    $('#add-row').on('click', function(){
        $('#emp-table tbody').append(rowHtml({name: '', position: '', type_name: 'กรอกเอง', rate: 0, days: 1, months: 12}));
        recalc();
    });

    $('#btn-clear-rows').on('click', function(){
        if (!$('#emp-table tbody tr').length) return;
        $('#emp-table tbody').empty();
        recalc();
    });

    $('#btn-raise').on('click', function(){
        var pct = parseFloat($('#raise-pct').val()) || 0;
        if (!pct) return;
        $('#emp-table tbody .rate').each(function(){
            var v = parseFloat(this.value) || 0;
            this.value = fmt(v * (1 + pct / 100));
        });
        recalc();
    });

    $('#emp-table').on('input', '.rate, .days, .months', recalc);
    $('#emp-table').on('click', '.remove-row', function(){ $(this).closest('tr').remove(); recalc(); });

    $('#btn-spread').on('click', function(){
        var total = parseFloat($('#planorder-order_price').val()) || recalc();
        if (total <= 0) return;
        var per = Math.floor((total / 12) * 100) / 100, acc = 0;
        var order = [10,11,12,1,2,3,4,5,6,7,8,9];
        order.forEach(function(m, idx){
            var val = (idx < 11) ? per : (total - acc);
            $('#planorder-month_' + m).val(fmt(val));
            if (idx < 11) acc += per;
        });
    });

    recalc();
})();
JS;
$this->registerJs($js);
?>
