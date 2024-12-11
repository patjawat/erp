<?php
use yii\helpers\Url;
use yii\helpers\Html;
$warehouse = Yii::$app->session->get('warehouse');
// echo "<pre>";
// print_r($warehouse);
// echo "</pre>";
?>
<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-gauge me-1"></i> Dashboard', ['/lm/'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-file-pen"></i> ทะเบียนประวัติ', ['/lm/leave'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่า', ['/lm/setting'], ['class' => 'btn btn-light']) ?>

</div>


