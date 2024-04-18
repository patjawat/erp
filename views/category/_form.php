<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\widgets\Select2;
use app\models\CategoryType;
/* @var $this yii\web\View */
/* @var $model app\models\Category */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?=
$form->field($model, 'category_type')->widget(Select2::classname(), [
    'data' =>  ArrayHelper::map(CategoryType::find()->where(['type_name' => $type->type_name])->all(),'id','title'),
    'options' => ['placeholder' => 'ท่าวัดความดันโลหิต'],
    'pluginOptions' => [
        'allowClear' => true,
    ],

]);
?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-check"></i> บันทึก', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
