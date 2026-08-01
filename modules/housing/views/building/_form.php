<?php

use app\modules\housing\models\Building;
use app\modules\filemanager\components\FileManagerHelper;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$buildingImage = $buildingImage ?? null;
$employeeItems = $employeeItems ?? [];
$inactiveResponsible = $inactiveResponsible ?? null;
$formId = 'housing-building-form';
$form = ActiveForm::begin([
    'id' => $formId,
    'options' => [
        'data-list-url' => Url::to(['index']),
        'enctype' => 'multipart/form-data',
    ],
]);
?>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'building_type')->dropDownList(Building::typeOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(Building::statusOptions()) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'sort_order')->input('number') ?></div>
    <div class="col-md-8"><?= $form->field($model, 'address')->textarea(['rows' => 2]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'electric_account_no')->textInput(['maxlength' => true]) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3]) ?></div>
    <div class="col-12">
        <?php if ($inactiveResponsible !== null): ?>
            <div class="alert alert-warning d-flex gap-2 align-items-start mb-3" role="alert">
                <i class="bi bi-exclamation-triangle flex-shrink-0 mt-1" style="font-size:18px"></i>
                <div>
                    <div class="fw-semibold">ต้องกำหนดผู้รับผิดชอบใหม่</div>
                    <div class="small">
                        <?= Html::encode($inactiveResponsible->fullname()) ?>
                        มีสถานะ <?= Html::encode($inactiveResponsible->statusName->title ?? 'ไม่ได้ปฏิบัติงาน') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?= $form->field($model, 'responsible_employee_id')->widget(Select2::class, [
            'data' => $employeeItems,
            'options' => [
                'placeholder' => 'ค้นหาชื่อผู้รับผิดชอบ',
            ],
            'pluginOptions' => array_filter([
                'allowClear' => true,
                'dropdownParent' => Yii::$app->request->isAjax ? '#main-modal' : null,
            ]),
        ])->hint('แสดงเฉพาะบุคลากรที่ยังปฏิบัติงาน และมีสิทธิ์ housing.staff หรือ housing.admin') ?>
    </div>
    <div class="col-12">
        <div class="border rounded-3 p-3">
            <div class="row g-3 align-items-center">
                <?php if ($buildingImage !== null): ?>
                    <div class="col-sm-auto">
                        <?= Html::img(FileManagerHelper::getImg($buildingImage->id), [
                            'class' => 'rounded-3 border object-fit-cover',
                            'style' => 'width:120px;height:90px',
                            'alt' => 'รูปภาพบ้านพักปัจจุบัน',
                        ]) ?>
                    </div>
                <?php endif; ?>
                <div class="col">
                    <?= $form->field($model, 'building_image')->fileInput([
                        'accept' => 'image/jpeg,image/png,image/webp',
                        'class' => 'form-control',
                    ])->hint('รองรับ JPG, PNG หรือ WebP ขนาดไม่เกิน 10 MB และความละเอียดไม่เกิน 50 ล้านพิกเซล รูปใหม่จะแทนที่รูปเดิม') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.container&&typeof erpReloadPjax==='function'&&erpReloadPjax(r.container)){return;}window.location.href=document.querySelector('#{$formId}').dataset.listUrl;});");
?>
