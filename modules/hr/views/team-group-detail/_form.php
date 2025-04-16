<?php

use yii\web\View;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use unclead\widgets\MultipleInput;
use app\modules\hr\models\Employees;
use unclead\widgets\MultipleInputColumn;


/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroupDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="team-group-detail-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-detail',
    ]); ?>

    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
    <div class="row">
         <div class="col-2"><?= $form->field($model, 'thai_year')->textInput()->label('พ.ศ.') ?></div>
         <div class="col-10"><?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อคำสั่ง') ?></div>
    </div>

    <?php echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                'data' => $model->listDocument(),
                'options' => ['placeholder' => 'เลือกหนังสือคำสั่ง...'],
                'pluginOptions' => [
                    'dropdownParent' => '#main-modal',
                    'allowClear' => true,
                    'multiple' => false,
                ],
            ])->label('หนังสือคำสั่ง');
        ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>


    <div class="form-group mt-3 d-flex justify-content-center gap-3">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS

    // เรียกใช้ function handleFormSubmit
    handleFormSubmit('#form-detail', null, function(response) {
    location.reload();
    });

JS;
$this->registerJS($js, View::POS_END);
?>
