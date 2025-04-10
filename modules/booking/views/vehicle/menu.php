<?php
use yii\helpers\Html;
?>

<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['/booking/vehicle/dashboard'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนจัดสรร',['/booking/vehicle/index'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-calendar"></i> ปฏิทินรวม',['/booking/vehicle/work'],['class' => 'btn btn-light'])?>


</div>
