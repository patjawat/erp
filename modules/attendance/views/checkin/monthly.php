<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var int $month @var string $monthName @var int $yearCE @var int $yearBE @var int $daysInMonth */
/** @var array $rows @var array $weekends @var int $totalLate */
/** @var array $groups @var array $units @var ?int $selGroup @var ?int $selUnit */

$this->title = 'สรุปการลงเวลารายเดือน';
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$thaiMonths = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$nowBE = (int)date('Y') + 543;
$yearOptions = range($nowBE + 1, $nowBE - 3);
$dayNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

$excelUrl = Url::to(['monthly-excel', 'month' => $month, 'year' => $yearBE, 'group' => $selGroup, 'unit' => $selUnit]);
$personCount = count($rows);

/** glyph ต่อสถานะ cell */
$glyph = function ($cell) {
    switch ($cell['state']) {
        case 'ontime': return '<span class="g-time">' . Html::encode($cell['time']) . '</span>';
        case 'late':   return '<span class="g-time is-late">' . Html::encode($cell['time']) . '</span>';
        case 'shift':  return '<span class="g-time is-shift">' . Html::encode($cell['time']) . '</span>';
        case 'leave':  return '<span class="g-leave">' . Html::encode($cell['lv']['ab'] ?? 'ล') . '</span>';
        case 'trip':   return '<span class="g-trip">ร</span>';
        case 'absent': return '<span class="g-absent">—</span>';
        case 'nodata': return '<span class="g-nodata" aria-hidden="true">·</span>';
        default:       return ''; // weekend / holiday / future
    }
};

/** วันที่แบบไทยสั้น เช่น 5 ก.ค. 69 */
$thaiShort = ['01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.', '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.', '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'];
$fmtDate = function ($ymd) use ($thaiShort) {
    if (!$ymd) {
        return '';
    }
    $t = strtotime($ymd);
    return (int)date('j', $t) . ' ' . ($thaiShort[date('m', $t)] ?? '') . ' ' . ((int)date('Y', $t) + 543 - 2500);
};
$fmtRange = function ($from, $to) use ($fmtDate) {
    return ($from === $to) ? $fmtDate($from) : $fmtDate($from) . ' – ' . $fmtDate($to);
};

/** เนื้อหา tooltip ของ cell — รวมทุกอย่างที่เกิดขึ้นในวันนั้น (ลงเวลา / ลา / ไปราชการ / วันหยุด) */
$tip = function ($cell, $d) use ($holidays, $weekends, $yearCE, $month, $fmtDate, $fmtRange, $dayNames) {
    $dateStr = sprintf('%04d-%02d-%02d', $yearCE, $month, $d);
    $w = (int)date('w', strtotime($dateStr));
    $out = '<div class="tp"><div class="tp-h">' . $dayNames[$w] . ' ' . Html::encode($fmtDate($dateStr)) . '</div>';
    $body = '';

    if (!empty($cell['time'])) {
        $label = $cell['state'] === 'late' ? 'ลงเวลาเข้า (สาย)' : ($cell['state'] === 'shift' ? 'ลงเวลาเข้า (เวร)' : 'ลงเวลาเข้า');
        $body .= '<div class="tp-r"><span class="tp-k">' . $label . '</span><span class="tp-v">' . Html::encode($cell['time']) . ' น.</span></div>';
    }
    if (!empty($cell['lv'])) {
        $lv = $cell['lv'];
        $body .= '<div class="tp-r"><span class="tp-k">' . Html::encode($lv['title'] ?: 'ลา') . '</span><span class="tp-v">'
            . Html::encode($fmtRange($lv['from'], $lv['to']))
            . ($lv['days'] !== null ? ' · ' . Html::encode(rtrim(rtrim(number_format((float)$lv['days'], 1), '0'), '.')) . ' วัน' : '')
            . '</span></div>';
        if (!empty($lv['reason'])) {
            $body .= '<div class="tp-note">' . Html::encode($lv['reason']) . '</div>';
        }
    }
    if (!empty($cell['trip'])) {
        foreach (array_slice($cell['trip'], 0, 3) as $tp) {
            $body .= '<div class="tp-r"><span class="tp-k">ไปราชการ</span><span class="tp-v">' . Html::encode($fmtRange($tp['from'], $tp['to'])) . '</span></div>'
                . '<div class="tp-note">' . Html::encode($tp['topic']) . '</div>';
        }
        if (count($cell['trip']) > 3) {
            $body .= '<div class="tp-note">และอีก ' . (count($cell['trip']) - 3) . ' รายการ</div>';
        }
    }
    if (isset($holidays[$d])) {
        $body .= '<div class="tp-r"><span class="tp-k">วันหยุด</span><span class="tp-v">' . Html::encode($holidays[$d]) . '</span></div>';
    }
    if ($body === '') {
        if ($cell['state'] === 'absent') {
            $body = '<div class="tp-note">ไม่มีการลงเวลา</div>';
        } elseif ($cell['state'] === 'nodata') {
            $body = '<div class="tp-note">ยังไม่เริ่มใช้ระบบลงเวลา (ไม่นับเป็นขาด)</div>';
        } elseif ($weekends[$d]) {
            $body = '<div class="tp-note">วันหยุดสุดสัปดาห์</div>';
        } else {
            return ''; // future / ไม่มีข้อมูล — ไม่ต้องมี tooltip
        }
    }
    return $out . $body . '</div>';
};
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'monthly']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-1 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar3"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">บุคลากรปฏิบัติราชการ ไล่การลงเวลาเข้ารายวัน พร้อมสรุปจำนวนครั้งที่มาสาย</p>
</div>
<?php $this->endBlock(); ?>

