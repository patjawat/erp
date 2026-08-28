<?php

use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
?>

<style>
.structure-grid-scroll { max-height: min(68vh, 760px); overflow-y: auto; overflow-x: hidden; }
.structure-grid-thumb { width: 72px; height: 72px; object-fit: cover; }
.structure-grid-title { display: -webkit-box; min-height: 2.7em; overflow: hidden; line-height: 1.35; overflow-wrap: anywhere; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.structure-grid-meta { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>

<div class="bg-body structure-grid-scroll">
    <div class="row g-3 p-3 mb-0">
        <?php foreach ($dataProvider->getModels() as $item): ?>
            <?php
            $data = is_array($item->data_json) ? $item->data_json : [];
            $price = (float) ($item->price ?? 0);
            $titleName = $item->asset_name ?: ($item->AssetitemName() ?: '-');
            $location = trim((string) ($data['location'] ?? '')) ?: $item->departmentName();
            $groupName = trim((string) ($data['structure_group_name'] ?? ''));
            $subgroupName = trim((string) ($data['structure_subgroup_name'] ?? ''));
            $category = $groupName !== ''
                ? $groupName . ($subgroupName !== '' ? ' › ' . $subgroupName : '')
                : ($item->assetCategory?->title ?? $item->assetType?->title ?? '-');
            ?>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <?= Html::img($item->ShowImg()['image'], [
                                'class' => 'structure-grid-thumb rounded border flex-shrink-0',
                                'alt' => $titleName,
                            ]) ?>
                            <div class="min-w-0 flex-grow-1">
                                <div class="structure-grid-meta small text-body-secondary" title="<?= Html::encode($item->code ?: '-') ?>"><?= Html::encode($item->code ?: '-') ?></div>
                                <div class="structure-grid-meta small text-body-secondary">GFMIS: <?= Html::encode($item->gfmis ?: '-') ?></div>
                                <div class="structure-grid-title fw-semibold" title="<?= Html::encode($titleName) ?>"><?= Html::encode($titleName) ?></div>
                                <div class="mt-1"><?= $item->getConditionBadge() ?></div>
                            </div>
                            <div class="flex-shrink-0"><?= $item->getStatusBadge() ?></div>
                        </div>

                        <div class="structure-grid-meta small text-body-secondary mb-1" title="<?= Html::encode($category) ?>"><?= Html::encode($category) ?></div>
                        <div class="structure-grid-meta small text-body-secondary mb-3">
                            <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i><?= Html::encode($location ?: 'ไม่ระบุสถานที่') ?>
                            <span class="mx-1">·</span>ปี <?= Html::encode($item->on_year ?: '-') ?>
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-end gap-3">
                            <div>
                                <div class="small text-body-secondary">ราคาแรกรับ</div>
                                <div class="fw-bold text-primary-emphasis"><?= number_format($price, 2) ?></div>
                            </div>
                            <div class="d-flex gap-1">
                                <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $item->id], ['class' => 'btn btn-sm btn-primary', 'title' => 'ดูรายละเอียด', 'data-pjax' => 0]) ?>
                                <?php if (Yii::$app->user->can('asset')): ?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-warning', 'title' => 'แก้ไข', 'data-pjax' => 0]) ?>
                                <?php endif; ?>
                                <?= Html::a('<i class="bi bi-qr-code-scan"></i>', ['/am/asset/view-qr-pdf', 'id' => $item->id], ['class' => 'btn btn-sm btn-secondary', 'title' => 'พิมพ์', 'data-pjax' => 0, 'target' => '_blank']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($dataProvider->getCount() === 0): ?>
            <div class="col-12 py-5 text-center text-body-secondary">
                <i class="fa-solid fa-building-circle-xmark fs-2 mb-2 d-block" aria-hidden="true"></i>
                ไม่พบรายการสิ่งปลูกสร้างตามเงื่อนไขที่ค้นหา
            </div>
        <?php endif; ?>
    </div>
</div>
