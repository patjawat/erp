<?php
use app\modules\inventory\models\Stock;
use yii\helpers\Html;

// $StockLists = Stock::find()->where(['name' => 'issue', 'from_warehouse_id' => $model->id])->all();
?>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ขอเบิกจำนวน <span class="badge rounded-pill text-bg-primary"> <?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
                <div>
                    <!-- <button class="btn btn-sm btn-primary rounded-pill"><i class="fa-solid fa-plus"></i>
                                เลือกรายการ</button> -->
                    <?php  Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/inventory/stock-movement/create', 'name' => 'issue', 'title' => '<i class="fa-regular fa-pen-to-square"></i> ขอเบิกวัสดุ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                </div>

            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th>คลัง</th>
                        <th>จำนวน</th>
                        <th>สถานะ</th>
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
                        <td><?=$item->warehouse->warehouse_name?></td>
                        <td><?=$item->SumQty()?></td>
                        <td>
                            
                        <?= $item->viewChecker()['status']?></td>
                        <td class="text-center">
                            <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/me/approve/view-stock-out','id' => $item->id],['class'=> 'btn btn-light open-modal','data' => ['size' => 'modal-xl']])?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
