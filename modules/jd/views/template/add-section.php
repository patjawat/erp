<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var app\modules\jd\models\JdTemplate $template */
/** @var app\modules\jd\models\JdTemplateSection $section */
$this->title = 'เพิ่มหัวข้อ: ' . $template->name;
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $template->name, 'url' => ['view', 'id' => $template->id]];
$this->params['breadcrumbs'][] = 'เพิ่มหัวข้อ';
?>
<?php $form = ActiveForm::begin(['id' => 'jd-section-form']); ?>
<?= $form->field($section, 'title')->textInput(['maxlength' => true, 'class' => 'form-control'])->label('หัวข้อ (เช่น หน้าที่ความรับผิดชอบ, คุณสมบัติผู้ดำรงตำแหน่ง)') ?>
<?= $form->field($section, 'content')->textarea(['rows' => 6, 'class' => 'form-control'])->label('เนื้อหา') ?>
<?= $form->field($section, 'sort_order')->textInput(['type' => 'number', 'class' => 'form-control'])->label('ลำดับ') ?>
<div class="mt-3">
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ยกเลิก', ['view', 'id' => $template->id], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs(<<<'JS'
handleFormSubmit('#jd-section-form', null, async function(response) {
    if (response.url) {
        window.location.href = response.url;
    } else {
        location.reload();
    }
});
JS
); ?>
