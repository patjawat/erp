<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk[] $models */
/** @var string $q */

$models = $models ?? [];
$q = $q ?? '';
?>

<style>
.repair-picker {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --primary-line: rgba(13, 110, 253, 0.22);
    --radius-sm: 8px;
    --radius-xs: 6px;
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    color: var(--ink-1);
}
.repair-picker__toolbar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
}
.repair-picker__search {
    position: relative;
    flex: 1 1 auto;
}
.repair-picker__search-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ink-3);
    pointer-events: none;
}
.repair-picker__input {
    min-height: 42px;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    padding-left: 2.45rem;
    color: var(--ink-1);
}
.repair-picker__input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.repair-picker__count {
    display: inline-flex;
    align-items: center;
    min-height: 32px;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    background: var(--surface-3);
    color: var(--ink-2);
    font-size: 0.82rem;
    font-weight: 600;
    white-space: nowrap;
}
.repair-picker__list {
    display: grid;
    gap: 0.45rem;
    max-height: min(62vh, 560px);
    overflow: auto;
    padding: 0;
    margin: 0;
    list-style: none;
}
.repair-picker__row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 0.8rem;
    width: 100%;
    min-height: 58px;
    padding: 0.7rem 0.8rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: inherit;
    text-align: left;
    transition: background 120ms var(--ease), border-color 120ms var(--ease);
}
.repair-picker__row:hover {
    background: var(--surface-hover);
    border-color: var(--line-strong);
}
.repair-picker__row:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.repair-picker__number {
    display: block;
    color: var(--primary);
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.repair-picker__title {
    display: block;
    margin-top: 0.15rem;
    color: var(--ink-1);
    font-size: 0.9rem;
    font-weight: 600;
}
.repair-picker__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem 0.65rem;
    margin-top: 0.25rem;
    color: var(--ink-3);
    font-size: 0.78rem;
}
.repair-picker__action {
    align-self: center;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    color: var(--primary);
    font-size: 0.84rem;
    font-weight: 700;
    white-space: nowrap;
}
.repair-picker__empty {
    padding: 2.4rem 1rem;
    border: 1px dashed var(--line-strong);
    border-radius: var(--radius-sm);
    color: var(--ink-3);
    text-align: center;
}
.repair-picker__empty-title {
    margin-bottom: 0.25rem;
    color: var(--ink-1);
    font-weight: 700;
}
@media (max-width: 575.98px) {
    .repair-picker__toolbar { align-items: stretch; flex-direction: column; }
    .repair-picker__count { justify-content: center; }
    .repair-picker__row { grid-template-columns: 1fr; }
    .repair-picker__action { justify-self: start; }
}
</style>

<div class="repair-picker" data-repair-picker>
    <div class="repair-picker__toolbar">
        <div class="repair-picker__search">
            <i class="bi bi-search repair-picker__search-icon" aria-hidden="true"></i>
            <input type="search"
                   class="form-control repair-picker__input"
                   data-repair-filter
                   value="<?= Html::encode($q) ?>"
                   placeholder="ค้นหาเลขที่ส่งซ่อม, หัวข้อ, รหัสครุภัณฑ์"
                   autocomplete="off">
        </div>
        <span class="repair-picker__count"><span data-repair-count><?= count($models) ?></span>&nbsp;รายการ</span>
    </div>

    <?php if (empty($models)): ?>
        <div class="repair-picker__empty">
            <div class="repair-picker__empty-title">ยังไม่พบรายการแจ้งซ่อม</div>
            <div>ลองค้นหาด้วยเลขที่ส่งซ่อมหรือหัวข้ออื่น</div>
        </div>
    <?php else: ?>
        <ul class="repair-picker__list" role="list">
            <?php foreach ($models as $model): ?>
                <?php
                $repairNumber = trim((string) ($model->repair_number ?? ''));
                $repairNumber = $repairNumber !== '' ? $repairNumber : ('HDB' . (int) $model->id);
                $title = trim((string) ($model->title ?? ''));
                $title = $title !== '' ? $title : 'ไม่ระบุหัวข้อ';
                $assetNumber = trim((string) ($model->asset_number ?? ''));
                $repairGroup = trim((string) ($model->repair_group ?? ''));
                $status = trim((string) ($model->status ?? ''));
                $dateRaw = (string) ($model->request_repair_date ?? $model->created_at ?? '');
                $dateText = '';
                if ($dateRaw !== '') {
                    $time = strtotime($dateRaw);
                    $dateText = $time ? date('d/m/Y', $time) : $dateRaw;
                }
                $searchText = trim($repairNumber . ' ' . $title . ' ' . $assetNumber . ' ' . $repairGroup . ' ' . $status . ' ' . $dateText);
                $searchText = function_exists('mb_strtolower') ? mb_strtolower($searchText, 'UTF-8') : strtolower($searchText);
                ?>
                <li class="repair-picker__item" data-repair-search="<?= Html::encode($searchText) ?>">
                    <button type="button"
                            class="repair-picker__row js-select-repair"
                            data-helpdesk-id="<?= (int) $model->id ?>"
                            data-repair-number="<?= Html::encode($repairNumber) ?>">
                        <span class="min-w-0">
                            <span class="repair-picker__number"><?= Html::encode($repairNumber) ?></span>
                            <span class="repair-picker__title"><?= Html::encode($title) ?></span>
                            <span class="repair-picker__meta">
                                <?php if ($assetNumber !== ''): ?><span>ครุภัณฑ์ <?= Html::encode($assetNumber) ?></span><?php endif; ?>
                                <?php if ($repairGroup !== ''): ?><span>แผนกช่าง <?= Html::encode($repairGroup) ?></span><?php endif; ?>
                                <?php if ($status !== ''): ?><span>สถานะ <?= Html::encode($status) ?></span><?php endif; ?>
                                <?php if ($dateText !== ''): ?><span>วันที่ <?= Html::encode($dateText) ?></span><?php endif; ?>
                            </span>
                        </span>
                        <span class="repair-picker__action">
                            <i class="bi bi-check2-circle" aria-hidden="true"></i>
                            เลือก
                        </span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
