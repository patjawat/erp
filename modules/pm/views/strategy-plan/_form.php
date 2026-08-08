<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
app\assets\RichTextAsset::register($this);
app\assets\FormGuardAsset::register($this);
$form = ActiveForm::begin();
?>
<div class="card border-0 shadow-sm"><div class="card-body p-4">
    <div class="row g-3">
        <div class="col-12 col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น SP-2568-2572']) ?></div>
        <div class="col-6 col-md-2"><?= $form->field($model, 'version')->textInput(['type' => 'number', 'min' => 1]) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($model, 'start_year')->textInput(['type' => 'number']) ?></div>
        <div class="col-6 col-md-3"><?= $form->field($model, 'end_year')->textInput(['type' => 'number']) ?></div>
        <div class="col-12"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>
        <div class="col-12"><?= $form->field($model, 'vision')->textarea(['rows' => 4, 'placeholder' => 'วิสัยทัศน์ขององค์กรในช่วงแผนนี้', 'data-richtext' => 'vision', 'data-rte-label' => 'วิสัยทัศน์']) ?></div>
        <div class="col-12"><?= $form->field($model, 'source_note')->textarea(['rows' => 2, 'placeholder' => 'ชื่อไฟล์ Word/Excel หรือแหล่งข้อมูลต้นฉบับ', 'data-richtext' => 'source_note', 'data-rte-label' => 'แหล่งที่มา/หมายเหตุ']) ?></div>
    </div>
</div><div class="card-footer bg-body d-flex justify-content-between gap-2 p-3">
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i data-lucide="save" class="me-1"></i> บันทึกฉบับร่าง', ['class' => 'btn btn-primary']) ?>
</div></div>
<?php ActiveForm::end(); ?>
