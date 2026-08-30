<?php
use yii\helpers\Html;
/** @var app\modules\hr\models\ExitInterview $model */
$canEdit = $canEdit ?? true;
?>
<?= Html::beginForm('', 'post', ['id' => 'exit-questionnaire']) ?>
<?php foreach ($sections as $section): ?>
<section class="card bg-body border shadow-sm mb-3" aria-labelledby="section-<?= (int)$section->id ?>">
    <div class="card-header bg-body-tertiary"><h2 class="h5 mb-1" id="section-<?= (int)$section->id ?>"><?= Html::encode($section->title) ?></h2><?php if ($section->description): ?><p class="text-body-secondary small mb-0"><?= Html::encode($section->description) ?></p><?php endif ?></div>
    <div class="card-body">
    <?php foreach ($section->questions as $question): $value = $answers[$question->id] ?? null; ?>
        <fieldset class="mb-4"><legend class="fs-6 fw-semibold mb-2"><?= Html::encode($question->prompt) ?><?php if ($question->is_required): ?> <span class="text-danger" aria-label="จำเป็น">*</span><?php endif ?></legend>
        <?php if ($question->question_type === 'long_text'): ?>
            <?= Html::textarea("answers[{$question->id}]", (string)$value, ['class' => 'form-control', 'rows' => 4, 'required' => (bool)$question->is_required, 'disabled' => !$canEdit]) ?>
        <?php elseif ($question->question_type === 'short_text'): ?>
            <?= Html::textInput("answers[{$question->id}]", (string)$value, ['class' => 'form-control', 'required' => (bool)$question->is_required, 'disabled' => !$canEdit]) ?>
        <?php elseif ($question->question_type === 'rating'): ?>
            <div class="d-flex flex-wrap gap-2" role="radiogroup" aria-label="<?= Html::encode($question->prompt) ?>"><?php for ($score = 1; $score <= 5; $score++): ?><label class="btn btn-outline-secondary"><input class="form-check-input me-1" type="radio" name="answers[<?= (int)$question->id ?>]" value="<?= $score ?>" <?= (string)$value === (string)$score ? 'checked' : '' ?> <?= $question->is_required ? 'required' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>> <?= $score ?></label><?php endfor ?></div><div class="d-flex justify-content-between text-body-secondary small mt-2"><span>ควรปรับปรุงมาก</span><span>ดีมาก</span></div>
        <?php elseif ($question->question_type === 'single_choice'): ?>
            <div class="d-flex flex-wrap gap-2"><?php foreach ($question->options as $option): ?><label class="btn btn-outline-secondary"><input class="form-check-input me-1" type="radio" name="answers[<?= (int)$question->id ?>]" value="<?= Html::encode($option->value) ?>" <?= (string)$value === (string)$option->value ? 'checked' : '' ?> <?= $question->is_required ? 'required' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>> <?= Html::encode($option->label) ?></label><?php endforeach ?></div>
        <?php elseif ($question->question_type === 'multi_choice'): $selected = (array)$value; ?>
            <div class="vstack gap-2"><?php foreach ($question->options as $option): ?><label class="form-check"><input class="form-check-input" type="checkbox" name="answers[<?= (int)$question->id ?>][]" value="<?= Html::encode($option->value) ?>" <?= in_array($option->value, $selected, true) ? 'checked' : '' ?> <?= !$canEdit ? 'disabled' : '' ?>><span class="form-check-label"><?= Html::encode($option->label) ?></span></label><?php endforeach ?></div>
        <?php elseif ($question->question_type === 'ranking'): $ranked = array_values((array)$value); $max = (int)($question->config()['max_selections'] ?? 3); $options = ['' => 'ไม่เลือก'] + \yii\helpers\ArrayHelper::map($question->options, 'value', 'label'); ?>
            <div class="row g-2"><?php for ($rank = 0; $rank < $max; $rank++): ?><div class="col-12 col-md-4"><label class="form-label" for="rank-<?= (int)$question->id ?>-<?= $rank ?>">อันดับ <?= $rank + 1 ?></label><?= Html::dropDownList("answers[{$question->id}][]", $ranked[$rank] ?? '', $options, ['class' => 'form-select js-ranking', 'id' => "rank-{$question->id}-{$rank}", 'required' => $question->is_required && $rank === 0, 'disabled' => !$canEdit]) ?></div><?php endfor ?></div><div class="form-text">ห้ามเลือกเหตุผลซ้ำกัน</div>
        <?php elseif ($question->question_type === 'date'): ?><?= Html::input('date', "answers[{$question->id}]", (string)$value, ['class' => 'form-control', 'disabled' => !$canEdit]) ?>
        <?php elseif ($question->question_type === 'number'): ?><?= Html::input('number', "answers[{$question->id}]", (string)$value, ['class' => 'form-control', 'disabled' => !$canEdit]) ?><?php endif ?>
        </fieldset>
    <?php endforeach ?>
    </div>
</section>
<?php endforeach ?>
<?php if ($model->status === 'submitted' && !$publicMode && $canEdit): ?><div class="mb-3"><label class="form-label" for="edit-reason">เหตุผลที่แก้ไขคำตอบที่ส่งแล้ว <span class="text-danger">*</span></label><?= Html::textarea('edit_reason', '', ['class' => 'form-control', 'id' => 'edit-reason', 'rows' => 2, 'required' => true]) ?></div><?php endif ?>
<?php if ($publicMode): ?><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="consent" value="1" id="consent" required><label class="form-check-label" for="consent">ข้าพเจ้ายินยอมให้นำคำตอบไปใช้วิเคราะห์เพื่อพัฒนาองค์กร โดยจำกัดการเข้าถึงข้อมูลรายบุคคล</label></div><?php endif ?>
<?php if ($canEdit): ?><div class="sticky-bottom bg-body border-top py-3 d-grid d-sm-flex justify-content-sm-end gap-2"><?= Html::submitButton('บันทึกร่าง', ['class' => 'btn btn-outline-secondary', 'name' => 'intent', 'value' => 'draft', 'formnovalidate' => true]) ?><?= Html::submitButton('ส่งแบบสัมภาษณ์', ['class' => 'btn btn-primary', 'name' => 'intent', 'value' => 'submit']) ?></div><?php endif ?>
<?= Html::endForm() ?>
<?php $this->registerJs(<<<JS
document.querySelectorAll('.js-ranking').forEach(function (select) {
  select.addEventListener('change', function () {
    var group = select.closest('.row').querySelectorAll('.js-ranking');
    var used = Array.from(group).map(function (item) { return item.value; }).filter(Boolean);
    group.forEach(function (item) { Array.from(item.options).forEach(function (option) { option.disabled = option.value && option.value !== item.value && used.includes(option.value); }); });
  });
  select.dispatchEvent(new Event('change'));
});
JS); ?>
