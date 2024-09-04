<?php
use app\modules\inventory\models\Stock;
use yii\helpers\Html;

$StockLists = Stock::find()->where(['name' => 'issue', 'from_warehouse_id' => $model->id])->all();
?>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <div class="d-flex justify-content-between">
                <h6><i class="fa-solid fa-file-circle-plus"></i> รายการ</h6>
                <div>
                    <!-- <button class="btn btn-sm btn-primary rounded-pill"><i class="fa-solid fa-plus"></i>
                                เลือกรายการ</button> -->
                    <?= Html::a('<i class="fa-solid fa-plus"></i> เลือกรายการ', ['/inventory/stock-movement/create', 'name' => 'issue', 'title' => '<i class="fa-regular fa-pen-to-square"></i> ขอเบิกวัสดุ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                </div>

            </div>
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">สถานะ</th>
                        <th scope="col">รหัส</th>
                        <th scope="col">วันที่ต้องการ</th>
                        <th scope="col">หมายเหตุ</th>
                        <th scope="col">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($StockLists as $item): ?>
                    <tr class="">
                        <td scope="row"></td>
                        <td><?= $item->rq_number ?></td>
                        <td>Item</td>
                        <td>Item</td>
                        <td>Item</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>