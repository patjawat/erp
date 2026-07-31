<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use app\modules\pm\models\Projects;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectsSearch $model */

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
                <?= $form->field($model, 'department_id')->widget(\kartik\tree\TreeViewInput::class, [
                    'query' => Organization::find()->where(['tb_name' => 'diagram'])->addOrderBy('root, lft'),
                    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                    'fontAwesome' => true,
                    'asDropdown' => true,
                    'multiple' => false,
                    'options' => ['placeholder' => 'ทุกหน่วยงาน'],
                ])->label('หน่วยงาน') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2600, 'placeholder' => 'ทุกปี'])->label('ปีงบประมาณ') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Projects::statusList(), ['prompt' => 'ทุกสถานะ'])->label('สถานะ') ?>
            </div>
        </div>
        <div class="d-flex gap-2 mb-2">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา', ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::a('<i class="fa-solid fa-rotate-left me-1"></i> ล้าง', ['index'], ['class' => 'btn btn-light btn-sm']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
