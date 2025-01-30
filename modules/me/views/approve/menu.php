<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 
use app\components\NotificationHelper;
$notify = NotificationHelper::Info();
$totalLeave = $notify['leave']['total'];
$totalPurchase = $notify['purchase']['total'];



?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-calendar-day"></i> อนุมัติการลา <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$totalLeave.'</span>',['/me/approve','name' => 'leave'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-bag-shopping"></i> อนุมัติจัดซื้อจัดจ้าง <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">'.$totalPurchase.'</span>',['/me/approve','name' => 'purchase'],['class' => 'btn btn-light'])?>

</div>