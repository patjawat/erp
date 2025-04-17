<?php
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\widgets\FlatpickrWidget;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
?>

<div class="team-group-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="d-flex gap-2">
    <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control'])->label('คำค้นหา') ?>
    <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->listThaiYear(),
            'options' => ['placeholder' => 'เลือกปี พ.ศ.'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '150px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => "function() {
                             $(this).submit()
                            $('#leavesearch-date_start').val('');
                            $('#leavesearch-date_end').val('');
                        }",
            ]
        ])->label('ปี พ.ศ.');
        ?>


    <div class="d-flex flex-row align-items-center gap-2 mt-3">
        <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btm-sm btn-light']) ?>
    </div>
</div>

    <?php ActiveForm::end(); ?>

</div>
