<?php
use yii\helpers\Html;

/** @var app\modules\inventoryV2\models\Warehouse $model */
$this->title = 'แก้ไขคลัง: ' . $model->warehouse_name;
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าคลัง', 'url' => ['/inventory-v2/warehouse/index']];
$this->params['breadcrumbs'][] = ['label' => $model->warehouse_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="warehouse-update">
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
