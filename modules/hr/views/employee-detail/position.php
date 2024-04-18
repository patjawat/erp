<?php
use app\components\CategoriseHelper;
use app\models\Categorise;
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeeDetail;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
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
    return '<div style="overflow:hidden;">' + markup + '</div>';
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

//  debug sql query
// $querySql = EmployeeDetail::find()->where(['name' => 'position', 'emp_id' => 1])
// ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') asc"))->createCommand()->getRawSql();
// print_r($querySql);


?>
<?=$form->field($model, 'data_json[fullname]')->hiddenInput(['value' => $model->employee->fullname])->label(false)?>
<?php echo $form->field($model, 'data_json[position_name_text]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[position_group]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[position_group_text]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[position_type]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[position_type_text]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[position_level_text]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[status_text]')->hiddenInput()->label(false) ?>

<div class="row">
    <div class="col-3">
    <?= $form->field($model, 'data_json[date_start]')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('ตั้งแต่วันที่') ?>

    </div>
    <div class="col-9">
        <?=$form->field($model, 'data_json[statuslist]')->textInput(['placeholder' => 'เช่น จ้างเป็นลูกจ้าง/เลื่อนขั้นค่าจ้าง 0.5 ขั้น'])->label('รายการเคลื่อนไหว ')?>
    </div>
    <div class="col-9">

        <?php
$initPositionName = ArrayHelper::map(CategoriseHelper::Categorise('position_name'), 'code', 'title');
echo $form->field($model, 'data_json[position_name]')->widget(Select2::classname(), [
    'initValueText' => $initPositionName,
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginEvents' => [
        "select2:unselect" => "function() {
                        $('#employeedetail-data_json-position_name_text').val('')
                        $('#employeedetail-data_json-position_group').val('')
                        $('#employeedetail-data_json-position_group_text').val('')
                        $('#employeedetail-data_json-position_type').val('')
                        $('#employeedetail-data_json-position_type_text').val('')
                        $('.position-group-label').text('')
                        $('.position-type-label').text('')

         }",
        "select2:select" => "function() {
                // console.log($(this).val());
                $.ajax({
                    type: 'get',
                    url: '" . Url::to(['/depdrop/categorise-by-code']) . "',
                    data: {
                        code: $(this).val(),
                        name:'position_name'
                    },
                    dataType: 'json',
                    success: function (res) {
                        // console.log(response);
                        $('#employeedetail-data_json-position_name_text').val(res.position_name)
                        $('#employeedetail-data_json-position_group').val(res.position_group)
                        $('#employeedetail-data_json-position_group_text').val(res.position_group_text)
                        $('#employeedetail-data_json-position_type').val(res.position_type)
                        $('#employeedetail-data_json-position_type_text').val(res.position_type_text)

                        $('.position-group-label').text(res.position_group_text)
                        $('.position-type-label').text(res.position_type_text)
                        if(res.position_type_text == 'ข้าราชการ'){
                            $('#employeedetail-data_json-position_level').prop('disabled', false);
                        }else{
                            $('#employeedetail-data_json-position_level').prop('disabled', true);
                            $('#employeedetail-data_json-position_level').val('').trigger('change');
                            $('#employeedetail-data_json-position_level_text').val('')
                        }
                    }
                });
         }",

    ],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'allowClear' => true,
        'minimumInputLength' => 1,
        'ajax' => [
            'url' => Url::to(['/depdrop/position-list']),
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
])->label('ชื่อตำแหน่ง')?>

        <div class="mb-3">
            <?php if (isset($model->data_json['position_group_text'])): ?>
            <label class=" test badge rounded-pill text-primary-emphasis bg-success-subtle">กลุ่ม : <i
                    class="fa-solid fa-layer-group text-success me-1"></i><span
                    class="position-group-label"><?=$model->data_json['position_group_text']?></span></label>
            <?php else: ?>
            <label class=" test badge rounded-pill text-primary-emphasis bg-success-subtle">กลุ่ม : <i
                    class="fa-solid fa-layer-group text-success me-1"></i><span
                    class="position-group-label"></span></label>
            <?php endif;?>
            <?php if (isset($model->data_json['position_type_text'])): ?>
            <label class="badge rounded-pill text-primary-emphasis bg-primary-subtle me-1">ประเภ : <i
                    class="fa-solid fa-tags ext-success text-primary"></i> <span
                    class="position-type-label"><?=$model->data_json['position_type_text']?></span></label>
            <?php else: ?>
            <label class="badge rounded-pill text-primary-emphasis bg-primary-subtle me-1">ประเภ : <i
                    class="fa-solid fa-tags ext-success text-primary"></i> <span
                    class="position-type-label"></span></label>
            <?php endif;?>
        </div>
    </div>


    <div class="col-3">
        <?php echo $form->field($model, 'data_json[position_number]')->textInput()->label('เลขประจำตำแหน่ง') ?>

    </div>

    <div class="col-3">
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
        'disabled' => (isset($model->data_json['position_type_text']) && $model->data_json['position_type_text'] == 'ข้าราชการ') ? false : true

    ],
])->label('ระดับตำแหน่ง')?>
    </div>


    <div class="col-3">
        <?=$form->field($model, 'data_json[expertise]')->widget(Select2::classname(), [
    'data' => $model->employee->ListExpertise(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => false,
        'maximumInputLength' => 10,
    ],
])->label('ความเชี่ยวชาญ')?>
    </div>
    <div class="col-6">
        <?=$form->field($model, 'data_json[department]')->widget(\kartik\tree\TreeViewInput::className(), [
    'name' => 'department',
    'query' => Organization::find()->addOrderBy('root, lft'),
    'value' => 1,
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => false,
    'options' => ['disabled' => false],
])->label('หน่วยงานภายในตามโครงสร้าง');?>

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
        <?=$form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ')?>
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
//     var thaiYear = function (ct) {
//         var leap=3;
//         var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];
//         if(ct){
//             var yearL=new Date(ct).getFullYear()-543;
//             leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;
//             if(leap==2){
//                 dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];
//             }
//         }
//         this.setOptions({
//             i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,
//         })
//     };

//     $("#employeedetail-data_json-education_begin").datetimepicker({
//         timepicker:false,
//         format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
//         lang:'th',  // แสดงภาษาไทย
//         onChangeMonth:thaiYear,
//         onShow:thaiYear,
//         yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
//         closeOnDateSelect:true,
//     });

//     $("#employeedetail-data_json-education_end").datetimepicker({
//         timepicker:false,
//         format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
//         lang:'th',  // แสดงภาษาไทย
//         onChangeMonth:thaiYear,
//         onShow:thaiYear,
//         yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
//         closeOnDateSelect:true,
//     });



JS;
$this->registerJS($js, View::POS_END)
?>