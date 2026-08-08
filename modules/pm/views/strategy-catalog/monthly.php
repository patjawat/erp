<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var \app\modules\pm\models\StrategyIndicatorYear $entry @var \app\modules\pm\models\StrategyIndicatorMonth[] $months */
/** @var \app\modules\pm\models\StrategyPlan $plan */

$this->title = 'ผลงานรายเดือน ' . $entry->indicator->code . ' ปี ' . $entry->fiscal_year;
$this->beginBlock('page-title'); ?>บันทึกผลงานรายเดือน<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'indicator']) ?><?php $this->endBlock();
$form = ActiveForm::begin();
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-3">
    <div>
        <h2 class="h5 mb-1"><?= Html::encode($entry->indicator->code) ?> · ปีงบประมาณ <?= (int) $entry->fiscal_year ?></h2>
        <p class="text-muted mb-0"><?= Html::encode($entry->displayName()) ?></p>
    </div>
    <?= Html::a('ดูรายละเอียดตัวชี้วัด', ['template', 'id' => $entry->id], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body p-4"><div class="row g-3">
    <div class="col-6 col-md-3">
        <div class="small text-muted">ค่าเป้าหมายทั้งปี</div>
        <div class="fs-5 fw-semibold"><?= Html::encode($entry->target_value ?? '—') ?> <span class="small text-muted"><?= Html::encode($entry->displayUnit() ?? '') ?></span></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="small text-muted">บันทึกแล้ว</div>
        <div class="fs-5 fw-semibold"><?= $entry->monthsFilled() ?>/12 เดือน</div>
    </div>
    <div class="col-6 col-md-3">
        <div class="small text-muted">ผลรวมรายเดือน</div>
        <div class="fs-5 fw-semibold"><?= ($t = $entry->monthlyTotal()) !== null ? Html::encode(rtrim(rtrim(number_format($t, 4, '.', ''), '0'), '.')) : '—' ?></div>
    </div>
    <div class="col-6 col-md-3"><?= $form->field($entry, 'actual_value')->textInput(['type' => 'number', 'step' => 'any'])->hint('สรุปเองได้ ไม่ผูกกับผลรวมรายเดือน') ?></div>
</div></div></div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-body-tertiary px-4 py-3">
        <div class="fw-semibold">ผลงานรายเดือน (เรียงตามปีงบประมาณ ต.ค. → ก.ย.)</div>
        <div class="small text-muted">ตัวตั้ง/ตัวหารกรอกไว้เพื่ออ้างอิงการคำนวณ เดือนที่เว้นว่างทั้งแถวจะไม่ถูกบันทึก</div>
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr>
            <th class="ps-4" style="width:8rem">เดือน</th><th style="width:10rem">ตัวตั้ง</th><th style="width:10rem">ตัวหาร</th>
            <th style="width:10rem">ผลงาน</th><th class="pe-4">หมายเหตุ</th>
        </tr></thead>
        <tbody>
        <?php foreach ($months as $i => $month): ?>
            <tr>
                <td class="ps-4 fw-semibold text-nowrap"><?= Html::encode($month->label((int) $entry->fiscal_year)) ?><?= Html::activeHiddenInput($month, "[$i]month") ?></td>
                <td><?= Html::activeTextInput($month, "[$i]numerator", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any']) ?></td>
                <td><?= Html::activeTextInput($month, "[$i]denominator", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any']) ?></td>
                <td><?= Html::activeTextInput($month, "[$i]value", ['class' => 'form-control form-control-sm', 'type' => 'number', 'step' => 'any']) ?></td>
                <td class="pe-4"><?= Html::activeTextInput($month, "[$i]note", ['class' => 'form-control form-control-sm']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <div class="card-footer bg-body d-flex justify-content-between p-3">
        <?= Html::a('กลับ', ['index', 'type' => 'indicator', 'planId' => $plan->id, 'year' => $entry->fiscal_year], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::submitButton('บันทึกผลงาน', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
