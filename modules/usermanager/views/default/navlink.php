<?php
use yii\helpers\Html;
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-0">
    <?= Html::a('<i class="bi bi-grid-1x2 me-1"></i> ภาพรวม', ['/usermanager/default/dashboard'], ['class' => 'btn btn-outline-success rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-signpost-2 me-1"></i> เส้นทาง', ['/usermanager/router'], ['class' => 'btn btn-outline-secondary rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-person-badge me-1"></i> บทบาท', ['/usermanager/role'], ['class' => 'btn btn-outline-danger rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-people me-1"></i> ผู้ใช้งานระบบ', ['/usermanager/user'], ['class' => 'btn btn-outline-primary rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-box-arrow-in-right me-1"></i> เซสชัน', ['/usermanager/session'], ['class' => 'btn btn-outline-info rounded-3 link-loading']) ?>
</div>