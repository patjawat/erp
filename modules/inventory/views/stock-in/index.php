<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\purchase\models\Order;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$warehouse = Yii::$app->session->get('warehouse');
$this->title = 'รับเข้า' . $warehouse['warehouse_name'];

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs'][] = 'ทะเบียนรับเข้า';

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
<?= $this->render('../default/menu', ['active' => 'stock_in']) ?>
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
} else {
    $count = 0;
}
?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="stock-in-index">
    <div class="card">
        <div class="card-header bg-primary-gradient text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex flex-column">
                    <h6 class="text-white"><i class="bi bi-ui-checks"></i> รับเข้าจำนวน <span
                            class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?></span> รายการ
                    </h6>
                    <span class="fw-semibold badge rounded-pill text-bg-light fs-6 mb-0"><?= $searchModel->SummaryTotal(false) ?></span>
                </div>

                <div class="d-flex gap-3">
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/inventory/stock-in/create', 'name' => 'order', 'type' => 'IN', 'title' => '<i class="bi bi-ui-checks"></i> สร้างใบรับเข้า'], ['class' => 'btn btn-light shadow open-modal position-relative', 'data' => ['size' => 'modal-md']]) ?>
                    <?= $count > 0 ?  Html::a('<i class="fa-solid fa-bell"></i> รอรับเข้า <span class="badge text-bg-danger">' . $count . '</span>', ['/inventory/stock-in/list-pending-order', 'name' => 'order', 'title' => '<i class="bi bi-ui-checks"></i> รายการตรวจรับ'], ['class' => 'btn btn-warning shadow open-modal position-relative', 'data' => ['size' => 'modal-xl']]) : '' ?>
                </div>

            </div>
        </div>
        <div class="card-body">


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
                                <?= $item->viewMoveMentDate(); ?>
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
                        <td><?= $item->CreateBy($item->viewMoveMentDate())['avatar']; ?></td>
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold "><?= number_format($item->getTotalOrderPrice(), 2); ?>
                            </span>
                        </td>
                        <td class="text-center"><?= $item->viewStatus(); ?></td>

                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-regular fa-file-lines me-1"></i> แสดง', ['/inventory/stock-in/view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/inventory/stock-in/update', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?></li>

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