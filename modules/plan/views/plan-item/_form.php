<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\plan\models\PlanType;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanType $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="plan-type-form">
    <?php $form = ActiveForm::begin([
        'id' => 'form'
    ]); ?>

    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'code')->hiddenInput(['maxlength' => true])->label(false) ?>
   
    <?php
    echo $form->field($model, 'plan_type_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(PlanType::find()->where(['name' => 'plan_type'])->all(), 'code', 'title'),
        'options' => [
            'id' => 'plan_type_id',
            'placeholder' => 'เลือกประเภท',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ])->label('ประเภท');
    ?>

    <?php
    echo $form->field($model, 'category_id')->widget(DepDrop::classname(), [
        'options' => [
             'id' => 'category_id',
            'placeholder' => 'เลือกประเภท',
        ],
        'type' => DepDrop::TYPE_SELECT2,
        'select2Options' => ['pluginOptions' => ['allowClear' => true]],
        'pluginOptions' => [
            'depends' => ['plan_type_id'],
            'url' => Url::to(['get-plan-category']),
            'loadingText' => 'กำลังโหลด ...',
            'params' => ['depdrop_all_params' => 'plan_type_id'],
            'initDepends' => ['plan_type_id'],
            'initialize' => true,
        ],
        'pluginEvents' => [
            "select2:select" => "function() { 

                        }",
        ],

    ])->label('หมวด'); ?>
    
    <?= $form->field($model, 'title')->textInput()->label('ชื่อของหมวดหมู่') ?>

    <?php
    // ใช้เฉพาะรายการในหมวดรายจ่ายบุคลากร — กำหนดว่าเวลาจัดทำแผนบุคลากรจะดึงพนักงานประเภทใดเข้ามา
    $employeeTypes = (new \yii\db\Query())
        ->select(['title', 'id'])
        ->from('employee_type')
        ->where(['active' => 1])
        ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
        ->indexBy('id')
        ->column();
    ?>
    <div class="border rounded-3 p-3 mb-3 bg-body-tertiary">
        <div class="fw-semibold small mb-2">การดึงรายชื่อบุคลากร (ใช้กับแผนบุคลากรเท่านั้น)</div>
        <?= $form->field($model, 'employee_type_ids')->widget(Select2::classname(), [
            'data' => $employeeTypes,
            'options' => [
                'id' => 'plan-item-employee-types',
                'multiple' => true,
                'placeholder' => 'เลือกได้มากกว่า 1 ประเภท (ไม่เลือก = ผู้ใช้เลือกเองตอนทำแผน)',
            ],
            'pluginOptions' => ['allowClear' => true],
        ])->label('ประเภทบุคลากรที่ผูกกับรายการนี้') ?>

        <?= $form->field($model, 'all_employee_types')->checkbox([
            'id' => 'plan-item-all-employee-types',
            'value' => 1,
            'uncheck' => 0,
        ])->label('ใช้กับบุคลากรทุกประเภทในหน่วยงาน (เช่น ค่าตอบแทน ฉ.11)') ?>
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
            <?= Html::button('ปิด', [
                'class' => 'btn btn-outline-secondary',
                'data-bs-dismiss' => 'modal'
            ]) ?>
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
$generateUrl = Url::to(['generate-code']); // <-- ชี้ไป actionGenerateCode
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });

    // เลือก "ทุกประเภท" แล้วไม่ต้องเลือกรายประเภทอีก
    function toggleEmployeeTypes() {
        var all = $('#plan-item-all-employee-types').is(':checked');
        $('#plan-item-employee-types').prop('disabled', all).trigger('change.select2');
        if (all) { $('#plan-item-employee-types').val(null).trigger('change'); }
    }
    $('#plan-item-all-employee-types').on('change', toggleEmployeeTypes);
    toggleEmployeeTypes();

//     $('#category_id').on('change', function() {
//     var categoryId = $(this).val();
//     if (categoryId) {
//         $.ajax({
//             url: '$generateUrl',
//             data: { category_id: categoryId },
//             success: function(res) {
//                 if(res.success) {
//                     $('#planitem-code').val(res.code);
//                 }
//             }
//         });
//     }
// });

JS;
$this->registerJs($js);
?>
