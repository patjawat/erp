<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\hr\models\ElearningAttempt;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $model */
/** @var yii\data\ActiveDataProvider $dataProvider */

?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-graduation-cap text-primary me-2"></i> ประวัติการเรียนรู้ผ่าน E-learning ของบุคลากร</h5>
        
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-hover align-middle border-0'],
            'summary' => false,
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'header' => 'ลำดับ',
                    'headerOptions' => ['class' => 'text-center', 'style' => 'width: 60px;'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'label' => 'ชื่อหลักสูตร',
                    'value' => function ($progress) {
                        return $progress->course ? $progress->course->title : 'ไม่พบข้อมูลหลักสูตร';
                    }
                ],
                [
                    'attribute' => 'status',
                    'label' => 'สถานะการเรียน',
                    'format' => 'raw',
                    'headerOptions' => ['class' => 'text-center', 'style' => 'width: 150px;'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($progress) {
                        if ($progress->status === 'completed') {
                            return '<span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle-check me-1"></i> จบการศึกษา</span>';
                        } elseif ($progress->status === 'learning') {
                            return '<span class="badge bg-info-subtle text-info"><i class="fa-solid fa-arrows-spin fa-spin me-1"></i> กำลังเรียน</span>';
                        }
                        return '<span class="badge bg-secondary-subtle text-secondary">ยังไม่เริ่มต้น</span>';
                    }
                ],
                [
                    'attribute' => 'started_at',
                    'label' => 'เริ่มเรียนเมื่อ',
                    'headerOptions' => ['class' => 'text-center', 'style' => 'width: 160px;'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($progress) {
                        return $progress->started_at ? Yii::$app->formatter->asDatetime($progress->started_at, 'short') : '-';
                    }
                ],
                [
                    'attribute' => 'completed_at',
                    'label' => 'เรียนสำเร็จเมื่อ',
                    'headerOptions' => ['class' => 'text-center', 'style' => 'width: 160px;'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($progress) {
                        return $progress->completed_at ? Yii::$app->formatter->asDatetime($progress->completed_at, 'short') : '-';
                    }
                ],
                [
                    'label' => 'คะแนนทดสอบหลังเรียนล่าสุด',
                    'format' => 'raw',
                    'headerOptions' => ['class' => 'text-center', 'style' => 'width: 180px;'],
                    'contentOptions' => ['class' => 'text-center'],
                    'value' => function ($progress) {
                        $attempt = ElearningAttempt::find()
                            ->where(['emp_id' => $progress->emp_id, 'course_id' => $progress->course_id])
                            ->orderBy(['attempt_number' => SORT_DESC])
                            ->one();
                        
                        if ($attempt) {
                            $statusClass = $attempt->is_passed ? 'text-success fw-bold' : 'text-danger';
                            $icon = $attempt->is_passed ? '<i class="fa-solid fa-circle-check text-success me-1"></i>' : '<i class="fa-solid fa-circle-xmark text-danger me-1"></i>';
                            
                            $scoreText = $icon . ' ' . Html::tag('span', "{$attempt->score}/{$attempt->total_questions} ({$attempt->percentage}%)", ['class' => $statusClass]);
                            
                            // ลิงก์ไปหน้าแสดงผลสอบ
                            $viewLink = Html::a('<i class="fa-solid fa-eye ms-2 text-primary fs-8" title="ดูผลคะแนน"></i>', ['/hr/elearning/quiz-result', 'id' => $attempt->id], ['data-pjax' => 0]);
                            
                            return $scoreText . $viewLink;
                        }
                        return '<span class="text-muted fs-8">ยังไม่ได้ทำข้อสอบ</span>';
                    }
                ]
            ]
        ]) ?>
        
    </div>
</div>
