<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\CategoriseHelper;
use app\modules\am\models\AssetItem; 
use app\components\ApproveHelper;
$path = Yii::$app->request->getPathInfo();

?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-calendar-plus"></i> หน้าหลัก <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/me/booking-meeting'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-calendar-plus"></i> ทะเบียนขอใช้ห้องประชุม <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">0</span>',['/me/booking-meeting/list-me'],['class' => 'btn btn-light'])?>

</div>