<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var app\modules\inventory\models\StockMovement $model */
/** @var yii\web\View $this */

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

<!-- <div class="card">
    <div class="card-body">
<h5>ขอเบิกวัสดุ</h5>
    </div>
</div> -->

<div class="row">
<div class="col-8">
<?=$this->render('list_product',['model' => $model])?>
</div>
<div class="col-4">
<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
                    <h6><i class="fa-solid fa-file-lines"></i> ขอเบิกวัสดุ</h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-regular fa-eye me-1 text-primary"></i> แสดง', ['/inventory/receive/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                                'class' => 'dropdown-item  delete-item',
                            ]) ?>
                        </div>
                    </div>
                </div>


        <table class="table table-striped-columns">
<tbody>

    <tr>
        <td class="text-end">รหัสขอเบิก</td>
        <td colspan="2"><?=$model->rq_number?></td>
        <td class="text-end">วันที่</td>
        <td colspan="2"><?=$model->viewCreateDate()?></td>
    </tr>
    <tr>
        <td class="text-end">ผู้ขอ</td>
        <td colspan="5"><?=$model->CreateBy()['avatar']?></td>
        
    </tr>
</tbody>
        </table>
    <?php
     DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'รหัสขอเบิก',
                'value' => function($model){
                    return $model->rq_number;
                }
            ],
            [
                'label' => 'วันที่',
                'value' => function($model){
                    return $model->rq_number;
                }
            ],
            [
                'label' => 'ผู้ขอ',
                'value' => function($model){
                    return $model->rq_number;
                }
            ]

        ],
    ]) ?>

</div>
</div>
</div>
</div>

    

</div>
