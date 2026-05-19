<?php
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeePositionGroup;
use app\modules\hr\models\EmployeeType;
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeeDetail;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$formatJs = <<< 'JS'
var formatRepo = function (repo) {
    if (repo.loading) {
        return repo.text;
    }
    // console.log(repo);
    var markup =
'<div class="row">' +
    '<div class="col-12">' +
        '<span>' + repo.text + '</span>' +
    '</div>' +
'</div>';
    if (repo.description) {
      markup += '<p>' + repo.text + '</p>';
    }
    return '<div style="overflow:hidden;">' + (repo.html || markup) + '</div>';
};
var formatRepoSelection = function (repo) {
    return repo.text || repo.text;
}
JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
function (data, params) {
    params.page = params.page || 1;
    return {
        results: data.results,
        pagination: {
            more: (params.page * 30) < data.total_count
        }
    };
}
JS;

$employeePositionId = $model->data_json['employee_position_id'] ?? null;
$employeePositionText = $model->data_json['employee_position_text'] ?? $model->data_json['position_name_text'] ?? '';
$employeePositionGroupId = $model->data_json['employee_position_group_id'] ?? null;
$employeeTypeId = $model->data_json['employee_type_id'] ?? null;
$employeeTypeText = $model->data_json['employee_type_text'] ?? $model->data_json['position_type_text'] ?? '';
$showEmployeePositionGroupField = false;
$employeeTypeColumnClass = $showEmployeePositionGroupField ? 'col-12 col-lg-4' : 'col-12 col-lg-6';
$employeePositionColumnClass = $showEmployeePositionGroupField ? 'col-12 col-lg-4' : 'col-12 col-lg-6';
$employeePositionGroupHiddenField = $form->field($model, 'data_json[employee_position_group_id]')->hiddenInput([
    'value' => $employeePositionGroupId,
])->label(false);

if (empty($employeeTypeId) && !empty($model->data_json['position_type'])) {
    foreach (EmployeeType::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $typeModel) {
        if (in_array($model->data_json['position_type'], $typeModel->legacyCodes(), true)) {
            $employeeTypeId = $typeModel->id;
            $employeeTypeText = $typeModel->title;
            break;
        }
    }
}

if (empty($employeeTypeId) && !empty($model->data_json['position_type_text'])) {
    foreach (EmployeeType::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $typeModel) {
        if (strcasecmp(trim((string) $typeModel->title), trim((string) $model->data_json['position_type_text'])) === 0) {
            $employeeTypeId = $typeModel->id;
            $employeeTypeText = $typeModel->title;
            break;
        }
    }
}

if (empty($employeeTypeId) && !empty($model->data_json['position_type'])) {
    foreach (EmployeeType::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $typeModel) {
        if (strcasecmp(trim((string) $typeModel->title), trim((string) $model->data_json['position_type'])) === 0) {
            $employeeTypeId = $typeModel->id;
            $employeeTypeText = $typeModel->title;
            break;
        }
    }
}

if (empty($employeePositionGroupId) && !empty($model->data_json['position_group'])) {
    $legacyGroup = EmployeePositionGroup::find()->where(['legacy_code' => $model->data_json['position_group']])->one();
    if ($legacyGroup) {
        $employeePositionGroupId = $legacyGroup->id;
    }
}

if (empty($employeePositionGroupId) && !empty($model->data_json['position_group_text'])) {
    $legacyGroupTitle = trim((string) $model->data_json['position_group_text']);
    foreach (EmployeePositionGroup::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $legacyGroup) {
        if (strcasecmp(trim((string) $legacyGroup->title), $legacyGroupTitle) === 0) {
            $employeePositionGroupId = $legacyGroup->id;
            break;
        }
    }
}

if (empty($employeePositionGroupId) && !empty($model->data_json['position_group'])) {
    $legacyGroupTitle = trim((string) $model->data_json['position_group']);
    foreach (EmployeePositionGroup::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $legacyGroup) {
        if (strcasecmp(trim((string) $legacyGroup->title), $legacyGroupTitle) === 0) {
            $employeePositionGroupId = $legacyGroup->id;
            break;
        }
    }
}

