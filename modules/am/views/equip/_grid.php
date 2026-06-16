<?php

use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */

?>
<style>
/* เลื่อนเฉพาะกริดการ์ด ส่วน pagination อยู่ด้านล่างคงที่ */
.equip-grid-scroll {
    max-height: min(68vh, 760px);
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
}
</style>
<div class="bg-body">
    <div class="equip-grid-scroll">
        <div class="row g-3 p-3 mb-0">
    <?php foreach ($dataProvider->getModels() as $item): ?>
        <?php
        $price = (float) ($item->price ?? 0);
        $catTitle = $item->assetCategory?->title ?? $item->assetType?->title ?? '-';
        $titleName = $item->asset_name ?: ($item->AssetitemName() ?: '-');
        ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card h-100 border shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-3">

                  
                    <div class="d-flex gap-3 mb-2">
                        <?= Html::img(
                            $item->ShowImg()['image'],
                            [
                                'class' => 'rounded border flex-shrink-0',
                                'style' => 'width:72px;height:72px;object-fit:cover;',
                                'alt' => $titleName,
                            ]
                        ) ?>

                        <div class="min-w-0 flex-grow-1">
                            <div class="small text-muted mb-0"><?= Html::encode($item->code ?: '-') ?></div>
                            <div class="small text-muted mb-0">GFMIS: <?= Html::encode($item->gfmis ?: '-') ?></div>
                            <div class="fw-semibold text-truncate"><?= Html::encode($titleName) ?></div>
                            <div class="mt-1">สภาพ : <?= $item->getConditionBadge() ?></div>
                        </div>
                    </div>
                    <div class="mt-1"><?= $item->getStatusBadge() ?></div>

                      </div>


                    <div class="small text-muted mb-2"><?= Html::encode($catTitle) ?></div>
                    <div class="mt-auto d-flex justify-content-between align-items-end">
                        <div>
                            <div class="small text-muted">ราคาแรกรับ</div>
                            <div class="fw-bold text-primary"><?= Html::encode(number_format($price, 2)) ?></div>
                        </div>
                        <div class="d-flex gap-1">
                            <?= Html::a('<i class="fa fa-eye"></i>', ['view-asset', 'id' => $item->id], ['class' => 'btn btn-sm btn-primary', 'data-pjax' => 0]) ?>
                             <?= Html::a('<i class="bi bi-qr-code-scan"></i>', ['/am/asset/view-qr', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-secondary',
                                    'title' => 'พิมพ์',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
                            <?php if (Yii::$app->user->can('asset')): ?>
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-warning', 'data-pjax' => 0]) ?>
                            <?php endif; ?>
<?php if (Yii::$app->user->can('admin')): ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-danger delete-asset',
                                        'title' => 'ลบ',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
        </div>
    </div>
</div>
