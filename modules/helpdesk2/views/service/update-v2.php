<?php

use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */

$this->title = 'บันทึกงานซ่อม (ช่าง)';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => 'งานซ่อมของช่าง (V2)', 'url' => ['technician-v2']];
$this->params['breadcrumbs'][] = $this->title;

$statusOrder = ['pending' => 1, 'receive' => 2, 'in_progress' => 3, 'success' => 4, 'cancel' => 0];
$currentStep = $statusOrder[$model->status] ?? 0;
$timelineSteps = [
    ['key' => 'pending',  'label' => 'รอรับเรื่อง',  'icon' => 'bi-hourglass-split'],
    ['key' => 'receive',  'label' => 'รับเรื่อง',     'icon' => 'bi-person-check'],
    ['key' => 'in_progress', 'label' => 'กำลังดำเนินการ', 'icon' => 'bi-tools'],
    ['key' => 'success',  'label' => 'เสร็จสิ้น',     'icon' => 'bi-check-circle'],
];
$isCancelled = ($model->status === 'cancel');

// แปลงค่าเวลาเริ่ม/เสร็จจาก Y-m-d H:i เป็น ว/ด/พ.ศ. เวลา สำหรับแสดงในฟอร์ม
$startAtValue = $model->data_json['start_at'] ?? '';
$finishAtValue = $model->data_json['finish_at'] ?? '';
$toThaiDatetime = function ($str) {
    if (empty($str) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}:\d{2})/', $str, $m)) {
        return $str;
    }
    $y = (int) $m[1] + 543;
    return $m[3] . '/' . $m[2] . '/' . $y . ' ' . $m[4];
};
if ($startAtValue !== '') {
    $startAtValue = $toThaiDatetime($startAtValue);
}
if ($finishAtValue !== '') {
    $finishAtValue = $toThaiDatetime($finishAtValue);
}
?>

