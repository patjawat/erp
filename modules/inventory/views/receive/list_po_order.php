<?php

use app\modules\inventory\models\StockMovement;
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
                        <th class="text-center" style="width:80px">จำนวนสั่งซื้อ</th>
                        <th class="text-center" style="width:80px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->ListPoOrder() as $item): ?>
                     <?php $checkStock = StockMovement::find()->where(['name'=> 'receive_item','product_id' => $item->product_id,'po_number' => $item->po_number])->one();?>
                    <?php  if (!empty($checkStock)) : ?>
                    <tr class="">
                        <td class="align-middle"><?php echo $item->product->Avatar(false);?></td>
                        <td class="align-middle text-center"><?= $item->qty; ?></td>
                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม', ['/inventory/receive/add-item', 'id' => $item->id, 'title' => '<i class="bi bi-ui-checks-grid"></i> เลือกรายการวัสดุเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </div>
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