<div class="att-mtx">
    <div class="att-mtx__shell">

        <!-- ตัวกรอง -->
        <form method="get" action="<?= Url::to(['monthly']) ?>" class="att-mtx__filter">
            <div class="att-mtx__filter-grid">
                <div class="att-field">
                    <label class="att-lbl">เดือน</label>
                    <select name="month" class="att-select">
                        <?php foreach ($thaiMonths as $mi => $mn): ?>
                        <option value="<?= $mi ?>" <?= $mi === $month ? 'selected' : '' ?>><?= $mn ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="att-field">
                    <label class="att-lbl">ปี (พ.ศ.)</label>
                    <select name="year" class="att-select">
                        <?php foreach ($yearOptions as $y): ?>
                        <option value="<?= $y ?>" <?= $y === $yearBE ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="att-field">
                    <label class="att-lbl">กลุ่มงาน</label>
                    <select name="group" class="att-select">
                        <option value="">ทุกกลุ่มงาน</option>
                        <?php foreach ($groups as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $id === $selGroup ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="att-field">
                    <label class="att-lbl">ฝ่าย/หน่วยงาน</label>
                    <select name="unit" class="att-select">
                        <option value="">ทุกฝ่าย</option>
                        <?php foreach ($units as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $id === $selUnit ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="att-mtx__filter-actions">
                    <button type="submit" class="att-btn att-btn--primary att-btn--sm"><i class="bi bi-funnel"></i> แสดง</button>
                    <a href="<?= $excelUrl ?>" id="mtx-export" class="btn btn-sm btn-success<?= empty($rows) ? ' disabled' : '' ?>"<?= empty($rows) ? ' aria-disabled="true" tabindex="-1"' : '' ?>><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
                </div>
            </div>
        </form>

        <!-- แถบสรุป + คำอธิบายสัญลักษณ์ -->
        <div class="att-mtx__bar">
            <div class="att-mtx__recap">
                <span class="att-mtx__period"><?= Html::encode($monthName) ?> <?= $yearBE ?></span>
                <span class="att-mtx__dot">·</span>
                <span>บุคลากร <strong><?= $personCount ?></strong> คน</span>
                <span class="att-mtx__dot">·</span>
                <span>มาสาย <strong class="<?= $totalLate > 0 ? 'text-late' : '' ?>"><?= $totalLate ?></strong> ครั้ง</span>
                <span class="att-mtx__dot">·</span>
                <span>ขาด <strong class="<?= ($totalAbsent ?? 0) > 0 ? 'text-absent' : '' ?>"><?= (int)($totalAbsent ?? 0) ?></strong> วัน</span>
                <span class="att-mtx__dot">·</span>
                <span>ลา <strong class="<?= ($totalLeave ?? 0) > 0 ? 'text-leave' : '' ?>"><?= (int)($totalLeave ?? 0) ?></strong> วัน</span>
                <span class="att-mtx__dot">·</span>
                <span>ไปราชการ <strong class="<?= ($totalTrip ?? 0) > 0 ? 'text-trip' : '' ?>"><?= (int)($totalTrip ?? 0) ?></strong> วัน</span>
            </div>
            <div class="att-mtx__legend">
                <span class="lg"><span class="g-time">08:12</span> ตรงเวลา</span>
                <span class="lg"><span class="g-time is-late">09:05</span> สาย <span class="lg-hint">(หลัง <?= Html::encode($shiftStart ?? '08:30') ?> น.)</span></span>
                <span class="lg"><span class="g-time is-shift">07:40</span> เวร</span>
                <span class="lg"><span class="g-leave">ล</span> ลา <span class="lg-hint">(ป ป่วย · ก กิจ · พ พักผ่อน)</span></span>
                <span class="lg"><span class="g-trip">ร</span> ไปราชการ</span>
                <span class="lg"><span class="g-absent">—</span> ขาด</span>
                <span class="lg"><span class="g-nodata">·</span> ยังไม่เริ่มใช้ระบบ</span>
                <span class="lg"><span class="lg-weekend"></span> เสาร์-อาทิตย์</span>
                <span class="lg"><span class="lg-holiday"></span> วันหยุดนักขัตฤกษ์</span>
            </div>
        </div>

        <?php if (!empty($rows) && ($coveredCount ?? 0) < $personCount): ?>
        <p class="att-mtx__coverage">
            <i class="bi bi-info-circle"></i>
            ระบบลงเวลามีข้อมูลของ <strong><?= (int)($coveredCount ?? 0) ?></strong> จาก <?= $personCount ?> คน
            ช่องที่เป็น <span class="g-nodata">·</span> คือช่วงที่บุคลากรยังไม่เริ่มใช้ระบบ จึงไม่ถูกนับเป็นขาดงาน
        </p>
        <?php endif; ?>

        <!-- Matrix -->
        <?php if (empty($rows)): ?>
            <div class="att-mtx__empty">
                <p class="att-mtx__empty-title">ไม่พบบุคลากรตามเงื่อนไข</p>
                <p class="att-mtx__empty-sub">ลองเปลี่ยนกลุ่มงาน/ฝ่าย หรือเลือกเดือนอื่น</p>
            </div>
        <?php else: ?>
        <div class="att-mtx__tools">
            <div class="att-mtx__search">
                <i class="bi bi-search att-mtx__search-icon" aria-hidden="true"></i>
                <input type="search" id="mtx-search" class="att-mtx__search-input" placeholder="ค้นหาชื่อหรือตำแหน่ง" autocomplete="off" aria-controls="mtx-table">
                <button type="button" class="att-mtx__search-clear" id="mtx-search-clear" hidden aria-label="ล้างคำค้นหา"><i class="bi bi-x-lg"></i></button>
            </div>
            <p class="att-mtx__hint" id="mtx-result" role="status" aria-live="polite">คลิกหัวคอลัมน์สรุปเพื่อเรียงลำดับ · คลิกชื่อเพื่อดูประวัติรายคน</p>
        </div>
        <div class="att-mtx__scroll">
            <table class="mtx" id="mtx-table">
                <caption class="visually-hidden">สรุปการลงเวลาเข้างานรายวันของบุคลากร <?= $personCount ?> คน เดือน<?= Html::encode($monthName) ?> พ.ศ. <?= $yearBE ?></caption>
                <thead>
                    <tr>
                        <th class="mtx-name" scope="col">
                            <button type="button" class="mtx-sort" data-sort="name" aria-label="เรียงตามชื่อ">รายชื่อ (<span id="mtx-count"><?= $personCount ?></span>) <i class="bi bi-arrow-down-up mtx-sort__i" aria-hidden="true"></i></button>
                        </th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <?php $w = (int)date('w', strtotime(sprintf('%04d-%02d-%02d', $yearCE, $month, $d))); $isHol = isset($holidays[$d]); ?>
                        <th class="mtx-day <?= $weekends[$d] ? 'is-weekend' : '' ?> <?= $isHol ? 'is-holiday' : '' ?>"<?= $isHol ? ' data-tip data-bs-title="' . Html::encode('<div class="tp"><div class="tp-h">' . Html::encode($fmtDate(sprintf('%04d-%02d-%02d', $yearCE, $month, $d))) . '</div><div class="tp-r"><span class="tp-k">วันหยุด</span><span class="tp-v">' . Html::encode($holidays[$d]) . '</span></div></div>') . '"' : '' ?>>
                            <span class="mtx-day__num"><?= $d ?></span>
                            <span class="mtx-day__dow"><?= $isHol ? '<span class="mtx-day__hol" aria-hidden="true">●</span>' : $dayNames[$w] ?></span>
                        </th>
                        <?php endfor; ?>
                        <th class="mtx-sum mtx-sum--trip" scope="col"><button type="button" class="mtx-sort" data-sort="trip">รวมไปราชการ <i class="bi bi-arrow-down-up mtx-sort__i" aria-hidden="true"></i></button></th>
                        <th class="mtx-sum mtx-sum--leave" scope="col"><button type="button" class="mtx-sort" data-sort="leave">รวมลา <i class="bi bi-arrow-down-up mtx-sort__i" aria-hidden="true"></i></button></th>
                        <th class="mtx-sum mtx-sum--absent" scope="col"><button type="button" class="mtx-sort" data-sort="absent">รวมขาด <i class="bi bi-arrow-down-up mtx-sort__i" aria-hidden="true"></i></button></th>
                        <th class="mtx-sum mtx-sum--late" scope="col"><button type="button" class="mtx-sort" data-sort="late">รวมสาย <i class="bi bi-arrow-down-up mtx-sort__i" aria-hidden="true"></i></button></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr data-name="<?= Html::encode(mb_strtolower($row['name'] . ' ' . $row['position'])) ?>"
                        data-late="<?= (int)$row['lateCount'] ?>" data-absent="<?= (int)($row['absentCount'] ?? 0) ?>"
                        data-leave="<?= (int)$row['leaveCount'] ?>" data-trip="<?= (int)($row['tripCount'] ?? 0) ?>">
                        <th class="mtx-name" scope="row">
                            <a class="mtx-person" href="<?= Url::to(['report', 'CheckinRecordSearch[emp_id]' => $row['id']]) ?>" title="ดูประวัติการลงเวลาของ <?= Html::encode($row['name']) ?>">
                                <img class="mtx-avatar" src="<?= Html::encode($row['avatar']) ?>" alt="" loading="lazy">
                                <span class="mtx-person__body">
                                    <span class="mtx-name__title"><?= Html::encode($row['name']) ?></span>
                                    <span class="mtx-name__sub">
                                        <?= $row['position'] !== '' ? Html::encode($row['position']) : '<span class="text-muted">—</span>' ?>
                                        <?php if ($row['shift'] === 'shift'): ?><span class="mtx-tag">เวร</span><?php endif; ?>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <?php $cell = $row['cells'][$d]; $tt = $tip($cell, $d); ?>
                        <td class="mtx-day <?= $weekends[$d] ? 'is-weekend' : '' ?> <?= isset($holidays[$d]) ? 'is-holiday' : '' ?>"<?= $tt !== '' ? ' data-tip data-bs-title="' . Html::encode($tt) . '"' : '' ?>><?= $glyph($cell) ?></td>
                        <?php endfor; ?>
                        <td class="mtx-sum mtx-sum--trip <?= ($row['tripCount'] ?? 0) > 0 ? 'is-trip' : '' ?>"><?= (int)($row['tripCount'] ?? 0) ?></td>
                        <td class="mtx-sum mtx-sum--leave <?= $row['leaveCount'] > 0 ? 'is-leave' : '' ?>"><?= $row['leaveCount'] ?></td>
                        <td class="mtx-sum mtx-sum--absent <?= ($row['absentCount'] ?? 0) > 0 ? 'is-absent' : '' ?>"><?= (int)($row['absentCount'] ?? 0) ?></td>
                        <td class="mtx-sum mtx-sum--late <?= $row['lateCount'] > 0 ? 'is-late' : '' ?>"><?= $row['lateCount'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="mtx-tot mtx-tot--late">
                        <th class="mtx-name" scope="row">มาสายรายวัน (คน)</th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <td class="mtx-day <?= $weekends[$d] ? 'is-weekend' : '' ?> <?= isset($holidays[$d]) ? 'is-holiday' : '' ?>"><?= ($dayLate[$d] ?? 0) > 0 ? '<span class="tot-late">' . (int)$dayLate[$d] . '</span>' : '' ?></td>
                        <?php endfor; ?>
                        <td class="mtx-sum mtx-sum--trip"></td>
                        <td class="mtx-sum mtx-sum--leave"></td>
                        <td class="mtx-sum mtx-sum--absent"></td>
                        <td class="mtx-sum mtx-sum--late <?= $totalLate > 0 ? 'is-late' : '' ?>"><?= $totalLate ?></td>
                    </tr>
                    <tr class="mtx-tot mtx-tot--absent">
                        <th class="mtx-name" scope="row">ขาดรายวัน (คน)</th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <td class="mtx-day <?= $weekends[$d] ? 'is-weekend' : '' ?> <?= isset($holidays[$d]) ? 'is-holiday' : '' ?>"><?= ($dayAbsent[$d] ?? 0) > 0 ? '<span class="tot-absent">' . (int)$dayAbsent[$d] . '</span>' : '' ?></td>
                        <?php endfor; ?>
                        <td class="mtx-sum mtx-sum--trip"></td>
                        <td class="mtx-sum mtx-sum--leave"></td>
                        <td class="mtx-sum mtx-sum--absent <?= ($totalAbsent ?? 0) > 0 ? 'is-absent' : '' ?>"><?= (int)($totalAbsent ?? 0) ?></td>
                        <td class="mtx-sum mtx-sum--late"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.att-mtx{
    --ink-1:#1a202c;--ink-2:#4a5568;--ink-3:#718096;--ink-4:#a0aec0;
    --surface:#fff;--surface-2:#f7f9fc;--surface-3:#eef2f7;--surface-hover:#f1f5f9;
    --line:rgba(15,23,42,.08);--line-strong:rgba(15,23,42,.14);
    --primary:#0d6efd;--primary-ink:#0a58ca;--primary-soft:rgba(13,110,253,.08);
    --success:#15803d;--success-soft:rgba(21,128,61,.1);--warning:#b45309;--warning-soft:rgba(180,83,9,.12);--danger:#b91c1c;
    --radius:10px;--radius-sm:8px;--shadow-1:0 1px 2px rgba(15,23,42,.04),0 1px 1px rgba(15,23,42,.03);--ease:cubic-bezier(.16,1,.3,1);
    color:var(--ink-1);
}
.att-mtx__shell{padding:1.25rem 0 2rem;display:flex;flex-direction:column;gap:1rem}
.att-mtx .text-late{color:var(--warning)}
.att-mtx .text-leave{color:#6d28d9}
.att-mtx .text-trip{color:#0f766e}

/* filter */
.att-mtx__filter{margin:0}
.att-mtx__filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr)) auto;gap:.6rem;align-items:end;padding:.9rem 1rem;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow-1)}
@media (max-width:820px){.att-mtx__filter-grid{grid-template-columns:1fr 1fr}.att-mtx__filter-actions{grid-column:1/-1}}
.att-mtx .att-field{min-width:0;margin:0}
.att-mtx .att-lbl{display:block;font-size:.78rem;font-weight:500;color:var(--ink-2);margin-bottom:.3rem}
.att-mtx .att-select{width:100%;min-height:40px;padding:.35rem .6rem;border:1px solid var(--line-strong);border-radius:var(--radius-sm);font-size:.88rem;color:var(--ink-1);background:var(--surface)}
.att-mtx .att-select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}
.att-mtx__filter-actions{display:flex;gap:.4rem;align-items:end;flex-wrap:wrap}

