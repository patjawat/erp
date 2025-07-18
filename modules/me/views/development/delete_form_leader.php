<?php
use yii\web\View;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;

$me = UserHelper::GetEmployee()->getInfo();

$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
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
    <div class="col-12">
        <?php
        try {
            if ($model->isNewRecord) {
                $initEmployee = Employees::find()->where(['id' => $model->Approve()['approve_1']['id']])->one()->getAvatar(false);
            } else {
                $initEmployee = Employees::find()->where(['id' => $model->data_json['approve_1']])->one()->getAvatar(false);
            }
        } catch (\Throwable $th) {
            $initEmployee = '';
        }

        echo $form->field($model, 'data_json[approve_1]')->widget(Select2::classname(), [
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
        ])->label('หัวหน้างาน');
        ?>
    </div>
    <div class="col-12">
        <?php
        try {
            $initEmployee = Employees::find()->where(['id' => $model->data_json['approve_2']])->one()->getAvatar(false);
        } catch (\Throwable $th) {
            $initEmployee = '';
        }

        echo $form->field($model, 'data_json[approve_2]')->widget(Select2::classname(), [
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
        ])->label('หัวหน้ากลุ่มงาน');
        ?>
    </div>
</div>
