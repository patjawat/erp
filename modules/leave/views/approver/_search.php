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
    'options' => [
        'data-pjax' => 0
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-2 mr-2 me-2']] // spacing form field groups
]); ?>

<?php $this->render('@app/components/ui/_filter', [
    'form' => $form,
    'model' => $model,
    'label' => false,
    'status' => $model->listStatus()
])
?>
<div class="row">
 <div class="col-2">
        <?=$this->render('@app/components/ui/_date_filter',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-3">
        <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false])?>
    </div>
    <div class="col-3">
        <?=$form->field($model, 'status')->widget(Select2::classname(), [
        'data' => $model->listStatus(),
        'options' => ['placeholder' => 'สถานะทั้งหมด'],
        'pluginOptions' => [
            'allowClear' => true,
            // 'width' => '150px',
        ],
        ])->label(false);?>
    </div>
    <div class="col-2">

       <?= $form->field($model, 'position_type_id')->widget(Select2::classname(), [
            'data' => $model->ListPositionType(),
            'options' => ['placeholder' => 'ประเภทบุคลากรทั้งหมด ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false) ?>

    </div>
        <div class="col-2">

        <?= $form->field($model, 'leave_type_id')->widget(Select2::classname(), [
            'data' => $model->listLeaveType(),
            'options' => ['placeholder' => 'ประเภทการลาทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false); ?>
    </div>
    


    <div class="col-5">
        <?php echo $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
            'name' => 'department',
            'id' => 'treeID',
            'query' => Organization::find()->addOrderBy('root, lft'),
            'value' => 1,
            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
            'fontAwesome' => true,
            'asDropdown' => true,
            'multiple' => false,
            'options' => ['disabled' => false, 'allowClear' => true, 'class' => 'close'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label(false); ?>
    </div>
    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
        <div class="d-flex flex-column flex-md-row gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> <span class="d-none d-sm-inline">ค้นหา</span>', [
                'class' => 'btn btn-primary w-md-auto',
                'id' => 'summit'
            ]) ?>
            <!-- <button class="btn btn-success export-leave"><i class="fa-solid fa-file-excel"></i> Excel</button> -->
        </div>
    </div>

</div>

<div class="collapse mt-3" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
</div>

<?php ActiveForm::end(); ?>