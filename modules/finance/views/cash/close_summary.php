<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $year ปีงบ พ.ศ. */
/** @var int $gy ค.ศ. */
/** @var string $start @var string $end */
/** @var array $closedSet map 'Y-m-d'=>x */
/** @var array $activeSet map 'Y-m-d'=>x */
/** @var int $closedCount @var int $pendingCount */

$this->title = 'สรุปการปิดบัญชีประจำวัน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-calendar-check" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ภาพรวมทั้งปีงบ — คลิกจุดวันเพื่อดูสรุป/ยกเลิกปิด<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$dows = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
$mAbbr = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$startTs = strtotime($start);
$endTs = strtotime($end);
$gridStartTs = $startTs - (int) date('w', $startTs) * 86400;
$today = date('Y-m-d');

// สร้างตาราง [col][row] = date | null + ป้ายเดือน
$cols = [];
$monthLabel = [];
$ts = $gridStartTs;
$c = 0;
while ($ts <= $endTs) {
    for ($r = 0; $r < 7; $r++) {
        $d = date('Y-m-d', $ts);
        $inRange = ($ts >= $startTs && $ts <= $endTs);
        $cols[$c][$r] = $inRange ? $d : null;
        if ($inRange && (int) date('j', $ts) === 1) {
            $monthLabel[$c] = $mAbbr[(int) date('n', $ts)];
        }
        $ts += 86400;
    }
    $c++;
}
$daySummaryUrl = Url::to(['day-summary']);
$excelUrl = Url::to(['close-excel']);
$activeTotal = count($activeSet);
?>

