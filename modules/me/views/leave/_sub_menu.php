<?php
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
          <a href="<?= Url::to(['/leave/default/index']) ?>" class="btn <?= $active !== 'index' ? 'btn-outline-primary' : 'btn-primary' ?>">
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติ
        </a>
        <a href="<?= Url::to(['/me/leave/calendar']) ?>" class="btn <?= $active !== 'calendar' ? 'btn-outline-primary' : 'btn-primary' ?>">
                <i class="fa-solid fa-calendar-day"></i>
                ปฏิทินการลา
        </a>
      
</div>