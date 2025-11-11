<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\components\CategoriseHelper;
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
    'id' => 'search-leave',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="row">

    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>

    <div class="col-5">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'theme' => Select2::THEME_KRAJEE_BS5,
            'options' => [
                'placeholder' => 'สถานะทั้งหมด',
                'multiple' => true,
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '100%',
            ],
            'pluginEvents' => [
                "change" => "function() { 
                   $.ajax({
                     url: '/approve/default/update-filter-status',
                     type: 'POST',
                     data: {
                     status: $(this).val(),
                     name:'hr_development_filter_status'
                     },
                     success: function(data) {
                      
                     }
                   });
                 }",
            ]
        ])->label(false); ?>
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

<div class="row mt-2">
    <div class="col-4">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ค้นหา...'])->label(false)->label(false) ?>
    </div>
    <div class="col-2">
        <?php
        echo $form->field($model, 'development_type_id')->widget(Select2::classname(), [
            'data' => CategoriseHelper::DevelopmentType(),
            'options' => ['placeholder' => 'เลือกประเภทการพัฒนา'],
            'pluginOptions' => [
                // 'dropdownParent' => '#main-modal',
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-6">
        <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => false, 'placeholder' => 'ผู้ขอ']) ?>
    </div>
</div>

<div class="collapse mt-3" id="collapseFilter">
    <!-- การกรองแบบละเอียด -->
    <div class="row">
        <div class="col-3">

            <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
                'data' => $model->ListThaiYear(),
                'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
                'pluginOptions' => [
                    'allowClear' => true,
                    // 'width' => '120px',
                ],
            ])->label(false); ?>

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

        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>