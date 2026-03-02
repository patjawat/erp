<?php

use app\modules\health\models\HealthFamilyDisease;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\health\models\HealthScreen */

$tmpData = $model->data_json ?? [];

$smokingStatusOptions = ['smoke' => 'สูบ', 'none' => 'ไม่สูบ', 'quit' => 'เคยสูบแต่เลิกแล้ว'];
$alcoholOptions = ['drink' => 'ดื่ม', 'none' => 'ไม่ดื่ม', 'quit' => 'เคยดื่มแต่เลิกแล้ว'];
$exerciseOptions = [
    'everyday'      => 'ออกกำลังทุกวัน ครั้งละ 30 นาที',
    '3_times_week'  => 'ออกกำลังกายสัปดาห์ละ 3 ครั้ง ครั้งละ 30 นาที',
    'less_than_3'   => 'ออกน้อยกว่าสัปดาห์ละ 3 ครั้ง',
    'none'          => 'ไม่ออกกำลังกาย',
];
$foodTasteOptions = ['sweet' => 'หวาน', 'salty' => 'เค็ม', 'fatty' => 'มัน', 'sour' => 'เปรี้ยว', 'none' => 'ไม่ชอบทุกข้อ'];
$miniSections = [
    ['title' => '1.5. ขับขี่ปลอดภัย', 'key' => 'driving_safety',  'opt' => ['none' => 'ไม่ขับขี่ไม่โดยสาร', 'always' => 'ทุกครั้ง', 'sometimes' => 'บางครั้ง', 'rarely' => 'นานๆ ครั้ง'], 'color' => 'primary'],
    ['title' => '1.6. เพศสัมพันธ์',   'key' => 'condom_usage',    'opt' => ['always' => 'ใช้ทุกครั้ง', 'requested' => 'ใช้เมื่อถูกร้องขอ', 'none' => 'ไม่ใช้', 'no_answer' => 'ไม่ตอบ'], 'color' => 'danger'],
];
// โหลดจาก DB (fallback เป็นค่า hardcode ถ้าตารางยังไม่มี)
$familyDiseaseList = HealthFamilyDisease::getActiveList();
$diseasesYear = [
    'h_diabetes' => 'เบาหวาน',       'h_hypertension' => 'ความดันสูง',
    'h_liver'    => 'โรคตับ',         'h_stroke'       => 'อัมพาต',
    'h_heart'    => 'โรคหัวใจ',       'h_dyslipidemia' => 'ไขมันเลือดผิดปกติ',
    'h_gastric'  => 'แผลในกระเพาะ',  'h_birth'        => 'คลอดบุตร > 4kg',
    'h_thirst'   => 'ดื่มน้ำบ่อย',   'h_nocturia'     => 'ปัสสาวะบ่อยกลางคืน',
    'h_fatigue'  => 'อ่อนเพลีย',     'h_skin_itch'    => 'คันตามผิวหนัง',
    'h_vision'   => 'ตาพร่ามัว',     'h_numbness'     => 'ชาปลายมือเท้า',
    'h_constipation' => 'ท้องผูกเรื้อรัง', 'h_urinary' => 'ฉี่ขัด/ปนเลือด',
];
?>

<?php $form = ActiveForm::begin([
    'id'                    => 'form',
    'type'                  => ActiveForm::TYPE_VERTICAL,
    'enableClientValidation' => true,
    'enableAjaxValidation'  => true,
    'validationUrl'         => ['/health/health-screen/validator'],
]); ?>

