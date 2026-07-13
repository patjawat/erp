<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningQuestion $model */
/** @var app\modules\hr\models\ElearningCourse $course */

$this->title = 'เพิ่มโจทย์ข้อสอบ: ' . $course->title;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = ['label' => 'จัดการ E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $course->title, 'url' => ['view', 'id' => $course->id]];
$this->params['breadcrumbs'][] = 'เพิ่มโจทย์ข้อสอบ';
?>

<div class="elearning-question-create">
    <?php $this->beginBlock('page-title'); ?>
    เพิ่มโจทย์ข้อสอบหลังเรียน
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold text-secondary mb-4"><i class="fa-solid fa-file-circle-question text-warning me-2"></i> หลักสูตร: <?= Html::encode($course->title) ?></h5>

            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-9">
                    <?= $form->field($model, 'question_text')->textarea(['rows' => 3, 'placeholder' => 'ระบุโจทย์คำถาม เช่น ข้อใดต่อไปนี้เป็นวิธีการล้างมือที่ถูกต้องที่สุด?'])->label('โจทย์คำถาม <span class="text-danger">*</span>') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'value' => 0])->label('ลำดับการแสดงผลข้อนี้') ?>
                </div>
            </div>

            <hr class="my-4 opacity-10">

            <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-list-ul text-primary me-2"></i> ตัวเลือกตอบและเฉลยคำถาม</h5>
            <p class="text-muted fs-7 mb-4">ระบุข้อความตัวเลือกตอบทั้ง 4 ข้อ และทำเครื่องหมายเลือกหน้าข้อที่เฉลยว่าถูกต้อง (ข้อที่ถูกต้องที่สุดเพียงข้อเดียว)</p>

            <div class="answers-container">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <?php
                    $char = chr(65 + $i); // A, B, C, D
                    ?>
                    <div class="row align-items-center mb-3">
                        <div class="col-auto text-end" style="width: 80px;">
                            <div class="form-check d-flex justify-content-end align-items-center">
                                <input class="form-check-input fs-5 cursor-pointer border-warning" type="radio" name="correct_answer" id="correct_<?= $i ?>" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold text-primary ms-2 cursor-pointer" for="correct_<?= $i ?>">เฉลย <?= $char ?></label>
                            </div>
                        </div>
                        <div class="col">
                            <?= Html::textInput("Answers[{$i}]", '', [
                                'class' => 'form-control',
                                'placeholder' => "ป้อนข้อความตัวเลือกที่ {$char} (เช่น ล้างมือด้วยแอลกอฮอล์เจลนาน 20 วินาที)",
                                'required' => true,
                            ]) ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="form-group mt-5 d-flex justify-content-end gap-2">
                <?= Html::a('ย้อนกลับ', ['view', 'id' => $course->id], ['class' => 'btn btn-light rounded-pill px-4']) ?>
                <?= Html::submitButton('บันทึกคำถามและเฉลย', ['class' => 'btn btn-primary rounded-pill px-4']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
