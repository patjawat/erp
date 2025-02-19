<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 
use app\components\NotificationHelper;
$path = Yii::$app->request->getPathInfo();

?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-chart-pie"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/meeting/dashboard'],['class' => $path == 'booking/booking-car-items' ? 'btn btn-light' : 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-file"></i> ทะเบียนขอใช้ห้องประชุม<span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/meeting'],['class' => $path == 'booking/booking-car' ? 'btn btn-light' : 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-file"></i> ห้องประชุม<span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/room'],['class' => $path == 'booking/booking-car' ? 'btn btn-light' : 'btn btn-light'])?>

</div>