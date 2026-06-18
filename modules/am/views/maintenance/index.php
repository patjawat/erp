<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการบำรุงรักษา';
$this->params['breadcrumbs'][] = $this->title;
$maintenanceData = static function ($item): array {
    if (is_array($item->data_json)) {
        return $item->data_json;
    }

    if (is_string($item->data_json)) {
        return json_decode($item->data_json, true) ?: [];
    }

    return [];
};
$formatThaiDate = static function ($date): string {
    if (empty($date)) {
        return '-';
    }

    return Yii::$app->thaiDate->toThaiDate($date, false, false);
};
$operatorName = static function ($item): string {
    $user = $item->createdBy;

    return $user?->employee?->fullname
        ?? $user?->username
        ?? '-';
};
$operatorInitial = static function (string $name): string {
    if ($name === '-' || trim($name) === '') {
        return '?';
    }

    return mb_substr(trim($name), 0, 1, 'UTF-8');
};
$iconClean = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brush-cleaning-icon lucide-brush-cleaning">
            <path d="m16 22-1-4"></path>
            <path d="M19 14a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2h-3a1 1 0 0 1-1-1V4a2 2 0 0 0-4 0v5a1 1 0 0 1-1 1H6a2 2 0 0 0-2 2v1a1 1 0 0 0 1 1"></path>
            <path d="M19 14H5l-1.973 6.767A1 1 0 0 0 4 22h16a1 1 0 0 0 .973-1.233z"></path>
            <path d="m8 22 1-4"></path>
        </svg>'
?>
<style>
.maintenance-list-card {
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    box-shadow: 0 12px 28px rgba(15, 23, 42, .06);
    overflow: hidden;
}

.maintenance-list-card .maintenance-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: .875rem 1rem;
}

.maintenance-table {
    margin-bottom: 0;
    --maintenance-ease: cubic-bezier(.22, 1, .36, 1);
}

.maintenance-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: .8125rem;
    font-weight: 700;
    white-space: nowrap;
    border-bottom: 1px solid #e2e8f0;
    padding: .875rem 1rem;
}

.maintenance-table tbody tr {
    animation: maintenance-row-in 220ms var(--maintenance-ease) both;
    animation-delay: calc(var(--i, 0) * 24ms);
    transition: background-color 180ms var(--maintenance-ease), box-shadow 180ms var(--maintenance-ease), transform 180ms var(--maintenance-ease);
}

.maintenance-table tbody tr:hover {
    background-color: #f8fbff;
    box-shadow: inset 0 0 0 1px rgba(13, 110, 253, .18);
    transform: translateY(-1px);
}

.maintenance-table tbody td {
    color: #1e293b;
    padding: .95rem 1rem;
    vertical-align: middle;
    border-color: #edf2f7;
}

.maintenance-row-number {
    align-items: center;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    color: #1d4ed8;
    display: inline-flex;
    font-size: .8125rem;
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    height: 2rem;
    justify-content: center;
    min-width: 2rem;
    padding: 0 .45rem;
}

.maintenance-title {
    color: #0f172a;
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.35;
    max-width: 56ch;
}

.maintenance-note {
    align-items: flex-start;
    color: #475569;
    display: flex;
    font-size: .875rem;
    gap: .45rem;
    line-height: 1.45;
    margin-top: .35rem;
    max-width: 70ch;
}

.maintenance-note i {
    color: #64748b;
    line-height: 1.45;
    margin-top: .05rem;
}

.maintenance-date-cell {
    min-width: 9.5rem;
}

.maintenance-date-pill {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    color: #334155;
    display: inline-flex;
    font-size: .8125rem;
    font-weight: 600;
    gap: .4rem;
    line-height: 1.2;
    padding: .4rem .65rem;
    white-space: nowrap;
}

.maintenance-date-cell .maintenance-date-pill {
    width: 100%;
}

.maintenance-date-pill.is-done {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #166534;
}

.maintenance-operator {
    align-items: center;
    display: flex;
    gap: .625rem;
    min-width: 13rem;
}

.maintenance-operator-avatar {
    align-items: center;
    background: #dbeafe;
    border: 1px solid #bfdbfe;
    border-radius: 50%;
    color: #1d4ed8;
    display: inline-flex;
    flex: 0 0 2.25rem;
    font-weight: 800;
    height: 2.25rem;
    justify-content: center;
    width: 2.25rem;
}

.maintenance-operator-name {
    color: #0f172a;
    font-size: .9rem;
    font-weight: 700;
    line-height: 1.35;
}

.maintenance-operator-label {
    color: #64748b;
    font-size: .75rem;
    line-height: 1.2;
}

