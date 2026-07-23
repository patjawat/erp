<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ลงเวลาเข้า-ออก';
$this->params['breadcrumbs'][] = ['label' => 'ของฉัน', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$saveUrl = Url::to(['/attendance/default/save']);
$backUrl = Url::to(['/attendance/default/index']);
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
<?= $this->render('@app/modules/attendance/menu', ['active' => 'checkin']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-1 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock-history"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">เลือกเข้า หรือ ออก แล้วกดลงเวลา ระบบบันทึกเวลาปัจจุบันและส่งให้หัวหน้าอนุมัติ</p>
</div>
<?php $this->endBlock(); ?>

<div class="att-checkin">
    <div class="att-shell">

        <!-- 1) เลือกประเภท เข้า/ออก -->
        <section class="att-card">
            <div class="att-card__head">
                <h2 class="att-card__title">ประเภทการลงเวลา</h2>
            </div>
            <div class="att-card__body">
                <div class="att-seg" role="radiogroup" aria-label="ประเภทการลงเวลา">
                    <button type="button" class="att-seg__item is-active" role="radio" aria-checked="true" data-check-type="in">
                        <i class="bi bi-box-arrow-in-right att-seg__icon" aria-hidden="true"></i>
                        <span class="att-seg__label">ลงเวลาเข้า</span>
                        <span class="att-seg__hint">เริ่มปฏิบัติงาน</span>
                    </button>
                    <button type="button" class="att-seg__item" role="radio" aria-checked="false" data-check-type="out">
                        <i class="bi bi-box-arrow-right att-seg__icon" aria-hidden="true"></i>
                        <span class="att-seg__label">ลงเวลาออก</span>
                        <span class="att-seg__hint">เลิกปฏิบัติงาน</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- 2) เวลาปัจจุบัน + ตำแหน่ง -->
        <section class="att-card">
            <div class="att-card__head">
                <h2 class="att-card__title">เวลาและตำแหน่ง</h2>
                <span class="att-method-tag"><i class="bi bi-hand-index" aria-hidden="true"></i> กดลงเวลา</span>
            </div>
            <div class="att-card__body">
                <div class="att-clock" aria-live="polite">
                    <span class="att-clock__label">เวลาที่จะบันทึก</span>
                    <span id="live-clock" class="att-clock__time">--:--:--</span>
                    <span id="live-date" class="att-clock__date"></span>
                </div>

                <div class="att-loc">
                    <p class="att-loc__coord">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <span id="coord-display">กำลังตรวจสอบสิทธิ์ตำแหน่ง...</span>
                    </p>

                    <div id="location-permission-wrap" class="att-permission d-none" role="region" aria-label="ขออนุญาตตำแหน่ง">
                        <p class="att-permission__title" id="location-permission-title">ขอใช้ตำแหน่งเพื่อลงเวลา</p>
                        <p class="att-permission__text" id="location-permission-text"></p>
                        <button type="button" id="btn-allow-location" class="att-btn att-btn--light att-btn--block">
                            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i> อนุญาตใช้ตำแหน่ง
                        </button>
                        <p class="att-permission__extra d-none" id="location-permission-extra"></p>
                    </div>

                    <div id="geofence-status" class="att-fence d-none" role="status" aria-live="polite"></div>
                </div>

                <input type="hidden" id="lat" name="lat">
                <input type="hidden" id="lng" name="lng">
                <input type="hidden" id="check_type" name="check_type" value="in">
            </div>
        </section>

        <!-- ผลลัพธ์ -->
        <div id="checkin-result" class="att-result d-none" role="alert" aria-live="assertive"></div>

        <!-- 3) ปุ่มดำเนินการ -->
        <div class="att-actions">
            <button type="button" id="btn-checkin" class="att-btn att-btn--primary att-btn--block att-btn--lg">
                <span class="att-btn__spinner" aria-hidden="true"></span>
                <i class="bi bi-check-circle att-btn__icon" aria-hidden="true"></i>
                <span id="btn-checkin-label">ลงเวลาเข้า</span>
            </button>
            <a href="<?= $backUrl ?>" class="att-btn att-btn--light att-btn--block">ย้อนกลับ</a>
        </div>
    </div>
</div>

<style>
.att-checkin {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd;
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --primary-line: rgba(13, 110, 253, 0.22);
    --success: #15803d;
    --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.10);
    --danger: #b91c1c;
    --danger-soft: rgba(185, 28, 28, 0.10);
    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    color: var(--ink-1);
}

