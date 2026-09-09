<?php
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$id = 'attendance-' . Yii::$app->security->generateRandomString(10);
$checkType = $checkType ?? Yii::$app->request->get('check_type', 'in');
$qr = Yii::$app->request->get('qr_token', '');
$qr = is_string($qr) ? $qr : '';
$this->registerCssFile('@web/css/attendance.css', ['depends' => [\app\assets\AppAsset::class]]);
$this->registerJsFile('@web/libs/html5-qrcode/html5-qrcode.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('@web/js/attendance-clock.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$config = Json::htmlEncode(['saveUrl' => Url::to(['/attendance/default/save']), 'shiftsUrl' => Url::to(['/attendance/default/shifts'])]);
$this->registerJs('window.AttendanceClock.mount(' . Json::htmlEncode($id) . ', ' . $config . ');');
?>
<form id="<?= Html::encode($id) ?>" class="attendance-clock d-flex flex-column gap-3" novalidate>
    <fieldset>
        <legend class="fs-6 fw-semibold">ประเภทการลงเวลา</legend>
        <div class="d-flex gap-2">
            <?php foreach (['in' => 'ลงเวลาเข้า', 'out' => 'ลงเวลาออก'] as $value => $label): ?>
                <input class="btn-check" type="radio" name="check_type" id="<?= $id . '-' . $value ?>" value="<?= $value ?>" <?= $checkType === $value ? 'checked' : '' ?>>
                <label class="btn btn-outline-primary flex-fill py-3" for="<?= $id . '-' . $value ?>"><?= $label ?></label>
            <?php endforeach; ?>
        </div>
    </fieldset>
    <div>
        <label class="form-label fw-semibold" for="<?= $id ?>-shift">เวรที่ลงเวลา</label>
        <select name="roster_item_id" id="<?= $id ?>-shift" class="form-select" aria-describedby="<?= $id ?>-shift-help"><option value="">กำลังโหลดตารางเวร...</option></select>
        <p id="<?= $id ?>-shift-help" data-role="shift-help" class="form-text mb-1" role="status"></p>
        <button type="button" data-role="reload-shifts" class="btn btn-sm btn-outline-secondary">โหลดตารางเวรใหม่</button>
    </div>
    <fieldset>
        <legend class="fs-6 fw-semibold">วิธีลงเวลา</legend>
        <div class="d-flex gap-3 flex-wrap">
            <?php foreach (['manual' => 'กดลงเวลา', 'qrcode' => 'สแกน QR'] as $value => $label): ?>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="method" id="<?= $id . '-' . $value ?>" value="<?= $value ?>" <?= ($qr !== '' ? $value === 'qrcode' : $value === 'manual') ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= $id . '-' . $value ?>"><?= $label ?></label>
            </div>
            <?php endforeach; ?>
        </div>
        <div data-role="qr-panel" class="mt-2 <?= $qr === '' ? 'd-none' : '' ?>">
            <label class="form-label" for="<?= $id ?>-qr">รหัสจาก QR จุดลงเวลา</label>
            <input name="qr_token" id="<?= $id ?>-qr" class="form-control" maxlength="255" value="<?= Html::encode($qr) ?>" autocomplete="off">
            <button type="button" data-role="scan" class="btn btn-outline-primary mt-2"><i class="bi bi-qr-code-scan me-1" aria-hidden="true"></i>เปิดกล้องสแกน QR</button>
            <button type="button" data-role="stop-scan" class="btn btn-outline-secondary mt-2 d-none">ปิดกล้อง</button>
            <div id="<?= $id ?>-reader" class="mt-2"></div>
            <p class="form-text mb-0">ใช้กล้องมือถือสแกนป้ายแล้วเปิดลิงก์ได้เช่นกัน ทุกวิธียังต้องใช้ GPS</p>
        </div>
    </fieldset>
    <div class="bg-body-tertiary rounded-3 p-3">
        <p class="fw-semibold mb-1">ตำแหน่ง GPS</p>
        <p data-role="gps" class="mb-2 text-body-secondary" role="status">ต้องได้รับตำแหน่งก่อนลงเวลา</p>
        <button type="button" class="btn btn-outline-primary" data-role="gps-refresh"><i class="bi bi-geo-alt me-1" aria-hidden="true"></i>ตรวจตำแหน่ง</button>
    </div>
    <div>
        <label class="form-label fw-semibold" for="<?= $id ?>-reason">เหตุผลกรณีลงเวลานอกพื้นที่</label>
        <textarea id="<?= $id ?>-reason" name="out_of_location_reason" rows="3" maxlength="2000" class="form-control" aria-describedby="<?= $id ?>-reason-help"></textarea>
        <p class="form-text mb-0" id="<?= $id ?>-reason-help">จำเป็นเมื่ออยู่นอกรัศมีจุดลงเวลา ระบบส่งเหตุผลและพิกัดให้หัวหน้าหรือผู้มีสิทธิตรวจสอบ</p>
    </div>
    <div data-role="result" class="alert d-none mb-0" role="alert" tabindex="-1"></div>
    <p class="small text-body-secondary mb-0">บันทึกเวลาปัจจุบันจากระบบตามเวลาไทย และเทียบกับเวรที่เลือก</p>
    <button type="submit" data-role="submit" class="btn btn-primary py-3" disabled>บันทึกเวลา</button>
    <button type="button" data-role="new" class="btn btn-outline-primary d-none">ลงเวลารายการถัดไป</button>
</form>
