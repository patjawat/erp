<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;

/** @var yii\web\View $this */
/** @var app\models\Employees $model */
/** @var yii\widgets\ActiveForm $form */
?>
<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-12">
        <?php
                $prefix = AppHelper::prefixName();
                echo $form->field($model, 'prefix')->widget(Select2::classname(), [
                    'data' => $prefix,
                    'options' => ['placeholder' => 'เลือกคำนำหน้า ...'],
                    'pluginOptions' => [
                        'tags' => true,
                        'maximumInputLength' => 10
                    ],
                ])->label('คำนำหน้า');?>

    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?= $form->field($model, 'fname')->textInput(['autofocus' => true])->label('ชื่อ') ?>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-12">
        <?= $form->field($model, 'lname')->textInput(['autofocus' => true])->label('นามสกุล') ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-12">
        <?php
                $prefix = AppHelper::prefixName();
                echo $form->field($model, 'data_json[prefix_en]')->widget(Select2::classname(), [
                    'data' => $prefix,
                    'options' => ['placeholder' => 'เลือกคำนำหน้า ...'],
                    'pluginOptions' => [
                        'tags' => true,
                        'maximumInputLength' => 10
                    ],
                ])->label('คำนำหน้า');?>

    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?= $form->field($model, 'fname_en')->textInput(['autofocus' => true])->label('ชื่อ(อังกฤษ)') ?>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-12">
        <?= $form->field($model, 'lname_en')->textInput(['autofocus' => true])->label('นามสกุล(อังกฤษ)') ?>
    </div>
</div>



<!-- Row -->
<div class="row">
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php
                $gender = ['ชาย','หญิง'];
                echo $form->field($model, 'gender')->widget(Select2::classname(), [
                    'data' => $gender,
                    'options' => ['placeholder' => 'เลือกคเพศ ...'],
                    'pluginOptions' => [
                        'tags' => true,
                        'maximumInputLength' => 10
                    ],
                ])->label('เพศ');?>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-12">

        <?=$form->field($model, 'birthday')->widget(Datetimepicker::className(),[
                                            'options' => [
                                                'timepicker' => false,
                                                'datepicker' => true,
                                                'mask' => '99/99/9999',
                                                'lang' => 'th',
                                                'yearOffset' => 543,
                                                'format' => 'd/m/Y', 
                                            ],
                                            ]);
                                        ?>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-12">
        <?=$form->field($model, 'cid')->widget(MaskedInput::className(),[
                'mask'=>'9-9999-99999-99-9'
            ])?>
    </div>
</div>
<!-- End Row -->




<!-- start row -->
<div class="row">
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?= $form->field($model, 'data_json[blood_group]')->label('หมู่โลหิต') ?>

    </div>
    <div class="col-lg-5 col-md-5 col-sm-12">
        <?= $form->field($model, 'data_json[hometown]')->label('ภูมิลำเนาเดิม') ?>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
        <?= $form->field($model, 'data_json[nationality]')->label('สัญชาติ') ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?= $form->field($model, 'data_json[ethnicity]')->label('เชื้อชาติ') ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?= $form->field($model, 'data_json[religion]')->label('ศาสนา') ?>
    </div>

    <div class="col-lg-3 col-md-3 col-sm-12">
        <?= $form->field($model, 'data_json[marital_status]')->label('สถานภาพสมรส') ?>
    </div>

    <div class="col-7">
        <?= $form->field($model, 'address')->textInput(['maxlength' => true])->label('ระบบุบ้านเลขที่ หมู่ที่ ') ?>

    </div>

    <div class="col-5">
        <?php
                                    $url = Url::to('/depdrop/address');
                                    echo $form->field($model, 'zipcode')->widget(Select2::className(), [
                                        'initValueText'=>'',//กำหนดค่าเริ่มต้น
                                        'options'=>['placeholder'=>'เลือกคำนำหน้า...'],
                                        'theme' => Select2::THEME_KRAJEE_BS5,
                                        'pluginOptions'=>[
                                            'dropdownParent' => '#main-modal',
                                            'allowClear'=>true,
                                            'minimumInputLength'=>4,//ต้องพิมพ์อย่างน้อย 3 อักษร ajax จึงจะทำงาน
                                            'ajax'=>[
                                                'url'=>$url,
                                                'dataType'=>'json',//รูปแบบการอ่านคือ json
                                                'data'=>new JsExpression('function(params) { return {q:params.term};}')
                                            ],
                                            'escapeMarkup'=>new JsExpression('function(markup) { return markup;}'),
                                            'templateResult'=>new JsExpression('function(prefix){ return prefix.text;}'),
                                            'templateSelection'=>new JsExpression('function(prefix) {return prefix.id;}'),
                                            
                                        ],
                                        'pluginEvents' => [
                                            "select2:select" => "function(result) { 
                                                var data = $(this).select2('data')[0]
                                                console.log(data);
                                                $('#profile-province').val(data.province_id)
                                                $('#profile-amphure').val(data.amphure_id)
                                                $('#profile-district').val(data.district_id)
                                                $('.address2').html('ที่อยู่ : '+data.text)
                                                $('#profile-data_json-address2').val(data.fulltext)
                                             }",
                                        ]
                                    ]);
                                    ?>
    </div>

<div class="col-lg-7 col-md-7 col-sm-12">
<div class="alert alert-primary mt-3" role="alert">
            <span
                class="address2"><?=isset($model->data_json['address2']) ? $model->data_json['address2'] : '-'?></span>
        </div>
</div>
</div>
<!-- End row -->

<!-- start row -->
<div class="row">
    <div class="col-6">
        <?= $form->field($model, 'email')->label('อีเมลย์') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'phone')->textInput(['type' => 'number'])->label('โทรศัพท์') ?>

    </div>
</div>
<!-- End row -->
<!-- Row -->
<div class="row">


</div>
<!-- End Row -->

<div class="row">
    <div class="col-12">

        <?=$form->field($model, 'province')->hiddenInput(['maxlength' => true])->label(false) ?>
        <?=$form->field($model, 'amphure')->hiddenInput(['maxlength' => true])->label(false)  ?>
        <?=$form->field($model, 'district')->hiddenInput(['maxlength' => true])->label(false)  ?>
        <?=$form->field($model, 'data_json[address2]')->hiddenInput(['maxlength' => true])->label(false)  ?>

    </div>
</div>