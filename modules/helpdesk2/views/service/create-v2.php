<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use kartik\file\FileInput;
use app\modules\hr\models\Employees;
use app\widgets\TomSelectWidget;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */

$this->title = 'แจ้งซ่อม (ฟอร์มใหม่)';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $this->title;

$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);
?>

<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="erp-icon-box bg-primary bg-opacity-10">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-semibold">แบบฟอร์มแจ้งซ่อม (เวอร์ชันใหม่)</h4>
                            <div class="text-muted small">สร้างใบแจ้งซ่อมโดยไม่กระทบระบบเดิม</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <?= Html::a('<i class="bi bi-x-lg me-1"></i>ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <?php $form = ActiveForm::begin([
            'id' => 'form-create-v2',
            'enableAjaxValidation' => true,
            'validationUrl' => ['/helpdesk/service/create-v2-validator'],
            'options' => ['enctype' => 'multipart/form-data'],
        ]); ?>

        <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'name')->hiddenInput(['value' => 'repair'])->label(false) ?>

        <div class="row g-3">
            <div class="col-12 col-xl-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">ข้อมูลผู้แจ้งซ่อม</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Requester name</label>
                                    <div class="form-control bg-body-tertiary"><?= Html::encode($emp->fullname ?? '-') ?></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <div class="form-control bg-body-tertiary"><?= Html::encode($emp?->departmentName() ?? '-') ?></div>
                                </div>
                                <div class="mb-3">
                                    <?= $form->field($model, 'data_json[phone]')
                                        ->textInput(['placeholder' => 'เบอร์โทรศัพท์ติดต่อ'])
                                        ->label('Phone') ?>
                                </div>
                                <div class="mb-0">
                                    <?= $form->field($model, 'data_json[email]')
                                        ->textInput(['placeholder' => 'อีเมล (ถ้ามี)'])
                                        ->label('Email') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-send"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">Submit</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button type="submit" name="save_mode" value="draft" class="btn btn-outline-secondary">
                                        <i class="bi bi-save2 me-1"></i> Save Draft
                                    </button>
                                    <button type="submit" name="save_mode" value="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-1"></i> Submit Request
                                    </button>
                                    <?= Html::a('<i class="bi bi-x-lg me-1"></i>Cancel', ['index'], ['class' => 'btn btn-outline-danger']) ?>
                                </div>
                                <div class="small text-muted mt-3">
                                    กด Submit เพื่อส่งงานเข้าระบบ ช่างจะเห็นในทะเบียนงานซ่อมตามสถานะ
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-xl-8">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">ข้อมูลแจ้งซ่อม</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <?= $form->field($model, 'data_json[send_type]')->radioList([
                                            'general' => 'งานซ่อมทั่วไป (ไม่ระบุรหัสครุภัณฑ์)',
                                            'asset' => 'งานซ่อมครุภัณฑ์ (ระบุรหัสครุภัณฑ์)',
                                        ], [
                                            'itemOptions' => ['class' => 'form-check-input'],
                                            'options' => ['class' => 'd-flex flex-column gap-2'],
                                        ])->label('ประเภทงานซ่อม') ?>
                                        <div class="text-muted small">เลือกประเภทงานซ่อมเพื่อแสดง/ซ่อนช่องรหัสครุภัณฑ์</div>
                                    </div>
                                    <div class="col-12">
                                        <?= $form->field($model, 'title')
                                            ->textInput(['placeholder' => 'เช่น เครื่องพิมพ์พิมพ์ไม่ออก / ไฟดับบางจุด'])
                                            ->label('รายละเอียดปัญหา') ?>
                                    </div>
                                    <div class="col-12">
                                        <?= $form->field($model, 'data_json[repair_note]')
                                            ->textArea(['rows' => 4, 'placeholder' => 'อธิบายอาการ/บริบทเพิ่มเติม เช่น เกิดเมื่อไหร่, ทำอะไรแล้ว, ข้อความ error, ผลกระทบ'])
                                            ->label('รายละเอียดเพิ่มเติม') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'device_type_id')->widget(Select2::classname(), [
                                            'data' => $model->listDeviceType(),
                                            'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('ประเภทอุปกรณ์') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'data_json[urgency]')->widget(Select2::classname(), [
                                            'data' => $model->listUrgency(),
                                            'options' => ['placeholder' => 'เลือกความเร่งด่วน ...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('ความเร่งด่วน') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-pc-display"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">ข้อมูลสถานที่/หน่วยงาน</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6" id="asset-number-wrap">
                                        <?= $form->field($model, 'asset_number')->widget(TomSelectWidget::class, [
                                            'items' => ['' => ''],
                                            'options' => ['class' => 'form-select'],
                                            'clientOptions' => [
                                                'valueField' => 'code',
                                                'labelField' => 'label',
                                                'searchField' => ['code', 'label', 'asset_name', 'location'],
                                                'placeholder' => 'ค้นหารหัส/ชื่อครุภัณฑ์/สถานที่...',
                                                'maxOptions' => 30,
                                                'create' => false,
                                                'render' => new JsExpression("{
                                                    option: function(item, escape) {
                                                        var img = item.image_url ? '<img src=\"' + escape(item.image_url) + '\" class=\"rounded-3 border border-secondary-subtle\" style=\"width:40px;height:40px;object-fit:cover;margin-right:10px;\" />' : '';
                                                        var code = item.code ? '<div class=\"fw-semibold\">' + escape(item.code) + '</div>' : '';
                                                        var name = item.asset_name ? '<div class=\"text-muted small\">' + escape(item.asset_name) + '</div>' : '';
                                                        var loc = item.location ? '<div class=\"text-muted small\">' + escape(item.location) + '</div>' : '';
                                                        return '<div class=\"d-flex align-items-center\">' + img + '<div class=\"flex-grow-1\">' + code + name + loc + '</div></div>';
                                                    },
                                                    item: function(item, escape) {
                                                        var img = item.image_url ? '<img src=\"' + escape(item.image_url) + '\" class=\"rounded-3 border border-secondary-subtle\" style=\"width:24px;height:24px;object-fit:cover;margin-right:8px;\" />' : '';
                                                        return '<div class=\"d-flex align-items-center\">' + img + '<div class=\"text-truncate\">' + escape(item.label || item.code || '') + '</div></div>';
                                                    }
                                                }"),
                                            ],
                                            'loadUrl' => Url::to(['/helpdesk/service/asset-lookup']),
                                        ])->label('รหัสครุภัณฑ์ (ถ้ามี)') ?>
                                        <div class="text-muted small">จะแสดงเป็น: รหัส — ชื่อครุภัณฑ์ — สถานที่ตั้ง</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'data_json[location]')
                                            ->textInput(['placeholder' => 'เช่น ห้อง 301, แผนกบัญชี'])
                                            ->label('สถานที่') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'data_json[phone]')
                                            ->textInput(['placeholder' => 'เบอร์โทรศัพท์ติดต่อ'])
                                            ->label('โทร') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'repair_group')->widget(Select2::classname(), [
                                            'data' => $model->listRepairGroup(),
                                            'options' => ['placeholder' => 'เลือกแผนกช่าง ...'],
                                            'pluginOptions' => [
                                                'allowClear' => true,
                                            ],
                                        ])->label('แผนกช่าง') ?>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <?= $form->field($model, 'request_repair_date')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                                            'options' => ['placeholder' => 'เลือกวันที่ต้องการให้ซ่อม'],
                                        ])->label('วันที่ต้องการให้ซ่อม') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-paperclip"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">รูปภาพปัญหา</h6>
                            </div>
                            <div class="card-body">
                                <?= FileInput::widget([
                                    'name' => 'upload_ajax[]',
                                    'options' => ['id' => 'repair-request-uploader', 'multiple' => true, 'accept' => 'image/*'],
                                    'pluginOptions' => [
                                        'showPreview' => true,
                                        'overwriteInitial' => false,
                                        'initialPreviewAsData' => true,
                                        'uploadUrl' => Url::to(['/filemanager/uploads/upload-ajax']),
                                        'uploadExtraData' => [
                                            'ref' => $model->ref,
                                            'name' => 'repair_request',
                                        ],
                                        'showUpload' => false,     // ไม่ต้องมีปุ่มอัปโหลด
                                        'showRemove' => true,
                                        'maxFileCount' => 20,
                                        'browseOnZoneClick' => true,
                                    ],
                                    'pluginEvents' => [
                                        // เลือกไฟล์แล้วอัปโหลดทันที
                                        'filebatchselected' => new JsExpression("function(){ $('#repair-request-uploader').fileinput('upload'); }"),
                                    ],
                                ]) ?>
                                <div class="small text-muted mt-2">รองรับการแนบรูปภาพ/ไฟล์ เพื่อช่วยให้ช่างวิเคราะห์อาการได้เร็วขึ้น</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header border-bottom d-flex align-items-center gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10">
                                    <i class="bi bi-card-text"></i>
                                </div>
                                <h6 class="text-uppercase text-secondary m-0">หมายเหตุเพิ่มเติม</h6>
                            </div>
                            <div class="card-body">
                                <?= $form->field($model, 'data_json[note]')
                                    ->textArea(['rows' => 4, 'placeholder' => 'ข้อมูลเพิ่มเติมที่อาจเป็นประโยชน์ต่อการซ่อม...'])
                                    ->label(false) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<'JS'
