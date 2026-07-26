<?php

use yii\helpers\Html;
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

$receiveAgeText = static function ($receiveDate): string {
    if (empty($receiveDate)) {
        return '';
    }

    try {
        $dateText = substr((string) $receiveDate, 0, 10);
        $receivedAt = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateText) ?: new \DateTimeImmutable((string) $receiveDate);
        $today = new \DateTimeImmutable('today');
    } catch (\Throwable $e) {
        return '';
    }

    if ($receivedAt > $today) {
        return 'ยังไม่ถึงวันที่รับ';
    }

    $diff = $receivedAt->diff($today);
    if ($diff->y === 0 && $diff->m === 0 && $diff->d === 0) {
        return 'รับวันนี้';
    }

    $parts = [];
    if ($diff->y > 0) {
        $parts[] = $diff->y . ' ปี';
    }
    if ($diff->m > 0) {
        $parts[] = $diff->m . ' เดือน';
    }

    if ($diff->d > 0) {
        $parts[] = $diff->d . ' วัน';
    }

    return 'ผ่านมา ' . implode(' ', $parts);
};

// สิทธิแก้ไข inline ใช้เกณฑ์เดียวกับปุ่ม "แก้ไข" ในคอลัมน์จัดการ
$canEdit = Yii::$app->user->can('asset');

// สร้าง attribute สำหรับเซลล์ที่แก้ไขได้ผ่าน popover
$qedit = static function (string $field, string $type, $value, string $title) use ($canEdit): string {
    if (!$canEdit) {
        return '';
    }
    return ' class="qedit" role="button" tabindex="0"'
        . ' data-qedit-field="' . Html::encode($field) . '"'
        . ' data-qedit-type="' . Html::encode($type) . '"'
        . ' data-qedit-value="' . Html::encode((string) ($value ?? '')) . '"'
        . ' data-qedit-title="' . Html::encode($title) . '"';
};
?>

<style>
.equip-register-table {
    min-width: 1420px;
    color: var(--bs-body-color);
}
.equip-register-table > thead {
    background-color: var(--bs-tertiary-bg);
}
.equip-register-table > thead > tr,
.equip-register-table > tbody > tr:not(.equip-group-row) {
    border-bottom: 1px solid var(--bs-border-color-translucent);
}
.equip-register-table .equip-table-heading {
    color: var(--bs-secondary-color);
    font-size: 0.6875rem;
    letter-spacing: 0.05em;
}
.equip-col-select { width: 44px; }
.equip-col-asset { min-width: 220px; }
.equip-col-gfmis { width: 140px; }
.equip-col-category { width: 160px; }
.equip-col-assignment { width: 224px; }
.equip-col-date { width: 155px; }
.equip-col-price { width: 144px; }
.equip-col-risk { width: 140px; }
.equip-col-status { width: 112px; }
.equip-col-actions { width: 200px; }
.equip-register-table .equip-group-row > td {
    background-color: var(--bs-secondary-bg);
    border-top: 1px solid var(--bs-border-color);
    border-bottom: 1px solid var(--bs-border-color);
}
.equip-group-title {
    color: var(--bs-emphasis-color);
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}
.equip-group-count,
.equip-meta-xs {
    color: var(--bs-secondary-color);
    font-size: 0.6875rem;
}
.equip-thumb-shell {
    width: 56px;
    height: 56px;
    background-color: var(--bs-tertiary-bg);
    color: var(--bs-tertiary-color);
}
.equip-thumb-image {
    width: 56px;
    height: 56px;
    object-fit: cover;
}
.equip-asset-title {
    max-width: 200px;
    color: var(--bs-emphasis-color);
    cursor: pointer;
}
.equip-assignment-value { max-width: 180px; }
.equip-risk-stack { min-width: 92px; }
</style>

