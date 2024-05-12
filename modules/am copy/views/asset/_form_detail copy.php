<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;
use app\components\CategoriseHelper;
use app\modules\hr\models\Organization;
/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */

?>


<div class="card">
    <div class="card-body">
        <div class="asset-form">


            <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false)?>

            <div class="row">
                <div class="col-lg-6 col-sm-12">


                    <div class="row">

                        <div class="col-12">
                            <?= $form->field($model, 'data_json[fsnnumber]')->widget(Select2::classname(), [
                                        'data' => $model->ListFsn(),
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
                                                        name:'asset_name'
                                                    },
                                                    dataType: 'json',
                                                    success: function (res) {
                                                        $('#asset-fsn').val(res.code)
                                                    }
                                                });
                                        }",],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('ชื่อครุภัณฑ์');
                                    
                                    ?>
                        </div>

                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'fsn')->textInput(['maxlength' => true]) ?>
                        </div>

                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'data_json[fsn2]')->textInput(['maxlength' => true])->label('เลขครุภัณฑ์เดิม') ?>
                        </div>
                        <div class="col-6">
                    <?=$form->field($model, 'receive_date')->widget(Datetimepicker::className(),[
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y', 
                            ],
                            ])->label(true);
                        ?>
                </div>

                <div class="col-6">
                <?=$form->field($model, 'dep_id')->widget(\kartik\tree\TreeViewInput::className(), [
                            'name' => 'department',
                            'query' => Organization::find()->addOrderBy('root, lft'),
                            'value' => 1,
                            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                            'fontAwesome' => true,
                            'asDropdown' => true,
                            'multiple' => false,
                            'options' => ['disabled' => false],
                        ])->label('หน่วยงานภายในตามโครงสร้าง');?>
                </div>

                    </div>
                    <!-- End Row Sub  -->
                </div>
                <!-- En col-6 main -->
                <div class="col-6">
                    <div class="row">
                        <div class="col-12">
                            <?php
                                echo $form->field($model, 'data_json[supvendor]')->widget(Select2::classname(), [
                                    'data' => $model->ListVendor(),
                                    'options' => ['placeholder' => 'เลือกผู้จำหน่าย'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                ])->label('ผู้แทนจำหน่าย');
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
                                ])->label('วิธีได้มา');
                        ?>
                </div>
                <div class="col-6">
                    <?php
                                echo $form->field($model, 'data_json[purchase]')->widget(Select2::classname(), [
                                    'data' => $model->ListPurchase(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                ])->label('การจัดซื้อ');
                        ?>
                </div>
                <div class="col-6">
                    <?php
                                echo $form->field($model, 'data_json[budgetdetail]')->widget(Select2::classname(), [
                                    'data' => $model->ListBudgetdetail(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                ])->label('งบที่ใช้');
                        ?>
                </div>

                <div class="col-6">
                    <?= $form->field($model, 'price')->textInput() ?>
                </div>

               
                    </div>
                </div>
            </div>





            <div class="row">


               



                <div class="col-sm">
                    <?php
                                // echo $form->field($model, 'dep_id')->widget(Select2::classname(), [
                                //     'data' => $model->ListDepartment(),
                                //     'options' => ['placeholder' => 'เลือกหน่วยงาน'],
                                //     'pluginOptions' => [
                                //     'allowClear' => true,
                                //     ],
                                // ])->label(true);
                        ?>
                   

                </div>
              
            </div>
            <div class="row">

                <div class="col-sm">
                    <?= $form->field($model, 'data_json[res_person]')->textInput()->label('ผู้รับผิดชอบ') ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <?= $form->field($model, 'data_json[budget_year]')->textInput()->label('ปีงบประมาณ') ?>
                </div>
                
            </div>

            <div class="row">
                <div class="col-sm-3">
                    <?php
                                echo $form->field($model, 'data_json[assetstatus]')->widget(Select2::classname(), [
                                    'data' => $model->ListAssetstatus(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                ])->label('สถานะการใช้งาน');
                        ?>
                </div>
                <div class="col-sm-3">

                    <?php
                                echo $form->field($model, 'data_json[maintain_pm]')->widget(Select2::classname(), [
                                    'data' => $model->ListMaintainpm(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                ])->label('การบำรุงรักษา PM');
                        ?>
                </div>
                <div class="col-sm-3">
                    <?php
                                echo $form->field($model, 'data_json[test_cal]')->widget(Select2::classname(), [
                                    'data' => $model->ListTestcal(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                ])->label('การสอบ CAL');
                        ?>
                </div>
                <div class="col-sm-3">
                    <?php
                                echo $form->field($model, 'data_json[asset_risk]')->widget(Select2::classname(), [
                                    'data' => $model->ListAssetrisk(),
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                ])->label('ความเสี่ยง');
                        ?>
                </div>
            </div>


            <div class="col-sm-12">
                <?= $form->field($model, 'data_json[model_detail]')->textArea(['rows' => '6'])->label('รายละเอียดยี่ห้อครุภัณฑ์') ?>
            </div>
            <div class="col-sm-12">
                <?=$model->Upload($model->ref,'asset_pic')?>
            </div>


            <div class="form-group mt-4 d-flex justify-content-center">
                <?= AppHelper::BtnSave(); ?>
            </div>

        </div>
    </div>
</div>