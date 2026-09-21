<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12MrecAudit;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Ha12MrecAudit $model */
/** @var array<int,array{id:int,name:string}> $units */

$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
?>
<?php $form = ActiveForm::begin(['id' => 'ha12-mrec-form', 'action' => ['create', 'fy' => $model->fiscal_year]]); ?>
    <p class="text-body-secondary small">ระบบจะเตรียม 12 หัวข้อความครบถ้วนมาตรฐานให้ (แก้ไข/เพิ่ม/ลบได้ในหน้ากรอก)</p>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label small fw-semibold">หน่วยงานเจ้าของ</label>
            <?= Html::activeDropDownList($model, 'owner_unit_id', $unitOptions, ['class' => 'form-select', 'prompt' => '— เลือกหน่วยงาน —']) ?>
        </div>
        <div class="col-6">
            <label class="form-label small fw-semibold">เริ่มช่วง</label>
            <?= DatepickerThai::widget(['name' => 'period_start_thai', 'value' => AppHelper::convertToThai(date('Y-m-01')), 'options' => ['id' => 'mr-ps', 'autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
        </div>
        <div class="col-6">
            <label class="form-label small fw-semibold">สิ้นสุดช่วง</label>
            <?= DatepickerThai::widget(['name' => 'period_end_thai', 'value' => AppHelper::convertToThai(date('Y-m-d')), 'options' => ['id' => 'mr-pe', 'autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
        </div>
        <div class="col-6">
            <label class="form-label small fw-semibold">วันที่ทบทวน</label>
            <?= DatepickerThai::widget(['name' => 'review_date_thai', 'value' => AppHelper::convertToThai(date('Y-m-d')), 'options' => ['id' => 'mr-rd', 'autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
        </div>
        <div class="col-6">
            <label class="form-label small fw-semibold">จำนวนที่ตรวจ (ฉบับ)</label>
            <?= Html::activeInput('number', $model, 'total_charts', ['class' => 'form-control', 'min' => '0']) ?>
        </div>
    </div>
    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
        <?= Html::submitButton('สร้าง', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data' => ['bs-dismiss' => 'modal']]) ?>
    </div>
<?php ActiveForm::end();
$this->registerJs(<<<JS
['#mr-ps','#mr-pe','#mr-rd'].forEach(function(s){ if (typeof thaiDatepicker === 'function') thaiDatepicker(s); });
handleFormSubmit('#ha12-mrec-form', null, function (r) { if (r && r.redirect_url) { window.location.href = r.redirect_url; return; } location.reload(); });
JS); ?>
