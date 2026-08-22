<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use app\modules\pm\models\Projects;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectsSearch $model */

// ค้นหาข้ามปีได้ จึงรวมหน่วยงานของทุกปีในทะเบียน ไม่ผูกกับปีใดปีหนึ่ง
$ouYear = (int) ($model->thai_year ?: \app\modules\plan\components\PlanHelper::currentPlanYear());
$ouGroups = \app\modules\settings\models\OrgUnit::groupedForSelect($ouYear);
?>
<div class="card mb-3">
    <div class="card-header d-flex align-items-center gap-2">
        <i data-lucide="search" style="width:18px;height:18px"></i>
        <span class="fw-semibold">ค้นหาโครงการ</span>
    </div>
    <div class="card-body pb-2">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => ['data-pjax' => 1],
        ]); ?>
        <div class="row g-2">
            <div class="col-md-4">
                <?= $form->field($model, 'name')->textInput(['placeholder' => 'ชื่อโครงการ / เลขที่'])->label('ชื่อ/เลขที่โครงการ') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'org_unit_id')->widget(Select2::class, [
                    'data' => $ouGroups,
                    'options' => ['placeholder' => 'ทุกหน่วยงาน'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('หน่วยงาน') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2600, 'placeholder' => 'ทุกปี'])->label('ปีงบประมาณ') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Projects::statusList(), ['prompt' => 'ทุกสถานะ'])->label('สถานะ') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'work_type')->dropDownList(Projects::workTypeList(), ['prompt' => 'ทุกชนิดงาน'])->label('ชนิดงาน') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'strategy_type')->dropDownList(Projects::strategyTypeList(), ['prompt' => 'ทุกประเภท'])->label('ในแผน/นอกแผน') ?>
            </div>
        </div>
        <div class="d-flex gap-2 mb-2">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา', ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::a('<i class="fa-solid fa-rotate-left me-1"></i> ล้าง', ['index'], ['class' => 'btn btn-light btn-sm']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
