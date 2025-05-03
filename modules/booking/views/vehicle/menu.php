<?php
use yii\helpers\Html;
?>

<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['/booking/vehicle/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-car"></i> ทะเบียนใช้รถยนต์ทั่วไป',['/booking/vehicle/index'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-truck-medical"></i> ทะเบียนใช้รถพยาบาล',['/booking/vehicle/ambulance'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-user-tag"></i> ทะเบียนจัดสรร',['/booking/vehicle/work'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทิน',['/booking/vehicle/calendar'],['class' => 'btn btn-light'])?>
</div>
