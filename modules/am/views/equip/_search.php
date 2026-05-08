<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use app\models\Categorise;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\am\components\AssetHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetSearch $model */
/** @var yii\widgets\ActiveForm $form */
$listAssetitem = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_item_id'])->all(), 'code', 'title');
$listAssetType = ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(), 'code', 'title');

$hasAdvancedFilters = !empty($model->q_department) || !empty($model->owner) || !empty($model->no_department) || !empty($model->no_owner)
    || !empty($model->method_get) || !empty($model->budget_type) || !empty($model->on_year) || !empty($model->q_receive_date)
    || !empty($model->po_number) || (isset($model->price1) && $model->price1 !== '') || (isset($model->price2) && $model->price2 !== '') || !empty($model->price_below);

$toolbarFieldOpts = ['options' => ['class' => 'mb-0']];

?>

<style>

</style>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'options' => [
        'data-pjax' => 0,
        'class' => 'equip-search-form',
    ],
]); ?>

<!-- ตัวกรองหลัก: ค้นหา · หมวด · สภาพ | ปุ่ม action ด้านขวา -->
<div class="equip-search-toolbar">
    <div class="row g-2 g-lg-3 align-items-center">
        <div class="col-12 col-lg-3">
            <div class="input-group w-100">
                <span class="input-group-text bg-body border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                <?= $form->field($model, 'q', [
                    'template' => '{input}',
                    'options' => ['class' => 'flex-grow-1 min-w-0 mb-0'],
                ])
                    ->textInput(['placeholder' => 'ค้นหา...', 'class' => 'form-control border-start-0'])
                    ->label(false) ?>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-2">
            <?php

            echo $form->field($model, 'asset_type_id', $toolbarFieldOpts)->widget(Select2::classname(), [
                'data' => AssetHelper::listAssetType(),
                'options' => [
                    'placeholder' => 'ทุกประเภท (หมวดหลัก)',
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
        <div class="col-12 col-sm-6 col-lg-2">
            <?php
            echo $form->field($model, 'asset_category_id', $toolbarFieldOpts)->widget(DepDrop::classname(), [
                'options' => [
                    'placeholder' => 'ทุกหมวด',
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

            ])->label(false); ?>
        </div>
        <div class="col-12 col-sm-3 col-lg-1">
            <?php
            echo $form->field($model, 'asset_condition', $toolbarFieldOpts)->widget(Select2::classname(), [
                'data' => $model->ListAssetCondition(),
                'options' => ['placeholder' => '--ทุกสภาพ--'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'pluginEvents' => [
                    "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                ]
            ])->label(false);
            ?>
        </div>
        <div class="col-12 col-sm-3 col-lg-1">
            <?php
            echo $form->field($model, 'asset_status', $toolbarFieldOpts)->widget(Select2::classname(), [
                'data' => $model->ListAssetStatus(),
                'options' => ['placeholder' => '--ทุกสถานะ--'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'pluginEvents' => [
                    "select2:select" => "function(result) { 
                                            var data = $(this).select2('data')[0]
                                            $('#asset-data_json-method_get_text').val(data.text)
                                         }",
                ]
            ])->label(false);
            ?>
        </div>
        <div class="col-12 col-lg-auto ms-lg-auto d-flex align-items-center justify-content-start justify-content-lg-end flex-wrap gap-2 equip-search-actions">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา', ['class' => 'btn btn-primary flex-grow-1 flex-sm-grow-0']) ?>
            <button class="btn btn-outline-primary flex-grow-1 flex-sm-grow-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                aria-expanded="<?= $hasAdvancedFilters ? 'true' : 'false' ?>" aria-controls="collapseFilter" id="btnToggleFilter">
                <i class="fa-solid fa-sliders me-1"></i> ตัวกรองเพิ่มเติม
            </button>
            <div class="dropdown flex-grow-1 flex-sm-grow-0">
                <button class="btn btn-success dropdown-toggle w-100 w-sm-auto" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                    <li><?= Html::a('<i class="fa-solid fa-table me-2"></i> ดาวน์โหลด Template', ['/am/import/download-template'], ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener', 'data-pjax' => 0]) ?></li>
                    <li><button type="button" class="dropdown-item btn-export-excel"><i class="fa-solid fa-file-excel me-2"></i> ส่งออก Excel</button></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><?= Html::a('<i class="fa-solid fa-file-import me-2"></i> นำเข้าข้อมูล', ['/am/import', 'title' => 'นำเข้าไฟล์ CSV'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>


<!-- ตัวกรองเพิ่มเติม: แสดงเมื่อกด "ตัวกรองเพิ่มเติม" (หรือเปิดอัตโนมัติถ้ามีค่ากรองอยู่) -->
<div class="collapse mt-3 pt-3 border-top <?= $hasAdvancedFilters ? 'show' : '' ?>" id="collapseFilter">
    <p class="text-muted small mb-3"><i class="fa-solid fa-info-circle me-1"></i> หน่วยงาน · ผู้รับผิดชอบ · วิธีได้มา · ช่วงราคา</p>

    <!-- กลุ่ม: หน่วยงาน & ผู้รับผิดชอบ -->
    <div class="mb-3">
        <span class="d-block small text-uppercase fw-semibold text-secondary mb-2">หน่วยงาน & ผู้รับผิดชอบ</span>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-end align-items-center gap-2">
                    <div class="flex-grow-1">
                        <?= $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
                            'name' => 'department',
                            'query' => Organization::find()->addOrderBy('root, lft'),
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
                        ])->label('หน่วยงานภายในตามโครงสร้าง'); ?>
                    </div>
                    <button type="button" class="btn btn-outline-secondary flex-shrink-0 mt-3" id="clear-q-department">
                        <i class="fa-solid fa-eraser me-1"></i> ล้าง
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <?php
                $url = \yii\helpers\Url::to(['/depdrop/employee']);
                $ownerEmp = !empty($model->owner) ? Employees::findOne(['cid' => $model->owner]) : null;
                $owner = $ownerEmp ? $ownerEmp->fullname : '';
                echo $form->field($model, 'owner')->widget(Select2::classname(), [
                    'initValueText' => $owner,
                    'options' => ['placeholder' => 'ผู้รับผิดชอบ'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 1,
                        'language' => ['errorLoading' => new JsExpression("function () { return 'กำลังโหลด...'; }")],
                        'ajax' => ['url' => $url, 'dataType' => 'json', 'data' => new JsExpression('function(params) { return {q:params.term}; }')],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(city) { return city.text; }'),
                        'templateSelection' => new JsExpression('function (city) { return city.text; }'),
                    ],
                ])->label('ผู้รับผิดชอบ'); ?>
            </div>
        </div>
    </div>

    <!-- กลุ่ม: กรองเฉพาะ -->
    <div class="mb-3">
        <span class="d-block small text-uppercase fw-semibold text-secondary mb-2">กรองเฉพาะ</span>
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                        <input type="hidden" name="AssetSearch[no_department]" value="0">
                        <?= Html::checkbox('AssetSearch[no_department]', !empty($model->no_department), ['value' => '1', 'id' => 'assetsearch-no_department', 'class' => 'form-check-input']) ?>
                        <label class="form-check-label" for="assetsearch-no_department">ที่ยังไม่กำหนดหน่วยงาน</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="AssetSearch[no_owner]" value="0">
                        <?= Html::checkbox('AssetSearch[no_owner]', !empty($model->no_owner), ['value' => '1', 'id' => 'assetsearch-no_owner', 'class' => 'form-check-input']) ?>
                        <label class="form-check-label" for="assetsearch-no_owner">ที่ยังไม่มีผู้รับผิดชอบ</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- กลุ่ม: วิธีได้มา & งบประมาณ -->
    <div class="mb-3">
        <span class="d-block small text-uppercase fw-semibold text-secondary mb-2">วิธีได้มา & งบประมาณ</span>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <?= $form->field($model, 'method_get')->widget(Select2::classname(), [
                    'data' => $model->ListMethodget(),
                    'options' => ['placeholder' => 'วิธีได้มาทั้งหมด'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('วิธีได้มา'); ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <?= $form->field($model, 'budget_type')->widget(Select2::classname(), [
                    'data' => $model->ListBudgetdetail(),
                    'options' => ['placeholder' => 'ประเภทเงินทั้งหมด'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('ประเภทเงิน'); ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <?= $form->field($model, 'on_year')->widget(Select2::classname(), [
                    'data' => $model->ListOnYear(),
                    'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label('ปีงบประมาณ'); ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <?= $form->field($model, 'q_receive_date')->textInput(['placeholder' => 'วันที่รับเข้า'])->label('วันที่รับเข้า'); ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <?= $form->field($model, 'po_number')->textInput(['placeholder' => 'เลขที่สั่งซื้อ'])->label('เลขที่สั่งซื้อ'); ?>
            </div>
        </div>
    </div>

    <!-- กลุ่ม: ช่วงราคา -->
    <div>
        <span class="d-block small text-uppercase fw-semibold text-secondary mb-2">ช่วงราคา (บาท)</span>
        <div class="row g-3">
            <div class="col-12 col-sm-4">
                <?= $form->field($model, 'price1')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0', 'placeholder' => 'ราคาต่ำสุด'])->label('ราคาต่ำสุด (ขึ้นไป)'); ?>
            </div>
            <div class="col-12 col-sm-4">
                <?= $form->field($model, 'price2')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0', 'placeholder' => 'ราคาสูงสุด'])->label('ราคาสูงสุด (ลงมา)'); ?>
            </div>
            <div class="col-12 col-sm-4">
                <?= $form->field($model, 'price_below')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0', 'placeholder' => 'เช่น 5000'])->label('ที่ราคาต่ำกว่าเกณฑ์'); ?>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>


<?php
$js = <<< JS

thaiDatepicker('#assetsearch-q_receive_date')

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

$('.btn-export-excel').click(function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการดาวน์โหลด?',
        text: "คุณต้องการส่งออกข้อมูลตามเงื่อนไขนี้เป็นไฟล์ Excel ใช่หรือไม่?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'ใช่, ดาวน์โหลดเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = $('.equip-search-form');
            form.append('<input type="hidden" name="export" value="excel" id="export-excel-input">');
            form[0].submit(); // Use native submit to bypass PJAX/AJAX
            setTimeout(() => {
                $('#export-excel-input').remove();
            }, 500);
        }
    });
});

JS;
$this->registerJS($js);
?>

<?php
$clearDepartmentJs = <<<'JS'
$('#clear-q-department').on('click', function() {
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

?>