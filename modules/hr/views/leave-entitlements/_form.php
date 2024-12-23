<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveEntitlements $model */
/** @var yii\widgets\ActiveForm $form */

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
    :not(.form-floating) > .input-lg.select2-container--krajee-bs5 .select2-selection--single, :not(.form-floating) > .input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 2px);
    padding: 4px;
    font-size: 1.0rem;
    line-height: 1.5;
    border-radius: .3rem;
}

.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #e5e5e5;
    color: #fff;
}
</style>
<div class="leave-entitlements-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <?php
                try {
                    $initEmployee = Employees::find()->where(['id' => $model->emp_id])->one()->getAvatar(false);
                } catch (\Throwable $th) {
                    $initEmployee = '';
                }
                echo $form->field($model, 'emp_id')->widget(Select2::classname(), [
                    'initValueText' => $initEmployee,
                    'options' => ['placeholder' => 'เลือกบุคลากร...'],
                    'size' => Select2::LARGE,
                    // 'theme' => Select2::THEME_MATERIAL,
                    'pluginEvents' => [
                        'select2:unselect' => 'function() {
                            $("#leave-data_json-leave_work_send").val("")
                            }',
                                'select2:select' => 'function() {
                                    var fullname = $(this).select2("data")[0].fullname;
                                    $("#leaveentitlements-position_type_id").val($(this).select2("data")[0].position_type_id)
                                    $("#leaveentitlements-year_of_service").val($(this).select2("data")[0].years_of_service)
                                    $("#leaveentitlements-month_of_service").val($(this).select2("data")[0].month_of_service)
                                    console.log($(this).select2("data")[0])
                                    
                            }',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
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
                ])->label(false)
                ?>
            
<div class="row">
<div class="col-6">
    <?= $form->field($model, 'year_of_service')->textInput() ?>
</div>
    <div class="col-6">
        <?= $form->field($model, 'thai_year')->textInput(['disabled' => false]) ?>
    </div>
</div>


<?= $form->field($model, 'days')->textInput() ?>

<?= $form->field($model, 'month_of_service')->hiddenInput(['value' => 0])->label(false) ?>
    <?= $form->field($model, 'position_type_id')->hiddenInput(['maxlength' => true])->label(false) ?>

    <?= $form->field($model, 'leave_type_id')->hiddenInput(['value' =>'LT4'])->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center gap-3">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
$('#form').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (res) {
            form.yiiActiveForm('updateMessages', res, true);
            if (form.find('.invalid-feedback').length) {
                // validation failed
            } else {
                // validation succeeded
            }
            if(res.status == 'error') {
                Swal.fire({
                      icon: 'error',
                      title: 'เกิดข้อผิดพลาด',
                      text: res.message,
                    })
            }
            if(res.status == 'success') {
                // alert(data.status)
                console.log(res.container);
                // $('#main-modal').modal('toggle');
                success()
                 $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            }
        }
    });
    return false;
});

JS;
$this->registerJs($js);
?>