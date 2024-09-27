<?php
use app\modules\inventory\models\Stock;
use yii\helpers\Html;

// $StockLists = Stock::find()->where(['name' => 'issue', 'from_warehouse_id' => $model->id])->all();
?>

<?php foreach ($dataProvider->getModels() as $item): ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">

            <?=$item->CreateBy('ขอเบิกวัสดุสำนักงาน')['avatar'] ?>
            <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/me/approve/view-stock-out','id' => $item->id],['class'=> 'btn btn-primary  text-white rounded-pill open-modal','data' => ['size' => 'modal-xl']])?>
        </div>
</div>
</div>
                    <?php endforeach; ?>
