<?php

use app\modules\kpi\models\KpiItem;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var KpiItem $item */
/** @var app\modules\kpi\models\KpiCycle $cycle */

$typeOptions = [KpiItem::TYPE_NUMERIC => 'ตัวเลข', KpiItem::TYPE_QUALITATIVE => 'คุณภาพ/ข้อความ'];
$freqOptions = [KpiItem::FREQ_MONTHLY => 'รายเดือน', KpiItem::FREQ_QUARTERLY => 'รายไตรมาส', KpiItem::FREQ_YEARLY => 'รายปี'];
$aggOptions = KpiItem::aggregationLabels();
$dirOptions = [KpiItem::DIR_ASC => 'มากขึ้น = ดี', KpiItem::DIR_DESC => 'น้อยลง = ดี'];

$form = ActiveForm::begin([
    'id' => 'kpi-item-form',
    'action' => Url::to(['edit-item', 'id' => $item->id]),
    'enableClientValidation' => false,
    'fieldConfig' => [
        'options' => ['class' => 'mb-2'],
        'labelOptions' => ['class' => 'form-label small fw-semibold mb-1'],
    ],
]);
?>
<div class="row g-2">
    <div class="col-12"><?= $form->field($item, 'indicator')->textInput(['maxlength' => true])->label('ชื่อตัวชี้วัด') ?></div>

    <div class="col-6 col-md-5"><?= $form->field($item, 'target_text')->textInput(['placeholder' => 'เช่น ≥90%'])->label('เป้าหมาย (ข้อความ)') ?></div>
    <div class="col-3 col-md-4"><?= $form->field($item, 'target_value')->textInput(['type' => 'number', 'step' => 'any'])->label('เป้า (ตัวเลข)') ?></div>
    <div class="col-3 col-md-3"><?= $form->field($item, 'unit')->textInput()->label('หน่วย') ?></div>

    <div class="col-12">
        <label class="form-label small fw-semibold mb-1">คะแนนตามระดับค่าเป้าหมาย <span class="text-body-secondary fw-normal">— ผลงานถึงเกณฑ์ระดับใด ได้คะแนนระดับนั้น (1–5)</span></label>
        <div class="row row-cols-5 g-1">
            <?php foreach ([1, 2, 3, 4, 5] as $l): ?>
                <div class="col">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">ร.<?= $l ?></span>
                        <?= Html::activeTextInput($item, 'level' . $l, ['type' => 'number', 'step' => 'any', 'class' => 'form-control']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-6 col-md-3"><?= $form->field($item, 'value_type')->dropDownList($typeOptions)->label('ชนิดผล') ?></div>
    <div class="col-6 col-md-3"><?= $form->field($item, 'direction')->dropDownList($dirOptions)->label('ทิศทาง') ?></div>
    <div class="col-6 col-md-3"><?= $form->field($item, 'aggregation')->dropDownList($aggOptions)->label('วิธีสรุปผล') ?></div>
    <div class="col-6 col-md-3"><?= $form->field($item, 'frequency')->dropDownList($freqOptions)->label('ความถี่') ?></div>

    <div class="col-6 col-md-4"><?= $form->field($item, 'weight')->textInput(['type' => 'number', 'step' => 'any', 'class' => 'form-control text-end'])->label('น้ำหนัก (%)') ?></div>

    <div class="col-12"><?= $form->field($item, 'formula')->textarea(['rows' => 2, 'placeholder' => 'เช่น (จำนวนงานพิธีการ × 100 ÷ จำนวนงานทั้งหมดที่วางแผนไว้)'])->label('สูตรคำนวณ') ?></div>
    <div class="col-12"><?= $form->field($item, 'evidence')->textarea(['rows' => 2, 'placeholder' => 'เช่น โครงการแผนงาน หนังสือเชิญ รายงานผลการจัดงาน ภาพกิจกรรม'])->label('หลักฐาน / เอกสารอ้างอิง') ?></div>
</div>

<div class="d-flex justify-content-end gap-2 mt-3">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
if (typeof handleFormSubmit === 'function') {
    // ไม่ส่ง successCallback → erp.js ปิด modal แล้ว location.reload อัตโนมัติ
    handleFormSubmit('#kpi-item-form');
}
JS);
?>
