<?php
use app\modules\inventory\models\Stock;
use yii\helpers\Html;

// $StockLists = Stock::find()->where(['name' => 'issue', 'from_warehouse_id' => $model->id])->all();
?>

<?php foreach ($dataProvider->getModels() as $item): ?>
<div class="card">
    <div class="card-body">
<?=$item->CreateBy('ขอเบิกวัสดุสำนักงาน')['avatar'] ?>
</div>
</div>
                    <?php endforeach; ?>
