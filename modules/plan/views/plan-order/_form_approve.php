<?php

use yii\helpers\Html;
use kartik\widgets\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Categorise;

/** @var $model app\modules\plan\models\Plan */
/** @var $items app\modules\plan\models\PlanItem[] */

$form = ActiveForm::begin(['id' => 'form']);
?>

<?php

echo $form->field($model, 'plan_budget_type_id')->widget(Select2::classname(), [
    'data' => ArrayHelper::map(categorise::find()->where(['name' => 'budget_type'])->all(), 'code', 'title'),
    'options' => [
        'placeholder' => 'เลือกประเภทเงิน',
        'id' => 'plan_budget_type_id'
    ],
    'pluginOptions' => [
        'allowClear' => true,
    ],
])->label('หมวดพัสดุ');
?>


<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
    handleFormSubmit('#form', null, async function(response) {
        if (response.url) {
             window.location.href = response.url;
        } else {
             location.reload();
        }
    });
JS;
$this->registerJs($js);
?>
