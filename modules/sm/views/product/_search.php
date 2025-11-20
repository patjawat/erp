<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="row">
    <div class="col-lg-4 col-lg-4 col-sm-12">
        <?php
        echo $form->field($model, 'category_id')->widget(Select2::classname(), [
            'data' => $model->ListProductType(),
            'options' => ['placeholder' => 'หมวดหมู่วัสดุทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                "select2:unselect" => "function() { $(this).submit(); }",
                'select2:select' => "function(result) { 
                                var data = \$(this).select2('data')[0].text;
                                \$('#order-data_json-product_type_name').val(data)
                                // $(this).submit();
                                }",
            ]
        ])->label(false);
        ?>
    </div>
    <div class="col-lg-2 col-lg-2 col-sm-12">
        <?= $form->field($model, 'metter_type')->widget(Select2::classname(), [
            'data' => $model->listMatterType(),
            'options' => ['placeholder' => 'ประเภทวัสดุทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-lg-2 col-lg-2 col-sm-12">
        <?= $form->field($model, 'unit')->widget(Select2::classname(), [
            'data' => $model->listUnit(),
            'options' => ['placeholder' => 'หน่วยทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
        <div class="col-lg-2 col-lg-2 col-sm-12">
        <div class="mt-1">
            <?= $form->field($model, 'innovation_account')->checkbox(['custom' => true, 'switch' => true, 'checked' => $model->innovation_account == "1" ? true : false])->label('แสดงบัญชีนวัตกรรม'); ?>
        </div>
    </div>
    <div class="col-lg-2 col-lg-2 col-sm-12">
        <div class="mt-1">
            <?= $form->field($model, 'active')->checkbox(['custom' => true, 'switch' => true, 'checked' => $model->active == "1" ? true : false])->label('สถานะเปิดใช้งาน'); ?>
        </div>
    </div>

    <div class="col-ุlg-9 col-lg-9 col-sm-12">
        <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา รายการวัสดุ , รหัส'])->label(false) ?>
    </div>

    <div class="col-lg-3 col-md-3 col-sm-12">
        
        <div class="d-flex justify-content-start gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button"
                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-excel"></i> Excel
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><?= Html::a('<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV', ['/sm/import-product', 'title' => '<i class="fas fa-file-csv text-white"></i> นำเข้าไฟล์ CSV'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                        <li><?= Html::a('<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า', 'https://docs.google.com/spreadsheets/d/1Z6I-Y7rTwiy_qF68xIgyZEKPthmTnkkezqMfomLfpyQ/edit?usp=sharing', ['class' => 'dropdown-item', 'target' => '_blank']) ?></li>
                    </ul>
                </div>
            </div>
    </div>
</div>
<?php ActiveForm::end(); ?>