if (empty($employeePositionId) && !empty($model->data_json['position_name'])) {
    $legacyPosition = EmployeePosition::find()
        ->with(['employeeType', 'employeePositionGroup'])
        ->where(['legacy_code' => $model->data_json['position_name']])
        ->one();

    if ($legacyPosition) {
        $employeePositionId = $legacyPosition->id;
        $employeePositionText = $legacyPosition->title;
        $employeePositionGroupId = $legacyPosition->employee_position_group_id;
        $employeeTypeId = $legacyPosition->employee_type_id;
        $employeeTypeText = $legacyPosition->employeeType?->title ?? $employeeTypeText;
    }
}

if (empty($employeePositionId) && !empty($model->data_json['position_name_text'])) {
    $legacyPositionTitle = trim((string) $model->data_json['position_name_text']);
    foreach (EmployeePosition::find()->with(['employeeType', 'employeePositionGroup'])->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $legacyPosition) {
        if (strcasecmp(trim((string) $legacyPosition->title), $legacyPositionTitle) === 0) {
            $employeePositionId = $legacyPosition->id;
            $employeePositionText = $legacyPosition->title;
            $employeePositionGroupId = $legacyPosition->employee_position_group_id;
            $employeeTypeId = $legacyPosition->employee_type_id;
            $employeeTypeText = $legacyPosition->employeeType?->title ?? $employeeTypeText;
            break;
        }
    }
}

if (empty($employeePositionId) && !empty($model->data_json['position_name'])) {
    $legacyPositionTitle = trim((string) $model->data_json['position_name']);
    foreach (EmployeePosition::find()->with(['employeeType', 'employeePositionGroup'])->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $legacyPosition) {
        if (strcasecmp(trim((string) $legacyPosition->title), $legacyPositionTitle) === 0) {
            $employeePositionId = $legacyPosition->id;
            $employeePositionText = $legacyPosition->title;
            $employeePositionGroupId = $legacyPosition->employee_position_group_id;
            $employeeTypeId = $legacyPosition->employee_type_id;
            $employeeTypeText = $legacyPosition->employeeType?->title ?? $employeeTypeText;
            break;
        }
    }
}

$initEmployeePosition = $employeePositionId ? [
    (string) $employeePositionId => $employeePositionText ?: 'ตำแหน่งพนักงาน (ใหม่)',
] : [];

//  debug sql query
// $querySql = EmployeeDetail::find()->where(['name' => 'position', 'emp_id' => 1])
// ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') asc"))->createCommand()->getRawSql();
// print_r($querySql);


?>
<?=$form->field($model, 'data_json[fullname]')->hiddenInput(['value' => $model->employee->fullname])->label(false)?>
<?php echo $form->field($model, 'data_json[position_level_text]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[status_text]')->hiddenInput()->label(false) ?>
<?php if (!$showEmployeePositionGroupField): ?>
    <?= $employeePositionGroupHiddenField ?>
<?php endif; ?>

