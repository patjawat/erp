<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
?>
<?php $form = ActiveForm::begin([
    'id' => 'form',
    'options' => ['class' => 'needs-validation']
]); ?>

<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// ตัวเลือกพื้นฐานสำหรับ Radio (อ้างอิงจาก Screenshot)
$radioOptions = [0 => 'ไม่มี', 1 => 'มี', 2 => 'ไม่เคยตรวจ'];

$smokingStatusOptions = [
    'smoke' => 'สูบ',
    'none' => 'ไม่สูบ',
    'quit' => 'เคยสูบแต่เลิกแล้ว'
];

$alcoholOptions = [
    'drink' => 'ดื่ม',
    'none' => 'ไม่ดื่ม',
    'quit' => 'เคยดื่มแต่เลิกแล้ว'
];

$exerciseOptions = [
    'everyday' => 'ออกกำลังทุกวัน ครั้งละ 30 นาที',
    '3_times_week' => 'ออกกำลังกายสัปดาห์ละ 3 ครั้ง ครั้งละ 30 นาที',
    'less_than_3' => 'ออกน้อยกว่าสัปดาห์ละ 3 ครั้ง',
    'none' => 'ไม่ออกกำลังกาย'
];


$foodTasteOptions = [
    'sweet' => 'หวาน',
    'salty' => 'เค็ม',
    'fatty' => 'มัน',
    'sour' => 'เปรี้ยว',
    'none' => 'ไม่ชอบทุกข้อ'
];

$drivingOptions = [
    'none' => 'ไม่ขับขี่ไม่โดยสาร',
    'always' => 'ขับขี่/โดยสาร และใส่หมวกกันน็อก/คาดเข็มขัดนิรภัยทุกครั้ง',
    'sometimes' => 'ขับขี่/โดยสาร และใส่หมวกกันน็อก/คาดเข็มขัดนิรภัยบางครั้ง',
    'rarely' => 'ขับขี่/โดยสาร และใส่หมวกกันน็อก/คาดเข็มขัดนิรภัยนาน ๆ ครั้ง (ใส่เฉพาะเมื่อมีด่านตรวจ)'
];


$condomOptions = [
    'always' => 'ใช้ทุกครั้ง',
    'requested' => 'ใช้เมื่อถูกร้องขอ',
    'none' => 'ไม่ใช้',
    'no_extramarital' => 'ไม่เคยมีเพศสัมพันธ์กับผู้ที่ไม่ใช่สามีหรือภรรยาของตนเอง',
    'no_answer' => 'ไม่ตอบ'
];

$checkupItems = [
    ['code' => '41301', 'name' => 'Mass Chest', 'price' => 52.00],
    ['code' => '31201', 'name' => 'Stool Examination-Routine direct amear', 'price' => 70.00],
    ['code' => '30101', 'name' => 'Complete Blood Count CBC', 'price' => 90.00],
    ['code' => '32203', 'name' => 'Glucose', 'price' => 40.00],
    ['code' => '32501', 'name' => 'Cholesterol', 'price' => 60.00],
    ['code' => '32502', 'name' => 'Triglyceride', 'price' => 60.00],
    ['code' => '32201', 'name' => 'Blood Urea Nitrogen :BUN', 'price' => 50.00],
    ['code' => '32202', 'name' => 'Creatinine', 'price' => 50.00],
    ['code' => '32310', 'name' => 'SGOT (AST)', 'price' => 50.00],
    ['code' => '32311', 'name' => 'SGPT (ALT)', 'price' => 50.00],
    ['code' => '32309', 'name' => 'Alkaline Phosphatase', 'price' => 50.00],
    ['code' => '32205', 'name' => 'Uric Acid', 'price' => 60.00],
];

$radioLayout = [
    'item' => function ($index, $label, $name, $checked, $value) {
        return '<div class="form-check form-check-inline me-3">'
            . Html::radio($name, $checked, [
                'value' => $value,
                'class' => 'form-check-input',
                'label' => '<span>' . $label . '</span>',
                'labelOptions' => ['class' => 'form-check-label']
            ]) . '</div>';
    },
    'class' => 'd-flex justify-content-md-end justify-content-start'
];

// รายการโรคส่วนที่ 1 (Checkbox)
$familyDiseaseList = [
    'diabetes' => 'เบาหวาน',
    'hypertension' => 'ความดันโลหิตสูง',
    'gout' => 'โรคเก๊าท์',
    'kidney' => 'ไตวายเรื้อรัง',
    'heart' => 'กล้ามเนื้อหัวใจตาย',
    'stroke' => 'เส้นเลือดในสมอง',
    'emphysema' => 'ถุงลมโป่งพอง',
    'unknown' => 'ไม่ทราบ'
];
?>
<?php
// เตรียมข้อมูลส่วนที่ 1 และ 2 ไว้ใน Array เพื่อวนลูปลดความยาวโค้ด
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

