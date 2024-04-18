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
    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label(false) ?>
<?php if($model->name !== "position_type"):?>
    <?php 
// echo $form->field($model, 'category_id')->widget(Select2::classname(), [
//     'data' => $model->name == "position_name" ? CategoriseHelper::PositionGroup() : CategoriseHelper::PositionType(),
//     'options' => ['placeholder' => 'เลือก ...'],
//     'pluginOptions' => [
//         'dropdownParent' => '#main-modal',
//         'allowClear' => true
//     ],
// ])->label('ชื่อกลุ่มงาน');
?>
<?php endif;?>

    <div class="row">

    <div class="col-12">
<?php if($model->name == "position_type"):?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อประเภท') ?>
<?php endif;?>

<?php if($model->name == "position_group"):?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อกลุ่ม') ?>
<?php endif;?>

<?php if($model->name == "position_name"):?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อตำแหน่ง') ?>
<?php endif;?>

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