.att-checkin .att-shell {
    max-width: 640px;
    margin: 0 auto;
    padding: 1.25rem 0 2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ── Card ── */
.att-checkin .att-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    overflow: hidden;
}
.att-checkin .att-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.7rem 1.1rem;
    border-bottom: 1px solid var(--line);
    background: var(--surface-2);
}
.att-checkin .att-card__title {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--ink-2);
}
.att-checkin .att-card__body {
    padding: 1rem 1.1rem;
}
.att-checkin .att-method-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: var(--surface-3);
    color: var(--ink-2);
    font-size: 0.76rem;
    font-weight: 600;
}

/* ── Segmented in/out ── */
.att-checkin .att-seg {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
}
.att-checkin .att-seg__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    min-height: 96px;
    padding: 0.9rem 0.6rem;
    border: 1.5px solid var(--line-strong);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--ink-2);
    cursor: pointer;
    transition: border-color var(--t, 140ms) var(--ease), background var(--t, 140ms) var(--ease), box-shadow var(--t, 140ms) var(--ease), color var(--t, 140ms) var(--ease);
}
.att-checkin .att-seg__icon {
    font-size: 1.6rem;
    line-height: 1;
    color: var(--ink-3);
    transition: color 140ms var(--ease);
}
.att-checkin .att-seg__label {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ink-1);
}
.att-checkin .att-seg__hint {
    font-size: 0.76rem;
    color: var(--ink-3);
}
.att-checkin .att-seg__item:hover {
    border-color: var(--primary-line);
    background: var(--surface-hover);
}
.att-checkin .att-seg__item:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.att-checkin .att-seg__item.is-active {
    border-color: var(--primary);
    background: var(--primary-soft);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.att-checkin .att-seg__item.is-active .att-seg__icon,
.att-checkin .att-seg__item.is-active .att-seg__label {
    color: var(--primary-ink);
}

/* ── Live clock ── */
.att-checkin .att-clock {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    padding: 1.1rem 1rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--surface-2);
    margin-bottom: 0.9rem;
}
.att-checkin .att-clock__label {
    font-size: 0.78rem;
    color: var(--ink-3);
}
.att-checkin .att-clock__time {
    font-size: 2.6rem;
    font-weight: 700;
    line-height: 1.05;
    color: var(--ink-1);
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.01em;
}
.att-checkin .att-clock__date {
    font-size: 0.85rem;
    color: var(--ink-2);
    font-variant-numeric: tabular-nums;
}

/* ── Location ── */
.att-checkin .att-loc__coord {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin: 0 0 0.5rem;
    font-size: 0.85rem;
    color: var(--ink-2);
}
.att-checkin .att-loc__coord i { color: var(--ink-3); }

.att-checkin .att-permission {
    border: 1px solid var(--primary-line);
    border-radius: var(--radius-sm);
    background: var(--primary-soft);
    padding: 0.9rem;
    margin-bottom: 0.5rem;
}
.att-checkin .att-permission__title {
    margin: 0 0 0.15rem;
    font-size: 0.86rem;
    font-weight: 700;
    color: var(--primary-ink);
}
.att-checkin .att-permission__text {
    margin: 0 0 0.75rem;
    font-size: 0.82rem;
    color: var(--ink-2);
    line-height: 1.5;
}
.att-checkin .att-permission__extra {
    margin: 0.6rem 0 0;
    font-size: 0.78rem;
    color: var(--ink-3);
    line-height: 1.45;
}

.att-checkin .att-fence {
    display: flex;
    align-items: flex-start;
    gap: 0.45rem;
    padding: 0.6rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.83rem;
    font-weight: 600;
    line-height: 1.45;
}
.att-checkin .att-fence i { margin-top: 0.1rem; flex: none; }
.att-checkin .att-fence.is-ok    { background: var(--success-soft); color: var(--success); }
.att-checkin .att-fence.is-warn  { background: var(--warning-soft); color: var(--warning); }
.att-checkin .att-fence.is-error { background: var(--danger-soft);  color: var(--danger); }

/* ── Result ── */
.att-checkin .att-result {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem 1rem;
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
    font-weight: 600;
}
.att-checkin .att-result.is-ok    { background: var(--success-soft); color: var(--success); }
.att-checkin .att-result.is-error { background: var(--danger-soft);  color: var(--danger); }

