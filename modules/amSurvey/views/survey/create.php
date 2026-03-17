<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\modules\amSurvey\models\AssetSurvey $model */

$this->title = 'สร้างโครงการสำรวจ';
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'survey_name')->textInput(['maxlength' => true, 'class' => 'form-control'])->label('ชื่อโครงการสำรวจ') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'survey_year')->textInput(['type' => 'number', 'class' => 'form-control'])->label('ปีสำรวจ (พ.ศ.)') ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'status')->dropdownList([
                                'draft' => 'ร่าง',
                                'active' => 'กำลังสำรวจ',
                                'closed' => 'ปิดแล้ว',
                            ], ['class' => 'form-select'])->label('สถานะ') ?>
                        </div>
                        <div class="col-12">
                            <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