<div class="health-form-container p-3">

    <?php if ($model->isNewRecord): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary-gradient text-white py-2">
                <h6 class="mb-0 text-white small fw-normal"><i class="fas fa-user-circle me-1"></i> เลือกพนักงาน</h6>
            </div>
            <div class="card-body">
                <?php echo $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => true]) ?>
            </div>
        </div>
    <?php else: ?>
        <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <?= $form->field($model, 'bmi')->hiddenInput()->label(false) ?>

    <div class="d-flex align-items-center gap-3 mb-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-id-card me-2"></i>หมวดที่ 1: ข้อมูลทั่วไป</h5>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6"><?= $form->field($model, 'thai_year')->textInput(['placeholder' => 'ระบุปีที่ตรวจ']) ?></div>
        <div class="col-md-6"><?= $form->field($model, 'date_checkup')->textInput(['readonly' => true]) ?></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-top border-4 border-danger h-100">
                <div class="card-body">
                    <h6 class="fw-bold">1.1. บุหรี่</h6>
                    <?= $form->field($model, 'data_json[smoking_status]', [
                        'enableAjaxValidation' => true,
                        'template'     => "{input}\n{error}",
                        'options'      => ['class' => 'form-group mb-0'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'],
                    ])->radioList($smokingStatusOptions, ['inline' => true])->label(false) ?>
                    <div class="p-2 rounded d-flex gap-2 align-items-center">
                        <span>จำนวน</span>
                        <?= Html::activeTextInput($model, 'data_json[smoke_qty]', ['class' => 'form-control w-25']) ?>
                        <span>มวน/วัน</span>
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
                        'template'     => "{input}\n{error}",
                        'options'      => ['class' => 'form-group mb-0'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'],
                    ])->radioList($alcoholOptions, ['inline' => true])->label(false) ?>
                    <div class="p-2 rounded d-flex gap-2 align-items-center">
                        <span>ความถี่</span>
                        <?= Html::activeTextInput($model, 'data_json[alcohol_qty]', ['class' => 'form-control w-25']) ?>
                        <span>ครั้ง/สัปดาห์</span>
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
                        'template'     => "{input}\n{error}",
                        'options'      => ['class' => 'form-group mb-0'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'],
                    ])->dropDownList($exerciseOptions, ['prompt' => '-- เลือก --', 'class' => 'form-select mt-3'])->label(false) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        <!-- 1.4 รสอาหาร — เลือกได้หลายอย่าง -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-top border-4 border-warning h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">1.4. รสอาหาร</h6>
                    <small class="text-muted d-block mb-3">เลือกได้มากกว่า 1 อย่าง</small>
                    <?php
                    $currentFoodTaste = is_array($tmpData['food_taste'] ?? null)
                        ? $tmpData['food_taste']
                        : (isset($tmpData['food_taste']) && $tmpData['food_taste'] !== '' ? [$tmpData['food_taste']] : []);
                    ?>
                    <?= $form->field($model, 'data_json[food_taste]', [
                        'template'     => "{input}\n{error}",
                        'options'      => ['class' => 'mb-0'],
                        'errorOptions' => ['class' => 'invalid-feedback d-block'],
                    ])->checkboxList($foodTasteOptions, [
                        'item' => function ($index, $label, $name, $checked, $value) use ($currentFoodTaste) {
                            $isChecked  = in_array($value, $currentFoodTaste, true) ? ' checked' : '';
                            $isNone     = $value === 'none';
                            $btnClass   = $isNone ? 'btn-outline-secondary' : 'btn-outline-warning';
                            $extraClass = $isNone ? ' ft-none' : ' ft-item';
                            $id = 'ft_' . $value;
                            return "<input type='checkbox' class='btn-check food-taste-cb{$extraClass}'"
                                 . " id='{$id}' name='{$name}' value='{$value}' autocomplete='off'{$isChecked}>"
                                 . "<label class='btn {$btnClass} rounded-pill' for='{$id}'>{$label}</label>";
                        },
                        'separator' => ' ',
                        'tag'       => 'div',
                        'class'     => 'd-flex flex-wrap gap-2',
                    ])->label(false) ?>
                </div>
            </div>
        </div>

        <?php foreach ($miniSections as $sec): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-top border-4 border-<?= $sec['color'] ?> h-100">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><?= $sec['title'] ?></h6>
                        <?= $form->field($model, "data_json[{$sec['key']}]", [
                            'enableAjaxValidation' => true,
                            'template'     => "{input}\n{error}",
                            'options'      => ['class' => 'form-group mb-0'],
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ])->dropDownList($sec['opt'], [
                            'prompt' => '-- เลือก --',
                            'class'  => 'form-select',
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
                'template'     => "{label}\n{input}\n{error}",
                'errorOptions' => ['class' => 'invalid-feedback d-block'],
            ])->textInput([
                'id'          => 'w-in',
                'type'        => 'number',
                'step'        => '0.1',
                'placeholder' => '0.0',
            ])->label('น้ำหนัก (กก.)') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'height', [
                'enableAjaxValidation' => true,
                'template'     => "{label}\n{input}\n{error}",
                'errorOptions' => ['class' => 'invalid-feedback d-block'],
            ])->textInput([
                'id'          => 'h-in',
                'type'        => 'number',
                'step'        => '0.1',
                'placeholder' => '0.0',
            ])->label('ส่วนสูง (ซม.)') ?>
        </div>
        <div class="col-12">
            <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">ดัชนีมวลกาย (BMI)</small>
                    <h3 class="mb-0" id="bmi-val">0.0</h3>
                </div>
                <div class="text-end">
                    <small class="text-muted">ผลประเมิน</small>
                    <h4 class="mb-0 text-primary" id="bmi-status">-</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm border-top border-4 border-primary mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-bold text-primary">ส่วนที่ 3: ประวัติการเจ็บป่วยในครอบครัว</h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'data_json[family_history]', [
                'enableAjaxValidation' => true,
                'template'     => "{input}\n{error}",
                'options'      => ['class' => 'form-group mb-0'],
                'errorOptions' => ['class' => 'invalid-feedback d-block px-3'],
            ])->checkboxList($familyDiseaseList, [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $checkedAttr = $checked ? 'checked' : '';
                    return "
                    <div class='col-6 col-md-3 mb-2'>
                        <div class='form-check'>
                            <input type='checkbox' class='form-check-input' name='{$name}' value='{$value}' id='f_{$value}' {$checkedAttr}>
                            <label class='form-check-label fw-normal' for='f_{$value}'>{$label}</label>
                        </div>
                    </div>";
                },
                'class' => 'row g-0 px-3',
            ])->label(false) ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm border-top border-4 border-danger mb-4">
        <div class="card-header bg-white d-flex justify-content-between">
            <h5 class="mb-0 fw-bold text-danger">ส่วนที่ 4: โรคประจำตัว</h5>
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span class="d-flex align-items-center gap-1"><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ไม่มี</span></span>
                <span class="d-flex align-items-center gap-1"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">มี</span></span>
                <span class="d-flex align-items-center gap-1"><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ไม่เคยตรวจ</span></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <?php foreach ($diseasesYear as $attr => $label):
                    if (!isset($tmpData[$attr])) {
                        $tmpData[$attr] = 0;
                    }
                ?>
                    <div class="col-md-6 border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-medium text-dark small"><?= $label ?></span>
                        <?= $form->field($model, "data_json[$attr]", ['options' => ['class' => 'mb-0']])->radioList([0 => '', 1 => '', 2 => ''], [
                            'item' => function ($index, $label, $name, $checked, $value) use ($tmpData, $attr) {
                                $isChecked = ($tmpData[$attr] == $value);
                                $colors = [0 => 'btn-outline-success', 1 => 'btn-outline-danger', 2 => 'btn-outline-secondary'];
                                return Html::radio($name, $isChecked, ['value' => $value, 'class' => 'btn-check', 'id' => "rd_{$name}_{$value}"])
                                    . Html::label('', "rd_{$name}_{$value}", [
                                        'class' => 'btn btn-xs rounded-circle p-0 ms-2 ' . $colors[$value],
                                        'style' => 'width:18px; height:18px;',
                                    ]);
                            },
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
$model->data_json = $tmpData;
ActiveForm::end();
?>

<?php
$this->registerJs(
    <<<JS
thaiDatepicker('#healthscreen-date_checkup');

// รสอาหาร — mutual exclusion กับ "ไม่ชอบทุกข้อ"
$(document).on('change', '.food-taste-cb', function() {
    if ($(this).hasClass('ft-none') && this.checked) {
        $('.food-taste-cb.ft-item').prop('checked', false);
    } else if ($(this).hasClass('ft-item') && this.checked) {
        $('.food-taste-cb.ft-none').prop('checked', false);
    }
});

$('#w-in, #h-in').on('input', function() {
    let w = parseFloat($('#w-in').val());
    let h = parseFloat($('#h-in').val());
    if (w > 0 && h > 0) {
        let hMeter = h / 100;
        let bmi = (w / (hMeter * hMeter)).toFixed(1);
        let label = '', color = '';
        if      (bmi < 18.5) { label = 'น้ำหนักน้อย / ผอม';    color = '#0dcaf0'; }
        else if (bmi < 23)   { label = 'ปกติ (สุขภาพดี)';       color = '#198754'; }
        else if (bmi < 25)   { label = 'ท้วม / เริ่มอ้วน';      color = '#ffc107'; }
        else if (bmi < 30)   { label = 'อ้วนระดับ 1';            color = '#dc3545'; }
        else                 { label = 'อ้วนระดับ 2 (อ้วนมาก)'; color = '#212529'; }
        $('#bmi-val').text(bmi);
        $('#healthscreen-bmi').val(bmi);
        $('#bmi-status').text(label).css('color', color);
    }
});

handleFormSubmit('#form', null, async function(response) {
    await location.reload();
});
JS
);
?>
