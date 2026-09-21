<?php

use app\modules\ha12\models\Ha12Indicator;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Ha12Indicator $model */
/** @var array<int,array{id:int,name:string}> $units */

$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
$levelOptions = array_combine(Ha12Indicator::LEVELS, Ha12Indicator::LEVELS);
?>
<?php $form = ActiveForm::begin([
    'id' => 'ha12-ind-form',
    'action' => $model->isNewRecord ? ['create', 'fy' => $model->fiscal_year] : ['update', 'id' => $model->id],
    'options' => ['data-list-url' => \yii\helpers\Url::to(['index', 'fy' => $model->fiscal_year])],
]); ?>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label small fw-semibold">ชื่อตัวชี้วัด <span class="text-danger">*</span></label>
            <?= Html::activeTextarea($model, 'name', ['class' => 'form-control', 'rows' => 2]) ?>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">หน่วยงานเจ้าของ</label>
            <?= Html::activeDropDownList($model, 'owner_unit_id', $unitOptions, ['class' => 'form-select', 'prompt' => '— เลือกหน่วยงาน —']) ?>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">เป้าหมาย</label>
            <?= Html::activeTextInput($model, 'target', ['class' => 'form-control', 'maxlength' => true, 'placeholder' => 'เช่น ≥ 90']) ?>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">หน่วย</label>
            <?= Html::activeTextInput($model, 'unit_label', ['class' => 'form-control', 'placeholder' => '%']) ?>
        </div>
        <div class="col-md-8">
            <label class="form-label small fw-semibold">ความเสี่ยง</label>
            <?= Html::activeTextarea($model, 'risk', ['class' => 'form-control', 'rows' => 1]) ?>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">ระดับ (A-I)</label>
            <?= Html::activeDropDownList($model, 'level', $levelOptions, ['class' => 'form-select', 'prompt' => '— ไม่ระบุ —']) ?>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">การแก้ไข</label>
            <?= Html::activeTextarea($model, 'fix', ['class' => 'form-control', 'rows' => 1]) ?>
        </div>
    </div>
    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data' => ['bs-dismiss' => 'modal']]) ?>
    </div>
<?php ActiveForm::end();
$this->registerJs(<<<JS
handleFormSubmit('#ha12-ind-form', null, function (r) {
    var c = r && r.container;
    if (c && document.querySelector(c) && typeof erpReloadPjax === 'function' && erpReloadPjax(c)) return;
    var url = document.querySelector('#ha12-ind-form').getAttribute('data-list-url');
    url ? window.location.href = url : location.reload();
});
JS); ?>
