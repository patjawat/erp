<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$sendTestUrl = Url::to(['send-test']);
$placeholder = $placeholder ?? 'พิมพ์ค้นหาชื่อหรือนามสกุล...';
?>
<?php $form = ActiveForm::begin([
    'id' => 'notify-send-test-form',
    'action' => $sendTestUrl,
    'method' => 'get',
    'options' => ['class' => 'needs-validation'],
]); ?>
    <div class="mb-3">
        <label for="notify-type" class="form-label">ประเภทแจ้งเตือน</label>
        <?= Html::dropDownList('type', '', array_merge(['' => '-- เลือกประเภท --'], $typeLabels), [
            'id' => 'notify-type',
            'class' => 'form-select',
            'required' => true,
        ]) ?>
    </div>
    <div class="mb-3">
        <label class="form-label">ผู้รับแจ้งเตือน</label>
        <?= $this->render('@app/components/ui/input_emp', [
            'form' => $form,
            'model' => $model,
            'label' => false,
            'placeholder' => $placeholder,
            'fieldName' => 'recipient_emp_id',
            'modal' => true,
        ]) ?>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">ยกเลิก</button>
        <?= Html::submitButton('ส่งแจ้งเตือนทดสอบ', ['class' => 'btn btn-primary rounded-3', 'id' => 'notify-send-test-btn']) ?>
    </div>
<?php ActiveForm::end() ?>
