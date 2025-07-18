<?php
use yii\helpers\Html;
use app\modules\purchase\models\Order;


?>
<div
    class="table-responsive"
>

</div>


<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-primary">
            <tr>
                <th style="width:500px">
                    <?php //  Html::a('<i class="fa-solid fa-circle-plus text-white"></i> เลือกรายการ', ['/purchase/order/product-list', 'order_id' => $model->id, 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เลือกรายการ '.$orderTypeName ], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal shadow', 'data' => ['size' => 'modal-xl']]) ?>
                <th class="text-center" style="width:80px">หน่วย</th>
                <th class="text-end">ราคาต่อหน่วย</th>
                <th class="text-center" style="width:80px">จำนวน</th>
                <th class="text-end">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php foreach ($model->purchase->ListOrderItems() as $item): ?>
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
                            <td class="text-end"><span class="fw-semibold"><?= number_format($model->purchase->calculateVAT()['priceBeforeDiscount'],2); ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>ส่วนลดสินค้า(เป็นเงิน)</td>
                            <td class="text-end"><span
                                    class="fw-semibold"><?= Html::a(($model->purchase->discount_price == '' ?  '0.00' : number_format($model->purchase->discount_price,2)),['/purchase/order/discount','id' => $model->id,'title' => '<i class="bi bi-currency-exchange"></i> กำหนดราคาส่วนลด'],['class' => 'open-modal','data' => ['size' => 'modal-sm']]); ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>เงินหลังหักส่วนลด</td>
                            <td class="text-end"><span class="fw-semibold"><?= number_format($model->purchase->calculateVAT()['priceAfterDiscount'],2) ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>ภาษีมูลค่าเพิ่ม 7% (<code><?= $model->purchase->vatName()?></code>)</td>
                            <td class="text-end"><span
                                    class="fw-semibold"><?= Html::a(number_format($model->purchase->calculateVAT()['vatAmount'],2),['/purchase/order/form-vat','id' => $model->purchase->id,'title' => '<i class="bi bi-currency-exchange"></i> กำหนดภาษี'],['class' => 'open-modal','data' => ['size' => 'modal-sm']]); ?></span>
                            </td>
                        </tr>
                        <tr class="">
                            <td>จำนวนเงินทั้งสิ้น</td>
                            <td class="text-end"><span class="fw-semibold"><?= number_format($model->purchase->calculateVAT()['priceAfterVAT'],2) ?></span>
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

<?php // echo $this->render('timeline_approve', ['model' => $model]) ?>
<?php echo $this->render('../approve/level_approve',['model' => $model->purchase,'name' => 'purchase'])?>
