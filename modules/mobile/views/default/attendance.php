<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\modules\attendance\models\CheckinRecord;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\hr\models\Employees $employee */
/** @var \app\modules\attendance\models\CheckinLocation[] $geofences */
/** @var CheckinRecord|null $lastCheckin */
/** @var CheckinRecord[] $history */

$this->params['current_page'] = $current_page ?? 'attendance';
$this->params['mobileTitle'] = 'ลงเวลา';
$this->params['mobileSubtitle'] = 'บันทึกเวลาเข้า-ออกงาน';

$geofences = $geofences ?? [];
$geofencesForJs = [];
foreach ($geofences as $g) {
    $geofencesForJs[] = [
        'id' => (int) $g->id,
        'name' => $g->name,
        'lat' => (float) $g->lat,
        'lng' => (float) $g->lng,
        'radius_m' => (int) $g->radius_m,
    ];
}
$geofencesJson = json_encode($geofencesForJs, JSON_UNESCAPED_UNICODE);
$saveUrl = Url::to(['/attendance/default/save']);
$saveUrlJs = json_encode($saveUrl);
$csrfParamJs = json_encode(Yii::$app->request->csrfParam);
$csrfTokenJs = json_encode(Yii::$app->request->csrfToken);
?>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon'     => 'clock',
    'title'    => $this->params['mobileTitle'],
    'subtitle' => $this->params['mobileSubtitle'],
]) ?>

<div class="app-scroll">

