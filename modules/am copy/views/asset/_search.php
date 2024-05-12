<?php

use yii\helpers\Html;
use app\components\AppHelper;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use app\models\Categorise;
use app\modules\hr\models\Employees;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $model */
/** @var yii\widgets\ActiveForm $form */
$listAssetitem = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_item'])->all(),'code','title');
$listAssetType= ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(),'code','title');
$listAssetGroup= ArrayHelper::map(Categorise::find()
->where(['name' => 'asset_group'])
->andwhere(['IN','code' ,[1,2,3]])
->all(),'code','title');
?>
<style>
.field-assetsearch-q {
    margin-bottom: 0px !important;
}
.right-setting {
    width: 500px !important;
}
</style>

<?php $form = ActiveForm::begin([
        'action' => ['/am/asset'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 0
        ],
    ]); ?>

<div class="d-flex gap-3">
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false) ?>
</div>

<div class="right-setting <?=$model->show?>" id="filter-asset">
<div class="card mb-0 w-100">
        <div class="card-header">
            <h5 class="card-title d-flex justify-content-between">
                ค้นหาข้อมูล
                <a href="javascript:void(0)"><i class="bi bi-x-circle filter-asset-close"></i></a>
            </h5>
        </div>
        <div class="card-body">
        <?= $form->field($model, 'asset_group')->widget(Select2::classname(), [
                                        'data' => $listAssetGroup,
                                        'options' => ['placeholder' => 'เลือกกลุ่มทรัพย์สิน'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('กลุ่ม');
                                    
                                    ?>
                                     <?= $form->field($model, 'asset_type')->widget(Select2::classname(), [
                                        'data' => $listAssetType,
                                        'options' => ['placeholder' => 'เลือกรายการครุภัณฑ์'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('ประเภท');
                                    
                                    ?>



        <?=$form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
    'name' => 'department',
    'query' => app\modules\hr\models\Organization::find()->addOrderBy('root, lft'),
    'value' => 1,
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => false,
    'options' => ['disabled' => false],
])->label('หน่วยงานภายในตามโครงสร้าง');?>


        <?= $form->field($model, 'asset_item')->widget(Select2::classname(), [
                                        'data' => $listAssetitem,
                                        'options' => ['placeholder' => 'เลือกรายการครุภัณฑ์'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        ],
                                    ])->label('ชื่อครุภัณฑ์');
                                    
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
</div>

                                    

    <div class="form-group">
    <?=AppHelper::BtnSave('ค้นหา')?>
    </div>
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