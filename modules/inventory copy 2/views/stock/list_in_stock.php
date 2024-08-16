<?php
use app\modules\inventory\models\Stock;
use yii\helpers\Html;

echo  $model->asset_item;
$stock  = Stock::find()
->where(['name' => 'order_item','movement_type' => 'IN','asset_item' => $model->asset_item])
->all();

foreach ($stock as $key => $value) {
     echo $value->id;
}
?>
<div class="table-responsive">
    <table class="table table-primary">
        <thead>
            <tr>
                <th scope="col">ล็อตผลิต</th>
                <th scope="col">วันผลิต</th>
                <th scope="col">วันหมดอายุ</th>
                <th scope="col" class="text-center">คงเหลือ</th>
                <th scope="col" style="width:100px;" class="text-center">กำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->ListItemsByLot() as $item): ?>
            <tr class="">
                <td scope="row"><?= $item->lot_number ?></td>
                <td><?=isset($item->data_json['mfg_date']) ? $item->data_json['mfg_date'] : '-' ?></td>
                <td><?= isset($item->data_json['exp_date']) ? $item->data_json['exp_date']  : '-' ?></td>
                <td class="text-center"> <?=$item->qty?></td>
                <td class="text-center"><?=Html::a('เลือก',['/inventory/stock/select-lot','id' => $item->id,'order_id' => $model->id],['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal','data' => ['size' => 'modal-md']])?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
    </table>
</div>
