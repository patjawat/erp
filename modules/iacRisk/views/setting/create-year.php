<?php
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'สร้างปีงบประมาณ';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ระบบจะสร้างรอบ 6 เดือน 9 เดือน และสิ้นปีให้อัตโนมัติ<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= Html::a('กลับหน้าตั้งค่า', ['index'], ['class' => 'btn btn-outline-secondary']) ?><?php $this->endBlock(); ?>

<section class="card bg-body border shadow-sm">
    <div class="card-body p-3 p-md-4">
    <?php $form = ActiveForm::begin(); ?>
        <div class="row g-3">
            <div class="col-12 col-md-6"><?= $form->field($model, 'hospital_id')->dropDownList($hospitalOptions, ['prompt' => 'เลือกโรงพยาบาล']) ?></div>
            <div class="col-12 col-md-6"><?= $form->field($model, 'fiscal_year')->input('number', ['min' => 2500, 'max' => 2700]) ?></div>
            <div class="col-12 col-md-6"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>
            <div class="col-12 col-md-3"><?= $form->field($model, 'start_date')->input('date') ?></div>
            <div class="col-12 col-md-3"><?= $form->field($model, 'end_date')->input('date') ?></div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::submitButton('สร้างปีงบประมาณ', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>
    </div>
</section>
