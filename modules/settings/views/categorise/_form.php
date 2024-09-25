<?php

use yii\helpers\Html;
use yii\web\View;
use yii\bootstrap5\ActiveForm;
use app\components\AppHelper;
use app\modules\hr\models\Organization;
use app\components\CategoriseHelper;
use kartik\widgets\Select2;



/** @var yii\web\View $this */
/** @var app\models\Categorise $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="categorise-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-categorise'
    ]); ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <div class="row">
        <div class="col-2">
            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>
        </div>

        <?php if($model->name == 'position_name'):?>
            <div class="col-5">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อตำแหน่งในสายงาน'); ?>
        </div>
        <div class="col-5">
            <?= $form->field($model, 'data_json[title_name]')->textInput(['maxlength' => true])->label('ชื่อสายงาน') ?>
        </div>
    <?php else:?>
        <div class="col-10">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        </div>
    <?php endif; ?>

       
    </div>
    <?php if($model->name == 'position_group'):?>
    <?=$form->field($model, 'category_id')->widget(Select2::classname(), [
    'data' => CategoriseHelper::PositionType(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'allowClear' => true
    ],
])->label('ประเภทบุคลากร');
?>
    <?php endif;?>

    <?php if($model->name == 'position_name'):?>
    <?=$form->field($model, 'category_id')->widget(Select2::classname(), [
    'data' => CategoriseHelper::PositionGroup(),
    'options' => ['placeholder' => 'เลือก ...'],
    'pluginOptions' => [
        'dropdownParent' => '#main-modal',
        'allowClear' => true
    ],
])->label('กลุ่มงาน');
?>
    <?php endif;?>

    <div class="d-flex justify-content-between mt-3">
        <?=Html::a('<i class="fa-solid fa-arrow-left"></i> หน้าแรก',['/hr/categorise','title' => 'ตั้งค่าตำแหน่ง'],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']])?>
        <div>
            <?= AppHelper::btnsave() ?>
            <?=Html::a('<i class="fa-solid fa-rotate-left"></i> ยกเลิก',['/hr/categorise','name' => 'position_type','title' => 'ตั้งค่าตำแหน่ง'],['class' => 'btn btn-secondary open-modal','data' => ['size' => 'modal-lg']])?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

$js = <<<JS

 $('#form-categorise').on('beforeSubmit', function (e) {
    var form = $(this);
    console.log('Submit');
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success:  function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
             $.pjax.reload({container:response.container}); 
             closeModal()
                success()                              
            }
        }
    });
    return false;
});



JS;
$this->registerJS($js, View::POS_END)
    ?>