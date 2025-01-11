<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\Projects $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="projects-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="row">
        <div class="row">
            <div class="col-4">
                <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'description:ntext',
            'emp_id',
            'status',
            'start_date',
            'end_date',
            'percentage_complete',
        ],
    ]) ?>
            </div>
            <div class="col-8">

            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <h4>Project Tasks</h4>
                    <?=Html::a('Create Task',['/pm/project-tasks/create','project_id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างขั้นตอนการดำเนินการ'],['class' => 'btn btn-primary open-modal'])?>
                </div>

                <div class="row row-cols-1 row-cols-sm-4 row-cols-md-4 g-3 mt-2">
                    <?php foreach($model->todos as $task):?>
                    <div class="col mt-1">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title"> <?=$task->task_name?></h4>
                                <p class="card-text">Body</p>
                            </div>
                        </div>

                    </div>
                    <?php endforeach;?>
                </div>

            </div>
        </div>
    </div>


</div>