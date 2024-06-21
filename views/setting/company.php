<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\filemanager\components\FileManagerHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;

$this->title = 'ตั้งค่าองค์กร';
?>
<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->

<div class="card">
    <div class="card-body">

        <h4 class="card-title"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h4>
        <input type="file" id="my_file" style="display: none;" />

        <a href="#" class="select-photo">
            <?php if ($model->isNewRecord): ?>
            <?= Html::img('@web/img/placeholder_cid.png', ['class' => 'avatar-profile object-fit-cover rounded shadow', 'style' => 'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) ?>
            <?php else: ?>

            <?php // Html::img($model->showAvatar(),['class' => 'avatar-profile object-fit-cover rounded shadow','style' =>'margin-top: 25px;max-width: 135px;max-height: 135px;    width: 100%;height: 100%;']) ?>
            <?php endif ?>
        </a>

        <?php $form = ActiveForm::begin(['id' => 'form-company']); ?>
        <div class="row d-flex justify-content-center">
            <div class="col-4">
                <?= $form->field($model, 'data_json[company_name]')->textInput()->label('ชื่อหน่วยงาน') ?>
                <?= $form->field($model, 'data_json[phone]')->textInput()->label('โทรศัพท์') ?>
                <div class="d-flex justify-content-between gap-3">
                    <?= $form->field($model, 'data_json[director_name]')->textInput()->label('ผู้อำนวยการ') ?>
                    <?= $form->field($model, 'data_json[director_position]')->textInput()->label('ตำแหน่ง') ?>
                </div>
                <?= $form->field($model, 'data_json[website]')->textInput()->label('เว็บไซต์') ?>
            </div>

            <div class="col-4">
                <?= $form->field($model, 'data_json[province]')->textInput(['placeholder' => 'ระบุ เช่น จังหวัดขอนแก่น'])->label('จังหวัด') ?>
                <?= $form->field($model, 'data_json[hoscode]')->textInput()->label('รหัสโรงพยาบาล') ?>
                <?= $form->field($model, 'data_json[email]')->textInput()->label('อีเมล') ?>
                <?= $form->field($model, 'data_json[fax]')->textInput()->label('แฟกซ์') ?>
            </div>
            <div class="col-8">
                <?= $form->field($model, 'data_json[address]')->textArea(['style' => 'height:100px'])->label('ที่อยู่') ?>
                <?= $model->upload($model->ref, 'company_logo') ?>
            </div>
        </div>

        <div class="form-group d-flex justify-content-center">
            <?= AppHelper::BtnSave() ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
    </div>