/* recap + legend */
.att-mtx__bar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem 1.5rem}
.att-mtx__recap{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;font-size:.9rem;color:var(--ink-2)}
.att-mtx__period{font-weight:400;color:var(--ink-1)}
.att-mtx__dot{color:var(--ink-4)}
.att-mtx__legend{display:flex;flex-wrap:wrap;gap:.75rem 1rem;font-size:.8rem;color:var(--ink-2)}
.att-mtx__legend .lg{display:inline-flex;align-items:center;gap:.3rem}
.att-mtx__legend .lg-hint{color:var(--ink-3);font-size:.72rem}
.att-mtx__legend .lg-weekend{display:inline-block;width:14px;height:14px;border-radius:3px;background:var(--surface-3);border:1px solid var(--line)}
.att-mtx__legend .lg-holiday{display:inline-block;width:14px;height:14px;border-radius:3px;background:#fbe1ea;border:1px solid #f4c9d8}

/* glyphs */
/* เวลาที่ลงจริง — สีบอกสถานะ (เขียว = ตรงเวลา, ส้ม = สาย, เทา = เวร ไม่ประเมินสาย) */
.att-mtx .g-time{font-size:.68rem;line-height:1;font-variant-numeric:tabular-nums;letter-spacing:-.01em;color:var(--success)}
.att-mtx .g-time.is-late{color:var(--warning);font-weight:600}
.att-mtx .g-time.is-shift{color:var(--ink-3)}
.att-mtx .g-leave{color:#6d28d9;font-weight:400;font-size:.82rem}
.att-mtx .g-trip{color:#0f766e;font-weight:400;font-size:.82rem}
.att-mtx .g-absent{color:var(--ink-4)}
.att-mtx .g-nodata{color:#cbd5e1;font-size:.9rem;line-height:1}
.att-mtx .text-absent{color:var(--danger)}

/* matrix table */
.att-mtx__scroll{overflow:auto;max-height:72vh;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow-1)}
.att-mtx .mtx{border-collapse:separate;border-spacing:0;width:max-content;min-width:100%;font-size:.8rem}
.att-mtx .mtx th,.att-mtx .mtx td{border-bottom:1px solid var(--line);border-right:1px solid var(--line)}
.att-mtx .mtx thead th{position:sticky;top:0;z-index:2;background:var(--surface-2);color:var(--ink-2);font-weight:500;text-align:center;padding:.35rem .2rem;vertical-align:middle}
.att-mtx .mtx-day{width:42px;min-width:42px;text-align:center;padding:.4rem .1rem}
.att-mtx .mtx thead .mtx-day{line-height:1.05}
.att-mtx .mtx-day__num{display:block;font-weight:400;color:var(--ink-1);font-variant-numeric:tabular-nums}
.att-mtx .mtx-day__dow{display:block;font-size:.62rem;color:var(--ink-3)}
.att-mtx .mtx th.is-weekend{background:var(--surface-3)}
.att-mtx .mtx td.is-weekend{background:var(--surface-2)}
.att-mtx .mtx th.is-holiday{background:#fbe1ea}
.att-mtx .mtx td.is-holiday{background:#fdeef3}
.att-mtx .mtx th.is-holiday .mtx-day__num{color:#be123c}
.att-mtx .mtx-day__hol{color:#be123c;font-size:.5rem;line-height:1;display:inline-block}

/* sticky name column (left) */
.att-mtx .mtx-name{position:sticky;left:0;z-index:1;background:var(--surface);text-align:left;padding:.4rem .7rem;min-width:228px;max-width:248px}
.att-mtx .mtx thead .mtx-name{z-index:3;background:var(--surface-2);padding:0}
.att-mtx .mtx tbody th.mtx-name{font-weight:400}
/* toolbar: ค้นหา + คำใบ้ */
.att-mtx__tools{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.5rem 1rem}
.att-mtx__search{position:relative;flex:0 1 320px;min-width:200px}
.att-mtx__search-icon{position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--ink-4);font-size:.85rem;pointer-events:none}
.att-mtx__search-input{width:100%;min-height:40px;padding:.35rem 2.2rem .35rem 2.1rem;border:1px solid var(--line-strong);border-radius:var(--radius-sm);font-size:.88rem;color:var(--ink-1);background:var(--surface)}
.att-mtx__search-input::placeholder{color:var(--ink-3)}
.att-mtx__search-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}
.att-mtx__search-input::-webkit-search-cancel-button{display:none}
.att-mtx__search-clear{position:absolute;right:.35rem;top:50%;transform:translateY(-50%);width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:50%;background:none;color:var(--ink-3);font-size:.72rem;cursor:pointer}
.att-mtx__search-clear:hover{background:var(--surface-hover);color:var(--ink-1)}
.att-mtx__hint{margin:0;font-size:.78rem;color:var(--ink-3)}
.att-mtx__coverage{margin:0;padding:.6rem .8rem;border:1px solid var(--line);border-radius:var(--radius-sm);background:var(--surface-2);font-size:.82rem;color:var(--ink-2);line-height:1.5}
.att-mtx__coverage .bi{color:var(--ink-3);margin-right:.25rem}

.att-mtx .mtx-person{display:flex;align-items:center;gap:.55rem;min-width:0;text-decoration:none;color:inherit;border-radius:var(--radius-xs,6px)}
.att-mtx a.mtx-person:hover .mtx-name__title{color:var(--primary-ink);text-decoration:underline}
.att-mtx a.mtx-person:focus-visible{outline:none;box-shadow:0 0 0 3px var(--primary-soft)}
.att-mtx .mtx-avatar{width:32px;height:32px;flex:none;border-radius:50%;object-fit:cover;background:var(--surface-3);border:1px solid var(--line)}
.att-mtx .mtx-person__body{min-width:0;display:flex;flex-direction:column}
.att-mtx .mtx-name__title{display:block;font-weight:400;color:var(--ink-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:176px}
.att-mtx .mtx-name__sub{display:flex;align-items:center;gap:.35rem;font-size:.74rem;color:var(--ink-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:176px}
.att-mtx .mtx-tag{flex:none;padding:.02rem .35rem;border-radius:999px;background:var(--surface-3);color:var(--ink-2);font-size:.66rem;font-weight:500}

/* sticky summary columns (right) — ไปราชการ, ลา, ขาด, สาย (ขวาสุด) */
.att-mtx .mtx-sum{position:sticky;z-index:1;background:var(--surface);text-align:center;min-width:64px;width:64px;font-variant-numeric:tabular-nums;font-weight:500;color:var(--ink-2)}
.att-mtx .mtx-sum--late{right:0;border-left:1px solid var(--line-strong)}
.att-mtx .mtx-sum--absent{right:64px;border-left:1px solid var(--line)}
.att-mtx .mtx-sum--leave{right:128px;border-left:1px solid var(--line)}
.att-mtx .mtx-sum--trip{right:192px;border-left:1px solid var(--line)}
.att-mtx .mtx thead .mtx-sum{z-index:3;background:var(--surface-2);color:var(--ink-2);font-weight:500;font-size:.72rem;line-height:1.2;white-space:normal;padding:0}
.att-mtx .mtx-sum--late.is-late{color:var(--warning);background:#fbe8cf}
.att-mtx .mtx-sum--leave.is-leave{color:#6d28d9;background:#ede7f6}
.att-mtx .mtx-sum--trip.is-trip{color:#0f766e;background:#d7f0ec}
.att-mtx .mtx-sum--absent.is-absent{color:var(--danger);background:#fbdcdc}

/* sort buttons ในหัวตาราง */
.att-mtx .mtx-sort{display:flex;align-items:center;justify-content:center;gap:.25rem;width:100%;padding:.4rem .25rem;border:0;background:none;font:inherit;color:inherit;cursor:pointer;line-height:1.2;border-radius:var(--radius-xs,6px)}
.att-mtx .mtx-name .mtx-sort{justify-content:center}
.att-mtx .mtx-sort:hover{background:var(--surface-3)}
.att-mtx .mtx-sort:focus-visible{outline:none;box-shadow:0 0 0 3px var(--primary-soft);color:var(--primary-ink)}
.att-mtx .mtx-sort__i{font-size:.62rem;opacity:.4;flex:none}
.att-mtx .mtx-sort.is-sorted{color:var(--primary-ink)}
.att-mtx .mtx-sort.is-sorted .mtx-sort__i{opacity:1}

/* row hover keeps sticky cells in sync */
.att-mtx .mtx tbody tr:hover td{background:var(--surface-hover)}
.att-mtx .mtx tbody tr:hover .mtx-name,.att-mtx .mtx tbody tr:hover .mtx-sum{background:var(--surface-hover)}
.att-mtx .mtx tbody tr:hover .mtx-sum--late.is-late{background:#f6ddb9}
.att-mtx .mtx tbody tr:hover .mtx-sum--leave.is-leave{background:#e3d9f5}
.att-mtx .mtx tbody tr:hover .mtx-sum--trip.is-trip{background:#c6e8e2}
.att-mtx .mtx tbody tr:hover .mtx-sum--absent.is-absent{background:#f7cccc}

/* แถวรวมท้ายตาราง (ต่อวัน) */
.att-mtx .mtx tfoot th,.att-mtx .mtx tfoot td{background:var(--surface-2);border-top:1px solid var(--line-strong)}
.att-mtx .mtx tfoot .mtx-name{font-size:.76rem;font-weight:500;color:var(--ink-2);text-align:right;padding-right:.75rem;white-space:nowrap}
.att-mtx .mtx tfoot .mtx-day{font-size:.72rem;font-variant-numeric:tabular-nums}
.att-mtx .tot-late{color:var(--warning);font-weight:600}
.att-mtx .tot-absent{color:var(--danger);font-weight:600}
.att-mtx .mtx tfoot .mtx-day.is-weekend{background:var(--surface-3)}
.att-mtx .mtx tfoot .mtx-day.is-holiday{background:#fbe1ea}

/* buttons */
.att-mtx .att-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;min-height:40px;padding:.45rem .9rem;border:1px solid transparent;border-radius:var(--radius-sm);font-size:.9rem;font-weight:500;text-decoration:none;cursor:pointer;transition:background 140ms var(--ease),border-color 140ms var(--ease)}
.att-mtx .att-btn--sm{min-height:38px;padding:.4rem .8rem;font-size:.85rem}
.att-mtx .att-btn--primary{background:var(--primary);color:#fff;border-color:var(--primary)}
.att-mtx .att-btn--primary:hover{background:var(--primary-ink);border-color:var(--primary-ink)}
.att-mtx .att-btn--success{background:#15803d;color:#fff;border-color:#15803d}
.att-mtx .att-btn--success:hover{background:#116631;border-color:#116631;color:#fff}

/* empty */
.att-mtx__empty{padding:3rem 1.5rem;text-align:center;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow-1)}
.att-mtx__empty-title{margin:0;font-weight:500;color:var(--ink-2);font-size:1.05rem}
.att-mtx__empty-sub{margin:.3rem 0 0;font-size:.88rem;color:var(--ink-3)}

/* มือถือ: matrix 31 วันอ่านไม่ได้จริง — ย่อคอลัมน์วัน ตรึงเฉพาะชื่อ+รวมสาย และซ่อนคอลัมน์สรุปที่เหลือ
   (สรุปเต็มยังอ่านได้จากแถบด้านบนและไฟล์ Excel) */
@media (max-width:820px){
    .att-mtx .mtx-name{min-width:150px;max-width:160px;padding:.4rem .5rem}
    .att-mtx .mtx-name__title,.att-mtx .mtx-name__sub{max-width:108px}
    .att-mtx .mtx-avatar{width:26px;height:26px}
    .att-mtx .mtx-day{width:34px;min-width:34px}
    .att-mtx .g-time{font-size:.6rem}
    .att-mtx .mtx-sum{min-width:52px;width:52px}
    .att-mtx .mtx-sum--late{right:0}
    .att-mtx .mtx-sum--absent{right:52px}
    .att-mtx .mtx-sum--leave,.att-mtx .mtx-sum--trip{display:none}
    .att-mtx .mtx tfoot .mtx-name{font-size:.7rem;padding-right:.5rem;white-space:normal}
    .att-mtx__tools{flex-direction:column;align-items:stretch}
    .att-mtx__search{flex:1 1 auto}
}

/* พิมพ์: รายงานราชการมักต้องแนบเสนอ */
@media print{
    .att-mtx__filter,.att-mtx__tools,.att-mtx__legend{display:none}
    .att-mtx__scroll{overflow:visible;border:0;box-shadow:none}
    .att-mtx .mtx-name,.att-mtx .mtx-sum,.att-mtx .mtx thead th{position:static}
    .att-mtx .mtx{font-size:.62rem}
    .att-mtx .mtx-day{width:auto;min-width:0}
}

@media (prefers-reduced-motion:reduce){.att-mtx .att-btn{transition:none}}

/* SweetAlert ของ export — radius ตามมาตรฐานระบบ (popup 12 / ปุ่ม 8)
   SweetAlert แทรก stylesheet ตอน runtime จึงมาหลัง style นี้ ต้องเพิ่ม specificity ให้ชนะ */
.swal2-popup.att-swal{border-radius:12px}
.swal2-popup.att-swal .swal2-actions .swal2-styled.att-swal__btn{border-radius:8px}
.swal2-popup.att-swal .att-swal__meta{font-size:.85rem;color:#4a5568}
@media (prefers-reduced-motion:reduce){.swal2-popup.att-swal .swal2-actions .swal2-styled.att-swal__btn{transition:none}}

/* tooltip รายละเอียดรายวัน (render ที่ body จึงอยู่นอก namespace) */
.att-mtx .mtx td[data-tip],.att-mtx .mtx th[data-tip]{cursor:help}
.att-tip{--bs-tooltip-max-width:280px}
.att-tip .tooltip-inner{max-width:280px;text-align:left;padding:.5rem .65rem;background:#1a202c;border-radius:8px}
.att-tip .tp-h{font-size:.75rem;color:#a0aec0;margin-bottom:.25rem}
.att-tip .tp-r{display:flex;gap:.5rem;justify-content:space-between;font-size:.8rem;line-height:1.5}
.att-tip .tp-k{color:#cbd5e1;white-space:nowrap}
.att-tip .tp-v{color:#fff;font-weight:500}
.att-tip .tp-note{font-size:.75rem;color:#cbd5e1;line-height:1.45;margin:.1rem 0 .25rem}
</style>

<script>
(function () {
    // tooltip แบบ delegate ตัวเดียวคุมทั้งตาราง (เลี่ยงการสร้าง instance หลายพันเซลล์ใน matrix)
    // เขียนเป็น inline script เพื่อให้ทำงานทั้งตอนโหลดหน้าเต็มและตอนถูกแทรกด้วย ajax/pjax
    // ตอนโหลดหน้าเต็ม script นี้รันก่อน bootstrap bundle (Yii วาง asset ไว้ท้าย body) จึงต้องรอ
    function initAttTip() {
        var scope = document.querySelector('.att-mtx__scroll');
        if (!scope || scope.dataset.tipReady) {
            return true;
        }
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            return false;
        }
        scope.dataset.tipReady = '1';
        new bootstrap.Tooltip(scope, {
            selector: '[data-tip]',
            html: true,
            container: 'body',
            placement: 'top',
            customClass: 'att-tip',
            trigger: 'hover focus'
        });
        return true;
    }

    if (!initAttTip()) {
        document.addEventListener('DOMContentLoaded', function () {
            if (!initAttTip()) {
                window.addEventListener('load', initAttTip); // เผื่อ bundle ถูกโหลดแบบ defer/async
            }
        });
    }

    // ---- ค้นหาชื่อ + เรียงลำดับ (ทำฝั่ง client — แถวทั้งหมดอยู่ใน DOM แล้ว ไม่ต้องโหลดหน้าใหม่) ----
    var table = document.getElementById('mtx-table');
    if (!table || table.dataset.toolsReady) {
        return;
    }
    table.dataset.toolsReady = '1';

    var tbody = table.tBodies[0];
    var rows = Array.prototype.slice.call(tbody.rows);
    var input = document.getElementById('mtx-search');
    var clearBtn = document.getElementById('mtx-search-clear');
    var countEl = document.getElementById('mtx-count');
    var resultEl = document.getElementById('mtx-result');
    var total = rows.length;
    var hintDefault = resultEl ? resultEl.textContent : '';

    function applyFilter() {
        var q = (input.value || '').trim().toLowerCase();
        var shown = 0;
        rows.forEach(function (tr) {
            var hit = !q || (tr.dataset.name || '').indexOf(q) !== -1;
            tr.hidden = !hit;
            if (hit) { shown++; }
        });
        if (countEl) { countEl.textContent = shown; }
        if (clearBtn) { clearBtn.hidden = !q; }
        if (resultEl) {
            resultEl.textContent = q
                ? (shown === 0 ? 'ไม่พบบุคลากรที่ตรงกับ "' + q + '"' : 'พบ ' + shown + ' จาก ' + total + ' คน')
                : hintDefault;
        }
    }

    if (input) {
        input.addEventListener('input', applyFilter);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && input.value) { input.value = ''; applyFilter(); }
        });
    }
    if (clearBtn) {
        clearBtn.addEventListener('click', function () { input.value = ''; applyFilter(); input.focus(); });
    }

    var sortKey = null, sortDesc = true;
    function applySort(key) {
        if (sortKey === key) {
            sortDesc = !sortDesc;
        } else {
            sortKey = key;
            sortDesc = key !== 'name'; // ตัวเลข = มากไปน้อยก่อน, ชื่อ = ก-ฮ ก่อน
        }
        var sorted = rows.slice().sort(function (a, b) {
            if (key === 'name') {
                return (a.dataset.name || '').localeCompare(b.dataset.name || '', 'th') * (sortDesc ? -1 : 1);
            }
            var d = (parseInt(b.dataset[key], 10) || 0) - (parseInt(a.dataset[key], 10) || 0);
            if (d === 0) { return (a.dataset.name || '').localeCompare(b.dataset.name || '', 'th'); }
            return d * (sortDesc ? 1 : -1);
        });
        var frag = document.createDocumentFragment();
        sorted.forEach(function (tr) { frag.appendChild(tr); });
        tbody.appendChild(frag);

        table.querySelectorAll('.mtx-sort').forEach(function (btn) {
            var on = btn.dataset.sort === key;
            btn.classList.toggle('is-sorted', on);
            var i = btn.querySelector('.mtx-sort__i');
            if (i) { i.className = 'bi mtx-sort__i ' + (on ? (sortDesc ? 'bi-sort-down' : 'bi-sort-up') : 'bi-arrow-down-up'); }
            var th = btn.closest('th');
            if (th) { th.setAttribute('aria-sort', on ? (sortDesc ? 'descending' : 'ascending') : 'none'); }
        });
    }
    table.querySelectorAll('.mtx-sort').forEach(function (btn) {
        btn.addEventListener('click', function () { applySort(btn.dataset.sort); });
    });

    // ---- Export Excel: confirm -> loading -> success (มาตรฐานเดียวกับ report อื่นในระบบ) ----
    var exportBtn = document.getElementById('mtx-export');
    if (exportBtn) {
        exportBtn.addEventListener('click', function (e) {
            if (exportBtn.classList.contains('disabled')) { e.preventDefault(); return; }
            if (!window.Swal) { return; } // fallback: ดาวน์โหลดตรงตาม href เดิม
            e.preventDefault();

            var url = exportBtn.getAttribute('href');
            var noMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var base = { customClass: { popup: 'att-swal', confirmButton: 'att-swal__btn', cancelButton: 'att-swal__btn' } };
            if (noMotion) { base.showClass = { popup: '' }; base.hideClass = { popup: '' }; }
            var mk = function (o) { return Object.assign({}, base, o); };

            Swal.fire(mk({
                title: 'Export สรุปการลงเวลาเป็น Excel?',
                html: '<div class="att-swal__meta">' + <?= json_encode(Html::encode($monthName . ' พ.ศ. ' . $yearBE), JSON_UNESCAPED_UNICODE) ?> + ' · บุคลากร ' + <?= (int)$personCount ?> + ' คน</div>',
                icon: 'question',
                iconColor: '#0d6efd',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-file-earmark-excel me-1"></i>Export',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                reverseButtons: false,
                focusConfirm: true
            })).then(function (r) {
                if (!r.isConfirmed) { return; }
                Swal.fire(mk({
                    title: 'กำลังสร้างไฟล์ Excel',
                    html: '<span class="att-swal__meta">กรุณารอสักครู่...</span>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () { Swal.showLoading(); }
                }));
                fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (resp) {
                        if (!resp.ok) { throw new Error('สร้างไฟล์ไม่สำเร็จ (สถานะ ' + resp.status + ')'); }
                        var cd = resp.headers.get('Content-Disposition') || '';
                        var m = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(cd);
                        var fname = m ? decodeURIComponent(m[1]) : 'attendance-monthly.xlsx';
                        return resp.blob().then(function (blob) { return { blob: blob, fname: fname }; });
                    })
                    .then(function (o) {
                        var objUrl = URL.createObjectURL(o.blob);
                        var a = document.createElement('a');
                        a.href = objUrl; a.download = o.fname;
                        document.body.appendChild(a); a.click(); document.body.removeChild(a);
                        setTimeout(function () { URL.revokeObjectURL(objUrl); }, 2000);
                        Swal.fire(mk({
                            icon: 'success', iconColor: '#198754',
                            title: 'ดาวน์โหลดเรียบร้อย',
                            html: '<span class="att-swal__meta">' + o.fname + '</span>',
                            timer: 1800, timerProgressBar: true, showConfirmButton: false
                        }));
                    })
                    .catch(function (err) {
                        Swal.fire(mk({ icon: 'error', title: 'ผิดพลาด', text: (err && err.message) || 'ดาวน์โหลดไม่สำเร็จ ลองอีกครั้ง' }));
                    });
            });
        });
    }
})();
</script>
