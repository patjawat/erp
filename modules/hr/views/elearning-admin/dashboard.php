<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var int $totalCourses */
/** @var int $totalEnrolled */
/** @var int $totalCompleted */
/** @var float $completionRate */
/** @var float $averagePassRate */
/** @var array $courseStats */
/** @var yii\data\ActiveDataProvider $attemptsDataProvider */

$this->title = 'สถิติภาพรวม E-learning โรงพยาบาล';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = ['label' => 'จัดการ E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$allDeps = \app\modules\hr\models\Employees::ListDepartment();
?>

<div class="elearning-admin-dashboard">
    <?php $this->beginBlock('page-title'); ?>
    <?= Html::encode($this->title) ?>
    <?php $this->endBlock(); ?>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <?= Html::a('<i class="fa-solid fa-list me-1"></i> จัดการหลักสูตร', ['index'], ['class' => 'btn btn-outline-primary rounded-pill px-3']) ?>
    </div>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <!-- ส่วนที่ 1: การ์ด KPI ตัวชี้วัดสำคัญ -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-white-50 fw-bold mb-1 fs-8">หลักสูตรที่เปิดสอน</h6>
                        <h2 class="fw-bold mb-0"><?= $totalCourses ?> <span class="fs-6 fw-normal">วิชา</span></h2>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa-solid fa-graduation-cap"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-white-50 fw-bold mb-1 fs-8">จำนวนสิทธิ์ผู้เรียน</h6>
                        <h2 class="fw-bold mb-0"><?= $totalEnrolled ?> <span class="fs-6 fw-normal">คน-สิทธิ์</span></h2>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-white-50 fw-bold mb-1 fs-8">อัตราการสำเร็จเรียนรู้</h6>
                        <h2 class="fw-bold mb-0"><?= $completionRate ?>%</h2>
                        <small class="text-white-50 fs-8">สำเร็จสะสม <?= $totalCompleted ?> สิทธิ์</small>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa-solid fa-circle-check"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-dark-50 fw-bold mb-1 fs-8">สถิติสอบผ่าน (Pass Rate)</h6>
                        <h2 class="fw-bold mb-0"><?= $averagePassRate ?>%</h2>
                        <small class="text-dark-50 fs-8">คิดจากการส่งแบบสอบถามสะสม</small>
                    </div>
                    <div class="fs-1 opacity-50"><i class="fa-solid fa-award"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 2: สถิติแยกตามหลักสูตร -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-table text-primary me-2"></i> สถิติข้อมูลจำแนกตามหลักสูตร (Course Analytics)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light fs-8 text-secondary">
                        <tr>
                            <th>ชื่อหลักสูตร</th>
                            <th class="text-center">จำนวนผู้ลงทะเบียน (คน)</th>
                            <th class="text-center">เรียนสำเร็จแล้ว (คน)</th>
                            <th class="text-center">อัตราการจบเรียน (%)</th>
                            <th class="text-center">จำนวนทำข้อสอบ (ครั้ง)</th>
                            <th class="text-center">คะแนนเฉลี่ยสอบ (%)</th>
                            <th class="text-center">ผ่านเกณฑ์สอบ (%)</th>
                            <th class="text-center" style="width: 120px;">รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody class="fs-7">
                        <?php foreach ($courseStats as $cStat): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= Html::encode($cStat['title']) ?></td>
                                <td class="text-center"><?= $cStat['enrolled'] ?></td>
                                <td class="text-center"><?= $cStat['completed'] ?></td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress w-50 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $cStat['completion_rate'] ?>%"></div>
                                        </div>
                                        <span><?= $cStat['completion_rate'] ?>%</span>
                                    </div>
                                </td>
                                <td class="text-center"><?= $cStat['attempts'] ?></td>
                                <td class="text-center fw-bold text-warning"><?= $cStat['avg_score'] ?>%</td>
                                <td class="text-center text-success fw-bold"><?= $cStat['pass_rate'] ?>%</td>
                                <td class="text-center">
                                    <?= Html::a('<i class="fa-solid fa-eye me-1"></i> ตรวจสอบ', ['view', 'id' => $cStat['id']], ['class' => 'btn btn-outline-primary btn-sm rounded-pill']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($courseStats)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">ยังไม่มีหลักสูตรอบรมใดๆ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 3: ประวัติการสอบล่าสุด -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-history text-warning me-2"></i> บันทึกรายงานการสอบหลังเรียนล่าสุด (Recent Quiz Attempts)</h5>
            
            <?= GridView::widget([
                'dataProvider' => $attemptsDataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle'],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => 'ลำดับ',
                        'headerOptions' => ['class' => 'text-center', 'style' => 'width: 60px;'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'label' => 'บุคลากรโรงพยาบาล',
                        'value' => function ($attempt) {
                            return $attempt->employee ? $attempt->employee->prefix . $attempt->employee->fname . ' ' . $attempt->employee->lname : 'ไม่พบข้อมูล';
                        }
                    ],
                    [
                        'label' => 'แผนก',
                        'value' => function ($attempt) use ($allDeps) {
                            if ($attempt->employee) {
                                return $allDeps[$attempt->employee->department] ?? $attempt->employee->department;
                            }
                            return '-';
                        }
                    ],
                    [
                        'label' => 'วิชาที่เรียนอบรม',
                        'value' => function ($attempt) {
                            return $attempt->course ? $attempt->course->title : 'ไม่พบข้อมูล';
                        }
                    ],
                    [
                        'attribute' => 'attempt_number',
                        'label' => 'ครั้งที่',
                        'headerOptions' => ['class' => 'text-center', 'style' => 'width: 80px;'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'score',
                        'label' => 'คะแนนที่ได้',
                        'headerOptions' => ['class' => 'text-center', 'style' => 'width: 100px;'],
                        'contentOptions' => ['class' => 'text-center fw-bold'],
                        'value' => function ($attempt) {
                            return "{$attempt->score} / {$attempt->total_questions}";
                        }
                    ],
                    [
                        'attribute' => 'percentage',
                        'label' => 'คิดเป็น %',
                        'headerOptions' => ['class' => 'text-center', 'style' => 'width: 100px;'],
                        'contentOptions' => ['class' => 'text-center fw-bold'],
                        'value' => function ($attempt) {
                            return "{$attempt->percentage}%";
                        }
                    ],
                    [
                        'attribute' => 'is_passed',
                        'label' => 'ผลลัพธ์ประเมิน',
                        'format' => 'raw',
                        'headerOptions' => ['class' => 'text-center', 'style' => 'width: 120px;'],
                        'contentOptions' => ['class' => 'text-center'],
                        'value' => function ($attempt) {
                            if ($attempt->is_passed) {
                                return '<span class="badge bg-success-subtle text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> ผ่านเกณฑ์</span>';
                            }
                            return '<span class="badge bg-danger-subtle text-danger fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i> ไม่ผ่านเกณฑ์</span>';
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'วันเวลาส่งคำตอบ',
                        'value' => function ($attempt) {
                            return Yii::$app->formatter->asDatetime($attempt->created_at, 'short');
                        }
                    ]
                ]
            ]) ?>
        </div>
    </div>
</div>