<div class="health-form-container overflow-hidden">
    <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>

    <div class="row g-4 mb-4">
        <div class="col-xl-12">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-id-card"></i>
                </div>
                <h5 class="mb-0 fw-bold text-primary">หมวดที่ 1: ข้อมูลทั่วไป</h5>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><?= $form->field($model, 'data_json[thai_year]')->textInput(['placeholder' => 'ระบุปีที่ตรวจ'])->label('ปีที่ตรวจสุขภาพ (Checkup Year)') ?></div>
                <div class="col-md-6"><?= $form->field($model, 'data_json[screening_date]')->textInput(['readonly' => true, 'class' => 'form-control bg-light'])->label('วันที่ทำแบบคัดกรอง (Screening Date)') ?></div>
            </div>
        </div>
        <div class="col-4 border-end-md">
            <div class="card border-0 shadow-sm border-top border-4 border-danger h-100">

                <div class="card-body py-3">
                    <h6 class="fw-bold">1.1. บุหรี่</h6>

                    <?= $form->field($model, 'data_json[smoking_status]')->inline()->radioList($smokingStatusOptions)->label(false) ?>
                    <div class="bg-light p-2 rounded d-flex gap-2 align-items-center mt-2">
                        <span>จำนวน</span> <?= Html::activeTextInput($model, 'data_json[smoke_qty]', ['class' => 'form-control form-control-sm text-center w-25']) ?>
                        <span>มวน/วัน</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="card border-0 shadow-sm border-top border-4 border-info h-100">
                <div class="card-body py-3">
                    <h6 class="fw-bold">1.2. แอลกอฮอล์</h6>
                    <?= $form->field($model, 'data_json[alcohol_status]')->inline()->radioList($alcoholOptions)->label(false) ?>
                    <div class="bg-light p-2 rounded d-flex gap-2 align-items-center mt-2">
                        <span>ความถี่</span> <?= Html::activeTextInput($model, 'data_json[alcohol_qty]', ['class' => 'form-control form-control-sm text-center w-25']) ?>
                        <span>ครั้ง/สัปดาห์</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-4">
            <div class="card border-0 shadow-sm border-top border-4 border-success h-100">
                <div class="card-body py-3">
                    <h6 class="fw-bold">1.3. ออกกำลังกาย</h6>
                    <?= $form->field($model, 'data_json[exercise_status]')->dropDownList($exerciseOptions, ['class' => 'form-select form-select-sm mt-3'])->label(false) ?>
                </div>
            </div>
        </div>


        <?php
        $miniSections = [
            ['title' => '1.4. รสอาหาร', 'key' => 'food_taste', 'opt' => $foodTasteOptions, 'color' => 'warning'],
            ['title' => '1.5. ขับขี่ปลอดภัย', 'key' => 'driving_safety', 'opt' => $drivingOptions, 'color' => 'primary'],
            ['title' => '1.6. เพศสัมพันธ์', 'key' => 'condom_usage', 'opt' => $condomOptions, 'color' => 'danger']
        ];
        foreach ($miniSections as $sec): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-top border-4 border-<?= $sec['color'] ?> h-100">
                    <div class="card-body py-3">
                        <h6 class="fw-bold mb-3 text-uppercase"><?= $sec['title'] ?></h6>
                        <?= $form->field($model, "data_json[{$sec['key']}]")->dropDownList($sec['opt'], ['class' => 'form-select form-select-sm'])->label(false) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>




        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-weight text-primary"></i>
                </div>
                <h5 class="mb-0 fw-bold text-primary">หมวดที่ 2: การวัดทางกายภาพ</h5>
            </div>

        </div>
        <div class="col-md-6"><?= $form->field($model, 'data_json[weight]')->textInput(['id' => 'w-in', 'type' => 'number', 'step' => '0.1', 'placeholder' => '0.0'])->label('น้ำหนัก (กก.)') ?></div>
        <div class="col-md-6"><?= $form->field($model, 'data_json[height]')->textInput(['id' => 'h-in', 'type' => 'number', 'step' => '0.1', 'placeholder' => '0.0'])->label('ส่วนสูง (ซม.)') ?></div>
        <div class="p-4 border rounded-4 d-flex justify-content-between align-items-center bg-light shadow-sm">
            <div>
                <p class="small text-muted mb-1">ค่าดัชนีมวลกาย (BMI)</p>
                <h2 class="mb-0" id="bmi-val"><?= $model->data_json['bmi'] ?? 0.0 ?></h2>
            </div>
            <div class="text-end">
                <p class="small text-muted mb-1">ผลการประเมิน</p>
                <h4 class="text-primary mb-0" id="bmi-status"><?= $model->getBmiResult()['label'] ?></h4>
            </div>
        </div>


        <div class="card h-100 border-0 shadow-sm border-top border-4 border-primary">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-primary">ส่วนที่ 3: ประวัติการเจ็บป่วยในครอบครัว</h5>
                </div>
            </div>
            <div class="card-body pt-0">
                <p class="text-muted mb-3">บิดา มารดา หรือพี่น้อง มีประวัติโรคต่อไปนี้หรือไม่</p>
                <div class="row g-2">
                    <?= $form->field($model, 'data_json[family_history]')->checkboxList($familyDiseaseList, [
                        'item' => function ($index, $label, $name, $checked, $value) {
                            return '<div class="col-6 col-md-4">' . Html::checkbox($name, $checked, ['value' => $value, 'label' => '<span>' . $label . '</span>', 'class' => 'form-check-input']) . '</div>';
                        },
                        'class' => 'row g-0 px-3'
                    ])->label(false) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="card h-100 border-0 shadow-sm border-top border-4 border-danger">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="erp-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-file-medical text-danger"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-danger">ส่วนที่ 4: โรคประจำตัว</h5>
                </div>
                <span class="badge bg-light text-dark fw-normal">
                    <div class="d-flex gap-3">

                        <div class="d-flex align-items-center gap-1">
                            <label class="btn btn-xs rounded-circle p-1 ms-1 btn-outline-success" style="width:15px; height:15px;"></label>
                            <span class="fw-medium text-dark">
                                ไม่มี
                            </span>
                        </div>
                        <div class="d-flex align-items-center  gap-1">
                            <label class="btn btn-xs rounded-circle p-1 ms-1 btn-outline-danger" style="width:15px; height:15px;"></label>
                            <span class="fw-medium text-dark">
                                มี
                            </span>
                        </div>
                        <div class="d-flex align-items-center  gap-1">
                            <label class="btn btn-xs rounded-circle p-1 ms-1 btn-outline-secondary" style="width:15px; height:15px;"></label>
                            <span class="fw-medium text-dark">
                                ไม่เคยตรวจ
                            </span>
                        </div>

                    </div>
                </span>
            </div>
            <div class="card-body pt-0">
                <div class="row g-0 border-top">
                 <?php 
