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
    'id' => 'form-status-confirm'
]); ?>
<?= $form->field($model, 'status')->textInput()->label(false) ?>
<?php
echo $model->status;
?>
<!-- ชื่อของประเภท -->
<?php if ($model->status == 2): ?>
<?=$form->field($model, 'data_json[pr_leader_confirm]')->radioList(
        ['Y' => 'เห็นชอบ', 'N' => 'ไม่เห็นชอบ'],
        ['custom' => true, 'inline' => true, 'id' => 'custom-radio-list']
    )->label(false);
?>
<?= $form->field($model, 'data_json[pr_confirm_comment_2]')->textArea()->label('หมายเหตุ') ?>
<?php endif ?>


<?php if ($model->status == 99): ?>
<?=
    $form->field($model, 'data_json[pr_confirm_3]')->radioList(
        ['Y' => 'ตรวจสอบผ่าน', 'N' => 'ตรวจสอบไม่ผ่าน'],
        ['custom' => true, 'inline' => true, 'id' => 'custom-radio-list']
    )->label(false);
?>
<?= $form->field($model, 'data_json[pr_confirm_comment_3]')->textArea()->label('หมายเหตุ') ?>
<?php endif ?>

<?php if ($model->status == 3): ?>
<?=
    $form->field($model, 'data_json[pr_confirm_4]')->radioList(
        ['Y' => 'อนุมัติ', 'N' => 'ไม่อนุมัติ'],
        ['custom' => true, 'inline' => true, 'id' => 'custom-radio-list']
    )->label(false);
?>
<?= $form->field($model, 'data_json[pr_confirm_comment_4]')->textArea()->label('หมายเหตุ') ?>
<?php endif ?>

<?php if ($model->status == 5): ?>
<?= $form->field($model, 'data_json[po_confirm_comment_6]')->textArea()->label('หมายเหตุ') ?>
<?php endif ?>

<?php if ($model->status == 6): ?>
<?= $form->field($model, 'data_json[po_confirm_comment_7]')->textArea()->label('หมายเหตุ') ?>
<?php endif ?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

</div>
</div>


</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

        \$('#form-status-confirm').on('beforeSubmit', function (e) {
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