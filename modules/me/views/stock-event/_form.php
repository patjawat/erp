<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
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

<div class="stock-event-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

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
    
       <?=$form->field($model, 'warehouse_id')->widget(Select2::classname(), [
                                        'data' =>ArrayHelper::map(
                                                    Warehouse::find()->where(['warehouse_type' => 'main'])
                                                        ->all(),
                                                    'id',
                                                    'warehouse_name'
                                                ),
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
                                    ])->label('คลังที่ต้องการเบิก');?>


<div class="form-group mt-3 d-flex justify-content-center gap-2">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summitxx"]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark"></i> ปิด</button>
</div>
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>