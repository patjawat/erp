<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$isUpdate = !$model->isNewRecord;
$this->title = $isUpdate ? 'แก้ไขร่างทะเบียนเจ้าหนี้' : 'สร้างร่างทะเบียนเจ้าหนี้';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'กล่องรับงานบัญชี', 'url' => ['/finance/inbox']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title');
echo Html::encode($this->title);
$this->endBlock();
$this->beginBlock('sub-title');
echo Html::encode('เอกสารต้นทาง ' . ($inbox->source_document_no ?: $inbox->source_id));
$this->endBlock();
$this->beginBlock('page-action');
echo Html::a('<i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กลับรายการตรวจสอบ', ['/finance/inbox/view', 'id' => $inbox->id], ['class' => 'btn btn-outline-secondary']);
$this->endBlock();
?>

<div class="row g-3">
    <div class="col-xl-8">
        <section class="card border shadow-sm">
            <div class="card-header bg-body"><h5 class="mb-0">ข้อมูลใบแจ้งหนี้และการวางบิล</h5></div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(); ?>
                <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

                <?= $form->field($model, 'vendor_id')->dropDownList($vendors, [
                    'prompt' => 'เลือกผู้แทนจำหน่ายจากทะเบียนหลัก',
                ])->label('ผู้แทนจำหน่าย') ?>
                <div class="form-text mb-3">
                    รหัสจากระบบพัสดุ: <?= Html::encode($inbox->vendor_code_snapshot ?: 'ไม่ระบุ') ?>,
                    ชื่อเดิม: <?= Html::encode($inbox->vendor_name_snapshot ?: 'ไม่ระบุ') ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6"><?= $form->field($model, 'invoice_no')->textInput(['maxlength' => true])->label('เลขที่ใบแจ้งหนี้') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'invoice_date')->input('date')->label('วันที่ใบแจ้งหนี้') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'billing_date')->input('date')->label('วันที่รับวางบิล') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'credit_days')->input('number', ['min' => 0, 'max' => 3650])->label('จำนวนวันเครดิต') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'withholding_tax_amount')->input('number', ['min' => 0, 'step' => '0.01'])->label('ภาษีหัก ณ ที่จ่าย (ประมาณการ)') ?></div>
                </div>

                <?= $form->field($model, 'note')->textarea(['rows' => 3])->label('หมายเหตุ') ?>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?= Html::submitButton(
                        $isUpdate
                            ? '<i class="bi bi-save me-1" aria-hidden="true"></i>บันทึกการแก้ไข'
                            : '<i class="bi bi-file-earmark-plus me-1" aria-hidden="true"></i>สร้างร่างทะเบียนเจ้าหนี้',
                        ['class' => 'btn btn-primary']
                    ) ?>
                    <?= Html::a('ยกเลิก', ['/finance/inbox/view', 'id' => $inbox->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="card border shadow-sm">
            <div class="card-header bg-body"><h5 class="mb-0">ยอดจากเอกสารต้นทาง</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-7 text-body-secondary">ยอดหนี้</dt><dd class="col-5 text-end fw-semibold"><?= Yii::$app->formatter->asDecimal($model->gross_amount, 2) ?></dd>
                    <dt class="col-7 text-body-secondary">VAT ใน Snapshot</dt><dd class="col-5 text-end"><?= Yii::$app->formatter->asDecimal($model->vat_amount, 2) ?></dd>
                    <dt class="col-7 text-body-secondary">ฐานวันครบกำหนด</dt><dd class="col-5 text-end">วันรับวางบิล</dd>
                </dl>
            </div>
        </section>
        <div class="alert alert-secondary mt-3 mb-0">
            การสร้างร่างยังไม่ลงบัญชี ไม่อนุมัติจ่าย และไม่แก้ยอดในระบบพัสดุ
        </div>
    </div>
</div>