function updateAssetNumberVisibility() {
  var v = $('input[name="Helpdesk[data_json][send_type]"]:checked').val() || '';
  var wrap = $('#asset-number-wrap');
  if (!wrap.length) return;
  if (v === 'asset') {
    wrap.removeClass('d-none');
  } else {
    wrap.addClass('d-none');
    // clear select2 value if exists
    try { $('#helpdesk-asset_number').val(null).trigger('change'); } catch (e) {}
  }
}
$(document).on('change', 'input[name="Helpdesk[data_json][send_type]"]', updateAssetNumberVisibility);
$(document).ready(updateAssetNumberVisibility);

// ยืนยันก่อนบันทึก + ถ้ามีไฟล์ค้างให้อัปโหลดก่อนค่อย submit
$('#form-create-v2').on('beforeSubmit', function (e) {
  var $form = $(this);

  // 1) confirm once
  if ($form.data('confirmedSubmit') !== 1) {
    e.preventDefault();
    Swal.fire({
      title: 'ยืนยันการบันทึก',
      text: 'ต้องการบันทึกข้อมูลแจ้งซ่อมรายการนี้ใช่หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'ยืนยันบันทึก',
      cancelButtonText: 'ยกเลิก',
      reverseButtons: false
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $form.data('confirmedSubmit', 1);
      $form.trigger('submit');
    });
    return false;
  }

  // 2) pending upload gate
  var $input = $('#repair-request-uploader');
  if (!$input.length) return true;
  var fi = $input.data('fileinput');
  if (!fi) return true;

  var hasPending = false;
  try {
    hasPending = (fi.getFileStack && fi.getFileStack().length > 0);
  } catch (err) {}

  if (!hasPending) return true;

  e.preventDefault();

  // ป้องกันวนลูป submit
  if ($form.data('waitingUpload') === 1) return false;
  $form.data('waitingUpload', 1);

  $input.one('filebatchuploadsuccess', function () {
    $form.removeData('waitingUpload');
    $form.trigger('submit');
  });
  $input.one('filebatchuploaderror', function () {
    $form.removeData('waitingUpload');
  });

  $input.fileinput('upload');
  return false;
});
JS;
$this->registerJs($js);
?>

