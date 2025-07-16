<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\DateFilterHelper;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
.offcanvas-footer {
    padding: 1rem 1rem;
    border-top: 1px solid #dee2e6;
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

<div class="row">
    <div class="col-3">
        <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-2">
        <?php
        echo $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' =>  DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ช่วงเวลาทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
                // 'width' => '130px',
            ],
            ])->label(false);
            ?>

    </div>

    <div class="col-2">
        <?php echo $form->field($model, 'date_start')->textInput(['class' => 'form-control','placeholder' => 'เริ่มจากวันที่'])->label(false);?>
    </div>
    <div class="col-2">
        <?php echo $form->field($model, 'date_end')->textInput(['class' => 'form-control','placeholder' => 'ถึงวีนที่'])->label(false);?>
    </div>
    <div class="col-2">
        <?=$form->field($model, 'status')->widget(Select2::classname(), [
        'data' => $model->listStatus(),
        'options' => ['placeholder' => 'สถานะทั้งหมด'],
        'pluginOptions' => [
            'allowClear' => true,
            // 'width' => '150px',
        ],
        ])->label(false);?>
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
    <!-- การกรองแบบละเอียด -->
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
<?= $form->field($model, 'title')->textInput(['placeholder' => 'เรื่องการประชุ'])->label(false) ?>
        </div>
        <div class="col-3">

            <?=$form->field($model, 'thai_year')->widget(Select2::classname(), [
                    'data' => $model->ListThaiYear(),
                    'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'width' => '120px',
                    ],
        ])->label(false);?>

        </div>

        <div class="col-4">

          
        </div>
    </div>


</div>

<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    thaiDatepicker('#leavesearch-date_start,#leavesearch-date_end')

    $("#leavesearch-date_start").on('change', function() {
            $('#leavesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#leavesearch-date_end").on('change', function() {
            $('#leavesearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });

JS;
$this->registerJS($js, View::POS_END);

?>