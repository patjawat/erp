<?php
use yii\helpers\Url;
?>
<div class="d-flex flex-wrap gap-2">
    <a href="<?= Url::to(['/leave/default/index']) ?>" class="btn <?= ($active ?? '') === 'index' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-calendar-check"></i> ภาพรวม
    </a>
    <a href="<?= Url::to(['/me/leave']) ?>" class="btn btn-outline-primary">
        <i class="bi bi-list-ul"></i> ขอลา / รายการของฉัน
    </a>
    <a href="<?= Url::to(['/approve-v2/leave']) ?>" class="btn btn-outline-primary">
        <i class="bi bi-person-check"></i> รายการที่รออนุมัติ
    </a>
</div>
