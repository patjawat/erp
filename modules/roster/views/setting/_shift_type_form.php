<?php

use app\modules\roster\models\ShiftType;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var ShiftType $model */

$colorOptions = [];
foreach (ShiftType::colorLabels() as $value => $label) {
    $colorOptions[$value] = $label;
}
?>
<?php $form = ActiveForm::begin([
    'id' => 'form',
    'options' => ['data-pjax' => false],
]); ?>

<div class="row g-3">
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'code')->textInput(['maxlength' => 20, 'placeholder' => 'M / A / N'])
            ->hint('ใช้อ้างในโค้ด เปลี่ยนภายหลังไม่ได้') ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'short_name')->textInput(['maxlength' => 10, 'placeholder' => 'ช / บ / ด'])
            ->hint('ตัวที่แสดงในช่องกริด') ?>
    </div>
    <div class="col-12 col-md-6">
        <?= $form->field($model, 'title')->textInput(['maxlength' => 100, 'placeholder' => 'เวรเช้า']) ?>
    </div>

    <div class="col-6 col-md-4">
        <?= $form->field($model, 'color')->dropDownList($colorOptions, ['class' => 'form-select']) ?>
    </div>
    <div class="col-6 col-md-4">
        <?= $form->field($model, 'sort_order')->input('number', ['min' => 0, 'step' => 1]) ?>
    </div>
    <div class="col-12 col-md-4 d-flex align-items-end pb-3">
        <div class="form-check form-switch">
            <?= Html::activeCheckbox($model, 'active', [
                'class' => 'form-check-input',
                'label' => 'ใช้งาน',
                'labelOptions' => ['class' => 'form-check-label'],
            ]) ?>
        </div>
    </div>
</div>

<hr class="my-3">

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="form-check form-switch">
            <?= Html::activeCheckbox($model, 'is_night', [
                'class' => 'form-check-input',
                'label' => 'เป็นเวรดึก',
                'labelOptions' => ['class' => 'form-check-label fw-semibold'],
            ]) ?>
        </div>
        <div class="text-body-secondary small ms-4">ใช้กับกฎ “ห้ามดึกติดเช้า”</div>
    </div>
    <div class="col-12 col-md-4">
        <div class="form-check form-switch">
            <?= Html::activeCheckbox($model, 'is_extra', [
                'class' => 'form-check-input',
                'label' => 'เป็นเวรเสริม/ควบ',
                'labelOptions' => ['class' => 'form-check-label fw-semibold'],
            ]) ?>
        </div>
        <div class="text-body-secondary small ms-4">ไม่นับเป็นเวรหลักของวัน</div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
// ActiveForm ใน modal ต้องผูก handleFormSubmit เอง ไม่งั้น submit แบบเต็มหน้า
// แล้วเบราว์เซอร์เด้งไปหน้า JSON ดิบแทนที่จะปิด modal
$this->registerJs(<<<'JS'
handleFormSubmit('#form', null, async function (response) {
    if (response.container && typeof erpReloadPjax === 'function'
        && erpReloadPjax(response.container)) {
        return;
    }
    location.reload();
});
JS);
?>
