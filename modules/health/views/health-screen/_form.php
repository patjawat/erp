<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\HealthCheckForm */

// เตรียมข้อมูลเพื่อเลี่ยง Indirect modification error
$tmpData = $model->data_json ?? [];

// --- ตัวเลือกสำหรับฟิลด์ต่างๆ ---
$smokingStatusOptions = ['smoke' => 'สูบ', 'none' => 'ไม่สูบ', 'quit' => 'เคยสูบแต่เลิกแล้ว'];
$alcoholOptions = ['drink' => 'ดื่ม', 'none' => 'ไม่ดื่ม', 'quit' => 'เคยดื่มแต่เลิกแล้ว'];
$exerciseOptions = [
    'everyday' => 'ออกกำลังทุกวัน ครั้งละ 30 นาที',
    '3_times_week' => 'ออกกำลังกายสัปดาห์ละ 3 ครั้ง ครั้งละ 30 นาที',
    'less_than_3' => 'ออกน้อยกว่าสัปดาห์ละ 3 ครั้ง',
    'none' => 'ไม่ออกกำลังกาย'
];
$miniSections = [
    ['title' => '1.4. รสอาหาร', 'key' => 'food_taste', 'opt' => ['sweet' => 'หวาน', 'salty' => 'เค็ม', 'fatty' => 'มัน', 'sour' => 'เปรี้ยว', 'none' => 'ไม่ชอบทุกข้อ'], 'color' => 'warning'],
    ['title' => '1.5. ขับขี่ปลอดภัย', 'key' => 'driving_safety', 'opt' => ['none' => 'ไม่ขับขี่ไม่โดยสาร', 'always' => 'ทุกครั้ง', 'sometimes' => 'บางครั้ง', 'rarely' => 'นานๆ ครั้ง'], 'color' => 'primary'],
    ['title' => '1.6. เพศสัมพันธ์', 'key' => 'condom_usage', 'opt' => ['always' => 'ใช้ทุกครั้ง', 'requested' => 'ใช้เมื่อถูกร้องขอ', 'none' => 'ไม่ใช้', 'no_answer' => 'ไม่ตอบ'], 'color' => 'danger']
];
$familyDiseaseList = ['diabetes' => 'เบาหวาน', 'hypertension' => 'ความดันสูง', 'gout' => 'เก๊าท์', 'kidney' => 'ไตวาย', 'heart' => 'หัวใจ', 'stroke' => 'อัมพาต', 'emphysema' => 'ถุงลมโป่งพอง', 'unknown' => 'ไม่ทราบ'];
$diseasesYear = [
    'h_diabetes' => 'เบาหวาน',
    'h_hypertension' => 'ความดันสูง',
    'h_liver' => 'โรคตับ',
    'h_stroke' => 'อัมพาต',
    'h_heart' => 'โรคหัวใจ',
    'h_dyslipidemia' => 'ไขมันเลือดผิดปกติ',
    'h_gastric' => 'แผลในกระเพาะ',
    'h_birth' => 'คลอดบุตร > 4kg',
    'h_thirst' => 'ดื่มน้ำบ่อย',
    'h_nocturia' => 'ปัสสาวะบ่อยกลางคืน',
    'h_fatigue' => 'อ่อนเพลีย',
    'h_skin_itch' => 'คันตามผิวหนัง',
    'h_vision' => 'ตาพร่ามัว',
    'h_numbness' => 'ชาปลายมือเท้า',
    'h_constipation' => 'ท้องผูกเรื้อรัง',
    'h_urinary' => 'ฉี่ขัด/ปนเลือด'
];
?>

<?php $form = ActiveForm::begin([
    'id' => 'form',
    'type' => ActiveForm::TYPE_VERTICAL,
    'enableClientValidation' => true,
    'enableAjaxValidation' => true, // ** ต้องเพิ่มบรรทัดนี้ **
    'validationUrl' => ['/me/health/validator'],
]); ?>

