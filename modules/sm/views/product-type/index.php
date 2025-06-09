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

$this->title = 'ตั้งค่าวัสดุ';
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

<?php Pjax::begin(['id' => 'sm-container','enablePushState' => false, 'timeout' => 3000]); ?>
<div class="row d-flex justify-content-center">
    <div class="col-8">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">

                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product-type/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> สร้างใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>

                    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
                </div>


                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" style="width:100px">รหัส</th>
                            <th>รายการ</th>
                            <th class="text-center" style="width:100px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $model):?>
                        <tr class="">
                            <td scope="row"><?=$model->code?></td>
                            <td><?=$model->title?></td>
                            <td class="text-center">
                                <?=Html::a('<i class="fa-solid fa-eye"></i>',['/sm/product-type/view','id' => $model->id],['class' => 'btn btn-sm btn-primary rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                                <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/sm/product-type/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>


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


            </div>
        </div>

    </div>
</div>
<?php Pjax::end(); ?>