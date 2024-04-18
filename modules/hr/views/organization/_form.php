<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\Select2;
use yii\bootstrap5\ActiveForm;
use app\components\CategoriseHelper;
use app\models\Categorise;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="categorise-form">

    <?php $form = ActiveForm::begin(['id' => 'form-organization']); ?>

    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?php if($model->name == "position_group"):?>
    <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
    'data' => CategoriseHelper::PositionType(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'tags' => true,
        'maximumInputLength' => 10,
    ],
])->label('ประเภท') ?>
<?php endif;?>
    <div class="row">
        <div class="col-3">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-9">
        <?php if($model->name == "position_group"):?>
            <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อกลุ่มงาน') ?>
            <?php else:?>
                <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อสายงาน') ?>
            <?php endif;?>
        </div>
        <div class="col-12">
            <?php if($model->name == "position_name"):?>
                <?= $form->field($model, 'data_json[sub_title]')->textInput(['maxlength' => true])->label('ชื่อในตำแหน่งสายงาน') ?>
                <?php endif;?>
        </div>
    </div>



    <div class="form-group d-flex justify-content-center mt-3">
    <?=app\components\AppHelper::BtnSave()?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

$js = <<<JS

 $('#form-organization').on('beforeSubmit', function (e) {
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
                // await  $.pjax.reload({container:response.container, history:false,url:response.url});  
                $.pjax.reload({container:response.container, history:false});  
                success()      
                $('#main-modal').modal('hide');                        
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