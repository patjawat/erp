<?php

use app\modules\housing\models\HousingRequest;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = $model->isNewRecord ? 'สร้างคำขอบ้านพัก' : 'แก้ไขคำขอบ้านพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'request']) ?><?php $this->endBlock();
?>
<style>
.request-form-page{--rq-border:var(--bs-border-color);--rq-soft:var(--bs-tertiary-bg);--rq-ink:var(--bs-emphasis-color);color:var(--rq-ink)}
.request-form-page .form-shell{background:var(--bs-body-bg);border:1px solid var(--rq-border);border-radius:.85rem;max-width:920px;margin:auto;overflow:hidden}
.request-form-page .form-intro{background:var(--rq-soft);border-bottom:1px solid var(--rq-border);padding:1rem 1.25rem}
.request-form-page .form-body{padding:1.25rem}
.request-form-page .form-label{font-weight:600}
.request-form-page .select2-container{width:100%!important}
.request-form-page .form-footer{border-top:1px solid var(--rq-border);padding:1rem 1.25rem;display:flex;justify-content:flex-end;gap:.5rem}
</style>
<div class="container-fluid py-3 request-form-page">
<div class="form-shell">
    <div class="form-intro">
        <h1 class="h5 mb-1"><?= Html::encode($this->title) ?></h1>
        <div class="small text-body-secondary">เลือกได้เฉพาะบุคลากรที่ยังปฏิบัติงาน ระบบจะตรวจคำขอซ้ำและสถานะการพักอาศัยก่อนบันทึก</div>
    </div>
    <?php $form = ActiveForm::begin(); ?>
    <div class="form-body">
        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
        <div class="row g-3">
            <div class="col-md-4"><?= $form->field($model, 'request_no')->textInput(['readonly' => true]) ?></div>
            <div class="col-md-8">
                <?= $form->field($model, 'emp_id')->widget(Select2::class, [
                    'data' => $employeeOptions,
                    'options' => ['placeholder' => 'ค้นหาชื่อบุคลากรที่ยังปฏิบัติงาน'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-md-6"><?= $form->field($model, 'request_type')->dropDownList(HousingRequest::typeOptions()) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'preferred_building_type')->dropDownList([
                'house' => 'บ้านพัก',
                'flat' => 'แฟลต',
                'any' => 'บ้านพักหรือแฟลต',
            ], ['prompt' => 'ยังไม่ระบุ']) ?></div>
            <div class="col-12"><?= $form->field($model, 'reason')->textarea(['rows' => 4, 'placeholder' => 'ระบุเหตุผลและความจำเป็นในการขอใช้ที่พัก']) ?></div>
            <div class="col-12"><?= $form->field($model, 'staff_note')->textarea(['rows' => 2, 'placeholder' => 'ข้อมูลสำหรับเจ้าหน้าที่ (ถ้ามี)']) ?></div>
        </div>
    </div>
    <div class="form-footer">
        <?= Html::a('ยกเลิก', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::submitButton('บันทึกร่างคำขอ', ['class' => 'btn btn-primary px-4']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
</div>
