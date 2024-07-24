<?php
use app\models\Categorise;
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;

$listcommittee = Order::find()
    ->where(['name' => 'committee'])
    ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.committee') asc"))
    ->all();
?>
<!-- กรรมการตรวจรับ -->

<?php Pjax::begin(['id' => 'committee']); ?>
<?php
$model = Yii::$app->session->get('order');

?>

<table class="table table-primary">
    <thead class="table-primary">
        <tr>
            <th scope="col"><?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> เลือกกรรรมการ', ['/purchase/order-item/create', 'id' => $model->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?></th>
            <th scope="col">ตำแหน่ง</th>
            <th scope="col" style="width: 120px;">ดำเนินการ</th>
            </div>
        </tr>
    </thead>
    <tbody class="table-group-divider">
        <?php foreach ($listcommittee as $item): ?>
        <tr class="">
            <td scope="row">
                <?= $item->ShowCommittee()['avatar']; ?>
            </td>
            <td>
                <?php
                try {
                    echo $item->data_json['committee_position'];
                } catch (\Throwable $th) {
                }
                ?>
            </td>
            <td class="align-middle">
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="bi bi-trash"></i>', ['/purchase/order-item/delete', 'id' => $item->id], [
                    'class' => 'btn btn-sm btn-danger rounded-pill delete-item',
                ]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php Pjax::end(); ?>