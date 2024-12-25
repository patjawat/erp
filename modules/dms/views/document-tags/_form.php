<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;

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
    'id' => 'formDocumentTags',
    // 'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    // 'validationUrl' => ['/dms/doc/validator'],
]); ?>

    <?php
        $initEmployee = isset($model->emp_id) ? Employees::find()->where(['id' => $model->emp_id])->one()->getAvatar(false) : null;
        echo $form->field($model, 'emp_id')->widget(Select2::classname(), [
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


<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>

<?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>

<?= $form->field($model, 'document_id')->hiddenInput(['maxlength' => true])->label(false) ?>
<?= $form->field($model, 'doc_number')->hiddenInput(['maxlength' => true])->label(false) ?>

<?= $form->field($model, 'doc_regis_number')->hiddenInput(['maxlength' => true])->label(false) ?>


<?= $form->field($model, 'status')->hiddenInput(['maxlength' => true])->label(false) ?>
    
    <div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<< JS

    \$('#formDocumentTags').on('beforeSubmit', function (e) {
        var form = \$(this);
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: async function (response) {
                console.log('submit');
                
                form.yiiActiveForm('updateMessages', response, true);
                if(response.status == 'success') {
                    $("#main-modal").modal("toggle");
                    success()
                    // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});      
                    await $.pjax.reload({container:response.container, history:false,timeout: false});                         
                }
            }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END)
?>