/* ── Buttons ── */
.att-checkin .att-actions {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.att-checkin .att-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-height: 44px;
    padding: 0.6rem 1.1rem;
    border: 1px solid transparent;
    border-radius: var(--radius-sm);
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: background 140ms var(--ease), border-color 140ms var(--ease), color 140ms var(--ease), transform 80ms var(--ease);
}
.att-checkin .att-btn--block { width: 100%; }
.att-checkin .att-btn--lg { min-height: 54px; font-size: 1.05rem; }
.att-checkin .att-btn--primary {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}
.att-checkin .att-btn--primary:hover { background: var(--primary-ink); border-color: var(--primary-ink); color: #fff; }
.att-checkin .att-btn--primary:active { transform: translateY(1px); }
.att-checkin .att-btn--primary:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--primary-soft); }
.att-checkin .att-btn--primary:disabled { opacity: 0.5; cursor: not-allowed; }
.att-checkin .att-btn--light {
    background: var(--surface-2);
    color: var(--ink-1);
    border-color: var(--line-strong);
}
.att-checkin .att-btn--light:hover { background: var(--surface-hover); color: var(--ink-1); }
.att-checkin .att-btn--light:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--primary-soft); }

.att-checkin .att-btn__spinner {
    display: none;
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-top-color: #fff;
    border-radius: 50%;
    animation: att-spin 0.7s linear infinite;
}
.att-checkin .att-btn.is-loading .att-btn__spinner { display: inline-block; }
.att-checkin .att-btn.is-loading .att-btn__icon { display: none; }
@keyframes att-spin { to { transform: rotate(360deg); } }

@media (prefers-reduced-motion: reduce) {
    .att-checkin .att-seg__item,
    .att-checkin .att-btn { transition: none; }
    .att-checkin .att-btn__spinner { animation-duration: 1.4s; }
}
</style>

