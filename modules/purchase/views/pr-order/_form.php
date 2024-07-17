<?php

use app\modules\hr\models\Employees;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

?>
<style>
.col-form-label {
    text-align: end;
}
</style>


<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    // 'type' => ActiveForm::TYPE_HORIZONTAL,
    // 'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
]); ?>

<!-- ชื่อของประเภท -->

<div class="row">
    <div class="col-6">
      
<?php
echo $form->field($model, 'data_json[item_type]')->widget(Select2::classname(), [
    'data' => $model->ListProductType(),
    'options' => ['placeholder' => 'กรุณาเลือก'],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
    ],
    'pluginEvents' => [
        'select2:select' => "function(result) { 
                            var data = \$(this).select2('data')[0].text;
                            \$('#order-data_json-product_type_name').val(data)
                        }",
    ]
])->label('ขอซื้อ/ขอจ้าง');
?>
  <?php
echo $form
    ->field($model, 'data_json[due_date]')
    ->widget(DateControl::classname(), [
        'type' => DateControl::FORMAT_DATE,
        'language' => 'th',
        'widgetOptions' => [
            'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
            'pluginOptions' => [
                'autoclose' => true
            ]
        ]
    ])
    ->label('วันที่ต้องการ');
?>

  </div>
    <div class="col-6">
    <?php
        echo $form->field($model, 'vendor_id')->widget(Select2::classname(), [
            'data' => $model->ListVendor(),
            'options' => ['placeholder' => 'เลือกบริษัทแนะนำ)'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                                    var data = \$(this).select2('data')[0].text;
                                    \$('#order-data_json-vendor_name').val(data)
                                }",
            ]
        ])->label('บริษัทแนะนำ');
    ?>
    <?php
        echo $form
            ->field($model, 'data_json[po_create_date]')
            ->widget(DateControl::classname(), [
                'type' => DateControl::FORMAT_DATE,
                'language' => 'th',
                'widgetOptions' => [
                    'options' => ['placeholder' => 'ระบุวันที่ขอซื้อ ...'],
                    'pluginOptions' => [
                        'autoclose' => true
                    ]
                ]
            ])
            ->label('วันที่ขอซื้อ');
    ?>



    </div>
</div>


<?= $form->field($model, 'data_json[comment]')->textArea()->label('หมายเหตุ') ?>
<?= $form->field($model, 'data_json[leader1]')->hiddenInput(['value' => $employee->leaderUser()['leader1']])->label(false) ?>
<?= $form->field($model, 'data_json[leader1_fullname]')->hiddenInput(['value' => $employee->leaderUser()['leader1_fullname']])->label(false) ?>
<?= $form->field($model, 'data_json[department]')->hiddenInput(['value' => $model->getUserReq()['department']])->label(false) ?>
<?= $form->field($model, 'data_json[product_type_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[vendor_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'status')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_leader_confirm]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_confirm_comment]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_director_confirm]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_director_comment]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'data_json[pr_confirm_2]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    \$('#form-order').on('beforeSubmit', function (e) {
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
                    await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                }
            }
        });
        return false;
    });

    JS;
$this->registerJS($js, View::POS_END)
?>