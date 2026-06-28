<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\inventoryV2\models\StockOrder $model */

$marker = $model->getMigratedFromV1();
if (!$marker) {
    return;
}

$migratedAt = !empty($marker['migrated_at']) ? date('d/m/Y H:i', (int) $marker['migrated_at']) : '-';
$migratedByName = '';
if (!empty($marker['migrated_by'])) {
    $emp = \app\modules\hr\models\Employees::find()
        ->where(['user_id' => (int) $marker['migrated_by']])
        ->one();
    if ($emp) {
        $migratedByName = trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? ''));
    }
    if ($migratedByName === '') {
        $migratedByName = 'user #' . (int) $marker['migrated_by'];
    }
}

$sourceUrl = !empty($marker['source_id'])
    ? Url::to(['/inventory/stock-order/view', 'id' => (int) $marker['source_id']])
    : null;
?>
<div class="alert alert-secondary border-secondary-subtle d-flex flex-wrap align-items-center gap-2 mb-3 py-2">
    <span class="badge bg-secondary">V1</span>
    <span class="me-2">
        <strong>เอกสารนี้ย้ายมาจากระบบเก่า (Inventory V1)</strong>
    </span>
    <span class="text-muted small">
        เลขที่เดิม: <code><?= Html::encode($marker['source_code'] ?? '-') ?></code>
        <?php if (!empty($marker['source_status'])): ?>
            · สถานะเดิม: <code><?= Html::encode($marker['source_status']) ?></code>
        <?php endif; ?>
        <?php if (!empty($marker['v1_movement_date'])): ?>
            · วันที่เดิม: <?= Html::encode($marker['v1_movement_date']) ?>
        <?php endif; ?>
        · ย้ายเมื่อ: <?= Html::encode($migratedAt) ?>
        <?php if ($migratedByName): ?>
            · โดย: <?= Html::encode($migratedByName) ?>
        <?php endif; ?>
    </span>
    <?php if ($sourceUrl): ?>
        <?= Html::a('<i class="bi bi-box-arrow-up-right me-1"></i> ดูต้นฉบับใน V1', $sourceUrl, [
            'class' => 'btn btn-sm btn-outline-secondary ms-auto',
            'target' => '_blank',
        ]) ?>
    <?php endif; ?>
</div>
