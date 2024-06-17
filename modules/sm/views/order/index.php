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
$this->title = 'บริหารพัสดุ';
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

<?php Pjax::begin(['id' => 'order-container']); ?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex gap-3 justify-content-start align-middle">
            <!-- <div class="btn-group">
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
                </div> -->
            <h6><i class="fa-solid fa-list-check"></i> ทะเบียนคุม</h6>

        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>

    </div>
</div>

<div class="card">
    <div class="card-body">


        <div class="table-responsive" style="height:800px">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="fw-semibold">สถานะ</th>
                        <th class="fw-semibold">ผู้ขอซื้อ</th>
                        <th class="fw-semibold">รหัสขอซื้อ (PR)</th>
                        <th class="fw-semibold">เลขทะเบียนคุม</th>
                        <th class="fw-semibold">เลขที่สั่งซื้อ (PO)</th>
                        <th class="fw-semibold">ผู้จำหน่าย</th>
                        <th class="fw-semibold">ความคืบหน้า</th>
                        <th class="fw-semibold">หมายเหตุ</th>
                        <th class="fw-semibold text-start">วันที่ขอซื้อ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td class="fw-light">
                            <div class="btn-group">
                                <?= Html::a('<i class="bi bi-clock"></i> ' . $model->viewStatus(), ['/sm/order/view', 'id' => $model->id], ['class' => 'btn btn-light']) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/sm/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> สร้างใบสั่งซื้อ', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                    </li>
                                </ul>
                            </div>

                        </td>
                        <td class="fw-light"><?= $model->getUserReq()['avatar'] ?></td>
                        <td class="fw-light align-middle">
                            <div class="d-flex flex-column">
                                <?= Html::a($model->pr_number, ['/sm/order/view', 'id' => $model->id, 'name' => 'pr'], ['class' => 'fw-bolder open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                <?php
                                try {
                                    echo $model->data_json['product_type_name'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </div>
                        </td>
                        <td class="fw-light align-middle">
                            <?= Html::a($model->pq_number, ['/purchase/pq-order/view', 'id' => $model->id], ['class' => 'fw-bolder']) ?>
                        </td>
                        <td>-</td>
                        <td class="fw-light align-middle">
                            <?php
                            try {
                                $model->vendor->title;
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>

                        </td>

                        <td class="fw-light align-middle">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" role="progressbar" aria-label="Progress" style="width: 50%;"
                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </td>
                        <td class="fw-light align-middle"><?php //  $model->data_json['comment'] ?></td>
                        <td class="fw-light align-middle">
                            <?= $model->viewCreatedAt() ?>
                            <div class="text-muted fs-13">4 วันที่แล้ว</div>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Pjax::end(); ?>