<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;


$this->title = 'การตรวจร่างกาย';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health']];
$this->params['breadcrumbs'][] = $this->title;
/* @var $this yii\web\View */
/* @var $model app\models\HealthScreen */
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'list'])
?>
<?php $this->endBlock(); ?>


<div class="physical-exam-form container-fluid py-3">
    <?= $this->render('patient_profile', ['model' => $model]) ?>


    <style>
        /* CSS ปรับแต่งให้ดูแพงและสะอาด */
        .bg-soft-success {
            background-color: #e8f5e9;
        }

        .tracking-tighter {
            letter-spacing: 0.5px;
            font-weight: 800;
        }

        .vitals-card {
            min-width: 90px;
        }

        .patient-info-banner .card {
            border: 1px solid #eef2f6 !important;
        }

        /* ปรับฟอนต์ให้ดูเป็นโปรแกรมระบบ */
        .patient-info-banner h3 {
            font-family: 'Inter', 'Sarabun', sans-serif;
        }

        /* เพิ่มลูกเล่นตอน Hover เบาๆ */
        .vitals-card:hover {
            background-color: #fff !important;
            border-color: #0d6efd !important;
            transition: all 0.3s ease;
        }
    </style>

    <?php $form = ActiveForm::begin(['id' => 'physical-exam-form']); ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="row g-3 mb-4 border-bottom pb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">ความดันโลหิต ครั้งที่ 1</label>
                    <div class="input-group">
                        <?= Html::activeTextInput($model, 'data_json[bp_1_sys]', ['class' => 'form-control ', 'placeholder' => 'SYS']) ?>
                        <span class="input-group-text">/</span>
                        <?= Html::activeTextInput($model, 'data_json[bp_1_dia]', ['class' => 'form-control ', 'placeholder' => 'DIA']) ?>
                        <span class="input-group-text">mmHg</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">ความดันโลหิต ครั้งที่ 2</label>
                    <div class="input-group">
                        <?= Html::activeTextInput($model, 'data_json[bp_2_sys]', ['class' => 'form-control ', 'placeholder' => 'SYS']) ?>
                        <span class="input-group-text">/</span>
                        <?= Html::activeTextInput($model, 'data_json[bp_2_dia]', ['class' => 'form-control ', 'placeholder' => 'DIA']) ?>
                        <span class="input-group-text">mmHg</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">รอบเอว</label>
                    <div class="input-group">
                        <?= Html::activeTextInput($model, 'data_json[waistline]', ['class' => 'form-control ']) ?>
                        <span class="input-group-text">ซม.</span>
                        <div class="ms-3 pt-1">
                            <?= Html::activeRadioList($model, 'data_json[waist_status]', ['normal' => 'ปกติ', 'over' => 'เกิน'], ['inline' => true]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4 border-bottom pb-4">
                <div class="col-md-6 border-end">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="erp-icon-box">
                            <i data-lucide="flask-conical"></i>
                        </div>
                        <h6 class="text-dark">การตรวจระดับน้ำตาลในเลือด</h6>
                    </div>
                    <?php
                    $lab_fields = [
                        'fbs' => 'FBS',
                        'fbs_post' => 'Post-prandial blood sugar',
                        'capillary_blood' => 'Capillary Blood',
                        'chol' => 'CHOL',
                        'tg' => 'TG',
                        'hdl' => 'HDL',
                        'ldl' => 'LDL'
                    ];
                    foreach ($lab_fields as $key => $label): ?>
                        <div class="row mb-2">
                            <div class="col-md-6 pt-1"><?= $label ?></div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <?= Html::activeTextInput($model, "data_json[lab_val][$key]", ['class' => 'form-control']) ?>
                                    <span class="input-group-text"><?= in_array($key, ['chol', 'tg', 'hdl', 'ldl', 'fbs', 'fbs_post']) ? 'mg%' : 'mg/dl' ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="erp-icon-box">
                            <i data-lucide="user-round-check"></i>
                        </div>
                        <h6 class="text-dark">การตรวจร่างกายทั่วไป</h6>
                    </div>
                    <table class="table table-sm table-borderless ms-5" style="table-layout: fixed; width: calc(100% - 3rem);">
                        <tbody>
                            <?php
                            $pe_items = ['GA' => 'GA', 'HEENT' => 'HEENT', 'HEART' => 'HEART', 'LUNG' => 'LUNG', 'ABD' => 'ABD', 'EXT' => 'EXT', 'NEURO' => 'NEURO', 'BREAST' => 'BREAST', 'other' => 'OTHER'];
                            foreach ($pe_items as $key => $label): ?>
                                <tr>
                                    <td class="fw-bold" style="width: 80px;"><?= $label ?></td>
                                    <td style="width: 150px;">
                                        <?= Html::activeRadioList($model, "data_json[pe_result][$key]", ['normal' => 'ปกติ', 'abnormal' => 'ผิดปกติ'], ['inline' => true, 'class' => '']) ?>
                                    </td>
                                    <td>
                                        <?php if ($key === 'HEENT'): ?>
                                            <?php // กรณี HEENT ให้แสดงเป็น Dropdown 
                                            ?>
                                            <?= Html::activeDropDownList(
                                                $model,
                                                "data_json[pe_detail][$key]",
                                                [
                                                    '' => 'เลือกรายละเอียด...',
                                                    'PALE' => 'PALE',
                                                    'LN' => 'LN',
                                                    'THYROID' => 'THYROID'
                                                ],
                                                ['class' => 'form-select']
                                            ) ?>
                                        <?php else: ?>
                                            <?php // กรณีอื่นๆ ให้แสดงเป็น TextInput ตามเดิม 
                                            ?>
                                            <?= Html::activeTextInput($model, "data_json[pe_detail][$key]", ['class' => 'form-control', 'placeholder' => 'รายละเอียด...']) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6 border-end">
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="erp-icon-box">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <h6 class="text-dark m-0">ความเห็นของแพทย์</h6>
                        </div>

                        <div class="ps-4"> <?= Html::activeRadioList($model, 'data_json[history_status]', ['no' => 'ปกติ/ไม่มี', 'yes' => 'มีโรคประจำตัว'], ['class' => 'mb-2']) ?>


                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="erp-icon-box">
                            <i data-lucide="file-check"></i>
                        </div>
                        <h6 class="text-dark m-0">ระบุโรค</h6>
                    </div>

                    <div class="ps-4 mb-3">
                        <div class="row g-0">
                            <div class="col-11"> 
                                <?= Html::activeCheckboxList($model, 'data_json[history_diseases]', [
                                                        'DM' => 'DM',
                                                        'HT' => 'HT',
                                                        'DLP' => 'DLP',
                                                        'Heart' => 'โรคหัวใจ',
                                                        'Kidney' => 'โรคไต',
                                                        'other' => 'อื่นๆ'
                                                    ], [
                                                        'class' => 'mb-2',
                                                        'item' => function ($index, $label, $name, $checked, $value) {
                                                            $check = $checked ? 'checked' : '';
                                                            return "<div class='form-check form-check-inline'>
                                        <input class='form-check-input' type='checkbox' name='{$name}' value='{$value}' {$check}>
                                        <label class='form-check-label'>{$label}</label>
                                    </div>";
                                                        }
                                                    ]) ?>
                                <?= Html::activeTextInput($model, 'data_json[history_other_detail]', [
                                    'class' => 'form-control form-control-sm',
                                    'placeholder' => 'ระบุโรคอื่นๆ...'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="erp-icon-box">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h6 class="text-dark m-0">คำแนะนำ</h6>
                        </div>

                        <div class="ps-4">
                            <div class="row g-0">
                                <div class="col-11"> <?= Html::activeTextArea($model, 'data_json[advice]', [
                                                            'class' => 'form-control',
                                                            'rows' => 4,
                                                            'placeholder' => '--- ระบุคำแนะนำ ---'
                                                        ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="erp-icon-box">
                            <i data-lucide="clipboard-plus"></i>
                        </div>
                        <h6 class="text-dark">แผนการดูแล</h6>
                    </div>
                    <?= Html::activeCheckboxList($model, 'data_json[management_plan]', [
                        'fbs_6_m'    => 'ไขมันในเลือดสูง และ CVD Risk>10% นัดตรวจ lipid profile 6 เดือน',
                        'lipid_6_m'  => 'FBS สูง เสี่ยงต่อเบาหวาน นัดตรวจ FBS 3-6 เดือน',
                        'pre_ht'     => 'Pre HT นัดตรวจ BP 3 เดือน',
                        'bv_carrier' => 'BV carrier นัดตรวจ ALT',
                        'bmi_25'     => 'BMI>25',
                        'cxr_afb'    => 'CXR ผิดปกติ นัดติดตาม AFB',
                        'cancer_ref' => 'ญาติป่วยเป็นมะเร็งชนิดเกี่ยวข้องพันธุกรรม นัดคัดกรองมะเร็ง',
                        'other'      => 'ผิดปกติอื่น ๆ รอผลตรวจเพิ่มเติมเพื่อยืนยัน',
                    ], [
                        'class' => 'ms-5',
                        'item' => function ($index, $label, $name, $checked, $value) {
                            // ครอบแต่ละรายการด้วย div เพื่อให้ขึ้นบรรทัดใหม่ (แนวตั้ง)
                            $check = $checked ? 'checked' : '';
                            return "<div class='checkbox mt-2'>
                            <label>
                                <input type='checkbox' name='{$name}' value='{$value}' {$check}> {$label}
                            </label>
                        </div>";
                                }
                            ]) ?>


                    <div class="mt-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="erp-icon-box">
                            <i data-lucide="calendar-plus"></i> 
                        </div>
                        <h6 class="text-dark">นัดตรวจครั้งต่อไป</h6>
                    </div>
                        <?= Html::activeRadioList($model, 'data_json[next_appointment]', [
                            '1_w' => '1 สัปดาห์',
                            '1_m' => '1 เดือน',
                            '3_m' => '3 เดือน',
                            '6_m' => '6 เดือน',
                            '1_y' => '1 ปี'
                        ], ['inline' => true,'class' => 'ms-5']) ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-light rounded-3">
                <div class="row align-items-center">
                    <div class="col-md-2 fw-bold">สรุปผลสุขภาพ:</div>
                    <div class="col-md-10 fw-bold text-primary">
                        <?= Html::activeRadioList($model, 'data_json[final_summary]', [
                            'healthy' => 'สุขภาพดี (ปกติ)',
                            'risk' => 'กลุ่มเสี่ยง',
                            'sick' => 'กลุ่มป่วย'
                        ], ['inline' => true]) ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer bg-white py-3 border-top d-flex justify-content-between">
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light px-4']) ?>
            <?= Html::submitButton('<i class="fas fa-save me-1"></i> บันทึกข้อมูลและยืนยันการตรวจ', ['class' => 'btn btn-success px-5 shadow-sm rounded-pill']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
    . {
        font-size: 0.8rem;
        border-radius: 4px;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .table-sm td {
        padding: 4px 0;
        border: none;
    }

    .pe-radio-group label,
    .small label {
        font-size: 0.85rem !important;
        margin-right: 10px;
        cursor: pointer;
    }

    .card {
        border: 1px solid rgba(0, 0, 0, .05);
    }
</style>