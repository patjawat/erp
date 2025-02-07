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

$this->title = 'ตั้งค่าองค์กร';

?>


<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->


        <?php $form = ActiveForm::begin(['id' => 'form-company']); ?>
        <div class="row d-flex justify-content-center">
            <div class="col-4">
            <div class="card">
    <div class="card-body">
        
        <?= $form->field($model, 'data_json[line_channel_token]')->textInput()->label('Channel access token') ?>
    </div>
    
</div>
</div>
</div>

<div class="form-group d-flex justify-content-center gap-3">
    <?= AppHelper::BtnSave() ?>
    <a href="https://developers.line.biz/console" target="_blank" class="btn btn-primary">Line Developers</a>
    <?php echo Html::a('<i class="fa-solid fa-book"></i> คู่มือ',['//settings/line-messaging/document'],['class' => 'btn btn-warning'])?>
        </div>

        <?php ActiveForm::end(); ?>

