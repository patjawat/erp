<?php
use yii\helpers\Html;
?>
<div class="d-flex align-items-center gap-2" style="margin-top: -5px;">
     <?php echo isset($item->car) ? Html::img($item->car?->ShowImg()['image'], ['class' => 'img-fluid rounded','style' => 'max-width: 80px;']) : '-'; ?>
     <!-- $item->car?->license_plate ?? '-' -->
        <div class="avatar-detail">
            <h6 class="mb-0 fs-12"><?=$item->car?->data_json['brand']?>
            </h6>
            <p class="text-muted mb-0 fs-13"><?=$item->car?->license_plate?></p>
            
        </div>
    </div>