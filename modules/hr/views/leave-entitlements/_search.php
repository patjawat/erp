<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\widgets\ActiveFormpression;
use app\modules\hr\models\Employees;
use yii\helpers\ArrayHelperayHelper;
use app\modules\hr\models\Organization;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeaveEntitlementsSearch $model */
/** @var yii\widgets\ActiveForm $form */

?>
<style>
    .field-leaveentitlementssearch-q_department {
    width: 400px !important; /* เพิ่ม !important เพื่อความแน่นอน */
}
</style>
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
          'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
    ]); ?>

    <div class="d-flex gap-2">
        <?php // echo $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label('คำค้นหา') ?>
        <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '120px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => 'function() {
                            $(this).submit()
                        }',
            ]
        ])->label(false);
        ?>
        <?php
        $url = Url::to(['/depdrop/employee-by-id']);
        try {
            $initEmployee = Employees::find()->where(['id' => $model->emp_id])->one()->fullname;
        } catch (\Throwable $th) {
            $initEmployee = '';
        }
        echo $form->field($model, 'emp_id')->widget(Select2::classname(), [
            'initValueText' => $initEmployee,
            'options' => ['placeholder' => 'เลือกบุคลากร ...'],
            'pluginOptions' => [
                'width' => '230px',
                'allowClear' => true,
                'minimumInputLength' => 1,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'templateResult' => new JsExpression('function(result) {
                    if (!result.id) {
                        return result.fullname; // ใช้ค่าที่ default หากไม่มี ID
                    }
                    return result.fullname; // ใช้ข้อมูลจาก result.fullname
                }'),
                'templateSelection' => new JsExpression('function(result) {
                    return result.text || result.fullname; // แสดง fullname หรือ text ตอนเลือก
                }'),
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                $(this).submit()
                }',
                'select2:unselecting' => 'function() {
                    $(this).submit()
                }',
            ]
        ])->label(false);
        ?>
<?=$form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
    'name' => 'department',
    'id' => 'treeID',
    'query' => Organization::find()->addOrderBy('root, lft'),
    'value' => 1,
    'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
    'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
    'fontAwesome' => true,
    'asDropdown' => true,
    'multiple' => false,
    'options' => [
        'placeholder' => 'หน่วยงานทั้งหมด...',
        'disabled' => false,
        'allowClear' => true,
    ],
    'pluginOptions' => [
        'allowClear' => true
    ],
     'dropdownConfig' => [
        'input' => [
            'placeholder' => 'หน่วยงานทั้งหมด...', // อีกจุดที่สามารถกำหนด placeholder ได้เช่นกัน
        ],
    ],
])->label(false);?>

            <?php echo Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    </div>
        <?php ActiveForm::end(); ?>
