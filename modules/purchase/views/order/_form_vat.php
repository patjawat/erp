<?php

use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveField;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\DateTimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>
<style>
.col-form-label {
    text-align: end;
}
</style>

<?php Pjax::begin(['id' => 'sm-container']); ?>


<?php $form = ActiveForm::begin([
    'id' => 'form-vat',
]); ?>

<?= $form->field($model, 'data_json[vat]')->widget(Select2::classname(), [
    'data' => [
        'NONE' => 'ไม่มี VAT',
        'IN' => 'VAT ใน',
        'EX' => 'VAT นอก',
    ],
    'options' => ['placeholder' => 'เลือกภาษี ...'],
    'pluginOptions' => [
        'maximumInputLength' => 10
    ],
])->label(false) ?>
<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>



<?php
$js = <<< JS

        \$('#form-vat').on('beforeSubmit', function (e) {
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
<?php Pjax::end(); ?>