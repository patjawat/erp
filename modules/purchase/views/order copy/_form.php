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
// use wbraganca\dynamicform\DynamicFormWidget;
// use vivekmarakana\dynamicform\DynamicFormWidget;
use wbraganca\dynamicform\DynamicFormWidget;
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

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

?>
<style>
.col-form-label {
    text-align: end;
}
</style>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'sm-container']); ?>


<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
]); ?>

<!-- ชื่อของประเภท -->

        <?= $form->field($model, 'data_json[leader1]')->hiddenInput(['value' => $employee->leaderUser()['leader1']])->label(false) ?>
        <?= $form->field($model, 'data_json[leader1_fullname]')->hiddenInput(['value' => $employee->leaderUser()['leader1_fullname']])->label(false) ?>
        <?= $form->field($model, 'data_json[product_type_name]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'status')->textInput()->label('สถานะ') ?>
        <?= $form->field($model, 'data_json[pr_confirm_2]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>

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
            ])->label('ขอซื้อ');
        ?>

    <?php if ($model->name == 'po'): ?>
        <?php
        echo $form->field($model, 'category_id')->widget(Select2::classname(), [
            'data' => $model->ListPr(),
            'options' => ['placeholder' => 'เลขที่ใบขอซื้อ (ถ้ามี)'],
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
        ])->label('ขอซื้อ');
        ?>
    <?php endif; ?>

                  <?= $form->field($model, 'data_json[vendor]')->textInput()->label('บริษัทผู้ขาย') ?>
                  <?php if ($model->name == 'pr'): ?>
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
<?php endif; ?>
  <?= $form->field($model, 'data_json[comment]')->textInput()->label('หมายเหตุ') ?>

<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    JS;
$this->registerJS($js, View::POS_READY)
?>
<?php Pjax::end(); ?>