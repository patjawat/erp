<?php

use app\modules\am\models\Asset;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveField;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\DateTimePicker;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-order-item',
    // 'type' => ActiveForm::TYPE_HORIZONTAL,
    // 'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]
]); ?>

<?= Html::img($product->ShowImg(), ['class' => ' card-img-top ', 'style' => 'max-width:100%;height:280px;max-height: 280px;']) ?>
<div class="row d-flex justify-content-center">
    <div class="col-4">
        <?= $form->field($model, 'qty')->textInput()->label('จำนวน'); ?>
    </div>
    <div class="col-4">
        <div class="mb-3 highlight-addon field-order-qty has-success">
            <label class="form-label has-star" for="order-qty">หน่วย</label>
            <input type="text" class="form-control is-valid" value="ชิ้น" disabled=true>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="col-8">
        <?= $form->field($model, 'price')->textInput()->label('ราคา'); ?>
    </div>
</div>
<?= $form->field($model, 'pr_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'pq_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'po_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'category_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'group_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'asset_item')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_type_name]')->textInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_name]')->textInput()->label(false); ?>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

</div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

        \$('#form-order-item').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            closeModal()
                            success()
                            try {
                                // loadRepairHostory()
                            } catch (error) {
                                
                            }
                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


    JS;
$this->registerJS($js)
?>