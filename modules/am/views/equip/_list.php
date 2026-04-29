<?php

use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\Employees;

/** @var yii\data\ActiveDataProvider $dataProvider */


$equipSubtitle = static function ($item): string {
    $dj = is_array($item->data_json) ? $item->data_json : [];
    $segments = [];
    foreach (['brand', 'asset_model'] as $k) {
        if (!empty($dj[$k])) {
            $segments[] = $dj[$k];
        }
    }
    if (!empty($dj['cpu'])) {
        $segments[] = $dj['cpu'];
    }
    if (!empty($dj['ram'])) {
        $segments[] = 'RAM ' . $dj['ram'];
    } elseif (!empty($dj['memory'])) {
        $segments[] = 'RAM ' . $dj['memory'];
    }
    if (!empty($dj['serial_number'])) {
        $segments[] = 'S/N: ' . $dj['serial_number'];
    }
    return implode(', ', $segments);
};
?>
<style>
    /* เฉพาะเลย์เอาต์ตาราง — สีตามธีมผ่านตัวแปร Bootstrap */
    .equip-register-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .equip-register-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: var(--bs-body-bg);
        color: var(--bs-secondary-color);
        font-size: 0.8125rem;
        font-weight: 600;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid var(--bs-border-color);
        white-space: nowrap;
        vertical-align: middle;
    }

    .equip-register-table tbody td {
        padding: 1rem 1rem;
        border-bottom: 1px solid var(--bs-border-color);
        vertical-align: middle;
        font-size: 0.9375rem;
        color: var(--bs-body-color);
    }

    .equip-register-table tbody tr:last-child td {
        border-bottom: none;
    }

    .equip-register-table tbody tr:hover td {
        background-color: var(--bs-secondary-bg);
    }

    .equip-list-scroll {
        max-height: min(68vh, 760px);
        overflow: auto;
    }

    /* คอลัมน์จัดการ: แคบเท่าที่จำเป็น + ปุ่มไม่หดคลิกยากบนมือถือ */
    .equip-register-table th.equip-actions-th,
    .equip-register-table td.equip-actions-cell {
        width: 150px;
    }

    .equip-register-table .equip-actions-inner .btn {
        flex-shrink: 0;
        min-width: 2.375rem;
        min-height: 2.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="equip-list-scroll">
    <div class="table-responsive">
        <table class="table equip-register-table mb-0">
            <thead style="background-color: white;">
                <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ข้อมูลครุภัณฑ์</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 160px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">หมวดหมู่</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 224px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานที่ตั้ง / ผู้รับผิดชอบ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 128px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">วันที่รับ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-end" style="width: 144px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ราคาแรกรับ (฿)</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สภาพ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานะ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 200px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $item): ?>
                    <?php
                    $price = (float) ($item->price ?? 0);
                    $location = '';
                    if (is_array($item->data_json) && !empty($item->data_json['location'])) {
                        $location = (string) $item->data_json['location'];
                    }
                    if ($location === '') {
                        $location = $item->departmentName();
                    }
                    $catTitle = $item->assetCategory?->title ?? $item->assetType?->title ?? '-';
                    $titleName = $item->asset_name ?: ($item->AssetitemName() ?: '-');
                    $ownerEmp = $item->ownerEmployee;
                    if ($ownerEmp === null && $item->owner !== null && $item->owner !== '' && is_numeric($item->owner)) {
                        $ownerEmp = Employees::findOne((int) $item->owner);
                    }
                    $ownerName = $ownerEmp?->fullname ?: '';
                    ?>

                    <tr style="border-bottom: 1px solid rgb(241, 245, 249);">
                        <td class="px-4 py-3 border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 border" style="width: 40px; height: 40px; background-color: rgb(248, 250, 252); border-color: rgb(226, 232, 240); color: rgb(148, 163, 184);">
                                    <?= Html::img(
                                        $item->ShowImg()['image'],
                                        [
                                            'class' => 'rounded border flex-shrink-0',
                                            'style' => 'width:56px;height:56px;object-fit:cover;',
                                            'alt' => $titleName,
                                        ]
                                    ) ?>
                                </div>
                                <div><span class="fw-bold d-block text-truncate" style="color: rgb(30, 41, 59); cursor: pointer; max-width: 200px;"><?= $titleName; ?></span>
                                    <div class="d-flex align-items-center mt-1 font-monospace" style="font-size: 11px; color: rgb(148, 163, 184);"><span><?= $item->code; ?></span></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0"><span class="badge rounded-2 fw-medium border" style="background-color: rgb(241, 245, 249); color: rgb(71, 85, 105); border-color: rgb(226, 232, 240); font-size: 11px; padding: 4px 10px;"><?= $catTitle ?></span></td>
                        <td class="px-4 py-3 border-0">
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center gap-2" style="color: rgb(30, 41, 59);"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin flex-shrink-0" aria-hidden="true">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg><span class="fw-semibold text-truncate" style="font-size: 14px; max-width: 180px;">งานรักษาความสะอาด</span></div>
                                <div class="d-flex align-items-center gap-2" style="color: rgb(100, 116, 139);"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user flex-shrink-0" aria-hidden="true">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg><span class="text-truncate" style="font-size: 12px; max-width: 180px;"><?= $ownerName ?></span></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0 text-center fw-medium" style="color: rgb(100, 116, 139); font-size: 12px;"><?= $item->receive_date ? Html::encode(Yii::$app->thaiFormatter->asDate($item->receive_date, 'medium')) : '-' ?></td>
                        <td class="px-4 py-3 border-0 text-end fw-bold font-monospace" style="color: rgb(30, 41, 59);"><?= number_format($price,2) ?></td>
                        <td class="px-4 py-3 border-0 text-center"><?= $item->getConditionBadge() ?></td>
                        <td class="px-4 py-3 border-0 text-center"><?= $item->getStatusBadge() ?></td>
                        <td class="text-center align-middle equip-actions-cell px-2 px-md-3  border-0">
                            <div class="equip-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-eye"></i>', ['view', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'บำรุงรักษา',
                                    'data-pjax' => 0,
                                ]) ?>
                                <?php if (Yii::$app->user->can('asset')): ?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-warning',
                                        'title' => 'แก้ไข',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>
                                <?= Html::a('<i class="bi bi-qr-code-scan"></i>', ['/am/asset/view-qr-pdf', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-light',
                                    'title' => 'พิมพ์',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
                                <?php if (Yii::$app->user->can('admin')): ?>
                                    <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-danger delete-asset',
                                        'title' => 'ลบ',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="card-footer bg-body border-top py-3 px-4">
    <?php
    echo DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]);
    ?>
</div>