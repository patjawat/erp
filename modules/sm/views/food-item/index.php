<?php

use app\modules\sm\models\ProductType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'อาหารสด';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<div class="food-item-index">

    <?php Pjax::begin(['id' => 'food-container','enablePushState' => false]); ?>
    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between">
                <?= Html::a('<i class="bi bi-plus-circle"></i> สร้างใหม่', ['/sm/food-item/create', 'title' => '<i class="bi bi-plus-circle text-primary"></i> สร้างใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th class="text-center" scope="col" style="width: 100px;">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="">
                    <td scope="row"><?=$model->title?></td>
                    <td class="text-center">
                        <?=Html::a('<i class="bi bi-eye"></i>',['/sm/food-item/view','id' => $model->id],['class' => 'btn btn-sm btn-outline-primary open-modal','data' => ['size' => 'modal-md']])?>
                        <?=Html::a('<i class="bi bi-pencil-square"></i>',['/sm/food-item/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไข'],['class' => 'btn btn-sm btn-outline-secondary open-modal','data' => ['size' => 'modal-md']])?>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>


    <div class="pt-2">
    <?= app\components\widgets\DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
</div>



    </div>
    </div>

    <?php Pjax::end(); ?>

</div>