<div class="container-fluid py-3">
    <div class="row g-3">
        <!-- Header + Timeline -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="erp-icon-box bg-primary bg-opacity-10 flex-shrink-0">
                                <i class="bi bi-wrench-adjustable-circle"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="mb-0 fw-semibold"><?= Html::encode($this->title) ?></h4>
                                <div class="text-muted small">
                                    รหัสงาน: <span class="fw-medium text-primary"><?= Html::encode($model->repair_number) ?></span>
                                    <span class="mx-2">•</span>
                                    <?= $model->viewStatus() ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap flex-shrink-0">
                            <?= Html::a('<i class="bi bi-eye me-1"></i>ดูใบงาน', ['view-v2', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                            <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับรายการ', ['technician-v2'], ['class' => 'btn btn-outline-secondary']) ?>
                        </div>
                    </div>
                    <!-- Timeline แนวนอน: สถานะงานซ่อม -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="small text-muted text-uppercase mb-2">สถานะงานซ่อม</div>
                        <div class="d-flex align-items-flex-start flex-wrap">
                            <?php foreach ($timelineSteps as $i => $step):
                                $stepNum = $i + 1;
                                $done = !$isCancelled && $currentStep > $stepNum;
                                $active = !$isCancelled && $currentStep === $stepNum;
                                $iconClass = $done ? 'text-success' : ($active ? 'text-primary' : 'text-muted');
                                $bgClass = $done ? 'bg-success bg-opacity-10 border-success' : ($active ? 'bg-primary bg-opacity-10 border-primary' : 'bg-secondary bg-opacity-10 border-secondary');
                                $labelClass = $isCancelled || $currentStep < $stepNum ? 'text-muted' : ($active ? 'text-primary fw-medium' : 'text-success');
                            ?>
                                <?php if ($i > 0): ?>
                                    <div class="flex-grow-1 border-top border-2 align-self-center <?= $done ? 'border-success' : 'border-secondary' ?>" style="margin-bottom: 1.4rem; min-width: 1rem;"></div>
                                <?php endif; ?>
                                <div class="d-flex flex-column align-items-center text-center flex-shrink-0" style="min-width: 5rem;">
                                    <div class="rounded-circle border <?= $bgClass ?> d-flex align-items-center justify-content-center <?= $iconClass ?>" style="width: 2.25rem; height: 2.25rem;">
                                        <i class="bi <?= $step['icon'] ?> small"></i>
                                    </div>
                                    <span class="small mt-1 <?= $labelClass ?>"><?= Html::encode($step['label']) ?></span>
                                </div>
                                <?php if ($i < count($timelineSteps) - 1): ?>
                                    <div class="flex-grow-1 border-top border-2 align-self-center <?= $currentStep > $stepNum ? 'border-success' : 'border-secondary' ?>" style="margin-bottom: 1.4rem; min-width: 1rem;"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($isCancelled): ?>
                            <div class="mt-2 text-center">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1">ยกเลิก</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ซ้าย: ข้อมูลผู้แจ้ง + รูปแจ้งซ่อม -->
        <div class="col-12 col-xl-4 order-2 order-xl-1">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">ข้อมูลผู้แจ้ง</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 48px; height: 48px;">
                                    <?php
                                    $emp = $model->emp;
                                    if ($emp && method_exists($emp, 'getImg')):
                                        echo Html::img($emp->getImg(), ['alt' => '', 'class' => 'object-fit-cover', 'style' => 'width: 48px; height: 48px;']);
                                    else:
                                        echo '<i class="bi bi-person-fill text-primary"></i>';
                                    endif;
                                    ?>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate"><?= Html::encode($model->emp->fullname ?? '-') ?></div>
                                    <div class="small text-muted text-truncate"><?= Html::encode($model->emp?->departmentName() ?? '-') ?></div>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 py-2">
                                    <span class="text-muted">สถานที่</span>
                                    <span class="fw-medium text-end text-break ms-2"><?= Html::encode($model->data_json['location'] ?? '-') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 py-2">
                                    <span class="text-muted">ความเร่งด่วน</span>
                                    <span><?= $model->viewUrgent()['view'] ?? '-' ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-0 py-2">
                                    <span class="text-muted">อาการที่แจ้ง</span>
                                    <span class="fw-medium text-end text-break ms-2" style="max-width: 65%;"><?= Html::encode($model->title) ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-images"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">รูปแจ้งซ่อม</h6>
                        </div>
                        <div class="card-body">
                            <div class="small text-muted mb-2">รูปภาพที่ผู้แจ้งแนบมา</div>
                            <?= $model->imageRequest ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ขวา: ฟอร์มบันทึกงานซ่อม -->
        <div class="col-12 col-xl-8 order-1 order-xl-2">
            <?php $form = ActiveForm::begin(['id' => 'form-update-v2']); ?>
            <div class="row g-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-clipboard-data"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">ภาพรวมงานซ่อม</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <?= $form->field($model, 'data_json[repair_channel]')->widget(Select2::class, [
                                        'data' => $model->listRepairChannel(),
                                        'options' => ['placeholder' => 'เลือกช่องทางซ่อม ...'],
                                        'pluginOptions' => ['allowClear' => true],
                                    ])->label('ช่องทางซ่อม') ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <?= $form->field($model, 'status')->widget(Select2::class, [
                                        'data' => $model->ListStatus(),
                                        'options' => ['placeholder' => 'เปลี่ยนสถานะ ...'],
                                        'pluginOptions' => ['allowClear' => true],
                                    ])->label('สถานะงาน') ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <?= $form->field($model, 'data_json[urgency]')->widget(Select2::class, [
                                        'data' => $model->listUrgency(),
                                        'options' => ['placeholder' => 'ความเร่งด่วน ...'],
                                        'pluginOptions' => ['allowClear' => true],
                                    ])->label('ความเร่งด่วน') ?>
                                </div>
                                <div class="col-12">
                                    <?= $form->field($model, 'data_json[tech_note]')
                                        ->textArea(['rows' => 3, 'placeholder' => 'บันทึกสั้น ๆ สำหรับทีมช่าง (ภายใน)'])
                                        ->label('หมายเหตุช่าง') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-bug"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">การวินิจฉัย</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <?= $form->field($model, 'data_json[root_cause]')
                                        ->textArea(['rows' => 3, 'placeholder' => 'สาเหตุของปัญหา (Root Cause)'])
                                        ->label('สาเหตุของปัญหา') ?>
                                </div>
                                <div class="col-12">
                                    <?= $form->field($model, 'data_json[diagnosis]')
                                        ->textArea(['rows' => 3, 'placeholder' => 'ผลการตรวจสอบ/การวิเคราะห์'])
                                        ->label('รายละเอียดการวินิจฉัย') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-tools"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">งานซ่อม</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <?= $form->field($model, 'data_json[start_at]')->widget(Datetimepicker::class, [
                                        'options' => [
                                            'timepicker' => true,
                                            'datepicker' => true,
                                            'mask' => '99/99/9999 99:99',
                                            'lang' => 'th',
                                            'yearOffset' => 543,
                                            'format' => 'd/m/Y H:i',
                                            'value' => $startAtValue,
                                        ],
                                    ])->label('เวลาเริ่ม') ?>
                                </div>
                                <div class="col-12 col-md-6">
                                    <?= $form->field($model, 'data_json[finish_at]')->widget(Datetimepicker::class, [
                                        'options' => [
                                            'timepicker' => true,
                                            'datepicker' => true,
                                            'mask' => '99/99/9999 99:99',
                                            'lang' => 'th',
                                            'yearOffset' => 543,
                                            'format' => 'd/m/Y H:i',
                                            'value' => $finishAtValue,
                                        ],
                                    ])->label('เวลาเสร็จ') ?>
                                </div>
                                <div class="col-12">
                                    <div class="small text-muted" id="repairDurationHint"></div>
                                </div>
                                <div class="col-12">
                                    <?= $form->field($model, 'data_json[repair_description]')
                                        ->textArea(['rows' => 4, 'placeholder' => 'รายละเอียดการดำเนินการซ่อม'])
                                        ->label('รายละเอียดการซ่อม') ?>
                                </div>
                                <div class="col-12">
                                    <?= $form->field($model, 'data_json[solution]')
                                        ->textArea(['rows' => 3, 'placeholder' => 'แนวทางแก้ไข/วิธีการแก้ปัญหา'])
                                        ->label('แนวทางแก้ไข') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-camera"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">รูปภาพงานซ่อม</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">แนบรูปภาพระหว่าง/หลังการซ่อม (อาการก่อนซ่อม, ระหว่างซ่อม, หลังซ่อม ฯลฯ)</p>
                            <?= $model->Upload('repair_work_photo') ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">อะไหล่และต้นทุน</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <?= $form->field($model, 'data_json[spare_parts]')
                                        ->textArea(['rows' => 3, 'placeholder' => 'รายการอะไหล่ที่ใช้ (พิมพ์เป็นบรรทัด)'])
                                        ->label('อะไหล่ที่ใช้') ?>
                                </div>
                                <div class="col-12 col-md-4">
                                    <?= $form->field($model, 'data_json[cost_labor]')
                                        ->textInput(['placeholder' => '0'])
                                        ->label('ค่าจ้างช่าง') ?>
                                </div>
                                <div class="col-12 col-md-4">
                                    <?= $form->field($model, 'data_json[cost_parts]')
                                        ->textInput(['placeholder' => '0'])
                                        ->label('ค่าอะไหล่') ?>
                                </div>
                                <div class="col-12 col-md-4">
                                    <?= $form->field($model, 'data_json[cost_total]')
                                        ->textInput(['placeholder' => '0'])
                                        ->label('ต้นทุนซ่อมรวม') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12" id="card-external-detail-wrap" style="display: none;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-building"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">รายละเอียดส่งซ่อมภายนอก</h6>
                        </div>
                        <div class="card-body">
