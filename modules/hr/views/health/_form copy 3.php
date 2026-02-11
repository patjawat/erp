<?php
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
?>
<?php $form = ActiveForm::begin([
        'id' => 'full-health-check-form',
        'options' => ['class' => 'needs-validation']
    ]); ?>

<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// ตัวเลือกพื้นฐานสำหรับ Radio (อ้างอิงจาก Screenshot)
$radioOptions = [0 => 'ไม่มี', 1 => 'มี', 2 => 'ไม่เคยตรวจ'];

$radioLayout = [
    'item' => function ($index, $label, $name, $checked, $value) {
        return '<div class="form-check form-check-inline me-3">' 
            . Html::radio($name, $checked, [
                'value' => $value,
                'class' => 'form-check-input',
                'label' => '<span class="small">'.$label.'</span>',
                'labelOptions' => ['class' => 'form-check-label']
            ]) . '</div>';
    },
    'class' => 'd-flex justify-content-md-end justify-content-start'
];

// รายการโรคส่วนที่ 1 (Checkbox)
$familyDiseaseList = [
    'diabetes' => 'เบาหวาน', 'hypertension' => 'ความดันโลหิตสูง', 'gout' => 'โรคเก๊าท์',
    'kidney' => 'ไตวายเรื้อรัง', 'heart' => 'กล้ามเนื้อหัวใจตาย', 'stroke' => 'เส้นเลือดในสมอง',
    'emphysema' => 'ถุงลมโป่งพอง', 'unknown' => 'ไม่ทราบ'
];
?>

<div class="mb-5 mt-4">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 1: ข้อมูลครอบครัว</h3>
    <p class="text-muted border-start border-4 border-primary ps-3">ระบุประวัติการเจ็บป่วยของบุคคลในครอบครัวสายตรง</p>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <h5 class="fw-bold text-primary mb-4 border-bottom pb-3">บิดาหรือมารดามีประวัติการเจ็บป่วยด้วย</h5>
        <div class="row g-3">
            <?= $form->field($model, 'data_json[parent_diseases]')->checkboxList($familyDiseaseList, [
                'item' => function ($index, $label, $name, $checked, $value) {
                    return '<div class="col-sm-6 col-md-4 mb-2"><div class="form-check custom-check py-1">'
                        . Html::checkbox($name, $checked, ['value' => $value, 'label' => $label, 'class' => 'form-check-input', 'id' => 'p-'.$value])
                        . '</div></div>';
                },
                'class' => 'row g-0'
            ])->label(false) ?>
            <div class="col-md-4 mt-2">
                <?= $form->field($model, 'data_json[parent_other]')->textInput(['placeholder' => 'อื่นๆ ระบุ...', 'class' => 'form-control border-0 border-bottom bg-light'])->label(false) ?>
            </div>
        </div>

        <h5 class="fw-bold text-success mt-5 mb-4 border-bottom pb-3">พี่น้อง (สายตรง) มีประวัติการเจ็บป่วยด้วย</h5>
        <div class="row g-3">
            <?= $form->field($model, 'data_json[sibling_diseases]')->checkboxList($familyDiseaseList, [
                'item' => function ($index, $label, $name, $checked, $value) {
                    return '<div class="col-sm-6 col-md-4 mb-2"><div class="form-check custom-check py-1">'
                        . Html::checkbox($name, $checked, ['value' => $value, 'label' => $label, 'class' => 'form-check-input', 'id' => 's-'.$value])
                        . '</div></div>';
                },
                'class' => 'row g-0'
            ])->label(false) ?>
            <div class="col-md-4 mt-2">
                <?= $form->field($model, 'data_json[sibling_other]')->textInput(['placeholder' => 'อื่นๆ ระบุ...', 'class' => 'form-control border-0 border-bottom bg-light'])->label(false) ?>
            </div>
        </div>
    </div>
</div>

