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

    <div class="col-12">
        <?php
                $prefix = AppHelper::prefixName();
                echo $form->field($model, 'prefix')->widget(Select2::classname(), [
                    'data' => [
                        'นาย' => 'นาย',
                        'นาง' => 'นาง',
                        'นางสาว' => 'นางสาว',
                    ],
                    'options' => ['placeholder' => 'เลือก ...'],
                    'pluginOptions' => [
                        'tags' => true,
                        'maximumInputLength' => 10
                    ],
                ])->label('คำนำหน้า');?>




    </div>


        <div class="col-lg-6">
            <?= $form->field($model, 'fname')->textInput(['autofocus' => true])->label('ชื่อ') ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'lname')->textInput(['autofocus' => true])->label('นามสกุล') ?>
        </div>


        <div class="col-6">
            <?= $form->field($model, 'fname_en')->textInput(['autofocus' => true])->label('ชื่อ(อังกฤษ)') ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'lname_en')->textInput(['autofocus' => true])->label('นามสกุล(อังกฤษ)') ?>
        </div>



    <div class="col-6">

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
    <div class="col-6">
        <?=$form->field($model, 'cid')->widget(MaskedInput::className(),[
                'mask'=>'9-9999-99999-99-9'
            ])?>
    </div>
    <div class="col-6">
                <?= $form->field($model, 'data_json[hometown]')->label('ภูมิลำเนาเดิม') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[ethnicity]')->label('เชื้อชาติ') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[marital_status]')->label('สถานภาพสมรส') ?>
            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[blood_group]')->label('หมู่โลหิต') ?>

            </div>
</div>














