<?php
use app\modules\medsop\models\Document;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<section class="surface-card mb-3" aria-labelledby="medsop-filter-title">
    <div class="surface-card__head d-flex align-items-center justify-content-between gap-2">
        <h2 id="medsop-filter-title" class="surface-card__title mb-0">ค้นหาเอกสาร</h2>
        <?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>
    <div class="surface-card__body">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'options' => ['role' => 'search']]); ?>
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-6">
                <?= $form->field($searchModel, 'q')->textInput(['class' => 'form-control form-control-input', 'placeholder' => 'ชื่อ เลขที่เอกสาร หรือวัตถุประสงค์', 'aria-label' => 'ค้นหาเอกสาร'])->label('คำค้นหา') ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <?= $form->field($searchModel, 'document_type')->dropDownList(Document::typeOptions(), ['prompt' => 'ทุกประเภท', 'class' => 'form-select form-control-input'])->label('ประเภท') ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <?= $form->field($searchModel, 'status')->dropDownList(Document::statusOptions(), ['prompt' => 'ทุกสถานะ', 'class' => 'form-select form-control-input'])->label('สถานะ') ?>
            </div>
            <div class="col-12 col-lg-2">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหาเอกสาร', ['class' => 'btn btn-primary w-100']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</section>
