<?php
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\View;

?>

<div class="row">


    <div class="col-lg-3 col-md-2 col-sm-12">
        <?php
            echo $form->field($model, 'data_json[prefix]')->widget(Select2::classname(), [
                'data' =>$model->employee->ListPrefixTh(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('คำนำหน้า');?>
    </div>
    <div class="col-lg-4 col-md-5 col-sm-12">
        <?= $form->field($model, 'data_json[fname]')->textInput(['autofocus' => true])->label('ชื่อ') ?>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-12">
        <?= $form->field($model, 'data_json[lname]')->textInput(['autofocus' => true])->label('นามสกุล') ?>
    </div>


    <div class="col-7">
        <?= $form->field($model, 'data_json[family_relation]')->widget(Select2::classname(), [
                'data' => $model->employee->ListFamilyRelation(),
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('ความสัมพันธ์') ?>


    </div>
    <div class="col-5">
        <?= $form->field($model, 'data_json[status]')->widget(Select2::classname(), [
                'data' => [
                    'มีชีวิตอยู่' => 'มีชีวิตอยู่',
                    'ถึงแก่กรรม' => 'ถึงแก่กรรม',
                ],
                'options' => ['placeholder' => 'เลือก ...'],
                'pluginOptions'=>[
                    'dropdownParent' => '#main-modal',
                    'tags' => true,
                    'maximumInputLength' => 10
                ],
            ])->label('สถานะ') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'data_json[phone]')->textInput()->label('โทรศัพท์') ?>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'data_json[address]')->textInput()->label('ที่อยู่') ?>

    </div>
</div>