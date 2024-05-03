<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use iamsaint\datetimepicker\Datetimepicker;
use app\components\CategoriseHelper;
use app\modules\hr\models\Organization;
use app\modules\am\models\Asset;
use yii\web\JsExpression;
use app\modules\hr\models\Employees;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */

?>


<div class="card">   
<div class="card-body">
        <div class="asset-form">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="row">
                    <div class="col-12">
                        <?= $form->field($model, 'data_json[asset_name]')->textInput(['maxlength' => true])->label('ชื่อสิ่งปลูกสร้าง') ?>
                        </div>
                        
                     
                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('หมายเลขสิงปลูกสร้าง') ?>
                            </div>
                            <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'data_json[engineer_name]')->textInput(['maxlength' => true])->label('วิศวกร') ?>
                        </div>
                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'data_json[building_regis]')->textInput(['maxlength' => true])->label('ทะเบียนอาคารสิ่งปลูกสร้าง') ?>
                        </div>
         
                       

                        <div class="col-6">
                            <?=$form->field($model, 'receive_date')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('วันที่สร้าง');
                        ?>
                        </div>
                        <div class="col-6">
                            <?=$form->field($model, 'data_json[end_date]')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('วันที่แล้วเสร็จ');
                        ?>
                        </div>
                <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'data_json[building_number]')->textInput(['maxlength' => true])->label('แบบเลขที่') ?>
                        </div>
                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'data_json[building_contract_number]')->textInput(['maxlength' => true])->label('สัญญาเลขที่') ?>
                        </div>

                    </div>
                    <!-- End Row Sub  -->
                </div>
                <!-- En col-6 main -->
                <div class="col-6">
                    <div class="row">
                   
                        <?php // print_r(CategoriseHelper::Title('หจก.นอร์ทอีส เมดิคอล ซัพพลาย')->one()->code) ?>
                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'asset_item')->widget(Select2::classname(), [
                                    'data' => $model->ListBuildingItems(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                    ]
                                ])->label('ประเภท') ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'data_json[land_code]')->widget(Select2::classname(), [
                                        'data' => ArrayHelper::map(Asset::find()->where(['asset_group' => 1])->all(),'code','code'),
                                        'options' => ['placeholder' => 'เลือกรายการพัสดุ'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                $('#asset-fsn').val('')
                                            }",
                                            "select2:select" => "function() {
                                                // console.log($(this).val());
                                                $.ajax({
                                                    type: 'get',
                                                    url: '".Url::to(['/depdrop/categorise-by-code'])."',
                                                    data: {
                                                        code: $(this).val(),
                                                        name:'asset_item'
                                                    },
                                                    dataType: 'json',
                                                    success: function (res) {
                                                        $('#asset-fsn_number').val(res.code)
                                                        $('#asset-data_json-asset_name_text').val(res.title)
                                                    }
                                                });
                                        }",],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('ปลูกบนที่ดิน');
                                    
                                    ?>
                        </div>
                        <div class="col-6">

                    <?php
                                echo $form->field($model, 'data_json[method_get]')->widget(Select2::classname(), [
                                    'data' => $model->ListMethodget(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                    ]
                                ])->label('วิธีได้มา');
                        ?>

            </div>
                <div class="col-6">
                    <?php
                                echo $form->field($model, 'purchase')->widget(Select2::classname(), [
                                    'data' => $model->ListPurchase(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-purchase_text').val(data.text)
                                        }",
                                        ]
                                        ])->label('การจัดซื้อ');
                                        ?>

                </div>
                <div class="col-6">
                    <?php
                                echo $form->field($model, 'data_json[budget_type]')->widget(Select2::classname(), [
                                    'data' => $model->ListBudgetdetail(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-budget_type_text').val(data.text)
                                         }",
                                    ]
                                ])->label('ประเภทเงิน');
                        ?>

                </div>

                <div class="col-6">
                    <?= $form->field($model, 'price')->textInput() ?>
                </div>
                <div class="col-6">
                <?= $form->field($model, 'on_year')->textInput()->label('ปีงบประมาณ') ?>
                </div>
                <div class="col-6">
                            <?php
                                echo $form->field($model, 'asset_status')->widget(Select2::classname(), [
                                    'data' => $model->ListAssetStatus(),
                                    'options' => ['placeholder' => 'กรุณาเลือก...'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                                    ]
                                ])->label('สถานะ');
                        ?>
                        </div>
                

               
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>