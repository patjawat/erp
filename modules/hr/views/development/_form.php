<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\components\ThaiDateHelper;
use app\components\CategoriseHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\DevelopmentDetail;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
$emp = UserHelper::GetEmployee();
$listDocumentMe  = $emp->listDocumentMe();


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
<?php if (!$model->isNewRecord): ?>
<input type="hidden" name="development_id" value="<?= (int) $model->id ?>">
<?php endif; ?>

<div class="container-fluid px-3 px-sm-4 pb-4">
    <!-- ข้อมูลอ้างอิงเอกสาร -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-file-earmark-text me-2"></i>ข้อมูลเอกสาร</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <?= $form->field($model, 'thai_year')->textInput(['class' => 'form-control']) ?>
                    <?= $form->field($model, 'data_json[doc_number]')->textInput(['class' => 'form-control'])->label('เลขที่หนังสือ') ?>
                </div>
                <div class="col-12 col-md-9">
                    <?php

                        $listDocumentData = ArrayHelper::map($listDocumentMe, 'id', function($model) {
                            return [
                                'text' => $model['topic'] ?? null,
                                'doc_number' => $model['doc_number'] ?? null,
                            ];
                        });

                            echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                                'data' => ArrayHelper::map($listDocumentMe, 'id', 'topic'),
                                'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    // 'dropdownParent' => '#main-modal',
                                ],
                                'pluginEvents' => [
                                    'select2:select' =>  new JsExpression("function(e) {
                                       var data = e.params.data;
                                        $('#development-topic').val(data.text);
                                    }"),
                                ]
                            ])->label('หนังสืออ้างอิง');
                            ?>
                </div>
            </div>
        </div>
    </div>

    <!-- รายละเอียดการพัฒนา -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-info-circle me-2"></i>รายละเอียดการพัฒนา</strong>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-0">
                <div class="col-12">
                    <?= $form->field($model, 'topic')->textInput(['maxlength' => true, 'placeholder' => 'ระบุหัวข้อการอบรม/ประชุม/ดูงาน']) ?>
                </div>
            </div>
            <div class="row g-3 mb-0">
                <div class="col-12">
                    <?= $form->field($model, 'data_json[travel_party]')->textInput(['maxlength' => true, 'placeholder' => 'เช่น คณะกรรมการโครงการ, หน่วยงานที่เดินทางร่วมกัน'])->label('คำอธิบายคณะเดินทาง') ?>
                </div>
            </div>

            <!-- สมาชิกคณะเดินทาง (เพิ่มจากฟอร์มได้) -->
            <div class="row g-3 mt-2">
                <div class="col-12">
                    <label class="form-label d-block mb-2">รายชื่อสมาชิกคณะเดินทาง</label>
                    <div id="travel-party-members-list" class="mb-3" data-emp-search-url="<?= Html::encode(Url::to(['/depdrop/employee-by-id'])) ?>">
                        <?php
                        $existingMembers = $model->isNewRecord ? [] : $model->listMember();
                        foreach ($existingMembers as $detail):
                            $emp = $detail->emp;
                            if (!$emp) {
                                $label = trim((string)($detail->data_json['label'] ?? '')) ?: $detail->emp_id;
                        ?>
                        <div class="travel-party-row d-flex align-items-center gap-2 py-2 border-bottom border-light">
                            <input type="hidden" name="member_emp_ids[]" value="<?= Html::encode($detail->emp_id) ?>">
                            <span class="text-body flex-grow-1"><?= Html::encode($label) ?></span>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill btn-remove-member" title="ลบ"><i class="bi bi-trash"></i></button>
                        </div>
                        <?php
                                continue;
                            }
                        ?>
                        <div class="travel-party-row d-flex align-items-center gap-2 py-2 border-bottom border-light">
                            <input type="hidden" name="member_emp_ids[]" value="<?= Html::encode($detail->emp_id) ?>">
                            <div class="flex-grow-1"><?= $emp->getAvatar(false) ?></div>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill btn-remove-member" title="ลบ"><i class="bi bi-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="flex-grow-1" style="min-width: 260px;">
                            <select id="member-emp-select-new" class="form-control" style="width: 100%;">
                                <option value="">เลือกบุคลากรเพื่อเพิ่มในคณะเดินทาง ...</option>
                            </select>
                        </div>
                        <button type="button" id="btn-add-travel-member" class="btn btn-outline-primary rounded-pill align-self-end">
                            <i class="bi bi-person-plus me-1"></i> เพิ่มสมาชิก
                        </button>
                    </div>
                    <p class="small text-muted mt-1 mb-0">เลือกบุคลากรจากรายการแล้วกด «เพิ่มสมาชิก» หรือเลือกแล้วกด Enter</p>
                </div>
            </div>

            <div class="row g-3 mt-0">
                <!-- คอลัมน์ซ้าย -->
                <div class="col-12 col-md-6">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'date_start')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.']) ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'date_end')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.']) ?>
                        </div>
                    </div>

                    <?php
                            echo $form->field($model, 'development_type_id')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::DevelopmentType(),
                                'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
                                'pluginOptions' => [
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('ประเภทการพัฒนา');
                            ?>

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

                <!-- คอลัมน์ขวา -->
                <div class="col-12 col-md-6">
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

                    ])->label('หัวหน้างาน');
                    ?>


                    <?php
            $url = Url::to(['/depdrop/employee-by-id']);
            $employee = Employees::find()->where(['id' => $model->leader_group_id])->one();
            $initEmployee = empty($model->leader_group_id) ? '' : Employees::findOne($model->leader_group_id)->getAvatar(false);//กำหนดค่าเริ่มต้น
            
            echo $form->field($model,'leader_group_id')->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                // 'size' => Select2::,
                'options' => ['placeholder' => 'เลือกบุคลากร ...'],
                'pluginOptions'=>[
                    // 'dropdownParent' => '#main-modal',
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

                    ])->label('หัวหน้ากลุ่มงาน');
                    ?>

                <div class="avatar-form">
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

    <!-- สถานที่และหน่วยงาน -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-geo-alt me-2"></i>สถานที่และหน่วยงาน</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <?php
                            echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListLocationOrg(true),
                                'options' => ['placeholder' => 'เลือกสถานที่'],
                        'pluginOptions' => [
                            'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                            'allowClear' => true,
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) { 
                                            }',
                            'select2:unselecting' => 'function() {

                                            }',
                        ],
                
                    ])->label('สถานที่จัดงาน');?>

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

                <div class="col-12 col-md-6">
                    <?php
                            echo $form->field($model, 'data_json[location_org]')->widget(Select2::classname(), [
                                'data' => CategoriseHelper::ListLocationOrg(true),
                                'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                                'pluginOptions' => [
                                      'tags' => true, // เปิดให้เพิ่มค่าใหม่ได้
                                    // 'dropdownParent' => '#main-modal',
                                    'allowClear' => true,
                                ],
                            ])->label('หน่วยงานที่จัด');
                            ?>

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

    <!-- ข้อมูลการเดินทาง -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2">
            <strong><i class="bi bi-car-front me-2"></i>ข้อมูลการเดินทาง</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <?= $form->field($model, 'vehicle_date_start')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.'])->label('วันไป') ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <?= $form->field($model, 'data_json[vehicle_time_start]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลา') ?>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <?= $form->field($model, 'vehicle_date_end')->textInput(['class' => 'form-control', 'placeholder' => 'วัน/เดือน/พ.ศ.'])->label('วันกลับ') ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <?= $form->field($model, 'data_json[vehicle_time_end]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลา') ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <?php
                                echo $form->field($model, 'vehicle_type_id')->widget(Select2::classname(), [
                                    'data' => $model->ListVehicleType(),
                                    'options' => ['placeholder' => 'เลือกพาหนะเดินทาง'],
                                    'pluginOptions' => [
                                        // 'dropdownParent' => '#main-modal',
                                        'allowClear' => true,
                                    ],
                                ])->label('พาหนะเดินทาง');
                                ?>
                        </div>
                        <div class="col-12 col-md-6">
                            <?= $form->field($model, 'data_json[license_plate]')->textInput(['placeholder' => 'ระบุทะเบียนพาหนะเดินทาง'])->label('ทะเบียนพาหนะเดินทาง') ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'data_json[distance]')->textInput(['placeholder' => 'ระบุระยะทาง'])->label('ระยะทาง/กิโลเมตร') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ประมาณค่าใช้จ่ายในการเข้ารับการอบรม/ประชุม/สัมมนา ครั้งนี้ -->
    <div class="card mb-3 border-0 shadow-sm" id="estimated-cost-card">
        <div class="card-header p-2">
            <strong><i class="fa-solid fa-money-bill-1 me-2"></i> ประมาณค่าใช้จ่ายในการเข้ารับการอบรม/ประชุม/สัมมนา ครั้งนี้</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_registration]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่าลงทะเบียน (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_accommodation]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่าที่พัก (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_vehicle_fuel]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่ายานพาหนะ/น้ำมันเชื้อเพลิง (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_allowance]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('ค่าเบี้ยเลี้ยง (บาท)') ?>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <?= $form->field($model, 'data_json[estimated_cost_other]')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'min' => '0',
                        'class' => 'form-control text-end estimated-cost-input',
                        'placeholder' => '0.00',
                    ])->label('อื่น ๆ (บาท)') ?>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-end align-items-center border-top pt-3 mt-1">
                        <span class="fw-semibold me-2">รวมทั้งหมด:</span>
                        <span id="estimated-cost-total" class="fs-5 text-primary fw-bold">0.00</span>
                        <span class="ms-1">บาท</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-4">
        <div class="col-12 text-center">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle me-2"></i> บันทึกข้อมูล', ['class' => 'btn btn-primary rounded-pill px-4 py-2 shadow me-2', 'id' => 'summit']) ?>

            <?= Html::a(
                '<i class="bi bi-arrow-left-circle me-2"></i> ย้อนกลับ',
                'javascript:history.back()',
                ['class' => 'btn btn-secondary rounded-pill px-4 py-2 shadow']
            ) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<<JS

