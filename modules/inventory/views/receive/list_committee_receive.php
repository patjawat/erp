<?php
use app\models\Categorise;
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;


?>
<!-- กรรมการตรวจรับ -->
<?php Pjax::begin(['id' => 'committee_receive']); ?>
<?php
// $model = Yii::$app->session->get('order');
// $listBoard = Order::find()
//     ->where(['name' => 'committee_receive'])
//     ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.board') asc"))
//     ->all();
?>


<table class="table">
    <thead class="table-primary">
        <tr>
            <th scope="col"><?= Html::a('<i class="bi bi-plus-circle-fill me-2"></i> เพิ่มกรรมการ', [
                            '/inventory/receive/add-committee','rc_number' => $model->rc_number,'name' => 'receive_committee','title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'
                        ], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']]) ?></th>
            <th scope="col">ตำแหน่ง</th>
            <th scope="col" style="width: 120px;">
            ดำเนินการ
        </th>
        </tr>
    </thead>
    <tbody class="table-group-divider">
        <?php foreach ($model->listCommittee() as $item): ?>
            <tr class="">
                <td scope="row">
                    <?php echo $item->ShowCommittee()['avatar']; ?>
            </td>
            <td>
                <?php
                try {
                    echo $item->data_json['committee_position_name'];
                } catch (\Throwable $th) {
                }
                ?>
                </td>
                <td class="align-middle">
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/receive/update-committee','id' => $item->id,'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="bi bi-trash"></i>', ['/inventory/receive/delete', 'id' => $item->id, 'container' => 'boardDetail-container'], [
                    'class' => 'btn btn-sm btn-danger rounded-pill delete-item',
                ]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php Pjax::end(); ?>