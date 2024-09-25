<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeavePermission $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Leave Permissions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="leave-permission-view">

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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'emp_id',
            'service_time:datetime',
            'point',
            'last_point',
            'new_point',
            'data_json',
            'thai_year',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'deleted_at',
            'deleted_by',
        ],
    ]) ?>

</div>
