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
$this->beginBlock('sub-title'); ?>ภาพรวมทั้งปีงบ — คลิกวันเพื่อดูสรุป/ยกเลิกปิด<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$mAbbr = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$today = date('Y-m-d');
// เดือนตามปีงบ ต.ค.(gy-1) → ก.ย.(gy)
$fmonths = [];
foreach ([10, 11, 12] as $m) {
    $fmonths[] = [$gy - 1, $m];
}
foreach (range(1, 9) as $m) {
    $fmonths[] = [$gy, $m];
}
$daySummaryUrl = Url::to(['day-summary']);
$excelUrl = Url::to(['close-excel']);
?>

<style>
.hm2{border-collapse:separate;border-spacing:3px;width:100%;table-layout:fixed;min-width:760px}
.hm2 th.hm-d{font-size:.68rem;color:var(--bs-secondary-color);text-align:center;font-weight:400;padding:0}
.hm2 th.hm-mo{font-size:.78rem;text-align:right;white-space:nowrap;width:70px;padding-right:6px;color:var(--bs-secondary-color)}
.hm2 .cell{height:28px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:.72rem;border:1px solid rgba(0,0,0,.05)}
.cell.s-closed{background:#22c55e;color:#fff;cursor:pointer}
.cell.s-pending{background:#f59e0b;color:#fff;cursor:pointer}
.cell.s-empty{background:#eef0f2;color:#adb5bd}
.cell.s-none{background:transparent;border-color:transparent}
.cell.s-today{outline:2px solid #0d6efd;outline-offset:1px}
.hm-legend span{width:13px;height:13px;border-radius:4px;display:inline-block;vertical-align:-2px;margin-right:4px}
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
    <div class="col-md-4"><div class="card border-success h-100"><div class="card-body text-center py-3">
        <div class="fs-3 fw-bold text-success"><?= number_format($closedCount) ?></div><div class="text-body-secondary small">วันที่ปิดบัญชีแล้ว</div>
    </div></div></div>
    <div class="col-md-4"><div class="card border-warning h-100"><div class="card-body text-center py-3">
        <div class="fs-3 fw-bold text-warning"><?= number_format($pendingCount) ?></div><div class="text-body-secondary small">วันที่มีรายการ<strong>รอปิด</strong></div>
    </div></div></div>
</div>

<div class="card border"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h6 class="mb-0">ภาพรวมการปิดบัญชี ปีงบ <?= $year ?></h6>
        <div class="hm-legend small text-body-secondary">
            <span style="background:#22c55e"></span>ปิดแล้ว &nbsp;
            <span style="background:#f59e0b"></span>รอปิด &nbsp;
            <span style="background:#eef0f2"></span>ไม่มีรายการ
        </div>
    </div>
    <div style="overflow-x:auto"><table class="hm2">
        <thead><tr>
            <th class="hm-mo"></th>
            <?php for ($d = 1; $d <= 31; $d++): ?><th class="hm-d"><?= $d ?></th><?php endfor; ?>
        </tr></thead>
        <tbody>
            <?php foreach ($fmonths as [$Y, $m]):
                $days = (int) date('t', mktime(0, 0, 0, $m, 1, $Y)); ?>
                <tr>
                    <th class="hm-mo"><?= $mAbbr[$m] ?> <?= substr((string) ($Y + 543), -2) ?></th>
                    <?php for ($d = 1; $d <= 31; $d++): ?>
                        <td>
                            <?php if ($d > $days): ?>
                                <div class="cell s-none"></div>
                            <?php else:
                                $date = sprintf('%04d-%02d-%02d', $Y, $m, $d);
                                $closed = isset($closedSet[$date]);
                                $active = isset($activeSet[$date]);
                                $s = $closed ? 's-closed' : ($active ? 's-pending' : 's-empty');
                                $click = $closed || $active;
                                $tip = $date . ($closed ? ' • ปิดบัญชีแล้ว' : ($active ? ' • มีรายการ (รอปิด)' : ' • ไม่มีรายการ'));
                                ?>
                                <div class="cell <?= $s ?><?= $date === $today ? ' s-today' : '' ?>" title="<?= Html::encode($tip) ?>"
                                    <?= $click ? 'data-day="' . $date . '"' : '' ?>><?= $d ?></div>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php if (count($activeSet) === 0): ?>
        <div class="text-body-secondary small mt-3">ยังไม่มีรายการรับ-จ่ายในปีงบนี้</div>
    <?php endif; ?>
</div></div>

<!-- Modal สรุปรายวัน (คลิกวัน) -->
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
