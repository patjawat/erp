<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\widgets\TomSelectWidget;

/** @var app\modules\jd\models\JdTemplate $model */
$this->title = 'สร้าง Template JD';
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$positionItems = ['' => '-- เลือกตำแหน่งงาน --'] + \app\components\CategoriseHelper::PositionName();
?>
<?php $form = ActiveForm::begin(['id' => 'jd-template-form']); ?>
<div class="row g-3">
    <div class="col-md-6">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control'])->label('ชื่อ template') ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'position_code')->widget(TomSelectWidget::class, [
            'items' => $positionItems,
            'options' => ['class' => 'form-select'],
            'clientOptions' => [
                'placeholder' => '-- เลือกตำแหน่งงาน --',
                'allowEmptyOption' => true,
            ],
        ])->label('ตำแหน่งงาน') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'is_active')->dropDownList([1 => 'ใช้งาน', 0 => 'ปิดใช้'], ['class' => 'form-control form-select'])->label('สถานะ') ?>
    </div>
</div>
<div class="mt-3">
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs(<<<'JS'
handleFormSubmit('#jd-template-form', null, async function(response) {
    if (response.container) {
        $.pjax.reload({ container: response.container, history: false });
    } else {
        location.reload();
    }
});
JS
); ?>
