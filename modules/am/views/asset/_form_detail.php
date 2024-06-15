<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\components\AppHelper;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;
use app\components\CategoriseHelper;
use app\modules\hr\models\Organization;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use yii\widgets\MaskedInput;
// $assetType = Categorise::find()->where(['name' => 'asset_type','category_id' => $model->category_id])->one();
// $AssetitemList = Categorise::find()->where(['name' => 'asset_item','asset_group' => 2])->all();
$Assetitems = Categorise::find()
// ->select(['categorise.name'])
->leftJoin('categorise t', 't.code=categorise.category_id')
->where(['categorise.name' => 'asset_item'])
->andWhere(['t.category_id' => $model->asset_group])

->all();

$listAssetitem = ArrayHelper::map($Assetitems,'code','title');

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */
/** @var yii\widgets\ActiveForm $form */
// echo $model->asset_group;
// print_r($listAssetitem);
?>


<div class="card">   
<div class="card-body">
        <div class="asset-form">
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                    <div class="row">

                    <div class="<?=$model->isNewRecord ? 'col-9' : 'col-12'?>">
                            <?= $form->field($model, 'asset_item')->widget(Select2::classname(), [
                                        'data' => $listAssetitem,
                                        'options' => ['placeholder' => 'เลือกรายการครุภัณฑ์'],
                                        'pluginEvents' => [
                                            "select2:unselect" => "function() { 
                                                $('#asset-code').val('')
                                            }",
                                            "select2:select" => "function() {
                                                // console.log($(this).val());
                                                $.ajax({
                                                    type: 'get',
                                                    url: '".Url::to(['/depdrop/get-fsn'])."',
                                                    data: {
                                                        asset_item: $(this).val(),
                                                        name:'asset_item'
                                                    },
                                                    dataType: 'json',
                                                    success: function (res) {
                                                        console.log(res.code)
                                                        if(localStorage.getItem('fsn_auto') == 0){
                                                            $('#asset-code').val(res.fsn)
                                                            $('#asset-data_json-asset_name_text').val(res.title)
                                                        }
                                                    }
                                                });
                                        }",],
                                        'addon' => [
                                            // 'prepend' => [
                                            //     'content' => '<i class="fas fa-globe"></i>'
                                            // ],
                                            'append' => [
                                                // 'content' => Html::a('<i class="fa-solid fa-gear"></i>',['/am/asset-item','group'=> 2,'title'=>'ครุภัณฑ์'], [
                                                //     'class' => 'btn btn-primary', 
                                                //     'title' => 'ตั้งค่าครุภัณฑ์', 
                                                //     'data' => [
                                                //         'confirm' => 'ไปยังการตั้งค่าครุภัณฑ์ ข้อมูลที่ยั้งไม่บันึกอาจสูญหาย?',
                                                //         'pjax' => false
                                                //     ],
                                                // ]),
                                                'content' => Html::a('<i class="fa-solid fa-gear"></i>',['/am/asset-item','group'=> 2,'title'=>'ครุภัณฑ์'],[
                                                    'class' => 'btn btn-primary',
                                                 
                                                     
                                                        // 'pjax' => false
                                                
                                                ]),
                                                'asButton' => true
                                            ]
                                            ],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('ชื่อครุภัณฑ์');
                                    
                                    ?>
                        </div>
                        <?php if($model->isNewRecord):?>
                        <div class="col-3">
                            <div style="margin-top:30px;">
                                <?= $form->field($model, 'fsn_auto')->checkbox(['custom' => true])->label('FSN Auto') ?>
                            </div>
                        </div>
<?php endif;?>
                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('หมายเลขครุภัณฑ์') ?>
                        </div>

                        <div class="col-lg-6 col-sm-6">
                            <?= $form->field($model, 'data_json[fsn_old]')->textInput(['maxlength' => true])->label('เลขครุภัณฑ์เดิม') ?>
                        </div>
                     

                <div class="col-6">
                    <?= $form->field($model, 'data_json[serial_number]')->textInput()->label('S/N') ?>
               
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'data_json[unit]')->textInput()->label('หน่วยนับ') ?>
               
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
                            ])->label('วันที่รับเข้า');
                        ?>
                </div>
                        <div class="col-6">
                    <?=$form->field($model, 'data_json[expire_date]')->widget(Datetimepicker::className(),[
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y', 
                            ],
                            ])->label('วันหมดประกัน');
                        ?>
                </div>
                    </div>
                    <!-- End Row Sub  -->
                </div>
                <!-- En col-6 main -->
                <div class="col-6">
                    <div class="row">
                   
                        <?php // print_r(CategoriseHelper::Title('หจก.นอร์ทอีส เมดิคอล ซัพพลาย')->one()->code) ?>
                        

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
                                echo $form->field($model, 'data_json[purchase]')->widget(Select2::classname(), [
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
               

                <div class="col-3">
                    <?= $form->field($model, 'price')->textInput() ?>
                </div>
                <div class="col-3">
                <?= $form->field($model, 'data_json[on_year]')->widget(MaskedInput::className(),['mask'=>'9999'])->label('ปีงบประมาณ') ?>
                  
                </div>
                <div class="col-6">
                            <?php
                                echo $form->field($model, 'data_json[vendor_id]')->widget(Select2::classname(), [
                                    'data' => $model->ListVendor(),
                                    'options' => ['placeholder' => 'เลือกผู้จำหน่าย'],
                                    'pluginOptions' => [
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-vendor_text').val(data.text)
                                         }",
                                    ]
                                ])->label('ผู้แทนจำหน่าย');
                        ?>


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

            
            <div
                class="alert alert-primary"
                role="alert"
            >
                <strong>*</strong> รายละเอียดยี่ห้อครุภัณฑ์
            </div>
            
            <div class="col-sm-12">
                <?= $form->field($model, 'data_json[detail]')->widget(kartik\editors\Summernote::class, [
                    'useKrajeePresets' => true,
                    // other widget settings
                ])->label(false);
                ?>
            </div>
            <div class="col-sm-12">
                <?=$model->Upload($model->ref,'asset_pic')?>
            </div>


         

        </div>
    </div>
</div>


<?php
$js = <<< JS
if($("#assetitem-fsn_auto").val()){
$( "#asset-fsn_auto" ).prop( "checked", localStorage.getItem('fsn_auto') == 1 ? true : false );
$('#asset-code').prop('disabled',localStorage.getItem('fsn_auto') == 1 ? true : false );
if(localStorage.getItem('fsn_auto') == 1)
{
    $('#asset-code').val('จะได้หมายเลข FSN หลังจากบันทึก')
}
}

$("#asset-fsn_auto").change(function() {
    //ตั้งค่า Run FSN Auto
    if(this.checked) {
        localStorage.setItem('fsn_auto',1);
        $('#asset-code').prop('disabled',this.checked);
        $('#asset-code').val('จะได้หมายเลข FSN หลังจากบันทึก')
    }else{
        localStorage.setItem('fsn_auto',0);
        $('#asset-code').prop('disabled',this.checked);
        $('#asset-code').val('')
    }
});


JS;
$this->registerJs($js);
?>