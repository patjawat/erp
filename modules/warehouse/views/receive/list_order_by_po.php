<?php
use yii\helpers\Html;

?>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th scope="col">เลขที่สั่งซื้อ</th>
                <th scope="col">ประเภท</th>
                <th scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($models as $model): ?>
            <tr class="">
                <td scope="row"><?= $model->po_number ?></td>
                <td><?= $model->data_json['product_type_name'] ?></td>
                <td>
                    <?= Html::a('ดำเนินการ', ['/warehouse/receive/create', 'category_id' => $model->po_number, 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รับสินค้าจากใบสั่งซื้อ'], ['class' => 'btn btn-success open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
