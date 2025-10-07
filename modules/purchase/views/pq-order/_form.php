<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php
try {
    $orderTypeName =  $model->data_json['order_type_name'];
} catch (\Throwable $th) {
    $orderTypeName = '';
}
?>
<?php Pjax::begin(['id' => 'purchase-container']); ?>
<style>
    .col-form-label {
        text-align: end;
    }

    fieldset>legend {
        font-family: 'kanit', sans-serif;
    }
</style>


<h5 class="text-center">ประเภท :
    <?= $orderTypeName ?></h5>
<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/purchase/pq-order/validator'],
]); ?>


<div class="row mt-4">
    <div class="col-12">
        <fieldset class="border p-3 rounded">
            <legend class="float-none w-auto px-2 fs-6 fw-semibold">คำสั่ง</legend>
            <div class="row">
                <div class="col-12">
                    <?= $form->field($model, 'data_json[order]')->textInput()->label('ตามคำสั่ง') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[order_number]')->textInput()->label('เลขที่คำสั่ง') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[order_date]')->textInput()->label('ลงวันที่');
                    ?>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="row mt-4">
        <fieldset class="border p-3 rounded">
            <legend class="float-none w-auto px-2 fs-6 fw-semibold">แผนงานโครงการ</legend>
            <div class="row">

                <div class="col-6">
                    <?= $form->field($model, 'data_json[pq_project_id]')->textInput()->label('โครงการเลขที่') ?>
                    <?= $form->field($model, 'data_json[pq_egp_number]')->textInput()->label('รหัสอ้างอิง EGP') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[pq_disbursement]')->textInput()->label('การเบิกจ่ายเงิน') ?>
                    <?= $form->field($model, 'data_json[pq_egp_report]')->textInput()->label('รายการแผน EGP') ?>
                </div>

                <div class="col-4">
                    <?php
                    echo $form->field($model, 'plan_type_id')->widget(Select2::classname(), [
                        'data' => $model->listPlanType(),
                        'options' => [
                            'placeholder' => 'เลือกกลุ่มแผนงาน',
                            'id' => 'plan_type_id'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ],

                    ])->label('ประเภทแผนงาน');
                    ?>
                </div>
                <div class="col-4">
                    <?php
                    echo $form->field($model, 'plan_category_id')->widget(DepDrop::classname(), [
                        'options' => [
                            'placeholder' => 'เลือกหมวด...',
                            'id' => 'plan_category_id'
                        ],
                        'type' => DepDrop::TYPE_SELECT2,
                        'select2Options' => ['pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                        ]],
                        'pluginOptions' => [
                            'depends' => ['plan_type_id'],
                            'url' => Url::to(['/plan/depdrop/plan-category']),
                            'loadingText' => 'กำลังโหลด ...',
                           'initialize' => true,
                            'initDepends' => ['plan_type_id'], // 🟢 สำคัญมาก
                            'params' => ['plan_type_id'],      // 🟢 ช่วยส่งค่าไปให้ DepDrop โหลดค่าเริ่มต้นได้

                        ]
                    ])->label('หมวดหมู่');
                    ?>

                </div>
                <div class="col-4">
<?php
                    echo $form->field($model, 'plan_item_id')->widget(DepDrop::classname(), [
                        'options' => [
                            'placeholder' => 'เลือกแผนงาน...',
                             'id' => 'plan_item_id'
                        ],
                        'type' => DepDrop::TYPE_SELECT2,
                        'select2Options' => [
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                            ],
                        ],
                        'pluginOptions' => [
                            'depends' => ['plan_category_id'], // ให้โหลดตามหมวด
                            'url' => Url::to(['/plan/depdrop/plan-item']),
                            'loadingText' => 'กำลังโหลด ...',
                           'initialize' => true,
                            'initDepends' => ['plan_category_id'], // 🟢 ให้โหลดค่าตามหมวดเมื่อเป็นหน้า update
                            'params' => ['plan_category_id'],      // 🟢 เพิ่ม params เพื่อให้โหลดค่าเดิม
                        ],
                    ])->label('ชื่อแผนงาน/โครงการ');
                    ?>
                </div>

                <div class="col-12">
                    <?php
                    echo $form->field($model, 'plan_order_id')->widget(DepDrop::classname(), [
                        'options' => ['placeholder' => 'เลือกแผนงาน...'],
                        'type' => DepDrop::TYPE_SELECT2,
                        'select2Options' => [
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                            ],
                        ],
                        'pluginOptions' => [
                            'depends' => ['plan_item_id'], // ให้โหลดตามหมวด
                            'url' => Url::to(['/plan/depdrop/plan-order']),
                            'loadingText' => 'กำลังโหลด ...',
                           'initialize' => true,
                            'initDepends' => ['plan_item_id'], // 🟢 ให้โหลดค่าตามหมวดเมื่อเป็นหน้า update
                            'params' => ['plan_item_id'],      // 🟢 เพิ่ม params เพื่อให้โหลดค่าเดิม
                        ],
                        'pluginEvents' => [
                            'select2:select' => new \yii\web\JsExpression("
                                    function(e) {
                                        var selectedId = e.params.data.id; // ค่าที่เลือก
                                        $.ajax({
                                            url: '/plan/depdrop/get-plan-info', // <- แก้ให้เป็น endpoint จริง
                                            type: 'get',
                                            data: { id: selectedId },
                                            dataType: 'json',
                                            success: function(response) {
                                                console.log(response.data.plan_budget_type_id);
                                                $('#order-data_json-pq_budget_type').val(response.data.plan_budget_type_id).trigger('change');
                                            },
                                            error: function() {
                                                console.error('โหลดข้อมูลไม่สำเร็จ');
                                            }
                                        });
                                    }
                                "),
                        ],
                    ])->label('ชื่อแผนงาน/โครงการ');
                    ?>

                </div>

            </div>
    </div>
    </fieldset>

