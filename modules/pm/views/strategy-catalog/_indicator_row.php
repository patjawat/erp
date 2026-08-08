<?php

use yii\helpers\Html;
use app\modules\pm\models\{StrategyIndicator, StrategyIndicatorYear};

/** @var StrategyIndicatorYear $entry @var bool $canEdit */

$indicator = $entry->indicator;
$cancelled = $entry->isCancelled();
?>
<tr class="<?= $cancelled ? 'opacity-75' : '' ?>">
    <td class="ps-4">
        <div class="fw-semibold"><?= Html::encode($indicator->code ?: '-') ?></div>
        <div class="small text-muted"><?= Html::encode(StrategyIndicator::levelList()[$indicator->level] ?? $indicator->level) ?></div>
    </td>
    <td>
        <div><?= Html::encode($entry->displayName()) ?></div>
        <?php if ($entry->name_override): ?><div class="small text-warning-emphasis">ปรับนิยามเฉพาะปีนี้</div><?php endif; ?>
        <?php if ($entry->copied_from_id): ?><div class="small text-muted">คัดลอกจากปี <?= (int) $entry->copiedFrom?->fiscal_year ?></div><?php endif; ?>
        <?php if ($cancelled && $entry->cancelled_reason): ?><div class="small text-muted">เหตุผล: <?= Html::encode($entry->cancelled_reason) ?></div><?php endif; ?>
    </td>
    <td class="text-nowrap">
        <?= Html::encode(($entry->operator ? StrategyIndicatorYear::operatorList()[$entry->operator] . ' ' : '') . ($entry->target_value ?? '-')) ?>
        <span class="small text-muted"><?= Html::encode($entry->displayUnit() ?? '') ?></span>
        <?php if ($entry->baseline_value !== null): ?><div class="small text-muted">ค่าฐาน <?= Html::encode($entry->baseline_value) ?></div><?php endif; ?>
    </td>
    <td>
        <span class="badge <?= $cancelled ? 'bg-secondary-subtle text-secondary' : 'bg-success-subtle text-success' ?>">
            <?= Html::encode(StrategyIndicatorYear::statusList()[$entry->status] ?? $entry->status) ?>
        </span>
        <?php if (!$cancelled): ?><div class="small text-muted mt-1">ผลรายเดือน <?= $entry->monthsFilled() ?>/12</div><?php endif; ?>
    </td>
    <td class="text-end pe-4">
        <?= Html::a('รายละเอียด', ['template', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        <?php if ($canEdit): ?>
            <?= Html::a('ผลรายเดือน', ['monthly', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= Html::a('แก้ไข', ['update', 'type' => 'indicator', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
            <?= $cancelled
                ? Html::a('กลับมาใช้', ['restore-year', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-success', 'data-method' => 'post'])
                : Html::a('ยกเลิกในปีนี้', ['cancel-year', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-warning', 'data-method' => 'post', 'data-confirm' => 'ยกเลิกการใช้ตัวชี้วัดนี้ในปีดังกล่าว? ข้อมูลจะยังคงอยู่ในทะเบียน']) ?>
        <?php endif; ?>
    </td>
</tr>
