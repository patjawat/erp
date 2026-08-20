<?php

use yii\helpers\Html;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */

// ตัวกรองเดียวของทั้งหน้า — การ์ดทุกใบอ่านปีงบจาก searchModel ตัวเดียวกัน
$years = $searchModel->groupYear();
$current = (string) ($searchModel->thai_year ?: AppHelper::YearBudget());
$isCurrentYear = (int) $current === (int) AppHelper::YearBudget();
?>
<div class="vd-toolbar">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label for="vd-year" class="form-label mb-0 small text-body-secondary">ปีงบประมาณ</label>
            <?= Html::beginForm(['dashboard'], 'get', ['data-pjax' => 1, 'class' => 'd-inline-flex']) ?>
            <?= Html::dropDownList(
                'VehicleSearch[thai_year]',
                $current,
                $years,
                [
                    'id' => 'vd-year',
                    'class' => 'form-select form-select-sm w-auto vd-num',
                    'onchange' => 'this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit();',
                ]
            ) ?>
            <?= Html::endForm() ?>
            <?php if (!$isCurrentYear): ?>
                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">ข้อมูลย้อนหลัง</span>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?= Html::a(
                '<i class="bi bi-list-check me-1"></i>ทะเบียนการจอง',
                ['/booking/vehicle/index'],
                ['class' => 'btn btn-sm btn-outline-secondary']
            ) ?>
            <?= Html::a(
                '<i class="bi bi-calendar-week me-1"></i>ตารางการใช้รถ',
                ['/booking/vehicle/schedule'],
                ['class' => 'btn btn-sm btn-outline-secondary']
            ) ?>
        </div>
    </div>
</div>