<?php
$saveUrlJs = json_encode($saveUrl);
$this->registerJs(<<<JS
(function(){
    var saveUrl = $saveUrlJs;
    var geofences = $geofencesJson;
    var requireGeofence = !!(geofences && geofences.length);
    var currentCheckType = 'in';
    var insideAllowed = null;   // null = ยังไม่รู้, true/false = ผลตรวจ

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
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function setFence(state, html) {
        var \$box = $('#geofence-status');
        \$box.removeClass('d-none is-ok is-warn is-error');
        if (!state) { \$box.addClass('d-none').html(''); return; }
        \$box.addClass(state).html(html);
    }

    function refreshGeofenceUI(la, ln) {
        if (!requireGeofence) { insideAllowed = true; setFence(null); updateSubmitState(); return; }
        if (la == null || ln == null) {
            insideAllowed = false;
            setFence('is-error', '<i class="bi bi-exclamation-triangle"></i>องค์กรกำหนดบริเวณลงเวลา ต้องได้รับพิกัด GPS ก่อนจึงจะลงเวลาได้');
            updateSubmitState();
            return;
        }
        var inside = null, nearest = null, nearestD = null;
        for (var i = 0; i < geofences.length; i++) {
            var z = geofences[i];
            var d = haversineM(la, ln, z.lat, z.lng);
            if (d <= z.radius_m) { inside = z; break; }
            if (nearestD === null || d < nearestD) { nearestD = d; nearest = z; }
        }
        if (inside) {
            insideAllowed = true;
            setFence('is-ok', '<i class="bi bi-check-circle"></i>อยู่ในบริเวณที่อนุญาต «' + escHtml(inside.name) + '» (รัศมี ' + inside.radius_m + ' ม.)');
        } else if (nearest) {
            insideAllowed = false;
            setFence('is-warn', '<i class="bi bi-geo-alt"></i>ยังไม่อยู่ในรัศมีที่กำหนด ห่างจาก «' + escHtml(nearest.name) + '» ~' + Math.round(nearestD) + ' ม. (อนุญาต ' + nearest.radius_m + ' ม.)');
        } else {
            insideAllowed = false;
            setFence(null);
        }
        updateSubmitState();
    }

    function updateSubmitState() {
        var ok = requireGeofence ? (insideAllowed === true) : true;
        var \$btn = $('#btn-checkin');
        if (\$btn.hasClass('is-loading')) return;
        \$btn.prop('disabled', !ok);
    }

    function updateCheckTypeUI() {
        $('.att-seg__item').removeClass('is-active').attr('aria-checked', 'false');
        $('.att-seg__item[data-check-type="' + currentCheckType + '"]').addClass('is-active').attr('aria-checked', 'true');
        $('#btn-checkin-label').text(currentCheckType === 'in' ? 'ลงเวลาเข้า' : 'ลงเวลาออก');
    }
    $('.att-seg__item').on('click', function() {
        currentCheckType = $(this).data('check-type');
        $('#check_type').val(currentCheckType);
        updateCheckTypeUI();
    });
    updateCheckTypeUI();

    function updateLiveClock() {
        var now = new Date();
        var p = function(n){ return String(n).padStart(2, '0'); };
        $('#live-clock').text(p(now.getHours()) + ':' + p(now.getMinutes()) + ':' + p(now.getSeconds()));
        $('#live-date').text(now.getDate() + '/' + (now.getMonth() + 1) + '/' + (now.getFullYear() + 543));
    }
    updateLiveClock();
    setInterval(updateLiveClock, 1000);

    function setCoord(la, ln) {
        $('#lat').val(la || '');
        $('#lng').val(ln || '');
        if (la != null && ln != null)
            $('#coord-display').text('พิกัด ' + la.toFixed(5) + ', ' + ln.toFixed(5));
        else
            $('#coord-display').text('ยังไม่มีพิกัด กดปุ่มด้านล่างเพื่ออนุญาตตำแหน่ง');
        refreshGeofenceUI(la, ln);
    }

    function showLocationPrompt(show, text, extra) {
        var \$w = $('#location-permission-wrap');
        var \$ex = $('#location-permission-extra');
        if (show) {
            \$w.removeClass('d-none');
            $('#location-permission-text').text(text || '');
            if (extra) { \$ex.removeClass('d-none').text(extra); }
            else { \$ex.addClass('d-none').text(''); }
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
            showLocationPrompt(true, 'เบราว์เซอร์นี้ไม่รองรับการระบุตำแหน่ง', '');
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
                    extra = 'ถ้าเคยปฏิเสธไว้ ให้ไปที่ตั้งค่าเบราว์เซอร์ > ความเป็นส่วนตัว/ตำแหน่ง แล้วอนุญาตสำหรับเว็บไซต์นี้';
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
    $('#btn-allow-location').on('click', fetchLocation);

    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' }).then(function(status) {
            if (status.state === 'denied') {
                setCoord(null, null);
                showLocationPrompt(true,
                    'การใช้ตำแหน่งถูกปิดไว้ในเบราว์เซอร์ กรุณาเปิดสิทธิ์ตำแหน่งในการตั้งค่า แล้วกดปุ่มด้านล่างเพื่อลองใหม่',
                    'iOS: ตั้งค่า > Safari > ตำแหน่ง — Android: ตั้งค่าแอปเบราว์เซอร์ > สิทธิ์ > ตำแหน่ง');
                return;
            }
            status.onchange = function() { if (status.state === 'granted') fetchLocation(); };
            fetchLocation();
        }).catch(fetchLocation);
    } else {
        fetchLocation();
    }

    function setResult(kind, html) {
        var \$res = $('#checkin-result');
        \$res.removeClass('d-none is-ok is-error').html('');
        \$res.addClass(kind === 'ok' ? 'is-ok' : 'is-error').html(html);
    }

    $('#btn-checkin').on('click', function() {
        var \$btn = $(this);
        if (\$btn.prop('disabled')) return;
        \$btn.addClass('is-loading').prop('disabled', true);
        var data = {
            method: 'manual',
            check_type: $('#check_type').val() || 'in',
            lat: $('#lat').val() || null,
            lng: $('#lng').val() || null
        };
        $.post(saveUrl, data).then(function(res) {
            if (res.success) {
                setResult('ok', '<i class="bi bi-check-circle-fill"></i>' + escHtml(res.message || 'บันทึกสำเร็จ'));
            } else {
                setResult('error', '<i class="bi bi-exclamation-triangle-fill"></i>' + escHtml(res.message || 'เกิดข้อผิดพลาด'));
            }
        }).fail(function() {
            setResult('error', '<i class="bi bi-exclamation-triangle-fill"></i>เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }).always(function() {
            \$btn.removeClass('is-loading');
            updateSubmitState();
        });
    });
})();
JS
);
?>
