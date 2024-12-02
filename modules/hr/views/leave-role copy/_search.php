<?php

use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex align-items-center gap-2">
    
    <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label('คำค้นหา') ?>
    <?php echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณ'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '120px',
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                        $(this).submit()
                        }",
                        "select2:unselecting" => "function() {
                            $(this).submit()
                        }",
            ]
        ])->label('ปีงบประมาณ');
    ?>

    <div class="d-flex flex-row mb-3 mt-4">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>
    
    <div class="mb-3 mt-4">
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false"><i class="fa-solid fa-filter"></i> เพิ่มเติม
        </button>
        <div class="dropdown-menu p-4 relative" style="width:300px">
       
            </div>
        </div>
        
    </div>
  
</div>

<?php ActiveForm::end(); ?>