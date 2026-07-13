<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\ElearningCourse $model */
/** @var yii\data\ActiveDataProvider $materialsProvider */
/** @var yii\data\ActiveDataProvider $questionsProvider */
/** @var yii\data\ActiveDataProvider $progressProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees']];
$this->params['breadcrumbs'][] = ['label' => 'จัดการ E-learning', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$targetDeps = json_decode($model->target_departments, true) ?: [];
$depNames = [];
$allDeps = \app\modules\hr\models\Employees::ListDepartment();
foreach ($targetDeps as $depId) {
    if (isset($allDeps[$depId])) {
        $depNames[] = $allDeps[$depId];
    }
}
?>

<div class="elearning-admin-view">
    <?php $this->beginBlock('page-title'); ?>
    รายละเอียดหลักสูตร
    <?php $this->endBlock(); ?>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <?= Html::a('<i class="fa-solid fa-pencil me-1"></i> แก้ไขหลักสูตร', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary rounded-pill px-3']) ?>
        <?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบหลักสูตร', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger rounded-pill px-3',
            'data' => [
                'confirm' => 'ยืนยันการลบหลักสูตรนี้และข้อมูลที่เกี่ยวข้องทั้งหมด?',
                'method' => 'post',
            ],
        ]) ?>
    </div>

    <?php $this->beginBlock('navbar_menu'); ?>
    <?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
    <?php $this->endBlock(); ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-9">
                    <h3 class="text-primary fw-bold mb-2"><?= Html::encode($model->title) ?></h3>
                    <p class="text-secondary"><?= nl2br(Html::encode($model->description)) ?></p>
                </div>
                <div class="col-md-3 border-start">
                    <div class="ps-3">
                        <div class="mb-2">
                            <span class="fs-8 text-muted d-block">สถานะ:</span>
                            <span class="badge <?= $model->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> px-2 py-1">
                                <?= $model->is_active ? 'เปิดสอนปกติ' : 'ปิดการเรียนชั่วคราว' ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="fs-8 text-muted d-block">เกณฑ์การสอบผ่าน:</span>
                            <span class="badge bg-warning-subtle text-warning px-2 py-1"><?= $model->passing_score_percent ?>% ของคะแนนรวม</span>
                        </div>
                        <div class="mb-2">
                            <span class="fs-8 text-muted d-block">กลุ่มเป้าหมาย (แผนก):</span>
                            <?php if (empty($targetDeps)): ?>
                                <span class="badge bg-light text-dark px-2 py-1">หลักสูตรทั่วไป</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark px-2 py-1" title="<?= implode(', ', $depNames) ?>">
                                    <?= count($depNames) ?> แผนก
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วน Tabs จัดการ -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs nav-tabs-custom card-header-tabs m-0 border-0" id="elearningTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-3 fw-bold" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" type="button" role="tab">
                        <i class="fa-solid fa-photo-film text-primary me-2"></i> สื่อการเรียนรู้ (<?= $materialsProvider->getTotalCount() ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-3 fw-bold" id="quiz-tab" data-bs-toggle="tab" data-bs-target="#quiz" type="button" role="tab">
                        <i class="fa-solid fa-file-circle-question text-warning me-2"></i> ข้อสอบหลังเรียน (<?= $questionsProvider->getTotalCount() ?> ข้อ)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-3 fw-bold" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">
                        <i class="fa-solid fa-users-viewfinder text-success me-2"></i> สถานะบุคลากรที่เข้าเรียน
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="elearningTabsContent">
                
                <!-- Tab 1: สื่อการเรียนรู้ -->
                <div class="tab-pane fade show active" id="materials" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-secondary">สื่อการเรียนรู้หลักสูตร</h5>
                        <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มวิดีโอ/ไฟล์ PDF', ['add-material', 'course_id' => $model->id], ['class' => 'btn btn-primary btn-sm rounded-pill px-3']) ?>
                    </div>
                    
                    <?= GridView::widget([
                        'dataProvider' => $materialsProvider,
                        'tableOptions' => ['class' => 'table table-hover align-middle border-0'],
                        'summary' => false,
                        'columns' => [
                            [
                                'attribute' => 'sort_order',
                                'label' => 'ลำดับ',
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 80px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'title',
                                'label' => 'ชื่อสื่อการสอน',
                                'format' => 'raw',
                                'value' => function ($material) {
                                    $icon = '<i class="fa-regular fa-file-pdf text-danger me-2 fs-5"></i>';
                                    if ($material->type === 'video_url') {
                                        $icon = '<i class="fa-brands fa-youtube text-danger me-2 fs-5"></i>';
                                    } elseif ($material->type === 'slide_link') {
                                        $icon = '<i class="fa-regular fa-file-powerpoint text-warning me-2 fs-5"></i>';
                                    }
                                    return $icon . ' ' . Html::encode($material->title);
                                }
                            ],
                            [
                                'attribute' => 'type',
                                'label' => 'ประเภท',
                                'headerOptions' => ['style' => 'width: 120px;'],
                                'value' => function ($material) {
                                    $types = [
                                        'video_url' => 'วิดีโอลิงก์',
                                        'pdf_file' => 'ไฟล์ PDF',
                                        'slide_link' => 'สไลด์ประกอบ',
                                    ];
                                    return $types[$material->type] ?? $material->type;
                                }
                            ],
                            [
                                'attribute' => 'file_path',
                                'label' => 'ลิงก์/ที่อยู่ไฟล์',
                                'format' => 'raw',
                                'value' => function ($material) {
                                    return Html::a(Html::encode(mb_strimwidth($material->file_path, 0, 40, '...')), $material->file_path, ['target' => '_blank', 'class' => 'text-decoration-none']);
                                }
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'จัดการ',
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 120px;'],
                                'contentOptions' => ['class' => 'text-center'],
                                'template' => '{update} {delete}',
                                'buttons' => [
                                    'update' => function ($url, $material) {
                                        return Html::a('<i class="fa-solid fa-pencil"></i>', ['update-material', 'id' => $material->id], ['class' => 'btn btn-sm btn-outline-primary me-1', 'title' => 'แก้ไข']);
                                    },
                                    'delete' => function ($url, $material) {
                                        return Html::a('<i class="fa-solid fa-trash"></i>', ['delete-material', 'id' => $material->id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'title' => 'ลบ',
                                            'data' => [
                                                'confirm' => 'ต้องการลบสื่อชิ้นนี้?',
                                                'method' => 'post',
                                            ],
                                        ]);
                                    }
                                ]
                            ]
                        ]
                    ]) ?>
                </div>

                <!-- Tab 2: ข้อสอบหลังเรียน -->
                <div class="tab-pane fade" id="quiz" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-secondary">ชุดข้อสอบหลังเรียน (Post-test)</h5>
                        <?= Html::a('<i class="fa-solid fa-plus me-1"></i> เพิ่มโจทย์ข้อสอบ', ['add-question', 'course_id' => $model->id], ['class' => 'btn btn-warning btn-sm rounded-pill px-3 text-dark fw-bold']) ?>
                    </div>

                    <div class="questions-list">
                        <?php foreach ($questionsProvider->getModels() as $index => $question): ?>
                            <div class="card border-0 bg-light p-3 mb-3 shadow-none">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-secondary mb-0">ข้อที่ <?= $index + 1 ?>: <?= Html::encode($question->question_text) ?></h6>
                                    <div>
                                        <?= Html::a('<i class="fa-solid fa-pencil"></i> แก้ไข', ['update-question', 'id' => $question->id], ['class' => 'btn btn-link btn-sm text-primary p-0 me-2']) ?>
                                        <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['delete-question', 'id' => $question->id], [
                                            'class' => 'btn btn-link btn-sm text-danger p-0',
                                            'data' => [
                                                'confirm' => 'ยืนยันลบคำถามข้อนี้?',
                                                'method' => 'post',
                                            ]
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="row ps-3">
                                    <?php foreach ($question->answers as $ans): ?>
                                        <div class="col-md-6 py-1.5 fs-7">
                                            <i class="fa-regular <?= $ans->is_correct ? 'fa-circle-check text-success' : 'fa-circle text-muted' ?> me-2"></i>
                                            <span class="<?= $ans->is_correct ? 'text-success fw-bold' : 'text-secondary' ?>"><?= Html::encode($ans->answer_text) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($questionsProvider->getTotalCount() == 0): ?>
                            <div class="text-center py-4">
                                <span class="fs-1 text-muted"><i class="fa-solid fa-question"></i></span>
                                <p class="text-muted mt-2">ยังไม่มีแบบทดสอบหลังเรียนในหลักสูตรนี้</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab 3: รายงานความก้าวหน้าผู้เรียน -->
                <div class="tab-pane fade" id="students" role="tabpanel">
                    <h5 class="fw-bold mb-3 text-secondary">ความก้าวหน้าการเรียนของบุคลากรรายบุคคล</h5>
                    
                    <?= GridView::widget([
                        'dataProvider' => $progressProvider,
                        'tableOptions' => ['class' => 'table table-hover align-middle'],
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'header' => 'ลำดับ',
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 60px;'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'label' => 'ชื่อ-นามสกุล บุคลากร',
                                'value' => function ($progress) {
                                    return $progress->employee ? $progress->employee->prefix . $progress->employee->fname . ' ' . $progress->employee->lname : 'ไม่พบข้อมูล';
                                }
                            ],
                            [
                                'label' => 'แผนก/หน่วยงาน',
                                'value' => function ($progress) {
                                    if ($progress->employee) {
                                        $allDeps = Employees::ListDepartment();
                                        return $allDeps[$progress->employee->department] ?? $progress->employee->department;
                                    }
                                    return '-';
                                }
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'สถานะการเรียน',
                                'format' => 'raw',
                                'value' => function ($progress) {
                                    if ($progress->status === 'completed') {
                                        return '<span class="badge bg-success-subtle text-success"><i class="fa-solid fa-check me-1"></i> จบการศึกษา</span>';
                                    } elseif ($progress->status === 'learning') {
                                        return '<span class="badge bg-info-subtle text-info"><i class="fa-solid fa-arrows-spin fa-spin me-1"></i> กำลังเรียน</span>';
                                    }
                                    return '<span class="badge bg-secondary-subtle text-secondary">ยังไม่เริ่มต้น</span>';
                                }
                            ],
                            [
                                'attribute' => 'started_at',
                                'label' => 'เริ่มเรียนเมื่อ',
                                'value' => function ($progress) {
                                    return $progress->started_at ? Yii::$app->formatter->asDatetime($progress->started_at, 'short') : '-';
                                }
                            ],
                            [
                                'attribute' => 'completed_at',
                                'label' => 'เรียนสำเร็จเมื่อ',
                                'value' => function ($progress) {
                                    return $progress->completed_at ? Yii::$app->formatter->asDatetime($progress->completed_at, 'short') : '-';
                                }
                            ],
                            [
                                'label' => 'ผลสอบหลังเรียน',
                                'format' => 'raw',
                                'value' => function ($progress) use ($model) {
                                    // ค้นหาการสอบล่าสุดของพนักงานในหลักสูตรนี้
                                    $attempt = ElearningAttempt::find()
                                        ->where(['emp_id' => $progress->emp_id, 'course_id' => $model->id])
                                        ->orderBy(['attempt_number' => SORT_DESC])
                                        ->one();
                                    
                                    if ($attempt) {
                                        $statusClass = $attempt->is_passed ? 'text-success fw-bold' : 'text-danger';
                                        $icon = $attempt->is_passed ? '<i class="fa-solid fa-certificate text-success me-1"></i>' : '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
                                        return $icon . ' ' . Html::tag('span', "{$attempt->score}/{$attempt->total_questions} ({$attempt->percentage}%)", ['class' => $statusClass]);
                                    }
                                    return '<span class="text-muted fs-8">ยังไม่ได้สอบ</span>';
                                }
                            ]
                        ]
                    ]) ?>
                </div>

            </div>
        </div>
    </div>
</div>
