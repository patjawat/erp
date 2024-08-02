<?php

use app\models\Categorise;
use app\modules\inventory\models\Warehouse;
use app\modules\purchase\models\Order;
// use kartik\date\DatePicker;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use kato\AirDatepicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\JsExpression;
use DateTime;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
// $listOpOrder = ArrayHelper::map(Order::find()->where(['name' => 'order'])->all(), 'id', 'po_number');
$listOpOrder = ArrayHelper::map(Categorise::find()->all(), 'id', 'title');
$receive_type_name = $model->receive_type == 'receive' ? 'รับเข้าปกติ' : 'รับจากใบใบสั่งซื้อ';
?>
<style>
.col-form-label {
    text-align: end;
}
</style>


    <?php $form = ActiveForm::begin([
        'id' => 'form-rc',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-3']]
    ]); ?>

    <div class="row">
        <div class="col-12">
            <?php
                echo $form
                    ->field($model, 'data_json[to_stock_date]')
                    ->widget(DateControl::classname(), [
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'th',
                        'ajaxConversion' => false,
                        'options' => [
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'dd/mm/yyyy',
                                'language' => 'th',
                                'todayHighlight' => true,
                                'calendarWeeks' => true,
                                'todayBtn' => 'linked',
                                'daysOfWeekHighlighted' => [0, 6],
                                'orientation' => 'bottom left',
                                'startDate' => '01/01/2564',  // ใส่วันที่เริ่มต้นที่เป็น พ.ศ.
                                'endDate' => '31/12/2564',  // ใส่วันที่สิ้นสุดที่เป็น พ.ศ.
                                'yearRange' => '2450:2564',  // ใส่ช่วงปีที่เป็น พ.ศ.
                            ],
                            'pluginEvents' => [
                                'changeDate' => "function(e) {
                    var year = e.date.getFullYear();
                    e.date.setFullYear(year - 543);
                    \$('#yourmodel-your_date_field').val(e.date.toISOString().slice(0, 10));
                }",
                            ],
                        ],
                    ])
                    ->label('วันรับเข้าคลัง');
            ?>
            <?php
                echo $form
                    ->field($model, 'data_json[checked_date]')
                    ->widget(DateControl::classname(), [
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'th',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'ระบุวันที่กรรมการคลังตรวจรับ ...'],
                            'pluginOptions' => [
                                'autoclose' => true
                            ]
                        ]
                    ])
                    ->label('วันที่กรรมการคลังตรวจรับ');
            ?>
        </div>
        <div class="col-6">
            <div class="mb-3 highlight-addon has-success">
                <label class="form-label has-star">วิธีรับเข้า</label>
                <input type="text" class="form-control" value="<?= $receive_type_name ?>" disabled="true">
            </div>

            <?php
                // echo $form->field($model, 'to_warehouse_id')->widget(Select2::classname(), [
                //     'data' => ArrayHelper::map(Warehouse::find()->all(), 'id', 'warehouse_name'),
                //     'options' => ['placeholder' => 'กรุณาเลือก'],
                //     'pluginOptions' => [
                //         'allowClear' => true,
                //         'dropdownParent' => '#main-modal',
                //     ],
                // ])->label('คลัง');
            ?>
        </div>
    </div>

    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'po_number')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'receive_type')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'to_warehouse_id')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>


    <?php
        $ref = $model->ref;
        $js = <<< JS

                            \$('#form-rc').on('beforeSubmit', function (e) {
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
        $this->registerJS($js)
    ?>