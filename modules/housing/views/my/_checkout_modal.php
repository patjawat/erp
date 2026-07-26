<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$location = implode(' / ', array_filter([$occupancy->unit?->building?->name, $occupancy->unit?->name, $occupancy->room?->name]));
$form = ActiveForm::begin(['id' => 'housing-checkout-request-form', 'options' => ['data-list-url' => Url::to(['/profile', 'name' => 'housing'])]]);
?>
<div class="alert alert-warning">
    <strong>บ้านพักที่จะส่งคืน</strong>
    <div class="mt-1"><?= Html::encode($location) ?></div>
</div>
<?= $form->field($model, 'requested_date')->input('date', ['min' => date('Y-m-d')]) ?>
<?= $form->field($model, 'move_out_reason')->textarea(['rows' => 4, 'placeholder' => 'ระบุเหตุผลและข้อมูลที่ผู้ดูแลควรทราบ']) ?>
<div class="small text-muted mb-3">หลังส่งคำขอ ผู้ดูแลจะตรวจสภาพ อุปกรณ์ เลขมิเตอร์ และจัดทำเอกสารให้ลงนาม</div>
<div class="d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('ส่งคำขอคืนบ้านพัก', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#housing-checkout-request-form');");
?>
