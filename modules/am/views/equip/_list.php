<?php

use yii\helpers\Html;
use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\Employees;

/** @var yii\data\ActiveDataProvider $dataProvider */

$statusBadge = static function ($item): string {
    $st = (int) $item->asset_status;
    $label = Html::encode($item->statusName() ?: '-');
    if ($st === 1) {
        return '<span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success-subtle fw-medium px-2 py-2">' . $label . '</span>';
    }
    if (in_array($st, [3, 5], true)) {
        return '<span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger-subtle fw-medium px-2 py-2">' . $label . '</span>';
    }
    return '<span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle fw-medium px-2 py-2">' . $label . '</span>';
};

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
<div class="bg-body">
    <div class="equip-list-scroll">
        <div class="table-responsive">
            <table class="table equip-register-table mb-0">
                <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อครุภัณฑ์ / ยี่ห้อ</th>
                    <th>หมวด</th>
                    <th class="text-end">ราคาแรกรับ</th>
                    <th>ที่ตั้ง</th>
                    <th>ผู้รับผิดชอบ</th>
                    <th>วันรับ</th>
                    <th class="text-center">สภาพ</th>
                    <th class="text-center equip-actions-th">จัดการ</th>
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
                    <tr>
                        <td>
                            <?= Html::a(Html::encode($item->code ?: '-'), ['view-asset', 'id' => $item->id], [
                                'class' => 'fw-semibold link-primary text-decoration-none',
                            ]) ?>
                        </td>
                        <td>
                            <div class="d-flex gap-3 align-items-center">
                                <?= Html::a(
                                    Html::img(
                                        $item->ShowImg()['image'],
                                        [
                                            'class' => 'rounded border flex-shrink-0',
                                            'style' => 'width:56px;height:56px;object-fit:cover;',
                                            'alt' => $titleName,
                                        ]
                                    ),
                                    ['view-asset', 'id' => $item->id],
                                    [
                                        'class' => 'flex-shrink-0 text-decoration-none',
                                        'data-pjax' => 0,
                                        'title' => 'ดูรายละเอียด',
                                    ]
                                ) ?>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold text-body"><?= Html::encode($titleName) ?></div>
                                    <?php $sub = $equipSubtitle($item); ?>
                                    <?php if ($sub !== ''): ?>
                                        <div class="small text-muted mt-1 lh-sm text-break"><?= Html::encode($sub) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-medium px-3 py-2"><?= Html::encode($catTitle) ?></span>
                        </td>
                        <td class="text-end text-nowrap"><span class="fw-bold text-body font-monospace"><?= Html::encode(number_format($price, 2)) ?></span></td>
                        <td class="text-muted small"><?= Html::encode($location ?: '-') ?></td>
                        <td class="text-muted small"><?= Html::encode($ownerName ?: '-') ?></td>
                        <td class="text-muted small text-nowrap"><?= $item->receive_date ? Html::encode(Yii::$app->thaiFormatter->asDate($item->receive_date, 'medium')) : '-' ?></td>
                        <td class="text-center"><?= $statusBadge($item) ?></td>
                        <td class="text-center align-middle equip-actions-cell px-2 px-md-3">
                            <div class="equip-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-solid fa-eye" aria-hidden="true"></i>', ['maintenance', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'บำรุงรักษา',
                                    'data-pjax' => 0,
                                ]) ?>
                                <?php if (Yii::$app->user->can('asset')): ?>
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>', ['update', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-warning',
                                        'title' => 'แก้ไข',
                                        'data-pjax' => 0,
                                    ]) ?>
                                <?php endif; ?>
                                <?php if (Yii::$app->user->can('admin')): ?>
                                    <?= Html::a('<i class="fa-solid fa-trash" aria-hidden="true"></i>', ['delete', 'id' => $item->id], [
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
</div>
<div class="card-footer bg-body border-top py-3 px-4">
    <?php
    echo DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]);
    ?>
</div>
