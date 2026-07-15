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

// สิทธิแก้ไข inline — ใช้เกณฑ์เดียวกับปุ่ม "แก้ไข" ในคอลัมน์จัดการ
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


<div class="equip-list-scroll">
    <div class="table-responsive">
        <table class="table equip-register-table mb-0">
            <thead style="background-color: white;">
                <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                    <?php if ($canEdit): ?>
                        <th class="px-3 py-3 border-0 text-center align-middle" style="width: 44px;">
                            <input type="checkbox" class="form-check-input equip-bulk-all" aria-label="เลือกทั้งหมด">
                        </th>
                    <?php endif; ?>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="min-width: 220px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ข้อมูลครุภัณฑ์</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 140px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">GFMIS</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 160px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">หมวดหมู่</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold" style="width: 224px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานที่ตั้ง / ผู้รับผิดชอบ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 155px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">วันที่รับ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-end" style="width: 144px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">ราคาแรกรับ (฿)</th>
                    <th class="px-3 py-3 border-0 text-uppercase fw-bold text-center" style="width: 140px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สภาพ · ความเสี่ยง</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 112px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">สถานะ</th>
                    <th class="px-4 py-3 border-0 text-uppercase fw-bold text-center" style="width: 200px; font-size: 11px; color: rgb(148, 163, 184); letter-spacing: 0.05em;">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // จัดกลุ่มรายการตาม "ประเภท" (asset_type) — หมวดหมู่ (asset_category) แสดงเป็น badge รายแถว
                $groups = [];
                foreach ($dataProvider->getModels() as $m) {
                    $groupTitle = trim((string) ($m->assetType?->title ?? '')) ?: 'ไม่ระบุประเภท';
                    $groups[$groupTitle][] = $m;
                }
                ?>
                <?php foreach ($groups as $groupTitle => $groupItems): ?>
                    <tr class="equip-group-row">
                        <td colspan="<?= $canEdit ? 10 : 9 ?>" class="px-4 py-2 border-0" style="background-color: rgb(248, 250, 252); border-top: 1px solid rgb(226, 232, 240); border-bottom: 1px solid rgb(226, 232, 240);">
                            <span class="fw-bold text-uppercase" style="font-size: 12px; letter-spacing: 0.05em; color: rgb(71, 85, 105);"><?= Html::encode((string) $groupTitle) ?></span>
                            <span class="ms-2" style="font-size: 11px; color: rgb(148, 163, 184);">(<?= count($groupItems) ?>)</span>
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
                    // คอลัมน์ "หมวดหมู่" อิงหมวดทรัพย์สิน (asset_category) เท่านั้น — ไม่ fallback ไปประเภท
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

                    <tr data-qedit-id="<?= (int) $item->id ?>" style="border-bottom: 1px solid rgb(241, 245, 249);">
                        <?php if ($canEdit): ?>
                            <td class="px-3 py-3 border-0 text-center align-middle">
                                <input type="checkbox" class="form-check-input equip-bulk-check" value="<?= (int) $item->id ?>" aria-label="เลือกรายการ">
                            </td>
                        <?php endif; ?>
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
                                    <?php if ($licensePlate !== ''): ?>
                                        <div class="d-flex align-items-center mt-1"><span style="font-size: 11px; color: rgb(100, 116, 139);">ทะเบียน :</span> <span class="fw-bold"><?= Html::encode($licensePlate) ?></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 border-0">
                            <span class="font-monospace" style="font-size: 12px; color: rgb(71, 85, 105);"><?= Html::encode($item->gfmis ?: '-') ?></span>
                        </td>
                        <td class="px-4 py-3 border-0">
                            <?php if ($catUnset): ?>
                                <?php $catBadge = '<span class="badge rounded-2 fw-medium bg-warning text-dark" style="font-size: 11px; padding: 4px 10px;">' . Html::encode($catTitle) . '</span>'; ?>
                            <?php else: ?>
                                <?php $catBadge = '<span class="badge rounded-2 fw-medium border" style="background-color: rgb(241, 245, 249); color: rgb(71, 85, 105); border-color: rgb(226, 232, 240); font-size: 11px; padding: 4px 10px;">' . Html::encode($catTitle) . '</span>'; ?>
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
                                . '<div class="d-flex align-items-center gap-2" style="color: rgb(30, 41, 59);"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin flex-shrink-0" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg><span class="fw-semibold text-truncate" style="font-size: 14px; max-width: 180px;">' . Html::encode($item->departmentName() ?: '—') . '</span></div>'
                                . '<div class="d-flex align-items-center gap-2" style="color: rgb(100, 116, 139);"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user flex-shrink-0" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg><span class="text-truncate" style="font-size: 12px; max-width: 180px;">' . Html::encode($ownerName ?: '—') . '</span></div>'
                                . '</div>';
                            ?>
                            <?php if ($canEdit): ?>
                                <a href="<?= \yii\helpers\Url::to(['/am/equip/quick-edit', 'id' => $item->id, 'section' => 'assignment']) ?>" class="open-modal qedit-modal text-decoration-none d-block" data-size="modal-md" title="เปลี่ยนสถานที่ / ผู้รับผิดชอบ"><?= $assignBlock ?></a>
                            <?php else: ?>
                                <?= $assignBlock ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 border-0 text-center fw-medium" style="color: rgb(100, 116, 139); font-size: 12px;">
                            <span<?= $qedit('receive_date', 'date', $item->receive_date ? \app\components\AppHelper::DateFormDb(substr((string) $item->receive_date, 0, 10)) : '', 'วันที่รับเข้า') ?>>
                            <?php if ($item->receive_date): ?>
                                <div><?= Html::encode(Yii::$app->thaiFormatter->asDate($item->receive_date, 'medium')) ?></div>
                                <div class="small text-primary mt-1"><?= Html::encode($receiveAgeText($item->receive_date)) ?></div>
                            <?php else: ?>
                                <span class="text-body-tertiary">—</span>
                            <?php endif; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 border-0 text-end fw-bold font-monospace" style="color: rgb(30, 41, 59);"><span<?= $qedit('price', 'number', $price, 'ราคาแรกรับ (บาท)') ?>><?= number_format($price, 2) ?></span></td>
                        <td class="px-3 py-3 border-0 text-center">
                            <div class="d-inline-flex flex-column align-items-stretch gap-1" style="min-width: 92px;">
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
