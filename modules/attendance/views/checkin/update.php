<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use app\modules\attendance\services\AttendanceCorrection;
use app\modules\attendance\services\RosterAttendance;
$this->title = 'แก้ไขและระบุเวร #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = $this->title;
$comparison = RosterAttendance::forRecord($model);
$selected = $values['roster_item_id'] ?? (string)($comparison['shift']['id'] ?? '');
$options = ['' => 'ยังไม่ระบุเวร — รอตรวจสอบ'];
foreach ($shifts as $shift) $options[$shift['id']] = $shift['name'] . ' · ' . $shift['start'] . ' ถึง ' . $shift['end'];
?>
<div class="row justify-content-center attendance-correction"><div class="col-12 col-lg-8">
<div class="card border-0 shadow-sm"><div class="card-body p-3 p-md-4">
<h1 class="h5 mb-2"><?= Html::encode($this->title) ?></h1>
<p class="text-body-secondary">บันทึกประวัติการแก้ไขและส่งกลับไปรออนุมัติใหม่ วิธีลงเวลาและหลักฐานต้นฉบับจะคงเดิม</p>
<p>พนักงาน: <?= Html::encode($model->employee ? $model->employee->fname . ' ' . $model->employee->lname : '-') ?></p>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= Html::encode($error) ?> <?= Html::a('โหลดข้อมูลล่าสุด', ['update', 'id' => $model->id], ['class' => 'alert-link']) ?></div><?php endif; ?>
<?= Html::beginForm(['update', 'id' => $model->id], 'post') ?>
<?= Html::hiddenInput('Correction[revision]', $values['revision'] ?? AttendanceCorrection::revision($model)) ?>
<div class="mb-3"><label for="correction-time" class="form-label">วันเวลา</label>
<?= Html::input('datetime-local', 'Correction[checkin_at]', str_replace(' ', 'T', $values['checkin_at'] ?? $model->checkin_at), ['id' => 'correction-time', 'class' => 'form-control', 'required' => true, 'step' => 1]) ?></div>
<div class="mb-3"><label for="correction-type" class="form-label">ประเภทลงเวลา</label>
<?= Html::dropDownList('Correction[check_type]', $values['check_type'] ?? $model->check_type, ['in' => 'ลงเวลาเข้า', 'out' => 'ลงเวลาออก'], ['id' => 'correction-type', 'class' => 'form-select']) ?></div>
<div class="mb-3"><label for="correction-shift" class="form-label">เวรที่เทียบ</label>
<?= Html::dropDownList('Correction[roster_item_id]', $selected, $options, ['id' => 'correction-shift', 'class' => 'form-select']) ?>
<button type="button" id="correction-reload" class="btn btn-outline-secondary btn-sm mt-2">โหลดเวรตามวันเวลาใหม่</button>
<p id="correction-feedback" class="form-text" role="status"></p></div>
<div class="mb-3"><label for="correction-reason" class="form-label">เหตุผลแก้ไข</label>
<?= Html::textarea('Correction[reason]', $values['reason'] ?? '', ['id' => 'correction-reason', 'class' => 'form-control', 'required' => true, 'maxlength' => 2000, 'rows' => 3]) ?></div>
<div class="d-flex flex-wrap gap-2"><?= Html::submitButton('บันทึกและส่งอนุมัติใหม่', ['class' => 'btn btn-primary']) ?><?= Html::a('ยกเลิก', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?></div>
<?= Html::endForm() ?>
</div></div></div></div>
<?php
$url = Json::htmlEncode(Url::to(['roster-options', 'id' => $model->id]));
$this->registerJs(<<<JS
$('#correction-reload').on('click', function () {
    var button = $(this).prop('disabled', true);
    $.getJSON($url, { at: $('#correction-time').val() }).done(function (r) {
        if (!r.success) { $('#correction-feedback').text(r.message); return; }
        var select = $('#correction-shift').empty().append(new Option('ยังไม่ระบุเวร — รอตรวจสอบ', ''));
        r.shifts.forEach(function (s) { select.append(new Option(s.name + ' · ' + s.start + ' ถึง ' + s.end, s.id)); });
        $('#correction-feedback').text('โหลดแล้ว กรุณาเลือกเวรอีกครั้ง');
    }).fail(function () { $('#correction-feedback').text('โหลดเวรไม่สำเร็จ กรุณาลองใหม่'); }).always(function () { button.prop('disabled', false); });
});
JS);
?>
