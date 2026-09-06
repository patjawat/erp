<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;


/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);
// กรองครุภัณฑ์ตามกลุ่มงานซ่อมของใบงาน (แพทย์=MED/SCI, คอม=COM, ทั่วไป=ทั้งหมด)
$assetTypes = \app\modules\helpdesk2\models\Helpdesk::assetTypesForGroup($model->repair_group);
$assetPicker = $model->listAssetForPicker($assetTypes);
$assetItems = $assetPicker['items'];
// คงค่าครุภัณฑ์ที่ผูกอยู่เดิมให้แสดงได้ แม้อยู่นอกตัวกรองชนิด
if (!empty($model->asset_number) && !isset($assetItems[$model->asset_number])) {
    $curAsset = \app\modules\am\models\Asset::findOne(['code' => $model->asset_number]);
    $curName = '';
    if ($curAsset) {
        $dj = is_array($curAsset->data_json) ? $curAsset->data_json : (json_decode((string) $curAsset->data_json, true) ?: []);
        $curName = (string) ($dj['asset_name'] ?? '');
    }
    $assetItems[$model->asset_number] = trim(($curName !== '' ? $curName . ' ' : '') . $model->asset_number);
}

?>

<?php $form = ActiveForm::begin([
        'id' => 'form-status',
    ]); ?>
<div class="row">
    <div class="col-6 col-md-6">
        <?=$form->field($model, 'device_type_id')->widget(Select2::classname(), [
                    'data' => $model->listDeviceType(),
                    'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ประเภทอุปกรณ์');
                ?>
    </div>

    <div class="col-6 col-md-6">
        <?= $form->field($model, 'asset_number')->widget(Select2::classname(), [
                    'data' => $assetItems,
                    'options' => ['placeholder' => 'ระบุ/แก้ไขครุภัณฑ์ (ค้นหาชื่อหรือรหัส)...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('รหัสครุภัณฑ์'); ?>
        <div class="form-text small text-muted mt-1">
            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
            ผูกครุภัณฑ์ให้ครบเพื่อให้แดชบอร์ด "ชนิดเครื่องมือ" และประวัติสอบเทียบ/บำรุงรักษาสมบูรณ์
        </div>
    </div>

    <div class="col-6">
        <?= $form->field($model, 'repair_type')->dropDownList($model::getRepairTypeList(), ['prompt' => 'เลือกประเภทการซ่อม'])->label('ประเภทการซ่อม');?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'repair_result')->widget(Select2::classname(), [
          'data' => $model->getRepairResultList(),
            'options' => ['placeholder' => 'เลือกผลการซ่อม ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])->label('ผลการซ่อม'); ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'status')->widget(Select2::classname(), [
    'data' => $model->listStatus(),
    'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
    'pluginOptions' => [
      'allowClear' => true,
      'dropdownParent' => '#main-modal',
    ],
    ])->label('สถานะงานซ่อม'); ?>

    </div>

</div>

<div class="col-12 d-flex justify-content-end mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-circle-check me-1"></i>
        ตกลง
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
   
   $(document).on('beforeSubmit', '#form-status', function (e) {
    e.preventDefault();
    const form = $(this);
    Swal.fire({
      title: "ยืนยัน?",
      text: "บันทึกข้อมูล!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      cancelButtonText: "ยกเลิก!",
      confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'กำลังบันทึก...',
          text: 'กรุณารอสักครู่',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        $.ajax({
          url:  form.attr('action'),
          type: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function (response) {
            Swal.close();
            if (response.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                timer: 1000,
                showConfirmButton: false
              }).then(() => {
                $('#main-modal').modal('hide');
                loadFormServiceRecord()
                loadTimeline()
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: response.message || 'ไม่สามารถบันทึกข้อมูลได้'
              });
            }
          },
          error: function () {
            Swal.close();
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด',
              text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
            });
          }
        });
      }
    });
    return false;
  });

JS;
$this->registerJs($js);
?>