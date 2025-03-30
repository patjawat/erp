<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\DatePicker;
use app\modules\am\models\Asset;
use kartik\widgets\DateTimePicker;
use kartik\datecontrol\DateControl;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;

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

<div class="card border border-primary">
    <div class="card-body">
        <?=$model->isNewRecord ? $product->Avatar() : $model->product->Avatar()?>
    </div>
</div>

        <div class="row">
            <div class="col-8">
                <?= $form->field($model, 'qty')->textInput([
                'type' => 'number',
                'step' => '0.00001',
                'min' => '0',
                'max' => '99999.99999' // ปรับตามความเหมาะสม
            ])->label('จำนวน'); ?>
            </div>
            <div class="col-4">
                <div class="mb-3 highlight-addon field-order-qty has-success">
                    <label class="form-label has-star" for="order-qty">หน่วย</label>
                    <input type="text" class="form-control is-valid"
                        value="<?=$model->data_json['asset_item_unit_name']?>" disabled=true>
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



<?= $form->field($model, 'pr_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'pq_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'po_number')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'category_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'group_id')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'asset_item')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_type_name]')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_unit_name]')->hiddenInput()->label(false); ?>
<?= $form->field($model, 'data_json[asset_item_name]')->hiddenInput()->label(false); ?>

<div class="d-grid gap-2">
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