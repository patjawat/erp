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
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/lm/leave/approve-validator'],
])
?>
<div class="row d-flex justify-content-center">
    <div class="col-6">
        
        <?php if($name == 'leader'):?>
        <?=$form->field($model, 'data_json[leader_approve]')->radioList(['Y' => 'อนุมัติ', 'N' => 'ไม่อนุมัติ'],['custom' => true, 'inline' => true])->label(false)?>
        <?php endif;?>

        <?php if($name == 'leader_group'):?>
        <?=$form->field($model, 'data_json[leader_group_approve]')->radioList(['Y' => 'อนุมัติ', 'N' => 'ไม่อนุมัติ'],['custom' => true, 'inline' => true])->label(false)?>
        <?php endif;?>

        <?php if($name == 'leader_group'):?>
        <?=$form->field($model, 'data_json[director_approve]')->radioList(['Y' => 'อนุมัติ', 'N' => 'ไม่อนุมัติ'],['custom' => true, 'inline' => true])->label(false)?>
        <?php endif;?>
        
    </div>
</div>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

        \$('#form').on('beforeSubmit', function (e) {
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