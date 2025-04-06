<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 
use app\components\ApproveHelper;
$path = Yii::$app->request->getPathInfo();

?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-chart-pie"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/driver/dashboard'],['class' => $path == 'booking/booking-car-items' ? 'btn btn-light' : 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-car"></i> ทะเบียนขอใช้รถทั่วไป <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/driver','car_type' => 'general'],['class' => $path == 'booking/booking-car' ? 'btn btn-light' : 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-truck-medical"></i> ทะเบียนขอใช้รถพยาบาล <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/driver','car_type' => 'ambulance'],['class' => $path == 'booking/booking-car' ? 'btn btn-light' : 'btn btn-light'])?>

</div>