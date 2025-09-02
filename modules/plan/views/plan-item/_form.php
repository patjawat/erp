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
     <?= $form->field($model, 'code')->textInput()->label('รหัส') ?>
    <?= $form->field($model, 'title')->textInput()->label('ชื่อของหมวดหมู่') ?>


    <div class="d-flex justify-content-center align-items-center mt-3">
        <div class="d-flex gap-2">
            <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
            <?= Html::button('ปิด', [
                'class' => 'btn btn-secondary',
                'data-bs-dismiss' => 'modal'
            ]) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
$generateUrl = Url::to(['generate-code']); // <-- ชี้ไป actionGenerateCode
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });

        $('#category_id').on('change', function() {
    var categoryId = $(this).val();
    if (categoryId) {
        $.ajax({
            url: '$generateUrl',
            data: { category_id: categoryId },
            success: function(res) {
                if(res.success) {
                    $('#planitem-code').val(res.code);
                }
            }
        });
    }
});

JS;
$this->registerJs($js);
?>