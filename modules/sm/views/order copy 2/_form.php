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
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'sm-container']); ?>
<div class="card">
    <div class="card-body">
        <p class="card-text">ขอซื้อขอจ้าง</p>
    </div>
</div>

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
                ])
                ->label('วันที่ต้องการ');

        ?>
    </div>
</div>




<?= $form->field($model, 'data_json[vendor]')->textInput()->label('บริษัทแนะนำ') ?>
<?= $form->field($model, 'data_json[leader1]')->hiddenInput(['value' => $employee->leaderUser()['leader1']])->label(false) ?>
<?= $form->field($model, 'data_json[leader1_fullname]')->hiddenInput(['value' => $employee->leaderUser()['leader1_fullname']])->label(false) ?>
<?= $form->field($model, 'data_json[comment]', ['hintType' => ActiveField::HINT_SPECIAL])->textArea(['rows' => 3])->label('หมายเหตุ')->hint('Enter address in 4 lines. First 2 lines must contain the street details and next 2 lines the city, zip, and country detail.') ?>
<?= $form->field($model, 'data_json[product_type_name]')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

</div>
</div>




<div class="row justify-content-center">
    <div class="col-lg-2 col-md-4 col-sm-12">
        <?= $this->render('step', ['model' => $model]) ?>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">

        <div class="d-flex justify-content-between">
            <div>
                <h5> <span class="badge rounded-pill bg-primary text-white">1</span> ขั้นตอนการขอซื้อขอจ้าง</h5>
            </div>
            <p class="">
                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal shadow', 'data' => ['size' => 'modal-lg']]) ?>
                <?= Html::a('<i class="fa-regular fa-trash-can"></i> ยกเลิก', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-danger rounded-pill shadow',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>
        </div>
        <div class="card">
            <div class="card-body">
                <!-- <div class="d-flex justify-content-between">
                    <p><i class="fa-solid fa-bag-shopping fs-3"></i> ใบขอซื้อ</p>
                </div> -->
                <table class="table table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
                            <td class="fw-semibold"><?= $model->pr_number ?></td>
                            <td class="text-end">ผู้ขอ</td>
                            <td> <?= $model->getUserReq()['avatar'] ?></td>
                        </tr>
                        <tr class="">
                            <td>เพื่อจัดซื้อ/ซ่อมแซม</td>
                            <td>
                                <?php
                                    try {
                                        echo $model->data_json['product_type_name'];
                                    } catch (\Throwable $th) {
                                    }
                                ?></td>
                            <td class="text-end">วันที่ขอซื้อ</td>
                            <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?></td>

                        </tr>
                        <tr class="">
                            <td class="text-end">เหตุผล</td>
                            <td> <?= $form->field($model, 'data_json[comment]')->textInput(['rows' => 3])->label(false) ?>
                            </td>
                            <td class="text-end">วันที่ต้องการ</td>
                            <td> <?php echo Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') ?>
                            </td>
                        </tr>
                        <td class="text-end">ผู้เห็นชอบ</td>
                        <td colspan="5"><?= $model->viewLeaderUser()['avatar'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-end">ความเห็น</td>
                            <td colspan="5">
                                <?= isset($model->data_json['pr_confirm_2']) ? '<span class="badge rounded-pill bg-success-subtle"><i class="fa-regular fa-thumbs-up"></i> ' . $model->data_json['pr_confirm_2'] . '</span>' : '' ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>



        <?php DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_wrapper',  // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items',  // required: css class selector
            'widgetItem' => '.item',  // required: css class
            'limit' => 4,  // the maximum times, an element can be cloned (default 999)
            'min' => 1,  // 0 or 1 (default 1)
            'insertButton' => '.add-item',  // css class
            'deleteButton' => '.remove-item',  // css class
            'model' => $modelsItems[0],
            'formId' => 'form-order',
            'formFields' => [
                'item_id',
                'price',
                'amount',
            ],
        ]); ?>


<div class="container-items"><!-- widgetContainer -->
            <?php foreach ($modelsItems as $i => $modelItems): ?>
                <div class="item panel panel-default"><!-- widgetBody -->
                    <div class="panel-heading">
                        <h3 class="panel-title pull-left">Address</h3>
                        <div class="pull-right">
                            <button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
                            <button type="button" class="remove-item btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-body">
                        <?php
                        // necessary for update action.
                        if (!$modelItems->isNewRecord) {
                            echo Html::activeHiddenInput($modelItems, "[{$i}]id");
                        }
                        ?>
                        <?= $form->field($modelItems, "[{$i}]item_id")->textInput(['maxlength' => true]) ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <?= $form->field($modelItems, "[{$i}]price")->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-sm-6">
                                <?= $form->field($modelItems, "[{$i}]amount")->textInput(['maxlength' => true]) ?>
                            </div>
                        </div><!-- .row -->
             
                        <!-- .row -->
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php DynamicFormWidget::end(); ?>

            

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
<?php Pjax::end(); ?>