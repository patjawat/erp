<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ลงเวลาเข้า-ออก';
$this->params['breadcrumbs'][] = ['label' => 'ของฉัน', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$saveUrl = Url::to(['/attendance/default/save']);
$geofences = $geofences ?? [];
$geofencesForJs = [];
foreach ($geofences as $g) {
    $geofencesForJs[] = [
        'id' => (int)$g->id,
        'name' => $g->name,
        'lat' => (float)$g->lat,
        'lng' => (float)$g->lng,
        'radius_m' => (int)$g->radius_m,
    ];
}
$geofencesJson = json_encode($geofencesForJs, JSON_UNESCAPED_UNICODE);
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'checkin']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock-history fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">เลือกลงเวลาเข้า หรือออก — ระบบบันทึกเวลาปัจจุบัน และส่งให้หัวหน้าอนุมัติ</p>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">
    <!-- เลือกประเภท: เข้า / ออก (เน้นหลัก) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-primary text-white py-3 px-3">
            <h6 class="mb-0 small fw-normal d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left-right"></i> เลือกประเภทการลงเวลา
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <button type="button" class="btn check-type-btn w-100 py-4 rounded-3 d-flex flex-column align-items-center gap-2 border-2 border-success bg-success bg-opacity-10" data-check-type="in">
                        <span class="rounded-circle bg-success bg-opacity-25 p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-in-right fs-2 text-success"></i>
                        </span>
                        <span class="fw-semibold">ลงเวลาเข้า</span>
                        <span class="text-muted small">บันทึกเวลาเข้างาน</span>
                    </button>
                </div>
                <div class="col-12 col-md-6">
                    <button type="button" class="btn check-type-btn w-100 py-4 rounded-3 d-flex flex-column align-items-center gap-2 border" data-check-type="out">
                        <span class="rounded-circle bg-secondary bg-opacity-25 p-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-arrow-right fs-2 text-secondary"></i>
                        </span>
                        <span class="fw-semibold">ลงเวลาออก</span>
                        <span class="text-muted small">บันทึกเวลาออกงาน</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- เวลาปัจจุบัน + วิธีลงเวลา -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-light py-2 px-3">
            <h6 class="mb-0 small fw-normal">รายละเอียด</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-lg-4">
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-primary bg-opacity-10">
                        <i class="bi bi-clock fs-1 text-primary"></i>
                        <div>
                            <span class="text-muted d-block mb-1">เวลาที่จะบันทึก</span>
                            <span id="live-clock" class="fw-bold d-block fs-1">--:--:--</span>
                            <span id="live-date" class="text-muted ms-0"></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <label class="form-label fw-semibold small text-muted">วิธีลงเวลา</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary checkin-method active rounded-pill px-3" data-method="manual">
                            <i class="bi bi-hand-index me-1"></i> กดลงเวลา
                        </button>
                        <?php if (false): ?>
                        <button type="button" class="btn btn-outline-primary checkin-method rounded-pill px-3" data-method="qrcode">
                            <i class="bi bi-qr-code me-1"></i> สแกน QR
                        </button>
                        <button type="button" class="btn btn-outline-primary checkin-method rounded-pill px-3" data-method="photo">
                            <i class="bi bi-camera me-1"></i> ถ่ายรูป
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-3 d-none" id="qr-area">
                <label class="form-label fw-semibold">ค่า QR ที่สแกน</label>
                <input type="text" id="qr_token" class="form-control" placeholder="สแกน QR หรือวางค่าที่นี่">
            </div>
            <div class="col-12 mt-3 d-none" id="photo-area">
                <label class="form-label fw-semibold">รูปถ่าย (หลักฐาน)</label>
                <input type="file" id="photo_file" class="form-control" accept="image/*" capture="environment">
                <input type="hidden" id="photo_path" name="photo_path">
            </div>
            <div class="col-12 mt-3">
                <p class="text-muted small mb-2">
                    <i class="bi bi-geo-alt me-1"></i> <span id="coord-display">กำลังตรวจสอบสิทธิ์ตำแหน่ง...</span>
                </p>
                <div id="location-permission-wrap" class="d-none alert alert-primary border-0 mb-0 py-3" role="region" aria-label="ขออนุญาตตำแหน่ง">
                    <p class="small fw-semibold mb-1" id="location-permission-title">ขอใช้ตำแหน่งเพื่อลงเวลา</p>
                    <p class="small mb-3" id="location-permission-text"></p>
                    <button type="button" id="btn-allow-location" class="btn btn-light text-primary fw-semibold w-100 rounded-3">
                        <i class="bi bi-geo-alt-fill me-1"></i> อนุญาตใช้ตำแหน่ง
                    </button>
                    <p class="small mb-0 mt-2 d-none opacity-75" id="location-permission-extra"></p>
                </div>
                <div id="geofence-status" class="alert alert-light border small py-2 mb-0 mt-2 d-none" role="status"></div>
            </div>
            <input type="hidden" id="lat" name="lat">
            <input type="hidden" id="lng" name="lng">
            <input type="hidden" id="check_type" name="check_type" value="in">
        </div>
    </div>

    <!-- ปุ่มดำเนินการ -->
    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center justify-content-sm-start mb-4">
        <button type="button" id="btn-checkin" class="btn btn-primary btn-lg px-5 py-3 rounded-3">
            <i class="bi bi-check-circle me-2"></i> <span id="btn-checkin-label">ลงเวลาเข้า</span>
        </button>
        <a href="<?= Url::to(['/attendance/default/index']) ?>" class="btn btn-outline-secondary btn-lg py-3 rounded-3">ย้อนกลับ</a>
    </div>

    <div id="checkin-result" class="alert rounded-3 mb-0 d-none" role="alert"></div>
