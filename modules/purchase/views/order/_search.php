<?php

use yii\web\View;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    .right-setting {
        width: 500px !important;
    }
</style>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 0
    ],
]); ?>
<div class="row">
    <div class="col-3">
        <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type'])->all(), 'code', 'title'),
            'options' => ['placeholder' => 'ประเภททั้งหมด'],
            'theme' => Select2::THEME_KRAJEE_BS5,
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-2">
        <?php

        echo $form->field($model, 'date_between')->widget(Select2::classname(), [
            'data' => [
                'pr_create_date' => 'วันที่ขอซื้อ',
                'po_date' => 'วันที่สั่งซื้อ',
                'gr_date' => 'วันที่ตรวจรับ'
            ],
            'options' => ['placeholder' => 'ประเภทการค้นหา...'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-3">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => ArrayHelper::map($model->ListStatus(), 'code', 'title'),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>


    <div class="col-4">
        <?= $form->field($model, 'vendor_id')->widget(Select2::classname(), [
            'data' => $model->ListVendor(),
            'options' => ['placeholder' => 'ผู้ขายทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-4">
        <?= $form->field($model, 'q_budget_type')->widget(Select2::classname(), [
            'data' => $model->ListBudgetdetail(),
            'options' => ['placeholder' => 'ประเภทเงินทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
        <div class="d-flex flex-column flex-md-row gap-2">

            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> <span class="d-none d-sm-inline">ค้นหา</span>', [
                'class' => 'btn btn-primary w-100 w-md-auto',
                'id' => 'summit'
            ]) ?>

            <?= Html::a(
                '<i class="fa-solid fa-circle-plus"></i> <span class="d-none d-sm-inline">สร้างใหม่</span>',
                ['/sm/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'],
                ['class' => 'btn btn-light open-modal w-100 w-md-auto', 'data' => ['size' => 'modal-lg']]
            ) ?>

            <div class="dropdown w-100 w-md-auto">
                <button class="btn btn-success dropdown-toggle w-100 w-md-auto" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="d-none d-sm-inline">Excel</span>
                </button>

                <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton1">
                    <li><?= Html::a(
                            '<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV',
                            ['/sm/import-product', 'title' => '<i class="fas fa-file-csv text-white"></i> นำเข้าไฟล์ CSV'],
                            ['class' => 'dropdown-item open-modal']
                        ) ?>
                    </li>
                    <li><?= Html::a(
                            '<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า',
                            'https://docs.google.com/spreadsheets/d/.../edit?usp=sharing',
                            ['class' => 'dropdown-item', 'target' => '_blank']
                        ) ?>
                    </li>
                </ul>
            </div>

        </div>
    </div>

</div>

<div class="collapse mt-3" id="collapseFilter">



</div>
<?php ActiveForm::end(); ?>

<?php


$js = <<<JS

thaiDatepicker('#ordersearch-date_start,#ordersearch-date_end')
$(".filter-emp").on("click", function(){
  $("#filter-emp").addClass("show");
  localStorage.setItem('right-setting','show')
})

// $(".filter-emp-close").on("click", function(){
//     $(".right-setting").removeClass("show");
//     localStorage.setItem('right-setting','hide')
// })

var thaiYear = function (ct) {
    var leap=3;  
    var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];  
    if(ct){  
        var yearL=new Date(ct).getFullYear()-543;  
        leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;  
        if(leap==2){  
            dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];  
        }  
    }              
    this.setOptions({  
        i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,  
    })                
};    
 


JS;
$this->registerJS($js, View::POS_END)
?>