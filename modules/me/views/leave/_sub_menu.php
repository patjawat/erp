<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
        <?php echo  Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนประวัติ',['/me/leave'],['class' => 'btn '.(isset($active) && $active == 'index' ? 'btn-primary' : 'btn-light')])?>
        <?php echo  Html::a('<i class="fa-solid fa-calendar-day"></i>  ปฏิทินการลา',['/me/leave/calendar'],['class' => 'btn '.(isset($active) && $active == 'calendar' ? 'btn-primary' : 'btn-light')])?>
</div>