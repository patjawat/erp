<?php

use yii\db\Query;
use yii\db\Expression;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */
/** @var app\modules\hr\models\Organization[] $orgs */

// ลำดับชั้น ประเภท -> หมวด -> รายการ (เลือกทีละขั้น)
$rows = (new Query())
    ->select([
        'item_code' => 'i.code', 'item' => 'i.title',
        'cat_code'  => 'c.code', 'cat'  => 'c.title',
        'type_code' => 't.code', 'type' => 't.title',
    ])
    ->from(['i' => 'categorise'])
    ->innerJoin(['c' => 'categorise'], "c.code = i.category_id AND c.name = 'plan_category'")
    ->innerJoin(['t' => 'categorise'], "t.code = c.category_id AND t.name = 'plan_type'")
    ->where(['i.name' => 'plan_item'])
    ->orderBy(new Expression("FIELD(t.code,'PER','OPS','INV','OTH'), c.code, i.code"))
    ->all();

$typeList   = [];  // type_code => type_title
$catsByType = [];  // type_code => [ [cat_code, cat_title], ... ]
$itemsByCat = [];  // cat_code  => [ [item_code, item_title], ... ]
$seenCat    = [];
$itemChain  = [];  // item_code => [type_code, cat_code]  (สำหรับ preselect ตอนแก้ไข)

foreach ($rows as $r) {
    $typeList[$r['type_code']] = $r['type'];
    if (empty($seenCat[$r['cat_code']])) {
        $catsByType[$r['type_code']][] = [$r['cat_code'], $r['cat']];
        $seenCat[$r['cat_code']] = true;
    }
    $itemsByCat[$r['cat_code']][] = [$r['item_code'], $r['item']];
    $itemChain[$r['item_code']] = [$r['type_code'], $r['cat_code']];
}

// ค่าเริ่มต้นตอนแก้ไข
$curItem = $model->plan_item_id;
$curType = $curItem && isset($itemChain[$curItem]) ? $itemChain[$curItem][0] : '';
$curCat  = $curItem && isset($itemChain[$curItem]) ? $itemChain[$curItem][1] : '';

$orgList = ArrayHelper::map($orgs, 'id', 'name');

$monthCols = [
    'month_10' => 'ต.ค.', 'month_11' => 'พ.ย.', 'month_12' => 'ธ.ค.',
    'month_1'  => 'ม.ค.', 'month_2'  => 'ก.พ.', 'month_3'  => 'มี.ค.',
    'month_4'  => 'เม.ย.', 'month_5'  => 'พ.ค.', 'month_6'  => 'มิ.ย.',
    'month_7'  => 'ก.ค.', 'month_8'  => 'ส.ค.', 'month_9'  => 'ก.ย.',
];

$form = ActiveForm::begin(['id' => 'me-plan-form']);
?>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2600, 'readonly' => true])->label('ปีงบประมาณ')->hint('ตามรอบทำแผนที่เปิด') ?>
            </div>
            <div class="col-md-9">
                <?php if (count($orgList) > 1): ?>
                    <?= $form->field($model, 'department_id')->widget(Select2::class, [
                        'data' => $orgList,
                        'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                        'pluginOptions' => ['allowClear' => false],
                    ])->label('หน่วยงาน') ?>
                <?php else: ?>
                    <?= $form->field($model, 'department_id')->hiddenInput()->label(false) ?>
                    <div class="mb-2">
                        <label class="form-label">หน่วยงาน</label>
                        <div class="form-control-plaintext fw-semibold"><?= Html::encode(reset($orgList)) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- เลือกรายการทีละขั้น: ประเภท -> หมวด -> รายการ -->
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">1. ประเภทรายจ่าย <span class="text-danger">*</span></label>
                <select id="me-plan-type" class="form-select">
                    <option value="">— เลือกประเภท —</option>
                    <?php foreach ($typeList as $code => $title): ?>
                        <option value="<?= Html::encode($code) ?>" <?= $code === $curType ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">2. หมวด <span class="text-danger">*</span></label>
                <select id="me-plan-category" class="form-select">
                    <option value="">— เลือกหมวด —</option>
                </select>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'plan_item_id')->dropDownList([], [
                    'id' => 'planorder-plan_item_id',
                    'prompt' => '— เลือกรายการ —',
                ])->label('3. รายการ <span class="text-danger">*</span>', ['encode' => false]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'description')->textInput(['maxlength' => 255])->label('วัตถุประสงค์ (ถ้ามี)') ?>
            </div>
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
                    <?= $form->field($model, $attr)->input('number', [
                        'step' => '0.01',
                        'class' => 'form-control month-input',
                    ])->label($label) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <hr>
        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'order_price')->input('number', [
                    'step' => '0.01',
                    'readonly' => true,
                ])->label('รวมทั้งสิ้น (บาท)') ?>
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
$catsByTypeJs = Json::encode($catsByType);
$itemsByCatJs = Json::encode($itemsByCat);
$curCatJs     = Json::encode($curCat);
$curItemJs    = Json::encode((string) $curItem);

$js = <<<JS
(function(){
    var catsByType = $catsByTypeJs;
    var itemsByCat = $itemsByCatJs;
    var curCat  = $curCatJs;
    var curItem = $curItemJs;

    var typeSel = document.getElementById('me-plan-type');
    var catSel  = document.getElementById('me-plan-category');
    var itemSel = document.getElementById('planorder-plan_item_id');

    function fill(sel, list, placeholder, selected){
        sel.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = ''; opt0.textContent = placeholder;
        sel.appendChild(opt0);
        (list || []).forEach(function(row){
            var o = document.createElement('option');
            o.value = row[0]; o.textContent = row[1];
            if (String(row[0]) === String(selected)) o.selected = true;
            sel.appendChild(o);
        });
    }

    function loadCats(selectedCat){
        fill(catSel, catsByType[typeSel.value] || [], '— เลือกหมวด —', selectedCat || '');
        loadItems(curItem);
    }
    function loadItems(selectedItem){
        fill(itemSel, itemsByCat[catSel.value] || [], '— เลือกรายการ —', selectedItem || '');
    }

    typeSel.addEventListener('change', function(){ curCat=''; curItem=''; loadCats(''); });
    catSel.addEventListener('change', function(){ curItem=''; loadItems(''); });

    // เริ่มต้น (รองรับโหมดแก้ไข)
    if (typeSel.value) { loadCats(curCat); }
})();

function recalcTotal(){
    var total = 0;
    document.querySelectorAll('.month-input').forEach(function(el){ total += parseFloat(el.value) || 0; });
    var t = document.getElementById('planorder-order_price');
    if (t) t.value = total.toFixed(2);
}
document.addEventListener('input', function(e){
    if (e.target.classList.contains('month-input')) recalcTotal();
});
document.getElementById('btn-spread').addEventListener('click', function(){
    var t = parseFloat(document.getElementById('planorder-order_price').value) || 0;
    if (t <= 0) {
        var v = prompt('ระบุยอดรวมที่ต้องการเฉลี่ย 12 เดือน (บาท)');
        t = parseFloat(v) || 0;
        if (t <= 0) return;
    }
    var inputs = document.querySelectorAll('.month-input');
    var per = Math.floor((t / 12) * 100) / 100;
    var acc = 0;
    inputs.forEach(function(el, idx){
        if (idx < 11) { el.value = per.toFixed(2); acc += per; }
        else { el.value = (t - acc).toFixed(2); }
    });
    recalcTotal();
});
recalcTotal();
JS;
$this->registerJs($js);
?>
