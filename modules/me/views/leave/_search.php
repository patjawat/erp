
<?php
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
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
    'id' => 'search-leave',
    'options' => [
        'data-pjax' => 1
    ],
     'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="row mb-2">
    <div class="col-2">
        <?=$this->render('@app/components/ui/_date_filter',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-5">
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
    <div class="row">
        <div class="col-6">
            <?php echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...','class' => 'form-control'])->label(false) ?>
        </div>
        <div class="col-2">

            <?=$form->field($model, 'thai_year')->widget(Select2::classname(), [
                    'data' => $model->ListThaiYear(),
                    'options' => [
                        'placeholder' => 'ปีงบประมาณทั้งหมด',
                        'id' => 'thaiYear',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'width' => '120px',
                    ],
        ])->label(false);?>

        </div>
        
        <div class="col-4">

            <?=$form->field($model, 'leave_type_id')->widget(Select2::classname(), [
                'data' => $model->listLeaveType(),
                    'options' => ['placeholder' => 'ประเภทการลาทั้งหมด'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label(false);?>
        </div>

    </div>

<div class="collapse mt-3" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
    


</div>

<?php ActiveForm::end(); ?>

