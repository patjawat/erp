<?php

use app\models\Categorise;
use app\modules\inventory\models\warehouse;
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
$receive_type_name = $model->receive_type == 'normal' ? 'รับเข้าปกติ' : 'รับจากใบใบสั่งซื้อ';
?>
<style>
.col-form-label {
    text-align: end;
}
</style>


<div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded mb-3 d-flex justify-content-between">
    <h5><i class="fa-solid fa-circle-info text-primary"></i> การบันทึกรับเข้า</h5>
    <div class="dropdown float-end">
        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right">

        </div>
    </div>
</div>
<div class="order-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-rc',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-3']]
    ]); ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'po_number')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'receive_type')->textInput()->label(false) ?>

    <div class="row">

        <div class="col-6">
        <?php
            // echo $form
            //     ->field($model, 'movement_date')
            //     ->widget(DateControl::classname(), [
            //         'type' => DateControl::FORMAT_DATE,
            //         'language' => 'th',
            //         'widgetOptions' => [
            //             'options' => ['placeholder' => 'ระบุวันที่วันรับเข้าคลัง ...'],
            //             'pluginOptions' => [
            //                 'autoclose' => true
            //             ]
            //         ]
            //     ])
            //     ->label('วันรับเข้าคลัง');
        ?>

        <?php

            // echo $form->field($model, 'movement_date')->widget(DatePicker::classname(), [
            //     'options' => ['placeholder' => 'เลือกวันที่...'],
            //     // 'pluginOptions' => [
            //     //     'autoclose' => true,
            //     //     'format' => 'yyyy-mm-dd',
            //     //     'todayHighlight' => true,
            //     //     'language' => 'th',  // กำหนดภาษาเป็นภาษาไทย
            //     //     'calendarWeeks' => true,
            //     //     'clearBtn' => true,
            //     //     'startDate' => '01/01/2021',
            //     //     'todayBtn' => true,
            //     //     'yearRange' => '-100:+0',
            //     //     'startView' => 'year',
            //     //     'minViewMode' => 'months',
            //     //     'format' => 'dd-mm-yyyy',  // รูปแบบวันที่เป็น พ.ศ.
            //     //     'beforeShowDay' => function ($date) {
            //     //         return date('Y', strtotime($date)) + 543;  // เปลี่ยนปี ค.ศ. เป็น พ.ศ.
            //     //     },
            //     // ],
            //     // 'pluginOptions' => [
            //     //     'autoclose' => true,
            //     //     'format' => 'dd/mm/yyyy',
            //     //     'language' => 'th',
            //     //     'todayHighlight' => true,
            //     //     'calendarWeeks' => true,
            //     //     'todayBtn' => true,
            //     //     'daysOfWeekHighlighted' => [0, 6],
            //     //     'startDate' => $model->getCurrDate()['startDate'],  // ใส่วันที่เริ่มต้นที่เป็น พ.ศ.
            //     //     'endDate' => $model->getCurrDate()['endDate'],  // ใส่วันที่สิ้นสุดที่เป็น พ.ศ.
            //     //     'orientation' => 'bottom left',
            //     //     'yearRange' => '2450:2564',  // ใส่ช่วงปีที่เป็น พ.ศ.
            //     // ],
            //     'pluginOptions' => [
            //         'format' => 'dd/mm/yyyy',
            //         'autoclose' => true
            //     ],
            //     'convertFormat' => true,
            //     'pluginEvents' => [
            //         'changeDate' => "function(e) {
            //             var year = parseInt(e.date.getFullYear()) + 543;
            //             \$(this).val(('0' + e.date.getDate()).slice(-2) + '/' +
            //                          ('0' + (e.date.getMonth()+1)).slice(-2) + '/' + year);
            //         }",
            //     ],
            //     //     'pluginEvents' => [
            //     //         'changeDate' => "function(e) {
            //     //     var year = e.date.getFullYear();
            //     //     e.date.setFullYear(year - 543);
            //     //     \$('#movement_date-movement_date').val(e.date.toISOString().slice(0, 10));
            //     // }",
            //     //     ]
            // ]);
        ?>
    
            <?php
                // echo $form
                //     ->field($model, 'data_json[to_stock_date]')
                //     ->widget(DateControl::classname(), [
                //         'type' => DateControl::FORMAT_DATE,
                //         'language' => 'th',
                //         'ajaxConversion' => false,
                //         'options' => [
                //             'pluginOptions' => [
                //                 'autoclose' => true,
                //                 'format' => 'dd/mm/yyyy',
                //                 'language' => 'th',
                //                 'todayHighlight' => true,
                //                 'calendarWeeks' => true,
                //                 'todayBtn' => 'linked',
                //                 'daysOfWeekHighlighted' => [0, 6],
                //                 'orientation' => 'bottom left',
                //                 'startDate' => '01/01/2564',  // ใส่วันที่เริ่มต้นที่เป็น พ.ศ.
                //                 'endDate' => '31/12/2564',  // ใส่วันที่สิ้นสุดที่เป็น พ.ศ.
                //                 'yearRange' => '2450:2564',  // ใส่ช่วงปีที่เป็น พ.ศ.
                //             ],
                //             'pluginEvents' => [
                //                 'changeDate' => "function(e) {
                //     var year = e.date.getFullYear();
                //     e.date.setFullYear(year - 543);
                //     \$('#yourmodel-your_date_field').val(e.date.toISOString().slice(0, 10));
                // }",
                //             ],
                //         ],
                //         // 'widgetOptions' => [
                //         //     'options' => ['placeholder' => 'ระบุวันรับเข้าคลัง ...'],
                //         //     'pluginOptions' => [
                //         //         'autoclose' => true
                //         //     ]
                //         // ]
                //     ])
                //     ->label('วันรับเข้าคลัง');
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
                <input type="text" class="form-control"  value="<?= $receive_type_name ?>" disabled="true">
            </div>

            <?php
                echo $form->field($model, 'to_warehouse_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(warehouse::find()->all(), 'id', 'warehouse_name'),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('คลัง');
            ?>
        </div>
    </div>
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