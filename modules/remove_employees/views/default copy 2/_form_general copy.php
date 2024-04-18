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

    <div class="col-6">
        <!-- Start Row -->
        <div class="row">
            <div class="col-3">
                <?php
            
                $prefix = AppHelper::prefixName();
                echo $form->field($model, 'prefix')->widget(Typeahead::classname(), [
                'options' => ['placeholder' => 'ระบุ...'],
                'pluginOptions' => ['highlight'=>true],
                'defaultSuggestions' => $prefix,
                    'dataset' => [
                                [
                                                'local' => $prefix,
                                                'limit' => 10
                                            ]
                                        ]
                                    ])->label('คำนำหน้า');
                                    ?>
            </div>
            <div class="col-4">
                <?= $form->field($model, 'fname')->textInput(['autofocus' => true])->label('ชื่อ') ?>
            </div>
            <div class="col-5">
                <?= $form->field($model, 'lname')->textInput(['autofocus' => true])->label('นามสกุล') ?>
            </div>
        </div>
        <!-- End Row on Col6 -->


    </div>
    <!-- End Col-6 -->
    <!-- start Col-6 -->
    <div class="col-6">
        
        
        </div>
        <!-- End Col-6 -->


</div>
<!-- End Row -->


<!-- Row -->
<div class="row">

    <div class="col-6">
        <?=$form->field($model, 'cid')->widget(MaskedInput::className(),[
                'mask'=>'9-9999-99999-99-9'
            ])?>
    </div>
</div>
<!-- End Row -->

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
    <div class="col-6">
        <?php //  $form->field($model, 'zipcode')->textInput(['maxlength' => true]) ?>
        <?php
                                    $url = Url::to('/depdrop/address');
                                    echo $form->field($model, 'zipcode')->widget(Select2::className(), [
                                        'initValueText'=>'',//กำหนดค่าเริ่มต้น
                                        'options'=>['placeholder'=>'เลือกคำนำหน้า...'],
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
    <div class="col-6">
        <?= $form->field($model, 'address')->textInput(['maxlength' => true])->label('ระบบุบ้านเลขที่ หมู่ที่ ') ?>

    </div>
</div>
<!-- End Row -->

<div class="row">
    <div class="col-12">
        <div class="alert alert-primary" role="alert">
            <span
                class="address2"><?=isset($model->data_json['address2']) ? $model->data_json['address2'] : '-'?></span>
        </div>

        <?=$form->field($model, 'province')->hiddenInput(['maxlength' => true])->label(false) ?>
        <?=$form->field($model, 'amphure')->hiddenInput(['maxlength' => true])->label(false)  ?>
        <?=$form->field($model, 'district')->hiddenInput(['maxlength' => true])->label(false)  ?>
        <?=$form->field($model, 'data_json[address2]')->hiddenInput(['maxlength' => true])->label(false)  ?>

    </div>
</div>