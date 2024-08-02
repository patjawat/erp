<?php

use app\modules\inventory\models\Stock;
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<?php Pjax::begin(['id' => 'order_item']); ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:500px">รายการ</th>
                        <!-- <th class="text-center" style="width:100px">ซื้อ</th>
                        <th class="text-center" style="width:100px">รับ</th>
                        <th class="text-center" style="width:100px">เหลือ</th> -->
                        <th class="text-center" style="width:100px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->ListOrderItems() as $item): ?>
                    <?php $checkStock = Stock::find()->where(['name'=> 'receive_item','asset_item' => $item->asset_item,'po_number' => $order->po_number])->sum('qty');?>
                    <?php $checkStock2 = Stock::find()->where(['name'=> 'receive_item','asset_item' => $item->asset_item,'po_number' => $order->po_number,'rc_number' => $model->rc_number])->One();?>

                    <?php if (!$checkStock2 && ($item->qty - $checkStock) !=0) : ?>
                    <tr class="">
                        <td class="align-middle"><?php echo $item->product->Avatar(false);?></td>
                        <!-- <td class="align-middle text-center"><?= $item->qty; ?></td>
                        <td class="align-middle text-center"><?= $checkStock; ?></td> -->
                        <!-- <td class="align-middle text-center"><?= $item->qty - $checkStock; ?></td> -->
                         
                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก', ['/inventory/receive/add-po-item', 'id' => $item->id, 'title' => '<i class="bi bi-ui-checks-grid"></i> เลือกรายการวัสดุเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            </div>
                        </td>
                    </tr>
                    <?php else:?>
                        <tr class="text-center">
                       <td  colspan="2"> 
                        <span class="text-success fs-5">
                           ดำเนินการเรียบร้อย !

                       </span>
                    </td>
                    </tr>
                    <?php  endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
<?php Pjax::end() ?>