<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use app\modules\pm\models\Projects;
use app\modules\pm\models\ProjectResponsible;
use app\modules\hr\models\Organization;

app\assets\RichTextAsset::register($this);
/** ตัวช่วยสร้าง option ของ textarea ที่เปิดแถบจัดรูปแบบข้อความ */
$rich = fn(string $label, int $rows = 3) => ['rows' => $rows, 'data-richtext' => '1', 'data-rte-label' => $label];

/** @var yii\web\View $this */
/** @var Projects $model */
/** @var app\modules\pm\models\ProjectObjective[] $objectives */
/** @var app\modules\pm\models\ProjectIndicator[] $indicators */
/** @var ProjectResponsible[] $responsibles */

$roleList = ProjectResponsible::roleList();

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
            <div class="col-md-3"><?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2600]) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'department_id')->widget(\kartik\tree\TreeViewInput::class, [
                'query' => Organization::find()->where(['tb_name' => 'diagram'])->addOrderBy('root, lft'),
                'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                'fontAwesome' => true,
                'asDropdown' => true,
                'multiple' => false,
                'options' => ['placeholder' => 'เลือกหน่วยงานจากผังองค์กร'],
            ])->label('หน่วยงานเจ้าของโครงการ') ?></div>
            <div class="col-md-3"><?= $form->field($model, 'status')->dropDownList(Projects::statusList()) ?></div>
        </div>
    </div>
</div>

<!-- 1. หลักการและเหตุผล -->
<div class="card mb-3">
    <div class="card-header fw-semibold">1. หลักการและเหตุผล</div>
    <div class="card-body">
        <?= $form->field($model, 'rationale')->textarea($rich('หลักการและเหตุผล', 4))->label(false) ?>
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
                        <th style="width:22%">ประเภท</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ตำแหน่ง</th>
                        <th style="width:15%">เบอร์โทร</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody data-rows="responsibles">
                    <?php foreach ($responsibles as $i => $r): ?>
                        <tr data-row>
                            <td data-row-index><?= $i + 1 ?></td>
                            <td>
                                <?= Html::hiddenInput("Responsibles[$i][emp_id]", $r->emp_id) ?>
                                <?= Html::dropDownList("Responsibles[$i][role]", $r->role, $roleList, ['class' => 'form-select form-select-sm']) ?>
                            </td>
                            <td><?= Html::textInput("Responsibles[$i][fullname]", $r->fullname, ['class' => 'form-control form-control-sm']) ?></td>
                            <td><?= Html::textInput("Responsibles[$i][position]", $r->position, ['class' => 'form-control form-control-sm']) ?></td>
                            <td><?= Html::textInput("Responsibles[$i][phone]", $r->phone, ['class' => 'form-control form-control-sm']) ?></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 12. งบประมาณ -->
<div class="card mb-3">
    <div class="card-header fw-semibold">12. งบประมาณ</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><?= $form->field($model, 'budget_total')->input('number', ['step' => '0.01', 'min' => 0]) ?></div>
            <div class="col-md-8"><?= $form->field($model, 'budget_source')->textInput(['maxlength' => true, 'placeholder' => 'เช่น เงินบำรุง / งบ สปสช. / เงินนอกงบประมาณ']) ?></div>
        </div>
        <?= $form->field($model, 'budget_detail')->textarea($rich('รายละเอียดงบประมาณ', 3) + ['placeholder' => 'แจกแจงรายการค่าใช้จ่าย']) ?>
        <div class="form-text">หมายเหตุ ค่าใช้จ่ายทุกรายการสามารถถัวเฉลี่ยจ่ายแทนกันได้</div>
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
$tplResponsible = str_replace("\n", '', '<tr data-row>'
    . '<td data-row-index></td>'
    . '<td><input type="hidden" name="Responsibles[__INDEX__][emp_id]" value="">'
    . '<select name="Responsibles[__INDEX__][role]" class="form-select form-select-sm">' . $roleOptions . '</select></td>'
    . '<td><input type="text" name="Responsibles[__INDEX__][fullname]" class="form-control form-control-sm"></td>'
    . '<td><input type="text" name="Responsibles[__INDEX__][position]" class="form-control form-control-sm"></td>'
    . '<td><input type="text" name="Responsibles[__INDEX__][phone]" class="form-control form-control-sm"></td>'
    . '<td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row><i class="fa-solid fa-trash"></i></button></td>'
    . '</tr>');

$templates = \yii\helpers\Json::encode([
    'objectives' => $tplObjective,
    'indicators' => $tplIndicator,
    'responsibles' => $tplResponsible,
]);

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
})();
JS;
$this->registerJs($js);
?>
