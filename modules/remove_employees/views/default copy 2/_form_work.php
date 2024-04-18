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
<!-- Row -->


<div class="row">
    <div class="col-12">
        <?= $form->field($model, 'address')->textarea(['maxlength' => true,'style'=> 'height:135px'])->label('ที่อยู่ตามบัตรประชาชน') ?>

    </div>
    <div class="col-12">
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
                                                $('#employees-province').val(data.province_id)
                                                $('#employees-amphure').val(data.amphure_id)
                                                $('#employees-district').val(data.district_id)
                                                $('.address2').html('ที่อยู่ : '+data.text)
                                                $('#employees-data_json-address2').val(data.text)
                                             }",
                                        ]
                                    ]);
                                    ?>
    </div>


    <div class="col-12">
        <div class="alert alert-primary mt-3" role="alert">
            <span class="address2"><?=isset($model->data_json['address2']) ? $model->data_json['address2'] : '-'?></span>
        </div>
    </div>
    <div class="col-6">
                <?= $form->field($model, 'data_json[nationality]')->label('สัญชาติ') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[religion]')->label('ศาสนา') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'phone')->textInput(['type' => 'number'])->label('โทรศัพท์') ?>

            </div>
    <div class="col-6">
                <?= $form->field($model, 'email')->label('อีเมลย์') ?>
            </div>
    
          

</div>

<?=$form->field($model, 'province')->hiddenInput(['maxlength' => true])->label(false) ?>
<?=$form->field($model, 'amphure')->hiddenInput(['maxlength' => true])->label(false)  ?>
<?=$form->field($model, 'district')->hiddenInput(['maxlength' => true])->label(false)  ?>
<?=$form->field($model, 'data_json[address2]')->hiddenInput(['maxlength' => true])->label(false)  ?>
