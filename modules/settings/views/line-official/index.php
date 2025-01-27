<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\widgets\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use kartik\widgets\Typeahead;
use app\components\SiteHelper;
use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;
$domain = Url::base(true);

$this->title = 'ตั้งค่าองค์กร';

?>


<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->


        <?php $form = ActiveForm::begin(['id' => 'form-company']); ?>
        <div class="row d-flex justify-content-center">
            <div class="col-4">
            <div class="card">
    <div class="card-body">

        <?= $form->field($model, 'data_json[line_liff_register]')->textInput()->label('การลงทะเบียน '.$domain.'/line/auth/register') ?>
        <?= $form->field($model, 'data_json[line_liff_login]')->textInput()->label('เข้าสู่ระบบ '.$domain.'/line/auth/login') ?>
        <?= $form->field($model, 'data_json[line_liff_user_connect]')->textInput()->label('ERP User Connect') ?>
        <?= $form->field($model, 'data_json[line_liff_profile]')->textInput()->label('ERP Profile') ?>
                <?= $form->field($model, 'data_json[line_liff_dashboard]')->textInput()->label('ERP Dashbroad') ?>
                <?= $form->field($model, 'data_json[line_liff_app]')->textInput()->label('ERP App') ?>
                <?= $form->field($model, 'data_json[line_liff_service]')->textInput()->label('ERP Service') ?>
                <?php //  $form->field($model, 'data_json[line_liff_about]')->textInput()->label('ERP About') ?>
            </div>

            </div>
    </div>
                   </div>

        <div class="form-group d-flex justify-content-center">
            <?= AppHelper::BtnSave() ?>
        </div>

        <?php ActiveForm::end(); ?>