<div class="mb-5 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 2: ประวัติเจ็บป่วยในปีที่ผ่านมา</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <div class="row g-0">
            <?php
            // รายการโรคอ้างอิงจาก Screenshot
            $diseasesYear = [
                'h_diabetes' => 'เบาหวาน', 'h_hypertension' => 'ความดันโลหิตสูง',
                'h_liver' => 'โรคตับ', 'h_stroke' => 'โรคอัมพาต',
                'h_heart' => 'โรคหัวใจ', 'h_dyslipidemia' => 'ไขมันในเลือดผิดปกติ',
                'h_gastric' => 'แผลในกระเพาะอาหาร', 'h_birth' => 'คลอดบุตรน้ำหนักเกิน 4 กก.',
                'h_thirst' => 'ดื่มน้ำบ่อยและมาก', 'h_nocturia' => 'ปัสสาวะกลางคืน 3 ครั้งขึ้นไป',
                'h_fatigue' => 'น้ำหนักลด/อ่อนเพลีย', 'h_skin_itch' => 'คันตามผิวหนัง',
                'h_vision' => 'ตาพร่ามัวต้องเปลี่ยนแว่นบ่อย', 'h_numbness' => 'ชาตามปลายมือปลายเท้า',
                'h_constipation' => 'ท้องผูกสลับท้องเสีย เกิน 6 สัปดาห์', 'h_urinary' => 'ปัสสาวะปนเลือด/ลำบาก',
                'h_menstruation' => 'เลือดออกผิดปกติประจำเดือน', 'h_chronic_wound' => 'แผลเรื้อรังไม่หายใน 3 สัปดาห์',
                'h_breast_lump' => 'มีก้อนที่เต้านมหรือตามตัว', 'h_mole' => 'ไฝโตขึ้นหรือเปลี่ยนสี',
                'h_chronic_cough' => 'ไอเรื้อรัง/เสียงแหบ เกิน 1 เดือน', 'h_weight_loss' => 'น้ำหนักลดเกินร้อยละ 10 ใน 6 เดือน',
                'h_otitis' => 'หูอื้อเรื้อรัง', 'h_jaundice' => 'เคยตัวเหลือง ตาเหลือง',
                'h_hepatitis' => 'เคยตรวจพบเชื้อไวรัสตับอักเสบ', 'h_thyroid' => 'เป็นโรคต่อมธัยรอยด์'
            ];

            foreach ($diseasesYear as $attr => $label): ?>
                <div class="col-md-6 border-bottom-dashed py-3 px-md-3">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-md-5 mb-2 mb-lg-0">
                            <label class="fw-medium small"><?= $label ?></label>
                        </div>
                        <div class="col-lg-6 col-md-7">
                            <?= $form->field($model, "data_json[$attr]")->radioList($radioOptions, $radioLayout)->label(false) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php 
            $specials = ['h_cancer' => 'โรคมะเร็ง', 'h_injury' => 'บาดเจ็บ อุบัติเหตุจากการทำงาน', 'h_infection' => 'โรคติดเชื้อจากการทำงาน'];
            foreach ($specials as $attr => $label): ?>
                <div class="col-12 border-bottom-dashed py-4 bg-light bg-opacity-25 px-3">
                    <div class="row align-items-center">
                        <div class="col-md-3 fw-bold small"><?= $label ?></div>
                        <div class="col-md-4">
                            <?= $form->field($model, "data_json[$attr]")->radioList($radioOptions, $radioLayout)->label(false) ?>
                        </div>
                        <div class="col-md-5 mt-2 mt-md-0">
                            <?= $form->field($model, "data_json[{$attr}_note]")->textInput(['placeholder' => 'ระบุรายละเอียด...', 'class' => 'form-control form-control-sm border-0 border-bottom bg-transparent rounded-0'])->label(false) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 p-4 bg-light rounded-3 border-start border-4 border-warning shadow-sm">
            <label class="fw-bold mb-3 d-block small text-uppercase">กรณีที่ท่านมีประวัติเจ็บป่วย ท่านปฏิบัติตนอย่างไร?</label>
            <?= $form->field($model, 'data_json[treatment_action]')->radioList([
                1 => 'รับการรักษาอยู่/ปฏิบัติตามที่แพทย์แนะนำ',
                2 => 'รับการรักษา แต่ไม่สม่ำเสมอ',
                3 => 'เคยรักษา ขณะนี้ไม่รักษา/หายาทานเอง'
            ], [
                'item' => function ($index, $label, $name, $checked, $value) {
                    return '<div class="form-check col-lg-4 mb-2 mb-lg-0">' 
                        . Html::radio($name, $checked, ['value' => $value, 'class' => 'form-check-input']) 
                        . '<label class="form-check-label small ms-2">'.$label.'</label></div>';
                },
                'class' => 'row g-0'
            ])->label(false) ?>
        </div>
    </div>
