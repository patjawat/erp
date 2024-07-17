<?php
use yii\helpers\Html;
?>
<div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded mb-3 d-flex justify-content-between">
    <h5><i class="fa-solid fa-circle-info text-primary"></i> การตรวจและเห็นชอบ</h5>
    <?php // Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่ม', ['/purchase/order-item/create', 'id' => $model->id, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
</div>

<table class="table table-primary">
    <tbody>
        <tr class="">
            <td  class="text-end" style="width: 137px;">ผู้เห็นชอบ</td>
            <td><?= $model->viewLeaderUser()['avatar'] ?></td>
            <td>
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/sm/order-item/update', 'id' => 1, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="bx bx-trash me-1"></i>', ['/sm/order-item/delete', 'id' => 1], [
                    'class' => 'btn btn-sm btn-danger delete-item',
                ]) ?>
            </td>
        </tr>
        <tr class="">
            <td  class="text-end">พัสดุตรวจสอบ</td>
            <td><?= $model->viewLeaderUser()['avatar'] ?></td>
            <td>
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/sm/order-item/update', 'id' => 1, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="bx bx-trash me-1"></i>', ['/sm/order-item/delete', 'id' => 1], [
                    'class' => 'btn btn-sm btn-danger delete-item',
                ]) ?>
            </td>
        </tr> <tr class="">
            <td  class="text-end">ผู้อำนวยการตรวจสอบ</td>
            <td><?= $model->viewLeaderUser()['avatar'] ?></td>
            <td>
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/sm/order-item/update', 'id' => 1, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="bx bx-trash me-1"></i>', ['/sm/order-item/delete', 'id' => 1], [
                    'class' => 'btn btn-sm btn-danger delete-item',
                ]) ?>
            </td>
        </tr>
    </tbody>
</table>