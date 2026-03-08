<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="d-flex flex-wrap gap-2 align-items-center">

    <a href="<?= Url::to(['/leave/default/index']) ?>" class="btn <?= ($active ?? '') === 'index' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-list-ul"></i> ขอลา / รายการของฉัน
    </a>
    <a href="<?= Url::to(['/leave/calendar/index']) ?>" class="btn <?= ($active ?? '') === 'calendar' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-calendar3"></i> ปฏิทินการลา
    </a>
    
</div>
