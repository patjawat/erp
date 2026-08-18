 <?php

    use yii\helpers\Html;
    ?>

 <div class="d-flex align-items-center gap-1">
     <?php if ($model->vehicle_type_id == 'official'): ?>
         <?= Html::img('@web/images/Logo-moph.png', ['class' => 'fc-vehicle-logo flex-shrink-0', 'alt' => 'รถยนต์ราชการ']) ?>
     <?php endif ?>
     <?php if ($model->vehicle_type_id == 'ambulance'): ?>
         <?= Html::img('@web/images/ambulance_icon.png', ['class' => 'fc-vehicle-logo flex-shrink-0', 'alt' => 'รถพยาบาล']) ?>
     <?php endif ?>
     <div class="avatar-detail">
         <p class="mb-0 fs-11"> <?= $model->viewTime()['full'] ?> </p>
         <p class="text-muted mb-0 fs-11 fc-detail d-inline-block text-truncate" style="max-width: 100px;"><?= $model->locationOrg?->title ?? '-' ?> </p>
     </div>
 </div>
