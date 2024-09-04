<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Stock $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock Movements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body d-flex justify-content-between">
        <h5>title</h5>
    <div>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </div>
    </div>
</div>


   

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'rq_number',
        ],
    ]) ?>

<div class="d-flex justify-content-between">
                <h6><i class="fa-solid fa-file-circle-plus"></i> รายการ</h6>
                <div>
                    <!-- <button class="btn btn-sm btn-primary rounded-pill"><i class="fa-solid fa-plus"></i>
                                เลือกรายการ</button> -->
                    <?= Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/inventory/stock-movement/list-in-stock', 'name' => 'issue', 'title' => '<i class="fa-regular fa-pen-to-square"></i> ขอเบิกวัสดุ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                </div>

            </div>
