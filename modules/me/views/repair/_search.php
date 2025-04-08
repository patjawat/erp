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

<?=$this->render('@app/components/Search',['form' => $form,'model' => $model,'showEmp' => false])?>
    

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS
    thaiDatepicker('#helpdesksearch-date_start,#helpdesksearch-date_end')
JS;
$this->registerJS($js, View::POS_END);

?>