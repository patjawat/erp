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

    <!-- รายการค่าใช้จ่าย -->
    <?php
    $expenseTypeList = (new DevelopmentDetail())->listExpenseType();
    $expenseRows = $model->isNewRecord ? [] : $model->expenses;
    ?>
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header p-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <strong><i class="fa-solid fa-money-bill-1 me-2"></i> รายการค่าใช้จ่าย</strong>
            <button type="button" id="btn-add-expense-row" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มรายการ
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 1%;">ลำดับ</th>
                            <th>รายการ</th>
                            <th style="width: 140px;">จำนวนเงิน (บาท)</th>
                            <th>หมายเหตุ</th>
                            <th style="width: 80px;" class="text-center">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider" id="expense-rows-tbody">
                        <?php foreach ($expenseRows as $idx => $exp): ?>
                        <tr class="expense-row">
                            <td class="text-muted expense-row-num"><?= $idx + 1 ?></td>
                            <td>
                                <input type="hidden" name="expense_rows[<?= $idx ?>][id]" value="<?= (int) $exp->id ?>">
                                <select name="expense_rows[<?= $idx ?>][category_id]" class="form-select">
                                    <option value="">-- เลือกรายการ --</option>
                                    <?php foreach ($expenseTypeList as $code => $title): ?>
                                    <option value="<?= Html::encode($code) ?>" <?= ($exp->category_id === (string)$code) ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="expense_rows[<?= $idx ?>][price]" class="form-control text-end" step="0.01" min="0" placeholder="0.00" value="<?= $exp->price !== null ? Html::encode($exp->price) : '' ?>">
                            </td>
                            <td>
                                <?php
                                    $expNote = '';
                                    if (is_array($exp->data_json)) {
                                        $expNote = $exp->data_json['note'] ?? '';
                                    } elseif (is_string($exp->data_json)) {
                                        $arr = json_decode($exp->data_json, true);
                                        $expNote = is_array($arr) ? ($arr['note'] ?? '') : '';
                                    }
                                    ?>
                                <input type="text" name="expense_rows[<?= $idx ?>][note]" class="form-control" placeholder="หมายเหตุ" value="<?= Html::encode($expNote) ?>">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill btn-remove-expense-row" title="ลบ"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="expense-row-template" class="expense-row d-none" data-template="1">
                            <td class="text-muted expense-row-num"></td>
                            <td>
                                <input type="hidden" data-name="expense_rows[__INDEX__][id]" value="">
                                <select data-name="expense_rows[__INDEX__][category_id]" class="form-select">
                                    <option value="">-- เลือกรายการ --</option>
                                    <?php foreach ($expenseTypeList as $code => $title): ?>
                                    <option value="<?= Html::encode($code) ?>"><?= Html::encode($title) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" data-name="expense_rows[__INDEX__][price]" class="form-control text-end" step="0.01" min="0" placeholder="0.00" value="">
                            </td>
                            <td>
                                <input type="text" data-name="expense_rows[__INDEX__][note]" class="form-control" placeholder="หมายเหตุ" value="">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill btn-remove-expense-row" title="ลบ"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mb-0 mt-2">กด «เพิ่มรายการ» เพื่อเพิ่มรายการค่าใช้จ่าย เช่น ค่าเบี้ยเลี้ยง ค่าที่พัก</p>
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

// รายการค่าใช้จ่าย: เพิ่มแถว
var expenseRowIndex = \$('#expense-rows-tbody .expense-row:not([data-template])').length;
\$('#btn-add-expense-row').on('click', function() {
    var \$tpl = \$('#expense-row-template');
    if (!\$tpl.length) return;
    var \$row = \$tpl.clone().removeAttr('id').removeClass('d-none').removeAttr('data-template');
    \$row.find('[data-name]').each(function() {
        var n = \$(this).data('name');
        if (n) \$(this).attr('name', n.replace(/__INDEX__/g, expenseRowIndex));
    });
    \$row.find('.expense-row-num').text(\$('#expense-rows-tbody .expense-row:not([data-template])').length + 1);
    \$tpl.before(\$row);
    expenseRowIndex++;
});
\$('#expense-rows-tbody').on('click', '.btn-remove-expense-row', function() {
    var \$row = \$(this).closest('tr.expense-row');
    if (\$row.attr('data-template')) return;
    \$row.remove();
    \$('#expense-rows-tbody .expense-row:not([data-template])').each(function(i) {
        \$(this).find('.expense-row-num').first().text(i + 1);
    });
});

    thaiDatepicker('#development-date_start,#development-date_end,#development-vehicle_date_start,#development-vehicle_date_end');

      \$(document).on('beforeSubmit', '#form-development', function (e) {
        var form = \$(this);
        var inModal = form.closest('.modal').length > 0;

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
          if (!result.isConfirmed) return;
          if (inModal) {
            var formData = form.serialize();
            var expenseRows = [];
            \$('#expense-rows-tbody .expense-row:not([data-template])').each(function() {
              var \$r = \$(this);
              expenseRows.push({
                id: (\$r.find('input[name*="[id]"]').val() || '').trim(),
                category_id: (\$r.find('select[name*="[category_id]"]').val() || '').trim(),
                price: (\$r.find('input[name*="[price]"]').val() || '').trim(),
                note: (\$r.find('input[name*="[note]"]').val() || '').trim()
              });
            });
            for (var i = 0; i < expenseRows.length; i++) {
              formData += '&expense_rows[' + i + '][id]=' + encodeURIComponent(expenseRows[i].id);
              formData += '&expense_rows[' + i + '][category_id]=' + encodeURIComponent(expenseRows[i].category_id);
              formData += '&expense_rows[' + i + '][price]=' + encodeURIComponent(expenseRows[i].price);
              formData += '&expense_rows[' + i + '][note]=' + encodeURIComponent(expenseRows[i].note);
            }
            \$.ajax({
              url: form.attr('action'),
              type: 'post',
              data: formData,
              dataType: 'json',
              beforeSend: function() {
                if (typeof beforLoadModal === 'function') beforLoadModal();
              },
              success: function (response) {
                if (response && response.status == 'success') {
                  if (typeof closeModal === 'function') closeModal();
                  Swal.fire({ title: "สำเร็จ!", text: "บันทึกข้อมูลเรียบร้อยแล้ว", icon: "success", timer: 1000, showConfirmButton: false }).then(function() { window.location.reload(); });
                } else if (response && response.redirect) {
                  window.location.href = response.redirect;
                } else {
                  window.location.reload();
                }
              }
            });
          } else {
            \$(document).off('beforeSubmit', '#form-development');
            form.off('submit');
            form[0].submit();
          }
        });
        return false;
      });

JS;
$this->registerJS($js, View::POS_END);

?>
