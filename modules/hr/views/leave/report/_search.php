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
       'action' => ['report'],
    'method' => 'get',
    'id' => 'search-leave',
    'options' => [
        'data-pjax' => 1
    ],
     'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>

        <?php echo $form->field($model, 'export')->hiddenInput()->label(false) ?>


       <?=$this->render('@app/components/ui/_filter',[
    'form' => $form,
    'model' => $model,
    'label' => false,
    'status' => $model->listStatus()
    ])
    ?>

     <div class="row">
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
        <div class="col-6">
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
    </div>


<div class="collapse mt-3" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
    


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