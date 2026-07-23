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
        case 'ontime': return '<i class="bi bi-check2 g-ontime" title="เข้า ' . Html::encode($cell['time']) . '"></i>';
        case 'late':   return '<i class="bi bi-clock-fill g-late" title="สาย ' . Html::encode($cell['time']) . '"></i>';
        case 'shift':  return '<i class="bi bi-circle-fill g-shift" title="เวร ' . Html::encode($cell['time']) . '"></i>';
        case 'absent': return '<span class="g-absent" title="ไม่มีการลงเวลา">—</span>';
        default:       return ''; // weekend / future
    }
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
                    <a href="<?= $excelUrl ?>" class="att-btn att-btn--success att-btn--sm"><i class="bi bi-file-earmark-excel"></i> ส่งออก Excel</a>
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
                <span>รวมมาสาย <strong class="<?= $totalLate > 0 ? 'text-late' : '' ?>"><?= $totalLate ?></strong> ครั้ง</span>
            </div>
            <div class="att-mtx__legend">
                <span class="lg"><i class="bi bi-check2 g-ontime"></i> ตรงเวลา</span>
                <span class="lg"><i class="bi bi-clock-fill g-late"></i> สาย</span>
                <span class="lg"><i class="bi bi-circle-fill g-shift"></i> เวร</span>
                <span class="lg"><span class="g-absent">—</span> ขาด</span>
                <span class="lg"><span class="lg-weekend"></span> วันหยุด</span>
            </div>
        </div>

        <!-- Matrix -->
        <?php if (empty($rows)): ?>
            <div class="att-mtx__empty">
                <p class="att-mtx__empty-title">ไม่พบบุคลากรตามเงื่อนไข</p>
                <p class="att-mtx__empty-sub">ลองเปลี่ยนกลุ่มงาน/ฝ่าย หรือเลือกเดือนอื่น</p>
            </div>
        <?php else: ?>
        <div class="att-mtx__scroll">
            <table class="mtx">
                <thead>
                    <tr>
                        <th class="mtx-name">รายชื่อ (<?= $personCount ?>)</th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <?php $w = (int)date('w', strtotime(sprintf('%04d-%02d-%02d', $yearCE, $month, $d))); ?>
                        <th class="mtx-day <?= $weekends[$d] ? 'is-weekend' : '' ?>">
                            <span class="mtx-day__num"><?= $d ?></span>
                            <span class="mtx-day__dow"><?= $dayNames[$w] ?></span>
                        </th>
                        <?php endfor; ?>
                        <th class="mtx-sum">รวมสาย</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td class="mtx-name">
                            <span class="mtx-name__title"><?= Html::encode($row['name']) ?></span>
                            <span class="mtx-name__sub">
                                <?= $row['position'] !== '' ? Html::encode($row['position']) : '<span class="text-muted">—</span>' ?>
                                <?php if ($row['shift'] === 'shift'): ?><span class="mtx-tag">เวร</span><?php endif; ?>
                            </span>
                        </td>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                        <td class="mtx-day <?= $weekends[$d] ? 'is-weekend' : '' ?>"><?= $glyph($row['cells'][$d]) ?></td>
                        <?php endfor; ?>
                        <td class="mtx-sum <?= $row['lateCount'] > 0 ? 'is-late' : '' ?>"><?= $row['lateCount'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
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

/* filter */
.att-mtx__filter{margin:0}
.att-mtx__filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr)) auto;gap:.6rem;align-items:end;padding:.9rem 1rem;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow-1)}
@media (max-width:820px){.att-mtx__filter-grid{grid-template-columns:1fr 1fr}.att-mtx__filter-actions{grid-column:1/-1}}
.att-mtx .att-field{min-width:0;margin:0}
.att-mtx .att-lbl{display:block;font-size:.78rem;font-weight:600;color:var(--ink-2);margin-bottom:.3rem}
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
.att-mtx__legend .lg-weekend{display:inline-block;width:14px;height:14px;border-radius:3px;background:var(--surface-3);border:1px solid var(--line)}

/* glyphs */
.att-mtx .g-ontime{color:var(--success)}
.att-mtx .g-late{color:var(--warning)}
.att-mtx .g-shift{color:var(--ink-4);font-size:.55rem;vertical-align:middle}
.att-mtx .g-absent{color:var(--ink-4)}

