<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use kartik\tree\TreeViewInput;
use app\components\DateFilterHelper;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    
<div class="d-flex justify-content-between align-top align-items-center gap-2">
    <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false])?>

        <?= $form->field($model, 'date_filter')->widget(Select2::classname(), [
            'data' => DateFilterHelper::getDropdownItems(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
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

        <?= $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ทั้งหมดทุกปี'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
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

        <?= $form->field($model, 'date_start')->textInput([
            'class' => 'form-control',
            'placeholder' => '__/__/____'
        ])->label(false) ?>

        <?= $form->field($model, 'date_end')->textInput([
            'class' => 'form-control',
            'placeholder' => '__/__/____'
        ])->label(false) ?>

        <?php echo $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '170px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 

                }',
                'select2:unselecting' => 'function() {

                }',
            ]
        ])->label(false) ?>

        <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary']) ?>
    </div>
    
    
    <!-- Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">เลือกเงื่อนไขของการค้นหาเพิ่มเติม</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
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
                ])->label('หน่วยงานภายในตามโครงสร้าง'); ?>


            <div class="d-flex flex-row gap-4">
                 <?php // echo $form->field($model, 'status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']); ?>
                <?php // echo $form->field($model, 'status')->checkboxList($model->listStatus(), ['custom' => true, 'inline' => false, 'id' => 'custom-checkbox-list-inline']); ?>
            </div>

            <div class="offcanvas-footer">
                <?php echo Html::submitButton(
                        '<i class="fa-solid fa-magnifying-glass"></i> ค้นหา',
                        [
                            'class' => 'btn btn-light',
                            'data-bs-backdrop' => 'static',
                            'tabindex' => '-1',
                            'id' => 'offcanvasExample',
                            'aria-labelledby' => 'offcanvasExampleLabel',
                        ]
                    ); ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>


    <?php

$js = <<< JS

    thaiDatepicker('#developmentsearch-date_start,#developmentsearch-date_end')
    $("#developmentsearch-date_start").on('change', function() {
            $('#developmentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });
    $("#developmentsearch-date_end").on('change', function() {
            $('#developmentsearch-thai_year').val(null).trigger('change');
            // $(this).submit();
    });


    JS;
$this->registerJS($js, View::POS_END);

?>