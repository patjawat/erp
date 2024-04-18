<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Whcheck $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Whchecks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="whcheck-view">

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
            'ref',
            'check_code',
            'check_date',
            'check_type',
            'check_store',
            'check_from',
            'check_hr',
            'check_status',
            'data_json',
            'updated_at',
            'created_at',
            'created_by',
            'updated_by',
        ],
    ]) ?>

</div>