<p class="text-muted small mb-3">กรอกรายละเอียดการส่งซ่อมภายนอก</p>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'data_json[external_vendor]')
                                    ->textInput(['placeholder' => 'ชื่อร้าน / ผู้รับซ่อม'])
                                    ->label('ร้าน/ผู้รับซ่อม') ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'data_json[external_contact]')
                                    ->textInput(['placeholder' => 'ชื่อผู้ติดต่อ / เบอร์โทร'])
                                    ->label('ผู้ติดต่อ / เบอร์โทร') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[external_items]')
                                    ->textArea(['rows' => 2, 'placeholder' => 'รายการที่ส่งไปซ่อม (อุปกรณ์/ชิ้นส่วน)'])
                                    ->label('รายการที่ส่งซ่อม') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[external_location]')
                                    ->textInput(['placeholder' => 'ที่อยู่ร้าน / สถานที่ส่ง'])
                                    ->label('สถานที่/ที่อยู่') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[external_notes]')
                                    ->textArea(['rows' => 3, 'placeholder' => 'วิธีส่ง-รับ คืน เงื่อนไข หรือหมายเหตุอื่น ๆ'])
                                    ->label('หมายเหตุ/ขั้นตอน') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12" id="card-external-bill-wrap" style="display: none;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom d-flex align-items-center gap-2">
                            <div class="erp-icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <h6 class="text-uppercase text-secondary m-0">สรุปค่าใช้จ่ายแนบบิล</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">สำหรับส่งซ่อมภายนอก สามารถแนบไฟล์ใบเสร็จ/บิลประกอบการสรุปค่าใช้จ่ายได้</p>
                            <?= $model->Upload('external_repair_bill') ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <?= Html::a('<i class="bi bi-x-lg me-1"></i>ยกเลิก', ['technician-v2'], ['class' => 'btn btn-outline-secondary']) ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save2 me-1"></i> บันทึก
                        </button>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$js = <<<JS
