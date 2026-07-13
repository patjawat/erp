<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $mandatoryCourses */
/** @var array $generalCourses */
/** @var app\modules\hr\models\Employees $employee */

$this->title = 'ห้องเรียน E-learning ของฉัน';
$this->params['breadcrumbs'][] = $this->title;

$allDeps = \app\modules\hr\models\Employees::ListDepartment();
$myDepName = $allDeps[$employee->department] ?? 'ทั่วไป';
?>

<div class="elearning-index">
    <?php $this->beginBlock('page-title'); ?>
    ห้องเรียนออนไลน์ (E-Learning)
    <?php $this->endBlock(); ?>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <!-- ทักทายเจ้าหน้าที่ -->
    <div class="card border-0 shadow-sm mb-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">สวัสดีครับ คุณ<?= Html::encode($employee->fname . ' ' . $employee->lname) ?></h4>
                    <p class="mb-0 opacity-85 fs-7"><i class="fa-solid fa-hospital-user me-1"></i> สังกัดแผนก: <strong><?= Html::encode($myDepName) ?></strong> | เพิ่มทักษะและความรู้ในการทำงานของคุณที่นี่</p>
                </div>
                <div class="fs-1 opacity-25 d-none d-md-block"><i class="fa-solid fa-graduation-cap"></i></div>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 1: หลักสูตรบังคับตามกลุ่มงาน / แผนก -->
    <div class="mb-4">
        <h5 class="fw-bold text-danger mb-3"><i class="fa-solid fa-circle-exclamation me-2"></i> หลักสูตรบังคับประจำแผนกของคุณ (Mandatory Training)</h5>
        <div class="row">
            <?php foreach ($mandatoryCourses as $item): ?>
                <?php
                $course = $item['model'];
                $progress = $item['progress'];
                $isPassed = $item['is_passed'];
                ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-3 border-danger">
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-8">หลักสูตรบังคับ</span>
                                <?php if ($isPassed): ?>
                                    <span class="badge bg-success text-white px-2 py-1 fs-8"><i class="fa-solid fa-certificate me-1"></i> สอบผ่านแล้ว</span>
                                <?php elseif ($progress && $progress->status === 'learning'): ?>
                                    <span class="badge bg-info-subtle text-info px-2 py-1 fs-8"><i class="fa-solid fa-arrows-spin fa-spin me-1"></i> กำลังศึกษา</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-8">ยังไม่เข้าเรียน</span>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title fw-bold text-dark mb-2"><?= Html::encode($course->title) ?></h5>
                            <p class="card-text text-secondary fs-7 mb-4 flex-grow-1">
                                <?= Html::encode(mb_strimwidth($course->description, 0, 110, "...")) ?>
                            </p>

                            <!-- แถบความคืบหน้าการเรียน -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between fs-8 text-muted mb-1">
                                    <span>สื่อการสอน <?= count($course->materials) ?> รายการ</span>
                                    <span>เกณฑ์ผ่าน <?= $course->passing_score_percent ?>%</span>
                                </div>
                                <?php if ($progress && $progress->status === 'completed'): ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                    </div>
                                <?php else: ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $progress ? '50%' : '0%' ?>"></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <?= Html::a(
                                    $isPassed ? 'ทบทวนบทเรียน <i class="fa-solid fa-rotate-right fs-8 ms-1"></i>' : 'เริ่มศึกษาบทเรียน <i class="fa-solid fa-circle-play fs-8 ms-1"></i>',
                                    ['view', 'id' => $course->id],
                                    ['class' => $isPassed ? 'btn btn-outline-success w-100 rounded-pill' : 'btn btn-danger w-100 rounded-pill']
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($mandatoryCourses)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 text-center text-muted fs-7">
                        <i class="fa-solid fa-circle-check text-success fs-3 mb-2"></i>
                        ไม่มีหลักสูตรบังคับเรียนประจำแผนกของคุณในขณะนี้
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ส่วนที่ 2: หลักสูตรทั่วไปเพิ่มเติม -->
    <div class="mb-4">
        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-graduation-cap me-2"></i> หลักสูตรทั่วไปและเพิ่มพูนทักษะ (General Training)</h5>
        <div class="row">
            <?php foreach ($generalCourses as $item): ?>
                <?php
                $course = $item['model'];
                $progress = $item['progress'];
                $isPassed = $item['is_passed'];
                ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-dark px-2 py-1 fs-8">หลักสูตรทั่วไป</span>
                                <?php if ($isPassed): ?>
                                    <span class="badge bg-success text-white px-2 py-1 fs-8"><i class="fa-solid fa-certificate me-1"></i> สอบผ่านแล้ว</span>
                                <?php elseif ($progress && $progress->status === 'learning'): ?>
                                    <span class="badge bg-info-subtle text-info px-2 py-1 fs-8"><i class="fa-solid fa-arrows-spin fa-spin me-1"></i> กำลังศึกษา</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-8">ยังไม่เข้าเรียน</span>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title fw-bold text-dark mb-2"><?= Html::encode($course->title) ?></h5>
                            <p class="card-text text-secondary fs-7 mb-4 flex-grow-1">
                                <?= Html::encode(mb_strimwidth($course->description, 0, 110, "...")) ?>
                            </p>

                            <!-- แถบความคืบหน้าการเรียน -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between fs-8 text-muted mb-1">
                                    <span>สื่อการสอน <?= count($course->materials) ?> รายการ</span>
                                    <span>เกณฑ์ผ่าน <?= $course->passing_score_percent ?>%</span>
                                </div>
                                <?php if ($progress && $progress->status === 'completed'): ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                    </div>
                                <?php else: ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $progress ? '50%' : '0%' ?>"></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-auto">
                                <?= Html::a(
                                    $isPassed ? 'ทบทวนบทเรียน <i class="fa-solid fa-rotate-right fs-8 ms-1"></i>' : 'เริ่มศึกษาบทเรียน <i class="fa-solid fa-circle-play fs-8 ms-1"></i>',
                                    ['view', 'id' => $course->id],
                                    ['class' => $isPassed ? 'btn btn-outline-success w-100 rounded-pill' : 'btn btn-primary w-100 rounded-pill']
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($generalCourses) && empty($mandatoryCourses)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-5 text-center text-muted">
                        <i class="fa-solid fa-folder-open fs-2 mb-2"></i>
                        ยังไม่มีหลักสูตรใดเปิดสอนในโมดูล E-learning ขณะนี้
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
