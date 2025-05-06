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

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
$emp = UserHelper::GetEmployee();
$listDocumentMe  = $emp->listDocumentMe();

$listDocumentData = ArrayHelper::map($listDocumentMe, 'id', 'topic');
?>



<style>
:not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 2px);
    padding: 4px;
    font-size: 1.0rem;
    line-height: 1.5;
    border-radius: .3rem;
}

.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #e5e5e5;
    color: #000;
}

.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px);
    line-height: 1.5;
    padding: 6px;
}

.avatar-form .avatar {
    height: 1.9rem !important;
    width: 1.9rem !important;
}

.avatar-form .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px);
    line-height: 1.5;
    padding: 0.1rem 0.1rem 0.5rem 0.1rem;
}
</style>

        <?php $form = ActiveForm::begin(['id' => 'form-development']); ?>

        <!-- ข้อมูลอ้างอิงเอกสาร -->
        <div class="card mb-3">
            <div class="card-header bg-light p-2">
                <strong><i class="bi bi-file-earmark-text me-2"></i>ข้อมูลเอกสาร</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <?= $form->field($model, 'thai_year')->textInput(['class' => 'form-control form-control-sm']) ?>
                    </div>
                    <div class="col-md-9 col-sm-12">
                        <?php
                        echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                            'data' => $listDocumentData,
                            'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'dropdownParent' => '#main-modal',
                            ],
                            'pluginEvents' => [
                                'select2:select' =>  new JsExpression("function() {
                                   var data = $(this).select2('data')[0]
                                    $('#development-topic').val(data.text)
                                }"),

                            ]
                        ])->label('หนังสืออ้างอิง');
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายละเอียดการพัฒนา -->
        <div class="card mb-3">
            <div class="card-header bg-light p-2">
                <strong><i class="bi bi-info-circle me-2"></i>รายละเอียดการพัฒนา</strong>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <?= $form->field($model, 'topic')->textInput(['maxlength' => true, 'placeholder' => 'ระบุหัวข้อการอบรม/ประชุม/ดูงาน']) ?>
                    </div>
                </div>

                <div class="row">
                    <!-- คอลัมน์ซ้าย -->
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= $form->field($model, 'date_start')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'วว/ดด/ปปปป']) ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= $form->field($model, 'date_end')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'วว/ดด/ปปปป']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <?php
                            echo $form->field($model, 'data_json[development_type_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentType(true),
                                'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ประเภทการพัฒนา');
                            ?>
                        </div>

                        <div class="form-group mt-2">
                            <?php
                            echo $form->field($model, 'data_json[development_level_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentLevel(true),
                                'options' => ['placeholder' => 'เลือกระดับการพัฒนา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ระดับการพัฒนา');
                            ?>
                        </div>

                        <div class="form-group mt-2">
                            <?php
                            echo $form->field($model, 'data_json[time_slot]')->widget(Select2::classname(), [
                                'data' => [
                                    'เต็มวัน' => 'เต็มวัน',
                                    'ครั้งวันเช้า' => 'ครั้งวันเช้า',
                                    'ครั้งวันบ่าย' => 'ครั้งวันบ่าย',
                                ],
                                'options' => ['placeholder' => 'เลือกช่วงเวลา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ช่วงเวลา');
                            ?>
                        </div>
                    </div>

                    <!-- คอลัมน์ขวา -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php
                            echo $form->field($model, 'data_json[development_go_type_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentGoType(true),
                                'options' => ['placeholder' => 'เลือกลักษณะ'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ลักษณะการเข้าร่วม');
                            ?>
                        </div>

                        <div class="form-group mt-2">
                            <?php
                            echo $form->field($model, 'data_json[claim_type_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentClaimType(true),
                                'options' => ['placeholder' => 'เลือกการเบิกเงิน'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('การเบิกเงิน');
                            ?>
                        </div>

                        <div class="form-group mt-2 avatar-form">


<?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employee = Employees::find()->where(['id' => $model->leader_id])->one();
            $initEmployee = empty($model->leader_id) ? '' : Employees::findOne($model->leader_id)->getAvatar(false);//กำหนดค่าเริ่มต้น
            
            echo $form->field($model,'leader_id')->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                // 'size' => Select2::,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    // 'dropdownParent' => '#main-modal',
                    'width' => '350px',
                    'allowClear'=>true,
                    'minimumInputLength'=>1,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                    'ajax'=>[
                        'url'=>$url,
                        'dataType'=>'json',//รูปแบบการอ่านคือ json
                        'data'=>new JsExpression('function(params) { return {q:params.term};}')
                    ],
                    'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                    'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                    'templateSelection'=>new JsExpression('function(emp) {return emp.text;}'),
                ],

                    ])->label('หัวหน้า');
                    ?>   

                        </div>

                        <div class="form-group mt-2 avatar-form">


<?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employeeAssignedTo = Employees::find()->where(['id' => $model->assigned_to])->one();
            $initEmployeeAssignedTo = empty($model->assigned_to) ? '' : Employees::findOne($model->assigned_to)->getAvatar(false);//กำหนดค่าเริ่มต้น
            
            echo $form->field($model,'assigned_to')->widget(Select2::classname(), [
                'initValueText' => $initEmployeeAssignedTo,
                // 'size' => Select2::,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    // 'dropdownParent' => '#main-modal',
                    'width' => '350px',
                    'allowClear'=>true,
                    'minimumInputLength'=>1,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                    'ajax'=>[
                        'url'=>$url,
                        'dataType'=>'json',//รูปแบบการอ่านคือ json
                        'data'=>new JsExpression('function(params) { return {q:params.term};}')
                    ],
                    'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                    'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                    'templateSelection'=>new JsExpression('function(emp) {return emp.text;}'),
                ],

                    ])->label('ผู้ปฏิบัติหน้าที่แทน');
                    ?>   
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- สถานที่และหน่วยงาน -->
        <div class="card mb-3">
            <div class="card-header bg-light p-2">
                <strong><i class="bi bi-geo-alt me-2"></i>สถานที่และหน่วยงาน</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php
                            echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListLocationOrg(true),
                                'options' => ['placeholder' => 'เลือกสถานที่'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('สถานที่จัดงาน');
                            ?>
                        </div>

                        <div class="form-group mt-2">
                            <?php
                            echo $form->field($model, 'data_json[province_name]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListProvinceName(true),
                                'options' => ['placeholder' => 'เลือกจังหวัด'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                ],
                            ])->label('จังหวัด');
                            ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?php
                            echo $form->field($model, 'data_json[location_org]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListLocationOrg(true),
                                'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('หน่วยงานที่จัด');
                            ?>
                        </div>

                        <div class="form-group mt-2">
                            <?= $form->field($model, 'data_json[location_org_type]')->radioList([
                                'ในจังหวัด' => 'ในจังหวัด',
                                'ต่างจังหวัด' => 'ต่างจังหวัด',
                                'ต่างประเทศ' => 'ต่างประเทศ',
                            ], [
                                'item' => function($index, $label, $name, $checked, $value) {
                                    $checked = $checked ? 'checked' : '';
                                    return "<div class='form-check form-check-inline'>
                                                <input class='form-check-input' type='radio' name='{$name}' id='{$index}' value='{$value}' {$checked}>
                                                <label class='form-check-label' for='{$index}'>{$label}</label>
                                            </div>";
                                }
                            ])->label('ประเภทสถานที่'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ข้อมูลการเดินทาง -->
        <div class="card mb-3">
            <div class="card-header bg-light p-2">
                <strong><i class="bi bi-car-front me-2"></i>ข้อมูลการเดินทาง</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= $form->field($model, 'vehicle_date_start')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'วว/ดด/ปปปป'])->label('วันไป') ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= $form->field($model, 'vehicle_date_end')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'วว/ดด/ปปปป'])->label('วันกลับ') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                                <div class="row">
                                    <div class="col-6">
                                    <div class="form-group">
                            <?php
                            echo $form->field($model, 'data_json[vehicle_type_name]')->widget(Select2::classname(), [
                                'data' => $model->ListVehicleType(),
                                'options' => ['placeholder' => 'เลือกพาหนะเดินทาง'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('พาหนะเดินทาง');
                            ?>
                        </div>
                                    </div>
                                    <div class="col-6">

<?= $form->field($model, 'data_json[license_plate]')->textInput(['placeholder' => 'ระบุทะเบียนพาหนะเดินทาง'])->label('ทะเบียนพาหนะเดินทาง') ?>

                                    </div>
                                </div>

                    </div>
                    
                </div>
            </div>
        </div>

        <?= $form->field($model, 'status')->hiddenInput(['maxlength' => true, 'value' => 'Pending'])->label(false) ?>
        <?= $form->field($model, 'emp_id')->hiddenInput(['maxlength' => true, 'value' => 1])->label(false) ?>

        <div class="form-group text-center mt-4">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle me-2"></i> บันทึกข้อมูล', ['class' => 'btn btn-primary rounded-pill px-4 py-2 shadow me-2', 'id' => 'summit']) ?>
            <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 shadow" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i> ยกเลิก
            </button>
        </div>

        <?php ActiveForm::end(); ?>


<?php

$js = <<<JS

    thaiDatepicker('#development-date_start,#development-date_end,#development-vehicle_date_start,#development-vehicle_date_end');

      \$('#form-development').on('beforeSubmit', function (e) {
        var form = \$(this);

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
            
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                boforeSubmit: function(){
                    beforLoadModal()
                },
                success: function (response) {
                    if(response.status == 'success') {
                        closeModal()
                        Swal.fire({
                            title: "สำเร็จ!",
                            text: "บันทึกข้อมูลเรียบร้อยแล้ว",
                            icon: "success",
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                }
            });
        }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END);

?>