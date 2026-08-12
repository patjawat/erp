<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\BondPolicy;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\BondPolicy $model */

$form = ActiveForm::begin(['id' => 'bond-policy-form']);
?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

<div class="alert alert-warning small">
    <i class="bi bi-exclamation-triangle me-1"></i>
    ตัวเลขที่แก้ตรงนี้มีผลกับคำแนะนำในหน้าบันทึกหลักประกันทันที รวมถึงรายการ
    "สัญญาที่เข้าเกณฑ์ต้องวางหลักประกันแต่ยังไม่มี" บนหน้าทะเบียน
    หลักประกันที่บันทึกไว้แล้วไม่ถูกแก้ตาม เพราะเก็บอัตราที่ใช้ตอนนั้นไว้ในตัวเอง
</div>

<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'title')->textInput([
            'maxlength' => true,
            'placeholder' => 'ข้อความนี้จะแสดงให้เจ้าหน้าที่เห็นเมื่อวงเงินเข้าเกณฑ์นี้',
        ]) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'proc_kind')->dropDownList(BondPolicy::kindList())
            ->hint('เลือกประเภทสัญญาเมื่อเกณฑ์นี้ใช้เฉพาะบางประเภท') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'min_amount')->input('number', ['step' => '0.01', 'min' => 0])
            ->hint('นับรวมค่านี้') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'max_amount')->input('number', ['step' => '0.01', 'min' => 0])
            ->hint('นับรวมค่านี้ · เว้นว่าง = ไม่จำกัดปลายบน') ?>
    </div>

    <div class="col-md-4 d-flex align-items-center">
        <div class="mb-3 w-100">
            <?= $form->field($model, 'required')->checkbox() ?>
        </div>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'rate')->input('number', ['step' => '0.01', 'min' => 0, 'max' => 100])
            ->hint('ใช้เมื่อติ๊ก "ต้องวางหลักประกัน"') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'sort_order')->input('number', ['step' => 1])
            ->hint('เลขน้อยถูกจับคู่ก่อน') ?>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'law_ref')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น ระเบียบกระทรวงการคลังฯ พ.ศ. 2560 ข้อ 168',
        ])->hint('ข้อความนี้ถูกแสดงคู่กับคำแนะนำ เพื่อให้เจ้าหน้าที่อ้างอิงได้ทันที') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'note')->textarea(['rows' => 2])
            ->hint('ลบข้อความเตือน "ยังไม่ผ่านการยืนยันจากงานพัสดุ" ออกเมื่อตรวจสอบแล้ว') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'active')->checkbox() ?>
    </div>
</div>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
