<?php
use yii\helpers\Html;
?>

<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['/booking/vehicle/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/booking/vehicle/index'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-user-tag"></i> ทะเบียนจัดสรร',['/booking/vehicle/work'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทินการใช้รถยนต์',['/booking/vehicle/calendar'],['class' => 'btn btn-light'])?>
</div>
