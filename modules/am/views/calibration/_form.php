<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>


<form id="form-calibration" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">รหัสเครื่องมือ (Asset ID)</label>
              <input type="text" class="form-control" name="asset_id" placeholder="เช่น EQ-67-001" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">หน่วยงานที่รับผิดชอบ</label>
              <input type="text" class="form-control" name="department" value="ศูนย์เครื่องมือแพทย์" readonly>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">วันที่ตามแผน (Plan Date)</label>
              <input type="date" class="form-control" name="plan_date" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">วันที่ดำเนินการ (Cal Date)</label>
              <input type="date" class="form-control" name="actual_date" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-bold">ผู้ให้บริการสอบเทียบ (Provider)</label>
              <select class="form-select" name="provider_type">
                <option value="external">หน่วยงานภายนอก (Outsource)</option>
                <option value="internal">ดำเนินการเอง (In-house)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">ผลการสอบเทียบ</label>
              <div class="mt-2">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="cal_result" id="pass" value="pass" checked>
                  <label class="form-check-label text-success" for="pass font-weight-bold">ผ่าน (Pass)</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="cal_result" id="fail" value="fail">
                  <label class="form-check-label text-danger" for="fail font-weight-bold">ไม่ผ่าน (Fail)</label>
                </div>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label fw-bold">รายละเอียด/หมายเหตุ</label>
              <textarea class="form-control" name="remark" rows="2" placeholder="ระบุค่าความคลาดเคลื่อนหรือรายละเอียดเพิ่มเติม"></textarea>
            </div>

            <div class="col-12">
              <div class="card bg-light">
                <div class="card-body">
                  <label class="form-label fw-bold">แนบใบสอบเทียบ (Calibration Certificate)</label>
                  <input class="form-control" type="file" name="cert_file" id="cert_file" accept=".pdf,.jpg,.png">
                  <div class="form-text">รองรับไฟล์ PDF หรือรูปภาพ ขนาดไม่เกิน 5MB</div>
                </div>
              </div>
            </div>
          </div>
        </div>
       
      </form>


<div class="asset-detail-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form'
    ]); ?>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'asset_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('หมายเลขทรัพย์สิน/ครุภัณฑ์') ?>
        </div>
         
        <div class="col-lg-6 col-md-6 col-sm-12">
            <?= $form->field($model, 'plan_date')->textInput(['placeholder' => 'ระบุบวันที่กำหนดแผน calibration'])->label('วันที่กำหนดแผน'); ?>
        </div>
         <div class="col-lg-6 col-md-6 col-sm-12">
             <?= $form->field($model, 'data_json[title]')->textInput()->label('หัวข้อ') ?>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <?= $form->field($model, 'actual_date')->textInput(['placeholder' => 'วันที่ดำเนินการ'])->label('วันที่ดำเนินการ'); ?>
        </div>
    </div>

    <?= $model->Upload() ?>


    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
 thaiDatepicker('#assetdetail-actual_date,#assetdetail-plan_date')
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>