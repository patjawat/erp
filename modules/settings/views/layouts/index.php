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
use kartik\editors\Summernote;
use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;
use app\modules\filemanager\components\FileManagerHelper;

$this->title = 'ตั้งค่า Layouts';


?>


<!-- <h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> ข้อมูลองค์กร</h1> -->
<div class="container">
<div class="card">
    <div class="card-body">
        <h4 class="card-title"><i class="bi bi-building-fill-check fs-1"></i> Layouts</h4>
        <?php $form = ActiveForm::begin(['id' => 'form-layouts']); ?>
        <?= $form->field($model, 'data_json[layout]')->radioList(['horizontal' => 'horizontal','vertical' => 'vertical'])->label(false) ?>

        <div class="form-group d-flex justify-content-center">
            <?= AppHelper::BtnSave() ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
</div>

