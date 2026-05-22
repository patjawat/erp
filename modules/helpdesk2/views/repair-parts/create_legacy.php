<?php

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\HelpdeskDetail $model */
/** @var app\modules\helpdesk2\models\HelpdeskDetail[] $partRows */
/** @var app\modules\inventoryV2\models\Warehouse[] $subWarehouses */

echo $this->render('_form_legacy', [
    'model' => $model,
    'partRows' => $partRows ?? [],
    'subWarehouses' => $subWarehouses ?? [],
]);
