<?php

use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;

/** @var yii\data\ActiveDataProvider $dataProvider */

$statusBadge = static function ($item): string {
    $st = (int) $item->asset_status;
    $label = Html::encode($item->statusName() ?: '-');
    if ($st === 1) {
        return '<span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success-subtle fw-medium">' . $label . '</span>';
    }
    if (in_array($st, [3, 5], true)) {
        return '<span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-medium">' . $label . '</span>';
    }
    return '<span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle fw-medium">' . $label . '</span>';
};
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
                            <div class="fw-semibold text-truncate"><?= Html::encode($titleName) ?></div>
                            <div class="mt-1"><?= $statusBadge($item) ?></div>
                        </div>
                    </div>
                    <div class="small text-muted mb-2"><?= Html::encode($catTitle) ?></div>
                    <div class="mt-auto d-flex justify-content-between align-items-end">
                        <div>
                            <div class="small text-muted">ราคาแรกรับ</div>
                            <div class="fw-bold text-primary"><?= Html::encode(number_format($price, 2)) ?></div>
                        </div>
                        <div class="d-flex gap-1">
                            <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view-asset', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary', 'data-pjax' => 0]) ?>
                             <?= Html::a('<i class="bi bi-qr-code-scan"></i>', ['/am/asset/view-qr-pdf', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-light',
                                    'title' => 'พิมพ์',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
                            <?php if (Yii::$app->user->can('asset')): ?>
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-warning', 'data-pjax' => 0]) ?>
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
<div class="card-footer bg-body py-3 px-4 border-top">
    <?php
    echo DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]);
    ?>
</div>
