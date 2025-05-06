<?php
use yii\helpers\Html;
?>
<div class="card mt-4">
        <div class="card-header bg-light p-2">
            <div class="d-flex align-items-center justify-content-between">
                <strong><i class="fa-solid fa-users me-2"></i>คณะเดินทาง</strong>
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม',['/me/development-detail/create','name' => 'member','development_id' => $model->id,'title' => '<i class="fa-solid fa-users me-2"></i>คณะเดินทาง'],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
            </div>
        </div>
        <div class="card-body">
            <?=$model->StackMember()?>
        </div>
    </div>