<?php
use app\modules\inventory\models\Stock;
use yii\helpers\Html;

// $StockLists = Stock::find()->where(['name' => 'issue', 'from_warehouse_id' => $model->id])->all();
?>

<div class="card" style="min-height: 463px;">
    <div class="card-body">
        <div class="table-responsive">
            <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ขอเบิกจำนวน <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php // Html::a('<i class="fa-solid fa-angles-right"></i> แสดงทั้งหมด',['/inventory/stock-out'],['class' => 'btn btn-light'])?>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">สถานะ</th>
                        <th style="width:100px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                    <tr class="">
                        <td><?php
                        $msg  ='ขอเบิกวัสดุสำนักงาน';
                       echo $item->CreateBy($msg)['avatar'] ?>
                       </td>
                       <td class="text-center"><?=$item->SumReqQty()?></td>
                       <td class="text-center"><?=$item->viewChecker()['status']?></td>
                        <td class="text-center">
                            <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/inventory/stock-order/view','id' => $item->id],['class'=> 'btn btn-light'])?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
       
    </div>
    <div class="card-footer">
    <?php echo Html::a('<i class="fa-solid fa-angles-right"></i> แสดงท้ังหมด', ['/inventory/order-request'], ['class' => 'btn btn-sm btn-light rounded-pill']) ?>
    </div>
</div>