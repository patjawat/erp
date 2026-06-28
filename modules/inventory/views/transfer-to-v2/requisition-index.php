<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $rows */
/** @var array $warehouseMap */
/** @var int $alreadyMigrated */
/** @var string $statusFilter */

$statusFilter = $statusFilter ?? 'pending';
$isSuccessMode = ($statusFilter === 'success');
$isAllMode = ($statusFilter === 'all');

$titles = [
    'pending' => 'ย้ายใบเบิก (PENDING) ไป Inventory V2',
    'success' => 'ย้ายใบจ่ายที่เสร็จแล้ว (SUCCESS) ไป Inventory V2',
    'all' => 'ย้ายใบเบิก-จ่าย V1 ไป Inventory V2',
];
$this->title = $titles[$statusFilter] ?? $titles['pending'];
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$warehouseName = function ($id) use ($warehouseMap) {
    if (!$id) return '-';
    $w = $warehouseMap[(int) $id] ?? null;
    return $w ? Html::encode($w['warehouse_name']) : ('#' . (int) $id);
};

$transferableCount = 0;
foreach ($rows as $r) {
    if (!empty($r['transferable'])) $transferableCount++;
}

$tabs = [
    'pending' => ['label' => 'ใบเบิก (รอจ่าย)', 'icon' => 'clock'],
    'success' => ['label' => 'ใบจ่ายที่เสร็จแล้ว', 'icon' => 'check-double'],
    'all' => ['label' => 'ทั้งหมด', 'icon' => 'list'],
];

