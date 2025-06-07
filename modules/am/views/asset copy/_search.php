<?php

use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\Categorise;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;

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
        'action' => ['/am/asset'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 0
        ],
         'fieldConfig' => ['options' => ['class' => 'form-group mb-3 mr-2 me-2']] // spacing form field groups
    ]); ?>

<div class="d-flex gap-/">
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...','width' => '100'])->label(false)->label(false) ?>


    <div class="d-flex flex-row align-items-center gap-2">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btm-sm btn-primary']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
            aria-controls="offcanvasRight" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม..."><i
                class="fa-solid fa-filter"></i></button>
        <?= Html::a('<i class="bi bi-list-ul"></i>', ['/setting/set-view', 'view' => 'list'], ['class' => 'btn btn-outline-primary setview']) ?>
                <?= Html::a('<i class="bi bi-grid"></i>', ['/setting/set-view', 'view' => 'grid'], ['class' => 'btn btn-outline-primary setview']) ?>
                <?=Html::a('<i class="fa-solid fa-file-import me-1"></i>',['/am/asset/import-csv'],['class' => 'btn btn-outline-primary','title' => 'นำเข้าข้อมูลจากไฟล์ .csv',
            'data' => [
                'bs-placement' => 'top',
                'bs-toggle' => 'tooltip',
                ]])?>
    </div>

</div>


<!-- Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <?= $form->field($model, 'asset_group')->hiddenInput()->label(false);
                                    
                                    ?>
                                        <?= $form->field($model, 'asset_type')->widget(Select2::classname(), [
                                        'data' => $listAssetType,
                                        'options' => ['placeholder' => 'เลือกรายการครุภัณฑ์'],
                                        'pluginOptions' => [
                                        'allowClear' => true,
                                        'width' => '500px',
                                        ],
                                    ])->label('ประเภททรัพย์สิน');
                                    
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
'dropdownConfig' => [
        'input' => [
            'placeholder' => 'หน่วยงา...',
        ],
        'options' => [
            // 'style' => 'width:300px;' // ✅ กำหนดความกว้างของ dropdown
        ]
    ],
])->label('หน่วยงานภายในตามโครงสร้าง');?>
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





        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-light mt-3']);?>
        <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-exclamation"></i> รายการไม่สมบูรณ์',
                    'url' => ['/am/asset/omit'],
                    'modal' => true, 
                    'size' => 'lg',
                    'class' => 'btn btn-danger mt-3'
                    ]
                    )?>
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