<div class="row">
    <div class="col-3">
    <?= $form->field($model, 'data_json[date_start]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ตั้งแต่วันที่') ?>

    </div>
    <div class="col-9">
        <?=$form->field($model, 'data_json[statuslist]')->textInput(['placeholder' => 'เช่น จ้างเป็นลูกจ้าง/เลื่อนขั้นค่าจ้าง 0.5 ขั้น'])->label('รายการเคลื่อนไหว ')?>
    </div>

    <div class="col-12">
        <div class="row g-3">
            <div class="<?= $employeeTypeColumnClass ?>">
                <?=
                    $form->field($model, 'data_json[employee_type_id]')->widget(Select2::classname(), [
                        'data' => EmployeeType::listItems(),
                        'options' => ['placeholder' => 'เลือกประเภทพนักงาน (ใหม่) ...'],
                        'pluginEvents' => [
                            "select2:unselect" => "function() {
                                $('#employeedetail-data_json-position_level').prop('disabled', true);
                                $('#employeedetail-data_json-position_level').val('').trigger('change');
                                $('#employeedetail-data_json-position_level_text').val('');
                            }",
                            "select2:select" => "function() {
                                var data = $(this).select2('data')[0] || {};
                                if (data.text === 'ข้าราชการ') {
                                    $('#employeedetail-data_json-position_level').prop('disabled', false);
                                } else {
                                    $('#employeedetail-data_json-position_level').prop('disabled', true);
                                    $('#employeedetail-data_json-position_level').val('').trigger('change');
                                    $('#employeedetail-data_json-position_level_text').val('');
                                }
                            }",
                        ],
                        'pluginOptions' => [
                            'dropdownParent' => '#main-modal',
                            'allowClear' => true,
                        ],
                    ])->label('ประเภทพนักงาน (ใหม่)')
                ?>
            </div>

            <?php if ($showEmployeePositionGroupField): ?>
                <div class="col-12 col-lg-4">
                    <?=
                        $form->field($model, 'data_json[employee_position_group_id]')->widget(Select2::classname(), [
                            'data' => EmployeePositionGroup::listItems(),
                            'options' => ['placeholder' => 'เลือกกลุ่มตำแหน่ง (ใหม่) ...'],
                            'pluginOptions' => [
                                'dropdownParent' => '#main-modal',
                                'allowClear' => true,
                            ],
                        ])->label('กลุ่มตำแหน่งพนักงาน (ใหม่)')
                    ?>
                </div>
            <?php endif; ?>

            <div class="<?= $employeePositionColumnClass ?>">
                <?=
                    $form->field($model, 'data_json[employee_position_id]')->widget(Select2::classname(), [
                        'initValueText' => $initEmployeePosition,
                        'options' => ['placeholder' => 'เลือกชื่อตำแหน่ง (ใหม่) ...'],
                        'pluginOptions' => [
                            'dropdownParent' => '#main-modal',
                            'allowClear' => true,
                            'minimumInputLength' => 0,
                            'ajax' => [
                                'url' => Url::to(['/depdrop/employee-position-list']),
                                'dataType' => 'json',
                                'delay' => 250,
                                'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                                'processResults' => new JsExpression($resultsJs),
                                'cache' => true,
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('formatRepo'),
                            'templateSelection' => new JsExpression('formatRepoSelection'),
                        ],
                        'pluginEvents' => [
                            'select2:select' => new JsExpression("function() {
                                var data = $(this).select2('data')[0] || {};
                                $('#employeedetail-data_json-employee_position_group_id').val(data.employee_position_group_id || '');
                            }"),
                            'select2:unselect' => new JsExpression("function() {
                                $('#employeedetail-data_json-employee_position_group_id').val('');
                            }"),
                            'select2:clear' => new JsExpression("function() {
                                $('#employeedetail-data_json-employee_position_group_id').val('');
                            }"),
                        ],
                    ])->label('ชื่อตำแหน่ง (ใหม่)')
                ?>
            </div>
        </div>

        <div class="text-muted small mt-2">
            ระบบจะกำหนดกลุ่มตำแหน่งจากชื่อตำแหน่งที่เลือกให้อัตโนมัติ และยังเก็บค่าไว้หลังบ้านเผื่อใช้ในอนาคต
        </div>
    </div>


    <div class="col-6">
        <?php echo $form->field($model, 'data_json[position_number]')->textInput()->label('เลขประจำตำแหน่ง') ?>

    </div>

    <div class="col-6">
        <?=$form->field($model, 'data_json[position_level]')->widget(Select2::classname(), [
    'data' => $model->employee->ListPositionLevel(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginEvents' => [
        "select2:unselect" => "function() {
            $('#employeedetail-data_json-position_level_text').val('')

         }",
        "select2:select" => "function() {
            var data = $(this).select2('data')
             $('#employeedetail-data_json-position_level_text').val(data[0].text)
            
         }",

    ],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => false,
        'maximumInputLength' => 10,
        'disabled' => ($employeeTypeText === 'ข้าราชการ') ? false : true

    ],
])->label('ระดับความเชี่ยวชาญ')?>
    </div>

    <div class="col-12">

    <?= $form->field($model, 'data_json[department]')->widget(\kartik\tree\TreeViewInput::className(), [
    'name' => 'department',
    'id' => 'treeID',
    'query' => Organization::find()->addOrderBy('root, lft'),
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => false,
    'showTooltips' => false,
    'dropdownConfig' => [
        'input' => [
            'placeholder' => '--หน่วยงานทั้งหมด--',
        ],
    ],
    'options' => [
        'class' => 'form-select',
    ],
    'pluginOptions' => [
        'allowClear' => true
    ],
    'pluginEvents' => [
        'treeview:change' => new JsExpression('function() {
            var $container = $(this).closest(".kv-tree-dropdown-container");
            setTimeout(function() {
                var $toggle = $container.find(".kv-tree-input");
                $toggle.removeClass("show open").attr("aria-expanded", "false");
                $container.find(".kv-tree-dropdown").removeClass("show open");
                if (window.bootstrap && bootstrap.Dropdown && $toggle.length) {
                    try {
                        var instance = bootstrap.Dropdown.getInstance($toggle[0]) || bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
                        if (instance) {
                            instance.hide();
                        }
                    } catch (e) {}
                }
            }, 0);
        }'),
    ],
])->label('หน่วยงานภายในตามโครงสร้าง'); ?>

        <?php
