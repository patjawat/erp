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
        'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
    ]); ?>

<div class="row">
    <div class="col-lg-7 col-md-4 col-sm-12 col-sx-12">
        <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุรายการค้นหา...','class' => 'form-control'])->label(false) ?>
    </div>
        <div class="col-lg-4 col-md-4 col-sm-12 col-sx-12">
            <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->listThaiYear(),
            'options' => ['placeholder' => 'เลือกปี พ.ศ.'],
            'pluginOptions' => [
                'allowClear' => true,
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
                        ])->label(false);
                        ?>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-sx-12">

                            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i>', ['class' => 'btn btm-sm btn-primary']) ?>
                        </div>

</div>

    <?php ActiveForm::end(); ?>

</div>
