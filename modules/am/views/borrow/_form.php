<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="asset-detail-form">

 <?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/am/borrow/validator'],
])
?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'asset_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>


    <div class="alert alert-info bg-info bg-opacity-10 border-info-subtle d-flex align-items-center mb-4">
        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
        <div>
            <small class="d-block text-muted">ครุภัณฑ์ที่ต้องการยืม:</small>
            <strong><?= $model->asset->asset_name ?></strong> (<?= $model->code ?>)
        </div>
    </div>

    <form>
        <div class="row g-3">
            <div class="col-md-12">
                <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'modal' => true,'label' => 'ผู้ขอยืม / หน่วยงานที่รับผิดชอบ'])?>

            </div>

            <div class="col-md-6">
                <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'วันที่ยืม...'])->label('วันที่ยืม'); ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'กำหนดคืน (โดยประมาณ)...'])->label('กำหนดคืน'); ?>
            </div>

            <div class="col-12">
                 <?= $form->field($model, 'data_json[remark]')->textArea(['rows' => 4,'placeholder' => 'ระบุเหตุผลการยืม เช่น ยืมใช้ชั่วคราวระหว่างเครื่องหลักส่งซ่อม...'])->label('วัตถุประสงค์ / หมายเหตุการยืม') ?>
            </div>
        </div>
    </form>

    <?= $model->Upload() ?>


    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
 thaiDatepicker('#assetdetail-date_start,#assetdetail-date_end')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>