</div>

<style>
    .border-bottom-dashed { border-bottom: 1px dashed #dee2e6; }
    .form-check-input { width: 1.1rem; height: 1.1rem; cursor: pointer; }
    .custom-check:hover { background-color: rgba(13, 110, 253, 0.05); border-radius: 4px; }
</style>


<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// --- ค่ากำหนดสำหรับส่วนที่ 3 ---
$smokingStatusOptions = [
    'smoke' => 'สูบ',
    'none' => 'ไม่สูบ',
    'quit' => 'เคยสูบแต่เลิกแล้ว'
];
?>

<div class="mb-5 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 3: ประวัติการสูบบุหรี่</h3>
    <p class="text-muted border-start border-4 border-info ps-3">กรุณาระบุพฤติกรรมการสูบบุหรี่ของท่านตามความเป็นจริง</p>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <div class="row align-items-start">
            <div class="col-12">
                <label class="fw-bold mb-4 text-dark d-block">ท่านสูบบุหรี่หรือไม่</label>
                
                <?= $form->field($model, 'data_json[smoking_status]')->radioList($smokingStatusOptions, [
                    'item' => function ($index, $label, $name, $checked, $value) use ($form, $model) {
                        $activeClass = $checked ? 'bg-light border-primary' : '';
                        $content = '<div class="form-check custom-radio-box p-3 mb-3 border rounded-3 ' . $activeClass . '">';
                        $content .= Html::radio($name, $checked, [
                            'value' => $value,
                            'class' => 'form-check-input',
                            'id' => 'smoke_status_' . $value
                        ]);
                        $content .= '<label class="form-check-label fw-bold ms-2" for="smoke_status_' . $value . '">' . $label . '</label>';
                        
                        // ถ้าเป็นตัวเลือก "สูบ" ให้แสดงช่องกรอกรายละเอียดต่อท้าย (อ้างอิงตาม Screenshot)
                        if ($value === 'smoke') {
                            $content .= '<div class="mt-3 ps-4 border-start ml-3">';
                            $content .= '<div class="row g-2 align-items-center">';
                            
                            $content .= '<div class="col-auto">จำนวน</div>';
                            $content .= '<div class="col-sm-2">' . Html::activeTextInput($model, 'data_json[smoke_qty]', ['class' => 'form-control form-control-sm text-center', 'placeholder' => '0']) . '</div>';
                            $content .= '<div class="col-auto">มวน/วัน</div>';
                            
                            $content .= '<div class="col-auto ms-md-3">ชนิดของบุหรี่</div>';
                            $content .= '<div class="col-sm-3">' . Html::activeTextInput($model, 'data_json[smoke_type]', ['class' => 'form-control form-control-sm', 'placeholder' => 'ระบุชนิด...']) . '</div>';
                            
                            $content .= '<div class="col-auto ms-md-3">ระยะเวลา</div>';
                            $content .= '<div class="col-sm-2">' . Html::activeTextInput($model, 'data_json[smoke_years]', ['class' => 'form-control form-control-sm text-center', 'placeholder' => '0']) . '</div>';
                            $content .= '<div class="col-auto">ปี</div>';
                            
                            $content .= '</div></div>';
                        }
                        
                        $content .= '</div>';
                        return $content;
                    }
                ])->label(false) ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* สไตล์เพิ่มเติมสำหรับส่วนที่ 3 */
    .custom-radio-box {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .custom-radio-box:hover {
        border-color: #0dcaf0 !important;
        background-color: rgba(13, 202, 240, 0.02);
    }
    .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
    }
    /* ปรับแต่ง Input ในบรรทัดเดียวกัน */
    .form-control-sm {
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    .form-control-sm:focus {
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.1);
    }
</style>

<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// ตัวเลือกสำหรับพฤติกรรมสุขภาพ
$smokingOptions = [
    'smoke' => 'สูบ',
    'none' => 'ไม่สูบ',
    'quit' => 'เคยสูบแต่เลิกแล้ว'
];

$alcoholOptions = [
    'drink' => 'ดื่ม',
    'none' => 'ไม่ดื่ม',
    'quit' => 'เคยดื่มแต่เลิกแล้ว'
];
?>

<div class="mb-4 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 3: ประวัติการสูบบุหรี่</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <label class="fw-bold mb-4 text-dark d-block">ท่านสูบบุหรี่หรือไม่</label>
        
        <div class="smoking-section">
            <?= $form->field($model, 'data_json[smoking_status]')->radioList($smokingOptions, [
                'item' => function ($index, $label, $name, $checked, $value) use ($model) {
                    $id = 'smoke_' . $value;
                    $content = '<div class="mb-3">';
                    $content .= '<div class="form-check form-check-inline">';
                    $content .= Html::radio($name, $checked, ['value' => $value, 'class' => 'form-check-input', 'id' => $id]);
                    $content .= Html::label($label, $id, ['class' => 'form-check-label fw-medium ms-2']);
                    $content .= '</div>';

                    // แสดงช่องกรอกข้อมูลเพิ่มเติมเฉพาะแถว "สูบ"
                    if ($value === 'smoke') {
                        $content .= '<div class="d-inline-flex flex-wrap align-items-center gap-3 ms-md-4 mt-2 mt-md-0 ps-4 border-start">';
                        
                        $content .= '<span>จำนวน</span>';
                        $content .= Html::activeTextInput($model, 'data_json[smoke_qty]', [
                            'class' => 'form-control form-control-sm text-center', 
                            'style' => 'width: 70px;',
                            'placeholder' => '0'
                        ]);
                        $content .= '<span>มวน/วัน</span>';

                        $content .= '<span class="ms-md-3">ชนิดของบุหรี่</span>';
                        $content .= Html::activeTextInput($model, 'data_json[smoke_type]', [
                            'class' => 'form-control form-control-sm', 
                            'style' => 'width: 150px;',
                            'placeholder' => 'ระบุ...'
                        ]);

                        $content .= '<span class="ms-md-3">ระยะเวลา</span>';
                        $content .= Html::activeTextInput($model, 'data_json[smoke_years]', [
                            'class' => 'form-control form-control-sm text-center', 
                            'style' => 'width: 70px;',
                            'placeholder' => '0'
                        ]);
                        $content .= '<span>ปี</span>';
                        
                        $content .= '</div>';
                    }
                    $content .= '</div>';
                    return $content;
                }
            ])->label(false) ?>
        </div>
    </div>
</div>

<div class="mb-4 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 4: การดื่มแอลกอฮอล์</h3>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <label class="fw-bold mb-4 text-dark d-block">ท่านดื่มเครื่องดื่มแอลกอฮอล์หรือไม่</label>

        <div class="alcohol-section">
            <?= $form->field($model, 'data_json[alcohol_status]')->radioList($alcoholOptions, [
                'item' => function ($index, $label, $name, $checked, $value) use ($model) {
                    $id = 'alcohol_' . $value;
                    $content = '<div class="mb-3">';
                    $content .= '<div class="form-check form-check-inline">';
                    $content .= Html::radio($name, $checked, ['value' => $value, 'class' => 'form-check-input', 'id' => $id]);
                    $content .= Html::label($label, $id, ['class' => 'form-check-label fw-medium ms-2']);
                    $content .= '</div>';

                    // แสดงช่องกรอกข้อมูลเพิ่มเติมเฉพาะแถว "ดื่ม"
                    if ($value === 'drink') {
                        $content .= '<div class="d-inline-flex align-items-center gap-3 ms-md-4 mt-2 mt-md-0 ps-4 border-start">';
                        $content .= '<span>จำนวน</span>';
                        $content .= Html::activeTextInput($model, 'data_json[alcohol_qty]', [
                            'class' => 'form-control form-control-sm text-center', 
                            'style' => 'width: 70px;',
                            'placeholder' => '0'
                        ]);
                        $content .= '<span>ครั้ง/สัปดาห์</span>';
                        $content .= '</div>';
                    }
                    $content .= '</div>';
                    return $content;
                }
            ])->label(false) ?>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 12px; }
    .form-check-input { width: 1.2rem; height: 1.2rem; cursor: pointer; }
    .form-check-label { cursor: pointer; }
    .form-control-sm { 
        border: 1px solid #ced4da; 
        border-radius: 4px;
        background-color: #fff !important;
    }
    .form-control-sm:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    /* ปรับแต่งเส้นแบ่งข้าง Input เมื่อเลือกสูบ/ดื่ม */
    .border-start {
        border-left: 3px solid #dee2e6 !important;
    }
