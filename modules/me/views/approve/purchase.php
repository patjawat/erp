<?php
use yii\helpers\Html;
?>
<?php foreach($dataProvider->getModels() as $item):?>
<div class="card mb-2 zoom-in">
    <div class="card-body">
        <?=Html::a($item->getUserReq('ขอเบิกวัสดุสำนักงาน')['avatar'],['/purchase/order/view','id' => $item->id]) ?>
    </div>
</div>
<?php endforeach;?>