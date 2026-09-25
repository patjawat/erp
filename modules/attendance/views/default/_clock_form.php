<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
$id='attendance-'.Yii::$app->security->generateRandomString(10);
$qr=Yii::$app->request->get('qr_token',''); $qr=is_string($qr)?$qr:'';
$me=\app\components\UserHelper::GetEmployee();
$this->registerCssFile('@web/css/attendance.css?v='.filemtime(Yii::getAlias('@webroot/css/attendance.css')),['depends'=>[\app\assets\AppAsset::class]],'attendance-ui');
$this->registerJsFile('@web/js/attendance-clock.js?v='.filemtime(Yii::getAlias('@webroot/js/attendance-clock.js')),['depends'=>[\yii\web\JqueryAsset::class]]);
$config=['saveUrl'=>Url::to(['/attendance/default/save']),'positionUrl'=>Url::to(['/attendance/default/position']),'shiftsUrl'=>Url::to(['/attendance/default/shifts']),'uploadUrl'=>Url::to(['/attendance/default/upload-photo']),'employeeId'=>$me->id??0,'autoStart'=>$autoStart??false];
$this->registerJs('window.AttendanceClock.mount('.Json::htmlEncode($id).','.Json::htmlEncode($config).');');
$uiId = Json::htmlEncode($id);
// Presentation only: mirror already-rendered times, without requests or changing submission behavior.
$this->registerJs(<<<JS
(function () {
    const form = document.getElementById($uiId);
    if (!form) return;
    const button = form.querySelector('[data-role="submit"]');
    function paint() {
        const hasTime = role => /\d{2}:\d{2}/.test(form.querySelector('[data-role="' + role + '"]').textContent);
        const state = hasTime('time-out') ? 'complete' : hasTime('time-in') ? 'out' : 'in';
        form.dataset.clockState = state;
        const reason = !form.querySelector('[data-role="reason-panel"]').classList.contains('d-none');
        if (!button.disabled && !reason) {
            const label = 'ลงเวลา';
            if (button.textContent !== label) button.textContent = label;
        }
    }
    const observer = new MutationObserver(paint);
    ['time-in', 'time-out', 'submit'].forEach(role => observer.observe(form.querySelector('[data-role="' + role + '"]'), {childList:true,subtree:true,characterData:true,attributes:true,attributeFilter:['disabled']}));
    observer.observe(form.querySelector('[data-role="reason-panel"]'), {attributes:true,attributeFilter:['class']});
    paint();
})();
JS);
?>
<form id="<?= $id ?>" class="attendance-clock d-flex flex-column gap-3 text-body p-3 rounded-3" novalidate data-ajax="true" data-no-loader="true">
<?= Html::hiddenInput('qr_token',$qr) ?>
<div class="attendance-clock-day small text-center">วันนี้ <span data-role="day-date"></span></div>
<div class="text-center"><time data-role="clock" aria-label="เวลาประเทศไทย"></time></div>
<button type="submit" data-role="submit" class="attendance-clock-button">ลงเวลา</button>
<div class="attendance-clock-times" aria-label="เวลาเข้าออกวันนี้">
    <?php foreach (['in'=>'IN','out'=>'OUT'] as $direction=>$label): ?>
    <div class="attendance-clock-entry attendance-clock-entry--<?= $direction ?>" aria-label="<?= $direction === 'in' ? 'เวลาเข้า' : 'เวลาออก' ?>">
        <div class="attendance-entry-heading"><span class="attendance-entry-label"><?= $label ?></span><span data-role="pending-<?= $direction ?>" class="attendance-entry-status d-none"><i class="bi bi-hourglass-split" aria-hidden="true"></i> รอยืนยัน</span></div>
        <time data-role="time-<?= $direction ?>">--:--</time>
    </div>
    <?php endforeach; ?>
</div>
<div class="attendance-clock-latest small text-center">
<a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="attendance-clock-history" aria-label="ประวัติลงเวลา" title="ประวัติลงเวลา"><i class="bi bi-clock-history" aria-hidden="true"></i></a>
<span data-role="latest"></span> <span data-role="pending-latest" class="attendance-pending d-none" role="img" aria-label="รอยืนยัน" title="รอยืนยัน"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span></div>
<p data-role="gps" class="small text-body-secondary mb-0" role="status"></p>
<div data-role="reason-panel" class="d-none">
<label for="<?= $id ?>-reason" class="form-label">เหตุผลลงเวลานอกพื้นที่</label>
<?= Html::textarea('out_of_location_reason','',['id'=>$id.'-reason','class'=>'form-control','rows'=>3,'maxlength'=>2000]) ?>
<div class="attendance-photo mt-3">
    <span class="form-label d-block">รูปยืนยันตัวตน <span class="text-danger">*</span></span>
    <label for="<?= $id ?>-photo" class="attendance-photo-pick btn btn-outline-primary w-100">
        <i class="bi bi-camera" aria-hidden="true"></i> <span data-role="photo-label">ถ่ายรูปตัวเอง</span>
    </label>
    <input type="file" id="<?= $id ?>-photo" data-role="photo-input" class="visually-hidden" accept="image/*" capture="user">
    <img data-role="photo-preview" class="attendance-photo-preview d-none" alt="ตัวอย่างรูปที่ถ่าย">
    <div class="form-text">ระบบย่อรูปและประทับเวลาให้อัตโนมัติ เก็บ 90 วัน</div>
</div>
</div>
<div data-role="result" class="alert d-none mb-0" role="alert" tabindex="-1"></div>
<noscript><p class="text-danger">กรุณาเปิด JavaScript เพื่อใช้ GPS และบันทึกเวลา</p></noscript>
</form>
