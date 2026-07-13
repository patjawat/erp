<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningCourse $course */
/** @var app\modules\hr\models\ElearningMaterial[] $materials */
/** @var app\modules\hr\models\ElearningProgress $progress */
/** @var app\modules\hr\models\ElearningAttempt[] $attempts */

$this->title = $course->title;
$this->params['breadcrumbs'][] = ['label' => 'ห้องเรียน E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$isPassed = false;
foreach ($attempts as $att) {
    if ($att->is_passed) {
        $isPassed = true;
        break;
    }
}
?>

<div class="elearning-view">
    <?php $this->beginBlock('page-title'); ?>
    ห้องเรียนออนไลน์: <?= Html::encode($course->title) ?>
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('page-action'); ?>
    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> กลับไปรายการหลักสูตร', ['index'], ['class' => 'btn btn-outline-primary']) ?>
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="row">
        <!-- ฝั่งซ้าย: ข้อมูลหลักสูตรและสื่อการเรียนรู้ -->
        <div class="col-lg-8 col-md-12 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="text-primary fw-bold mb-3"><?= Html::encode($course->title) ?></h3>
                    <p class="text-secondary fs-7 lh-base"><?= nl2br(Html::encode($course->description)) ?></p>
                    
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <span class="badge bg-primary-subtle text-primary py-2 px-3 fs-7 rounded-pill">
                            <i class="fa-solid fa-book-open me-1"></i> บทเรียน <?= count($materials) ?> หัวข้อ
                        </span>
                        <span class="badge bg-warning-subtle text-warning py-2 px-3 fs-7 rounded-pill">
                            <i class="fa-solid fa-circle-question me-1"></i> เกณฑ์ผ่านสอบ <?= $course->passing_score_percent ?>%
                        </span>
                        <?php if ($isPassed): ?>
                            <span class="badge bg-success text-white py-2 px-3 fs-7 rounded-pill">
                                <i class="fa-solid fa-certificate me-1"></i> สำเร็จหลักสูตรนี้แล้ว
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- รายการสื่อการเรียนรู้ -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-photo-film text-primary me-2"></i> สื่อประกอบการสอนเพื่อความเข้าใจ</h5>
                    <p class="text-muted fs-7 mb-4">กรุณากดคลิกเปิดศึกษาเนื้อหาจากเอกสารหรือสื่อวิดีโอด้านล่างนี้ให้ครบถ้วน ก่อนเข้ารับการทำแบบทดสอบวัดผลหลังเรียน</p>

                    <div class="list-group list-group-flush">
                        <?php foreach ($materials as $index => $material): ?>
                            <?php
                            $icon = '<i class="fa-regular fa-file-pdf text-danger fs-4"></i>';
                            if ($material->type === 'video_url') {
                                $icon = '<i class="fa-brands fa-youtube text-danger fs-4"></i>';
                            } elseif ($material->type === 'slide_link') {
                                $icon = '<i class="fa-regular fa-file-powerpoint text-warning fs-4"></i>';
                            }
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3 border-0 bg-light rounded-3 mb-2 px-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <?= $icon ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0"><?= $index + 1 ?>. <?= Html::encode($material->title) ?></h6>
                                        <small class="text-muted fs-8">
                                            <?= $material->type === 'video_url' ? 'สื่อวิดีโอนำเสนอ' : ($material->type === 'pdf_file' ? 'เอกสารประกอบ PDF' : 'สไลด์บรรยาย') ?>
                                        </small>
                                    </div>
                                </div>
                                <div>
                                    <?= Html::a('<i class="fa-solid fa-circle-play me-1"></i> เปิดศึกษา', ['study-material', 'id' => $material->id], ['class' => 'btn btn-sm btn-primary rounded-pill px-3']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($materials)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fa-regular fa-folder-open fs-2 mb-2 d-block"></i>
                                ยังไม่มีสื่อประกอบการสอนในขณะนี้
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ฝั่งขวา: การทำแบบทดสอบหลังเรียนและประวัติผลสอบ -->
        <div class="col-lg-4 col-md-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-file-signature text-warning me-2"></i> แบบทดสอบวัดความรู้</h5>
                    <p class="text-muted fs-7 mb-4">ทำแบบประเมินผลการเรียนรู้หลังเรียนให้ได้คะแนนผ่านเกณฑ์ <?= $course->passing_score_percent ?>% เพื่อจบหลักสูตรนี้</p>
                    
                    <?php if ($isPassed): ?>
                        <div class="bg-success-subtle text-success p-3 rounded mb-3">
                            <h5 class="fw-bold mb-1"><i class="fa-solid fa-circle-check"></i> ผ่านเกณฑ์ประเมินแล้ว</h5>
                            <p class="mb-0 fs-8">คุณได้ผ่านเกณฑ์วัดระดับความรู้ของหลักสูตรนี้เรียบร้อย</p>
                        </div>
                        <?= Html::a('<i class="fa-solid fa-rotate-right me-1"></i> ทำข้อสอบใหม่อีกครั้ง', ['quiz', 'id' => $course->id], ['class' => 'btn btn-outline-warning w-100 rounded-pill']) ?>
                    <?php else: ?>
                        <?= Html::a('<i class="fa-solid fa-arrow-right-to-bracket me-1"></i> เริ่มทำแบบทดสอบหลังเรียน', ['quiz', 'id' => $course->id], ['class' => 'btn btn-warning w-100 rounded-pill fw-bold text-dark py-2.5 shadow-sm']) ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ประวัติการสอบ -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-history me-2"></i> ประวัติการทำแบบทดสอบของคุณ</h6>
                    
                    <div class="timelineTimeline timeline-sm">
                        <?php foreach ($attempts as $attempt): ?>
                            <div class="pb-3 border-start ps-3 position-relative" style="border-color: #dee2e6 !important;">
                                <div class="position-absolute start-0 translate-middle-x rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; margin-left: -1px; top: 0;">
                                    <i class="fa-solid <?= $attempt->is_passed ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' ?> fs-6"></i>
                                </div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 fs-7">ครั้งที่ <?= $attempt->attempt_number ?>: <?= $attempt->is_passed ? 'ผ่าน' : 'ไม่ผ่าน' ?></h6>
                                        <small class="text-muted fs-8"><?= Yii::$app->formatter->asDatetime($attempt->created_at, 'short') ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge <?= $attempt->is_passed ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> fw-bold">
                                            <?= $attempt->score ?>/<?= $attempt->total_questions ?> (<?= $attempt->percentage ?>%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($attempts)): ?>
                            <div class="text-center py-3 text-muted fs-8">
                                <i class="fa-regular fa-clock me-1"></i> ยังไม่มีประวัติการสอบหลังเรียน
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
