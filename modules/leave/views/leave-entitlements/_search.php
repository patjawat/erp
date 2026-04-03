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

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-0 mr-2 me-2']] // spacing form field groups
]); ?>

<div class="row">
    <div class="col-3">
        <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'ปีงบประมาณทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
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
    </div>
    <div class="col-4">
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
    </div>
    <div class="col-4">


        <?= $form->field($model, 'q_department')->widget(\kartik\tree\TreeViewInput::className(), [
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

<div class="collapse mt-3" id="collapseFilter">
</div>
<?php ActiveForm::end(); ?>