<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
$form = ActiveForm::begin(['id' => 'exit-question-form', 'options' => ['data-list-url' => Url::to(['templates'])]]);
?>
<?= $form->field($model, 'prompt')->textarea(['rows' => 3]) ?>
<?= $form->field($model, 'code')->textInput(['maxlength' => true])->hint('รหัสต้องไม่ซ้ำภายในส่วน และไม่ควรเปลี่ยนเมื่อเริ่มใช้งานแล้ว') ?>
<div class="row"><div class="col-md-6"><?= $form->field($model, 'question_type')->dropDownList(['short_text' => 'ข้อความสั้น', 'long_text' => 'ข้อความหลายบรรทัด', 'single_choice' => 'เลือกข้อเดียว', 'multi_choice' => 'เลือกหลายข้อ', 'ranking' => 'จัดอันดับ', 'rating' => 'คะแนน 1–5', 'date' => 'วันที่', 'number' => 'ตัวเลข'], ['class' => 'form-select']) ?></div><div class="col-md-3"><?= $form->field($model, 'sequence')->input('number', ['min' => 1]) ?></div><div class="col-md-3 pt-md-4"><?= $form->field($model, 'is_required')->checkbox() ?></div></div>
<?= $form->field($model, 'analytics_key')->textInput(['maxlength' => true])->hint('เว้นว่างหากไม่ต้องนำคำถามนี้ไปคำนวณ Dashboard') ?>
<?= $form->field($model, 'condition_json')->textInput()->hint('ตัวอย่าง {"exit_type":["retirement"]}') ?>
<?= $form->field($model, 'options_text')->textarea(['rows' => 6])->hint('สำหรับคำถามแบบเลือกหรือจัดอันดับ: หนึ่งตัวเลือกต่อบรรทัด รูปแบบ value|ข้อความที่แสดง') ?>
<div class="d-grid d-sm-flex justify-content-sm-end gap-2"><?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('บันทึกคำถาม', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end(); $this->registerJs(<<<JS
handleFormSubmit('#exit-question-form', null, function () { window.location.href = document.querySelector('#exit-question-form').dataset.listUrl; });
JS); ?>
