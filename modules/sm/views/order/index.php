<?php

use app\modules\sm\models\Inventory;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\InventorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ขอซื้อขอจ้าง';
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

<?php Pjax::begin(['id' => 'sm-container']); ?>

<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex gap-3 justify-content-start">
            <div class="btn-group">
            <?= Html::a('<i class="fa-solid fa-business-time"></i> ใบขอซื้อ', ['/sm/order', 'name' => 'pr'], ['class' => 'btn btn-light']) ?>
                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><?= Html::a('<i class="fa-solid fa-circle-plus text-primary me-1"></i> สร้างใบขอซื้อ', ['/sm/order/create', 'name' => 'pr', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มขอซื้อ-ขอจ้าง (PR)'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    </li>
                </ul>
            </div>
            <div class="btn-group">
            <?= Html::a('<i class="fa-solid fa-basket-shopping"></i> ใบสั่งซื้อ', ['/sm/order', 'name' => 'po'], ['class' => 'btn btn-light']) ?>
                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><?= Html::a('<i class="fa-solid fa-circle-plus text-primary me-1"></i> สร้างใบสั่งซื้อ', ['/sm/order/create', 'name' => 'po', 'status' => 5, 'title' => '<i class="bi bi-plus-circle"></i> สร้างใบสั่งซื้อ (PO)'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    </li>
                </ul>
            </div>

        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>

    </div>
</div>

<div class="inventory-index">
   
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
        <div class="card-body p-1">

            <div class="table-responsive">
                <table class="table table-primary">
                    <thead>
                        <tr>
                            <th style="width:150px">สถานะ</th>
                            <th>เลขที่</th>
                            <th>ผู้แทนจำหน่าย</th>
                            <th>ราคารวม</th>
                            <th>วันที่ขอ</th>
                            <th width="100px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr class="">
                            <td><?= $model->viewPrStatus() ?></td>
                            <td scope="row">
                                <?= Html::a($model->code, ['view', 'id' => $model->id]) ?>
                            </td>
                            <td>R1C2</td>
                            <td>R1C3</td>
                            <td>R1C3</td>
                            <td>
                                <?= Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $model->id]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    
</div>
<?php Pjax::end(); ?>