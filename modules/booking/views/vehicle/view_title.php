 <?php

    use yii\helpers\Html;
    ?>

 <div class="d-flex align-items-center gap-2">
     <?php if ($model->vehicle_type_id == 'official'): ?>
         <?= Html::img('@web/images/logo-moph.png', ['width' => '24']) ?>
     <?php endif ?>
     <?php if ($model->vehicle_type_id == 'ambulance'): ?>
         <?= Html::img('@web/images/ambulance_icon.png', ['width' => '24']) ?>
     <?php endif ?>
     <div class="avatar-detail">
         <p class="mb-0 fs-11 fw-semibold"> <?= $model->viewTime()['full'] ?> </p>
         <p class="text-muted mb-0 fs-11 fc-detail d-inline-block text-truncate" style="max-width: 100px;"><?= $model->locationOrg?->title ?? '-' ?> </p>
     </div>
 </div>