</style>



<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// ตัวเลือกสำหรับการออกกำลังกาย
$exerciseOptions = [
    'everyday' => 'ออกกำลังทุกวัน ครั้งละ 30 นาที',
    '3_times_week' => 'ออกกำลังกายสัปดาห์ละ 3 ครั้ง ครั้งละ 30 นาที',
    'less_than_3' => 'ออกน้อยกว่าสัปดาห์ละ 3 ครั้ง',
    'none' => 'ไม่ออกกำลังกาย'
];
?>

<div class="mb-4 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 5: การออกกำลังกาย</h3>
    <p class="text-muted border-start border-4 border-success ps-3">กรุณาระบุความถี่ในการออกกำลังกายของท่าน</p>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <label class="fw-bold mb-4 text-dark d-block">พฤติกรรมการออกกำลังกาย</label>

        <div class="exercise-section">
            <?= $form->field($model, 'data_json[exercise_status]')->radioList($exerciseOptions, [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $id = 'exercise_' . $value;
                    $activeClass = $checked ? 'bg-light border-success' : '';
                    
                    $content = '<div class="form-check custom-exercise-item p-3 mb-2 border rounded-3 ' . $activeClass . '">';
                    $content .= Html::radio($name, $checked, [
                        'value' => $value,
                        'class' => 'form-check-input',
                        'id' => $id
                    ]);
                    $content .= Html::label($label, $id, ['class' => 'form-check-label fw-medium ms-2 w-100']);
                    $content .= '</div>';
                    
                    return $content;
                }
            ])->label(false) ?>
        </div>
    </div>
