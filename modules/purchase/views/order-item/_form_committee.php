<?php

use app\modules\hr\models\Employees;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
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

?>
<style>
    .select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #fff;
}
:not(.form-floating) > .input-lg.select2-container--krajee-bs5 .select2-selection--single, :not(.form-floating) > .input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 12px) !important;
}
.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #3F51B5;
}
</style>
<div class="order-form">

<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/order-item/validator'],
]); ?>

    <?php
        $initEmployee = isset($model->data_json['employee_id']) ? Employees::find()->where(['id' => $model->data_json['employee_id']])->one()->getAvatar(false) : null;
        // echo $initEmployee->getAvatar(false);
        echo $form->field($model, 'data_json[employee_id]')->widget(Select2::classname(), [
            'initValueText' => $initEmployee,
            'id' => 'boardId',
            'options' => ['placeholder' => 'เลือก ...'],
            'size' => Select2::LARGE,
            'pluginEvents' => [
                'select2:unselect' => 'function() {
            $("#order-data_json-board_fullname").val("")

         }',
                'select2:select' => 'function() {
                var fullname = $(this).select2("data")[0].fullname;
                  var position_name = $(this).select2("data")[0].position_name_text;
                $("#order-data_json-emp_fullname").val(fullname)
                $("#order-data_json-emp_position").val(position_name)
               
         }',
            ],
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
                'allowClear' => true,
                'minimumInputLength' => 1,
                'ajax' => [
                    'url' => Url::to(['/depdrop/employee-by-id']),
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                    'processResults' => new JsExpression($resultsJs),
                    'cache' => true,
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                'templateResult' => new JsExpression('formatRepo'),
            ],
        ])->label('ชื่อ')
    ?>

<?php
echo $form->field($model, 'data_json[committee]')->widget(Select2::classname(), [
    'data' => $model->ListBoard(),
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
    ],
    'pluginEvents' => [
        'select2:select' => "function(result) { 
                            var data = \$(this).select2('data')[0].text;
                            \$('#order-data_json-committee_name').val(data)
                        }",
    ]
])->label('คณะกรรมการ');
?>
    <?php //  $form->field($model, 'action')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[committee_name]')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[emp_fullname]')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[emp_position]')->hiddenInput(['maxlength' => true])->label(false) ?>
   
    
    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<< JS

    \$('#boardId').val(8).trigger('change');
    \$('#form-order').on('beforeSubmit', function (e) {
        var form = \$(this);
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: async function (response) {
                form.yiiActiveForm('updateMessages', response, true);
                if(response.status == 'success') {
                    $("#main-modal").modal("toggle");
                    success()
                    // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});      
                    await $.pjax.reload({container:'#purchase-container', history:false,timeout: false});                         
                }
            }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END)
?>

