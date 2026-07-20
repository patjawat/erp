<?php
use app\modules\medsop\models\Document;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<section class="medsop-filter mb-4" aria-labelledby="medsop-filter-title">
    <h2 id="medsop-filter-title" class="visually-hidden">ค้นหาและกรองเอกสาร</h2>
    <div class="medsop-filter__body">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'options' => ['role' => 'search']]); ?>
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-6">
                <?= $form->field($searchModel, 'q', ['options' => ['class' => 'mb-0']])->textInput(['class' => 'form-control medsop-search-input', 'placeholder' => 'ค้นหาด้วยชื่อ เลขที่เอกสาร หรือวัตถุประสงค์', 'aria-label' => 'ค้นหาเอกสาร'])->label(false) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <?= $form->field($searchModel, 'document_type')->dropDownList(Document::typeOptions(), ['prompt' => 'ทุกประเภท', 'class' => 'form-select'])->label('ประเภท') ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <?= $form->field($searchModel, 'status')->dropDownList(Document::statusOptions(), ['prompt' => 'ทุกสถานะ', 'class' => 'form-select'])->label('สถานะ') ?>
            </div>
            <div class="col-12 col-lg-2 d-flex gap-2">
                <?= Html::submitButton('<i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">ค้นหาเอกสาร</span>', ['class' => 'btn btn-primary medsop-search-button', 'aria-label' => 'ค้นหาเอกสาร']) ?>
                <?= Html::a('<i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">ล้างตัวกรอง</span>', ['index'], ['class' => 'btn btn-outline-secondary medsop-search-button', 'aria-label' => 'ล้างตัวกรอง']) ?>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <?= $form->field($searchModel, 'organization_id')->dropDownList(ArrayHelper::map($filterOrganizations, 'id', static function ($organization) { return str_repeat('– ', max(0, (int) $organization->lvl - 1)) . $organization->name; }), ['prompt' => 'ทุกหน่วยงาน', 'class' => 'form-select'])->label('หน่วยงาน') ?>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <?= $form->field($searchModel, 'category')->dropDownList(array_combine($categoryOptions, $categoryOptions), ['prompt' => 'ทุกหมวดหมู่', 'class' => 'form-select'])->label('หมวดหมู่') ?>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <?= $form->field($searchModel, 'created_emp_id')->dropDownList(ArrayHelper::map($filterCreators, 'id', static function ($employee) { return $employee->fullname(); }), ['prompt' => 'ผู้สร้างทุกคน', 'class' => 'form-select'])->label('ผู้สร้าง') ?>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <?= $form->field($searchModel, 'review_state')->dropDownList(['DUE' => 'ถึงกำหนดหรือเลยกำหนด', 'UPCOMING' => 'ถึงกำหนดภายใน 90 วัน', 'NO_DATE' => 'ยังไม่กำหนดวันทบทวน'], ['prompt' => 'ทุกกำหนดทบทวน', 'class' => 'form-select'])->label('กำหนดทบทวน') ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</section>
