<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */
$formWarehouse = Yii::$app->session->get('selectMainWarehouse');
$toWarehouse = Yii::$app->session->get('warehouse');


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

<div class="stock-in-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/inventory/stock-in/create-validator'],
    ]); ?>


<style>
.col-form-label {
    text-align: end;
}
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

<div class="row">
<div class="col-6">

<?php
       echo $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
                                        'data' => $model->listWareHouseMain(),
                                        'options' => ['placeholder' => 'เลือกคลังที่ต้องการเบิก'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 

                                            }",
                                            "select2:select" => "function() {
                                               console.log($(this).val());
                                        }",],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'dropdownParent' => '#main-modal',
                                        ],
                                    ])->label('คลังวัสดุหลัก');
                                    
                                    ?>
</div>
<div class="col-6">

<?php
       echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
                                        'data' => $model->listWareHouseSub(),
                                        'options' => ['placeholder' => 'เลือกคลังที่ต้องการเบิก'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 

                                            }",
                                            "select2:select" => "function() {
                                               console.log($(this).val());
                                        }",],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                            'dropdownParent' => '#main-modal',
                                        ],
                                    ])->label('คลังย่อย');
                                    
                                    ?>
</div>
</div>
<?php

try {
    //code...
    $initEmployee =  Employees::find()->where(['id' => $model->checker])->one()->getAvatar(false);
} catch (\Throwable $th) {
    $initEmployee = '';
}
        echo $form->field($model, 'checker')->widget(Select2::classname(), [
            'initValueText' => $initEmployee,
            'options' => ['placeholder' => 'เลือก ...'],
            'size' => Select2::LARGE,
            'pluginEvents' => [
                'select2:unselect' => 'function() {
                $("#order-data_json-board_fullname").val("")

         }',
                'select2:select' => 'function() {
                var fullname = $(this).select2("data")[0].fullname;
                var position_name = $(this).select2("data")[0].position_name;
                $("#order-data_json-board_fullname").val(fullname)
                $("#order-data_json-position_name").val(position_name)
               
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
        ])->label('ผู้เห็นชอบ')
    ?>

<?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5])->label('เหตุผล');?>
<?php //  $form->field($model, 'data_json[checker_confirm]')->textInput(['value' => ''])->label('เหตุผล');?>

    <?php echo $form->field($model, 'name')->hiddenInput()->label(false); ?>
    <?php echo $form->field($model, 'data_json[checker_confirm]')->hiddenInput()->label(false); ?>
    <?php echo $model->isNewRecord ? $form->field($model, 'category_id')->hiddenInput()->label(false) : null; ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS

    $('#form').on('beforeSubmit',  function (e) {
        e.preventDefault();
         Swal.fire({
                title: 'ยืนยัน',
                text: 'เบิกวัสดุ',
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "ใช่, ยืนยัน!",
                cancelButtonText: "ยกเลิก",
                }).then(async (result) => {
                if (result.value == true) {
                    var form = \$(this);
                    $.ajax({
                        url: form.attr('action'),
                        type: 'post',
                        data: form.serialize(),
                        dataType: 'json',
                        success: async function (response) {
                                form.yiiActiveForm('updateMessages', response, true);
                                if(response.status == 'error') 
                                {
                                    Swal.fire({
                                        title: "เกิดข้อผิดพลาดบางอย่าง!",
                                        text: response.message,
                                        icon: "error"
                                        });
                                    }
                                    if(response.status == 'success') {
                                            location.reload()
                                            // closeModal()
                                            // success()
                                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                        }
                                    }
                            });
                        return false;
                        
                    }
                    return false;
                });
                return false;
                
    });
JS;
$this->registerJS($js);
?>