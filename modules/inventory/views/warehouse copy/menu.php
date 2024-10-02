<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างคลังใหม่', ['/inventory/warehouse/create', 'title' => '<i class="fa-solid fa-circle-plus me-1"></i> สร้างคลังใหม่'], ['id' => 'addWarehouse', 'class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-lg']]); ?>
    <button class="btn btn-light" onclick="openTour()">
        <span><i class="fa-regular fa-circle-question"></i> แนะนำ</span>
    </button><!-- end btn -->
</div>


