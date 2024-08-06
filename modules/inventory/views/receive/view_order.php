<?php
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;

?>
<style>
.popover-x {
    display: none !Important;
}
</style>
<?php Pjax::begin(['id' => 'order_item']); ?>
<!-- 
<div class="card border border-primary">
    <div class="d-flex p-3">
        <img class="avatar" src="/img/placeholder-img.jpg" alt="">
        <div class="avatar-detail">
            <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top">
                <?= isset($model->data_json['vendor_name']) ? $model->data_json['vendor_name'] : '' ?>
            </h6>
            <p class="text-primary mb-0 fs-13">
                <?=isset($model->data_json['vendor_address']) ? $model->data_json['vendor_address'] : '-'?></p>
        </div>
    </div>
    <div class="card-body pb-1">
        <table class="table table-sm table-striped-columns">
            <tbody>
                <tr class="">
                    <td style="width: 150px;">กำหนดวันส่งมอบ</td>
                    <td><?php // $model->data_json['delivery_date']?></td>
                </tr>
                <tr class="">
                    <td style="width: 108px;">ใบสั่งซื้อเลขที่</td>
                    <td><?=$model->po_number?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted d-flex justify-content-between">
        <p>ผู้ขาย</p>

    </div>
</div> -->

<?php
                            try {
                                $orderTypeName =  $model->data_json['order_type_name'];
                            } catch (\Throwable $th) {
                                $orderTypeName = '';
                            }
                        ?>
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-primary">
            <tr>
                <th style="width:500px">
                    รายการ
                </th>
                <th class="text-center">หน่วย</th>
                <th class="text-center">มูลค่า</th>
                <th class="text-center">จำนวนรับ</th>
                <th class="text-center">ราคาต่อหน่วย</th>
                <th class="text-end">ล็อตผลิต</th>
                <th class="text-center">วันผลิต</th>
                <th class="text-end">วันหมดอายุ</th>
                <th class="text-center" scope="col" style="width: 120px;">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php foreach ($model->ListOrderItems() as $item): ?>
            <tr class="">
                <td class="align-middle">
                    <?php
                            try {
                                echo $item->product->Avatar();
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>
                </td>

                <td class="align-middle text-center">
                    <?php
                        try {
                            echo $item->product->data_json['unit'];
                        } catch (\Throwable $th) {

                        }
                        ?>
                </td>
                <td class="align-middle text-end fw-semibold">
                    <?php
                            try {
                                echo number_format($item->price, 2);
                            } catch (\Throwable $th) {

                            }
                            ?>
                </td>
                <td class="align-middle text-center"><?= $item->qty ?></td>
                <td class="align-middle text-center"><?= $item->qty ?></td>
                <td class="align-middle text-center"><?= $item->qty ?></td>
                <td class="align-middle text-center"><?= $item->qty ?></td>
                <td class="align-middle text-end">
                    <div class="d-flex justify-content-end fw-semibold">
                        <?php
                                try {
                                    echo number_format(($item->qty * $item->price), 2);
                                } catch (\Throwable $th) {
                                    // throw $th;
                                }
                                ?>
                    </div>
                </td>
                <td class="align-middle">
                    <div class="d-flex justify-content-center gap-2">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/receive/update-order-item', 'id' => $item->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> รับเข้า'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

</div>
<?php Pjax::end() ?>