<?php

use yii\helpers\Url;
use yii\helpers\Html;
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
    echo $form->field($model, 'category_id')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(PlanType::find()->where(['name' => 'plan_type'])->all(), 'code', 'title'),
        'options' => [
            'id' => 'plan_category_id',
            'placeholder' => 'เลือกประเภท',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ])->label('ประเภท');
    ?>
     <?= $form->field($model, 'code')->textInput()->label('รหัส') ?>
    <?= $form->field($model, 'title')->textInput()->label('ชื่อของหมวดหมู่') ?>


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

    $('#plan_category_id').on('change', function() {
    var categoryId = $(this).val();
    if (categoryId) {
        $.ajax({
            url: '$generateUrl',
            data: { category_id: categoryId },
            success: function(res) {
                if(res.success) {
                    $('#plancategory-code').val(res.code);
                }
            }
        });
    }
});

JS;
$this->registerJs($js);
?>
