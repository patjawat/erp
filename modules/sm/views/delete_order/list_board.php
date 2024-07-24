<?php
use app\models\Categorise;
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;

$listBoard = Order::find()
    ->where(['name' => 'board'])
    ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.board') asc"))
    ->all();
?>
<!-- กรรมการตรวจรับ -->

<?php Pjax::begin(['id' => 'board-container']); ?>

<div class="d-flex justify-content-between align-items-center">
    <h5><i class="fa-solid fa-circle-info text-primary"></i> กรรมการตรวจรับ</h5>

    <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เพิ่ม', ['/sm/order-item/create', 'id' => $model->id, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
    
</div>

<table class="table table-primary">
    <thead>
        <tr>
            <th scope="col">คณะกรรมการ</th>
            <th scope="col">ตำแหน่ง</th>
            <th scope="col" style="width: 100px;">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($listBoard as $item): ?>
            <tr class="">
                <td scope="row">
                    <?= $item->ShowBoard()['avatar']; ?>
            </td>
            <td>
                <?php
                try {
                    echo $item->data_json['board_position'];
                } catch (\Throwable $th) {
                }
                ?>
                </td>
            <td>
                
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/sm/order-item/update', 'id' => $item->id, 'name' => 'board', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="bi bi-trash"></i>', ['/sm/order-item/delete', 'id' => $item->id], [
                    'class' => 'btn btn-sm btn-danger delete-item',
                ]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php Pjax::end(); ?>