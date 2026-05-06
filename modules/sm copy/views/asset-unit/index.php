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

    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product-type/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> สร้างใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>

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
                                    <?=Html::a('<i class="fa-solid fa-eye"></i>',['/sm/product-type/view','id' => $model->id],['class' => 'btn btn-sm btn-primary rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/sm/product-type/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                                </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
    

    <div class="d-flex justify-content-center">
                    <div class="text-muted">
                        <?= yii\bootstrap5\LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => [
                                'listOptions' => 'pagination pagination-sm',
                                'class' => 'pagination-sm',
                            ],
                        ]); ?>
                    </div>
                </div>

    <?php Pjax::end(); ?>

</div>
