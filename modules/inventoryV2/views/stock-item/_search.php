<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\StockItemSearch $model */
/** @var yii\widgets\ActiveForm $form */
$warehouses = $warehouses ?? ['' => '-- ทุกคลัง --'];
?>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1,
        'class' => 'search-form',
    ],
]); ?>

<!-- แถวที่ 1: ค้นหาหลัก + ปุ่มดำเนินการ (UX: งานหลักอยู่บนสุด) -->
<div class="row align-items-end mb-3">
    <div class="col-12 col-md-7 col-lg-8 mb-2 mb-md-0">
        <?= $form->field($model, 'q', [
            'options' => ['class' => 'mb-0'],
        ])->textInput([
            'placeholder' => 'ค้นหาชื่อวัสดุ หรือรหัสพัสดุ...',
            'class' => 'form-control form-control-lg',
        ])->label(false) ?>
    </div>
    <div class="col-12 col-md-5 col-lg-4">
        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา', [
                'class' => 'btn btn-primary',
                'id' => 'summit',
            ]) ?>
            <?= Html::a(
                '<i class="fa-solid fa-rotate-left me-1"></i> ล้าง',
                ['index'],
                ['class' => 'btn btn-outline-secondary']
            ) ?>
            <?= Html::a(
                '<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่',
                ['/inventory-v2/stock-item/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'],
                ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']]
            ) ?>
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel me-1"></i> Excel
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                    <li><?= Html::a(
                        '<i class="fa-solid fa-file-export me-2"></i> ส่งออก Excel',
                        array_merge(['/inventory-v2/stock-item/export-excel'], Yii::$app->request->queryParams),
                        ['class' => 'dropdown-item', 'target' => '_blank']
                    ) ?></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><?= Html::a(
                        '<i class="fa-solid fa-file-csv me-2"></i> นำเข้าด้วย CSV',
                        ['/sm/import-product', 'title' => '<i class="fas fa-file-csv text-white"></i> นำเข้าไฟล์ CSV'],
                        ['class' => 'dropdown-item open-modal']
                    ) ?></li>
                    <li><?= Html::a(
                        '<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า',
                        'https://docs.google.com/spreadsheets/d/1Z6I-Y7rTwiy_qF68xIgyZEKPthmTnkkezqMfomLfpyQ/edit?usp=sharing',
                        ['class' => 'dropdown-item', 'target' => '_blank']
                    ) ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- แถวที่ 2: ตัวกรอง (เรียงตามการใช้งาน: คลัง → ยอด → หมวดหมู่ → ประเภท → หน่วย) -->
<div class="row g-2 mb-3">
    <div class="col-12">
        <small class="text-muted fw-medium d-block mb-2">
            <i class="fa-solid fa-filter me-1"></i> ตัวกรอง
        </small>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'warehouse_id', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
            'data' => $warehouses,
            'options' => ['placeholder' => 'คลัง'],
            'pluginOptions' => ['allowClear' => true],
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'balance_status', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
            'data' => [
                '' => 'ทั้งหมด',
                'below' => 'ต่ำกว่ากำหนด',
                'above' => 'มากกว่ากำหนด',
            ],
            'options' => ['placeholder' => 'ยอด Min/Max'],
            'pluginOptions' => ['allowClear' => true],
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'balance_min', ['options' => ['class' => 'mb-0']])->textInput([
            'type' => 'number',
            'step' => 'any',
            'placeholder' => 'ยอดตั้งแต่ (≥) เช่น 2',
            'class' => 'form-control',
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'balance_max', ['options' => ['class' => 'mb-0']])->textInput([
            'type' => 'number',
            'step' => 'any',
            'placeholder' => 'ยอดไม่เกิน (≤) เช่น 10',
            'class' => 'form-control',
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'is_active', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
            'data' => [
                '' => 'ทั้งหมด',
                '1' => 'เปิดใช้งาน',
                '0' => 'ปิดใช้งาน',
            ],
            'options' => ['placeholder' => 'สถานะ'],
            'pluginOptions' => ['allowClear' => true],
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'category_id', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
            'data' => $model->ListStockItemType(),
            'options' => ['placeholder' => 'หมวดหมู่'],
            'pluginOptions' => ['allowClear' => true],
            'pluginEvents' => [
                'select2:unselect' => 'function() { $(this).closest("form").submit(); }',
                'select2:select' => "function(result) {
                    var data = $(this).select2('data')[0].text;
                    $('#order-data_json-product_type_name').val(data);
                }",
            ],
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'metter_type', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
            'options' => ['placeholder' => 'ประเภทวัสดุ'],
            'pluginOptions' => ['allowClear' => true],
        ])->label(false) ?>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <?= $form->field($model, 'unit', ['options' => ['class' => 'mb-0']])->widget(Select2::classname(), [
            'data' => $model->listUnit(),
            'options' => ['placeholder' => 'หน่วย'],
            'pluginOptions' => ['allowClear' => true],
        ])->label(false) ?>
    </div>
</div>

<!-- แถวที่ 3: สวิตช์ตัวเลือก -->
<div class="row align-items-center border-top pt-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-4 gap-md-5 align-items-center">
            <?= $form->field($model, 'is_innovation', ['options' => ['class' => 'mb-0']])->checkbox([
                'custom' => true,
                'switch' => true,
                'checked' => $model->is_innovation == '1' ? true : false,
            ])->label('บัญชีนวัตกรรม') ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