<?php if ($msg = Yii::$app->session->getFlash('success')): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 small mb-3"><?= Html::encode($msg) ?></div>
<?php endif; ?>
<?php if ($msg = Yii::$app->session->getFlash('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-3 small mb-3"><?= Html::encode($msg) ?></div>
<?php endif; ?>

<div class="d-flex flex-column gap-3">
    <?php if (!empty($lastCheckin)): ?>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-start gap-2">
                    <i data-lucide="history" class="text-primary flex-shrink-0 mt-1 mi-md"></i>
                    <div class="min-w-0">
                        <p class="small text-body-secondary mb-1">ครั้งล่าสุด</p>
                        <p class="fw-semibold mb-0 small"><?= Html::encode($lastCheckin->getCheckTypeLabel()) ?> · <?= Html::encode($lastCheckin->checkin_at) ?></p>
                        <p class="small text-body-secondary mb-0"><?= Html::encode($lastCheckin->getStatusLabel()) ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3">
            <p class="small fw-semibold text-body-secondary mb-2">เลือกประเภท</p>
            <div class="row g-2">
                <div class="col-6">
                    <button type="button" class="btn check-type-btn w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-1 border border-2 border-success bg-success bg-opacity-10" data-check-type="in">
                        <i data-lucide="log-in" style="width: 1.75rem; height: 1.75rem;" class="text-success"></i>
                        <span class="fw-semibold small">เข้างาน</span>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn check-type-btn w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-1 border" data-check-type="out">
                        <i data-lucide="log-out" style="width: 1.75rem; height: 1.75rem;" class="text-secondary"></i>
                        <span class="fw-semibold small">ออกงาน</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-3 p-3 rounded-3 bg-primary bg-opacity-10 mb-3">
                <i data-lucide="clock" class="text-primary flex-shrink-0" style="width: 2rem; height: 2rem;"></i>
                <div>
                    <span class="small text-body-secondary d-block">เวลาที่จะบันทึก</span>
                    <span id="live-clock" class="fw-bold fs-2">--:--:--</span>
                    <span id="live-date" class="small text-body-secondary d-block"></span>
                </div>
            </div>
            <input type="hidden" id="lat" name="lat">
            <input type="hidden" id="lng" name="lng">
            <input type="hidden" id="check_type" name="check_type" value="in">
            <p class="small text-body-secondary mb-2">
                <i data-lucide="map-pin" style="width: 1rem; height: 1rem; vertical-align: -0.15em;"></i>
                <span id="coord-display">กำลังตรวจสอบสิทธิ์ตำแหน่ง...</span>
            </p>
            <div id="location-permission-wrap" class="d-none border rounded-3 p-3 bg-primary bg-opacity-10 mb-3" role="region" aria-label="ขออนุญาตตำแหน่ง">
                <p class="small fw-semibold mb-1" id="location-permission-title">ขอใช้ตำแหน่งเพื่อลงเวลา</p>
                <p class="small text-body-secondary mb-0" id="location-permission-text"></p>
                <button type="button" id="btn-allow-location" class="btn btn-primary w-100 rounded-3 mt-3 fw-semibold">
                    อนุญาตใช้ตำแหน่ง
                </button>
                <p class="small text-muted mb-0 mt-2 d-none" id="location-permission-extra"></p>
            </div>
            <div id="geofence-status" class="alert alert-light border small py-2 mb-3 d-none" role="status"></div>
            <button type="button" id="btn-checkin" class="btn btn-primary w-100 py-3 rounded-3 fw-semibold">
                <span id="btn-checkin-label">ลงเวลาเข้า</span>
            </button>
        </div>
    </div>

    <div id="checkin-result" class="alert rounded-3 mb-0 d-none" role="alert"></div>

    <p class="small text-body-secondary text-center mb-2 px-2">หลังบันทึกแล้วรอหัวหน้าอนุมัติตามกระบวนการขององค์กร</p>

    <!-- ────────── ประวัติบันทึกเวลา (14 วันล่าสุด) ────────── -->
    <?php
    $history = $history ?? [];
    // group by date (Y-m-d), value = list of records
    $grouped = [];
    foreach ($history as $rec) {
        $ts = strtotime((string) $rec->checkin_at);
        if ($ts === false) continue;
        $dateKey = date('Y-m-d', $ts);
        $grouped[$dateKey][] = $rec;
    }
    krsort($grouped);
    ?>
    <section class="att-history" aria-labelledby="att-history-title">
        <header class="att-history-head">
            <h2 class="att-history-title" id="att-history-title">
                <i data-lucide="history" aria-hidden="true"></i>
                ประวัติบันทึกเวลา
            </h2>
            <span class="att-history-sub">14 วันล่าสุด</span>
        </header>

        <?php if (empty($grouped)): ?>
            <div class="att-history-empty">
                <i data-lucide="calendar-x" aria-hidden="true"></i>
                <p class="mb-0 mt-2">ยังไม่มีบันทึกในช่วง 14 วันที่ผ่านมา</p>
            </div>
        <?php else: ?>
            <ol class="att-day-list" aria-label="ประวัติรายวัน">
                <?php $dayIdx = 0; foreach ($grouped as $dateKey => $records):
                    $dayIdx++;
                    try { $dateLabel = ThaiDateHelper::formatThaiDate($dateKey, 'long'); }
                    catch (\Throwable $e) { $dateLabel = $dateKey; }
                    $dayOfWeek = ['อา','จ','อ','พ','พฤ','ศ','ส'][(int) date('w', strtotime($dateKey))];
                ?>
                    <li class="att-day" style="--att-i: <?= $dayIdx ?>">
                        <header class="att-day-head">
                            <div class="att-day-date">
                                <span class="att-day-dow"><?= Html::encode($dayOfWeek) ?></span>
                                <span class="att-day-num"><?= (int) date('j', strtotime($dateKey)) ?></span>
                            </div>
                            <div class="att-day-label"><?= Html::encode($dateLabel) ?></div>
                        </header>
                        <ul class="att-record-list">
                            <?php foreach ($records as $rec):
                                $ts = strtotime((string) $rec->checkin_at);
                                $time = $ts !== false ? date('H:i', $ts) : '-';
                                $isIn = ($rec->check_type ?? '') === CheckinRecord::CHECK_TYPE_IN;
                                $statusVal = (string) ($rec->status ?? '');
                                $statusTone = 'secondary';
                                if ($statusVal === CheckinRecord::STATUS_APPROVED)      $statusTone = 'success';
                                elseif ($statusVal === CheckinRecord::STATUS_REJECTED)  $statusTone = 'danger';
                                elseif ($statusVal === CheckinRecord::STATUS_PENDING)   $statusTone = 'warning';
                                $methodLbl = $rec->getMethodLabel();
                            ?>
                                <li class="att-record" data-type="<?= $isIn ? 'in' : 'out' ?>">
                                    <span class="att-record-icon" aria-hidden="true">
                                        <i data-lucide="<?= $isIn ? 'log-in' : 'log-out' ?>"></i>
                                    </span>
                                    <div class="att-record-meta">
                                        <div class="att-record-row">
                                            <span class="att-record-type"><?= $isIn ? 'เข้างาน' : 'ออกงาน' ?></span>
                                            <span class="att-record-time"><?= Html::encode($time) ?></span>
                                        </div>
                                        <div class="att-record-sub">
                                            <span class="att-record-method"><?= Html::encode($methodLbl) ?></span>
                                            <?php if ((int) ($rec->is_in_location ?? 0) === 0 && !empty($rec->location_id)): ?>
                                                <span class="att-record-flag">นอกพื้นที่</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="att-record-pill" data-tone="<?= Html::encode($statusTone) ?>">
                                        <?= Html::encode($rec->getStatusLabel()) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </section>
</div>

</div>

<style>
.att-history { margin-top: var(--space-md); }
.att-history-head {
    display: flex; align-items: baseline; justify-content: space-between;
    gap: var(--space-sm); padding: 0 var(--space-2xs);
    margin-bottom: var(--space-sm);
}
.att-history-title {
    margin: 0; font-size: var(--fs-lg); font-weight: 800; color: var(--ink);
    line-height: 1.2; display: inline-flex; align-items: center; gap: var(--space-2xs);
}
.att-history-title svg { width: 1.125rem; height: 1.125rem; color: var(--mobile-primary); }
.att-history-sub { font-size: var(--fs-xs); color: var(--ink-4); font-weight: 600; }

.att-history-empty {
    text-align: center; padding: var(--space-xl) var(--space-md);
    background: var(--surface); border-radius: 16px;
    color: var(--ink-3); font-size: var(--fs-sm);
    box-shadow: var(--shadow-sm);
}
.att-history-empty svg { width: 2rem; height: 2rem; color: var(--ink-4); }

.att-day-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: var(--space-md); }
.att-day {
    background: var(--surface);
    border-radius: 16px;
    padding: var(--space-md);
    box-shadow: var(--shadow-sm);
    opacity: 0; transform: translateY(6px);
    animation: att-day-in 320ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
    animation-delay: calc(var(--att-i, 1) * 50ms);
}
@keyframes att-day-in {
    to { opacity: 1; transform: translateY(0); }
}
.att-day-head {
    display: flex; align-items: center; gap: var(--space-sm);
    padding-bottom: var(--space-xs); margin-bottom: var(--space-sm);
    border-bottom: 1px solid var(--ink-line);
}
.att-day-date {
    width: 2.75rem; height: 2.75rem;
    border-radius: 12px;
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    flex-shrink: 0;
    line-height: 1;
}
.att-day-dow { font-size: 0.625rem; font-weight: 700; letter-spacing: 0.02em; }
.att-day-num { font-size: 1.125rem; font-weight: 800; font-variant-numeric: tabular-nums; }
.att-day-label { font-size: var(--fs-sm); font-weight: 700; color: var(--ink); }

.att-record-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: var(--space-xs); }
.att-record {
    display: grid; grid-template-columns: auto 1fr auto;
    align-items: center; gap: var(--space-sm);
    padding: var(--space-xs) var(--space-sm);
    border-radius: 12px;
    background: var(--surface-2);
}
.att-record-icon {
    width: 2rem; height: 2rem; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.att-record-icon svg { width: 1rem; height: 1rem; }
.att-record[data-type="in"]  .att-record-icon { background: var(--success-soft); color: var(--success); }
.att-record[data-type="out"] .att-record-icon { background: color-mix(in oklch, var(--ink-4) 18%, transparent); color: var(--ink-3); }

.att-record-meta { min-width: 0; }
.att-record-row { display: flex; align-items: baseline; gap: var(--space-xs); }
.att-record-type { font-size: var(--fs-sm); font-weight: 700; color: var(--ink); }
.att-record-time { font-size: var(--fs-md); font-weight: 800; color: var(--ink); font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }
.att-record-sub { display: flex; flex-wrap: wrap; gap: var(--space-2xs) var(--space-xs); margin-top: 2px; font-size: var(--fs-xs); color: var(--ink-3); }
.att-record-flag { color: var(--warning); font-weight: 700; }
.att-record-pill {
    flex-shrink: 0; padding: 3px 10px; border-radius: 999px;
    font-size: var(--fs-2xs); font-weight: 700; line-height: 1.4;
}
.att-record-pill[data-tone="success"]   { background: var(--success-soft);  color: var(--success); }
.att-record-pill[data-tone="warning"]   { background: var(--warning-soft);  color: var(--warning); }
.att-record-pill[data-tone="danger"]    { background: var(--danger-soft);   color: var(--danger-strong); }
.att-record-pill[data-tone="secondary"] { background: color-mix(in oklch, var(--ink-4) 15%, transparent); color: var(--ink-3); }

@media (prefers-reduced-motion: reduce) {
    .att-day { animation: none !important; opacity: 1 !important; transform: none !important; }
}
</style>

<?php
$this->registerJs(<<<JS
(function(){
    var saveUrl = $saveUrlJs;
    var csrfParam = $csrfParamJs;
    var csrfToken = $csrfTokenJs;
    var geofences = $geofencesJson;
    var currentMethod = 'manual';
    var currentCheckType = 'in';
    var lat = null, lng = null;

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
        var \$box = \$('#geofence-status');
        if (!geofences || !geofences.length) {
            \$box.addClass('d-none').removeClass('alert-success alert-warning alert-danger').text('');
            return;
        }
        if (la == null || ln == null) {
            \$box.removeClass('d-none alert-success alert-warning').addClass('alert-danger')
                .html('ต้องได้รับพิกัด GPS ก่อนลงเวลา (องค์กรกำหนดบริเวณ)');
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
                .text('อยู่ในบริเวณ: ' + inside.name + ' (รัศมี ' + inside.radius_m + ' ม.)');
        } else if (nearest) {
            \$box.removeClass('d-none alert-success alert-danger').addClass('alert-warning')
                .text('ยังไม่อยู่ในรัศมี — ห่างจาก ' + nearest.name + ' ~' + Math.round(nearestD) + ' ม.');
        } else {
            \$box.addClass('d-none').text('');
        }
    }

    function updateCheckTypeUI() {
        \$('.check-type-btn').removeClass('border-2 border-success border-secondary bg-success bg-opacity-10 bg-secondary bg-opacity-10').addClass('border');
        var \$sel = \$('.check-type-btn[data-check-type="' + currentCheckType + '"]');
        \$sel.removeClass('border');
        if (currentCheckType === 'in') {
            \$sel.addClass('border-2 border-success bg-success bg-opacity-10');
        } else {
            \$sel.addClass('border-2 border-secondary bg-secondary bg-opacity-10');
        }
        \$('#btn-checkin-label').text(currentCheckType === 'in' ? 'ลงเวลาเข้า' : 'ลงเวลาออก');
        var \$btn = \$('#btn-checkin');
        \$btn.removeClass('btn-secondary btn-primary');
        if (currentCheckType === 'out') {
            \$btn.addClass('btn-secondary');
        } else {
            \$btn.addClass('btn-primary');
        }
    }

    \$('.check-type-btn').on('click', function() {
        currentCheckType = \$(this).data('check-type');
        \$('#check_type').val(currentCheckType);
        updateCheckTypeUI();
    });
    updateCheckTypeUI();

    function updateLiveClock() {
        var now = new Date();
        var h = String(now.getHours()).padStart(2, '0');
        var m = String(now.getMinutes()).padStart(2, '0');
        var s = String(now.getSeconds()).padStart(2, '0');
        \$('#live-clock').text(h + ':' + m + ':' + s);
        var d = now.getDate(), mo = now.getMonth() + 1, y = now.getFullYear() + 543;
        \$('#live-date').text(d + '/' + mo + '/' + y);
    }
    updateLiveClock();
    setInterval(updateLiveClock, 1000);

    function setCoord(la, ln) {
        lat = la; lng = ln;
        \$('#lat').val(la || '');
        \$('#lng').val(ln || '');
        if (la != null && ln != null) {
            \$('#coord-display').text('พิกัด: ' + la.toFixed(5) + ', ' + ln.toFixed(5));
        } else {
            \$('#coord-display').text('ยังไม่มีพิกัด — กดปุ่มด้านล่างเพื่ออนุญาตตำแหน่ง');
        }
        refreshGeofenceUI(la, ln);
    }

    function showLocationPrompt(show, text, extra) {
        var \$w = \$('#location-permission-wrap');
        var \$ex = \$('#location-permission-extra');
        if (show) {
            \$w.removeClass('d-none');
            \$('#location-permission-text').text(text || '');
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
        var \$btn = \$('#btn-allow-location');
        \$btn.prop('disabled', true);
        \$('#coord-display').text('กำลังขอตำแหน่ง...');
        if (!navigator.geolocation) {
            setCoord(null, null);
            showLocationPrompt(true,
                'อุปกรณ์หรือเบราว์เซอร์นี้ไม่รองรับการระบุตำแหน่ง',
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

    \$('#btn-allow-location').on('click', function() { fetchLocation(); });

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

    \$('#btn-checkin').on('click', function() {
        var \$btn = \$(this);
        \$btn.prop('disabled', true);
        var data = {
            method: currentMethod,
            check_type: \$('#check_type').val() || 'in',
            lat: \$('#lat').val() || null,
            lng: \$('#lng').val() || null,
            qr_token: null,
            photo_path: null
        };
        data[csrfParam] = csrfToken;
        \$.post(saveUrl, data).then(function(res) {
            var \$res = \$('#checkin-result');
            \$res.removeClass('d-none alert-danger alert-success').html('');
            if (res.success) {
                \$res.addClass('alert-success').text(res.message || 'บันทึกสำเร็จ');
            } else {
                \$res.addClass('alert-danger').text(res.message || 'เกิดข้อผิดพลาด');
            }
        }).fail(function() {
            \$('#checkin-result').removeClass('d-none alert-success').addClass('alert-danger')
                .text('เชื่อมต่อไม่สำเร็จ');
        }).always(function() {
            \$btn.prop('disabled', false);
        });
    });
})();
JS
);
?>
