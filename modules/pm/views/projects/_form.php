<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use app\modules\pm\models\Projects;
use app\modules\pm\models\ProjectResponsible;

app\assets\RichTextAsset::register($this);
app\assets\FormGuardAsset::register($this);
/** ตัวช่วยสร้าง option ของ textarea ที่เปิดแถบจัดรูปแบบข้อความ */
$rich = fn(string $label, int $rows = 3) => ['rows' => $rows, 'data-richtext' => '1', 'data-rte-label' => $label];

// หน่วยงานจากทะเบียนกลางของปีนี้ — จัดกลุ่มตามประเภท (หน่วยงาน/ทีมประสาน) และเยื้องตามผัง
$ouGroups = \app\modules\settings\models\OrgUnit::groupedForSelect((int) $model->thai_year);

// กลยุทธ์ทั้งหมด จัดกลุ่มตามตัวชี้วัดที่สังกัด เพื่อให้เลือกถูกตัวเมื่อชื่อกลยุทธ์คล้ายกัน
$tacticGroups = [];
foreach (\app\modules\pm\models\StrategyTactic::find()->with('indicator', 'goal')->orderBy(['indicator_id' => SORT_ASC, 'sort_order' => SORT_ASC])->all() as $tactic) {
    $indicator = $tactic->indicator;
    $group = $indicator ? trim($indicator->code . ' ' . $indicator->name) : 'ยังไม่ผูกตัวชี้วัด';
    $tacticGroups[$group][$tactic->id] = $tactic->label();
}

/** @var yii\web\View $this */
/** @var Projects $model */
/** @var app\modules\pm\models\ProjectObjective[] $objectives */
/** @var app\modules\pm\models\ProjectIndicator[] $indicators */
/** @var ProjectResponsible[] $responsibles */

$roleList = ProjectResponsible::roleList();

// บุคลากรที่ยังปฏิบัติงาน + คนในแถวเดิมที่พ้นจากการปฏิบัติงานไปแล้ว (โครงการปีเก่าต้องคงค่าไว้)
$people = ProjectResponsible::activeEmployees();
$empOptions = ProjectResponsible::employeeOptions($responsibles, $people);

$form = ActiveForm::begin(['id' => 'project-form']);
?>

<?php if ($model->hasErrors()): ?>
    <div class="alert alert-danger"><?= Html::errorSummary($model) ?></div>
<?php endif; ?>

<!-- ข้อมูลทั่วไป -->
<div class="card mb-3">
    <div class="card-header fw-semibold"><i class="fa-solid fa-circle-info me-1"></i> ข้อมูลทั่วไป</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'ชื่อโครงการ']) ?></div>
            <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เว้นว่าง = ออกอัตโนมัติ'])->hint('เว้นว่างไว้ ระบบจะออกรหัสให้ตามรูปแบบในหน้าตั้งค่า') ?></div>
        </div>
        <div class="row">
            <div class="col-md-3"><?= $form->field($model, 'work_type')->dropDownList(Projects::workTypeList())->hint('โครงการใช้งบประมาณ · แผนงาน/กิจกรรมอาจไม่ใช้งบ') ?></div>
            <div class="col-md-3"><?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2600]) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'org_unit_id')->widget(Select2::class, [
                'data' => $ouGroups,
                'options' => ['placeholder' => '-- เลือกหน่วยงาน --'],
                'pluginOptions' => ['allowClear' => false],
            ])->label('หน่วยงาน/ทีมเจ้าของโครงการ')->hint('เลือกจากทะเบียนหน่วยงาน (โครงสร้าง/ทีมประสาน/นอกผัง)') ?></div>
            <div class="col-md-3"><?= $form->field($model, 'status')->dropDownList(Projects::statusList()) ?></div>
        </div>
        <div class="row">
            <div class="col-md-12"><?= $form->field($model, 'tactic_id')->widget(Select2::class, [
                'data' => $tacticGroups,
                'options' => ['placeholder' => '-- ไม่ผูก = โครงการนอกแผนยุทธศาสตร์ --'],
                'pluginOptions' => ['allowClear' => true],
            ])->label('กลยุทธ์ที่รองรับ')->hint('ผูกกลยุทธ์ = โครงการในแผนยุทธศาสตร์ · เว้นว่าง = โครงการนอกแผนยุทธศาสตร์') ?></div>
        </div>
    </div>
