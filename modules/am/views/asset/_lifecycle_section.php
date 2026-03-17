<?php

use yii\helpers\Html;
use app\modules\am\models\AssetDetail;
use app\modules\hr\models\Organization;

/** @var app\modules\am\models\Asset $model */

$transactions = $model->getTransactions()->with('createdBy')->limit(20)->all();
$repairTransactions = $model->getRepairTransactions()->limit(10)->all();
$lifecycleLabels = [
    $model::LIFECYCLE_RECEIVED => 'รับเข้า',
    $model::LIFECYCLE_ACTIVE => 'ใช้งาน',
    $model::LIFECYCLE_REPAIR => 'ส่งซ่อม',
    $model::LIFECYCLE_DISPOSED => 'จำหน่าย',
];
?>
<div class="row g-3">
    <div class="col-12 col-md-4">
        <h6 class="text-uppercase text-secondary mb-2">QR Code</h6>
        <div class="border rounded p-2 d-inline-block">
            <?php if (!empty($model->qr_code_path)): ?>
                <?= Html::img($model->qr_code_path, ['alt' => $model->code, 'style' => 'width:120px;height:120px;']) ?>
            <?php else:
                $src = $model->QrCode();
                if (is_string($src) && $src !== ''): ?>
                <?= Html::img($src, ['alt' => $model->code, 'style' => 'width:120px;height:120px;']) ?>
            <?php else: ?>
                <span class="text-muted small">ไม่มีรหัสครุภัณฑ์</span>
            <?php endif; endif; ?>
        </div>
        <div class="mt-2">
            <?= Html::a('พิมพ์ QR', ['/am/asset/print-qr', 'ids' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <h6 class="text-uppercase text-secondary mb-2">สถานะวงจรชีวิต</h6>
        <p class="mb-0">
            <?php
            $status = $model->lifecycle_status ?? $model::LIFECYCLE_ACTIVE;
            $label = $lifecycleLabels[$status] ?? $status;
            $color = $status === $model::LIFECYCLE_ACTIVE ? 'success' : ($status === $model::LIFECYCLE_REPAIR ? 'warning' : ($status === $model::LIFECYCLE_DISPOSED ? 'secondary' : 'primary'));
            ?>
            <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($label) ?></span>
        </p>
        <div class="mt-2 small">
            <?= Html::a('โอนย้าย', ['/am/asset-lifecycle/transfer', 'asset_id' => $model->id], ['class' => 'me-2']) ?>
            <?= Html::a('ส่งซ่อม', ['/am/asset-lifecycle/repair', 'asset_id' => $model->id], ['class' => 'me-2']) ?>
            <?= Html::a('จำหน่าย', ['/am/asset-lifecycle/dispose', 'asset_id' => $model->id], ['class' => 'me-2']) ?>
            <?php if (($model->lifecycle_status ?? '') === $model::LIFECYCLE_REPAIR): ?>
                <?= Html::a('รับคืนจากซ่อม', ['/am/asset-lifecycle/return-repair', 'id' => $model->id], ['class' => 'btn btn-sm btn-success', 'data-method' => 'post']) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-12">
        <h6 class="text-uppercase text-secondary mb-2">ประวัติการเคลื่อนย้าย/วงจรชีวิต</h6>
        <?php if (!empty($transactions)): ?>
        <table class="table table-hover align-middle mb-0 table-sm">
            <thead class="table-light">
                <tr>
                    <th>วันเวลา</th>
                    <th>ประเภท</th>
                    <th>จาก → ถึง</th>
                    <th>ผู้โอนย้าย/ผู้บันทึก</th>
                    <th>หมายเหตุ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($transactions as $t):
                    $dj = is_array($t->data_json) ? $t->data_json : (is_string($t->data_json) ? (json_decode($t->data_json, true) ?: []) : []);
                    $toDept = isset($dj['to_department']) && $dj['to_department'] ? Organization::findOne($dj['to_department']) : null;
                    $toDeptName = $toDept ? $toDept->name : '-';
                    $byUser = null;
                    if (!empty($dj['transferred_by']['username'])) {
                        $byUser = $dj['transferred_by']['username'];
                    } elseif ($t->created_by && $t->createdBy) {
                        $byUser = $t->createdBy->username ?? ('#' . $t->created_by);
                    }
                ?>
                <tr>
                    <td><?= Yii::$app->formatter->asDatetime($t->created_at) ?></td>
                    <td><?= Html::encode(AssetDetail::lifecycleTypeLabel($dj['transaction_type'] ?? '')) ?></td>
                    <td><?= Html::encode(($dj['from_location'] ?? '-') . ' → ' . ($dj['to_location'] ?? '-')) ?> <?= !empty($dj['to_department']) ? ' (หน่วยงาน: ' . Html::encode($toDeptName) . ')' : '' ?></td>
                    <td><?= $byUser ? Html::encode($byUser) : '-' ?></td>
                    <td><?= Html::encode($dj['remark'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted mb-0">ยังไม่มีประวัติ</p>
        <?php endif; ?>
    </div>
</div>
<?php if (!empty($repairTransactions)): ?>
<div class="row g-3 mt-1">
    <div class="col-12">
        <h6 class="text-uppercase text-secondary mb-2">ประวัติซ่อม</h6>
        <table class="table table-hover align-middle mb-0 table-sm">
            <thead class="table-light">
                <tr>
                    <th>วันเวลา</th>
                    <th>รายละเอียด</th>
                    <th>ค่าซ่อม</th>
                    <th>ผู้ซ่อม</th>
                </tr>
            </thead>
            <tbody class="table-group-divider align-middle">
                <?php foreach ($repairTransactions as $t):
                    $dj = is_array($t->data_json) ? $t->data_json : (is_string($t->data_json) ? (json_decode($t->data_json, true) ?: []) : []);
                ?>
                <tr>
                    <td><?= Yii::$app->formatter->asDatetime($t->created_at) ?></td>
                    <td><?= Html::encode($dj['remark'] ?? '-') ?></td>
                    <td><?= isset($dj['repair_cost']) && $dj['repair_cost'] !== '' ? number_format($dj['repair_cost']) . ' บาท' : '-' ?></td>
                    <td><?= Html::encode($dj['vendor'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
