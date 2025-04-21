<?php
use yii\helpers\Html;
use yii\db\Expression;
use yii\widgets\DetailView;
use app\modules\inventory\models\Stock;
$warehouse = Yii::$app->session->get('warehouse');
$stockEvents = Stock::find()
->select([
    'stock.*',
    new Expression('SUM(qty * unit_price) AS total')
])
->andWhere([
    'asset_item' => $asset_item,
    'warehouse_id' => $warehouse->id,
])
->andWhere(['>', 'qty', 0]);

// Debug raw SQL
// echo $stockEvents->createCommand()->getRawSql();

$stockEvents = $stockEvents->all();

$balance = 0;
$balanceQty = 0;

?>

<?php if(count($stockEvents) > 0):?>
<table class="table">
    <thead>
        <tr>
            <th class="fw-semibold" scope="col" style="width:130px">หมายเลขล็อต</th>
            <th class="fw-semibold text-center">คงเหลือ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach($stockEvents as $item2):?>
        <tr class="<?=$lot_number == $item2->lot_number ? 'table-active' : ''?>">
            <td><?=$item2->lot_number?></td>
            <td class="fw-semibold text-center"><?=$item2->qty?></td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<?php else:?>
<h3 class="text-center">หมด</h3>
<?php endif;?>