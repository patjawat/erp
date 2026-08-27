<?php

use yii\helpers\Html;
use app\models\Categorise;
use kartik\widgets\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanItemSearch $model */
/** @var yii\widgets\ActiveForm $form */

$planYears = ArrayHelper::map(
    \app\modules\plan\models\PlanOrder::find()->select('thai_year')->where(['not', ['thai_year' => null]])->distinct()->orderBy(['thai_year' => SORT_DESC])->asArray()->all(),
    'thai_year',
    'thai_year'
);
$curPlanYear = \app\modules\plan\components\PlanHelper::currentPlanYear();
if (!isset($planYears[$curPlanYear])) {
    $planYears = [$curPlanYear => $curPlanYear] + $planYears;
}

// หน่วยงานจากทะเบียนกลาง (org_unit) ของปีที่ค้นหา — จัดกลุ่ม+เยื้องเหมือนหน้าตั้งค่า
$ouYear = (int) ($model->thai_year ?: $curPlanYear);
$ouGroups = \app\modules\settings\models\OrgUnit::groupedForSelect($ouYear);
?>

<div class="plan-item-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row g-3 align-items-end">
        <div class="col-12 col-md-3 col-xl-2">
            <?= $form->field($model, 'thai_year')->widget(Select2::class, [
                'data' => $planYears,
                'options' => ['placeholder' => 'ปีงบประมาณ (ทุกปี)'],
                'pluginOptions' => ['allowClear' => true],
            ])->label(false) ?>
        </div>
                       <div class="col-12 col-md-9 col-xl-6">
                        <?= $form->field($model, 'plan_unit_id')->widget(Select2::class, [
                            'data' => $ouGroups,
                            'options' => ['placeholder' => 'ทุกหน่วยงาน (จากทะเบียน)'],
                            'pluginOptions' => ['allowClear' => true],
                        ])->label(false); ?>
                    </div>
        <div class="col-12 col-md-8 col-xl-3">
            <?php

            echo $form->field($model, 'status')->widget(Select2::classname(), [
                // 'data' => ArrayHelper::map(categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title'),
                'data' => [
                    'draft' => 'ฉบับร่าง',
                    'submit' => 'ส่งคำขอ',
                    'approve' => 'อนุมัติ',
                    'renew' => 'ปรับแผน',
                    'reject' => 'ไม่อนุมัติ'
                ],
                'options' => [
                    'placeholder' => 'สถานะทั้งหมด',
                    'id' => 'plan_budget_type_id'
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(false);
            ?>

        </div>
            <div class="col-12 col-md-4 col-xl-1">
        <div class="d-flex justify-content-md-end gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i><span class="d-xl-none">ค้นหา</span>', ['class' => 'btn btn-primary', 'aria-label' => 'ค้นหา']) ?>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter me-1"></i><span class="d-xl-none">ตัวกรองเพิ่ม</span>
            </button>
        </div>
    </div>
    </div>



    <div class="collapse mt-3" id="collapseFilter">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl">
                <?= $form->field($model, 'unit_type')->widget(Select2::class, [
                    'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'org_unit_type', 'active' => 1])->orderBy('sort')->all(), 'code', 'title'),
                    'options' => ['placeholder' => 'ทุกประเภทหน่วยงาน', 'id' => 'unit_type_expenses'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('ประเภทหน่วยงาน') ?>
            </div>
            <div class="col-12 col-md-6 col-xl">
                        <?= $form->field($model, 'plan_type_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'plan_type', 'code' => 'OPS'])->all(), 'code', 'title'),
                            'options' => ['placeholder' => 'ทุกประเภท'],
                            'pluginOptions' => ['allowClear' => true],
                        ])->label('ประเภท') ?>
                    </div>
            <div class="col-12 col-md-6 col-xl">
                        <?php
                        // กรองเฉพาะหมวดค่าใช้สอย (plan_category ใต้ OPS) — ไม่แสดงตัวเลือกของพัสดุ/บุคลากร
                        echo $form->field($model, 'plan_category_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'plan_category', 'category_id' => 'OPS'])->andWhere(['not', ['code' => 'OPS_03']])->orderBy('code')->all(), 'code', 'title'),
                            'options' => [
                                'placeholder' => 'เลือกรายการค่าใช้จ่าย',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('หมวดค่าใช้สอย');
                        ?>
                    </div>
                    <div class="col-12 col-md-6 col-xl">
                        <?php
                        echo $form->field($model, 'plan_item_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'plan_item', 'category_id' => ArrayHelper::getColumn(Categorise::find()->where(['name' => 'plan_category', 'category_id' => 'OPS'])->andWhere(['not', ['code' => 'OPS_03']])->all(), 'code')])->orderBy('sort')->all(), 'code', 'title'),
                            'options' => [
                                'placeholder' => 'ทุกรายการค่าใช้สอย',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('รายการค่าใช้สอย'); ?>

                    </div>
                    <div class="col-12 col-md-6 col-xl">
                        <?= $form->field($model, 'plan_budget_type_id')->widget(Select2::class, [
                            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'budget_type'])->orderBy('sort')->all(), 'code', 'title'),
                            'options' => ['placeholder' => 'ทุกแหล่งงบประมาณ'],
                            'pluginOptions' => ['allowClear' => true],
                        ])->label('แหล่งงบประมาณ') ?>
                    </div>
    </div>


</div>

<?php ActiveForm::end(); ?>

</div>
