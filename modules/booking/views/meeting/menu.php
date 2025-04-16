<?php
use yii\helpers\Html;
?>
<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['/booking/meeting/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/booking/meeting/index'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทิน',['/booking/meeting/calendar'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-gear"></i> ตั้งค่าห้องประชุม',['/booking/room'],['class' => 'btn btn-light'])?>
</div>