</div>

<?php /* แผนงาน/กิจกรรมกรอกน้อยข้อกว่าโครงการ — ข้อที่เป็นเรื่องของโครงการซ่อนไว้ก่อน เปิดกรอกได้ */ ?>
<?php $isActivity = $model->isActivity(); ?>
<?php if ($isActivity): ?>
<div class="mb-3">
    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target=".pm-extra" aria-expanded="false">
        <i class="fa-solid fa-plus me-1"></i> แสดงข้อมูลเพิ่มเติม (หลักการและเหตุผล · เป้าหมาย/ตัวชี้วัด · งบประมาณ)
    </button>
</div>
<?php endif; ?>

<!-- 1. หลักการและเหตุผล -->
<div class="<?= $isActivity ? 'collapse pm-extra' : '' ?>">
<div class="card mb-3">
    <div class="card-header fw-semibold">1. หลักการและเหตุผล</div>
    <div class="card-body">
        <?= $form->field($model, 'rationale')->textarea($rich('หลักการและเหตุผล', 4))->label(false) ?>
    </div>
</div>
</div>

<!-- 2. วัตถุประสงค์ -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">2. วัตถุประสงค์</span>
        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="objectives"><i class="fa-solid fa-plus"></i> เพิ่ม</button>
    </div>
    <div class="card-body">
        <div data-rows="objectives">
            <?php foreach ($objectives as $i => $obj): ?>
                <div class="input-group mb-2" data-row>
                    <span class="input-group-text" data-row-index><?= $i + 1 ?>.</span>
                    <?= Html::textarea("Objectives[$i][detail]", $obj->detail, ['class' => 'form-control', 'rows' => 1, 'placeholder' => 'เพื่อ...']) ?>
                    <button type="button" class="btn btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- 3. เป้าหมาย/ตัวชี้วัด -->
<div class="<?= $isActivity ? 'collapse pm-extra' : '' ?>">
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">3. เป้าหมาย/ตัวชี้วัดผลสำเร็จ (สอดคล้องกับวัตถุประสงค์)</span>
        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="indicators"><i class="fa-solid fa-plus"></i> เพิ่ม</button>
    </div>
    <div class="card-body">
        <div data-rows="indicators">
            <?php foreach ($indicators as $i => $ind): ?>
                <div class="input-group mb-2" data-row>
                    <span class="input-group-text" data-row-index><?= $i + 1 ?>.</span>
                    <?= Html::textarea("Indicators[$i][detail]", $ind->detail, ['class' => 'form-control', 'rows' => 1, 'placeholder' => 'ตัวชี้วัด']) ?>
                    <?= Html::input('number', "Indicators[$i][target_percent]", $ind->target_percent, ['class' => 'form-control', 'style' => 'max-width:110px', 'step' => '0.01', 'min' => 0, 'max' => 100, 'placeholder' => 'ร้อยละ']) ?>
                    <span class="input-group-text">%</span>
                    <button type="button" class="btn btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<!-- 4. กลุ่มเป้าหมาย / 5. วิธีดำเนินการ -->
<div class="card mb-3">
    <div class="card-body">
        <?= $form->field($model, 'target_group')->textarea($rich('กลุ่มเป้าหมาย', 2)) ?>
        <?= $form->field($model, 'method')->textarea($rich('วิธีดำเนินการ', 4)) ?>
    </div>
</div>

<!-- 6-8. ระยะเวลา / สถานที่ / วิทยากร -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'start_date')->input('date') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'end_date')->input('date') ?></div>
        </div>
        <?= $form->field($model, 'duration_text')->textInput(['maxlength' => true, 'placeholder' => 'เช่น ไตรมาส 2 ปีงบประมาณ 2569']) ?>
        <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'lecturer')->textarea($rich('วิทยากร', 2)) ?>
    </div>
</div>

<!-- 9-10. ประเมินผล / ผลที่คาดว่าจะได้รับ -->
<div class="card mb-3">
    <div class="card-body">
        <?= $form->field($model, 'evaluation')->textarea($rich('การประเมินผลโครงการ', 3)) ?>
        <?= $form->field($model, 'expected_result')->textarea($rich('ผลที่คาดว่าจะได้รับ', 3)) ?>
    </div>
</div>