</div>

<?php
$saveUrlJs = json_encode($saveUrl);
$this->registerJs(<<<JS
(function(){
    var saveUrl = $saveUrlJs;
    var geofences = $geofencesJson;
    var currentMethod = 'manual';
    var currentCheckType = 'in';
    var lat = null, lng = null;

    function escHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function haversineM(lat1, lon1, lat2, lon2) {
        var R = 6371000;
        var toRad = function(x) { return x * Math.PI / 180; };
        var dLat = toRad(lat2 - lat1);
        var dLon = toRad(lon2 - lon1);
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function refreshGeofenceUI(la, ln) {
        var \$box = $('#geofence-status');
        if (!geofences || !geofences.length) {
            \$box.addClass('d-none').removeClass('alert-success alert-warning alert-danger').text('');
            return;
        }
        if (la == null || ln == null) {
            \$box.removeClass('d-none alert-success alert-warning').addClass('alert-danger')
                .html('<i class="bi bi-exclamation-triangle me-1"></i>องค์กรกำหนดบริเวณลงเวลา — ต้องได้รับพิกัด GPS ก่อนลงเวลา');
            return;
        }
        var inside = null;
        var nearest = null;
        var nearestD = null;
        for (var i = 0; i < geofences.length; i++) {
            var z = geofences[i];
            var d = haversineM(la, ln, z.lat, z.lng);
            if (d <= z.radius_m) {
                inside = z;
                break;
            }
            if (nearestD === null || d < nearestD) {
                nearestD = d;
                nearest = z;
            }
        }
        if (inside) {
            \$box.removeClass('d-none alert-warning alert-danger').addClass('alert-success')
                .html('<i class="bi bi-check-circle me-1"></i>อยู่ในบริเวณที่อนุญาต: «' + escHtml(inside.name) + '» (รัศมี ' + inside.radius_m + ' ม.)');
        } else if (nearest) {
            \$box.removeClass('d-none alert-success alert-danger').addClass('alert-warning')
                .html('<i class="bi bi-geo-alt me-1"></i>ยังไม่อยู่ในรัศมีที่กำหนด — ห่างจาก «' + escHtml(nearest.name) + '» ~' + Math.round(nearestD) + ' ม. (อนุญาต ' + nearest.radius_m + ' ม.)');
        } else {
            \$box.addClass('d-none').text('');
        }
    }

    function updateCheckTypeUI() {
        $('.check-type-btn').removeClass('border-2 border-success border-secondary bg-success bg-opacity-10 bg-secondary bg-opacity-10').addClass('border');
        var \$sel = $('.check-type-btn[data-check-type="' + currentCheckType + '"]');
        \$sel.removeClass('border');
        if (currentCheckType === 'in') {
            \$sel.addClass('border-2 border-success bg-success bg-opacity-10');
        } else {
            \$sel.addClass('border-2 border-secondary bg-secondary bg-opacity-10');
        }
        $('#btn-checkin-label').text(currentCheckType === 'in' ? 'ลงเวลาเข้า' : 'ลงเวลาออก');
        $('#btn-checkin').removeClass('btn-secondary').addClass('btn-primary');
        if (currentCheckType === 'out') $('#btn-checkin').removeClass('btn-primary').addClass('btn-secondary');
    }

    $('.check-type-btn').on('click', function() {
        currentCheckType = $(this).data('check-type');
        $('#check_type').val(currentCheckType);
        updateCheckTypeUI();
    });
    updateCheckTypeUI();

    function updateLiveClock() {
        var now = new Date();
        var h = String(now.getHours()).padStart(2, '0');
        var m = String(now.getMinutes()).padStart(2, '0');
        var s = String(now.getSeconds()).padStart(2, '0');
        $('#live-clock').text(h + ':' + m + ':' + s);
        var d = now.getDate(), mo = now.getMonth() + 1, y = now.getFullYear() + 543;
        $('#live-date').text(d + '/' + mo + '/' + y);
    }
    updateLiveClock();
    setInterval(updateLiveClock, 1000);

    function updateMethod(m) {
        currentMethod = m;
        $('.checkin-method').removeClass('active').addClass('btn-outline-primary');
        $('.checkin-method[data-method="' + m + '"]').removeClass('btn-outline-primary').addClass('active btn-primary');
        $('#qr-area').toggleClass('d-none', m !== 'qrcode');
        $('#photo-area').toggleClass('d-none', m !== 'photo');
    }
    $('.checkin-method').on('click', function() {
        updateMethod($(this).data('method'));
    });

    function setCoord(la, ln) {
        lat = la; lng = ln;
        $('#lat').val(la || '');
        $('#lng').val(ln || '');
        if (la != null && ln != null)
            $('#coord-display').text('พิกัด: ' + la.toFixed(5) + ', ' + ln.toFixed(5));
        else
            $('#coord-display').text('ยังไม่มีพิกัด — กดปุ่มด้านล่างเพื่ออนุญาตตำแหน่ง');
        refreshGeofenceUI(la, ln);
    }

    function showLocationPrompt(show, text, extra) {
        var \$w = $('#location-permission-wrap');
        var \$ex = $('#location-permission-extra');
        if (show) {
            \$w.removeClass('d-none');
            $('#location-permission-text').text(text || '');
            if (extra) {
                \$ex.removeClass('d-none').text(extra);
            } else {
                \$ex.addClass('d-none').text('');
            }
        } else {
            \$w.addClass('d-none');
            \$ex.addClass('d-none').text('');
        }
    }

    function fetchLocation() {
        var \$btn = $('#btn-allow-location');
        \$btn.prop('disabled', true);
        $('#coord-display').text('กำลังขอตำแหน่ง...');
        if (!navigator.geolocation) {
            setCoord(null, null);
            showLocationPrompt(true,
                'เบราว์เซอร์นี้ไม่รองรับการระบุตำแหน่ง',
                '');
            \$btn.prop('disabled', true);
            return;
        }
        navigator.geolocation.getCurrentPosition(
            function(p) {
                \$btn.prop('disabled', false);
                showLocationPrompt(false);
                setCoord(p.coords.latitude, p.coords.longitude);
            },
            function(err) {
                \$btn.prop('disabled', false);
                setCoord(null, null);
                var code = err && err.code;
                var msg = 'ยังไม่ได้รับตำแหน่ง กรุณากดปุ่ม «อนุญาตใช้ตำแหน่ง» แล้วเลือกอนุญาตเมื่อระบบถาม';
                var extra = '';
                if (code === 1) {
                    msg = 'ยังไม่อนุญาตให้ใช้ตำแหน่ง กรุณากดปุ่มด้านล่าง แล้วเลือกอนุญาตในหน้าต่างของเบราว์เซอร์';
                    extra = 'ถ้าเคยปฏิเสธไว้ ให้ไปที่การตั้งค่าเบราว์เซอร์ > ความเป็นส่วนตัว/ตำแหน่ง แล้วอนุญาตสำหรับเว็บไซต์นี้';
                } else if (code === 2) {
                    msg = 'ระบบหาตำแหน่งไม่ได้ชั่วคราว ลองอีกครั้งในที่โล่งหรือเปิด GPS';
                } else if (code === 3) {
                    msg = 'หมดเวลารอตำแหน่ง กรุณากดปุ่มเพื่อลองใหม่';
                }
                showLocationPrompt(true, msg, extra);
            },
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
        );
    }

    $('#btn-allow-location').on('click', function() { fetchLocation(); });

    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' }).then(function(status) {
            if (status.state === 'denied') {
                setCoord(null, null);
                showLocationPrompt(true,
                    'การใช้ตำแหน่งถูกปิดไว้ในเบราว์เซอร์ กรุณาเปิดสิทธิ์ตำแหน่งในการตั้งค่า แล้วกดปุ่มด้านล่างเพื่อลองใหม่',
                    'iOS: ตั้งค่า > Safari > ตำแหน่ง — Android: ตั้งค่าแอปเบราว์เซอร์ > สิทธิ์ > ตำแหน่ง');
                return;
            }
            status.onchange = function() {
                if (status.state === 'granted') {
                    fetchLocation();
                }
            };
            fetchLocation();
        }).catch(function() { fetchLocation(); });
    } else {
        fetchLocation();
    }

    $('#btn-checkin').on('click', function() {
        var \$btn = $(this);
        \$btn.prop('disabled', true);
        var data = {
            method: currentMethod,
            check_type: $('#check_type').val() || 'in',
            lat: $('#lat').val() || null,
            lng: $('#lng').val() || null,
            qr_token: currentMethod === 'qrcode' ? $('#qr_token').val().trim() || null : null,
            photo_path: currentMethod === 'photo' ? $('#photo_path').val() || null : null
        };
        $.post(saveUrl, data).then(function(res) {
            var \$res = $('#checkin-result');
            \$res.removeClass('d-none alert-danger alert-success').html('');
            if (res.success) {
                \$res.addClass('alert-success').html('<i class="bi bi-check-circle-fill me-2"></i>' + (res.message || 'บันทึกสำเร็จ'));
                $('#qr_token').val('');
                updateMethod('manual');
            } else {
                \$res.addClass('alert-danger').html('<i class="bi bi-exclamation-triangle-fill me-2"></i>' + (res.message || 'เกิดข้อผิดพลาด'));
            }
        }).fail(function() {
            $('#checkin-result').removeClass('d-none alert-success').addClass('alert-danger')
                .html('<i class="bi bi-exclamation-triangle-fill me-2"></i>เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }).always(function() {
            \$btn.prop('disabled', false);
        });
    });

    $('#photo_file').on('change', function() {
        var f = this.files[0];
        if (!f) return;
        var fd = new FormData();
        fd.append('file', f);
        $.ajax({
            url: $('body').data('upload-photo-url') || '/attendance/default/upload-photo',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false
        }).done(function(r) {
            if (r && r.url) $('#photo_path').val(r.url);
        });
    });
})();
JS
);
?>
