<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

$this->title = 'สร้างใบลาใหม่';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$name = $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : '';
$positionName = $employee && $employee->positionType ? $employee->positionType->title : '';
$phone = $employee->phone ?? '';
$typeTitle = $leaveType ? $leaveType->title : $draft['leave_type_id'];
$signatureSystemUrl = $signatureSystemUrl ?? null;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <a href="<?= Url::to(['/leave/leave/create']) ?>" class="btn btn-link btn-sm text-body p-0"><i class="bi bi-arrow-left fs-4"></i></a>
    <h4 class="fw-bold text-body mb-0">สร้างใบลาใหม่</h4>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <div class="row g-4">
        <!-- ซ้าย: ตรวจสอบข้อมูล -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-3">
                        <span class="rounded bg-primary bg-opacity-25 text-primary p-2"><i class="bi bi-file-text"></i></span>
                        ตรวจสอบข้อมูล
                    </h6>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle overflow-hidden border border-2 border-primary bg-body-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 64px; height: 64px;">
                            <?php
                            if ($employee && method_exists($employee, 'ShowAvatar')) {
                                $photoUrl = $employee->ShowAvatar();
                                echo Html::img($photoUrl, [
                                    'alt' => Html::encode($name),
                                    'class' => 'w-100 h-100',
                                    'style' => 'object-fit: cover;',
                                ]);
                            } else {
                                echo '<i class="bi bi-person fs-1 text-muted"></i>';
                            }
                            ?>
                        </div>
                        <div>
                            <div class="fw-bold text-body fs-6"><?= Html::encode($name) ?></div>
                            <div class="small text-muted"><?= Html::encode($positionName) ?></div>
                            <div class="small text-primary">☎ <?= Html::encode($phone) ?></div>
                        </div>
                    </div>
                    <?php
                    $leaveTimeLabel = (isset($draft['leave_time_type']) && (float) $draft['leave_time_type'] === 0.5) ? 'ครึ่งวัน' : 'การลาเต็มวัน';
                    $totalDaysDisplay = (float) ($draft['total_days'] ?? 0);
                    $calDays = (int) ($draft['summary_calendar_days'] ?? 0);
                    $summarySatSun = (int) ($draft['summary_sat_sun'] ?? 0);
                    $summaryHoliday = (int) ($draft['summary_holiday'] ?? 0);
                    ?>
                    <div class="card border-0 border-start border-3 border-info rounded-4 overflow-hidden mb-4">
                        <div class="card-body py-3 px-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="p-2 bg-info bg-opacity-10 rounded-circle text-info"><i class="bi bi-calendar3 fs-5"></i></div>
                                <h6 class="fw-bold mb-0 text-body">สรุปวันลา</h6>
                            </div>
                            <div class="row g-2 small">
                                <div class="col-6 col-md-3 text-center">
                                    <div class="text-muted mb-1">รวมระยะเวลา</div>
                                    <div class="fw-semibold text-body"><?= $calDays ?> วัน</div>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <div class="text-muted mb-1">วันเสาร์-อาทิตย์</div>
                                    <div class="fw-semibold text-body"><?= $summarySatSun ?> วัน</div>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <div class="text-muted mb-1">วันหยุดนักขัตฤกษ์</div>
                                    <div class="fw-semibold text-body"><?= $summaryHoliday ?> วัน</div>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <div class="text-muted mb-1">รวมวันลา</div>
                                    <div class="fw-semibold text-primary"><?= $totalDaysDisplay == (int) $totalDaysDisplay ? (int) $totalDaysDisplay : number_format($totalDaysDisplay, 1) ?> วัน</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-borderless small mb-0">
                        <tr>
                            <td class="text-muted" style="width: 100px;">ประเภท</td>
                            <td><?= Html::encode($typeTitle) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">ลักษณะการลา</td>
                            <td><?= Html::encode($leaveTimeLabel) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">จำนวนวัน</td>
                            <td><?= $totalDaysDisplay == (int) $totalDaysDisplay ? (int) $totalDaysDisplay : number_format($totalDaysDisplay, 1) ?> วัน</td>
                        </tr>
                        <tr>
                            <td class="text-muted">ช่วงเวลา</td>
                            <td><?= Html::encode($draft['date_start'] ?? '') ?> - <?= Html::encode($draft['date_end'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">เหตุผล</td>
                            <td><?= Html::encode($draft['reason'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">เบอร์โทรติดต่อ</td>
                            <td><?= Html::encode($draft['contact_phone'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">สถานที่ไป</td>
                            <td><?= Html::encode($draft['place_go'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">ที่อยู่</td>
                            <td><?= Html::encode($draft['address'] ?? '-') ?></td>
                        </tr>
                        <?php if (!empty($draft['attachment_info'])): ?>
                        <tr>
                            <td class="text-muted">เอกสารแนบ</td>
                            <td>
                                <ul class="list-unstyled small mb-0">
                                    <?php foreach ($draft['attachment_info'] as $att): ?>
                                    <li><i class="bi bi-paperclip me-1"></i><?= Html::encode($att['file_name'] ?? '') ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    <div class="mt-4">
                        <a href="<?= Url::to(['/leave/leave/create']) ?>" class="btn btn-light border rounded-3">กลับไปแก้ไข</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ขวา: ลงลายมือชื่อ -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-3">
                        <span class="rounded bg-success bg-opacity-25 text-success p-2"><i class="bi bi-pen"></i></span>
                        ลงลายมือชื่อ
                    </h6>
                    <ul class="nav nav-pills nav-fill gap-2 mb-3" id="signature-tabs">
                        <li class="nav-item">
                            <button type="button" class="nav-link active rounded-3" data-tab="live" id="tab-live">เซ็นสด</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link rounded-3" data-tab="system" id="tab-system" <?= $signatureSystemUrl ? '' : 'disabled' ?>><?= $signatureSystemUrl ? 'ลายเซ็นระบบ' : 'ลายเซ็นระบบ (ยังไม่มีลายเซ็น)' ?></button>
                        </li>
                    </ul>
                    <div class="border border-2 border-secondary border-dashed rounded-3 p-3 mb-2 position-relative" id="signature-area">
                        <button type="button" class="btn btn-link btn-sm text-danger position-absolute top-0 end-0 p-1" id="clear-signature" title="ล้าง"><i class="bi bi-trash"></i></button>
                        <div id="signature-live-wrap">
                            <div class="bg-white rounded-3 border position-relative" id="signature-pad-wrap" style="min-height: 200px;">
                                <canvas id="signature-canvas" width="500" height="250" style="width: 100%; max-width: 500px; height: auto; max-height: 250px; touch-action: none;"></canvas>
                            </div>
                            <p class="small text-muted mb-0 mt-2">เซ็นชื่อในกรอบสีขาว (500x250px)</p>
                        </div>
                        <div id="signature-system-wrap" class="d-none">
                            <div class="bg-white rounded-3 border p-2 text-center">
                                <img id="signature-system-img" src="<?= $signatureSystemUrl ? Html::encode($signatureSystemUrl) : '' ?>" alt="ลายเซ็นระบบ" class="img-fluid mw-100">
                            </div>
                            <p class="small text-muted mb-0 mt-2">ใช้ลายเซ็นที่บันทึกในระบบ</p>
                        </div>
                    </div>

                    <form id="leave-confirm-form" action="<?= Url::to(['/leave/leave/save']) ?>" method="post" class="mt-4">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <input type="hidden" name="signature_data" id="signature-data" value="">
                        <input type="hidden" name="signature_type" id="signature-type" value="canvas">
                        <button type="submit" class="btn btn-success rounded-3 w-100 py-3 fw-bold">
                            <i class="bi bi-check-circle me-2"></i>ยืนยันและสร้างใบลา
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function(){
    var canvas = document.getElementById('signature-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var wrap = document.getElementById('signature-pad-wrap');
    var input = document.getElementById('signature-data');
    var inputType = document.getElementById('signature-type');
    var liveWrap = document.getElementById('signature-live-wrap');
    var systemWrap = document.getElementById('signature-system-wrap');
    var tabLive = document.getElementById('tab-live');
    var tabSystem = document.getElementById('tab-system');
    var drawing = false;

    function setTab(tab) {
        var isLive = (tab === 'live');
        if (liveWrap) liveWrap.classList.toggle('d-none', !isLive);
        if (systemWrap) systemWrap.classList.toggle('d-none', isLive);
        if (inputType) inputType.value = isLive ? 'canvas' : 'system';
        if (input && !isLive) input.value = '';
        if (tabLive) { tabLive.classList.toggle('active', isLive); }
        if (tabSystem) { tabSystem.classList.toggle('active', !isLive); }
    }
    if (tabLive) tabLive.addEventListener('click', function(){ setTab('live'); });
    if (tabSystem && !tabSystem.disabled) tabSystem.addEventListener('click', function(){ setTab('system'); });

    function resize() {
        var w = wrap ? wrap.offsetWidth : 500;
        canvas.width = Math.min(500, w);
        canvas.height = 250;
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
    }
    resize();
    if (window.addEventListener) window.addEventListener('resize', resize);

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var touch = e.touches ? e.touches[0] : e;
        var scaleX = canvas.width / rect.width, scaleY = canvas.height / rect.height;
        return { x: (touch.clientX - rect.left) * scaleX, y: (touch.clientY - rect.top) * scaleY };
    }
    function start(e) { e.preventDefault(); drawing = true; var p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }
    function move(e) { e.preventDefault(); if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }
    function end(e) { e.preventDefault(); drawing = false; updateData(); }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end, { passive: false });

    function updateData() {
        if (input) input.value = canvas.toDataURL('image/png');
    }
    document.getElementById('clear-signature').addEventListener('click', function(){
        if (inputType && inputType.value === 'canvas') {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (input) input.value = '';
        }
    });

    document.getElementById('leave-confirm-form').addEventListener('submit', function(){
        if (inputType && inputType.value === 'canvas') updateData();
    });
})();
JS
, \yii\web\View::POS_END);
?>
