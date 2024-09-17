<?php
use yii\helpers\Html;
use yii\helpers\Url;
$warehouse = Yii::$app->session->get('warehouse');
// echo "<pre>";
// print_r($warehouse);
// echo "</pre>";
?>
<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่า', ['/lm/setting'], ['class' => 'btn btn-light']) ?>

</div>


