<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\modules\pm\models\KpiIndicator;

/** @var app\modules\pm\models\KpiIndicator $model */
/** @var int[] $years @var array $rowModels (fiscal_year => KpiIndicatorYear) */
/** @var array $groups @var array $units */

$this->title = $model->isNewRecord ? 'เพิ่มตัวชี้วัด' : 'แก้ไขตัวชี้วัด';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'kpi']) ?><?php $this->endBlock();
?>

<div class="mb-3">
    <?= Html::a('<i data-lucide="arrow-left"></i> กลับสู่ทะเบียน', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
</div>

<?php $form = ActiveForm::begin(); ?>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <h3 class="h6 mb-3">ข้อมูลทั่วไป</h3>
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'group_id')->dropDownList($groups, ['prompt' => 'เลือกกลุ่ม']) ?>
        </div>
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'org_unit_id')->dropDownList($units, ['prompt' => 'เลือกหน่วยงาน (ถ้ามี)']) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'name')->textarea(['rows' => 2]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'definition')->textarea(['rows' => 2]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'formula')->textarea(['rows' => 2])->hint('เช่น (ผลงานที่ได้ / เป้าหมาย) × 100') ?>
        </div>
        <div class="col-12 col-md-4">
            <?= $form->field($model, 'unit')->textInput(['placeholder' => 'เช่น %, ครั้ง, ราย']) ?>
        </div>
        <div class="col-12 col-md-4">
            <?= $form->field($model, 'operator')->dropDownList(KpiIndicator::operatorList()) ?>
        </div>
        <div class="col-12 col-md-4">
            <?= $form->field($model, 'frequency')->dropDownList(KpiIndicator::frequencyList(), ['prompt' => '-']) ?>
        </div>
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'owner_name')->textInput() ?>
        </div>
        <div class="col-6 col-md-3">
            <?= $form->field($model, 'sort_order')->input('number') ?>
        </div>
        <div class="col-6 col-md-3 d-flex align-items-end">
            <?= $form->field($model, 'is_active')->checkbox() ?>
        </div>
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'evaluation_method')->textarea(['rows' => 2]) ?>
        </div>
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'data_source')->textarea(['rows' => 2]) ?>
        </div>
    </div>
</div></div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <h3 class="h6 mb-1">ค่าเป้าหมาย / ผลงานจริง รายปี</h3>
    <p class="small text-muted mb-3">กรอกเฉพาะปีที่มีข้อมูล — สถานะ PASS/GAP คำนวณอัตโนมัติจากทิศทางที่เลือก</p>
    <div class="table-responsive"><table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr>
            <th>ปีงบประมาณ (พ.ศ.)</th><th>ค่าเป้าหมาย</th><th>ผลงานจริง</th>
        </tr></thead>
        <tbody>
        <?php foreach ($years as $fy): $row = $rowModels[$fy]; ?>
            <tr>
                <td class="fw-semibold"><?= (int) $fy ?></td>
                <td><?= Html::input('number', "Years[$fy][target_value]", $row->target_value, ['class' => 'form-control form-control-sm', 'step' => 'any', 'style' => 'max-width:180px']) ?></td>
                <td><?= Html::input('number', "Years[$fy][actual_value]", $row->actual_value, ['class' => 'form-control form-control-sm', 'step' => 'any', 'style' => 'max-width:180px']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div></div>

<div class="d-flex gap-2 mb-4">
    <?= Html::submitButton('<i data-lucide="save" class="me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
