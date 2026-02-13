<?php

use app\modules\hr\models\Organization;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Html;

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

<div class="row">
    <div class="col-2">
        <?= $form->field($model, 'thai_year', ['showLabels' => false])->widget(Select2::classname(), [
            'data' => $model->getYearList(), // สร้างฟังก์ชันดึงปีใน Model
            'options' => ['placeholder' => 'เลือกปีที่ตรวจ...'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
    <div class="col-4">
        <?php echo $form->field($model, 'q_department',[
            'template' => '{input}{error}', // เอา {label} ออก และไม่ใช้ wrapper col-md-10
    'options' => ['class' => 'col-12']
        ])->widget(\kartik\tree\TreeViewInput::className(), [
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
    <div class="col-2">
        <?= $form->field($model, 'health_status', ['showLabels' => false])->widget(Select2::classname(), [
            'data' => $model->getHealthStatusList(),
            'options' => ['placeholder' => 'สถานะทั้งหมด...'],
            'pluginOptions' => ['allowClear' => true],
        ]) ?>
    </div>
        <div class="col-3">
        <?php echo $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    


    <div class="col-1">
        <?= Html::submitButton('<i class="fas fa-search"></i> ค้นหา', ['class' => 'btn btn-primary shadow-sm']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>