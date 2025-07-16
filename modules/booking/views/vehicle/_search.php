<?php
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\vehiclesearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'action' => isset($action) && $action ? $action : ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
      'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
]); ?>
<?php // $this->render('@app/components/ui/Search',['form' => $form,'model' => $model])?>

<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-12">
        <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false])?>
    </div>

    <div class="col-lg-2 col-md-2 col-sm-12">
        <?= $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' => DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false) ?>
    </div>


    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => 'เริ่มจากวันที่'])->label(false);?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-12">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => 'ถึงวีนที่'])->label(false);?>
    </div>

    <div class="col-lg-2 col-md-2 col-sm-12">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 

                }',
                'select2:unselecting' => 'function() {

                }',
            ]
        ])->label(false) ?>
    </div>
    <div class="col-lg-1 col-md-1 col-sm-12">
        <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
            aria-expanded="false" aria-controls="collapseFilter">
            <i class="fa-solid fa-filter"></i>
        </button>
    </div>



    <div class="collapse mt-3" id="collapseFilter">
        <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                    // $(this).submit()
                }',
                'select2:unselecting' => 'function() {
                    // $(this).submit()
                }',
            ]
        ])->label(false) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<< JS

    thaiDatepicker('#vehiclesearch-date_start,#vehiclesearch-date_end')
    $("#vehiclesearch-date_start").on('change', function() {
            $('#vehiclesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#vehiclesearch-date_end").on('change', function() {
            $('#vehiclesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


JS;
$this->registerJS($js, View::POS_END);
?>