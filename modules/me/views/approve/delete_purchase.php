<?php
use yii\helpers\Html;
?>
<div class="d-flex flex-column">
    <?php foreach($dataProvider->getModels() as $item):?>

    <div class="d-flex justify-content-start total font-weight-bold rounded p-2">
        <?=Html::img($item->getUserReq()['employee']->showAvatar(), ['class' => 'avatar avatar-sm bg-primary text-white'])?>
        <div class="d-flex">
            <div class="avatar-detail">
                <h6 class="mb-1 fs-15" data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-custom-class="custom-tooltip" data-bs-title="ดูเพิ่มเติม...">ขออนุมิติจัดซื้อจัดจ้าง
                </h6>

                <p class="text-muted mb-0 fs-13"><?=$item->viewCreated()?> | <?=$item->viewCreatedAt()?></p>

            </div>
        </div>
    </div>

    <?php endforeach;?>
</div>