</div>

<div class="row mt-4">
    <fieldset class="border rounded">
        <legend class="float-none w-auto px-2 fs-6 fw-semibold">วิธีการซื้อ/จ้าง</legend>

        <div class="row">
            <div class="col-6">
                <?php
                echo $form->field($model, 'data_json[pq_purchase_type]')->widget(Select2::classname(), [
                    'data' => $model->ListPurchase(),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                    'pluginEvents' => [
                        'select2:select' => "function(result) { 
                                        var data = \$(this).select2('data')[0]
                                        \$('#order-data_json-pq_purchase_type_name').val(data.text)
                                        }",
                    ]
                ])->label('วิธีซื้อหรือจ้าง');
                ?>
            </div>

            <div class="col-6">
                <?php
                $conditionUrl = Url::to(['/depdrop/categorise-by-code']);
                echo $form->field($model, 'data_json[pq_condition]')->widget(Select2::classname(), [
                    'data' => $model->ListPurchaseCondition(),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                    'pluginEvents' => [
                        'select2:select' => 'function(result) { 
                                                  $.ajax({
                                                    url: \'' . $conditionUrl . "',
                                                    type: 'get',
                                                    data: {name:'purchase_condition',code:\$(this).val()},
                                                    dataType: 'json',
                                                    success: async function (response) {
                                                        console.log(response)       
                                                         \$('#order-data_json-pq_condition_name').val(response.title)           
                                                         \$('#order-data_json-pq_income_reason').val(response.data_json.comment)           
                                                        }

                                                });
                                        }",
                    ]
                ])->label('เงื่อนไข')
                ?>


            </div>

            <div class="col-12">
                <?= $form->field($model, 'data_json[pq_income_reason]')->textArea(['rows' => 5, 'style' => 'height: 106px;'])->label('เหตุผลการจัดหา') ?>
            </div>
            <div class="col-6">

                <?php
                echo $form->field($model, 'data_json[pq_method_get]')->widget(Select2::classname(), [
                    'data' => $model->ListMethodget(),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                    'pluginEvents' => [
                        'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0]
                                            \$('#order-data_json-pq_method_get_name').val(data.text)
                                        }",
                    ]
                ])->label('วิธีจัดหา');
                ?>

                <?=
                $form->field($model, 'data_json[pq_budget_group]')->widget(Select2::classname(), [
                    'data' => $model->ListBudgetGroup(),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                    'pluginEvents' => [
                        'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0]
                                            \$('#order-data_json-pq_budget_group_name').val(data.text)
                                        }",
                    ]
                ])->label('หมวดเงิน');
                ?>
                <?=
                $form->field($model, 'data_json[pq_budget_type]')->widget(Select2::classname(), [
                    'data' => $model->ListBudgetdetail(),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                    'pluginEvents' => [
                        'select2:select' => "function(result) { 
                                            var data = \$(this).select2('data')[0]
                                            \$('#order-data_json-pq_budget_type_name').val(data.text)
                                        }",
                    ]
                ])->label('ประเภทเงิน');
                ?>


            </div>
            <div class="col-6">
                <?= $form->field($model, 'data_json[pq_consideration]')->radioList(['เกณฑ์ราคา' => 'เกณฑ์ราคา', 'เกณฑ์ประเมินประสิทธิภาพต่อราคา' => 'เกณฑ์ประเมินประสิทธิภาพต่อราคา'], ['custom' => true, 'inline' => true])->label('การพิจารณา') ?>
                <?= $form->field($model, 'data_json[pq_reason]')->textArea(['style' => 'height: 130px;'])->label('เหตุผลความจำเป็น') ?>


            </div>
            <div class="col-12">



                <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[pq_purchase_type_name]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[pq_method_get_name]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[pq_budget_group_name]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[pq_budget_type_name]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[pq_condition_name]')->hiddenInput()->label(false) ?>
            </div>
        </div>

    </fieldset>
</div>

<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <?= Html::button('ปิด', ['class' => 'btn btn-secondary', 'data-bs-dismiss' => 'modal']) ?>
</div>

<?php ActiveForm::end(); ?>



<?php
$js = <<< JS

thaiDatepicker('#order-data_json-order_date')

$('#form-order').on('beforeSubmit', function (e) {
    e.preventDefault(); // ป้องกันการส่งฟอร์มโดยปกติ
    var form = $(this);

    Swal.fire({
        title: 'ยืนยันการบันทึก?',
        text: 'คุณต้องการบันทึกข้อมูลนี้หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'บันทึก',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลังบันทึก...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'บันทึกสำเร็จ!',
                            text: 'ข้อมูลของคุณถูกบันทึกแล้ว',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(true)
                            // $.pjax.reload({ 
                            //     container: response.container, 
                            //     history: false, 
                            //     replace: false, 
                            //     timeout: false 
                            // });
                        });
                    } else {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: response.message || 'ไม่สามารถบันทึกข้อมูลได้',
                            icon: 'error'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'มีบางอย่างผิดพลาด กรุณาลองใหม่อีกครั้ง',
                        icon: 'error'
                    });
                }
            });
        }
    });

    return false;
});


JS;
$this->registerJS($js, View::POS_END)
?>
<?php Pjax::end(); ?>