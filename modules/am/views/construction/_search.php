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
         'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
    ]); ?>

<div class="row">
    <div class="col-lg-11 col-md-11 col-sm-12">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...','width' => '100'])->label(false)->label(false) ?>
    </div>
    <div class="col-1">
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
    <div class="d-flex align-items-end gap-2">
        <div class="flex-grow-1">
            <?=$form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
                'name' => 'department',
                'query' => app\modules\hr\models\Organization::find()->addOrderBy('root, lft'),
                'value' => !empty($model->q_department) ? $model->q_department : null,
                'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                'fontAwesome' => true,
                'asDropdown' => true,
                'multiple' => false,
                'options' => ['disabled' => false],
                'dropdownConfig' => [
                    'input' => [
                        'placeholder' => 'เลือกหน่วยงาน...',
                    ],
                ],
                'pluginEvents' => [
                    'treeview:change' => new JsExpression('function() {
                        var $container = $(this).closest(".kv-tree-dropdown-container");
                        setTimeout(function() {
                            var $toggle = $container.find(".kv-tree-input");
                            $toggle.removeClass("show open").attr("aria-expanded", "false");
                            $container.find(".kv-tree-dropdown").removeClass("show open");
                            if (window.bootstrap && bootstrap.Dropdown && $toggle.length) {
                                try {
                                    var instance = bootstrap.Dropdown.getInstance($toggle[0]) || bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
                                    if (instance) {
                                        instance.hide();
                                    }
                                } catch (e) {}
                            }
                        }, 0);
                    }'),
                ],
            ])->label('หน่วยงานภายในตามโครงสร้าง');?>
        </div>
        <button type="button" class="btn btn-outline-secondary flex-shrink-0" id="clear-q-department-construction">
            <i class="fa-solid fa-eraser me-1"></i> ล้าง
        </button>
    </div>
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

<?php
$clearDepartmentJs = <<<'JS'
$('#clear-q-department-construction').on('click', function() {
    const $field = $('.field-assetsearch-q_department');
    const $input = $field.find('#assetsearch-q_department, input[name="AssetSearch[q_department]"]').first();
    const treeInput = $input.data('treeinput');
    const treeView = $input.data('treeview');

    if (!$input.length) {
        return;
    }

    $input.val('');
    $input.trigger('treeview:change', ['', '']);
    $input.trigger('change');

    if (treeView && treeView.$tree) {
        treeView.$tree.find('.kv-selected').removeClass('kv-selected');
        if (typeof treeView.disableToolbar === 'function') {
            treeView.disableToolbar();
        }
    }

    if (treeInput && typeof treeInput.setInput === 'function') {
        treeInput.setInput([]);
    } else if (treeInput && treeInput.$input) {
        treeInput.$input.html(treeInput.caret + treeInput.placeholder);
    }

    const $toggle = $field.find('.kv-tree-input').first();
    if ($toggle.length) {
        $toggle.attr('aria-expanded', 'false');
    }

    const $container = $field.find('.kv-tree-dropdown-container').first();
    if ($container.length) {
        $container.removeClass('show open');
        if (window.bootstrap && bootstrap.Dropdown && $toggle.length) {
            try {
                var instance = bootstrap.Dropdown.getInstance($toggle[0]) || bootstrap.Dropdown.getOrCreateInstance($toggle[0]);
                if (instance) {
                    instance.hide();
                }
            } catch (e) {}
        }
    }
});
JS;
$this->registerJS($clearDepartmentJs);
