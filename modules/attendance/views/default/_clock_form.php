<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
$id='attendance-'.Yii::$app->security->generateRandomString(10);
$qr=Yii::$app->request->get('qr_token',''); $qr=is_string($qr)?$qr:'';
$me=\app\components\UserHelper::GetEmployee();
$this->registerCssFile('@web/css/attendance.css',['depends'=>[\app\assets\AppAsset::class]]);
$this->registerJsFile('@web/js/attendance-clock.js',['depends'=>[\yii\web\JqueryAsset::class]]);
$config=['saveUrl'=>Url::to(['/attendance/default/save']),'positionUrl'=>Url::to(['/attendance/default/position']),'shiftsUrl'=>Url::to(['/attendance/default/shifts']),'employeeId'=>$me->id??0,'autoStart'=>$autoStart??false];
$this->registerJs('window.AttendanceClock.mount('.Json::htmlEncode($id).','.Json::htmlEncode($config).');');
?>
<form id="<?= $id ?>" class="attendance-clock d-flex flex-column gap-3 text-body bg-body p-3 rounded-3" novalidate>
<?= Html::hiddenInput('qr_token',$qr) ?>
<div class="text-center"><div class="small text-body-secondary">เวลาประเทศไทย</div><time data-role="clock" class="fs-2 fw-semibold">กำลังโหลดเวลา…</time></div>
<p data-role="latest" class="small mb-0">กำลังตรวจรายการล่าสุด…</p>
<?php if ($qr !== ''): ?><p class="mb-0">ลงเวลาผ่านป้าย QR — ระบบตรวจ GPS ของจุดนี้</p><?php endif; ?>
<p data-role="gps" class="small text-body-secondary mb-0" role="status">กดสแกนเวลาเพื่อเปิด GPS และบันทึก ไม่ต้องเลือกเข้า–ออก</p>
<div data-role="reason-panel" class="d-none">
<label for="<?= $id ?>-reason" class="form-label">เหตุผลลงเวลานอกพื้นที่</label>
<?= Html::textarea('out_of_location_reason','',['id'=>$id.'-reason','class'=>'form-control','rows'=>3,'maxlength'=>2000]) ?>
<p class="form-text">ระบุเหตุผลแล้วส่งให้หัวหน้าตรวจสอบ</p></div>
<div data-role="result" class="alert d-none mb-0" role="alert" tabindex="-1"></div>
<button type="submit" data-role="submit" class="btn btn-primary py-3">สแกนเวลา</button>
<a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="text-center">ประวัติลงเวลาของฉัน</a>
<noscript><p class="text-danger">กรุณาเปิด JavaScript เพื่อใช้ GPS และบันทึกเวลา</p></noscript>
</form>
