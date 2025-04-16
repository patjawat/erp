<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroup $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="team-group-form">

    <?php $form = ActiveForm::begin(['id' => 'form-team']); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS

// เรียกใช้ function handleFormSubmit
handleFormSubmit('#form-team', null, function(response) {
  location.reload();
});

JS;
$this->registerJS($js, View::POS_END);
?>
