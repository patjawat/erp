<?php

use app\modules\purchase\models\Order;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\SiteHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\datecontrol\DateControl;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
$listPqNumber = ArrayHelper::map(Order::find()->where(['name' => 'order'])->all(), 'id', 'pq_number');
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'purchase']); ?>
<?php $form = ActiveForm::begin([
                    'action' => ['/purchase/po-order/update', 'id' => $model->id],
                    // 'type' => ActiveForm::TYPE_HORIZONTAL,
                    'fieldConfig' => ['labelSpan' => 4, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
                ]); ?>

<div class="row">
<div class="col-6">
<?= $form->field($model, 'data_json[po_date]')
                                ->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('ลงวันที่') ?>

<?= $form->field($model, 'data_json[delivery_date]')->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('กำหนดวันส่งมอบ') ?>
                        <?= $form->field($model, 'data_json[credit_days]')->textInput()->label('ครดิต (วัน)') ?>
</div>
<div class="col-6">
<?= $form->field($model, 'data_json[order_receipt_date]')->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('วันที่รับใบสั่ง') ?>

<?= $form->field($model, 'data_json[warranty]')
                                                    ->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('การรับประกัน') ?>
                                    <?= $form->field($model, 'data_json[signing_date]')->widget(DateControl::classname(), [
                                    'type' => DateControl::FORMAT_DATE,
                                    'language' => 'th',
                                    'widgetOptions' => [
                                        'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                                        'pluginOptions' => [
                                            'autoclose' => true
                                        ]
                                    ]
                                ])->label('วันที่ลงนาม') ?>
</div>
</div>
                    
                     
                    
                    

               
        <div class="row d-flex justify-content-center mt-5">
            <div class="col-md-4 gap-3">
                <div class="d-grid gap-2">
                    <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary rounded-pill shadow']) ?>
                </div>
            </div>
        </div>
        <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>




        <?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    \$('#order-id').on("select2:unselect", function (e) { 
        console.log("select2:unselect", e);
        window.location.href ='/purchase/po-order/create'
    });
    // function getId(id){
    //     window.location.href = Url::to(['/purchase/po-order/create'])
    // }
    JS;
$this->registerJS($js)
?>

<?php  Pjax::end() ?>