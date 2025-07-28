 <?php
 use yii\helpers\Html;
 ?>
 <div class="d-flex align-items-center gap-2">
<?=Html::img('@web/images/ambulance_icon.png',['width' => '24'])?>
            <div class="avatar-detail">
                <p class="mb-0 fs-11 fw-semibold"> <?=$model->viewTime()?> </p> 
                <p class="text-muted mb-0 fs-11 fw-semibold"><?=$model->locationOrg?->title ?? '-'?> </p>
            </div>
        </div>

    
