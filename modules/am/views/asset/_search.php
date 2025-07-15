<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\Categorise;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use kartik\tree\TreeViewInput;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $model */
/** @var yii\widgets\ActiveForm $form */
$listAssetitem = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_item'])->all(),'code','title');
$listAssetType= ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(),'code','title');

?>
<style>
.field-assetsearch-q {
    margin-bottom: 0px !important;
}

.right-setting {
    width: 500px !important;
}

.select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.25rem + 2px);
    line-height: 1.5;
    padding: 0.375rem 1.5rem 0.375rem 0.5rem !important;
}
</style>

<?php $form = ActiveForm::begin([
        // 'action' => ['/am/asset'],
        // 'action' => 'index',
        'method' => 'get',
        'options' => [
            'data-pjax' => 0
        ],
         'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
    ]); ?>

<div class="row">
    <div class="col-lg-4 col-md-4 col-sm-12">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false)->label(false) ?>
    </div>

    <div class="col-lg-4 col-md-4 col-sm-12">
        <?php

  echo $form->field($model, 'asset_type_id')->widget(Select2::classname(), [
    'data' => $model->listAssetType(),
        'options' => [
        'placeholder' => 'ทุกประเภท',
        'id' => 'asset_type_id'
    ],
        'pluginOptions' => [
        'allowClear' => true,
    ],
                  'pluginEvents' => [
                        "select2:select" => "function() { 
                            // $(this).submit(); 
                        }",
                    ],
])->label(false);
?>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">

        <?php
echo $form->field($model, 'asset_category_id')->widget(DepDrop::classname(), [
    'options' => [
        'placeholder' => 'ทุกหมวดหมู่',
     ],
    'type' => DepDrop::TYPE_SELECT2,
    'select2Options' => ['pluginOptions' => ['allowClear' => true]],
    'pluginOptions' => [
        'depends' => ['asset_type_id'],
        'url' => Url::to(['/am/asset-item/get-asset-category']),
        'loadingText' => 'กำลังโหลด ...',
        'params' => ['depdrop_all_params' => 'assetitemsearch-asset_type_id'],
        'initDepends' => ['asset_type_id'],
        'initialize' => true,
    ],
                  'pluginEvents' => [
                        "select2:select" => "function() { 

                        }",
                    ],

])->label(false);?>
    </div>



 <div class="col-lg-1 col-md-1 col-sm-12">
    <div class="d-flex flex-row align-items-center gap-2">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
            aria-expanded="false" aria-controls="collapseFilter">
            <i class="fa-solid fa-filter"></i>
        </button>
    </div>
    </div>

</div>

<div class="collapse mt-3" id="collapseFilter">
    <?= $form->field($model, 'asset_group')->hiddenInput()->label(false);
                                    
                                    ?>
                                        <div class="col-lg-4 col-md-4 col-sm-12">
        <?php
 echo $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
    'name' => 'department',
    'id' => 'treeID',
    'query' => Organization::find()->addOrderBy('root, lft'),
    'value' => null,  // ไม่ตั้งค่าเริ่มต้น
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => false,
    'options' => [
        'class' => 'close',
        'allowClear' => true,
    ],
    'pluginOptions' => [
        'allowClear' => true,
        'placeholder' => 'เลือกหน่วยงาน...',
    ],
])->label(false);
?>

    </div>

    <?= $form->field($model, 'asset_type')->widget(Select2::classname(), [
                                        'data' => $listAssetType,
                                        'options' => ['placeholder' => 'เลือกรายการครุภัณฑ์'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '500px',
                                        ],
                                    ])->label(false);
                                    
                                    ?>

    <?= $form->field($model, 'asset_item')->widget(Select2::classname(), [
                                        'data' => $listAssetitem,
                                        'options' => ['placeholder' => 'เลือกรายการครุภัณฑ์'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '350px',
                                        ],
                                    ])->label('ชื่อทรัพย์สิน');
                                    
                                    ?>



    <div class="row">
        <div class="col-6">
            <?= $form->field($model, 'method_get')->widget(Select2::classname(), [
                                        'data' => $model->ListMethodget(),
                                        'options' => ['placeholder' => 'เลือกวิธีได้มา'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('วิธีได้มา');
                                    ?>
        </div>
        <div class="col-6">
            <?= $form->field($model, 'budget_type')->widget(Select2::classname(), [
                                        'data' => $model->ListBudgetdetail(),
                                        'options' => ['placeholder' => 'เลือกประเภทเงิน'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('ประเภทเงิน');
                                    ?>
        </div>
        <div class="col-6">


            <?= $form->field($model, 'on_year')->widget(Select2::classname(), [
                                        'data' => $model->ListOnYear(),
                                        'options' => ['placeholder' => 'เลือกปีงบประมาณ'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '150px',
                                        ],
                                    ])->label('งบประมาณ');
                                    ?>
        </div>
        <div class="col-6">


            <?php
                        $url = \yii\helpers\Url::to(['/depdrop/employee']);
                        $owner = empty($model->owner) ? '' : Employees::findOne(['cid' => $model->owner])->fullname;
                                echo $form->field($model, 'owner')->widget(Select2::classname(), [
                                    // 'data' => $model->ListEmployees(),
                                    'initValueText'=>$owner,
                                    'options' => ['placeholder' => 'กรุณาเลือก'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 1,
                                        'language' => [
                                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                        ],
                                        'ajax' => [
                                            'url' => $url,
                                            'dataType' => 'json',
                                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                        ],
                                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new JsExpression('function(city) { return city.text; }'),
                                        'templateSelection' => new JsExpression('function (city) { return city.text; }'),
                                    ],
                                    'pluginEvents' => [
                                        // "select2:select" => "function(result) { 
                                        //     var data = $(this).select2('data')[0]
                                        //     $('#asset-data_json-method_get_text').val(data.text)
                                        //  }",
                                    ]
                                ])->label('ผู้รับผิดชอบ');
                        ?>
        </div>
        <div class="col-6">
            <?=$form->field($model, 'q_receive_date')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '99/99/9999',
    ])->label('วันที่รับเข้า');
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

    <div class="row">
        <div class="col-6">

            <?= $form->field($model, 'price1')->textInput(['type' => 'number'])->label('ระบุราคาต่ำสุด') ?>
        </div>
        <div class="col-6">

            <?= $form->field($model, 'price2')->textInput(['type' => 'number'])->label('ระบุราคาสูงสุด') ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'po_number')->textInput()->label('เลขที่สั่งซื้อ') ?>
        </div>
    </div>

</div>

<?php ActiveForm::end(); ?>


<?php
$js = <<< JS
$('#show').val(localStorage.getItem('right-setting'))
console.log(localStorage.getItem('right-setting'));
$("#filter-asset").addClass(localStorage.getItem('right-setting'));

$(".filter-asset").on("click", function(){
  $("#filter-asset").addClass("show");
  localStorage.setItem('right-setting','show')
})

$(".filter-asset-close").on("click", function(){
    $(".right-setting").removeClass("show");
    localStorage.setItem('right-setting','hide')
})

// const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
// const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

JS;
$this->registerJS($js);
      
      ?>