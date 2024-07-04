<?php
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<?php Pjax::begin(['id' => 'order_item']); ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>รูป</th>
                        <th style="width:500px">รายการ</th>
                        <th class="text-center" style="width:80px">จำนวน</th>
                        <th class="text-center" style="width:80px">หน่วย</th>
                        <th class="text-center" style="width:80px">ดำเนินการ</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->ListOrderItems() as $item): ?>
                    <?php // if ($item->to_stock != $item->qty): ?>
                    <tr class="">
                        <td class="align-middle">
                            <?php
                            try {
                                echo Html::img($item->product->ShowImg(), ['class' => '  ', 'style' => 'max-width:50px;;height:280px;max-height: 50px;']);
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>
                        </td>
                        <td class="align-middle">
                            <?php
                            try {
                                echo $item->product->title;
                            } catch (\Throwable $th) {
                            }
                            // throw $th;
                            ?></td>
                        <td class="align-middle text-center">
                        <td>
                            <?= $item->qty; ?>

                        </td>
                        <?php
                        try {
                            echo $item->product->data_json['unit'];
                        } catch (\Throwable $th) {
                        }
                        // throw $th;
                        ?>
                        </td>
                       
                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม', ['/warehouse/receive/add-item', 'id' => $item->id, 'title' => 'เลือกรายการวัสดุเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </div>

                        </td>
                    </tr>
                    <?php // endif; ?>
                    <?php endforeach; ?>

                </tbody>
            </table>
            
        </div>
    </div>
</div>
<?php Pjax::end() ?>