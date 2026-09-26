<?php

use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/**
 * ช่องวันที่มาตรฐาน ERP ผูกกับ model — datepicker ไทย (แสดง/กรอก วว/ดด/พ.ศ.)
 * name-based (ไม่ผูก client validator รูปแบบ Y-m-d); ฝั่ง controller ต้องแปลงกลับด้วย
 * AppHelper::normalizeDateToDb() ก่อน validate
 *
 * @var yii\base\Model $model
 * @var string $attr
 * @var string|null $label
 * @var string|null $hint
 */
$id = Html::getInputId($model, $attr);
$value = (string) $model->$attr;
// ค่าจากฐานข้อมูลเป็น ค.ศ. Y-m-d → แสดงเป็น พ.ศ.; ค่าที่ผู้ใช้กรอกค้างไว้ (พ.ศ.) แสดงตามเดิม
$display = preg_match('/^\d{4}-\d{2}-\d{2}/', $value) ? AppHelper::convertToThai(substr($value, 0, 10)) : $value;
$hasError = $model->hasErrors($attr);
?>
<div class="mb-3">
    <label class="form-label" for="<?= $id ?>"><?= Html::encode($label ?? $model->getAttributeLabel($attr)) ?></label>
    <?= DatepickerThai::widget([
        'name' => Html::getInputName($model, $attr),
        'value' => $display,
        'options' => ['id' => $id, 'class' => 'form-control' . ($hasError ? ' is-invalid' : ''), 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
    ]) ?>
    <?php if ($hasError): ?>
        <div class="invalid-feedback d-block"><?= Html::encode($model->getFirstError($attr)) ?></div>
    <?php endif; ?>
    <?php if (!empty($hint)): ?>
        <div class="form-text"><?= Html::encode($hint) ?></div>
    <?php endif; ?>
</div>
