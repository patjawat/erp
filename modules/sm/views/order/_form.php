<?php

use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
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

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin([
    'id' => 'form-order'
]); ?>
 <!-- ชื่อของประเภท -->


<div class="row">
<div class="col-6">
<?php
                    echo $form->field($model, 'category_id')->widget(Select2::classname(), [
                        'data' => $model->ListProductType(),
                        'options' => ['placeholder' => 'กรุณาเลือก'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                        'pluginEvents' => [
                            'select2:select' => "function(result) { 
                            var data = \$(this).select2('data')[0].text;
                            \$('#order-data_json-product_type_name').val(data)
                        }",
                        ]
                    ])->label('ประเภทขอซื้อ');
                ?>
</div>
<div class="col-6">
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
                  ])->label('วันที่ต้องการ');

                ?>
</div>
</div>

               
            

                <?= $form->field($model, 'data_json[leader1]')->textInput(['value' => $employee->leaderUser()['leader1']])->label(false) ?>
                <?= $form->field($model, 'data_json[leader1_fullname]')->textInput(['value' => $employee->leaderUser()['leader1_fullname']])->label(false) ?>
                <?= $form->field($model, 'data_json[vendor]')->textInput()->label('บริษัทแนะนำ') ?>
                <?= $form->field($model, 'data_json[comment]', ['hintType' => ActiveField::HINT_SPECIAL])->textArea(['rows' => 3])->label('หมายเหตุ')->hint('Enter address in 4 lines. First 2 lines must contain the street details and next 2 lines the city, zip, and country detail.') ?>
                <?= $form->field($model, 'data_json[product_type_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
                <div class="form-group mt-3 d-flex justify-content-center">
                    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
                </div>
                
            </div>
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