// ใช้ locale ไทยของ xdsoft datetimepicker (เดือน/วันเป็นภาษาไทย, ปี พ.ศ.)
if (typeof jQuery !== 'undefined' && jQuery.datetimepicker && jQuery.datetimepicker.setLocale) {
  jQuery.datetimepicker.setLocale('th');
}

function parseDateTime(value) {
  if (!value) return null;
  value = value.trim();
  // รูปแบบ ว/ด/พ.ศ. เวลา (เช่น 16/03/2569 09:00)
  var m = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{2})/);
  if (m) {
    var day = parseInt(m[1], 10);
    var month = parseInt(m[2], 10) - 1;
    var year = parseInt(m[3], 10) - 543;
    var h = parseInt(m[4], 10);
    var min = parseInt(m[5], 10);
    var d = new Date(year, month, day, h, min);
    return isNaN(d.getTime()) ? null : d;
  }
  // รูปแบบ Y-m-d H:i (fallback)
  var d = new Date(value.replace(' ', 'T'));
  return isNaN(d.getTime()) ? null : d;
}

function updateDuration() {
  const start = parseDateTime(\$('#helpdesk-data_json-start_at').val());
  const end = parseDateTime(\$('#helpdesk-data_json-finish_at').val());
  const el = \$('#repairDurationHint');
  if (!start || !end) {
    el.text('');
    return;
  }
  const diffMs = end - start;
  if (diffMs <= 0) {
    el.text('ระยะเวลา: -');
    return;
  }
  const mins = Math.floor(diffMs / 60000);
  const h = Math.floor(mins / 60);
  const m = mins % 60;
  el.text('ระยะเวลาโดยประมาณ: ' + h + ' ชม. ' + m + ' นาที');
}

var thaiMonths = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
var thaiYear = function(ct) {
  var leap = 3;
  var dayWeek = ["พฤ.", "ศ.", "ส.", "อา.", "จ.", "อ.", "พ."];
  if (ct) {
    var yearL = new Date(ct).getFullYear() - 543;
    leap = (((yearL % 4 === 0) && (yearL % 100 !== 0)) || (yearL % 400 === 0)) ? 2 : 3;
    if (leap === 2) dayWeek = ["ศ.", "ส.", "อา.", "จ.", "อ.", "พ.", "พฤ."];
  }
  this.setOptions({ i18n: { th: { dayOfWeek: dayWeek, months: thaiMonths } }, dayOfWeekStart: leap });
};

// แก้ค่าในช่องให้เป็นปี พ.ศ. (25xx) ถ้า plugin ใส่ปี ค.ศ. (20xx)
function ensureThaiYearInValue(selector) {
  var \$el = \$(selector);
  var val = (\$el.val() || '').trim();
  var match = val.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}:\d{2})/);
  if (match) {
    var y = parseInt(match[3], 10);
    if (y >= 2000 && y < 2100) {
      \$el.val(match[1] + '/' + match[2] + '/' + (y + 543) + ' ' + match[4]);
    }
  }
}

var dtOptions = {
  timepicker: true,
  format: 'd/m/Y H:i',
  lang: 'th',
  yearOffset: 543,
  i18n: { th: { dayOfWeek: ["พฤ.", "ศ.", "ส.", "อา.", "จ.", "อ.", "พ."], months: thaiMonths } },
  onChangeMonth: thaiYear,
  onShow: thaiYear,
  closeOnDateSelect: true
};

\$('#helpdesk-data_json-start_at').datetimepicker('destroy').datetimepicker(dtOptions).on('change', function() {
  ensureThaiYearInValue('#helpdesk-data_json-start_at');
  updateDuration();
});
\$('#helpdesk-data_json-finish_at').datetimepicker('destroy').datetimepicker(dtOptions).on('change', function() {
  ensureThaiYearInValue('#helpdesk-data_json-finish_at');
  updateDuration();
});

ensureThaiYearInValue('#helpdesk-data_json-start_at');
ensureThaiYearInValue('#helpdesk-data_json-finish_at');
\$(document).on('input', '#helpdesk-data_json-start_at, #helpdesk-data_json-finish_at', function() {
  ensureThaiYearInValue('#helpdesk-data_json-start_at');
  ensureThaiYearInValue('#helpdesk-data_json-finish_at');
  updateDuration();
});
updateDuration();

function toggleExternalCards() {
  var sel = document.querySelector('select[name*="data_json"][name*="repair_channel"]');
  var val = sel ? sel.value : '';
  var show = (val === 'external');
  \$('#card-external-detail-wrap').toggle(show);
  \$('#card-external-bill-wrap').toggle(show);
}
\$(document).on('change', 'select[name*="data_json"][name*="repair_channel"]', toggleExternalCards);
toggleExternalCards();
JS;
$this->registerJs($js, View::POS_END);
?>
