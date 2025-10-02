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

<?=$this->render('@app/components/ui/_filter',['form' => $form,'model' => $model,'label' => false])?>

<div class="collapse mt-3" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
    <div class="row">
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


</div>

<?php ActiveForm::end(); ?>