<div class="equip-list-scroll">
    <div class="table-responsive">
        <table class="table align-middle equip-register-table mb-0">
            <thead>
                <tr>
                    <?php if ($canEdit): ?>
                        <th class="equip-col-select px-3 py-3 border-0 text-center align-middle">
                            <input type="checkbox" class="form-check-input equip-bulk-all" aria-label="เลือกทั้งหมด">
                        </th>
                    <?php endif; ?>
                    <th class="equip-table-heading equip-col-asset px-4 py-3 border-0 text-uppercase fw-bold">ข้อมูลครุภัณฑ์</th>
                    <th class="equip-table-heading equip-col-gfmis px-4 py-3 border-0 text-uppercase fw-bold">GFMIS</th>
                    <th class="equip-table-heading equip-col-category px-4 py-3 border-0 text-uppercase fw-bold">หมวดหมู่</th>
                    <th class="equip-table-heading equip-col-assignment px-4 py-3 border-0 text-uppercase fw-bold">สถานที่ตั้ง / ผู้รับผิดชอบ</th>
                    <th class="equip-table-heading equip-col-date px-4 py-3 border-0 text-uppercase fw-bold text-center">วันที่รับ</th>
                    <th class="equip-table-heading equip-col-price px-4 py-3 border-0 text-uppercase fw-bold text-end">ราคาแรกรับ (฿)</th>
                    <th class="equip-table-heading equip-col-risk px-3 py-3 border-0 text-uppercase fw-bold text-center">สภาพ · ความเสี่ยง</th>
                    <th class="equip-table-heading equip-col-status px-4 py-3 border-0 text-uppercase fw-bold text-center">สถานะ</th>
                    <th class="equip-table-heading equip-col-actions px-4 py-3 border-0 text-uppercase fw-bold text-center">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // จัดกลุ่มรายการตาม "ประเภท" (asset_type); หมวดหมู่ (asset_category) แสดงเป็น badge รายแถว
                $groups = [];
                foreach ($dataProvider->getModels() as $m) {
                    $groupTitle = trim((string) ($m->assetType?->title ?? '')) ?: 'ไม่ระบุประเภท';
                    $groups[$groupTitle][] = $m;
                }
                ?>
                <?php foreach ($groups as $groupTitle => $groupItems): ?>
                    <tr class="equip-group-row">
                        <td colspan="<?= $canEdit ? 10 : 9 ?>" class="px-4 py-2">
                            <span class="equip-group-title fw-bold text-uppercase"><?= Html::encode((string) $groupTitle) ?></span>
                            <span class="equip-group-count ms-2">(<?= count($groupItems) ?>)</span>
                        </td>
                    </tr>
                    <?php foreach ($groupItems as $item): ?>
                    <?php
                    $price = (float) ($item->price ?? 0);
                    $location = '';
                    if (is_array($item->data_json) && !empty($item->data_json['location'])) {
                        $location = (string) $item->data_json['location'];
                    }
                    if ($location === '') {
                        $location = $item->departmentName();
                    }
                    // คอลัมน์ "หมวดหมู่" อิงหมวดทรัพย์สิน (asset_category) เท่านั้น ไม่ fallback ไปประเภท
                    $catTitle = trim((string) ($item->assetCategory?->title ?? ''));
                    $catUnset = $catTitle === '';
                    if ($catUnset) {
                        $catTitle = 'ไม่ระบุ';
                    }
                    $titleName = $item->asset_name ?: ($item->AssetitemName() ?: '-');
                    $licensePlate = trim((string) ($item->license_plate ?? ''));
                    $ownerEmp = $item->ownerEmployee;
                    if ($ownerEmp === null && $item->owner !== null && $item->owner !== '' && is_numeric($item->owner)) {
                        $ownerEmp = Employees::findOne((int) $item->owner);
                    }
                    $ownerName = $ownerEmp?->fullname ?: '';
                    ?>

                    <tr data-qedit-id="<?= (int) $item->id ?>">
                        <?php if ($canEdit): ?>
                            <td class="px-3 py-3 border-0 text-center align-middle">
                                <input type="checkbox" class="form-check-input equip-bulk-check" value="<?= (int) $item->id ?>" aria-label="เลือกรายการ">
                            </td>
                        <?php endif; ?>
                        <td class="px-4 py-3 border-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="equip-thumb-shell rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 border">
                                    <?= Html::img(
                                        $item->ShowImg()['image'],
                                        [
                                            'class' => 'equip-thumb-image rounded border flex-shrink-0',
                                            'alt' => $titleName,
                                        ]
                                    ) ?>
                                </div>
                                <div><span class="equip-asset-title fw-bold d-block text-truncate"><?= Html::encode($titleName) ?></span>
                                    <div class="equip-meta-xs d-flex align-items-center mt-1 font-monospace"><span><?= Html::encode($item->code) ?></span></div>
                                    <?php if ($licensePlate !== ''): ?>
                                        <div class="d-flex align-items-center mt-1 gap-1"><span class="equip-meta-xs">ทะเบียน:</span> <span class="fw-bold small text-body"><?= Html::encode($licensePlate) ?></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0">
                            <span class="font-monospace small text-body-secondary"><?= Html::encode($item->gfmis ?: '-') ?></span>
                        </td>
                        <td class="px-4 py-3 border-0">
                            <?php if ($catUnset): ?>
                                <?php $catBadge = '<span class="badge rounded-2 fw-medium bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">' . Html::encode($catTitle) . '</span>'; ?>
                            <?php else: ?>
                                <?php $catBadge = '<span class="badge rounded-2 fw-medium bg-body-tertiary text-body-secondary border px-2 py-1">' . Html::encode($catTitle) . '</span>'; ?>
                            <?php endif; ?>
                            <?php if ($canEdit): ?>
                                <a href="<?= \yii\helpers\Url::to(['/am/equip/quick-edit', 'id' => $item->id, 'section' => 'category']) ?>" class="open-modal qedit-modal text-decoration-none" data-size="modal-md" title="เปลี่ยนประเภท / หมวดหมู่"><?= $catBadge ?></a>
                            <?php else: ?>
                                <?= $catBadge ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 border-0">
                            <?php
                            $assignBlock = '<div class="d-flex flex-column gap-1">'
                                . '<div class="d-flex align-items-center gap-2 text-body"><i class="bi bi-geo-alt text-body-secondary flex-shrink-0" aria-hidden="true"></i><span class="equip-assignment-value fw-semibold text-truncate">' . Html::encode($item->departmentName() ?: 'ไม่ระบุ') . '</span></div>'
                                . '<div class="d-flex align-items-center gap-2 text-body-secondary"><i class="bi bi-person flex-shrink-0" aria-hidden="true"></i><span class="equip-assignment-value small text-truncate">' . Html::encode($ownerName ?: 'ไม่ระบุ') . '</span></div>'
                                . '</div>';
                            ?>
                            <?php if ($canEdit): ?>
                                <a href="<?= \yii\helpers\Url::to(['/am/equip/quick-edit', 'id' => $item->id, 'section' => 'assignment']) ?>" class="open-modal qedit-modal text-decoration-none d-block" data-size="modal-md" title="เปลี่ยนสถานที่ / ผู้รับผิดชอบ"><?= $assignBlock ?></a>
                            <?php else: ?>
                                <?= $assignBlock ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 border-0 text-center fw-medium small text-body-secondary">
                            <span<?= $qedit('receive_date', 'date', $item->receive_date ? \app\components\AppHelper::DateFormDb(substr((string) $item->receive_date, 0, 10)) : '', 'วันที่รับเข้า') ?>>
                            <?php if ($item->receive_date): ?>
                                <div><?= Html::encode(Yii::$app->thaiFormatter->asDate($item->receive_date, 'medium')) ?></div>
                                <div class="small text-primary mt-1"><?= Html::encode($receiveAgeText($item->receive_date)) ?></div>
                            <?php else: ?>
                                <span class="text-body-tertiary">ไม่ระบุ</span>
                            <?php endif; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 border-0 text-end fw-bold font-monospace text-body-emphasis"><span<?= $qedit('price', 'number', $price, 'ราคาแรกรับ (บาท)') ?>><?= number_format($price, 2) ?></span></td>
                        <td class="px-3 py-3 border-0 text-center">
                            <div class="equip-risk-stack d-inline-flex flex-column align-items-stretch gap-1">
                                <span<?= $qedit('asset_condition', 'enum', $item->asset_condition, 'สภาพครุภัณฑ์') ?>><?= $item->getConditionBadge() ?></span>
                                <span<?= $qedit('risk_level', 'enum', $item->risk_level, 'ระดับความเสี่ยง') ?>><?= $item->getRiskLevelBadge() ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0 text-center"><span<?= $qedit('asset_status', 'enum', $item->asset_status, 'สถานะ') ?>><?= $item->getStatusBadge() ?></span></td>
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
                              
                                <div class="dropdown flex-grow-1 flex-sm-grow-0">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle w-100 w-sm-auto" type="button"
                                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                                        <li>
                                              <?= Html::a('<i class="bi bi-qr-code-scan me-2"></i>พิมพ์สติกเกอร์', ['/am/asset/view-qr', 'id' => $item->id], [
                                    'class' => 'dropdown-item',
                                    'title' => 'พิมพ์',
                                    'data-pjax' => 0,
                                    'target' => '_blank',
                                ]) ?>
                                        </li>
                                        <li>
                                        <?= Html::a('<i class="fa-solid fa-print me-2"></i> พิมพ์ค่าเสื่อม', ['/am/asset/depreciation', 'id' => $item->id], ['class' => 'open-modal w-100 dropdown-item', 'data' => ['size' => 'modal-lg']]) ?>
                                        </li>
                                        <li>
                                            <?= Html::a('<i class="fa-solid fa-triangle-exclamation me-2"></i> ส่งซ่อม / แจ้งปัญหา', ['/me/repair-v2/create', 'asset_number' => $item->code, 'send_type' => 'asset', 'container' => 'ma-container', 'title' => '<i class="fa-solid fa-circle-info fs-3"></i>  ส่งซ่อม'], ['class' => 'open-modal dropdown-item', 'data' => ['size' => 'modal-lg']]) ?>
                                        </li>
                                        
                                        <?php if (Yii::$app->user->can('admin')): ?>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <?= Html::a('<i class="fa-regular fa-trash-can me-2"></i> ลบข้อมูล', ['delete', 'id' => $item->id], [
                                                    'class' => 'dropdown-item delete-asset',
                                                    'title' => 'ลบ',
                                                    'data-pjax' => 0,
                                                ]) ?>
                                        </li>
                                            <?php endif; ?>
                                    </ul>
                                </div>

                            </div>
                        </td>
                    </tr>

                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canEdit): ?>
    <?= $this->render('_quick_edit') ?>
    <?= $this->render('_bulk_actions') ?>
<?php endif; ?>
