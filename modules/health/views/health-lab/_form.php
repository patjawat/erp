<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\LabSetting */
?>

<div class="lab-setting-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form',
        // 'type' => ActiveForm::TYPE_VERTICAL,
        // 'options' => ['data-pjax' => true] 
    ]); ?>

    <div class="row g-3">
        <div class="col-12 mb-2">
            <div class="d-flex align-items-center p-3 bg-light rounded-3 border-start border-primary border-4">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                    <i class="fas fa-vials fa-lg"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">ตั้งค่ารายการตรวจวิเคราะห์</h6>
                    <small class="text-muted small">กำหนดรหัส ชื่อ และราคามาตรฐานสำหรับระบบ LAB</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <?= $form->field($model, 'lab_code')->textInput([
                'maxlength' => true,
                'placeholder' => 'เช่น L001...',
                'class' => 'form-control form-control-lg border-2 text-uppercase fw-bold text-primary',
                'autofocus' => true
            ])->label('รหัส LAB <span class="text-danger small">*</span>') ?>
        </div>

        <div class="col-md-8">
            <?= $form->field($model, 'lab_name')->textInput([
                'maxlength' => true,
                'placeholder' => 'ระบุชื่อรายการตรวจ...',
                'class' => 'form-control form-control-lg border-2'
            ])->label('ชื่อรายการ <span class="text-danger small">*</span>') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'lab_type')->widget(Select2::classname(), [
                'data' => [
                    'Blood' => 'ตรวจเลือด (Blood)',
                    'Urine' => 'ตรวจปัสสาวะ (Urine)',
                    'X-Ray' => 'เอกซเรย์ (X-Ray)',
                    'Special' => 'ตรวจพิเศษ (Special)',
                ],
                'options' => ['placeholder' => 'เลือกหรือพิมพ์ประเภทใหม่...'],
                'pluginOptions' => [
                    'allowClear' => true, 
                    'tags' => true,
                    'dropdownParent' => '#main-modal' // ให้แสดงผลทับ Modal ได้ถูกต้อง
                ],
            ])->label('ประเภทรายการ') ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'lab_price', [
                'addon' => [
                    'prepend' => ['content' => '฿', 'class' => 'bg-primary text-white border-primary fw-bold'],
                ]
            ])->textInput([
                'type' => 'number',
                'step' => '0.01',
                'min' => '0',
                'placeholder' => '0.00',
                'class' => 'form-control form-control-lg fw-bold text-end pe-3'
            ])->label('ราคาพื้นฐานต่อหน่วย') ?>
        </div>

        <div class="col-12">
            <?= $form->field($model, 'data_json')->textarea([
                'rows' => 3,
                'placeholder' => '{"unit": "mg/dL", "min": 70, "max": 100}',
                'class' => 'form-control border-dashed bg-light font-monospace small',
                'value' => is_array($model->data_json) ? json_encode($model->data_json, JSON_UNESCAPED_UNICODE) : $model->data_json
            ])->label('ค่ามาตรฐาน / ข้อมูล JSON <small class="text-muted fw-normal">(Optional)</small>') ?>
        </div>
    </div>

    <?php if (!Yii::$app->request->isAjax): ?>
        <div class="text-end mt-4 pt-3 border-top">
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-link text-secondary me-2 text-decoration-none']) ?>
            <?= Html::submitButton('<i class="fas fa-save me-1"></i> บันทึกข้อมูล', ['class' => 'btn btn-primary px-5 rounded-pill shadow-sm']) ?>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>
</div>

<style>
    /* Custom CSS สำหรับความเนี้ยบใน Modal */
    .border-dashed { border-style: dashed !important; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    
    /* ปรับแต่งความสูงของ Select2 ให้เท่ากับ input-lg */
    .select2-container--krajee-bs5 .select2-selection--single {
        height: 48px !important;
        line-height: 48px !important;
        border: 2px solid #dee2e6 !important;
        background-color: #fff !important;
    }
    .select2-container--krajee-bs5 .select2-selection__rendered {
        line-height: 44px !important;
    }
    .select2-container--krajee-bs5 .select2-selection__arrow {
        height: 46px !important;
    }
    
    /* ปรับ Border เมื่อ Focus */
    .form-control-lg:focus, .select2-container--krajee-bs5.select2-container--focus .select2-selection {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1) !important;
    }
</style>

<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>