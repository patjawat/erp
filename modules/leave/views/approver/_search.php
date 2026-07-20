<?php

use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Organization;

$hasAdvancedFilters =  !empty($model->position_type_id) || !empty($model->leave_type_id);
/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $listEmployeeType */
?>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 0
    ],
    'fieldConfig' => ['options' => ['class' => 'mb-3']] // spacing form field groups
]); ?>

<div class="row g-3 align-items-end">
    <div class="col-12 col-md-6 col-xl-2">
        <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
                // 'width' => '150px',
            ],
        ])->label(false); ?>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-12 col-xl-auto ms-xl-auto">
        <div class="d-flex flex-wrap justify-content-start justify-content-xl-end gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา', ['class' => 'btn btn-primary flex-grow-1 flex-sm-grow-0']) ?>
            <button
                class="btn btn-outline-primary flex-grow-1 flex-sm-grow-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseFilter"
                aria-expanded="<?= $hasAdvancedFilters ? 'true' : 'false' ?>"
                aria-controls="collapseFilter"
                id="btnToggleFilter"
            >
                <i class="fa-solid fa-sliders me-1"></i> ตัวกรอง
            </button>
        </div>
    </div>
</div>

<div class="collapse mt-3 pt-3 border-top <?= $hasAdvancedFilters ? 'show' : '' ?>" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
    <div class="row g-3">

        <div class="col-12 col-md-6 col-xl-2">

            <?= $form->field($model, 'position_type_id')->widget(Select2::classname(), [
                'data' => $listEmployeeType,
                'options' => ['placeholder' => 'ประเภทบุคลากรทั้งหมด ...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label(false) ?>

        </div>
        <div class="col-12 col-md-6 col-xl-2">

            <?= $form->field($model, 'leave_type_id')->widget(Select2::classname(), [
                'data' => $model->listLeaveType(),
                'options' => ['placeholder' => 'ประเภทการลาทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label(false); ?>
        </div>



        <div class="col-12 col-xl-5 position-relative" style="z-index: 1055;">
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
</div>

<?php ActiveForm::end(); ?>
