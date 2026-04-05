<?php
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

$isNew = $model->isNewRecord;

$googleMapsApiKey = trim((string)(Yii::$app->params['googleMapsApiKey'] ?? ''));
$latInputId = Html::getInputId($model, 'lat');
$lngInputId = Html::getInputId($model, 'lng');
$defaultLat = 13.7563;
$defaultLng = 100.5018;

if ($googleMapsApiKey !== '') {
    $mapJs = <<<JS
window.attendanceLocationMapInit = function () {
    var latInput = document.getElementById('{$latInputId}');
    var lngInput = document.getElementById('{$lngInputId}');
    var mapEl = document.getElementById('attendance-location-map');
    if (!latInput || !lngInput || !mapEl || typeof google === 'undefined' || !google.maps) {
        return;
    }
    function readCoord() {
        var la = parseFloat(latInput.value);
        var ln = parseFloat(lngInput.value);
        if (!isFinite(la) || !isFinite(ln)) {
            la = {$defaultLat};
            ln = {$defaultLng};
        }
        return { lat: la, lng: ln };
    }
    function applyToInputs(pos) {
        latInput.value = pos.lat.toFixed(7);
        lngInput.value = pos.lng.toFixed(7);
        latInput.dispatchEvent(new Event('input', { bubbles: true }));
        lngInput.dispatchEvent(new Event('input', { bubbles: true }));
        latInput.dispatchEvent(new Event('change', { bubbles: true }));
        lngInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    var start = readCoord();
    var map = new google.maps.Map(mapEl, {
        center: start,
        zoom: 17,
        mapTypeControl: true,
        streetViewControl: true,
    });
    var marker = new google.maps.Marker({
        position: start,
        map: map,
        draggable: true,
    });
    marker.addListener('dragend', function () {
        var p = marker.getPosition();
        applyToInputs({ lat: p.lat(), lng: p.lng() });
    });
    map.addListener('click', function (e) {
        var p = e.latLng;
        marker.setPosition(p);
        applyToInputs({ lat: p.lat(), lng: p.lng() });
    });
    function syncMapFromInputs() {
        var c = readCoord();
        marker.setPosition(c);
        map.panTo(c);
    }
    latInput.addEventListener('change', syncMapFromInputs);
    lngInput.addEventListener('change', syncMapFromInputs);
};
JS;
    $this->registerJs($mapJs, View::POS_HEAD);
    $this->registerJsFile(
        'https://maps.googleapis.com/maps/api/js?' . http_build_query([
            'key' => $googleMapsApiKey,
            'callback' => 'attendanceLocationMapInit',
            'language' => 'th',
            'region' => 'TH',
        ]),
        ['position' => View::POS_END, 'async' => true, 'defer' => true]
    );
}
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'name')->textInput(['class' => 'form-control', 'placeholder' => 'ชื่อจุด/บริเวณ'])->label('ชื่อจุด <span class="text-danger">*</span>') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'lat')->textInput(['type' => 'number', 'step' => 'any', 'class' => 'form-control', 'placeholder' => '13.7563'])->label('Latitude') ?>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'lng')->textInput(['type' => 'number', 'step' => 'any', 'class' => 'form-control', 'placeholder' => '100.5018'])->label('Longitude') ?>
            </div>
            <?php if ($googleMapsApiKey !== ''): ?>
            <div class="col-12">
                <label class="form-label small text-muted mb-2">เลือกจุดบนแผนที่</label>
                <p class="small text-muted mb-2">คลิกบนแผนที่หรือลากหมุดเพื่อกำหนดศูนย์กลางจุดลงเวลา พิกัดจะอัปเดตในช่อง Latitude / Longitude</p>
                <div class="ratio ratio-16x9 rounded-3 border overflow-hidden shadow-sm">
                    <div id="attendance-location-map" class="bg-light"></div>
                </div>
            </div>
            <?php else: ?>
            <div class="col-12">
                <p class="small text-muted mb-0">เลือกบนแผนที่ได้เมื่อตั้งค่า <code class="small">GOOGLE_MAPS_API_KEY</code> หรือ <code class="small">googleMapsApiKey</code> ใน <code class="small">config/params.php</code> และเปิดใช้ Maps JavaScript API ใน Google Cloud</p>
            </div>
            <?php endif; ?>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'radius_m')->textInput(['type' => 'number', 'min' => 0, 'max' => 100000, 'class' => 'form-control'])->label('รัศมีอนุญาตลงเวลา (เมตร)') ?>
                <p class="form-text text-muted small mb-0">ระยะห่างสูงสุดจากจุดศูนย์กลาง (Lat/Lng) ที่ยอมรับเมื่อลงเวลาด้วย GPS — ใส่ 0 = ไม่ใช้รัศมีกับจุดนี้ (ถ้าทุกจุดเป็น 0 ระบบจะไม่บังคับตรวจพิกัด)</p>
            </div>
            <div class="col-12 col-md-6">
                <?= $form->field($model, 'qr_token')->textInput(['class' => 'form-control', 'placeholder' => 'เว้นว่างให้ระบบสร้างอัตโนมัติ'])->label('ค่า QR (ถ้ามี)') ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'active')->dropdownList([1 => 'เปิดใช้งาน', 0 => 'ปิด'], ['class' => 'form-select'])->label('สถานะ') ?>
            </div>
            <div class="col-12">
                <?= Html::submitButton($isNew ? 'เพิ่มจุดลงเวลา' : 'บันทึก', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
