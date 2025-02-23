<?php

use yii\web\View;
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
        
       <!-- <div class="d-flex justify-content-between gap-2" style="width:250px">
       <?php echo $form->field($model, 'date_start')->textInput()->label('ตั้งแต่วันที่');?>
       <?php echo $form->field($model, 'date_end')->textInput()->label('ถึงวันที่');?>
      
            </div> -->
        <div class="d-flex flex-row mb-3 mt-4">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
        </div>
       
        
    

    </div>
</div>
<?php ActiveForm::end(); ?>


<?php

$js = <<< JS
    thaiDatepicker('#helpdesksearch-date_start,#helpdesksearch-date_end')
JS;
$this->registerJS($js, View::POS_END);

?>