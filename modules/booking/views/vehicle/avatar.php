<?php
use yii\helpers\Html;
$title = 'ขอใช้'.$model->carType->title.' ไป'.($model->locationOrg?->title ?? '-');
?>
<div class="d-flex align-items-center">
    <?php // Html::img($model->employee->showAvatar(),['class' => 'avatar avatar-sm bg-primary'])?>
<div class="avatar-detail pt-2">
                <h6 class="mb-0 fs-13"><?=$model->employee->fullname;?>  <?=$model->viewStatus()['view']?></h6>
                <p class="text-muted mb-0 fs-13"><?=$title?></p>
                <p class="text-muted mb-0 fs-13">เวลา <?=$model->viewTime()?></p>
            </div>
</div>