<?php
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
        <h6><i class="fa-solid fa-right-left"></i> รายการขอเบิกวัดุ</h6>
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เบิกวัสดุ', ['/inventory/stock/order-request', 'receive_type' => 'purchase', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> เบิกวัสดุ'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
    </div>
        <div
            class="table-responsive"
        >
        <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th scope="col">กก</th>
                        <th scope="col">Column 3</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach($dataProvider->getModels() as $model):?>
                    <tr class="">
                        <td scope="row"><?=$model->name?></td>
                        <td>R1C2</td>
                        <td>R1C3</td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>
