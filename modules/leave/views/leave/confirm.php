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
                        <div class="rounded-circle overflow-hidden border border-2 border-primary flex-shrink-0" style="width: 64px; height: 64px;">
                            <?= $employee ? $employee->getAvatar(false) : '' ?>
                        </div>
                        <div>
                            <div class="fw-bold text-body fs-6"><?= Html::encode($name) ?></div>
                            <div class="small text-muted"><?= Html::encode($positionName) ?></div>
                            <div class="small text-primary">☎ <?= Html::encode($phone) ?></div>
                        </div>
                    </div>
                    <table class="table table-borderless small mb-0">
                        <tr>
                            <td class="text-muted" style="width: 100px;">ประเภท</td>
                            <td><?= Html::encode($typeTitle) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">จำนวนวัน</td>
                            <td><?= (int) $draft['total_days'] ?> วัน</td>
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
                            <td class="text-muted">ที่อยู่</td>
                            <td><?= Html::encode($draft['address'] ?? '-') ?></td>
                        </tr>
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
                            <button type="button" class="nav-link active rounded-3" data-tab="live">เซ็นสด</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link rounded-3" data-tab="system">ลายเซ็นระบบ</button>
                        </li>
                    </ul>
                    <div class="border border-2 border-secondary border-dashed rounded-3 p-3 mb-2 position-relative">
                        <button type="button" class="btn btn-link btn-sm text-danger position-absolute top-0 end-0 p-1" id="clear-signature" title="ล้าง"><i class="bi bi-trash"></i></button>
                        <div class="bg-white rounded-3 border position-relative" id="signature-pad-wrap" style="min-height: 200px;">
                            <canvas id="signature-canvas" width="500" height="250" style="width: 100%; max-width: 500px; height: auto; max-height: 250px; touch-action: none;"></canvas>
                        </div>
                        <p class="small text-muted mb-0 mt-2">เซ็นชื่อในกรอบสีขาว (500x250px)</p>
                    </div>

                    <form id="leave-confirm-form" action="<?= Url::to(['/leave/leave/save']) ?>" method="post" class="mt-4">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                        <input type="hidden" name="signature_data" id="signature-data" value="">
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
    var drawing = false;

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
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (input) input.value = '';
    });

    document.getElementById('leave-confirm-form').addEventListener('submit', function(){
        updateData();
    });
})();
JS
, \yii\web\View::POS_END);
?>
