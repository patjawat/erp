<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
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
        'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
        'options' => [
            'data-pjax' => 0
        ],
    ]); ?>
    <div class="d-flex justify-content-center align-items-center gap-2">



<?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control'])->label(false) ?>
 <?=$form->field($model, 'room_id')->widget(Select2::classname(), [
                'data' => $model->listRooms(),
                    'options' => ['placeholder' => 'ห้องประชุมทั้งหทด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '150px',
                    ],
                ])->label(false);?>
            <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
            ],
        ])->label(false);
        ?>



            <?=$form->field($model, 'thai_year')->widget(Select2::classname(), [
                    'data' => $model->ListThaiYear(),
                    'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '120px',
                    ],
        ])->label(false);?>
        

    <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>

    <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => '__/__/____'])->label(false);?>


        <?=$form->field($model, 'status')->widget(Select2::classname(), [
                'data' => $model->listStatus(),
                    'options' => ['placeholder' => 'สถานะทั้งหทด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'width' => '150px',
                    ],
                ])->label(false);?>
                   <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>

         </div>           

<?php ActiveForm::end(); ?>

<?php


$js = <<<JS
thaiDatepicker('#meetingsearch-date_start,#meetingsearch-date_end')

JS;
$this->registerJS($js, View::POS_END)
?>