<div class="health-form-container p-3">
    <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'bmi')->hiddenInput()->label(false) ?>

    <div class="d-flex align-items-center gap-3 mb-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-id-card me-2"></i>หมวดที่ 1: ข้อมูลทั่วไป</h5>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><?= $form->field($model, 'thai_year')->textInput(['placeholder' => 'ระบุปีที่ตรวจ']) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'date_checkup')->textInput(['readonly' => true, 'class' => 'form-control bg-light']) ?></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-top border-4 border-danger h-100">
                <div class="card-body">
                    <h6 class="fw-bold">1.1. บุหรี่</h6>
                    <?php // $form->field($model, 'data_json[smoking_status]')->radioList($smokingStatusOptions, ['inline' => true])->label(false) 
                    ?>
                    <?= $form->field($model, 'data_json[smoking_status]', [
                        'enableAjaxValidation' => true,
                        // กำหนด template ให้มี {error} และหุ้มด้วย div ที่มีคลาสสำหรับตรวจสอบ
                        'template' => "{input}\n{error}",
                        'options' => ['class' => 'form-group mb-0'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'], // d-block เพื่อให้แสดงแน่นอน
                    ])->radioList($smokingStatusOptions, [
                        'inline' => true,
                    ])->label(false) ?>
                    <div class="bg-light p-2 rounded d-flex gap-2 align-items-center">
                        <span>จำนวน</span> <?= Html::activeTextInput($model, 'data_json[smoke_qty]', ['class' => 'form-control form-control-sm w-25']) ?> <span>มวน/วัน</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-top border-4 border-info h-100">
                <div class="card-body">
                    <h6 class="fw-bold">1.2. แอลกอฮอล์</h6>
                    <?= $form->field($model, 'data_json[alcohol_status]', [
                        'enableAjaxValidation' => true,
                        // กำหนด template ให้มี {error} และหุ้มด้วย div ที่มีคลาสสำหรับตรวจสอบ
                        'template' => "{input}\n{error}",
                        'options' => ['class' => 'form-group mb-0'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'], // d-block เพื่อให้แสดงแน่นอน
                    ])->radioList($alcoholOptions, [
                        'inline' => true,
                    ])->label(false) ?>
                    <div class="bg-light p-2 rounded d-flex gap-2 align-items-center">
                        <span>ความถี่</span> <?= Html::activeTextInput($model, 'data_json[alcohol_qty]', ['class' => 'form-control form-control-sm w-25']) ?> <span>ครั้ง/สัปดาห์</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-top border-4 border-success h-100">
                <div class="card-body">
                    <h6 class="fw-bold">1.3. ออกกำลังกาย</h6>
                    <?= $form->field($model, 'data_json[exercise_status]', [
                        'enableAjaxValidation' => true,
                        'template' => "{input}\n{error}",
                        'options' => ['class' => 'form-group mb-0'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'],
                    ])->dropDownList($exerciseOptions, ['prompt' => '-- เลือก --', 'class' => 'form-select form-select-sm mt-3'])->label(false) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach ($miniSections as $sec): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-top border-4 border-<?= $sec['color'] ?> h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><?= $sec['title'] ?></h6>
                        <?= $form->field($model, "data_json[{$sec['key']}]", [
                            'enableAjaxValidation' => true,
                            // ใส่ template เพื่อให้มีที่ว่างสำหรับแสดงข้อความ Error
                            'template' => "{input}\n{error}",
                            'options' => ['class' => 'form-group mb-0'],
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ])->dropDownList($sec['opt'], [
                            'prompt' => '-- เลือก --',
                            'class' => 'form-select form-select-sm'
                        ])->label(false) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-weight me-2"></i>หมวดที่ 2: การวัดทางกายภาพ</h5>
    <div class="row g-3 mb-4">
       <div class="col-md-6">
    <?= $form->field($model, 'weight', [
        'enableAjaxValidation' => true,
        'template' => "{label}\n{input}\n{error}", // ต้องมี {error} และ {label} (หรือเอา label ออกถ้าไม่ใช้)
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->textInput([
        'id' => 'w-in', 
        'type' => 'number', 
        'step' => '0.1', 
        'placeholder' => '0.0'
    ])->label('น้ำหนัก (กก.)') ?>
</div>

<div class="col-md-6">
    <?= $form->field($model, 'height', [
        'enableAjaxValidation' => true,
        'template' => "{label}\n{input}\n{error}",
        'errorOptions' => ['class' => 'invalid-feedback d-block'],
    ])->textInput([
        'id' => 'h-in', 
        'type' => 'number', 
        'step' => '0.1', 
        'placeholder' => '0.0'
    ])->label('ส่วนสูง (ซม.)') ?>
</div>
        <div class="col-12">
            <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-light">
                <div><small class="text-muted">ดัชนีมวลกาย (BMI)</small>
                    <h3 class="mb-0" id="bmi-val">0.0</h3>
                </div>
                <div class="text-end"><small class="text-muted">ผลประเมิน</small>
                    <h4 class="mb-0 text-primary" id="bmi-status">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm border-top border-4 border-primary mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold text-primary">ส่วนที่ 3: ข้อมูลครอบครัว</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'data_json[family_history]', [
    'enableAjaxValidation' => true,
    'template' => "{input}\n{error}", // เพิ่มที่วาง Error ไว้ใต้กลุ่ม Checkbox
    'options' => ['class' => 'form-group mb-0'],
    'errorOptions' => ['class' => 'invalid-feedback d-block px-3'], // เพิ่ม px-3 เพื่อให้ตัวหนังสือแดงเยื้องตรงกับ checkbox
])->checkboxList($familyDiseaseList, [
    'item' => function ($index, $label, $name, $checked, $value) {
        $checkedAttr = $checked ? "checked" : "";
        return "
        <div class='col-6 col-md-3 mb-2'>
            <div class='form-check'>
                <input type='checkbox' class='form-check-input' name='{$name}' value='{$value}' id='f_{$value}' {$checkedAttr}>
                <label class='form-check-label fw-normal' for='f_{$value}'>{$label}</label>
            </div>
        </div>";
    },
    'class' => 'row g-0 px-3' // ใช้ g-0 เพื่อให้ระยะห่างแนบสนิทสวยงาม
])->label(false) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm border-top border-4 border-danger mb-4">
        <div class="card-header bg-white d-flex justify-content-between">
            <h5 class="mb-0 fw-bold text-danger">ส่วนที่ 4: ประวัติเจ็บป่วยปีก่อน</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="btn btn-xs rounded-circle p-0 ms-2 btn-outline-success" style="width:15px; height:15px;"></label> ไม่มี
                <label class="btn btn-xs rounded-circle p-0 ms-2 btn-outline-danger" style="width:15px; height:15px;"></label> มี
                <label class="btn btn-xs rounded-circle p-0 ms-2 btn-outline-secondary" style="width:15px; height:15px;"></label> ไม่เคยตรวจ
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <?php foreach ($diseasesYear as $attr => $label):
                    if (!isset($tmpData[$attr])) {
                        $tmpData[$attr] = 0;
                    }
                ?>
                    <div class="col-md-6 border-bottom border-end-md py-2 px-3 d-flex justify-content-between align-items-center bg-hover">
                        <span class="fw-medium text-dark small"><?= $label ?></span>
                        <?= $form->field($model, "data_json[$attr]", ['options' => ['class' => 'mb-0']])->radioList([0 => '', 1 => '', 2 => ''], [
                            'item' => function ($index, $label, $name, $checked, $value) use ($tmpData, $attr) {
                                $isChecked = ($tmpData[$attr] == $value);
                                $colors = [0 => 'btn-outline-success', 1 => 'btn-outline-danger', 2 => 'btn-outline-secondary'];
                                return Html::radio($name, $isChecked, ['value' => $value, 'class' => 'btn-check', 'id' => "rd_{$name}_{$value}"])
                                    . Html::label('', "rd_{$name}_{$value}", ['class' => "btn btn-xs rounded-circle p-0 ms-2 " . $colors[$value], 'style' => 'width:18px; height:18px;']);
                            }
                        ])->label(false) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="text-center pb-5">
        <?= Html::submitButton('<i class="fas fa-save me-1"></i> บันทึกข้อมูลสุขภาพ', ['class' => 'btn btn-primary px-5 rounded-pill shadow']) ?>
    </div>
</div>

<?php
$model->data_json = $tmpData; // อัปเดต Model ก่อนปิด
ActiveForm::end();
?>
<style>
    /* UX/UI Improvements */
    .bg-hover:hover {
        background-color: #f8f9fa;
    }

    .btn-xs {
        padding: 1px;
        font-size: 0.7rem;
    }

    .border-end-md {
        border-right: 1px solid #eee;
    }

    @media (max-width: 768px) {
        .border-end-md {
            border-right: none;
        }

        .card-header h5 {
            font-size: 1rem;
        }
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    /* ปรับแต่ง Dropdown ให้ดูสะอาด */
    .form-select-sm {
        border: 1px solid #eee;
        background-color: #fbfbfb;
    }
</style>

<style>
    .table thead th {
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        color: #856404;
        border-bottom: 2px solid #ffeeba;
    }

    .table tbody td {
        border-bottom: 1px solid #f2f2f2;
    }

    .checkup-item {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(255, 193, 7, 0.02);
    }
</style>

<?php
// เพิ่ม JavaScript เล็กน้อยเพื่อคำนวณราคาสด (Optional)
$this->registerJs(
    <<<JS
 thaiDatepicker('#healthscreen-date_checkup')

    $('.checkup-item').on('change', function() {
        let total = 0;
        $('.checkup-item:checked').each(function() {
            total += parseFloat($(this).data('price'));
        });
        $('#total-price-display').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
    });

$('#w-in, #h-in').on('input', function() {
    let w = parseFloat($('#w-in').val());
    let h = parseFloat($('#h-in').val());

    if (w > 0 && h > 0) {
        let hMeter = h / 100;
        let bmi = (w / (hMeter * hMeter)).toFixed(1);
        
        // จำลองเกณฑ์จาก AppHelper
        let label = "";
        let color = "";

        if (bmi < 18.5) {
            label = "น้ำหนักน้อย / ผอม";
            color = "#0dcaf0"; // info
        } else if (bmi < 23) {
            label = "ปกติ (สุขภาพดี)";
            color = "#198754"; // success
        } else if (bmi < 25) {
            label = "ท้วม / เริ่มอ้วน";
            color = "#ffc107"; // warning
        } else if (bmi < 30) {
            label = "อ้วนระดับ 1";
            color = "#dc3545"; // danger
        } else {
            label = "อ้วนระดับ 2 (อ้วนมาก)";
            color = "#212529"; // dark
        }

        $('#bmi-val').text(bmi);
        $('#healthscreen-bmi').val(bmi);
        $('#bmi-status').text(label).css('color', color);
    }
});

        function syncSummary() {
        $('#out-bmi').text($('#bmi-val').text());
        $('#out-waist').text($('#waist-in').val() || '-');
        $('#out-bp').text($('#bp-in').val() || '-');
        $('#out-fbs').text($('#fbs-in').val() || '-');
        $('#out-chol').text($('#chol-in').val() || '-');
        $('#out-anemia').text($('#anemia-in option:selected').text());
        $('#res-diag').text($('#diag-in').val() || 'ปกติ/ไม่พบ');
        $('#res-risk').text($('#risk-in').val() || 'ไม่พบปัจจัยเสี่ยง');
        $('#res-plan').text($('#plan-in').val() || '-');
        $('#res-advice').text($('#advice-in').val() || '-');
        $('#res-summary').text($('#summary-in').val() || '-');
    }
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS
);
?>