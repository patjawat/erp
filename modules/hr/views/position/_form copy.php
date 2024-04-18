<?php

use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\widgets\Select2;
use app\components\AppHelper;
use iamsaint\datetimepicker\Datetimepicker;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use app\modules\hr\models\Organization;
use app\models\Categorise;
use app\components\CategoriseHelper;
/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var yii\widgets\ActiveForm $form */


$formatJs = <<< 'JS'
var formatRepo = function (repo) {
    if (repo.loading) {
        return repo.text;
    }
    console.log(repo);
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

?>

<div class="categorise-form">

    <?php $form = ActiveForm::begin(['id' => 'form-position']); ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>

    <div class="row">
    <div class="col-3">
        <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    </div>
    <div class="col-9">

    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อตำแหน่ง') ?>


    </div>
    <div class="col-3">

    <?php 
     $initPositionName = ArrayHelper::map(CategoriseHelper::Categorise('position_name'), 'code', function($model){
        return $this->render('@app/modules/hr/views/position/poaition_ajax_template',['model' => $model]);
    });
    echo $form->field($model, 'data_json[position_group]')->widget(Select2::classname(), [
    'initValueText'=> $initPositionName,
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'allowClear' => true,
        'minimumInputLength' => 1,
        'ajax' => [
            'url' => Url::to(['/depdrop/position-group-list']),
            'dataType' => 'json',
            'delay' => 250,
            'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
            'processResults' => new JsExpression($resultsJs),
            'cache' => true
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('formatRepo'),
        'templateSelection' => new JsExpression('formatRepoSelection'),
    ],
])->label('ชื่อกลุ่ม') ?>
    </div>
    <div class="col-12">


    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    </div>
    </div>
  



    <div class="form-group d-flex justify-content-center">
    <?= AppHelper::btnsave() ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

$js = <<<JS

 $('#form-position').on('beforeSubmit', function (e) {
    var form = $(this);
    console.log('Submit');
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                $.pjax.reload({container:response.container, history:false,url:response.url});    
                $('#main-modal').modal('hide');
                success()             
            }
        }
    });
    return false;
});

function saveSuccess(url){
console.log('succesas');
    $.ajax({
        type: "get",
        url: url,
        dataType: "json",
        success: function (response) {
            $('#main-modal').modal('show');
            $('#main-modal-label').html(response.title);
            $('.modal-body').html(response.content);
            $('.modal-footer').html(response.footer);
            $(".modal-dialog").removeClass('modal-sm modal-md modal-lg');
            $(".modal-dialog").addClass('modal-lg');
        }
    });
}
    


JS;
$this->registerJS($js, View::POS_END)
    ?>
