<?php
use yii\helpers\Html;
use app\models\Categorise;
use app\components\ApproveHelper;
use app\components\CategoriseHelper; 
use app\modules\am\models\AssetItem;
$path = Yii::$app->request->getPathInfo();
?>

<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทินการใช้รถ',['/me/booking-vehicle/calendar'],['class' => 'btn btn-light '.(isset($active) && $active =='calendar' ? 'active' : '')])?>
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/me/booking-vehicle/index'],['class' => 'btn btn-light '.(isset($active) && $active =='index' ? 'active' : '')])?>
</div>
