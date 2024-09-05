<?php

use app\modules\sm\models\Product;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'วัสดุ';
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

<?php Pjax::begin(['id' => 'sm-container', 'timeout' => 3000]); ?>

<div class="row">

    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
                <div class="w-50">
                    <div class="d-flex justify-content-end gap-2 align-items-start">

                        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
                        <div class="dropdown float-end btn btn-sm btn-outline-primary">
                            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-gear fs-5"></i>
                            
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="bi bi-grid-fill me-1"></i>  ประเภทวัสดุ', ['/sm/product-type', 'title' => '<i class="bi bi-grid-fill"></i> ประเภทวัสดุ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            <?= Html::a('<i class="bi bi-grid-fill me-1"></i>  หน่วยนับ', ['/sm/product-unit','title' => 'หน่วยนับ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        </div>
                    </div>
                </div>
                    
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
            <h6><i class="bi bi-ui-checks"></i> ทั้งหมด <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                        <th style="width:500px">รายการ</th>
                        <th class="text-center" style="width:80px">หน่วย</th>
                        <th class="text-center" scope="col" style="width: 80px;">ดำเนินการ</th>
                        </thead>
                        <tbody>
                            <?php foreach ($dataProvider->getModels() as $model): ?>
                            <tr class="rounded">
                                <td scope="row">
                                    <?=$model->Avatar()?>
                                </td>
                                <td class="text-center"><?=(isset($model->data_json['unit']) ? $model->data_json['unit'] : '-')?></td>
                                <td class="text-center">
                                    <?=Html::a('<i class="fa-solid fa-eye"></i>',['/sm/product/view','id' => $model->id],['class' => 'btn btn-sm btn-primary rounded-pill open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square"></i>',['/sm/product/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'btn btn-sm btn-warning rounded-pill open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?=Html::a('<i class="fa-solid fa-trash"></i>', ['/sm/product/delete', 'id' => $model->id], [
    'class' => 'btn btn-sm btn-danger rounded-pill  delete-item',
])?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    <div class="text-muted">
                        <?= LinkPager::widget([
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