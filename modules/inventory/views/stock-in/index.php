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
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="layout-grid"></i>  
        <?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu',['active' => 'stock-in']) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'inventory-container', 'enablePushState' => true, 'timeout' => 88888888]); ?>
<?php
// นับจำนวน order ที่รอรับเข้าคลัง
$warehouseModel = Warehouse::findOne($warehouse->id);
$dataJson = $warehouseModel->data_json;
if (is_string($dataJson)) {
    $dataJson = json_decode($dataJson, true) ?? [];
}
if (isset($dataJson['item_type'])) {
    $item = $dataJson['item_type'];
    $item = is_array($item) ? $item : (array) $item;
    $item = array_diff($item, ['M25']);
    $query = Order::find()
        ->where(['name' => 'order', 'status' => 5])
        ->andWhere(['IN', 'category_id', $item]);
        // echo $query->createCommand()->rawSql;
        $count = $query->count();
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
                            class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?></span>
                        รายการ
                    </h6>
                    <span
                        class="fw-semibold badge rounded-pill text-bg-light fs-6 mb-0"><?= $searchModel->SummaryTotal(false) ?></span>
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
                    <th scope="col">รหัส/วันที่รับเข้าคลัง</th>
                    <th>คลังหลัก</th>
                    <th>เลขทะเบียนคุม/ประเภทวัสดุ</th>
                    <th>รับจาก</th>
                    <th>เจ้าหน้าที่</th>
                    <th style="width:130px" class="text-end">มูลค่า</th>
                    <th style="width:100px" class="text-center">สถานะ</th>
                    <th class="text-center" style="width:100px">ดำเนินการ</th>
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
                        <td><?=$item->warehouse->warehouse_name?></td>
                        <td class="fw-light align-middle">
                            <div class=" d-flex flex-column">
                                <?php if (isset($item->purchase)): ?>
                                    <span
                                        class="fw-semibold "><?= $item->purchase->pq_number ?></span>
                                <?php endif; ?>
                                <?= $item->assetType?->title ?? '-' ?>
                            </div>
                        </td>
                        <td class="fw-light align-middle">
                            <div class=" d-flex flex-column">
                                <?= isset($item->purchase) ? ('<span class="fw-semibold ">' . $item->purchase->po_number . '</span>') : null ?>
                                <?= isset($item->vendor) ? $item->vendor->title : '' ?>
                            </div>
                        <td><?= $item->CreateBy($item->viewMoveMentDate())['avatar']; ?></td>
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold ">
                            <?php 
                                    $price = $item->getTotalOrderPrice();
                                    // ตรวจสอบว่าเป็นตัวเลขหรือไม่ และไม่เป็นค่าว่าง
                                    if (is_numeric($price)) {
                                        echo number_format($price, 2);
                                    } else {
                                        echo "0.00";
                                    }
                                ?>    
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
                                    <li><?= Html::a('<i class="fa-regular fa-file-lines me-1"></i> แสดง', ['/inventory/stock-in/view', 'id' => $item->id], ['class' => 'dropdown-item']) ?>
                                    </li>
                                    <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/inventory/stock-in/update', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    </li>

                                </ul>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
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


<?php Pjax::end(); ?>