/* matrix table */
.att-mtx__scroll{overflow:auto;max-height:72vh;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow-1)}
.att-mtx .mtx{border-collapse:separate;border-spacing:0;width:max-content;min-width:100%;font-size:.8rem}
.att-mtx .mtx th,.att-mtx .mtx td{border-bottom:1px solid var(--line);border-right:1px solid var(--line)}
.att-mtx .mtx thead th{position:sticky;top:0;z-index:2;background:var(--surface-2);color:var(--ink-2);font-weight:600;text-align:center;padding:.35rem .2rem;vertical-align:middle}
.att-mtx .mtx-day{width:32px;min-width:32px;text-align:center;padding:.4rem 0}
.att-mtx .mtx thead .mtx-day{line-height:1.05}
.att-mtx .mtx-day__num{display:block;font-weight:400;color:var(--ink-1);font-variant-numeric:tabular-nums}
.att-mtx .mtx-day__dow{display:block;font-size:.62rem;color:var(--ink-3)}
.att-mtx .mtx th.is-weekend{background:var(--surface-3)}
.att-mtx .mtx td.is-weekend{background:var(--surface-2)}

/* sticky name column (left) */
.att-mtx .mtx-name{position:sticky;left:0;z-index:1;background:var(--surface);text-align:left;padding:.45rem .7rem;min-width:210px;max-width:230px}
.att-mtx .mtx thead .mtx-name{z-index:3;background:var(--surface-2)}
.att-mtx .mtx-name__title{display:block;font-weight:600;color:var(--ink-1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:214px}
.att-mtx .mtx-name__sub{display:flex;align-items:center;gap:.35rem;font-size:.74rem;color:var(--ink-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:214px}
.att-mtx .mtx-tag{flex:none;padding:.02rem .35rem;border-radius:999px;background:var(--surface-3);color:var(--ink-2);font-size:.66rem;font-weight:600}

/* sticky summary column (right) */
.att-mtx .mtx-sum{position:sticky;right:0;z-index:1;background:var(--surface);text-align:center;min-width:62px;font-variant-numeric:tabular-nums;font-weight:600;color:var(--ink-2);border-left:1px solid var(--line-strong)}
.att-mtx .mtx thead .mtx-sum{z-index:3;background:var(--surface-2);color:var(--ink-2);font-weight:600}
.att-mtx .mtx-sum.is-late{color:var(--warning);background:#fbe8cf}

/* row hover keeps sticky cells in sync */
.att-mtx .mtx tbody tr:hover td{background:var(--surface-hover)}
.att-mtx .mtx tbody tr:hover .mtx-name,.att-mtx .mtx tbody tr:hover .mtx-sum{background:var(--surface-hover)}
.att-mtx .mtx tbody tr:hover .mtx-sum.is-late{background:#f6ddb9}

/* buttons */
.att-mtx .att-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;min-height:40px;padding:.45rem .9rem;border:1px solid transparent;border-radius:var(--radius-sm);font-size:.9rem;font-weight:600;text-decoration:none;cursor:pointer;transition:background 140ms var(--ease),border-color 140ms var(--ease)}
.att-mtx .att-btn--sm{min-height:38px;padding:.4rem .8rem;font-size:.85rem}
.att-mtx .att-btn--primary{background:var(--primary);color:#fff;border-color:var(--primary)}
.att-mtx .att-btn--primary:hover{background:var(--primary-ink);border-color:var(--primary-ink)}
.att-mtx .att-btn--success{background:#15803d;color:#fff;border-color:#15803d}
.att-mtx .att-btn--success:hover{background:#116631;border-color:#116631;color:#fff}

/* empty */
.att-mtx__empty{padding:3rem 1.5rem;text-align:center;border:1px solid var(--line);border-radius:var(--radius);background:var(--surface);box-shadow:var(--shadow-1)}
.att-mtx__empty-title{margin:0;font-weight:600;color:var(--ink-2);font-size:1.05rem}
.att-mtx__empty-sub{margin:.3rem 0 0;font-size:.88rem;color:var(--ink-3)}

@media (prefers-reduced-motion:reduce){.att-mtx .att-btn{transition:none}}
</style>
