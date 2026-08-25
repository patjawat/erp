<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$hospitalOptions = ArrayHelper::map($context['hospitals'], 'id', 'name');
$yearOptions = ArrayHelper::map($context['years'], 'id', static fn ($model) => (string) $model->fiscal_year);
$periodOptions = ArrayHelper::map($context['periods'], 'id', 'name');
?>
<form method="get" class="card bg-body border shadow-sm mb-3" aria-label="เลือกขอบเขตข้อมูล IAC&Risk">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label fw-semibold" for="iac-hospital">โรงพยาบาล</label>
                <?= Html::dropDownList('hospital_id', $context['hospitalId'], $hospitalOptions, ['id' => 'iac-hospital', 'class' => 'form-select', 'disabled' => count($hospitalOptions) <= 1]) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label fw-semibold" for="iac-year">ปีงบประมาณ</label>
                <?= Html::dropDownList('fiscal_year_id', $context['fiscalYearId'], $yearOptions, ['id' => 'iac-year', 'class' => 'form-select', 'prompt' => 'ยังไม่ได้เปิดปี']) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-2">
                <label class="form-label fw-semibold" for="iac-period">รอบรายงาน</label>
                <?= Html::dropDownList('period_id', $context['periodId'], $periodOptions, ['id' => 'iac-period', 'class' => 'form-select', 'prompt' => 'เลือกรอบ']) ?>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label class="form-label fw-semibold" for="iac-unit">หน่วยงาน/ทีมประสาน</label>
                <?= Html::dropDownList('org_unit_id', $context['orgUnitId'], $context['units'], ['id' => 'iac-unit', 'class' => 'form-select', 'prompt' => 'ไม่พบหน่วยงาน']) ?>
            </div>
            <div class="col-12 col-xl-2 d-grid">
                <?= Html::submitButton('<i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i> แสดงข้อมูล', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
</form>
