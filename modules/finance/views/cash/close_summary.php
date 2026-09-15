<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $year ปีงบ พ.ศ. (ปฏิทิน) */
/** @var int $month 1-12 */
/** @var int $gy ค.ศ. */
/** @var array $byDate map 'Y-m-d' => ['in'=>,'out'=>,'count'=>] */

$months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$dows = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];

$this->title = 'สรุปการปิดบัญชีประจำวัน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-calendar-check" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ปฏิทินแสดงวันที่ปิดบัญชีแล้ว — คลิกวันเพื่อดูสรุป/ยกเลิกปิด<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

$firstDow = (int) date('w', mktime(0, 0, 0, $month, 1, $gy));
$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $gy));
$today = date('Y-m-d');
$prevY = $month === 1 ? $year - 1 : $year;
$prevM = $month === 1 ? 12 : $month - 1;
$nextY = $month === 12 ? $year + 1 : $year;
$nextM = $month === 12 ? 1 : $month + 1;
$daySummaryUrl = Url::to(['day-summary']);
$excelUrl = Url::to(['close-excel']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
?>

<?= $this->render('_menu', ['active' => 'close']) ?>
<?= $this->render('_close_menu', ['active' => 'summary']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="card border"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-primary">เดือน <?= $months[$month] ?> <?= $year ?></h5>
        <div class="btn-group">
            <a href="<?= Url::to(['close-summary', 'year' => $prevY, 'month' => $prevM]) ?>" class="btn btn-outline-primary"><i class="bi bi-chevron-left"></i></a>
            <a href="<?= Url::to(['close-summary']) ?>" class="btn btn-outline-primary">วันนี้</a>
            <a href="<?= Url::to(['close-summary', 'year' => $nextY, 'month' => $nextM]) ?>" class="btn btn-outline-primary"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    <div class="table-responsive"><table class="table table-bordered mb-0 text-center align-top">
        <thead class="table-light"><tr>
            <?php foreach ($dows as $i => $d): ?><th class="<?= $i === 0 ? 'text-danger' : '' ?>" style="width:14.28%"><?= $d ?></th><?php endforeach; ?>
        </tr></thead>
        <tbody><tr>
            <?php
            for ($blank = 0; $blank < $firstDow; $blank++) {
                echo '<td class="bg-body-tertiary"></td>';
            }
            $col = $firstDow;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dbDate = sprintf('%04d-%02d-%02d', $gy, $month, $day);
                $has = isset($byDate[$dbDate]);
                $isToday = $dbDate === $today;
                echo '<td style="height:96px" class="' . ($isToday ? 'border-primary border-2' : '') . '">';
                echo '<div class="text-start ' . ($col === 0 ? 'text-danger' : 'text-body-secondary') . '">' . $day . '</div>';
                if ($has) {
                    $net = ($byDate[$dbDate]['in'] ?? 0) - ($byDate[$dbDate]['out'] ?? 0);
                    echo '<button type="button" class="btn btn-success btn-sm w-100 mt-2 text-truncate" data-day="' . $dbDate . '" title="ดูสรุป">'
                        . '<i class="bi bi-check2-circle me-1"></i>ปิดแล้ว</button>';
                }
                echo '</td>';
                $col++;
                if ($col === 7 && $day < $daysInMonth) {
                    echo '</tr><tr>';
                    $col = 0;
                }
            }
            for (; $col < 7 && $col !== 0; $col++) {
                echo '<td class="bg-body-tertiary"></td>';
            }
            ?>
        </tr></tbody>
    </table></div>
</div></div>

<!-- Modal สรุปรายวัน -->
<div class="modal fade" id="daySumModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">สรุป ปิดบัญชี วันที่ <span id="ds-date"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="text-success">รายรับ</h6>
                        <table class="table table-sm"><tbody id="ds-in"></tbody>
                            <tfoot><tr class="table-light fw-bold"><td>รวมรับ</td><td></td><td class="text-end" id="ds-in-tot">0.00</td></tr></tfoot></table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">รายจ่าย</h6>
                        <table class="table table-sm"><tbody id="ds-out"></tbody>
                            <tfoot><tr class="table-light fw-bold"><td>รวมจ่าย</td><td></td><td class="text-end" id="ds-out-tot">0.00</td></tr></tfoot></table>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <?= Html::beginForm(['close-undo'], 'post', ['id' => 'ds-undo-form']) ?>
                <?= Html::hiddenInput('date', '', ['id' => 'ds-undo-date']) ?>
                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('ยกเลิกปิดบัญชีวันนี้? รายการจะถูกปลดล็อกให้แก้ไขได้อีกครั้ง')"><i class="bi bi-unlock me-1"></i>ยกเลิกปิดบัญชีวันนี้</button>
                <?= Html::endForm() ?>
                <span class="d-flex gap-2">
                    <a class="btn btn-outline-success" id="ds-rep-register" target="_blank"><i class="bi bi-table me-1"></i>ทะเบียนปิดบัญชี</a>
                    <a class="btn btn-outline-success" id="ds-rep-407" target="_blank"><i class="bi bi-cash-stack me-1"></i>407</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </span>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(<<<JS
(function () {
    const URL = '{$daySummaryUrl}', EXCEL = '{$excelUrl}';
    const fmt = v => Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const modal = new bootstrap.Modal(document.getElementById('daySumModal'));
    const rows = (tbodyId, arr) => {
        const tb = document.getElementById(tbodyId); tb.innerHTML = '';
        let tot = 0;
        (arr || []).forEach(r => {
            tot += r.sum;
            tb.insertAdjacentHTML('beforeend', '<tr><td>' + r.label + '</td><td class="text-end text-body-secondary">' + r.count + ' รายการ</td><td class="text-end fw-semibold">' + fmt(r.sum) + '</td></tr>');
        });
        if (!(arr || []).length) tb.innerHTML = '<tr><td colspan="3" class="text-center text-body-secondary">ไม่มีรายการ</td></tr>';
        return tot;
    };
    document.querySelectorAll('[data-day]').forEach(b => b.addEventListener('click', function () {
        const d = this.dataset.day;
        fetch(URL + '?date=' + d, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(res => {
            document.getElementById('ds-date').textContent = res.date;
            document.getElementById('ds-in-tot').textContent = fmt(rows('ds-in', res.in));
            document.getElementById('ds-out-tot').textContent = fmt(rows('ds-out', res.out));
            document.getElementById('ds-undo-date').value = res.date;
            document.getElementById('ds-rep-register').href = EXCEL + '?report=register&date=' + encodeURIComponent(res.date);
            document.getElementById('ds-rep-407').href = EXCEL + '?report=balance407&date=' + encodeURIComponent(res.date);
            modal.show();
        });
    }));
})();
JS);
?>
