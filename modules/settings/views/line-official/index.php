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
use app\modules\hr\models\Employees;

$this->title = 'ตั้งค่าองค์กร';

?>


<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->


        <?php $form = ActiveForm::begin(['id' => 'form-company']); ?>
        <div class="row d-flex justify-content-center">
            <div class="col-4">
            <div class="card">
    <div class="card-body">

                <?= $form->field($model, 'data_json[line_liff_dashboard]')->textInput()->label('ERP Dashbroad') ?>
                <?= $form->field($model, 'data_json[line_liff_register]')->textInput()->label('ERP Register') ?>
                <?= $form->field($model, 'data_json[line_liff_profile]')->textInput()->label('ERP Profile') ?>
                <?= $form->field($model, 'data_json[line_liff_app]')->textInput()->label('ERP App') ?>
                <?= $form->field($model, 'data_json[line_liff_service]')->textInput()->label('ERP Service') ?>
                <?= $form->field($model, 'data_json[line_liff_about]')->textInput()->label('ERP About') ?>
                <?= $form->field($model, 'data_json[line_liff_login]')->textInput()->label('ERP Login') ?>
                <?= $form->field($model, 'data_json[line_liff_user_connect]')->textInput()->label('ERP User Connect') ?>
            </div>

            </div>
    </div>
                   </div>

        <div class="form-group d-flex justify-content-center">
            <?= AppHelper::BtnSave() ?>
        </div>

        <?php ActiveForm::end(); ?>