//         $form->field($model, 'data_json[department]')->widget(\kartik\tree\TreeViewInput::className(), [
//     'name' => 'department',
//     'query' => Organization::find()->addOrderBy('root, lft'),
//     'value' => 1,
//     'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
//     'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
//     'fontAwesome' => true,
//     'asDropdown' => true,
//     'multiple' => false,
//     'options' => ['disabled' => false],
// ])->label('หน่วยงานภายในตามโครงสร้าง');
?>

    </div>
    <div class="col-6">
        <?=$form->field($model, 'data_json[status]')->widget(Select2::classname(), [
    'data' => $model->employee->ListStatus(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginEvents' => [
        "select2:unselect" => "function() {
            $('#employeedetail-data_json-status_text').val('')

         }",
        "select2:select" => "function() {
            var data = $(this).select2('data')
             $('#employeedetail-data_json-status_text').val(data[0].text)
            
         }",

    ],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => false,
        'maximumInputLength' => 10,
    ],
])->label('สถานะ')?>

    </div>

    <div class="col-3">
        <?=$form->field($model, 'data_json[salary]')->textInput(['type' => 'number'])->label('อัตราเงินเดือน')?>
    </div>
    <div class="col-3">
        <?=$form->field($model, 'data_json[point]')->textInput(['placeholder' => '0.00', 'type' => 'decimals'])->label('ผลการประเมิน')?>
    </div>

    <div class="col-12">
        <?=$form->field($model, 'data_json[doc_ref]')->textInput()->label('เอกสารอ้างอิง')?>
    </div>
    <div class="col-12">
        <?=$form->field($model, 'data_json[comment]')->textArea()->label('หน่วยงานตาม จ.18')?>
    </div>
</div>
<?php $model->upload($model->ref, 'position')?>



<?php
// Url::to(['/depdrop/categorise-by-code']);
$js = <<<JS



// $('.test').click(function (e) {
//     e.preventDefault();
//     $('body,#selectPositionGroup').html('xx');

// });
// $('body','#selectPositionGroup').text('dddd');
// // $('#selectPositionGroup').text('xxx');
    var thaiYear = function (ct) {
        var leap=3;
        var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];
        if(ct){
            var yearL=new Date(ct).getFullYear()-543;
            leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;
            if(leap==2){
                dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];
            }
        }
        this.setOptions({
            i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,
        })
    };

    $("#employeedetail-data_json-date_start").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,
        onShow:thaiYear,
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });
JS;
$this->registerJS($js, View::POS_END)
?>
