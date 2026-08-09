<?php

use yii\helpers\Html;
use app\components\RichText;
use app\modules\pm\models\StrategyIndicatorYear;

/**
 * แถวตัวชี้วัดหนึ่งรายการในทะเบียนตัวชี้วัด
 *
 * @var app\modules\pm\models\StrategyIndicator $indicator
 * @var StrategyIndicatorYear|null $entry ข้อมูลของปีที่เลือก — เป็น null เมื่อยังไม่เคยกรอกรายละเอียด
 * @var int $year
 * @var bool $canEdit
 * @var bool $isChild ตัวชี้วัดรองจะเยื้องเข้ามาให้เห็นลำดับชั้น
 */

$cancelled = $entry && $entry->isCancelled();
$goal = $indicator->goal;
?>
<tr class="<?= $cancelled ? 'opacity-75' : '' ?>">
    <td class="ps-4" style="<?= $isChild ? 'padding-left:3rem !important' : '' ?>">
        <div class="d-flex align-items-start gap-2">
            <span class="badge <?= $isChild ? 'bg-secondary-subtle text-secondary' : 'bg-info-subtle text-info-emphasis' ?> mt-1"><?= $isChild ? 'รอง' : 'หลัก' ?></span>
            <div>
                <div class="fw-semibold"><?= Html::encode($indicator->code) ?></div>
                <div><?= Html::encode($entry ? $entry->displayName() : $indicator->name) ?></div>
                <?php if (!$isChild && $goal): ?>
                    <div class="small text-muted"><?= Html::encode($goal->code . ' ' . $goal->name) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </td>
    <td class="text-nowrap">
        <?php if ($entry): ?>
            <?= Html::encode(($entry->operator ? StrategyIndicatorYear::operatorList()[$entry->operator] . ' ' : '') . ($entry->target_value ?? '-')) ?>
            <span class="small text-muted"><?= Html::encode($entry->displayUnit() ?? '') ?></span>
            <?php if ($entry->baseline_value !== null): ?><div class="small text-muted">ค่าฐาน <?= Html::encode($entry->baseline_value) ?></div><?php endif; ?>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>
    </td>
    <td>
        <?php if (!$entry): ?>
            <span class="badge bg-warning-subtle text-warning-emphasis">ยังไม่ได้กรอกรายละเอียด</span>
        <?php elseif ($cancelled): ?>
            <span class="badge bg-secondary-subtle text-secondary">ยกเลิกในปีนี้</span>
            <?php if ($entry->cancelled_reason): ?><div class="small text-muted mt-1"><?= Html::encode(RichText::plain($entry->cancelled_reason, 80)) ?></div><?php endif; ?>
        <?php else: ?>
            <span class="badge bg-success-subtle text-success">ใช้งาน</span>
            <div class="small text-muted mt-1">ผลรายเดือน <?= $entry->monthsFilled() ?>/12</div>
            <?php if ($entry->copied_from_id): ?><div class="small text-muted">คัดลอกจากปี <?= (int) $entry->copiedFrom?->fiscal_year ?></div><?php endif; ?>
        <?php endif; ?>
    </td>
    <td class="text-end pe-4">
        <?php if ($entry): ?>
            <?= Html::a('รายละเอียด', ['template', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?php if ($canEdit): ?>
                <?= Html::a('ผลรายเดือน', ['monthly', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                <?= Html::a('แก้ไข', ['update', 'type' => 'indicator', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                <?= $cancelled
                    ? Html::a('กลับมาใช้', ['restore-year', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-success', 'data-method' => 'post'])
                    : Html::a('ยกเลิกในปีนี้', ['cancel-year', 'id' => $entry->id], ['class' => 'btn btn-sm btn-outline-warning', 'data-method' => 'post', 'data-confirm' => 'ยกเลิกการใช้ตัวชี้วัดนี้ในปีดังกล่าว? ข้อมูลจะยังคงอยู่ในทะเบียน']) ?>
            <?php endif; ?>
        <?php elseif ($canEdit): ?>
            <?= Html::a('<i data-lucide="plus" class="me-1"></i> เพิ่มรายละเอียด', ['detail', 'indicatorId' => $indicator->id, 'year' => $year], ['class' => 'btn btn-sm btn-primary']) ?>
        <?php else: ?>
            <span class="text-muted small">—</span>
        <?php endif; ?>
    </td>
</tr>
