<?php

use app\models\Categorise;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Categorises';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-brands fa-line" style="color:#0ec863"></i> ตั้งค่า Line Notify
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?><?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>



<div class="categorise-index">

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <!-- <h5><i class="fa-brands fa-line" style="color:#0ec863"></i> ตั้งค่า Line Notify</h5> -->
            <?= Html::a('<i class="fa-solid fa-circle-plus me-2"></i>สร้างใหม่', ['/line-group/create', 'name' => 'line_group', 'title' => '<i class="fa-brands fa-line" style="color:#0ec863"></i> สร้าง Line Notify'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>

        </div>
    </div>
</div>

    <?php Pjax::begin(['id' => 'line-group-container']); ?>

    <div class="card">
        <div class="card-body">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'code',
            'title',
            [
                'header' => 'Token',
                'value' => function ($model) {
                    try {
                        return $model->data_json['token'];
                    } catch (\Throwable $th) {
                        // throw $th;
                    }
                }
            ],
            [
                'header' => 'กำเนินการ',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary me-2 open-modal', 'data' => ['size' => 'modal-md']])
                        . Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ]);
                }
            ],
        ],
    ]); ?>
        </div>
    </div>
    
    <?php Pjax::end(); ?>

</div>
