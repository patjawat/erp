<?php
use yii\helpers\Html;
$path = Yii::$app->request->getPathInfo();
?>

<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทินการใช้รถยนต์ทั่งไป',['/me/booking-vehicle/calendar'],['class' => 'btn btn-light '.(isset($active) && $active =='official' ? 'active' : '')])?>
        <?php echo  Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทินการใช้รถพยาบาล',['/me/booking-vehicle/calendar-ambulance'],['class' => 'btn btn-light '.(isset($active) && $active =='ambulance' ? 'active' : '')])?>
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติการจอง',['/me/booking-vehicle/index'],['class' => 'btn btn-light '.(isset($active) && $active =='index' ? 'active' : '')])?>
</div>
