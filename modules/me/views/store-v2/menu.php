<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\ApproveHelper;
use app\components\CategoriseHelper; 
use app\modules\am\models\AssetItem;
$notify = ApproveHelper::Info();
$total = $notify['total'];
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];



?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-gauge"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me/store-v2/dashboard','name' => 'leave'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-cube"></i>  สต๊อก/เบิกใช้งาน',['/me/store-v2/index'],['class' => 'btn btn-light'])?>
        <?php //echo  Html::a('<i class="fa-regular fa-circle-check"></i> เลือกคลัง',['/me/store-v2/select-warehouse'],['class' => 'btn btn-light'])?>
        <?php // echo  Html::a('<i class="fa-regular fa-circle-check"></i> ทะเบียนจ่าย',['/me/store-v2/order-out'],['class' => 'btn btn-light'])?>
        <?php // echo  Html::a('<i class="fa-regular fa-circle-check"></i> เบิกวัสดุคลังหลัก',['/me/store-v2/order-in'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="bi bi-shop"></i> เบิกวัสดุคลังหลัก',['/me/main-stock/store'],['class' => 'btn btn-light'])?>

</div>