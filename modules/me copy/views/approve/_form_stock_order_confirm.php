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
    'id' => 'form-leader-confirm',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/approve/stock-confirm-validator'],
])
?>

<?=$form->field($model, 'data_json[order_confirm]')->radioList(
        ['Y' => 'อนุมัติ', 'N' => 'ไม่อนุมัติ'],
        ['custom' => true, 'inline' => true]
    )->label(false);
?>
<?= $form->field($model, 'data_json[order_comment]')->textArea()->label('หมายเหตุ') ?>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>


<table class="table">
  <thead>
    <tr>
      <th scope="col">ชื่อรายการ</th>
      <th class="text-center">จำนวนเบิก</th>
      <th class="text-center">หน่วย</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($model->getItems() as $item):?>
    <tr>
      <td><?= $item->product->avatar()?></td>
      <td class="text-center"><?=$item->qty?></td>
      <td class="text-center">-</td>
    </tr>
    <?php endforeach;?>
  </tbody>
</table>

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