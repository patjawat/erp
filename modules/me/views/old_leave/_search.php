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
<div>
    

<div class="d-flex gap-2">
    
   
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

<?=$form->field($model, 'date_start')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ตั้งแต่วันที่');
                ?>
                <?=$form->field($model, 'date_end')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label('ถึงวันที่');
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
        <?php
            echo $form->field($model, 'status')->checkboxList($model->listStatus(),['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']
            );
               ?>
      
      <?php echo $form->field($model, 'leave_type_id')->checkboxList($model->listLeaveType(), 
                ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']
            );?>

<?= $form->field($model, 'status')->textInput()->label(false) ?>

            </div>
        </div>
        
    </div>
  
</div>

<div>

</div>

</div>
<?php ActiveForm::end(); ?>