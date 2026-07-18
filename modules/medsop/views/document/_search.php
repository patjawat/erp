<?php
use app\modules\medsop\models\Document;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<section class="medsop-filter mb-4" aria-labelledby="medsop-filter-title">
    <h2 id="medsop-filter-title" class="visually-hidden">ค้นหาและกรองเอกสาร</h2>
    <div class="medsop-filter__body">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index'], 'options' => ['role' => 'search']]); ?>
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-7">
                <?= $form->field($searchModel, 'q', ['options' => ['class' => 'mb-0']])->textInput(['class' => 'form-control medsop-search-input', 'placeholder' => 'ค้นหาด้วยชื่อ เลขที่เอกสาร หรือวัตถุประสงค์', 'aria-label' => 'ค้นหาเอกสาร'])->label(false) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <?= $form->field($searchModel, 'document_type')->dropDownList(Document::typeOptions(), ['prompt' => 'ทุกประเภท', 'class' => 'form-select'])->label('ประเภท') ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <?= $form->field($searchModel, 'status')->dropDownList(Document::statusOptions(), ['prompt' => 'ทุกสถานะ', 'class' => 'form-select'])->label('สถานะ') ?>
            </div>
            <div class="col-12 col-lg-1 d-flex gap-2">
                <?= Html::submitButton('<i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">ค้นหาเอกสาร</span>', ['class' => 'btn btn-primary medsop-search-button', 'aria-label' => 'ค้นหาเอกสาร']) ?>
                <?= Html::a('<i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">ล้างตัวกรอง</span>', ['index'], ['class' => 'btn btn-outline-secondary medsop-search-button', 'aria-label' => 'ล้างตัวกรอง']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</section>