// แก้ปัญหา Indirect modification โดยดึงค่าออกมาพักไว้
$tmpData = $model->data_json ?? [];

foreach ($diseasesYear as $attr => $label): 
    // กำหนดค่า Default เป็น 0 หากยังไม่มีข้อมูล
    if (!isset($tmpData[$attr])) {
        $tmpData[$attr] = 0;
    }
?>
    <div class="col-md-6 border-bottom border-end-md py-2 px-3 d-flex justify-content-between align-items-center bg-hover">
        <span class="fw-medium text-dark"><?= $label ?></span>
        
        <?= $form->field($model, "data_json[$attr]")->radioList([0 => '', 1 => '', 2 => ''], [
            'item' => function ($index, $label, $name, $checked, $value) use ($tmpData, $attr) {
                // เช็คสถานะ Checked จากตัวแปรที่เราเตรียมไว้
                $isChecked = ($tmpData[$attr] == $value);
                
                $colors = [
                    0 => 'btn-outline-success', 
                    1 => 'btn-outline-danger', 
                    2 => 'btn-outline-secondary'
                ];

                return Html::radio($name, $isChecked, [
                    'value' => $value,
                    'class' => 'btn-check',
                    'id' => $name . '_' . $value
                ]) . Html::label('', $name . '_' . $value, [
                    'class' => 'btn btn-xs rounded-circle p-0 ms-2 ' . $colors[$value], 
                    'style' => 'width:20px; height:20px;'
                ]);
            },
            'class' => 'd-flex mb-0'
        ])->label(false) ?>
    </div>
<?php endforeach; ?>

<?php 
// อัปเดตค่ากลับเข้า Model
$model->data_json = $tmpData; 
?>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- 
<div class="card border-0 shadow-sm border-top border-4 border-dark mb-5">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2"></i>ส่วนที่ 9: รายการตรวจสุขภาพ</h5>
        <div class="h5 mb-0 text-primary fw-bold">รวม <span id="total-price-display">682.00</span> บาท</div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">รายการ</th>
                    <th class="text-end pe-4">ราคา (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checkupItems as $item): ?>
                    <tr>
                        <td class="ps-4 py-2">
                            <div class="form-check">
                                <?= Html::checkbox("data_json[checkup_list][{$item['code']}]", true, ['class' => 'form-check-input checkup-item', 'data-price' => $item['price'], 'id' => 'chk-' . $item['code']]) ?>
                                <label class="form-check-label ms-2" for="chk-<?= $item['code'] ?>"><?= $item['name'] ?></label>
                            </div>
                        </td>
                        <td class="text-end pe-4 fw-bold"><?= number_format($item['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div> -->


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
 thaiDatepicker('#employeedetail-data_json-screening_date')

    $('.checkup-item').on('change', function() {
        let total = 0;
        $('.checkup-item:checked').each(function() {
            total += parseFloat($(this).data('price'));
        });
        $('#total-price-display').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
    });

        $('#w-in, #h-in').on('input', function(){
        let w = parseFloat($('#w-in').val());
        let h = parseFloat($('#h-in').val())/100;
        if(w && h) {
            let bmi = (w/(h*h)).toFixed(1);
            $('#bmi-val').text(bmi);
            $('#employeedetail-data_json-bmi').val(bmi);
            $('#bmi-status').text(bmi < 23 ? 'ปกติ' : 'เริ่มอ้วน').css('color', bmi < 23 ? '#2563eb' : '#ef4444');
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


<?php ActiveForm::end(); ?>