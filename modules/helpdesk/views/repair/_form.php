<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use softark\duallistbox\DualListbox;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);
?>
<div class="repair-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-repair',
        'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
        'validationUrl' =>['/helpdesk/repair/create-validator']
    ]); ?>

<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[technician_name]')->hiddenInput()->label(false) ?>
    
    <?php if($model->isNewRecord):?>
        <?= $form->field($model, 'data_json[create_name]')->hiddenInput(['value' => $emp->fullname])->label(false) ?>
        <?= $form->field($model, 'data_json[repair_status]')->hiddenInput(['value' => 'ร้องขอ'])->label(false) ?>
        <?= $form->field($model, 'data_json[send_type]')->hiddenInput(['general' => 'ทั่วไป','asset' => 'ครุภัณฑ์'],['inline'=>false,'custom' => true])->label(false) ?>
        <?= $form->field($model, 'data_json[title]')->textInput(['placeholder' => 'ระบุอาการเสีย...'])->label('อาการเสีย') ?>
        <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5,'placeholder' => 'ระบุรายละเอียดเพิ่มเติมของอาการเสีย...'])->label('เพิ่มเติม') ?>
        <?= $form->field($model, 'data_json[location]')->textInput(['placeholder' => 'ระบุสถานที่เกิดเหตุ...'])->label('สถานที่') ?>
        <?= $form->field($model, 'data_json[urgency]')->radioList($model->listUrgency(),['inline'=>true,'custom' => true])->label('ความเร่งด่วน') ?>
        <?php else:?>
            <?= $form->field($model, 'data_json[urgency]')->hiddenInput($model->listUrgency(),['inline'=>true,'custom' => true])->label(false) ?>
            <?= $form->field($model, 'data_json[location]')->hiddenInput(['placeholder' => 'ระบุสถานที่เกิดเหตุ...'])->label(false) ?>
            

            <div class="row">
                <div class="col-6">
                    <div class="bg-danger-subtle p-4 rounded d-flex justify-content-between">
                        <?= $form->field($model, 'data_json[send_type]')->radioList(['general' => 'ทั่วไป','asset' => 'ครุภัณฑ์'],['inline'=>false,'custom' => true])->label('ส่งซ่อม') ?>
                        
                        <i class="fa-solid fa-triangle-exclamation fs-1 text-danger"></i>
                    </div>
                    
                </div>
                <div class="col-6">
        <?= $form->field($model, 'data_json[repair_type]')->radioList(['ซ่อมภายใน' => 'ซ่อมภายใน','ซ่อมภายนอก' => 'ซ่อมภายนอก'],['inline'=>true,'custom' => true])->label('ประเภทารซ่อม') ?>
            <?= $form->field($model, 'data_json[repair_status]')->widget(Select2::classname(), [
    'data' => [
        'ร้องขอ' => 'ร้องขอ',
        'รับเรื่อง' => 'รับเรื่อง',
        'เสร็จสิ้น' => 'เสร็จสิ้น'
    ],
    'options' => ['placeholder' => 'ระบุสถานะการซ่อม ...'],
    'pluginOptions' => [
        'allowClear' => true
    ],
])->label('สถานะ') ?>
        </div>
    </div>

    <?= $form->field($model, 'data_json[repair_note]')->textArea(['rows' => 6,'placeholder' => 'ระบุการแก้ไข/อื่นๆ...'])->label('การแก้ไข') ?>



    <?php
    $options = [
        'multiple' => true,
        'size' => 8,
    ];
    // $items = ['1' => 'Item1', '2' => 'Item2', '3' => 'Item3',];
    $items = ArrayHelper::map(Employees::find()->limit(10)->all(),'fullname',function($model){
        return $model->fullname;
    });


    echo DualListbox::widget([
        'model' => $model,
        'attribute' => 'data_json[join]',
        'items' => $model->listTecName(),
        'options' => $options,
        'clientOptions' => [
            'moveOnSelect' => false,
            'selectedListLabel' => 'ช่างผู้ร่วมงาน',
            'nonSelectedListLabel' => 'ช่างเทคนิค',
        ],
    ]);
?>

    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'data_json[price]')->textInput(['placeholder' => 'ระบุมูลค่าการซ่อมถ้ามี','type' => 'number'])->label('มูลค่าการซ่อม') ?>
        </div>

    </div>
    <?=$model->Upload('repair')?>

<?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[note]')->hiddenInput()->label(false) ?>
    <?php endif;?>
    


    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS
 
$('#form-repair').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                success()
                try {
                    loadRepairHostory()
                } catch (error) {
                    
                }
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js)
?>