<style>
.fc-hm{width:14px;height:14px;border-radius:3px;margin:1px;border:1px solid rgba(0,0,0,.06)}
.fc-hm.s-closed{background:#22c55e;cursor:pointer}
.fc-hm.s-pending{background:#f59e0b;cursor:pointer}
.fc-hm.s-empty{background:#e9ecef}
.fc-hm.s-none{background:transparent;border-color:transparent}
.fc-hm.s-today{outline:2px solid #0d6efd;outline-offset:1px}
.fc-hm-wrap{overflow-x:auto}
.fc-hm-tbl{border-collapse:separate;border-spacing:0}
.fc-hm-tbl td{padding:0;text-align:center}
.fc-hm-dow{font-size:.7rem;color:var(--bs-secondary-color);padding-right:6px!important;text-align:right;white-space:nowrap}
.fc-hm-mo{font-size:.72rem;color:var(--bs-secondary-color);text-align:left;height:16px}
.fc-legend span{width:12px;height:12px;border-radius:3px;display:inline-block;vertical-align:-1px;margin-right:3px}
</style>

<?= $this->render('_menu', ['active' => 'close']) ?>
<?= $this->render('_close_menu', ['active' => 'summary']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card border h-100"><div class="card-body d-flex align-items-end gap-2">
        <form method="get" class="d-flex gap-2 align-items-end mb-0">
            <div><label class="form-label mb-0 small">ปีงบประมาณ</label>
                <input type="number" class="form-control form-control-sm" name="year" value="<?= $year ?>" style="width:110px"></div>
            <button type="submit" class="btn btn-sm btn-primary">ดู</button>
        </form>
    </div></div></div>
    <div class="col-md-4"><div class="card border-success h-100"><div class="card-body text-center">
        <div class="fs-3 fw-bold text-success"><?= number_format($closedCount) ?></div><div class="text-body-secondary small">วันที่ปิดบัญชีแล้ว</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-warning h-100"><div class="card-body text-center">
        <div class="fs-3 fw-bold text-warning"><?= number_format($pendingCount) ?></div><div class="text-body-secondary small">วันที่มีรายการ<strong>รอปิด</strong></div>
    </div></div></div>
</div>

<div class="card border"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h6 class="mb-0">ภาพรวมการปิดบัญชี ปีงบ <?= $year ?> <span class="text-body-secondary small">(ต.ค. <?= $gy + 542 ?> – ก.ย. <?= $year ?>)</span></h6>
        <div class="fc-legend small text-body-secondary">
            <span style="background:#22c55e"></span>ปิดแล้ว &nbsp;
            <span style="background:#f59e0b"></span>รอปิด &nbsp;
            <span style="background:#e9ecef"></span>ไม่มีรายการ
        </div>
    </div>
    <div class="fc-hm-wrap"><table class="fc-hm-tbl">
        <tr><td></td><?php foreach ($cols as $ci => $rows): ?><td class="fc-hm-mo"><?= $monthLabel[$ci] ?? '' ?></td><?php endforeach; ?></tr>
        <?php for ($r = 0; $r < 7; $r++): ?>
            <tr>
                <td class="fc-hm-dow"><?= $r % 2 === 1 ? $dows[$r] : '' ?></td>
                <?php foreach ($cols as $ci => $rows): $d = $rows[$r] ?? null; ?>
                    <td>
                        <?php if ($d === null): ?>
                            <div class="fc-hm s-none"></div>
                        <?php else:
                            $closed = isset($closedSet[$d]);
                            $active = isset($activeSet[$d]);
                            $s = $closed ? 's-closed' : ($active ? 's-pending' : 's-empty');
                            $clickable = $closed || $active;
                            $tip = $d . ($closed ? ' • ปิดบัญชีแล้ว' : ($active ? ' • มีรายการ (ยังไม่ปิด)' : ' • ไม่มีรายการ'));
                            ?>
                            <div class="fc-hm <?= $s ?><?= $d === $today ? ' s-today' : '' ?>" title="<?= Html::encode($tip) ?>"
                                <?= $clickable ? 'data-day="' . $d . '"' : '' ?>></div>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    </table></div>
    <?php if ($activeTotal === 0): ?>
        <div class="text-body-secondary small mt-3">ยังไม่มีรายการรับ-จ่ายในปีงบนี้</div>
    <?php endif; ?>
</div></div>

<!-- Modal สรุปรายวัน (คลิกจุด) -->
<div class="modal fade" id="daySumModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">สรุป ปิดบัญชี วันที่ <span id="ds-date"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><h6 class="text-success">รายรับ</h6>
                <table class="table table-sm"><tbody id="ds-in"></tbody>
                    <tfoot><tr class="table-light fw-bold"><td>รวมรับ</td><td></td><td class="text-end" id="ds-in-tot">0.00</td></tr></tfoot></table></div>
            <div class="col-md-6"><h6 class="text-warning">รายจ่าย</h6>
                <table class="table table-sm"><tbody id="ds-out"></tbody>
                    <tfoot><tr class="table-light fw-bold"><td>รวมจ่าย</td><td></td><td class="text-end" id="ds-out-tot">0.00</td></tr></tfoot></table></div>
        </div></div>
        <div class="modal-footer justify-content-between">
            <?= Html::beginForm(['close-undo'], 'post', ['id' => 'ds-undo-form']) ?>
            <?= Html::hiddenInput('date', '', ['id' => 'ds-undo-date']) ?>
            <button type="submit" class="btn btn-outline-danger d-none" id="ds-undo-btn" onclick="return confirm('ยกเลิกปิดบัญชีวันนี้? รายการจะถูกปลดล็อกให้แก้ไขได้อีกครั้ง')"><i class="bi bi-unlock me-1"></i>ยกเลิกปิดบัญชีวันนี้</button>
            <?= Html::endForm() ?>
            <span class="d-flex gap-2">
                <a class="btn btn-outline-success" id="ds-rep-register" target="_blank"><i class="bi bi-table me-1"></i>ทะเบียน</a>
                <a class="btn btn-outline-success" id="ds-rep-407" target="_blank"><i class="bi bi-cash-stack me-1"></i>407</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </span>
        </div>
    </div></div>
</div>

<?php
$this->registerJs(<<<JS
(function () {
    const URL = '{$daySummaryUrl}', EXCEL = '{$excelUrl}';
    const fmt = v => Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const modal = new bootstrap.Modal(document.getElementById('daySumModal'));
    const rows = (tbodyId, arr) => {
        const tb = document.getElementById(tbodyId); tb.innerHTML = ''; let tot = 0;
        (arr || []).forEach(r => { tot += r.sum; tb.insertAdjacentHTML('beforeend', '<tr><td>' + r.label + '</td><td class="text-end text-body-secondary">' + r.count + ' รายการ</td><td class="text-end fw-semibold">' + fmt(r.sum) + '</td></tr>'); });
        if (!(arr || []).length) tb.innerHTML = '<tr><td colspan="3" class="text-center text-body-secondary">ไม่มีรายการ</td></tr>';
        return tot;
    };
    document.querySelectorAll('[data-day]').forEach(b => b.addEventListener('click', function () {
        fetch(URL + '?date=' + this.dataset.day, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(res => {
            document.getElementById('ds-date').textContent = res.date;
            document.getElementById('ds-in-tot').textContent = fmt(rows('ds-in', res.in));
            document.getElementById('ds-out-tot').textContent = fmt(rows('ds-out', res.out));
            document.getElementById('ds-undo-date').value = res.date;
            document.getElementById('ds-undo-btn').classList.toggle('d-none', !res.closed);
            document.getElementById('ds-rep-register').href = EXCEL + '?report=register&date=' + encodeURIComponent(res.date);
            document.getElementById('ds-rep-407').href = EXCEL + '?report=balance407&date=' + encodeURIComponent(res.date);
            modal.show();
        });
    }));
})();
JS);
?>
