<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningCourse $course */
/** @var app\modules\hr\models\ElearningQuestion[] $questions */

$this->title = 'ทำแบบทดสอบ: ' . $course->title;
$this->params['breadcrumbs'][] = ['label' => 'ห้องเรียน E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $course->title, 'url' => ['view', 'id' => $course->id]];
$this->params['breadcrumbs'][] = 'แบบทดสอบหลังเรียน';
?>

<div class="elearning-quiz">
    <?php $this->beginBlock('page-title'); ?>
    แบบทดสอบหลังการเรียนรู้ (Post-test)
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('page-action'); ?>
    <?= Html::a('<i class="fa-solid fa-xmark me-1"></i> ออกจากการสอบ', ['view', 'id' => $course->id], [
        'class' => 'btn btn-outline-danger',
        'data' => [
            'confirm' => 'คุณต้องการยกเลิกการสอบและกลับไปหน้าหลักสูตรใช่หรือไม่? (คำตอบของคุณจะไม่ได้รับการบันทึก)',
        ]
    ]) ?>
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 bg-light border-bottom">
            <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-file-signature text-warning me-2"></i> หลักสูตร: <?= Html::encode($course->title) ?></h5>
            <p class="text-muted mb-0 fs-7">
                มีข้อสอบทั้งหมด <strong><?= count($questions) ?></strong> ข้อ | เกณฑ์ผ่านหลักสูตรอย่างน้อย <strong><?= $course->passing_score_percent ?>%</strong> (ประมาณ <?= ceil(count($questions) * ($course->passing_score_percent / 100)) ?> ข้อ)
            </p>
        </div>

        <div class="card-body p-4">
            <?php $form = ActiveForm::begin([
                'action' => ['submit-quiz', 'id' => $course->id],
                'method' => 'post',
                'options' => ['id' => 'quiz-form']
            ]); ?>

            <div class="quiz-questions">
                <?php foreach ($questions as $qIndex => $question): ?>
                    <div class="question-card p-4 mb-4 rounded-3 border-0 bg-white shadow-none" style="background-color: #fcfcfc !important; border: 1px solid #f0f0f0 !important;">
                        <h6 class="fw-bold text-dark mb-3">
                            <span class="text-primary me-2">ข้อที่ <?= $qIndex + 1 ?>:</span> <?= Html::encode($question->question_text) ?>
                        </h6>
                        
                        <div class="options-list ps-3">
                            <?php foreach ($question->answers as $aIndex => $answer): ?>
                                <?php
                                $char = chr(65 + $aIndex); // A, B, C, D
                                ?>
                                <div class="form-check py-2.5 rounded hover-bg-light px-3 mb-2 border-0" style="background-color: #ffffff; border: 1px solid #f0f0f0; margin-left: 0;">
                                    <input class="form-check-input fs-5 cursor-pointer" type="radio" name="answers[<?= $question->id ?>]" id="q_<?= $question->id ?>_a_<?= $answer->id ?>" value="<?= $answer->id ?>" required>
                                    <label class="form-check-label text-secondary fw-semibold cursor-pointer w-100 ps-2 fs-7" for="q_<?= $question->id ?>_a_<?= $answer->id ?>">
                                        <span class="text-primary fw-bold me-2"><?= $char ?>.</span> <?= Html::encode($answer->answer_text) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <?= Html::submitButton('<i class="fa-solid fa-paper-plane me-1"></i> ส่งแบบทดสอบตรวจคะแนน', [
                    'class' => 'btn btn-warning text-dark fw-bold btn-lg rounded-pill px-5 py-2.5 shadow-sm',
                    'data' => [
                        'confirm' => 'ยืนยันความถูกต้องและต้องการส่งแบบทดสอบหลังเรียนเพื่อตรวจคะแนนทันทีใช่หรือไม่?',
                    ]
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
.hover-bg-light:hover {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
}
</style>
