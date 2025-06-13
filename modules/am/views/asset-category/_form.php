<?php
use yii\helpers\Html;
use yii\helpers\Json;
// use yii\bootstrap5\ActiveForm;
use app\models\Categorise;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use unclead\multipleinput\MultipleInput;
?>

<?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/am/asset-category/validator']
    ]); ?>

<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

<div class="row">
<div class="col-2">
          <?=$form->field($model, 'code')->textInput()->label("รหัส")?>
        </div>
        <div class="col-10">
    <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อของใหวดหมู่...'])->label("ชื่อหมวดหมู่") ?>
</div>
</div>
<div class="row">
    <div class="col-12">
        <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
                   'data' => $model->listAssetType(),
                    'options' => ['placeholder' => 'ระบุประเภทรัพย์สิน...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ], 
                ])->label("ประเภททรัพย์สิน");
                ?>
    </div>
</div>

<div class="form-group mt-3 d-flex justify-content-center gap-2">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summitxx"]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark"></i> ปิด</button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJs($js);
?>