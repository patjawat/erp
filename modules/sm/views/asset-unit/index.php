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

$this->title = 'Product Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-type-index">

    <?php Pjax::begin(['enablePushState' => false]); ?>
    <div class="d-flex justify-content-between">

    <?= Html::a('<i class="bi bi-plus-circle"></i> สร้างใหม่', ['/sm/product-type/create', 'title' => '<i class="bi bi-plus-circle text-primary"></i> สร้างใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>

        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>

    <div
        class="table-responsive"
    >
        <table
            class="table table-primary"
        >
            <thead>
                <tr>
                    <th scope="col">รายการ</th>
                    <th scope="col">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="">
                    <td scope="row"><?=$model->title?></td>
                    <td class="text-center">
                                    <?=Html::a('<i class="bi bi-eye"></i>',['/sm/product-type/view','id' => $model->id],['class' => 'btn btn-sm btn-outline-primary open-modal','data' => ['size' => 'modal-md']])?>
                                    <?=Html::a('<i class="bi bi-pencil-square"></i>',['/sm/product-type/update','id' => $model->id,'title' => '<i class="bi bi-pencil-square"></i> แก้ไข'],['class' => 'btn btn-sm btn-outline-secondary open-modal','data' => ['size' => 'modal-md']])?>
                                </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    

    <div class="pt-2">
    <?= app\components\widgets\DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
</div>

    <?php Pjax::end(); ?>

</div>
