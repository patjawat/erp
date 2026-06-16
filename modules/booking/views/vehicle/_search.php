<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\vehiclesearch $model */
/** @var yii\widgets\ActiveForm $form */

$hasAdvancedFilters = !empty($model->status) || !empty($model->location) || !empty($model->emp) || !empty($model->not_logged);

$collapseId = 'vehicleCollapseFilter';
$toolbarFieldOpts = ['options' => ['class' => 'mb-0']];
?>

<?php $form = ActiveForm::begin([
    'action' => isset($action) && $action ? $action : ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1,
        'class' => 'vehicle-search-form',
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
]); ?>

<div class="vehicle-search-toolbar">
    <div class="row g-2 g-lg-3 align-items-center">

        <div class="col-12 col-lg-3">
            <div class="input-group w-100">
                <span class="input-group-text bg-body border-end-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </span>
                <?= $form->field($model, 'q', [
                    'template' => '{input}',
                    'options' => ['class' => 'flex-grow-1 min-w-0 mb-0'],
                ])->textInput([
                    'placeholder' => 'ค้นหา เลขที่ ผู้ขอ ปลายทาง...',
                    'class' => 'form-control border-start-0',
                    'id' => 'vehiclesearch-q',
                ])->label(false) ?>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-lg-2">
            <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
        </div>

        <div class="col-6 col-sm-4 col-lg-2">
            <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
        </div>

        <div class="col-6 col-sm-4 col-lg-2">
            <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex align-items-center justify-content-start justify-content-lg-end flex-wrap gap-2 vehicle-search-actions">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา',
                ['class' => 'btn btn-primary flex-grow-1 flex-sm-grow-0']) ?>

            <button class="btn btn-outline-primary flex-grow-1 flex-sm-grow-0"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#<?= $collapseId ?>"
                    aria-expanded="<?= $hasAdvancedFilters ? 'true' : 'false' ?>"
                    aria-controls="<?= $collapseId ?>"
                    id="btnVehicleToggleFilter">
                <i class="fa-solid fa-sliders me-1"></i> ตัวกรองเพิ่มเติม
            </button>

            <div class="dropdown flex-grow-1 flex-sm-grow-0">
                <button class="btn btn-success dropdown-toggle w-100 w-sm-auto"
                        type="button"
                        id="vehicleExcelDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <i class="fa-solid fa-file-excel me-1"></i> Excel
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="vehicleExcelDropdown">
                    <li>
                        <button type="button" class="dropdown-item btn-vehicle-export-excel export-leave">
                            <i class="fa-solid fa-file-excel me-2 text-success"></i> ส่งออก Excel
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="collapse mt-3 pt-3 border-top <?= $hasAdvancedFilters ? 'show' : '' ?>" id="<?= $collapseId ?>">
    <p class="text-muted small mb-3">
        <i class="fa-solid fa-info-circle me-1"></i>
        สถานะ · สถานที่ปลายทาง · ผู้ขอใช้รถ · สถานะการบันทึก
    </p>

    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <?= $form->field($model, 'status', $toolbarFieldOpts)->widget(Select2::classname(), [
                'data' => $model->listStatus(),
                'options' => ['placeholder' => 'สถานะทั้งหมด'],
                'pluginOptions' => ['allowClear' => true],
            ])->label('สถานะ') ?>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <?= $form->field($model, 'location', $toolbarFieldOpts)->widget(Select2::classname(), [
                'data' => $model->ListOrg(),
                'options' => ['placeholder' => 'สถานที่ไปทั้งหมด'],
                'pluginOptions' => [
                    'tags' => true,
                    'allowClear' => true,
                ],
            ])->label('สถานที่ปลายทาง') ?>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <?= $this->render('@app/components/ui/input_emp', [
                'form' => $form,
                'model' => $model,
                'label' => 'ผู้ขอใช้รถ',
                'placeholder' => 'ผู้ขอใช้รถยนต์',
            ]) ?>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <?= $form->field($model, 'not_logged', $toolbarFieldOpts)->dropDownList([
                '' => 'ทั้งหมด',
                '1' => 'ยังไม่บันทึกการเดินทาง',
            ], ['class' => 'form-select'])->label('สถานะการบันทึก') ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
thaiDatepicker('#vehiclesearch-date_start,#vehiclesearch-date_end');

\$("#vehiclesearch-date_start").on('change', function () {
    \$('#vehiclesearch-thai_year').val(null).trigger('change');
    \$('#vehiclesearch-date_filter').val(null).trigger('change');
});
\$("#vehiclesearch-date_end").on('change', function () {
    \$('#vehiclesearch-thai_year').val(null).trigger('change');
    \$('#vehiclesearch-date_filter').val(null).trigger('change');
});

(function () {
    var \$collapse = \$('#vehicleCollapseFilter');
    var \$toggle = \$('#btnVehicleToggleFilter');
    if (!\$collapse.length || !\$toggle.length) return;

    \$collapse.on('shown.bs.collapse', function () {
        \$toggle.attr('aria-expanded', 'true');
    });
    \$collapse.on('hidden.bs.collapse', function () {
        \$toggle.attr('aria-expanded', 'false');
    });
})();
JS;
$this->registerJS($js, View::POS_END);
?>