</div>

<style>
    /* สไตล์สำหรับส่วนการออกกำลังกาย */
    .custom-exercise-item {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        border: 1px solid #f0f0f0 !important;
    }
    .custom-exercise-item:hover {
        background-color: #f8fff9;
        border-color: #198754 !important;
    }
    .custom-exercise-item .form-check-input:checked + .form-check-label {
        color: #198754;
        font-weight: 600 !important;
    }
    /* ปรับแต่งปุ่ม Radio สีเขียวให้เข้ากับธีมการออกกำลังกาย */
    .custom-exercise-item .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
</style>


<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// ตัวเลือกสำหรับรสอาหารที่ชอบ (อ้างอิงตามรูปภาพที่ 6)
$foodTasteOptions = [
    'sweet' => 'หวาน',
    'salty' => 'เค็ม',
    'fatty' => 'มัน',
    'sour' => 'เปรี้ยว',
    'none' => 'ไม่ชอบทุกข้อ'
];
?>

<div class="mb-4 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 6: รสอาหารที่ชอบ</h3>
    <p class="text-muted border-start border-4 border-warning ps-3">กรุณาระบุรสชาติอาหารที่ท่านชอบรับประทานเป็นปกติ</p>
</div>

<div class="card border-0 shadow-sm mb-5">
    <div class="card-body p-4 p-md-5">
        <label class="fw-bold mb-4 text-dark d-block">รสอาหารที่ชอบ</label>

        <div class="food-taste-section">
            <div class="row">
                <?= $form->field($model, 'data_json[food_taste]')->radioList($foodTasteOptions, [
                    'item' => function ($index, $label, $name, $checked, $value) {
                        $id = 'taste_' . $value;
                        // จัด Layout ให้เป็นรายการแนวตั้งตามรูปภาพต้นฉบับ
                        return '<div class="col-12 mb-3">' .
                                    '<div class="form-check custom-taste-item">' .
                                        Html::radio($name, $checked, [
                                            'value' => $value,
                                            'class' => 'form-check-input',
                                            'id' => $id
                                        ]) .
                                        Html::label($label, $id, ['class' => 'form-check-label fw-medium ms-2']) .
                                    '</div>' .
                               '</div>';
                    },
                    'class' => 'row g-0' // ลบช่องว่างส่วนเกินของ row ภายใน radioList
                ])->label(false) ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* สไตล์สำหรับการเลือกพฤติกรรมการกิน */
    .custom-taste-item {
        padding: 10px 15px;
        border-radius: 8px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .custom-taste-item:hover {
        background-color: #fff9e6; /* สีเหลืองจางๆ ตามธีมรสอาหาร */
        border-color: #ffc107;
    }
    .custom-taste-item .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }
    .custom-taste-item .form-check-input:checked {
        background-color: #ffc107;
        border-color: #ffc107;
    }
    .custom-taste-item .form-check-label {
        cursor: pointer;
    }
