<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 
use app\components\ApproveHelper;
$notify = ApproveHelper::Info();
$total = $notify['total'];
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];



?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-gauge"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"></span>',['/me','name' => 'leave'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-regular fa-circle-check"></i> รายการที่ต้องอนุมัติ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$total.'</span>',['/approve'],['class' => 'btn btn-light'])?>

</div>