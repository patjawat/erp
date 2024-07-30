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
                    <?= Html::a('<i class="fa-solid fa-circle-plus text-white"></i> เลือกรายการ', ['/purchase/order/product-list', 'order_id' => $model->id, 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เลือกรายการ '.$orderTypeName ], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal shadow', 'data' => ['size' => 'modal-lg']]) ?>
                </th>
                <th class="text-center" style="width:80px">หน่วย</th>
                <th class="text-end">ราคาต่อหน่วย</th>
                <th class="text-center" style="width:80px">จำนวน</th>
                <th class="text-end">จำนวนเงิน</th>
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
                <td class="align-middle text-center">
                    <?= $item->qty ?>
                </td>
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
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/purchase/order/update-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/purchase/order/delete-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger rounded-pill delete-item']) ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
    <div class="row justify-content-end">
        <div class="col-8">

            

        </div>
        <div class="col-4">
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr class="">
                            <td>รวมเงิน</td>
                            <td class="text-end"><span class="fw-semibold"><?= number_format($model->calculateVAT()['priceBeforeDiscount'],2); ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>ส่วนลดสินค้า(เป็นเงิน)</td>
                            <td class="text-end"><span
                                    class="fw-semibold"><?= Html::a(($model->discount_price == '' ?  '0.00' : number_format($model->discount_price,2)),['/purchase/order/discount','id' => $model->id,'title' => '<i class="bi bi-currency-exchange"></i> กำหนดราคาส่วนลด'],['class' => 'open-modal','data' => ['size' => 'modal-sm']]); ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>เงินหลังหักส่วนลด</td>
                            <td class="text-end"><span class="fw-semibold"><?= number_format($model->calculateVAT()['priceAfterDiscount'],2) ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>ภาษีมูลค่าเพิ่ม 7% (<code><?= $model->vatName()?></code>)</td>
                            <td class="text-end"><span
                                    class="fw-semibold"><?= Html::a(number_format($model->calculateVAT()['vatAmount'],2),['/purchase/order/form-vat','id' => $model->id,'title' => '<i class="bi bi-currency-exchange"></i> กำหนดภาษี'],['class' => 'open-modal','data' => ['size' => 'modal-sm']]); ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>จำนวนเงินทั้งสิ้น</td>
                            <td class="text-end"><span class="fw-semibold"><?= number_format($model->calculateVAT()['priceAfterVAT'],2) ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php

?>
        </div>
    </div>
</div>
<?php Pjax::end() ?>