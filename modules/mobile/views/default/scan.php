<?php
/** @var yii\web\View $this */
/** @var string $current_page */
$this->params['current_page']   = $current_page ?? 'scan';
$this->params['mobileTitle']    = 'สแกน QR Code';
$this->params['mobileSubtitle'] = 'สแกนเพื่อเปิดข้อมูลในระบบ';

$scanReturn = (string) Yii::$app->request->get('return', '');
$isMaintenanceReturn = $scanReturn === 'maintenance';
$hintText = $isMaintenanceReturn
    ? 'สแกน QR Code ของครุภัณฑ์เพื่อแนบหมายเลขในใบแจ้งซ่อม'
    : 'สแกน QR Code ของทรัพย์สิน หรือการยืม เพื่อเปิดดูข้อมูลในระบบ';
?>
<style>
.scan-page-header { padding: 0.75rem 0; }
.scan-scanner-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0, 105, 255, 0.12);
    overflow: hidden;
    background: #1a1d24;
    margin-bottom: 1rem;
}
.scan-scanner-wrap {
    position: relative;
    width: 100%;
    min-height: 280px;
}
.scan-scanner-wrap #qr-reader {
    width: 100% !important;
    border: none !important;
}
.scan-scanner-wrap #qr-reader__scan_region {
    background: #000 !important;
}
.scan-scanner-wrap #qr-reader__dashboard {
    background: #1a1d24 !important;
    border-top: 1px solid rgba(255,255,255,0.08) !important;
}
.scan-scanner-wrap video {
    border-radius: 12px;
}
.scan-line {
    position: absolute;
    left: 50%;
    top: 0;
    transform: translateX(-50%);
    width: 80%;
    max-width: 220px;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--mobile-primary, #0d6efd), transparent);
    border-radius: 2px;
    box-shadow: 0 0 12px rgba(13, 110, 253, 0.5);
    animation: scan-line-move 2s ease-in-out infinite;
    pointer-events: none;
    z-index: 10;
}
@keyframes scan-line-move {
    0%, 100% { top: 15%; opacity: 1; }
    50% { top: 85%; opacity: 0.9; }
}
.scan-loading {
    position: absolute;
    inset: 0;
    background: rgba(26, 29, 36, 0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    z-index: 5;
    border-radius: 16px;
}
.scan-loading.spin .scan-loading-spinner {
    animation: scan-spin 0.8s linear infinite;
}
@keyframes scan-spin {
    to { transform: rotate(360deg); }
}
.scan-loading-spinner {
    width: 2.5rem;
    height: 2.5rem;
    border: 3px solid rgba(13, 110, 253, 0.25);
    border-top-color: var(--mobile-primary, #0d6efd);
    border-radius: 50%;
}
.scan-loading-text { color: #adb5bd; font-size: 0.875rem; margin: 0; }
.scan-error-msg {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 12px;
    color: var(--danger);
    padding: 1rem;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    display: none;
}
.scan-error-msg.show { display: block; }
.scan-hint-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.scan-hint-card .card-body { padding: 1rem; }
</style>

<div class="card scan-scanner-card">
    <div class="scan-scanner-wrap">
        <div class="scan-loading spin" id="scan-loading">
            <div class="scan-loading-spinner"></div>
            <p class="scan-loading-text">กำลังเปิดกล้อง...</p>
        </div>
        <div class="scan-line" id="scan-line" aria-hidden="true"></div>
        <div id="qr-reader" style="min-height: 260px;"></div>
    </div>
</div>

<div class="scan-error-msg" id="scan-error-msg" role="alert"></div>

<div class="card scan-hint-card">
    <div class="card-body">
        <p class="small text-body-secondary mb-0">
            <i data-lucide="info" class="me-1" style="width: 1rem; height: 1rem; vertical-align: -0.15em;"></i>
            <?= \yii\helpers\Html::encode($hintText) ?>
        </p>
    </div>
</div>

<?php
$assetUrlById          = \yii\helpers\Url::to(['/mobile/default/asset', 'id' => '__ID__']);
$assetUrlByCode        = \yii\helpers\Url::to(['/mobile/default/asset', 'code' => '__CODE__']);
// scan QR แล้วไปฟอร์มแจ้งซ่อม (wizard) พร้อม prefill asset_number จากรหัสที่สแกนได้
$maintenanceUrlById    = \yii\helpers\Url::to(['/mobile/default/repair-request', 'asset_number' => '__CODE__', 'send_type' => 'asset']);
$maintenanceUrlByCode  = \yii\helpers\Url::to(['/mobile/default/repair-request', 'asset_number' => '__CODE__', 'send_type' => 'asset']);
?>
<script src="<?= \yii\helpers\Url::to('@web/libs/html5-qrcode/html5-qrcode.min.js') ?>"></script><?php // self-hosted (เดิม jsdelivr) ?>
<script>
(function() {
    var loadingEl = document.getElementById('scan-loading');
    var errorEl = document.getElementById('scan-error-msg');
    var scanLineEl = document.getElementById('scan-line');
    var readerId = 'qr-reader';
    var html5QrCode = null;
    var lastRedirect = '';
    var scanReturn = <?= json_encode($scanReturn) ?>;
    var assetUrlById = <?= json_encode($assetUrlById) ?>;
    var assetUrlByCode = <?= json_encode($assetUrlByCode) ?>;
    var maintenanceUrlById = <?= json_encode($maintenanceUrlById) ?>;
    var maintenanceUrlByCode = <?= json_encode($maintenanceUrlByCode) ?>;

    function hideLoading() {
        if (loadingEl) loadingEl.style.display = 'none';
    }
    function showError(msg) {
        if (errorEl) {
            errorEl.textContent = msg;
            errorEl.classList.add('show');
        }
    }
    function hideError() {
        if (errorEl) errorEl.classList.remove('show');
    }
    function vibrate() {
        if (navigator.vibrate) navigator.vibrate(100);
    }
    function routeAssetById(id) {
        if (scanReturn === 'maintenance') {
            // wizard แจ้งซ่อมรับเฉพาะ asset_number (string) ไม่ใช่ asset.id ตัวเลข
            // กรณีสแกน QR /q/asset/<id> ขณะอยู่โหมดแจ้งซ่อม → fallback ส่ง id ไปเป็น code
            // (controller จะลองหา Asset by code ก่อน แล้วยังเปิดฟอร์มได้แม้ไม่เจอ)
            return maintenanceUrlById ? maintenanceUrlById.replace('__CODE__', encodeURIComponent(id)) : null;
        }
        return assetUrlById ? assetUrlById.replace('__ID__', encodeURIComponent(id)) : null;
    }
    function routeAssetByCode(code) {
        var template = scanReturn === 'maintenance' ? maintenanceUrlByCode : assetUrlByCode;
        return template ? template.replace('__CODE__', encodeURIComponent(code)) : null;
    }
    function extractPath(raw) {
        try {
            return new URL(raw, window.location.origin).pathname;
        } catch (e) {
            var t = raw.indexOf('/') !== 0 ? '/' + raw : raw;
            return t.replace(/\?.*$/, '');
        }
    }
    /**
     * แปลงข้อความจาก QR เป็น URL สำหรับ redirect
     * - /q/asset/{id} → หน้าครุภัณฑ์ด้วย id
     * - ข้อความธรรมดา (รหัสครุภัณฑ์) → หน้าครุภัณฑ์ด้วย code (สแกนสติกเกอร์ QR บนครุภัณฑ์)
     * - URL ภายนอก → ใช้ตามนั้น
     * - return=maintenance → กลับไปฟอร์มแจ้งซ่อมพร้อม asset/asset_code
     */
    function toRedirectUrl(text) {
        var raw = (text || '').trim();
        if (!raw) return null;
        var path = extractPath(raw);
        var routePath = raw.indexOf('/') !== 0 ? '/' + raw : raw;
        routePath = routePath.replace(/\?.*$/, '');
        var idMatch = path.match(/^\/q\/asset\/([^\/]+)/);
        if (idMatch) return routeAssetById(idMatch[1]);
        if (/^https?:\/\//i.test(raw)) return raw;
        if (path.match(/^\/q\/document\//)) return routePath;
        if (path.match(/^\/q\/stock\//)) return routePath;
        // รหัสครุภัณฑ์โดยตรง (จากสติกเกอร์ QR ของระบบ) → เปิดหน้าครุภัณฑ์ด้วย code
        if (raw.length <= 80 && raw.indexOf(' ') < 0) return routeAssetByCode(raw);
        return null;
    }
    function redirectTo(url) {
        if (lastRedirect === url) return;
        lastRedirect = url;
        vibrate();
        window.location.href = url;
    }

    function onScanSuccess(decodedText, decodedResult) {
        var url = toRedirectUrl(decodedText);
        if (url) redirectTo(url);
    }

    function startScanner() {
        if (typeof Html5Qrcode === 'undefined') {
            showError('ไม่สามารถโหลดตัวสแกนได้ กรุณารีเฟรชหน้า');
            hideLoading();
            return;
        }
        html5QrCode = new Html5Qrcode(readerId);
        var config = {
            fps: 10,
            qrbox: { width: 220, height: 220 },
            aspectRatio: 1
        };
        html5QrCode.start(
            { facingMode: 'environment' },
            config,
            onScanSuccess,
            function() {}
        ).then(function() {
            hideLoading();
            hideError();
        }).catch(function(err) {
            hideLoading();
            var msg = 'ไม่สามารถเปิดกล้องได้';
            if (err && err.name === 'NotAllowedError') {
                msg = 'กรุณาอนุญาตการเข้าถึงกล้องในเบราว์เซอร์เพื่อสแกน QR Code';
            } else if (err && err.message) {
                msg = err.message;
            }
            showError(msg);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startScanner);
    } else {
        startScanner();
    }

    window.addEventListener('beforeunload', function() {
        if (html5QrCode && html5QrCode.isScanning) {
            try { html5QrCode.stop(); } catch (e) {}
        }
    });
})();
</script>
