<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<div class="card">
        <div
            class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
            <div class="d-flex justify-content-start gap-2">
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่',
                    'url' =>['/am/fsn/create'],
                    'modal' => true,
                    'size' => 'lg',
            ])?>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
   
            </div>

            <div class="d-flex gap-2">
            <?=Html::a('<i class="bi bi-list-ul"></i>',['index','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['index','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
                <?=Html::a('<i class="fa-solid fa-gear"></i>', ['/am/fsn/group-setting'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
            </div>

        </div>
    </div>

<div class="fsn-view">

    <h1><?= Html::encode($this->title) ?></h1>



    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'code',
            'title',
            'description',
            'active',
        ],
    ]) ?>

</div>
