<?php
use yii\helpers\Html;
?>


<div class="card">

    <div class="card-body">
    <div class="d-flex justify-content-between">
        <h6><i class="fa-solid fa-right-left"></i> รายการรอรับเข้าคลัง</h6>
    </div>
    <?php if(count($models) >=1): ?>
        <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">เลขที่สั่งซื้อ</th>
                <th scope="col">ประเภท</th>
                <th scope="col" style="width:100px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php foreach ($models as $model): ?>
                <tr class="">
                    <td scope="row"><?= $model->po_number ?></td>
                    <td><?= $model->data_json['order_type_name'] ?></td>
                    <td class="text-end">
                        <?= Html::a('ดำเนินการ', ['/inventory/receive/create', 'category_id' => $model->po_number, 'receive_type' => 'purchase', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ'], ['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else:?>
            <h5 class="text-center">ไม่มีรายการ</h5>
            <?php endif;?>
            </div>
            </div>

