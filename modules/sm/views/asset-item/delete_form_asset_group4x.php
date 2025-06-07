<?php
use app\components\AppHelper;
use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\select2\Select2;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */
/** @var yii\widgets\ActiveForm $form */
$title = Yii::$app->request->get('title');
$code = Yii::$app->request->get('code');

?>
<div class="row">
    <div class="col-4">
        <input type="file" id="my_file" style="display: none;" />

        <a href="#" class="select-photo">
            <?php if($model->showImg() != false):?>
            <?= Html::img($model->showImg(),['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;']) ?>
            <?php else:?>
            <?=Html::img('@web/img/placeholder-img.jpg',['class' => 'avatar-profile object-fit-cover rounded','style' =>'max-width:100%;'])?>
            <?php endif;?>
        </a>
    </div>
    <div class="col-8">
        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อรายการ'])->label("ชื่อรายการ") ?>
            </div>
            <div class="col-4">
                <?=$form->field($model, 'code')->textInput(['placeholder' => "กำหนดระหัส"])->label("รหัสพัสดุ")?>
            </div>
        </div>

    </div>
</div>