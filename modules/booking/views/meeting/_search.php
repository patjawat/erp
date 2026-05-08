<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
use app\modules\hr\models\Organization;

$hasAdvancedFilters = !empty($model->title) || !empty($model->thai_year);

$toolbarFieldOpts = ['options' => ['class' => 'mb-0']];


/** @var yii\web\View $this */
/** @var app\modules\lm\models\meetingsearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<div class="card">
    <div class="card-header">
        <h6 class="mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">

        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
            'options' => [
                'data-pjax' => 0
            ],
        ]); ?>



        <div class="row">
            <div class="col-6 col-md-2">
                <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
            </div>
            <div class="col-6 col-md-2">
                <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
            </div>
            <div class="col-6 col-md-2">
                <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-12">
                <?= $form->field($model, 'room_id')->widget(Select2::classname(), [
                    'data' => $model->listRooms(),
                    'options' => ['placeholder' => '--ห้องประชุมทั้งหมด--'],
                    'pluginOptions' => [
                        'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                        'allowClear' => true,
                    ],
                ])->label(false) ?>

            </div>
             <div class="col-lg-2 col-md-2 col-sm-12">
                <?= $form->field($model, 'status')->widget(Select2::classname(), [
                    'data' => $model->listStatus(),
                    'options' => ['placeholder' => '--สถานะการจองทั้งหมด--'],
                    'pluginOptions' => [
                        'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                        'allowClear' => true,
                    ],
                ])->label(false) ?>

            </div>


            <div class="col-12 col-lg-auto ms-lg-auto d-flex align-items-center justify-content-start justify-content-lg-end flex-wrap gap-2 equip-search-actions">
                <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา', ['class' => 'btn btn-primary flex-grow-1 flex-sm-grow-0']) ?>
                <button class="btn btn-outline-primary flex-grow-1 flex-sm-grow-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
                    aria-expanded="<?= $hasAdvancedFilters ? 'true' : 'false' ?>" aria-controls="collapseFilter" id="btnToggleFilter">
                    <i class="fa-solid fa-sliders me-1"></i> ตัวกรองเพิ่มเติม
                </button>

            </div>

        </div>

        <div class="collapse mt-3 pt-3 border-top <?= $hasAdvancedFilters ? 'show' : '' ?>" id="collapseFilter">
            <!-- การกรองแบบละเอียด -->
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <?= $form->field($model, 'title')->textInput(['placeholder' => 'เรื่องการประชุ'])->label(false) ?>
                </div>
                <div class="col-6">
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
                            ])->label(false); ?>
                        </div>
                        <button type="button" class="btn btn-outline-secondary flex-shrink-0 mt-3" id="clear-q-department">
                            <i class="fa-solid fa-eraser me-1"></i> ล้าง
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<?php
$clearDepartmentJs = <<<'JS'
$(document).off('click', '#clear-q-department').on('click', '#clear-q-department', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $field = $('.field-meetingsearch-q_department');
    const $input = $field.find('#meetingsearch-q_department, input[name="MeetingSearch[q_department]"], input[name="department"]').first();

    if (!$input.length) {
        return;
    }

    const treeInput = $input.data('treeinput');
    const treeView = $input.data('treeview');

    $input.val('');
    $input.trigger('change');
    $input.trigger('treeview:change', ['', '']);

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