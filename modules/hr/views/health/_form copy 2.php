<style>
    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
    }

    .progress {
        height: 8px;
        border-radius: 50px;
        background-color: #e2e8f0;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #475569;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border-radius: 0.75rem;
        padding: 0.625rem 1rem;
        border: 1px solid #e2e8f0;
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .bg-slate-50 {
        background-color: #f8fafc;
    }

    /* Checkbox & Button Style */
    .check-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        background: #fff;
    }

    .check-card:hover {
        background-color: #f8fafc;
    }

    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        margin-right: 1rem;
        cursor: pointer;
    }

    .btn-pill {
        border-radius: 9999px;
        padding: 0.5rem 1.25rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        font-weight: 500;
    }

    .btn-pill.active {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .rating-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        border: none;
        background: #eff6ff;
        color: #1e40af;
        font-weight: bold;
        transition: all 0.2s;
    }

    .rating-btn.active {
        background: #3b82f6;
        color: white;
    }

    .btn-next {
        background-color: #2563eb;
        color: white;
        padding: 0.625rem 2.5rem;
        border-radius: 0.75rem;
        font-weight: bold;
        border: none;
    }

    .btn-next:hover {
        background-color: #1d4ed8;
    }

    .btn-prev {
        color: #94a3b8;
        font-weight: bold;
        text-decoration: none;
        border: none;
        background: none;
    }

    .animate-fadeIn {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
</head>
<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\EmployeeDetail */

$this->registerCss("
    .step-content { display: none; }
    .step-content.active { display: block; animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .rating-circle { width: 45px; height: 45px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: bold; color: #3b82f6; transition: 0.3s; border: 2px solid transparent; }
    .rating-circle.active { background: #2563eb; color: white; transform: scale(1.1); box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4); }
    
    .choice-pill { border: 1px solid #e2e8f0; border-radius: 50px; padding: 8px 20px; cursor: pointer; background: white; color: #64748b; font-weight: 500; transition: 0.2s; display: inline-block; margin-right: 8px; }
    .choice-pill.active { background: #2563eb; color: white; border-color: #2563eb; }
    
    .summary-card { background: white; border-radius: 12px; border: 1px solid #f1f5f9; padding: 15px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .diagnosis-box { background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0; padding: 25px; }
    .form-control { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 15px; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
");
?>

<div class="health-screening-formxx">
    <div class="container">
        <?php $form = ActiveForm::begin(['id' => 'form']); ?>
        <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[bmi]')->hiddenInput()->label(false) ?>

        <h2 class="text-center text-slate-800 mb-4">แบบคัดกรองสุขภาพรายบุคคล</h2>


        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-id-card"></i>
            </div>
            หมวดที่ 1: ข้อมูลทั่วไป
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6"><?= $form->field($model, 'emp_id')->textInput(['readonly' => true, ])->label('รหัสบุคลากร (Staff ID)') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'data_json[fullname]')->textInput(['readonly' => true, ])->label('ชื่อ-นามสกุล (Full Name)') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'data_json[age]')->textInput(['readonly' => true, ])->label('อายุ (Age)') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'data_json[gender]')->textInput(['readonly' => true, ])->label('เพศ (Gender)') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'data_json[thai_year]')->textInput(['placeholder' => 'ระบุปีที่ตรวจ'])->label('ปีที่ตรวจสุขภาพ (Checkup Year)') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'data_json[screening_date]')->textInput(['readonly' => true, ])->label('วันที่ทำแบบคัดกรอง (Screening Date)') ?></div>
        </div>


        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-weight"></i>
            </div>
            หมวดที่ 2: การวัดทางกายภาพ
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-6"><?= $form->field($model, 'data_json[weight]')->textInput(['id' => 'w-in', 'type' => 'number', 'step' => '0.1', 'placeholder' => '0.0'])->label('น้ำหนัก (กก.)') ?></div>
            <div class="col-md-6"><?= $form->field($model, 'data_json[height]')->textInput(['id' => 'h-in', 'type' => 'number', 'step' => '0.1', 'placeholder' => '0.0'])->label('ส่วนสูง (ซม.)') ?></div>
        </div>
        <div class="p-4 border rounded-4 d-flex justify-content-between align-items-center bg-light shadow-sm mb-4">
            <div>
                <p class="small text-muted mb-1">ค่าดัชนีมวลกาย (BMI)</p>
                <h2 class="mb-0" id="bmi-val"><?= $model->data_json['bmi'] ?? 0.0 ?></h2>
            </div>
            <div class="text-end">
                <p class="small text-muted mb-1">ผลการประเมิน</p>
                <h4 class="text-primary mb-0" id="bmi-status"><?= $model->getBmiResult()['label'] ?></h4>
            </div>
        </div>


        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-notes-medical"></i>
            </div>
            หมวดที่ 3: ประวัติโรคประจำตัว
        </div>
        <div class="row g-3 mb-3">
            <?php
            // ดึงค่าที่เคยบันทึกไว้มาเก็บในตัวแปร (ตรวจสอบว่าเป็น Array หรือไม่)
            $savedDiseases = $model->data_json['chronic_diseases'] ?? [];
            if (!is_array($savedDiseases)) {
                $savedDiseases = [];
            }
            ?>

            <!-- <?php //foreach (['เบาหวาน', 'ความดันโลหิตสูง', 'ไขมันในเลือดสูง', 'โรคหัวใจ', 'โรคไต', 'มะเร็ง'] as $d): 
                    ?> -->
            <?php foreach ($model->getChronicDiseasesList() as $item): ?>
                <div class="col-md-6 mb-2">
                    <label class="d-flex align-items-center p-3 border rounded-3 hover-bg-light w-100 cursor-pointer">
                        <?php
                        // สมมติว่าในตาราง Categorise มีคอลัมน์ชื่อ 'item_name' เก็บชื่อโรค
                        $diseaseValue = $item->title;

                        // ตรวจสอบว่าค่านี้นี้เคยถูกบันทึกไว้ใน JSON หรือไม่
                        $isChecked = in_array($diseaseValue, $savedDiseases);

                        echo Html::checkbox("EmployeeDetail[data_json][chronic_diseases][]", $isChecked, [
                            'value' => $diseaseValue, // ส่งค่านี้ไปบันทึก
                            'class' => 'form-check-input me-3'
                        ]);
                        ?>
                        <span class="fw-medium"><?= $diseaseValue ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>


        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-users"></i>
            </div>
            หมวดที่ 4: ประวัติครอบครัว
        </div>
        <div class="row g-3">
            <?php
            // 1. ดึงข้อมูลที่เคยบันทึกไว้ใน family_history
            $savedFamilyHistory = $model->data_json['family_history'] ?? [];
            if (!is_array($savedFamilyHistory)) {
                $savedFamilyHistory = [];
            }
            ?>

            <?php foreach (['เบาหวาน', 'ความดันโลหิตสูง', 'ไขมันในเลือดสูง', 'โรคหัวใจ'] as $d): ?>
                <div class="col-md-6">
                    <label class="d-flex align-items-center p-3 border rounded-3 hover-bg-light w-100 cursor-pointer">
                        <?php
                        // 2. เช็คว่าโรคนี้มีอยู่ใน array ข้อมูลเก่าหรือไม่
                        $isChecked = in_array($d, $savedFamilyHistory);

                        echo Html::checkbox("EmployeeDetail[data_json][family_history][]", $isChecked, [
                            'value' => $d,
                            'class' => 'form-check-input me-3'
                        ]);
                        ?>
                        <span class="fw-medium"><?= $d ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

       <div class="d-flex align-items-center gap-3 mb-3 mt-4">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-heartbeat"></i>
            </div>
             หมวดที่ 5: พฤติกรรมสุขภาพ
        </div>

        <?php
        $habits = [
            'smoking' => ['label' => 'การสูบบุหรี่', 'opts' => ['ไม่สูบ', 'ยังสูบอยู่', 'เคยสูบ']],
            'alcohol' => ['label' => 'การดื่มแอลกอฮอล์', 'opts' => ['ไม่ดื่ม', 'ยังดื่มอยู่', 'เคยดื่ม']],
            'exercise' => ['label' => 'การออกกำลังกาย', 'opts' => ['สม่ำเสมอ', 'ไม่สม่ำเสมอ', 'ไม่ออกกำลังกาย']]
        ];

        foreach ($habits as $key => $h):
            // 1. ดึงค่าที่เคยบันทึกไว้ (ถ้าไม่มีให้เป็นค่าว่าง)
            $currentValue = $model->data_json[$key] ?? '';
        ?>
            <div class="mb-4 habit-group">
                <label class="text-slate-700 mb-3 d-block"><?= $h['label'] ?></label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($h['opts'] as $opt): ?>
                        <?php
                        // 2. เช็คว่าตัวเลือกนี้ตรงกับค่าใน Database หรือไม่
                        $isActive = ($currentValue === $opt);
                        ?>
                        <label class="choice-pill <?= $isActive ? 'active' : '' ?>">
                            <?= Html::radio("EmployeeDetail[data_json][$key]", $isActive, [
                                'value' => $opt,
                                'class' => 'd-none'
                            ]) ?>
                            <?= $opt ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
       <div class="d-flex align-items-center gap-3 mb-3 mt-4">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-user-shield"></i>
            </div>
หมวดที่ 6: สุขภาพจากการทำงาน
                    </div>

        <p class="text-slate-600 mb-3">ปัจจัยเสี่ยงในที่ทำงาน</p>
        <div class="row g-3">
            <?php
            // ดึงค่าปัจจัยเสี่ยงที่เคยบันทึกไว้
            $savedWorkRisks = $model->data_json['work_risks'] ?? [];
            if (!is_array($savedWorkRisks)) $savedWorkRisks = [];
            ?>
            <?php foreach (['สารเคมี', 'เสียงดัง', 'ท่าทาง (ยกของหนัก/ยืนนาน)', 'ความเครียดจากการทำงาน'] as $d): ?>
                <div class="col-md-6">
                    <label class="d-flex align-items-center p-3 border rounded-3 hover-bg-light w-100 cursor-pointer">
                        <?php
                        $isChecked = in_array($d, $savedWorkRisks);
                        echo Html::checkbox("EmployeeDetail[data_json][work_risks][]", $isChecked, [
                            'value' => $d,
                            'class' => 'form-check-input me-3'
                        ]);
                        ?>
                        <span class="fw-medium"><?= $d ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
       <div class="d-flex align-items-center gap-3 mb-3 mt-4">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-smile"></i>
            </div>
             หมวดที่ 7: การประเมินตนเอง
        </div>

        <?php
        $ratings = [
            'stress_level' => '1. ระดับความเครียดในช่วง 1 เดือนที่ผ่านมา (1-5)',
            'happiness_level' => '2. ระดับความสุขโดยรวม (1-5)'
        ];
        foreach ($ratings as $key => $label):
            // ดึงค่าคะแนนที่เคยบันทึกไว้ (1-5)
            $currentRating = $model->data_json[$key] ?? null;
        ?>
            <div class="mb-5 rating-group">
                <p class="text-slate-700 mb-4"><?= $label ?></p>
                <div class="d-flex justify-content-around px-md-5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php
                        // เช็คว่าคะแนนรอบลูปตรงกับที่บันทึกไว้หรือไม่
                        // ใช้ == เพราะบางทีค่าจาก DB อาจเป็น String หรือ Integer
                        $isRatingActive = ($currentRating == $i);
                        ?>
                        <label class="rating-circle <?= $isRatingActive ? 'active' : '' ?>">
                            <?= Html::radio("EmployeeDetail[data_json][$key]", $isRatingActive, [
                                'value' => $i,
                                'class' => 'd-none'
                            ]) ?>
                            <span><?= $i ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>

        
        <h5 class="mb-4 text-primary"><i class="fas fa-user-md me-2"></i> หมวดที่ 8: ผลการตรวจและวินิจฉัยของแพทย์</h5>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="p-4 rounded-4 h-100" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                    <h6 class="text-uppercase small text-muted mb-4">ข้อมูลทางกายภาพเบื้องต้น</h6>
                    <?= $form->field($model, 'data_json[bloodPressure]')->textInput(['id' => 'bp-in', 'placeholder' => 'เช่น 120/80'])->label('ความดันโลหิต (Blood Pressure)') ?>
                    <?= $form->field($model, 'data_json[waistCircumference]')->textInput(['id' => 'waist-in', 'placeholder' => '0.0'])->label('รอบเอว (Waist Circumference) - ซม.') ?>
                    <?= $form->field($model, 'data_json[anemiaStatus]')->dropDownList(['' => 'เลือกสถานะ', 'Negative' => 'ปกติ (Negative)', 'Mild' => 'ซีดเล็กน้อย (Mild)', 'Severe' => 'ซีดมาก (Severe)'], ['id' => 'anemia-in'])->label('ภาวะซีด (Pallor/Anemia Status)') ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-4 rounded-4 h-100" style="background-color: #f0f7ff; border: 1px solid #dbeafe;">
                    <h6 class="text-uppercase small text-primary mb-4">ผลทางห้องปฏิบัติการ (LAB)</h6>
                    <?= $form->field($model, 'data_json[bloodSugar]')->textInput(['id' => 'fbs-in', 'placeholder' => 'มก./ดล.'])->label('ระดับน้ำตาลในเลือด (FBS)') ?>
                    <?= $form->field($model, 'data_json[cholesterol]')->textInput(['id' => 'chol-in', 'placeholder' => 'มก./ดล.'])->label('ระดับไขมันรวม (Cholesterol)') ?>
                    <button type="button" class="btn btn-white w-100 border-dashed border-2 py-3 mt-2 text-primary small rounded-3"><i class="fas fa-cloud-upload-alt me-2"></i> อัปโหลดรูปภาพผล Lab</button>
                </div>
            </div>
        </div>
        <div class="mt-4 border-top pt-4">
            <h6 class="text-uppercase small text-muted mb-4">การวินิจฉัยและความเห็นแพทย์</h6>
            <div class="row g-3">
                <div class="col-md-6"><?= $form->field($model, 'data_json[diagnosis]')->textarea(['id' => 'diag-in', 'rows' => 2])->label('ระบุโรคที่ตรวจพบ (Diagnosis)') ?></div>
                <div class="col-md-6"><?= $form->field($model, 'data_json[riskFactors]')->textarea(['id' => 'risk-in', 'rows' => 2])->label('ปัจจัยเสี่ยงที่ตรวจพบ (Risk Factors)') ?></div>
                <div class="col-12"><?= $form->field($model, 'data_json[doctorOpinion]')->textarea(['id' => 'opinion-in', 'rows' => 2])->label('ความเห็นของแพทย์ (Doctor\'s Opinion)') ?></div>
                <div class="col-md-6"><?= $form->field($model, 'data_json[carePlan]')->textarea(['id' => 'plan-in', 'rows' => 2])->label('แผนการดูแล (Care Plan)') ?></div>
                <div class="col-md-6"><?= $form->field($model, 'data_json[medicalAdvice]')->textarea(['id' => 'advice-in', 'rows' => 2])->label('คำแนะนำ (Medical Advice)') ?></div>
            </div>
            <div class="p-3 bg-danger-subtle rounded-3 d-flex justify-content-between align-items-center mt-3 border border-danger-subtle">
                <span class="text-danger">ส่งตัวไปรักษาต่อ (Referral)</span>
                <?= Html::checkbox('EmployeeDetail[data_json][is_referral]', false, ['class' => 'form-check-input']) ?>
            </div>
            <div class="mt-4"><?= $form->field($model, 'data_json[overallSummary]')->textarea(['id' => 'summary-in', 'rows' => 3, 'class' => 'form-control border-primary-subtle'])->label('สรุปผลการตรวจสุขภาพ (Overall Summary)') ?></div>
        </div>

        <div class="py-5">
            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 70px; height: 70px;">
                <i class="fas fa-check fa-2x"></i>
            </div>
            <h3 class="mb-1">รายงานสรุปผลการตรวจสุขภาพประจำปี</h3>
            <p class="text-muted fw-medium">ประจำปีงบประมาณ <?= $model->data_json['thai_year'] ?? '' ?></p>
        </div>

        <div class="row g-2 mb-4 px-md-4">
            <?php
            $summary = [
                ['BMI', 'out-bmi', ''],
                ['รอบเอว', 'out-waist', ' ซม.'],
                ['ความดัน', 'out-bp', ''],
                ['น้ำตาล', 'out-fbs', ''],
                ['ไขมัน', 'out-chol', ''],
                ['ภาวะซีด', 'out-anemia', '']
            ];
            foreach ($summary as $s):
            ?>
                <div class="col-4 col-md-2">
                    <div class="summary-card">
                        <p class="small text-muted text-uppercase mb-1" style="font-size: 10px;"><?= $s[0] ?></p>
                        <p class="h5 mb-0 text-dark" id="<?= $s[1] ?>">-</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="diagnosis-box text-start px-4">
            <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                <i class="fas fa-clipboard-list text-primary me-2"></i> <span class="fw-bold">บันทึกการวินิจฉัยและแผนการดูแล</span>
            </div>
            <div class="row gy-4">
                <div class="col-md-6">
                    <label class="small text-muted text-uppercase d-block mb-1" style="font-size: 10px;">โรคที่ตรวจพบ</label>
                    <p class="mb-0" id="res-diag">ปกติ/ไม่พบ</p>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted text-uppercase d-block mb-1" style="font-size: 10px;">แผนการดูแล</label>
                    <p class="mb-0" id="res-plan">-</p>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted text-uppercase d-block mb-1" style="font-size: 10px;">ปัจจัยเสี่ยง</label>
                    <p class="mb-0" id="res-risk">ไม่พบปัจจัยเสี่ยง</p>
                </div>
                <div class="col-md-6">
                    <label class="small text-muted text-uppercase d-block mb-1" style="font-size: 10px;">คำแนะนำ</label>
                    <p class="mb-0" id="res-advice">-</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-top">
                <p class="small text-primary text-uppercase mb-1" style="font-size: 10px;">สรุปผลการตรวจสุขภาพ (OVERALL SUMMARY)</p>
                <p class="text-dark mb-0" id="res-summary">-</p>
            </div>


            <div class="d-flex gap-3 mt-5">
                <button type="button" class="btn btn-light flex-grow-1 py-3 rounded-4 border">กลับหน้าโปรไฟล์</button>
                <?= Html::submitButton('<i class="fas fa-print me-2"></i> พิมพ์รายงานผล', ['class' => 'btn btn-primary flex-grow-1 py-3 rounded-4 shadow-lg']) ?>
            </div>
        </div>

        <div id="nav-footer" class="mt-5 d-flex justify-content-between align-items-center pt-4 border-top">
            <button type="button" id="prev" class="btn btn-light text-decoration-none text-muted"><i class="fa-solid fa-chevron-left"></i> ย้อนกลับ</button>

            <button type="button" id="next" class="btn btn-primary px-5 py-2.5 rounded-pill"> ถัดไป <i class="fa-solid fa-chevron-right"></i></button>

            <?= Html::submitButton('<i class="fas fa-save me-2"></i> บันทึกและดูรายงาน', [
                'id' => 'btn-submit',
                'class' => 'btn btn-success px-5 py-2.5 rounded-pill',
                'style' => 'display:none;'
            ]) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
</div>
</div>

<?php
$js = <<<JS

 thaiDatepicker('#employeedetail-data_json-screening_date')

    // คลิกปุ่มบันทึก (AJAX + SweetAlert2)
    $('#btn-submit').click(function(e) {
        e.preventDefault(); // ป้องกันการ Submit แบบปกติ

        Swal.fire({
            title: 'ยืนยันการบันทึกข้อมูล?',
            text: "ข้อมูลคัดกรองจะถูกบันทึกลงในระบบ",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'ยืนยันบันทึก',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // แสดง Loading ระหว่างส่งข้อมูล
                Swal.fire({
                    title: 'กำลังบันทึกข้อมูล...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });

                // ส่งข้อมูลแบบ AJAX
                let form = $('#form');
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกสำเร็จ!',
                                text: 'ระบบกำลังนำคุณไปยังหน้าสรุปผล',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Redirect ไปหน้า View (รายงานสรุป)
                                window.location.href = response.redirectUrl;
                            });
                        } else {
                            Swal.fire('เกิดข้อผิดพลาด', response.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            }
        });
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

    // UI Interactive
    $('.choice-pill').click(function(){
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
    });
    $('.rating-circle').click(function(){
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
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

    go(1);
JS;
$this->registerJs($js, View::POS_END);
?>