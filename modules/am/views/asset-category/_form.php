<?php
use yii\helpers\Html;
use yii\helpers\Json;
// use yii\bootstrap5\ActiveForm;
use app\models\Categorise;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\AppHelper;
use unclead\multipleinput\MultipleInput;

/** @var string $group */
$group = $group ?? 'EQUIP';
$isBuilding = $group === 'BLDG';
?>

<?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation'=> true,//เปิดการใช้งาน AjaxValidation
    'validationUrl' =>['/am/asset-category/validator']
    ]); ?>

<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'group_id')->hiddenInput()->label(false) ?>

<div class="row">
<div class="col-5">
          <?=$form->field($model, 'code')->textInput(['placeholder'=>'เว้นว่างได้ (ร่าง)'])->label($isBuilding ? 'รหัสหมวดอาคาร' : 'FSN')->hint($isBuilding
              ? 'ระบุรหัสจริงที่ใช้ขึ้นต้นหมายเลขทะเบียนอาคาร เช่น 0920-004-0001 ห้ามใช้ BLDG'
              : 'รหัส FSN ของหมวดนี้ · เว้นว่างเพื่อบันทึกเป็นร่างก่อนได้ แต่ต้องกำหนดรหัสและเปิดใช้งานจึงจะนำไปเพิ่มทะเบียนครุภัณฑ์ได้')?>
        </div>
        <div class="col-7">
    <?= $form->field($model, 'title')->textInput(['maxlength' => true,'placeholder'=>'ระบุชื่อหมวดหมู่...'])->label($isBuilding ? 'ชื่อหมวดอาคาร' : 'ชื่อหมวดหมู่') ?>
</div>
</div>
<div class="row">
    <div class="col-12">
        <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
                   'data' => $model->listAssetType($group),
                    'options' => ['placeholder' => 'ระบุประเภทรัพย์สิน...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label("ประเภททรัพย์สิน");
                ?>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <?= $form->field($model, 'useful_life')->textInput([
            'type' => 'number',
            'min' => 1,
            'placeholder' => 'เช่น 5',
        ])->label('อายุการใช้งาน (ปี)')->hint('ใช้เป็นค่าตั้งต้นของทรัพย์สินในหมวดนี้') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'depreciation_rate', [
            'template' => '{label}<div class="input-group">{input}<span class="input-group-text">%</span></div>{hint}{error}',
        ])->textInput([
            'type' => 'number',
            'step' => '0.01',
            'min' => 0,
            'placeholder' => 'เช่น 20.00',
        ])->label('อัตราค่าเสื่อม')->hint('ทศนิยม 2 ตำแหน่ง') ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-check form-switch mt-2">
            <?= Html::activeCheckbox($model, 'active', [
                'class' => 'form-check-input',
                'label' => false,
                'uncheck' => '0',
                'value' => '1',
            ]) ?>
            <label class="form-check-label" for="<?= Html::getInputId($model, 'active') ?>">
                เปิดใช้งาน <span class="text-muted small">(ปิด = ร่าง/ตัวอย่าง ยังไม่นำไปใช้กับ<?= $isBuilding ? 'ทะเบียนอาคาร' : 'ครุภัณฑ์' ?>)</span>
            </label>
        </div>
    </div>
</div>

<div class="form-group mt-3 d-flex justify-content-center gap-2">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summitxx"]) ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-circle-xmark"></i> ปิด</button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
(function () {
    // ยิง AJAX ตรง + อัพเดตทันทีที่ได้ response สำเร็จ (ไม่พึ่ง handleFormSubmit ซึ่งมี chain ยืนยัน/loading/toast
    // ที่หน่วงและขึ้นกับ modal-hide animation — แพทเทิร์นเดียวกับ vendor/_form_quick.php)
    var \$form = \$('#form');
    if (!\$form.length) { return; }

    \$form.off('beforeSubmit.assetCategoryForm').on('beforeSubmit.assetCategoryForm', function (e) {
        var form = \$(this);
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json'
        }).done(function (response) {
            if (response && response.status === 'success') {
                if (typeof erpHideModal === 'function') {
                    erpHideModal('#main-modal');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'บันทึกสำเร็จ', showConfirmButton: false, timer: 1500 });
                }
                \$(document).trigger('assetCategory:saved', [response]);
            } else if (response && response.errors && typeof form.yiiActiveForm === 'function') {
                form.yiiActiveForm('updateMessages', response.errors, true);
            } else if (response && typeof form.yiiActiveForm === 'function') {
                form.yiiActiveForm('updateMessages', response, true);
            }
        }).fail(function (xhr) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'บันทึกไม่สำเร็จ (HTTP ' + (xhr ? xhr.status : '?') + ')', showConfirmButton: false, timer: 2400 });
            }
        });
        return false;
    });
})();
JS;
$this->registerJs($js);
?>
