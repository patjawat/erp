<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 
use app\components\NotificationHelper;

?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-file"></i> ทะเบียนขอใช้ยานพาหนะ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/booking-car'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-car"></i> ข้อมูลยานพาหนะ <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/booking/booking-cars-items'],['class' => 'btn btn-light'])?>

</div>