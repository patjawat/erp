<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningAttempt $model */
/** @var app\modules\hr\models\ElearningCourse $course */

$this->title = 'ผลการสอบ: ' . $course->title;
$this->params['breadcrumbs'][] = ['label' => 'ห้องเรียน E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $course->title, 'url' => ['view', 'id' => $course->id]];
$this->params['breadcrumbs'][] = 'ผลการสอบ';
?>

<div class="elearning-quiz-result d-flex justify-content-center">
    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="card border-0 shadow-sm col-xl-6 col-lg-8 col-md-10 col-sm-12 p-4 text-center mt-3">
        <div class="card-body">
            
            <?php if ($model->is_passed): ?>
                <div class="fs-1 text-success mb-3"><i class="fa-solid fa-circle-check fa-beat" style="--fa-animation-duration: 2s;"></i></div>
                <h3 class="fw-bold text-success mb-1">ขอแสดงความยินดี! คุณสอบผ่าน</h3>
                <p class="text-muted fs-7">คุณได้ศึกษาและผ่านการประเมินความรู้ของหลักสูตรนี้เรียบร้อยแล้ว</p>
            <?php else: ?>
                <div class="fs-1 text-danger mb-3"><i class="fa-solid fa-circle-xmark fa-shake" style="--fa-animation-duration: 2s;"></i></div>
                <h3 class="fw-bold text-danger mb-1">คุณยังไม่ผ่านเกณฑ์</h3>
                <p class="text-muted fs-7">เกณฑ์คะแนนสอบผ่านของหลักสูตรนี้คืออย่างน้อย <strong><?= $course->passing_score_percent ?>%</strong></p>
            <?php endif; ?>

            <!-- วงกลมแสดงคะแนนสอบ -->
            <div class="d-inline-flex flex-column align-items-center justify-content-center rounded-circle my-4 border border-5 p-4 <?= $model->is_passed ? 'border-success text-success bg-success-subtle' : 'border-danger text-danger bg-danger-subtle' ?>" style="width: 180px; height: 180px;">
                <h1 class="fw-bold mb-0" style="font-size: 2.8rem;"><?= $model->score ?></h1>
                <div class="border-top w-50 my-1 opacity-25"></div>
                <h5 class="fw-bold mb-0">จาก <?= $model->total_questions ?> ข้อ</h5>
                <small class="fs-8 fw-semibold mt-1">(<?= $model->percentage ?>%)</small>
            </div>

            <div class="alert <?= $model->is_passed ? 'alert-success border-0' : 'alert-danger border-0' ?> fs-7 text-start mb-4">
                <ul class="mb-0">
                    <li><strong>หลักสูตร:</strong> <?= Html::encode($course->title) ?></li>
                    <li><strong>ทดสอบครั้งที่:</strong> <?= $model->attempt_number ?></li>
                    <li><strong>ส่งคำตอบเมื่อ:</strong> <?= Yii::$app->formatter->asDatetime($model->created_at, 'medium') ?></li>
                </ul>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <?= Html::a('<i class="fa-solid fa-hospital-user me-1"></i> กลับห้องเรียนหลักสูตร', ['view', 'id' => $course->id], ['class' => 'btn btn-primary rounded-pill px-4']) ?>
                
                <?php if (!$model->is_passed): ?>
                    <?= Html::a('<i class="fa-solid fa-rotate-right me-1"></i> ลองทำแบบทดสอบอีกครั้ง', ['quiz', 'id' => $course->id], ['class' => 'btn btn-warning text-dark fw-bold rounded-pill px-4']) ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
