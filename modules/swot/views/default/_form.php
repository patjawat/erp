<?php

use app\components\AppHelper;
use app\modules\swot\models\SwotBoard;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoard $model */

$curYear = (int) AppHelper::YearBudget();
$years = [];
for ($y = $curYear + 1; $y >= $curYear - 3; $y--) {
    $years[$y] = (string) $y;
}
if ($model->budget_year && !isset($years[$model->budget_year])) {
    $years[$model->budget_year] = (string) $model->budget_year;
}
?>
<div class="mb-3">
    <label class="form-label">ชื่อเรื่องที่ต้องการวิเคราะห์ <span class="text-danger">*</span></label>
    <?= Html::activeTextInput($model, 'title', [
        'class' => 'form-control',
        'placeholder' => 'เช่น การพัฒนาบริการผู้ป่วยนอก, การวิเคราะห์หน่วยงาน...',
        'maxlength' => true,
        'required' => true,
    ]) ?>
</div>

<div class="mb-3">
    <label class="form-label d-block">กรอบการวิเคราะห์</label>
    <div class="btn-group w-100" role="group">
        <?php $fw = $model->framework ?: SwotBoard::FRAMEWORK_SWOT; ?>
        <input type="radio" class="btn-check" name="<?= Html::getInputName($model, 'framework') ?>" id="fw-swot" value="swot" <?= $fw === 'swot' ? 'checked' : '' ?>>
        <label class="btn btn-outline-success" for="fw-swot">
            <i class="bi bi-grid-3x3-gap me-1"></i>SWOT
            <span class="d-block small text-muted">จุดแข็ง · จุดอ่อน · โอกาส · อุปสรรค</span>
        </label>

        <input type="radio" class="btn-check" name="<?= Html::getInputName($model, 'framework') ?>" id="fw-soar" value="soar" <?= $fw === 'soar' ? 'checked' : '' ?>>
        <label class="btn btn-outline-primary" for="fw-soar">
            <i class="bi bi-stars me-1"></i>SOAR
            <span class="d-block small text-muted">จุดแข็ง · โอกาส · ความปรารถนา · ผลลัพธ์</span>
        </label>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">วัตถุประสงค์ / บริบท</label>
    <?= Html::activeTextarea($model, 'objective', [
        'class' => 'form-control',
        'rows' => 2,
        'placeholder' => 'ระบุเป้าหมายหรือขอบเขตของการวิเคราะห์นี้ (ไม่บังคับ)',
    ]) ?>
</div>

<div class="mb-1">
    <label class="form-label">ปีงบประมาณ</label>
    <?= Html::activeDropDownList($model, 'budget_year', $years, [
        'class' => 'form-select',
        'prompt' => '— เลือกปีงบ —',
        'value' => $model->budget_year ?: $curYear,
    ]) ?>
</div>
