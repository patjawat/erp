<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model yii\base\Model */
/* @var $form yii\bootstrap5\ActiveForm */
?>

<div class="fade-in">

    <div class="d-flex align-items-center gap-2 text-secondary small mb-3">
        <a href="<?= Url::to(['index']) ?>" class="text-decoration-none text-secondary hover-text-primary">ทรัพย์สิน</a>
        <span>/</span>
        <span class="text-dark fw-medium">
            <?= Yii::$app->controller->action->id == 'create' ? 'เพิ่มข้อมูล' : 'แก้ไขข้อมูล' ?>
        </span>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'asset-form',
        'options' => ['class' => 'animate-in fade-in slide-in-from-right-4 duration-300', 'enctype' => 'multipart/form-data'],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{error}",
            'labelOptions' => ['class' => 'form-label fw-medium text-secondary small mb-1'],
            'inputOptions' => ['class' => 'form-control shadow-sm', 'style' => 'font-size: 0.9rem;'],
        ],
    ]); ?>

    <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden">
        
        <div class="card-header px-4 py-3 d-flex justify-content-between align-items-center rounded-top-3" style="background-color: #1a508e !important;">
            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2 text-white" style="font-size: 1.1rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-75"><rect width="20" height="14" x="2" y="3" rx="2"></rect><line x1="8" x2="16" y1="21" y2="21"></line><line x1="12" x2="12" y1="17" y2="21"></line></svg> 
                <?= Yii::$app->controller->action->id == 'create' ? 'เพิ่มข้อมูลครุภัณฑ์' : 'บันทึกข้อมูลครุภัณฑ์' ?>
            </h5>
            <div class="badge bg-white bg-opacity-10 text-white px-3 py-2 fw-normal rounded">
                หมวดพัสดุ: ครุภัณฑ์
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                
                <div class="col-lg-3 col-xl-2">
                    <div class="sticky-top" style="top: 20px; z-index: 1;">
                        <div class="bg-light rounded-3 border border-2 border-dashed d-flex flex-column align-items-center justify-content-center cursor-pointer hover-bg-gray transition mb-2 position-relative" style="aspect-ratio: 1/1;">
                            
                            <?php if (!empty($model->photo)): ?>
                                <img src="<?= Yii::getAlias('@web/uploads/') . $model->photo ?>" class="w-100 h-100 object-fit-cover rounded-3">
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary opacity-50 mb-2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>
                                <p class="small text-secondary mb-0">อัปโหลดรูปภาพ</p>
                            <?php endif; ?>
                            
                            <?= $form->field($model, 'photo')->fileInput([
                                'class' => 'position-absolute top-0 start-0 w-100 h-100 opacity-0', 
                                'style' => 'cursor: pointer;'
                            ])->label(false) ?>
                        </div>
                        <div class="text-center small text-muted">รองรับ JPG, PNG ขนาดไม่เกิน 5MB</div>
                    </div>
                </div>

                <div class="col-lg-9 col-xl-10">
                    
                    <div class="mb-5">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-4 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><line x1="12" x2="12" y1="2" y2="22"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            ข้อมูลงบประมาณ
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <?= $form->field($model, 'budget_type')->dropDownList(
                                    ['งบลงทุน' => 'งบลงทุน', 'งบดำเนินงาน' => 'งบดำเนินงาน', 'เงินบำรุง' => 'เงินบำรุง'], ['class' => 'form-select shadow-sm']
                                )->label('ประเภทงบ') ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'life_year')->textInput(['placeholder' => '2568'])->label('ปีงบประมาณ') ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'price')->textInput(['type' => 'number', 'step' => '0.01'])->label('จำนวนเงินรวม') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'budget_type')->textInput()->label('หน่วยงานภายใน/แหล่งเงิน') ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-4 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"></circle></svg>
                            ข้อมูลทั่วไปของครุภัณฑ์
                        </h6>
                        <div class="row g-4">
                            <div class="col-12">
                                <?= $form->field($model, 'name')->textInput(['required' => true])->label('ชื่อครุภัณฑ์ <span class="text-danger">*</span>') ?>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <?= $form->field($model, 'category_id')->dropDownList(
                                    ['1' => 'ครุภัณฑ์คอมพิวเตอร์', '2' => 'ครุภัณฑ์การแพทย์'], ['class' => 'form-select shadow-sm']
                                )->label('ประเภทครุภัณฑ์') ?>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="mb-3">
                                    <label class="form-label fw-medium text-secondary small mb-1">รหัส FSN (เลขหมวด)</label>
                                    <div class="input-group shadow-sm">
                                        <input type="text" class="form-control" name="fsn_code" placeholder="ระบุรหัส FSN">
                                        <button class="btn btn-light border" type="button">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <?= $form->field($model, 'asset_code')->textInput()->label('เลขครุภัณฑ์เดิม') ?>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <?= $form->field($model, 'brand')->textInput()->label('ยี่ห้อ (Brand)') ?>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <?= $form->field($model, 'model')->textInput()->label('รุ่น / Model') ?>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="mb-3">
                                    <label class="form-label fw-medium text-secondary small mb-1">สี</label>
                                    <input type="text" class="form-control shadow-sm" name="color">
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <?= $form->field($model, 'serial_no')->textInput()->label('S/N (Serial Number)') ?>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div class="mb-3">
                                    <label class="form-label fw-medium text-secondary small mb-1">หน่วยนับ</label>
                                    <input type="text" class="form-control shadow-sm" name="unit">
                                </div>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'name')->textarea(['rows' => 4])->label('คุณลักษณะเฉพาะ / รายละเอียดเพิ่มเติม') ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-4 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><rect width="20" height="14" x="2" y="6" rx="2"></rect></svg>
                            ข้อมูลการได้มา
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <?= $form->field($model, 'budget_type')->dropDownList(
                                    ['ตกลงราคา (ซื้อ/จ้าง)' => 'ตกลงราคา (ซื้อ/จ้าง)', 'สอบราคา' => 'สอบราคา', 'e-bidding' => 'e-bidding'], ['class' => 'form-select shadow-sm']
                                )->label('วิธีได้มา') ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'supplier_id')->textInput()->label('ผู้ขาย / ผู้บริจาค') ?>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <?= $form->field($model, 'received_date')->textInput(['type' => 'date'])->label('วันที่ตรวจรับ') ?>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="mb-3">
                                    <label class="form-label fw-medium text-secondary small mb-1">วันที่รับเข้า</label>
                                    <input type="date" class="form-control shadow-sm" name="checkin_date">
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <div class="mb-3">
                                    <label class="form-label fw-medium text-secondary small mb-1">วันหมดประกัน</label>
                                    <input type="date" class="form-control shadow-sm" name="warranty_date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-4 d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            สถานที่และสถานะ
                        </h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <?= $form->field($model, 'location_id')->textInput()->label('สถานที่ใช้งาน / ห้อง') ?>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-medium text-secondary small mb-1">ผู้รับผิดชอบ</label>
                                    <input type="text" class="form-control shadow-sm" name="responsible_person">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'status')->dropDownList(
                                    ['Normal' => 'ใช้งานปกติ', 'Repair' => 'ชำรุด/รอซ่อม', 'Disposed' => 'จำหน่าย/เสื่อมสภาพ'], ['class' => 'form-select shadow-sm']
                                )->label('สถานะครุภัณฑ์') ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-top px-4 py-3 d-flex justify-content-end gap-2">
            <a href="<?= Url::to(['view', 'id' => $model->id ?? 1]) ?>" class="btn btn-white border shadow-sm text-secondary">
                ย้อนกลับ
            </a>
            <button type="submit" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm px-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"></path><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"></path><path d="M7 3v4a1 1 0 0 0 1 1h7"></path></svg>
                บันทึกข้อมูล
            </button>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>

<style>
    .hover-bg-gray:hover { background-color: #f8f9fa; }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>