<!-- 11. ผู้รับผิดชอบ -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">11. ผู้รับผิดชอบโครงการ</span>
        <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="responsibles"><i class="fa-solid fa-plus"></i> เพิ่ม</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:32px">#</th>
                        <th style="width:20%">ประเภท</th>
                        <th style="width:30%">ชื่อ-นามสกุล</th>
                        <th>ตำแหน่ง</th>
                        <th style="width:15%">เบอร์โทร</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody data-rows="responsibles">
                    <?php foreach ($responsibles as $i => $r): ?>
                        <tr data-row>
                            <td data-row-index><?= $i + 1 ?></td>
                            <td><?= Html::dropDownList("Responsibles[$i][role]", $r->role, $roleList, ['class' => 'form-select form-select-sm']) ?></td>
                            <td>
                                <?= Html::dropDownList("Responsibles[$i][emp_id]", $r->emp_id, $empOptions, [
                                    'class' => 'form-select form-select-sm', 'data-emp-picker' => '1', 'prompt' => 'ระบุเอง / บุคคลภายนอก',
                                ]) ?>
                                <?= Html::textInput("Responsibles[$i][fullname]", $r->fullname, [
                                    'class' => 'form-control form-control-sm mt-1' . ($r->emp_id ? ' d-none' : ''),
                                    'data-emp-field' => 'fullname', 'placeholder' => 'พิมพ์ชื่อ-นามสกุล',
                                ]) ?>
                            </td>
                            <td><?= Html::textInput("Responsibles[$i][position]", $r->position, ['class' => 'form-control form-control-sm', 'data-emp-field' => 'position']) ?></td>
                            <td><?= Html::textInput("Responsibles[$i][phone]", $r->phone, ['class' => 'form-control form-control-sm', 'data-emp-field' => 'phone']) ?></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 12. งบประมาณ -->
<div class="<?= $isActivity ? 'collapse pm-extra' : '' ?>">
<div class="card mb-3">
    <div class="card-header fw-semibold">12. งบประมาณ<?= $isActivity ? ' <span class="small text-muted fw-normal">(แผนงาน/กิจกรรมจะกรอกหรือไม่ก็ได้)</span>' : '' ?></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><?= $form->field($model, 'budget_total')->input('number', ['step' => '0.01', 'min' => 0]) ?></div>
            <div class="col-md-8"><?= $form->field($model, 'budget_source')->textInput(['maxlength' => true, 'placeholder' => 'เช่น เงินบำรุง / งบ สปสช. / เงินนอกงบประมาณ']) ?></div>
        </div>
        <?= $form->field($model, 'budget_detail')->textarea($rich('รายละเอียดงบประมาณ', 3) + ['placeholder' => 'แจกแจงรายการค่าใช้จ่าย']) ?>
        <div class="form-text">หมายเหตุ ค่าใช้จ่ายทุกรายการสามารถถัวเฉลี่ยจ่ายแทนกันได้</div>
    </div>
</div>
</div>

