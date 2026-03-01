<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\components\CategoriseHelper;
use app\modules\hr\models\Employees;
use app\widgets\datepicker\DatepickerThai;

/** @var yii\web\View $this */
/** @var app\modules\development\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
$emp = UserHelper::GetEmployee();
$listDocumentMe = $emp ? $emp->listDocumentMe() : [];
$isNewRecord = $model->isNewRecord;
$formAction = $isNewRecord
    ? Url::to(['/development/default/create'])
    : Url::to(['/development/default/update', 'id' => $model->id]);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-development',
    'action' => $formAction,
    'options' => ['class' => 'development-form'],
    'fieldConfig' => [
        'labelOptions' => ['class' => 'form-label fw-medium text-body'],
        'inputOptions' => ['class' => 'form-control'],
        'errorOptions' => ['class' => 'invalid-feedback'],
    ],
]); ?>

<!-- 1. ข้อมูลเอกสาร -->
<div class="rounded-3 shadow-sm overflow-hidden mb-3 mb-md-4">
    <div class="bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-2 py-sm-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-primary d-flex align-items-center small">
                <span class="badge rounded-circle bg-primary text-white me-2">1</span>
                <i class="bi bi-file-earmark-text me-2"></i>ข้อมูลเอกสาร
            </h6>
            <span class="text-primary text-opacity-75 small d-none d-sm-inline">ขั้นที่ 1/4</span>
        </div>
    </div>
    <div class="p-3 p-sm-4">
        <div class="row g-2 g-md-3">
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'thai_year') ?>
                <?= $form->field($model, 'data_json[doc_number]')->label('เลขที่หนังสือ') ?>
            </div>
            <div class="col-12 col-md-9">
                <?php
                echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map($listDocumentMe, 'id', 'topic'),
                    'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
                    'pluginOptions' => ['allowClear' => true],
                    'pluginEvents' => [
                        'select2:select' => new JsExpression("function(e) {
                            var data = e.params.data;
                            $('#development-topic').val(data.text);
                        }"),
                    ],
                ])->label('หนังสืออ้างอิง');
                ?>
            </div>
        </div>
    </div>
</div>

<!-- 2. รายละเอียดการพัฒนา -->
<div class="rounded-3 shadow-sm overflow-hidden mb-3 mb-md-4">
    <div class="bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-2 py-sm-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-primary d-flex align-items-center small">
                <span class="badge rounded-circle bg-primary text-white me-2">2</span>
                <i class="bi bi-info-circle me-2"></i>รายละเอียดการพัฒนา
            </h6>
            <span class="text-primary text-opacity-75 small d-none d-sm-inline">ขั้นที่ 2/4</span>
        </div>
    </div>
    <div class="p-3 p-sm-4">
        <div class="row g-2 g-md-3">
            <div class="col-12">
                <?= $form->field($model, 'topic')->textInput(['maxlength' => true, 'placeholder' => 'ระบุหัวข้อการอบรม/ประชุม/ดูงาน']) ?>
            </div>
            <div class="col-12 col-md-6">
                <div class="border border-secondary border-opacity-25 rounded-2 p-2 p-md-3">
                <div class="row g-2 g-md-3">
                    <div class="col-12 col-sm-6">
                        <?= $form->field($model, 'date_start')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'วว/ดด/ปปปป']]) ?>
                    </div>
                    <div class="col-12 col-sm-6">
                        <?= $form->field($model, 'date_end')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'วว/ดด/ปปปป']]) ?>
                    </div>
                </div>
                <div>
                    <?php
                    echo $form->field($model, 'development_type_id')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentType(),
                        'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ประเภทการพัฒนา');
                    ?>
                </div>
                <div>
                    <?php
                    echo $form->field($model, 'data_json[development_level_name]')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentLevel(true),
                        'options' => ['placeholder' => 'เลือกระดับการพัฒนา'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ระดับการพัฒนา');
                    ?>
                </div>
                <div>
                    <?php
                    echo $form->field($model, 'data_json[time_slot]')->widget(Select2::classname(), [
                        'data' => [
                            'เต็มวัน' => 'เต็มวัน',
                            'ครั้งวันเช้า' => 'ครั้งวันเช้า',
                            'ครั้งวันบ่าย' => 'ครั้งวันบ่าย',
                        ],
                        'options' => ['placeholder' => 'เลือกช่วงเวลา'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ช่วงเวลา');
                    ?>
                </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="border border-secondary border-opacity-25 rounded-2 p-2 p-md-3">
                <div>
                    <?php
                    echo $form->field($model, 'data_json[development_go_type_name]')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentGoType(true),
                        'options' => ['placeholder' => 'เลือกลักษณะ'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ลักษณะการเข้าร่วม');
                    ?>
                </div>
                <div>
                    <?php
                    echo $form->field($model, 'data_json[claim_type_name]')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentClaimType(true),
                        'options' => ['placeholder' => 'เลือกการเบิกเงิน'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('การเบิกเงิน');
                    ?>
                </div>
                <?php
                $url = Url::to(['/depdrop/employee-by-id']);
                $initEmployee = empty($model->leader_id) ? '' : (Employees::findOne($model->leader_id) ? Employees::findOne($model->leader_id)->getAvatar(false) : '');
                echo $form->field($model, 'leader_id')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 1,
                        'ajax' => [
                            'url' => $url,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term};}'),
                        ],
                        'escapeMarkup' => new JsExpression('function(markup) { return markup;}'),
                        'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                        'templateSelection' => new JsExpression('function(emp) {return emp.text;}'),
                    ],
                ])->label('หัวหน้างาน');
                ?>
                <?php
                $initEmployee = empty($model->leader_group_id) ? '' : (Employees::findOne($model->leader_group_id) ? Employees::findOne($model->leader_group_id)->getAvatar(false) : '');
                echo $form->field($model, 'leader_group_id')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 1,
                        'ajax' => [
                            'url' => $url,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term};}'),
                        ],
                        'escapeMarkup' => new JsExpression('function(markup) { return markup;}'),
                        'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                        'templateSelection' => new JsExpression('function(emp) {return emp.text;}'),
                    ],
                ])->label('หัวหน้ากลุ่มงาน');
                ?>
                <div class="avatar-form">
                    <?php
                    $initEmployeeAssignedTo = empty($model->assigned_to) ? '' : (Employees::findOne($model->assigned_to) ? Employees::findOne($model->assigned_to)->getAvatar(false) : '');
                    echo $form->field($model, 'assigned_to')->widget(Select2::classname(), [
                        'initValueText' => $initEmployeeAssignedTo,
                        'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
                            'ajax' => [
                                'url' => $url,
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term};}'),
                            ],
                            'escapeMarkup' => new JsExpression('function(markup) { return markup;}'),
                            'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                            'templateSelection' => new JsExpression('function(emp) {return emp.text;}'),
                        ],
                    ])->label('ผู้ปฏิบัติหน้าที่แทน');
                    ?>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. สถานที่และหน่วยงาน -->
<div class="rounded-3 shadow-sm overflow-hidden mb-3 mb-md-4">
    <div class="bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-2 py-sm-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-primary d-flex align-items-center small">
                <span class="badge rounded-circle bg-primary text-white me-2">3</span>
                <i class="bi bi-geo-alt me-2"></i>สถานที่และหน่วยงาน
            </h6>
            <span class="text-primary text-opacity-75 small d-none d-sm-inline">ขั้นที่ 3/4</span>
        </div>
    </div>
    <div class="p-3 p-sm-4">
        <div class="row g-2 g-md-3">
            <div class="col-12 col-md-6">
                <?php
                echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                    'data' => CategoriseHelper::ListLocationOrg(true),
                    'options' => ['placeholder' => 'เลือกสถานที่'],
                    'pluginOptions' => ['tags' => true, 'allowClear' => true],
                ])->label('สถานที่จัดงาน');
                ?>
                <?php
                echo $form->field($model, 'data_json[province_name]')->widget(Select2::classname(), [
                    'data' => CategoriseHelper::ListProvinceName(true),
                    'options' => ['placeholder' => 'เลือกจังหวัด'],
                    'pluginOptions' => [],
                ])->label('จังหวัด');
                ?>
            </div>
            <div class="col-12 col-md-6">
                <?php
                echo $form->field($model, 'data_json[location_org]')->widget(Select2::classname(), [
                    'data' => CategoriseHelper::ListLocationOrg(true),
                    'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                    'pluginOptions' => ['tags' => true, 'allowClear' => true],
                ])->label('หน่วยงานที่จัด');
                ?>
                <?= $form->field($model, 'data_json[location_org_type]')->radioList([
                    'ในจังหวัด' => 'ในจังหวัด',
                    'ต่างจังหวัด' => 'ต่างจังหวัด',
                    'ต่างประเทศ' => 'ต่างประเทศ',
                ], [
                    'class' => 'd-flex flex-wrap gap-2 gap-sm-3',
                    'item' => function ($index, $label, $name, $checked, $value) {
                        $checked = $checked ? 'checked' : '';
                        return "<div class='form-check form-check-inline mb-0'>
                            <input class='form-check-input' type='radio' name='{$name}' id='{$index}' value='{$value}' {$checked}>
                            <label class='form-check-label' for='{$index}'>{$label}</label>
                        </div>";
                    }
                ])->label('ประเภทสถานที่');
                ?>
            </div>
        </div>
    </div>
</div>

<!-- 4. ข้อมูลการเดินทาง -->
<div class="rounded-3 shadow-sm overflow-hidden mb-3 mb-md-4">
    <div class="bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-2 py-sm-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-primary d-flex align-items-center small">
                <span class="badge rounded-circle bg-primary text-white me-2">4</span>
                <i class="bi bi-car-front me-2"></i>ข้อมูลการเดินทาง
            </h6>
            <span class="text-primary text-opacity-75 small d-none d-sm-inline">ขั้นที่ 4/4</span>
        </div>
    </div>
    <div class="p-3 p-sm-4">
        <div class="row g-2 g-md-3">
            <div class="col-12 col-md-6">
                <div class="row g-2 g-md-3">
                    <div class="col-12 col-sm-8 col-md-8">
                        <?= $form->field($model, 'vehicle_date_start')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'วว/ดด/ปปปป']])->label('วันไป') ?>
                    </div>
                    <div class="col-12 col-sm-4 col-md-4">
                        <?= $form->field($model, 'data_json[vehicle_time_start]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99', 'options' => ['class' => 'form-control']])->label('เวลา') ?>
                    </div>
                </div>
                <div class="row g-2 g-md-3">
                    <div class="col-12 col-sm-8 col-md-8">
                        <?= $form->field($model, 'vehicle_date_end')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'วว/ดด/ปปปป']])->label('วันกลับ') ?>
                    </div>
                    <div class="col-12 col-sm-4 col-md-4">
                        <?= $form->field($model, 'data_json[vehicle_time_end]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99', 'options' => ['class' => 'form-control']])->label('เวลา') ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="row g-2 g-md-3">
                    <div class="col-12 col-sm-6">
                        <?php
                        echo $form->field($model, 'vehicle_type_id')->widget(Select2::classname(), [
                            'data' => $model->ListVehicleType(),
                            'options' => ['placeholder' => 'เลือกพาหนะเดินทาง'],
                            'pluginOptions' => ['allowClear' => true],
                        ])->label('พาหนะเดินทาง');
                        ?>
                    </div>
                    <div class="col-12 col-sm-6">
                        <?= $form->field($model, 'data_json[license_plate]')->textInput(['placeholder' => 'ทะเบียน'])->label('ทะเบียนพาหนะ') ?>
                    </div>
                </div>
                <?= $form->field($model, 'data_json[distance]')->textInput(['placeholder' => 'ระบุระยะทาง (กม.)'])->label('ระยะทาง/กิโลเมตร') ?>
            </div>
        </div>
    </div>
</div>

<!-- ปุ่มดำเนินการ (mobile/tablet: full-width, sticky feel) -->
<div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-between align-items-stretch align-sm-center py-3 px-3 px-sm-4 border-top bg-white shadow-sm rounded-bottom">
    <?= Html::a('<i class="bi bi-chevron-left me-1"></i> ย้อนกลับ', $isNewRecord ? ['/development/default/list', 'thai_year' => $model->thai_year ?: (int) date('Y') + 543] : 'javascript:history.back()', ['class' => 'btn btn-outline-primary rounded-3 px-3 py-2 order-2 order-sm-1']) ?>
    <?= Html::submitButton(($isNewRecord ? 'ยืนยันสร้างบันทึก' : 'บันทึกข้อมูล') . ' <i class="bi bi-chevron-right ms-1"></i>', ['class' => 'btn btn-success rounded-3 px-4 py-2 order-1 order-sm-2', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$('#form-development').on('beforeSubmit', function (e) {
    var form = $(this);
    Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขออบรม/ประชุม/ดูงาน!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        if (typeof closeModal === 'function') closeModal();
                        Swal.fire({
                            title: "สำเร็จ!",
                            text: "บันทึกข้อมูลเรียบร้อยแล้ว",
                            icon: "success",
                            timer: 1000,
                            showConfirmButton: false
                        }).then(function() {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.reload();
                            }
                        });
                    }
                }
            });
        }
    });
    return false;
});
JS;
$this->registerJs($js, View::POS_END);
?>
