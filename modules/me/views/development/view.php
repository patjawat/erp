<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */


use yii\widgets\DetailView;
/** @var yii\web\View $this */
$this->title = 'อบรม/ประชุม/ดูงาน';
$this->title = $model->topic;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?=$this->title?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">

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
          
        ],
    ]) ?>
    </div>
</div>

<div class="development-view">


</div>
