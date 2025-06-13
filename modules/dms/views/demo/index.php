<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<h2>อัปโหลด PDF</h2>
<?php $form = ActiveForm::begin(['action' => ['upload'], 'options' => ['enctype' => 'multipart/form-data']]); ?>
    <?= Html::fileInput('file') ?>
    <br><br>
    <?= Html::submitButton('Upload') ?>
<?php ActiveForm::end(); ?>
