<?php
use yii\web\View;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="development-detail-form">

    <?php $form = ActiveForm::begin(['id' => 'form-expense']); ?>

    <?= $form->field($model, 'development_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?php
                        echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                            'data' => $model->listExpenseType(),
                            'options' => ['placeholder' => 'เลือกค่าใช้จ่าย ...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                // 'dropdownParent' => '#main-modal',
                            ],
                            'pluginEvents' => [
                                'select2:select' =>  new JsExpression("function() {
                                   var data = $(this).select2('data')[0]

                                }"),

                            ]
                        ])->label('รายการค่าใช้จ่าย');
                        ?>
    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 3, 'maxlength' => true])->label('เพิ่มเติม') ?>
    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark"></i> ปิด</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<<JS
    // เรียกใช้ function handleFormSubmit
    handleFormSubmit('#form-expense', null, function(response) {
    location.reload();
    });

JS;
$this->registerJS($js, View::POS_END);
?>
