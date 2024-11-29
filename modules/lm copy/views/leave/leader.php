

       

<?php
use yii\helpers\Url;
use yii\web\JsExpression;
use app\modules\hr\models\Employees;
use yii\helpers\ArrayHelper;
use kartik\widgets\Select2;
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
<div class="row">
<div class="col-6">

<?php
                        try {
                            //code...
                            $initEmployee =  Employees::find()->where(['id' => $model->Approve()['leader']['id']])->one()->getAvatar(false);
                        } catch (\Throwable $th) {
                            $initEmployee = '';
                        }
                        // echo $initEmployee;
                        // echo $model->Approve()['leader']['avatar'];
                        echo $form->field($model, 'data_json[leader]')->widget(Select2::classname(), [
                            'initValueText' => $initEmployee,
                            // 'initValueText' => $model->Approve()['leader']['avatar'],
                            'options' => ['placeholder' => 'เลือกรายการ...'],
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
                        ])->label('หัวหน้างาน')
                        ?>



</div>
<div class="col-6">

<?php
                        try {
                            //code...
                            $initEmployee =  Employees::find()->where(['id' => $model->data_json['leader_group']])->one()->getAvatar(false);
                        } catch (\Throwable $th) {
                            $initEmployee = '';
                        }
                        echo $form->field($model, 'data_json[leader_group]')->widget(Select2::classname(), [
                            'initValueText' => $initEmployee,
                            'options' => ['placeholder' => 'เลือกรายการ...'],
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
                        ])->label('หัวหน้ากลุ่มงาน')
                        ?>

    
</div>
</div>
