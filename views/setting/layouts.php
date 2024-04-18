<?php


use app\components\AppHelper;
use app\components\SiteHelper;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use kartik\widgets\Typeahead;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;
use app\modules\filemanager\components\FileManagerHelper;

$this->title = "ตั้งค่า Layout";
?>
<h1 class="text-center"><i class="bi bi-building-fill-check fs-1"></i> <?=$this->title?></h1>

<div class="form-company mt-5">
    <?php $form = ActiveForm::begin(['id' => 'form-company']); ?>

    
<div class="row d-flex justify-content-center">
<div class="col-4">
    <?= $form->field($model, 'data_json[layout]')->textInput()->label('Layout') ?>
</div>

</div>

<div class="form-group d-flex justify-content-center">
        <?=AppHelper::BtnSave()?>
    </div>

    <?php ActiveForm::end(); ?>

    </div>