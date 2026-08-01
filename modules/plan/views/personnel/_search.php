<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\depdrop\DepDrop;
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
?>

<div class="plan-item-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="row">
        <div class="col-lg-2">
            <?= $form->field($model, 'thai_year')->widget(Select2::class, [
                'data' => $planYears,
                'options' => ['placeholder' => 'ปีงบประมาณ (ทุกปี)'],
                'pluginOptions' => ['allowClear' => true],
            ])->label(false) ?>
        </div>
                       <div class="col-lg-7">
                        <?= $form->field($model, 'department_id')->widget(\kartik\tree\TreeViewInput::className(), [
                            'name' => 'department',
                            'id' => 'treeID',
                            'query' => app\modules\hr\models\Organization::find()->addOrderBy('root, lft'),
                            'value' => 1,
                            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                            'fontAwesome' => true,
                            'asDropdown' => true,
                            'multiple' => false,
                            'options' => ['disabled' => false],
                        ])->label(false); ?>
                    </div>
        <div class="col-lg-2">
            <?php

            echo $form->field($model, 'status')->widget(Select2::classname(), [
                // 'data' => ArrayHelper::map(categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title'),
                'data' => [
                    'draft' => 'ฉบับร่าง',
                    'submit' => 'ส่งคำขอ',
                    'approve' => 'อนุมัติ',
                    'renew' => 'ปรับแผน'
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
            <div class="col-1">
        <div class="d-flex flex-row align-items-center gap-2">
            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="false" aria-controls="collapseFilter">
                <i class="fa-solid fa-filter"></i>
            </button>
        </div>
    </div>
    </div>



    <div class="collapse mt-3" id="collapseFilter">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
                        <?php

                        echo $form->field($model, 'plan_type_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'plan_type'])->all(), 'code', 'title'),
                            'options' => [
                                'placeholder' => 'เลือกรายการค่าใช้จ่าย',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'pluginEvents' => [
                                "select2:select" => "function() { 
                                    if($(this).val() == 'PE1'){
                                        $('#planorder-wage_type_id').prop('disabled', false).trigger('change');
                                        } else {
                                            $('#planorder-wage_type_id').prop('disabled', true).trigger('change');
                                    }
                                }",
                            ],
                        ])->label(false);
                        ?>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <?php
                        echo $form->field($model, 'wage_type_id')->widget(Select2::classname(), [
                            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'plan_wage_type'])->all(), 'code', 'title'),
                            'options' => [
                                'placeholder' => 'เลือกค่าจ้าง',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'pluginEvents' => [
                                "select2:select" => "function() {}",
                            ],
                        ])->label(false); ?>

                    </div>
    </div>


</div>

<?php ActiveForm::end(); ?>

</div>