<?php
use app\models\Categorise;
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;

$listCommittee = Order::find()
    ->where(['name' => 'rc_commitee'])
    ->all();
?>
<?php Pjax::begin(['id' => 'rc_commitee']); ?>
<div class="card mb-2">
    <div class="card-body" style="min-height: 204px;">


        <table class="table table-primary">
            <thead>
                <tr>
                    <th scope="col"><i class="fa-solid fa-user-tie"></i> กรรมการตรวจรับวัสดุเข้าคลัง</th>

                    <th scope="col" class="d-flex justify-content-end"> 
                        <?= Html::a('<i class="fa-solid fa-plus"></i>เพิ่ม',
                            ['/inventory/rc-order/add-committee', 'name' => 'rc_commitee', 'category_id' => $model->id, 'title' => '<i class="fa-solid fa-user-tie"></i> กรรมการตรวจรับ'],
                            ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listCommittee as $item): ?>
                <tr class="">
                    <td scope="row">
                        <?= $item->ShowBoard()['avatar']; ?>
                    </td>
                    <td class="d-flex justify-content-end gap-2">

                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        <?= Html::a('<i class="bi bi-trash"></i>', ['/inventory/rc-order/delete', 'id' => $item->id, 'container' => 'rc_commitee'], [
                            'class' => 'btn btn-sm btn-danger delete-item',
                        ]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>




    </div>
</div>
<?php Pjax::end(); ?>