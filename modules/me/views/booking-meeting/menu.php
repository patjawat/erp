<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\ApproveHelper;
use app\components\CategoriseHelper; 
use app\modules\am\models\AssetItem;
$path = Yii::$app->request->getPathInfo();

?>
<div class="d-flex gap-2">
        <?php  echo  Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['/me/booking-meeting/dashboard'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทินรวม',['/me/booking-meeting/calendar'],['class' => 'btn btn-light'])?>
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/me/booking-meeting/index'],['class' => 'btn btn-light'])?>
        
</div>