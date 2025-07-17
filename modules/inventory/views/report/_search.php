<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\modules\inventory\models\Warehouse;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductTypeSearch $model */
/** @var yii\widgets\ActiveForm $form */
$months = [
    10 => "ตุลาคม",
    11 => "พฤศจิกายน",
    12 => "ธันวาคม",
    1 => "มกราคม",
    2 => "กุมภาพันธ์",
    3 => "มีนาคม",
    4 => "เมษายน",
    5 => "พฤษภาคม",
    6 => "มิถุนายน",
    7 => "กรกฎาคม",
    8 => "สิงหาคม",
    9 => "กันยายน"
];
?>

    <?php $form = ActiveForm::begin([
        'action' => ['/inventory/report/index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row">

<div class="col-lg-4 col-lg-4 col-sm-12">

    <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(),'id','warehouse_name'),
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

    
    <?php
            echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
                'data' => $model->ListGroupYear(),
                'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
                'pluginEvents' => [
                    'select2:select' => "function(result) { 
                        $(this).submit()
                        }",
                        "select2:unselecting" => "function() {
                            $(this).submit()
                            }",
                            ]
                            ])->label(false);
                            ?>
                            </div>
                  <div class="col-2">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => 'เริ่มจากวันที่'])->label(false);?>
    </div>
    <div class="col-2">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => 'ถึงวีนที่'])->label(false);?>
    </div>
    
    <div class="col-1">
        <?php echo Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
                
</div>
    <?php ActiveForm::end(); ?>

<?php
$js = <<< JS

thaiDatepicker('#stockeventsearch-date_start,#stockeventsearch-date_end')

    $('#stockeventsearch-date_start').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        $(this).submit();
    });
    
    $('#stockeventsearch-date_end').change(function (e) { 
        e.preventDefault();
        $('#stockeventsearch-thai_year').val(null).trigger('change');
        $(this).submit();
    });



 
JS;
$this->registerJS($js);
?>
