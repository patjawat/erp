<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\pm\models\Projects;
/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="projects-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Projects', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'description:ntext',
            'emp_id',
            'status',
            //'start_date',
            //'end_date',
            //'percentage_complete',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Projects $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>

<div class="container">
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Office Management
                    </h5>
                    <p class="card-text">
                        An office management app project streamlines administrative tasks by integrating tools for
                        scheduling, communication, and task management, enhancing...
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img alt="Profile picture of Anthony Lewis" class="avatar me-2"
                                src="https://placehold.co/30x30" />
                            <div>
                                <div>
                                    Anthony Lewis
                                </div>
                                <div class="text-muted">
                                    Project Leader
                                </div>
                            </div>
                        </div>
                        <div class="text-muted">
                            Deadline
                            <br />
                            14 Jan 2024
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tasks task-icon me-2">
                        </i>
                        <div>
                            Tasks: 6/10
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <div class="avatar bg-light text-muted d-flex align-items-center justify-content-center">
                            +1
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Office Management
                    </h5>
                    <p class="card-text">
                        An office management app project streamlines administrative tasks by integrating tools for
                        scheduling, communication, and task management, enhancing...
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img alt="Profile picture of Anthony Lewis" class="avatar me-2"
                                src="https://placehold.co/30x30" />
                            <div>
                                <div>
                                    Anthony Lewis
                                </div>
                                <div class="text-muted">
                                    Project Leader
                                </div>
                            </div>
                        </div>
                        <div class="text-muted">
                            Deadline
                            <br />
                            14 Jan 2024
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tasks task-icon me-2">
                        </i>
                        <div>
                            Tasks: 6/10
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <div class="avatar bg-light text-muted d-flex align-items-center justify-content-center">
                            +1
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        Office Management
                    </h5>
                    <p class="card-text">
                        An office management app project streamlines administrative tasks by integrating tools for
                        scheduling, communication, and task management, enhancing...
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img alt="Profile picture of Anthony Lewis" class="avatar me-2"
                                src="https://placehold.co/30x30" />
                            <div>
                                <div>
                                    Anthony Lewis
                                </div>
                                <div class="text-muted">
                                    Project Leader
                                </div>
                            </div>
                        </div>
                        <div class="text-muted">
                            Deadline
                            <br />
                            14 Jan 2024
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-tasks task-icon me-2">
                        </i>
                        <div>
                            Tasks: 6/10
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <img alt="Profile picture of team member" class="avatar me-1"
                            src="https://placehold.co/30x30" />
                        <div class="avatar bg-light text-muted d-flex align-items-center justify-content-center">
                            +1
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>