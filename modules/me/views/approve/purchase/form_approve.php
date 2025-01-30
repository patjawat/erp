<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\DetailView;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
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
$user = UserHelper::GetEmployee();
?>

<?php // if($model->data_json['leader1'] == $user->id):?>
<?php $form = ActiveForm::begin([
    'id' => 'form-leader-confirm',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/pr-order/checkervalidator'],
])
?>

<?=$form->field($model, 'data_json[pr_leader_confirm]')->radioList(
        ['Y' => 'เห็นชอบ', 'N' => 'ไม่เห็นชอบ'],
        ['custom' => true, 'inline' => true]
    )->label(false);
?>
<?= $form->field($model, 'data_json[pr_confirm_comment]')->textArea()->label('หมายเหตุ') ?>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php // else:?>
<h6 class="text-center">ไม่อนุญาต</h6>
<?php // endif;?> 
<?php
$js = <<< JS

        \$('#form-leader-confirm').on('beforeSubmit', function (e) {
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
                                loadRepairHostory()
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