if ($isAllMode) {
    $formAction = null; // โหมด all ใช้แยกย้ายไม่ได้ ต้องเลือก tab ก่อน
    $submitLabel = '';
} elseif ($isSuccessMode) {
    $formAction = ['/inventory/transfer-to-v2/create-issued'];
    $submitLabel = '<i class="fa-solid fa-file-import me-1"></i> ย้ายใบที่เลือก ไป V2 (สถานะ CONFIRMED + ตัดยอด)';
    $confirmMsg = 'ยืนยันย้ายใบจ่ายที่เลือก ไป V2 — ระบบจะตัด stock_balance ใน V2 ทันที (ยอดอาจติดลบ ให้ไปเคลียร์ที่เมนู "ปรับยอดคลัง > ยอดติดลบ")';
} else {
    $formAction = ['/inventory/transfer-to-v2/create-requisitions'];
    $submitLabel = '<i class="fa-solid fa-file-import me-1"></i> ย้ายใบที่เลือก ไป V2 (สถานะ PENDING)';
    $confirmMsg = 'ยืนยันย้ายใบเบิกที่เลือกไป Inventory V2 (สถานะรอหัวหน้าอนุมัติ)?';
}
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="share-2"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3">
    <?php foreach ($tabs as $key => $tab): ?>
        <li class="nav-item">
            <a class="nav-link <?= $statusFilter === $key ? 'active' : '' ?>"
               href="<?= Url::to(['requisition-index', 'status_filter' => $key]) ?>">
                <i class="fa-solid fa-<?= $tab['icon'] ?> me-1"></i> <?= $tab['label'] ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2 mb-0">
            <i class="fa-solid fa-clipboard-list me-1"></i>
            <?= Html::encode($this->title) ?>
            <span class="badge text-white bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1 ms-2">
                ใน list <?= count($rows) ?> ใบ
            </span>
            <span class="badge text-white bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">
                ย้ายได้ <?= $transferableCount ?> ใบ
            </span>
            <?php if ($alreadyMigrated > 0): ?>
            <span class="badge text-white bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                ย้ายแล้ว <?= $alreadyMigrated ?> ใบ (ซ่อน)
            </span>
            <?php endif; ?>
        </h6>
    </div>
    <div class="card-body">
        <?php if ($isSuccessMode): ?>
            <p class="text-muted small mb-0">
                ใบจ่ายที่ <code>order_status='success'</code> ใน V1 — เมื่อย้ายเข้า V2 จะเป็น <strong>CONFIRMED</strong> และ <strong>ตัด stock_balance ทันที</strong> (ผ่าน <code>InventoryService::adjustBalance(allowNegative=true)</code>)
                <br>ถ้ายอดติดลบ → ไปเคลียร์ที่ <code>ปรับยอดคลัง > ยอดติดลบ</code>
            </p>
        <?php elseif ($isAllMode): ?>
            <p class="text-muted small mb-0">
                แสดงทั้งใบเบิกค้างจ่ายและใบจ่ายที่เสร็จแล้ว — เพื่อย้ายข้อมูล กรุณาเลือก tab เฉพาะ (PENDING หรือ SUCCESS) เพราะวิธีการย้ายต่างกัน
            </p>
        <?php else: ?>
            <p class="text-muted small mb-0">
                ใบเบิกที่ <code>order_status='pending'</code> ใน V1 — ย้ายเข้า V2 เป็น <strong>PENDING (รอหัวหน้าอนุมัติ)</strong>, <code>source_type=REQUEST</code>
                <br>ระบบจะ <strong>ไม่แก้ไข</strong> ข้อมูลใน V1 — ใบที่ย้ายแล้วถ้าต้องการลบใน V1 ให้ทำแยกในระบบเก่า
            </p>
        <?php endif; ?>
    </div>

    <?php if (empty($rows)): ?>
        <div class="alert alert-info m-3">
            <i class="fa-solid fa-info-circle me-1"></i> ไม่พบใบ V1 ที่ตรงเงื่อนไข
        </div>
    <?php else: ?>
        <?php $form = \yii\widgets\ActiveForm::begin([
            'action' => $formAction ?: ['requisition-index'],
            'method' => 'post',
            'options' => ['id' => 'transfer-requisition-form'],
        ]); ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 48px;">
                            <?php if (!$isAllMode): ?>
                                <input type="checkbox" id="check-all" class="form-check-input">
                            <?php endif; ?>
                        </th>
                        <th style="width: 56px;">#</th>
                        <th>เลขใบ</th>
                        <th>สถานะ V1</th>
                        <th>วันที่</th>
                        <th>คลังหลัก (Main)</th>
                        <th>คลังย่อย (Sub)</th>
                        <th class="text-end">รายการ</th>
                        <th class="text-end">map V2 ได้</th>
                        <th class="text-end">skip</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                <?php $n = 1; foreach ($rows as $r): ?>
                    <?php
                        $rowClass = '';
                        $note = '';
                        if ($r['line_matched'] === 0) {
                            $rowClass = 'table-secondary text-muted';
                            $note = '<span class="badge bg-secondary">ไม่มีรายการ map ได้ — ข้าม</span>';
                        } elseif ($r['line_skipped'] > 0) {
                            $note = '<span class="text-warning small"><i class="fa-solid fa-triangle-exclamation"></i> จะข้าม item: '
                                . Html::encode(implode(', ', $r['skipped_codes'])) . '</span>';
                        }
                        $v1Status = (string) ($r['v1_status'] ?? '');
                        $statusClass = $v1Status === 'success' ? 'bg-success' : ($v1Status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary');
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td class="text-center">
                            <?php if (!$isAllMode && $r['transferable']): ?>
                                <input type="checkbox" name="order_ids[]" value="<?= $r['id'] ?>" class="form-check-input row-check">
                            <?php else: ?>
                                <input type="checkbox" disabled class="form-check-input">
                            <?php endif; ?>
                        </td>
                        <td><?= $n++ ?></td>
                        <td><code><?= Html::encode($r['code']) ?></code></td>
                        <td><span class="badge <?= $statusClass ?>"><?= Html::encode($v1Status ?: '-') ?></span></td>
                        <td><?= Html::encode($r['movement_date'] ?: '-') ?></td>
                        <td><?= $warehouseName($r['main_warehouse_id']) ?></td>
                        <td><?= $warehouseName($r['sub_warehouse_id']) ?></td>
                        <td class="text-end"><?= $r['line_total'] ?></td>
                        <td class="text-end text-success fw-medium"><?= $r['line_matched'] ?></td>
                        <td class="text-end <?= $r['line_skipped'] > 0 ? 'text-warning fw-medium' : 'text-muted' ?>">
                            <?= $r['line_skipped'] ?>
                        </td>
                        <td><?= $note ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!$isAllMode && $transferableCount > 0): ?>
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                <i class="fa-solid fa-info-circle me-1"></i>
                เลข order_no คงค่าเดิมจาก V1 — ระบบจะตรวจซ้ำก่อนสร้าง
            </span>
            <?= Html::submitButton($submitLabel, [
                'class' => $isSuccessMode ? 'btn btn-danger btn-sm' : 'btn btn-primary btn-sm',
                'data' => ['confirm' => $confirmMsg],
            ]) ?>
        </div>
        <?php endif; ?>

        <?php \yii\widgets\ActiveForm::end(); ?>
    <?php endif; ?>
</div>

<?php
$js = <<<JS
document.getElementById('check-all')?.addEventListener('change', function (e) {
    document.querySelectorAll('.row-check').forEach(function (cb) {
        cb.checked = e.target.checked;
    });
});
JS;
$this->registerJs($js);
