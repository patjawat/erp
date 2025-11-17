<?php

use yii\helpers\Html;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\components\DateFilterHelper;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */

?>

<?php $form = ActiveForm::begin([
    'action' => ['list-by-order'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="row">

<div class="col-lg-2 col-md-2 col-sm-12">

        <?= $form->field($model, 'q_warehouse_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name'),
            // 'data' => ArrayHelper::map(Warehouse::find()->all(),'id','warehouse_name'),
            'options' => ['placeholder' => 'คลังทั้งหมด'],
            'pluginEvents' => [
                "select2:unselect" => "function() { 
                }",
                "select2:select" => "function() {

                    }",
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);

        ?>
    </div>

    <div class="col-lg-3 col-md-3 col-sm-12">
        <?= $form->field($model, 'q_asset_type')->widget(Select2::classname(), [
            'data' => $model->ListAssetType(),
            'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
         <?= $form->field($model, 'transaction_type')->widget(Select2::classname(), [
            'data' => ['IN' => 'รับเข้า', 'OUT' => 'จ่ายออก'],
            'options' => ['placeholder' => 'เลือกความเคลื่อนไหว'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control', 'placeholder' => 'เริ่มจากวันที่'])->label(false); ?>
    </div>
   <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control', 'placeholder' => 'ถึงวันที่'])->label(false); ?>
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
    <div class="row mt-2">
         <div class="col-lg-2 col-md-2 col-sm-12">
                <?= $form->field($model, 'q_code')->widget(Select2::classname(), [
                'data' => $model->ListCode(),
                'options' => ['placeholder' => 'เลขที่'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(false); ?>
            </div>
                <div class="col-3">
                <?= $form->field($model, 'q_vendor')->widget(Select2::classname(), [
                'data' => $model->ListVendor(),
                'options' => ['placeholder' => 'ผู้ขาย'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(false); ?>
            </div>
         <div class="col-4">
           <?=$form->field($model, 'asset_item')->widget(Select2::classname(), [
            'data' =>  ArrayHelper::map(Categorise::find()->where(['name' => 'asset_item', 'group_id' => 'EQUIP'])->all(), 'code',function($model){
                 return $model->code.' '.$model->title;
            }),
            'options' => ['placeholder' => 'เลือกรายการวัสดุ'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>

        </div>



    </div>

</div>


<div class="collapse mt-3" id="collapseFilter">
    
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

thaiDatepicker('#stockeventsearch-date_start,#stockeventsearch-date_end')

    $('#stockeventsearch-date_start').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
    });
    
    $('#stockeventsearch-date_end').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');

    });

        $('#stockeventsearch-date_filter').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
    });



 
JS;
$this->registerJS($js);
?>