</style>

<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// ตัวเลือกสำหรับการขับขี่หรือโดยสาร (อ้างอิงตามรูปภาพที่ 7)
$drivingOptions = [
    'none' => 'ไม่ขับขี่ไม่โดยสาร',
    'always' => 'ขับขี่/โดยสาร และใส่หมวกกันน็อก/คาดเข็มขัดนิรภัยทุกครั้ง',
    'sometimes' => 'ขับขี่/โดยสาร และใส่หมวกกันน็อก/คาดเข็มขัดนิรภัยบางครั้ง',
    'rarely' => 'ขับขี่/โดยสาร และใส่หมวกกันน็อก/คาดเข็มขัดนิรภัยนาน ๆ ครั้ง (ใส่เฉพาะเมื่อมีด่านตรวจ)'
];
?>

<div class="mb-4 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 7: พฤติกรรมการขับขี่หรือโดยสาร</h3>
    <p class="text-muted border-start border-4 border-primary ps-3">กรุณาระบุพฤติกรรมการใช้อุปกรณ์นิรภัยขณะเดินทาง</p>
</div>

<div class="card border-0 shadow-sm mb-5">
    <div class="card-body p-4 p-md-5">
        <label class="fw-bold mb-4 text-dark d-block">ท่านขับขี่หรือโดยสารรถจักรยานยนต์/รถยนต์หรือไม่</label>

        <div class="driving-safety-section">
            <?= $form->field($model, 'data_json[driving_safety]')->radioList($drivingOptions, [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $id = 'driving_' . $value;
                    // แสดงผลแบบรายการแนวตั้ง (Vertical) ตามต้นฉบับ
                    return '<div class="col-12 mb-3">' .
                                '<div class="form-check custom-driving-item p-2">' .
                                    Html::radio($name, $checked, [
                                        'value' => $value,
                                        'class' => 'form-check-input',
                                        'id' => $id
                                    ]) .
                                    Html::label($label, $id, ['class' => 'form-check-label fw-medium ms-2']) .
                                '</div>' .
                           '</div>';
                },
                'class' => 'row g-0'
            ])->label(false) ?>
        </div>
    </div>
</div>

<style>
    /* สไตล์สำหรับการเลือกพฤติกรรมการขับขี่ */
    .custom-driving-item {
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .custom-driving-item:hover {
        background-color: #f0f7ff; /* สีฟ้าจางๆ ตามธีมความปลอดภัย */
        cursor: pointer;
    }
    .custom-driving-item .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }
    .custom-driving-item .form-check-label {
        cursor: pointer;
        line-height: 1.5;
    }
</style>


<?php

/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// ตัวเลือกสำหรับการใช้ถุงยางอนามัย (อ้างอิงตามรูปภาพที่ 8)
$condomOptions = [
    'always' => 'ใช้ทุกครั้ง',
    'requested' => 'ใช้เมื่อถูกร้องขอ',
    'none' => 'ไม่ใช้',
    'no_extramarital' => 'ไม่เคยมีเพศสัมพันธ์กับผู้ที่ไม่ใช่สามีหรือภรรยาของตนเอง',
    'no_answer' => 'ไม่ตอบ'
];
?>

<div class="mb-4 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 8: พฤติกรรมทางเพศสัมพันธ์</h3>
    <p class="text-muted border-start border-4 border-danger ps-3">ข้อมูลนี้ใช้เพื่อการประเมินความเสี่ยงทางสุขภาพ โปรดเลือกตามความเป็นจริง</p>
</div>