\$memberSelectUrl = \$('#travel-party-members-list').data('emp-search-url') || '';
if (\$memberSelectUrl && \$('#member-emp-select-new').length) {
    \$('#member-emp-select-new').select2({
        theme: 'krajee-bs5',
        allowClear: true,
        placeholder: 'เลือกบุคลากรเพื่อเพิ่มในคณะเดินทาง ...',
        minimumInputLength: 1,
        ajax: {
            url: \$memberSelectUrl,
            dataType: 'json',
            data: function(params) { return { q: params.term }; },
            processResults: function(data) {
                return { results: data.results || [], pagination: data.pagination || { more: false } };
            }
        },
        escapeMarkup: function(m) { return m; },
        templateResult: function(emp) { return emp && emp.text ? emp.text : 'กำลังค้นหา...'; },
        templateSelection: function(emp) { return emp.text || ''; }
    });
    function appendTravelMemberRow(id, avatarHtml) {
        var exists = \$('#travel-party-members-list input[name="member_emp_ids[]"]').filter(function() { return \$(this).val() === String(id); }).length;
        if (exists) return;
        var row = \$('<div class="travel-party-row d-flex align-items-center gap-2 py-2 border-bottom border-light"></div>');
        row.append(\$('<input type="hidden" name="member_emp_ids[]">').val(id));
        row.append(\$('<div class="flex-grow-1"></div>').html(avatarHtml || ('<span class="text-body">' + id + '</span>')));
        row.append(\$('<button type="button" class="btn btn-outline-danger btn-sm rounded-pill btn-remove-member" title="ลบ"><i class="bi bi-trash"></i></button>'));
        \$('#travel-party-members-list').append(row);
    }
    \$('#member-emp-select-new').on('select2:select', function(e) {
        var data = e.params.data;
        var id = data.id;
        var avatarHtml = (data.text && typeof data.text === 'string') ? data.text : null;
        appendTravelMemberRow(id, avatarHtml);
        \$('#member-emp-select-new').val(null).trigger('change');
    });
    \$('#btn-add-travel-member').on('click', function() {
        var sel = \$('#member-emp-select-new').select2('data');
        if (sel && sel[0] && sel[0].id) {
            var data = sel[0];
            var id = data.id;
            var avatarHtml = (data.text && typeof data.text === 'string') ? data.text : null;
            appendTravelMemberRow(id, avatarHtml);
            \$('#member-emp-select-new').val(null).trigger('change');
        }
    });
}
\$('#travel-party-members-list').on('click', '.btn-remove-member', function() {
    \$(this).closest('.travel-party-row').remove();
});

// คำนวณรวมประมาณค่าใช้จ่าย
function updateEstimatedCostTotal() {
    var total = 0;
    \$('#estimated-cost-card .estimated-cost-input').each(function() {
        var v = parseFloat(\$(this).val());
        if (!isNaN(v) && v >= 0) total += v;
    });
    \$('#estimated-cost-total').text(total.toFixed(2).replace(/\\B(?=(\\d{3})+(?!\\d))/g, ','));
}
\$('#estimated-cost-card').on('input change', '.estimated-cost-input', updateEstimatedCostTotal);
updateEstimatedCostTotal();

    thaiDatepicker('#development-date_start,#development-date_end,#development-vehicle_date_start,#development-vehicle_date_end');

JS;
$this->registerJS($js, View::POS_END);

?>
