<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\purchase\models\Order;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$warehouse = Yii::$app->session->get('warehouse');
$this->title = 'รับเข้า' . $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = $this->title;
$createIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-plus-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6"/><path d="M6 12v6"/></svg>';

?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'stock_in'])?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'inventory-container', 'enablePushState' => true, 'timeout' => 88888888]); ?>
<?php
    // นับจำนวน order ที่รอรับเข้าคลัง
    $warehouseModel = Warehouse::findOne($warehouse->id);
    if (isset($warehouseModel->data_json['item_type'])) {
        $item = $warehouseModel->data_json['item_type'];
        $count = Order::find()
            ->where(['name' => 'order', 'status' => 5])
            ->andWhere(['IN', 'category_id', $item])
            ->andWhere(['!=', 'category_id', 'M25'])
            ->count();
    }
?>

<div class="card">
    <div class="card-body d-flex justify-content-between">
        <div class="d-flex gap-3">
            <?= Html::a($createIcon . 'สร้างรายการรับเข้า', ['/inventory/stock-in/create', 'name' => 'order', 'type' => 'IN', 'title' => '<i class="bi bi-ui-checks"></i> สร้างใบรับเข้า'], ['class' => 'btn btn-primary shadow open-modal position-relative', 'data' => ['size' => 'modal-md']]) ?>
            <?= $count > 0 ?  Html::a('<i class="fa-solid fa-bell"></i> รอรับเข้า <span class="badge text-bg-danger">' . $count . '</span>', ['/inventory/stock-in/list-pending-order', 'name' => 'order', 'title' => '<i class="bi bi-ui-checks"></i> รายการตรวจรับ'], ['class' => 'btn btn-warning shadow open-modal position-relative', 'data' => ['size' => 'modal-xl']]) : '' ?>
        </div>
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="stock-in-index">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6><i class="bi bi-ui-checks"></i> รับเข้าจำนวน <span
                        class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?></span> รายการ
                </h6>
                <div>
                    มูลค่า <span
                        class="fw-semibold badge rounded-pill text-bg-light fs-6"><?= $searchModel->SummaryTotal(false) ?></span>บาท
                </div>
            </div>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="fw-semibold" scope="col">รหัส/วันที่รับเข้าคลัง</th>
                    <th class="fw-semibold">เลขทะเบียนคุม/ประเภทวัสดุ</th>
                    <th class="fw-semibold">รับจาก</th>
                    <th class="fw-semibold">เจ้าหน้าที่</th>
                    <th style="width:130px" class="text-end fw-semibold">มูลค่า</th>
                    <th style="width:100px" class="text-center fw-semibold">สถานะ</th>
                    <th class="text-center fw-semibold" style="width:100px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php $row = 1;
                    foreach ($dataProvider->getModels() as $item): ?>
                <tr>
                    <td class="text-center"><?= $row++ ?></td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-semibold "><?= $item->code ?></span>
                            <?= $item->ViewReceiveDate(); ?>
                        </div>
                    </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span
                                class="fw-semibold "><?= isset($item->purchase) ? $item->purchase->pq_number : '-' ?></span>
                            <?= isset($item->data_json['asset_type_name']) ? $item->data_json['asset_type_name'] : '' ?>
                        </div>
                    </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <?= isset($item->purchase) ? ('<span class="fw-semibold ">' . $item->purchase->po_number . '</span>') : null ?>
                            <?= isset($item->data_json['vendor_name']) ? $item->data_json['vendor_name'] : '' ?>
                        </div>
                    <td><?= $item->CreateBy($item->ViewReceiveDate())['avatar']; ?></td>
                    </td>
                    <td class="text-end">
                        <span class="fw-semibold "><?= number_format($item->getTotalOrderPrice(), 2); ?>
                        </span>
                    </td>
                    <td class="text-center"><?= $item->viewStatus(); ?></td>

                    <td class="fw-light">
                        <div class="btn-group">
                            <?= Html::a('<i class="fa-regular fa-file-lines"></i>', ['/inventory/stock-in/view', 'id' => $item->id], ['class' => 'btn btn-light w-100']) ?>
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/inventory/stock-in/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>



<?php Pjax::end(); ?>