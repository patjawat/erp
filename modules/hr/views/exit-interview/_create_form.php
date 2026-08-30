<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\modules\hr\models\ExitInterview;
$form = ActiveForm::begin(['id' => 'exit-create-form', 'options' => ['data-list-url' => Url::to(['registry'])]]);
?>
<?= $form->field($model, 'emp_id')->dropDownList($employeeItems, ['prompt' => 'เลือกบุคลากร', 'class' => 'form-select']) ?>
<div class="row"><div class="col-md-6"><?= $form->field($model, 'exit_type')->dropDownList(ExitInterview::exitTypeOptions(), ['class' => 'form-select']) ?></div><div class="col-md-6"><?= $form->field($model, 'response_source')->dropDownList(['hr_interview' => 'HR สัมภาษณ์และกรอก', 'self_service' => 'ส่งลิงก์ให้ตอบ', 'excel_import' => 'นำเข้าจาก Excel'], ['class' => 'form-select']) ?></div><div class="col-md-6"><?= $form->field($model, 'exit_date')->input('date') ?></div><div class="col-md-6"><?= $form->field($model, 'interview_date')->input('date') ?></div></div>
<div class="d-grid d-sm-flex justify-content-sm-end gap-2"><?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('สร้างรายการ', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end();
$this->registerJs(<<<JS
handleFormSubmit('#exit-create-form', null, function (r) {
  if (r && r.container && typeof erpReloadPjax === 'function' && erpReloadPjax(r.container)) return;
  window.location.href = document.querySelector('#exit-create-form').dataset.listUrl;
});
JS);
?>
