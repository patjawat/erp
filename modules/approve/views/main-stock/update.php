<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\purchase\models\Order;
?>



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
            <?php $totalPrice = 0?>
            <?php foreach ($model->stock->ListItems() as $item): ?>
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
                                echo number_format($item->unit_price, 2);
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
                                    $sumPrice = ($item->qty * $item->unit_price);
                                    echo number_format($sumPrice);
                                    $totalPrice +=$sumPrice;
                                    try {
                                } catch (\Throwable $th) {
                                    // throw $th;
                                }
                                ?>
                    </div>
                </td>
               
            </tr>
            <?php endforeach; ?>
<tr>
    <td colspan="4" class="text-center">รวม</td>
    <td class="text-end fw-semibold"><?=number_format($totalPrice)?></td>
</tr>
        </tbody>
    </table>
</div>
    

<?php echo $this->render('level_approve',[
    'model' => $model->stock,'name' => 'main_stock',
    ])?>
<?php // echo $this->render('../approve/level_approve',['model' => $model->purchase,'name' => 'purchase'])?>
