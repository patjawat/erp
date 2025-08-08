<?php

use yii\helpers\Html;
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
    'action' => ['list-summary'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="row">


    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ช่วงเวลาทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
                'pluginEvents' => [
        "select2:clear" => "function() {
            $('#stocktransactionsearch-date_start, #stocktransactionsearch-date_end').val('');
        }",
    ],
        ])->label(false);
        ?>



    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control', 'placeholder' => 'เริ่มจากวันที่'])->label(false); ?>
    </div>
   <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control', 'placeholder' => 'ถึงวีนที่'])->label(false); ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">

        <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
            // 'data' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name'),
            'data' => ArrayHelper::map(Warehouse::find()->all(),'id','warehouse_name'),
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

    <div class="col-lg-2 col-md-2 col-sm-12">
        <?= $form->field($model, 'asset_type')->widget(Select2::classname(), [
            'data' => $model->ListAssetType(),
            'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                $(this).submit()
                }",
                'select2:unselecting' => "function(result) { 
                    $(this).submit()
                    }",

            ]
        ])->label(false);
        ?>
    </div>
    <div class="col-lg-1 col-md-1 col-sm-12">
         <?= $form->field($model, 'transaction_type')->widget(Select2::classname(), [
            'data' => ['IN' => 'รับเข้า', 'OUT' => 'จ่ายออก'],
            'options' => ['placeholder' => 'เลือกความเคลื่อนไหว'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
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
    <div class="row">
          <div class="col-2">
              <?php echo $form->field($model, 'code')->textInput([ 'placeholder' => 'เลขที่'])->label(false); ?>
            </div>
            <div class="col-2">
                
                </div>

    
        <div class="col-3">
            <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
                'data' => $model->ListThaiYear(),
                'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                    // 'width' => '120px',
                ],
            ])->label(false); ?>

        </div>



    </div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS

thaiDatepicker('#stocktransactionsearch-date_start,#stocktransactionsearch-date_end')

    $('#stocktransactionsearch-date_start').change(function (e) { 
        e.preventDefault();
        $('#stocktransactionsearch-thai_year').val(null).trigger('change');
    });
    
    $('#stocktransactionsearch-date_end').change(function (e) { 
        e.preventDefault();
        $('#stocktransactionsearch-thai_year').val(null).trigger('change');

    });

        $('#stocktransactionsearch-date_filter').change(function (e) { 
        e.preventDefault();
        $('#stocktransactionsearch-thai_year').val(null).trigger('change');
    });



 
JS;
$this->registerJS($js);
?>