<div class="card border-0 shadow-sm mb-5">
    <div class="card-body p-4 p-md-5">
        <label class="fw-bold mb-4 text-dark d-block">เมื่อมีเพศสัมพันธ์กับผู้ที่ไม่ใช่สามีหรือภรรยาของท่าน ท่านหรือคู่ของท่าน ใช้ถุงยางอนามัยหรือไม่</label>

        <div class="sexual-health-section">
            <?= $form->field($model, 'data_json[condom_usage]')->radioList($condomOptions, [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $id = 'condom_' . $value;
                    return '<div class="col-12 mb-3">' .
                                '<div class="form-check custom-sex-item p-1">' .
                                    Html::radio($name, $checked, [
                                        'value' => $value,
                                        'class' => 'form-check-input',
                                        'id' => $id
                                    ]) .
                                    Html::label($label, $id, ['class' => 'form-check-label fw-medium ms-2']) .
                                '</div>' .
                           '</div>';
                },
                'class' => 'row g-0'
            ])->label(false) ?>
        </div>
    </div>
</div>

<style>
    /* สไตล์สำหรับการเลือกพฤติกรรมทางเพศ */
    .custom-sex-item {
        border-radius: 6px;
        transition: background-color 0.2s;
    }
    .custom-sex-item:hover {
        background-color: #fff5f5; /* สีชมพู/แดงจางๆ ตามธีมสุขภาพทางเพศ */
        cursor: pointer;
    }
    .custom-sex-item .form-check-input {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }
    /* เปลี่ยนสี radio เมื่อเลือกเป็นโทนสีแดง/ชมพูเพื่อให้เข้ากับส่วนที่ 8 */
    .custom-sex-item .form-check-input:checked {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .custom-sex-item .form-check-label {
        cursor: pointer;
        line-height: 1.6;
    }
</style>

<?php
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model app\models\HealthCheckForm */

// รายการตรวจสุขภาพจากรูปภาพที่ 9
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

$totalPrice = 682.00;
?>

<div class="mb-4 mt-5">
    <h3 class="fw-bold text-dark mb-2">ส่วนที่ 9: รายการตรวจสุขภาพ</h3>
    <p class="text-muted border-start border-4 border-dark ps-3">สรุปรายการตรวจและประมาณการค่าใช้จ่าย</p>
</div>

<div class="card border-0 shadow-sm mb-5 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-warning bg-opacity-10">
                <tr>
                    <th class="py-3 px-4 text-center" style="width: 50px;">เลือก</th>
                    <th class="py-3" style="width: 120px;">รหัสรายการ</th>
                    <th class="py-3">รายการ</th>
                    <th class="py-3 text-center">จำนวน</th>
                    <th class="py-3 text-end">ราคาต่อหน่วย</th>
                    <th class="py-3 text-end px-4">ราคารวม</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checkupItems as $item): ?>
                <tr>
                    <td class="text-center px-4">
                        <?= $form->field($model, "data_json[checkup_list][{$item['code']}]")->checkbox([
                            'label' => false,
                            'value' => 1,
                            'uncheck' => 0,
                            'class' => 'form-check-input checkup-item',
                            'data-price' => $item['price'],
                            'checked' => true // ตั้งค่าเริ่มต้นให้เลือกทุกรายการตามรูปภาพ
                        ])->label(false) ?>
                    </td>
                    <td class="fw-bold text-muted small"><?= $item['code'] ?></td>
                    <td><?= $item['name'] ?></td>
                    <td class="text-center">1</td>
                    <td class="text-end text-secondary"><?= number_format($item['price'], 2) ?></td>
                    <td class="text-end px-4 fw-bold"><?= number_format($item['price'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-light fw-bold">
                <tr>
                    <td colspan="5" class="text-center py-3 px-4">รวมเป็นเงิน</td>
                    <td class="text-end px-4 py-3 text-primary h5 mb-0">
                        <span id="total-price-display"><?= number_format($totalPrice, 2) ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

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
$this->registerJs(<<<JS
    $('.checkup-item').on('change', function() {
        let total = 0;
        $('.checkup-item:checked').each(function() {
            total += parseFloat($(this).data('price'));
        });
        $('#total-price-display').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
    });
JS
);
?>


<?php ActiveForm::end(); ?>