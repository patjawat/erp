<?php
use yii\helpers\Html;
?>
<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-gauge-high me-1"></i> Dashboard',['/booking/meeting/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-calendar-days me-1"></i> ทะเบียนการจอง',['/booking/meeting/index'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-gear me-1"></i> ตั้งค่าห้องประชุม',['/booking/room'],['class' => 'btn btn-light'])?>


</div>