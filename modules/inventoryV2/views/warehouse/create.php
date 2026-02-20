<?php
use yii\helpers\Html;

/** @var app\modules\inventoryV2\models\Warehouse $model */
$this->title = 'สร้างคลังใหม่';
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าคลัง', 'url' => ['/inventory-v2/warehouse/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warehouse-create">
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
