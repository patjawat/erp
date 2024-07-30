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
]); ?>

<div
    class="card"
>
    <?= Html::img($product->ShowImg(), ['class' => 'card-img-top']) ?>
    <div class="card-body">
        
<div class="row">
    <div class="col-8">
        <?= $form->field($model, 'qty')->textInput()->label('จำนวน'); ?>
    </div>
    <div class="col-4">
        <div class="mb-3 highlight-addon field-order-qty has-success">
            <label class="form-label has-star" for="order-qty">หน่วย</label>
            <input type="text" class="form-control is-valid" value="<?=$model->data_json['asset_item_unit_name']?>" disabled=true>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="col-8">
        <?= $form->field($model, 'price')->textInput()->label('ราคา'); ?>
    </div>
    <div class="col-4">
        <div class="mb-3 highlight-addon field-order-qty has-success">
            <label class="form-label has-star" for="order-qty">ราคา</label>
            <input type="text" class="form-control is-valid" value="บาท" disabled=true>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</div>
    </div>
</div>



<?= $form->field($model, 'pr_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'pq_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'po_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'category_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'group_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'asset_item')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_type_name]')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_unit_name]')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_name]')->hiddenInput()->label(false); ?>

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