<div class="d-flex gap-2 mb-4">
    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก', ['class' => 'btn btn-success']) ?>
    <?= Html::a('ยกเลิก', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-light']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
// เทมเพลตแถวใหม่ (index จะถูกแทนที่ด้วย __INDEX__)
$tplObjective = str_replace("\n", '', '<div class="input-group mb-2" data-row>'
    . '<span class="input-group-text" data-row-index></span>'
    . '<textarea name="Objectives[__INDEX__][detail]" class="form-control" rows="1" placeholder="เพื่อ..."></textarea>'
    . '<button type="button" class="btn btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button>'
    . '</div>');

$tplIndicator = str_replace("\n", '', '<div class="input-group mb-2" data-row>'
    . '<span class="input-group-text" data-row-index></span>'
    . '<textarea name="Indicators[__INDEX__][detail]" class="form-control" rows="1" placeholder="ตัวชี้วัด"></textarea>'
    . '<input type="number" name="Indicators[__INDEX__][target_percent]" class="form-control" style="max-width:110px" step="0.01" min="0" max="100" placeholder="ร้อยละ">'
    . '<span class="input-group-text">%</span>'
    . '<button type="button" class="btn btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button>'
    . '</div>');

$roleOptions = '';
foreach ($roleList as $val => $label) {
    $roleOptions .= '<option value="' . Html::encode($val) . '">' . Html::encode($label) . '</option>';
}
$empOptionsHtml = '<option value="">ระบุเอง / บุคคลภายนอก</option>';
foreach ($empOptions as $val => $label) {
    $empOptionsHtml .= '<option value="' . Html::encode($val) . '">' . Html::encode($label) . '</option>';
}
$tplResponsible = str_replace("\n", '', '<tr data-row>'
    . '<td data-row-index></td>'
    . '<td><select name="Responsibles[__INDEX__][role]" class="form-select form-select-sm">' . $roleOptions . '</select></td>'
    . '<td><select name="Responsibles[__INDEX__][emp_id]" class="form-select form-select-sm" data-emp-picker="1">' . $empOptionsHtml . '</select>'
    . '<input type="text" name="Responsibles[__INDEX__][fullname]" class="form-control form-control-sm mt-1" data-emp-field="fullname" placeholder="พิมพ์ชื่อ-นามสกุล"></td>'
    . '<td><input type="text" name="Responsibles[__INDEX__][position]" class="form-control form-control-sm" data-emp-field="position"></td>'
    . '<td><input type="text" name="Responsibles[__INDEX__][phone]" class="form-control form-control-sm" data-emp-field="phone"></td>'
    . '<td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button></td>'
    . '</tr>');

$templates = \yii\helpers\Json::encode([
    'objectives' => $tplObjective,
    'indicators' => $tplIndicator,
    'responsibles' => $tplResponsible,
]);
$peopleJson = \yii\helpers\Json::encode($people);

$js = <<<JS
(function(){
    var templates = $templates;
    var counters = {};

    function reindex(name){
        var container = document.querySelector('[data-rows="'+name+'"]');
        if(!container) return;
        var rows = container.querySelectorAll('[data-row]');
        rows.forEach(function(row, idx){
            var badge = row.querySelector('[data-row-index]');
            if(badge){ badge.textContent = (name === 'responsibles') ? (idx+1) : (idx+1) + '.'; }
        });
    }

    document.querySelectorAll('[data-add-row]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var name = btn.getAttribute('data-add-row');
            var container = document.querySelector('[data-rows="'+name+'"]');
            if(!container) return;
            if(counters[name] === undefined){
                counters[name] = container.querySelectorAll('[data-row]').length;
            }
            var html = templates[name].replace(/__INDEX__/g, counters[name]);
            counters[name]++;
            if(name === 'responsibles'){
                var tmp = document.createElement('tbody');
                tmp.innerHTML = html;
                container.appendChild(tmp.firstElementChild);
            } else {
                var tmp = document.createElement('div');
                tmp.innerHTML = html;
                container.appendChild(tmp.firstElementChild);
            }
            reindex(name);
        });
    });

    document.addEventListener('click', function(e){
        var btn = e.target.closest('[data-remove-row]');
        if(!btn) return;
        var row = btn.closest('[data-row]');
        var container = row.closest('[data-rows]');
        var name = container ? container.getAttribute('data-rows') : null;
        row.remove();
        if(name) reindex(name);
    });

    // เลือกชื่อจากทะเบียนแล้วเติมตำแหน่ง/เบอร์โทรให้ ยังแก้ไขต่อได้เอง
    // ชื่อที่บันทึกเก็บเป็นข้อความของตัวเอง (hidden) เพื่อให้โครงการปีเก่าคงชื่อ ณ ตอนนั้นไว้
    // เลือก "ระบุเอง" จะเปิดช่องพิมพ์ชื่อ และไม่ล้างค่าที่กรอกไว้เดิม
    var people = $peopleJson;
    document.addEventListener('change', function(e){
        var picker = e.target.closest('[data-emp-picker]');
        if(!picker) return;
        var row = picker.closest('[data-row]');
        var nameInput = row.querySelector('[data-emp-field="fullname"]');
        var info = people[picker.value];

        if(!picker.value){
            if(nameInput) nameInput.classList.remove('d-none');
            return;
        }
        if(nameInput) nameInput.classList.add('d-none');
        if(!info){
            // คนที่พ้นจากการปฏิบัติงาน — คงชื่อเดิมที่บันทึกไว้ ไม่เขียนทับ
            return;
        }
        if(nameInput) nameInput.value = info.fullname || '';
        ['position','phone'].forEach(function(field){
            var input = row.querySelector('[data-emp-field="'+field+'"]');
            if(input) input.value = info[field] || '';
        });
    });
})();
JS;
$this->registerJs($js);
?>