.maintenance-action-group {
    gap: .25rem;
}

.maintenance-action-group .btn {
    border-radius: .75rem;
    min-height: 2.5rem;
    min-width: 2.5rem;
    transition: background-color 160ms var(--maintenance-ease), color 160ms var(--maintenance-ease), transform 160ms var(--maintenance-ease);
}

.maintenance-action-group .btn:hover,
.maintenance-action-group .btn:focus-visible {
    color: #0d6efd;
    transform: translateY(-1px);
}

.maintenance-empty {
    color: #475569;
    padding: 2.5rem 1rem;
    text-align: center;
}

@keyframes maintenance-row-in {
    from {
        opacity: .001;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 767.98px) {
    .maintenance-list-card .maintenance-toolbar {
        align-items: stretch !important;
    }

    .maintenance-table {
        min-width: 920px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .maintenance-table tbody tr,
    .maintenance-action-group .btn {
        animation: none !important;
        transition-duration: .01ms !important;
    }
}
</style>
<div class="asset-detail-index">
    <div class="maintenance-list-card bg-body">
        <div class="maintenance-toolbar d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div>
                <h6 class="mb-1 fw-bold text-body"><?= Html::encode($this->title) ?></h6>
                <div class="small text-muted">ตรวจรายการตามแผน วันที่ดำเนินการ หมายเหตุ และผู้บันทึกงาน</div>
            </div>

            <?= Html::a('<i data-lucide="circle-plus"></i> สร้างใหม่', ['create', 'code' => $searchModel->code, 'title' => $iconClean . ' การบำรุงรักษา'], ['class' => 'btn btn-primary open-modal d-inline-flex align-items-center justify-content-center gap-2', 'data' => ['size' => 'modal-lg']]) ?>
        </div>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>
    <div class="table-responsive">
        <table class="table maintenance-table">
            <thead>
                <tr>
                    <th class="text-center" style="width:72px">ลำดับ</th>
                    <th>รายการและหมายเหตุ</th>
                    <th style="width:160px">วันที่ตามแผน</th>
                    <th style="width:160px">วันที่ดำเนินการ</th>
                    <th>ผู้ดำเนินการ</th>
                    <th class="text-center" style="width:130px">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <?php
                    $data = $maintenanceData($item);
                    $title = trim((string) ($data['title'] ?? ''));
                    $remark = trim((string) ($data['remark'] ?? ''));
                    $operator = $operatorName($item);
                    ?>
                    <tr style="--i: <?= (int) min($key, 10) ?>">
                        <td class="text-center">
                            <span class="maintenance-row-number"><?= Html::encode(($dataProvider->pagination->offset + 1) + $key) ?></span>
                        </td>
                        <td>
                            <?= Html::encode($remark !== '' ? StringHelper::truncate($remark, 150) : 'ไม่ได้ระบุ') ?>
                           
                        </td>
                        <td class="maintenance-date-cell">
                            <span class="maintenance-date-pill">
                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                <?= Html::encode($formatThaiDate($item->date_start)) ?>
                            </span>
                        </td>
                        <td class="maintenance-date-cell">
                            <span class="maintenance-date-pill is-done">
                                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                                <?= Html::encode($formatThaiDate($item->date_end)) ?>
                            </span>
                        </td>
                        <td>
                            <div class="maintenance-operator">
                                <span class="maintenance-operator-avatar" aria-hidden="true"><?= Html::encode($operatorInitial($operator)) ?></span>
                                <span>
                                    <span class="maintenance-operator-name d-block"><?= Html::encode($operator) ?></span>
                                    <span class="maintenance-operator-label">ผู้บันทึก/ดำเนินการ</span>
                                </span>
                            </div>
                        </td>
                        <td class="text-center py-2">
                            <div class="maintenance-action-group d-flex justify-content-center">
                                <a href="<?= Url::to(['/am/maintenance/view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                    <i class="fa-regular fa-eye"></i></a>
                                <a href="<?= Url::to(['/am/maintenance/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="แก้ไข">
                                 <i class="fa-regular fa-pen-to-square"></i></a>

                                <a href="<?= Url::to(['/am/maintenance/delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary delete-item" title="ลบ">
                                   <i class="fa-regular fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($dataProvider->getCount() === 0): ?>
                    <tr>
                        <td colspan="6">
                            <div class="maintenance-empty">
                                <div class="fw-semibold text-body mb-1">ยังไม่มีรายการบำรุงรักษา</div>
                                <div class="small">กดสร้างใหม่เพื่อบันทึกแผนหรือผลการบำรุงรักษาของครุภัณฑ์นี้</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php Pjax::end(); ?>
    </div>

</div>
