<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\HealthScreenSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'options' => ['data-pjax' => 1], // สำหรับกรณีใช้ Pjax
]); ?>

<div class="row g-2">
    <div class="col-md-4">
        <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => false]) ?>

    </div>

    <div class="col-md-3">
        <?= $form->field($model, 'thai_year', ['showLabels' => false])->widget(Select2::classname(), [
            'data' => $model->getYearList(), // สร้างฟังก์ชันดึงปีใน Model
            'options' => ['placeholder' => 'เลือกปีที่ตรวจ...'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>

    <div class="col-md-3">
        <?= $form->field($model, 'health_status', ['showLabels' => false])->widget(Select2::classname(), [
            'data' => $model->getHealthStatusList(),
            'options' => ['placeholder' => 'สถานะทั้งหมด...'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>

    <div class="col-md-2">
        <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary shadow-sm']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>