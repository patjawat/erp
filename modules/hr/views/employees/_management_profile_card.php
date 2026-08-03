<?php
use yii\helpers\Html;
?>
<section class="managed-profile-card mb-3" aria-label="บุคลากรที่กำลังดู">
    <div class="managed-profile-card__head">
        <?= Html::img($model->showAvatar(), ['class' => 'managed-profile-card__avatar', 'alt' => 'รูปของ '.$model->fullname]) ?>
        <div class="min-w-0">
            <h2 class="h6 mb-1 text-truncate"><?= Html::encode($model->fullname) ?></h2>
            <p class="small text-body-secondary mb-0 text-truncate"><?= $model->positionName() ?></p>
        </div>
    </div>
    <div class="managed-profile-card__body small">
        <div class="d-flex gap-2 mb-2"><i data-lucide="building-2" class="text-primary" aria-hidden="true"></i><span><?= Html::encode($model->departmentName() ?: 'ไม่ระบุหน่วยงาน') ?></span></div>
        <div class="d-flex gap-2"><i data-lucide="shield-check" class="text-success" aria-hidden="true"></i><span>แสดงเฉพาะข้อมูลเพื่อการบริหารงานบุคคล</span></div>
    </div>
</section>
