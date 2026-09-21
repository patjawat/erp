<?php

use app\modules\ha12\models\Ha12Round;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Ha12Round $model */
/** @var array<int,array{id:int,name:string}> $units */

$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
$typeOptions = [
    Ha12Round::TYPE_YEAR => 'ทั้งปีงบประมาณ',
    Ha12Round::TYPE_QUARTER => 'รายไตรมาส',
    Ha12Round::TYPE_MONTH => 'รายเดือน',
];
$quarterJson = Json::htmlEncode(Ha12Round::QUARTER_LABELS);
$monthJson = Json::htmlEncode(Ha12Round::MONTH_LABELS);
?>
<?php $form = ActiveForm::begin(['id' => 'ha12-round-form', 'action' => ['create', 'fy' => $model->fiscal_year]]); ?>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">ปีงบประมาณ</label>
            <?= Html::activeTextInput($model, 'fiscal_year', ['class' => 'form-control']) ?>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">ประเภทรอบ</label>
            <?= Html::activeDropDownList($model, 'period_type', $typeOptions, ['class' => 'form-select', 'id' => 'rnd-type']) ?>
        </div>
        <div class="col-md-4" id="rnd-no-wrap">
            <label class="form-label small fw-semibold">ช่วง</label>
            <?= Html::activeDropDownList($model, 'period_no', [], ['class' => 'form-select', 'id' => 'rnd-no']) ?>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">ขอบเขตหน่วยงาน</label>
            <?= Html::activeDropDownList($model, 'scope_unit_id', $unitOptions, ['class' => 'form-select', 'prompt' => 'ทั้งโรงพยาบาล (ทุกหน่วยงาน)']) ?>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">ชื่อรอบ (ถ้าเว้นว่างจะตั้งอัตโนมัติ)</label>
            <?= Html::activeTextInput($model, 'title', ['class' => 'form-control', 'maxlength' => true]) ?>
        </div>
    </div>
    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
        <?= Html::submitButton('สร้างรอบ', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data' => ['bs-dismiss' => 'modal']]) ?>
    </div>
<?php ActiveForm::end();

$curNo = (int) ($model->period_no ?? 1);
$this->registerJs(<<<JS
(function(){
    var quarters = {$quarterJson}, months = {$monthJson};
    var typeEl = document.getElementById('rnd-type');
    var noEl = document.getElementById('rnd-no');
    var wrap = document.getElementById('rnd-no-wrap');
    function fill(){
        var t = typeEl.value, opts = {};
        if (t === 'quarter') opts = quarters; else if (t === 'month') opts = months;
        if (t === 'year') { wrap.style.display = 'none'; noEl.innerHTML=''; return; }
        wrap.style.display = '';
        noEl.innerHTML = '';
        Object.keys(opts).forEach(function(k){
            var o = document.createElement('option'); o.value = k; o.text = opts[k];
            if (parseInt(k,10) === {$curNo}) o.selected = true;
            noEl.appendChild(o);
        });
    }
    typeEl.addEventListener('change', fill); fill();
})();
handleFormSubmit('#ha12-round-form', null, function (r) { if (r && r.redirect_url) { window.location.href = r.redirect_url; return; } location.reload(); });
JS); ?>
