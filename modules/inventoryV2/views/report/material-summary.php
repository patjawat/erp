<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var int $year ค.ศ. ที่เก็บใน DB */
/** @var int $month */
/** @var int|null $warehouseId */
/** @var array $warehouses */
/** @var array $rows */
/** @var bool $hasData */
/** @var array{closed_at: int|null, closed_by_name: string|null}|null $closeMeta */

$this->title = 'สรุปรายงานวัสดุคงคลัง';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$monthNames = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];
$periodLabel = isset($monthNames[$month]) ? $monthNames[$month] . ' ' . ($year + 543) . ' (พ.ศ.)' : '';
$closedAtLabel = '';
if ($hasData && !empty($closeMeta['closed_at'])) {
    $closedAtLabel = date('d/m/', (int) $closeMeta['closed_at']) . (date('Y', (int) $closeMeta['closed_at']) + 543) . ' เวลา ' . date('H:i', (int) $closeMeta['closed_at']);
}

// คอลัมน์ที่ accountant ต้อง audit (clickable cells)
// ใช้ key เดียวกับ DB / kind ของ actionCategoryDrilldown
$drillKinds = ['opening', 'in', 'out_sub', 'out_hosp', 'total_out', 'closing'];

/**
 * Format ตัวเลข — ถ้า < 0 ห่อด้วย <span class="is-negative"> (สีแดง)
 * ถ้า ~ 0 (|val| < 0.005) คืน em-dash (ใช้ cell-zero styling)
 */
$fmtSigned = function ($value) {
    $v = (float) $value;
    if (abs($v) < 0.005) {
        return '<span class="cell-zero" aria-label="ไม่มียอด">—</span>';
    }
    $formatted = number_format($v, 2);
    if ($v < 0) {
        return '<span class="is-negative" aria-label="ติดลบ">' . $formatted . '</span>';
    }
    return $formatted;
};

/**
 * Render cell ตัวเลข — ถ้า value = 0 แสดง em-dash ไม่ทำ button
 * ถ้า kind อยู่ใน $drillKinds → ทำเป็น button .cell-drill (เปิด modal)
 * อย่างอื่น → static text
 */
$renderCell = function ($value, $kind, $categoryCode) use ($drillKinds) {
    $v = (float) $value;
    if (abs($v) < 0.005) {
        return '<span class="cell-zero" aria-label="ไม่มียอด">—</span>';
    }
    $formatted = number_format($v, 2);
    $negCls = $v < 0 ? ' is-negative' : '';
    if (!in_array($kind, $drillKinds, true)) {
        return $negCls !== '' ? '<span class="is-negative" aria-label="ติดลบ">' . $formatted . '</span>' : $formatted;
    }
    return '<button type="button" class="cell-drill' . $negCls . '"'
        . ' data-kind="' . Html::encode($kind) . '"'
        . ' data-category="' . Html::encode($categoryCode) . '"'
        . ' aria-label="' . ($v < 0 ? 'ติดลบ ' : '') . 'ดูรายการ ' . Html::encode($kind) . ' ของประเภท ' . Html::encode($categoryCode) . '">'
        . $formatted
        . '</button>';
};
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-journal-text fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">รายงานสรุปตามประเภทวัสดุ สำหรับส่งบัญชี เลือกเดือนและคลัง แล้วกดปิดเดือนหากยังไม่มีข้อมูล</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4 ms-report-summary">
    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 px-4">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/material-summary']) ?>" id="form-material-summary">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-auto">
                        <label class="form-label small text-muted mb-1" for="ms-month">เดือน / ปีงบประมาณ (พ.ศ.)</label>
                        <div class="d-flex gap-2 flex-nowrap">
                            <select id="ms-month" name="month" class="form-select" style="min-width: 130px;">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="ms-year" name="year" class="form-select" aria-label="ปี พ.ศ." style="min-width: 110px;">
                                <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                                    <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?> (พ.ศ.)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <label class="form-label small text-muted mb-1" for="ms-warehouse">คลัง</label>
                        <select id="ms-warehouse" name="warehouse_id" class="form-select" style="min-width: 200px;">
                            <?php foreach ($warehouses as $wid => $wname): ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md d-flex flex-wrap gap-2 justify-content-md-end">
                        <button type="submit" class="btn btn-primary px-3">
                            <i class="bi bi-search me-1"></i> แสดงรายงาน
                        </button>
                        <?php if ($hasData): ?>
                            <a href="<?= Url::to(array_merge(['/inventory-v2/report/export-excel'], ['year' => $year, 'month' => $month], $warehouseId ? ['warehouse_id' => $warehouseId] : [])) ?>" class="btn btn-success px-3">
                                <i class="bi bi-file-earmark-excel me-1"></i> Excel
                            </a>
                        <?php endif; ?>
                        <a href="<?= Url::to(array_merge(['/inventory-v2/report/material-by-item'], ['year' => $year, 'month' => $month], $warehouseId ? ['warehouse_id' => $warehouseId] : [])) ?>" class="btn btn-outline-secondary px-3">
                            <i class="bi bi-list-ul me-1"></i> แยกรายการ
                        </a>
                        <button type="button" class="btn btn-outline-secondary px-3" id="btn-close-month" data-bs-toggle="modal" data-bs-target="#modal-close-month">
                            <i class="bi bi-calendar-check me-1"></i> ปิดเดือน
                        </button>
                        <button type="button" class="btn btn-outline-primary px-3" id="btn-cm-doctor" data-bs-toggle="modal" data-bs-target="#modal-cm-doctor">
                            <i class="bi bi-clipboard2-pulse me-1"></i> ตรวจ &amp; ซ่อมปิดเดือน
                        </button>
                        <?php if ($hasData): ?>
                            <button type="button" class="btn btn-outline-danger px-3" id="btn-cancel-close"
                                    data-year="<?= (int)$year ?>" data-month="<?= (int)$month ?>" data-warehouse="<?= $warehouseId ? (int)$warehouseId : '' ?>">
                                <i class="bi bi-x-circle me-1"></i> ยกเลิกปิดเดือน
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!$hasData): ?>
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            ยังไม่มีข้อมูลปิดเดือนสำหรับเดือน<?= isset($monthNames[$month]) ? ' ' . $monthNames[$month] . ' ' . ($year + 543) . ' (พ.ศ.)' : '' ?> กรุณาเลือกคลังด้านบน แล้วกด <strong>ปิดเดือน</strong> เพื่อคำนวณและบันทึกข้อมูล
        </div>
    <?php else: ?>
        <div class="card border shadow-sm rounded-3">
            <div class="ms-report-summary__head">
                <div class="ms-report-summary__head-titles">
                    <h6 class="mb-0 fw-semibold text-body">สรุปยอดวัสดุคงคลัง <?= Html::encode($periodLabel) ?></h6>
                    <span class="ms-report-summary__head-hint">หน่วยตัวเลข: บาท · คลิกตัวเลขเพื่อดูรายการรายตัว</span>
                </div>
                <?php if ($closedAtLabel !== ''): ?>
                    <span class="ms-report-summary__closed-badge" title="วันเวลาที่ปิดเดือนล่าสุด">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                        ปิดเดือนแล้ว
                        <span class="ms-report-summary__closed-badge-meta">
                            <?= Html::encode($closedAtLabel) ?>
                            <?= !empty($closeMeta['closed_by_name']) ? ' โดย ' . Html::encode($closeMeta['closed_by_name']) : '' ?>
                        </span>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 ms-report-summary__table">
                        <thead>
                            <tr>
                                <th style="width: 4%;" class="text-center">#</th>
                                <th style="width: 22%;">รายการ</th>
                                <th style="width: 10%;" class="text-end">สินค้าคงเหลือ (บาท)</th>
                                <th style="width: 10%;" class="text-end">ซื้อระหว่างเดือน (บาท)</th>
                                <th style="width: 10%;" class="text-end">รวม (บาท)</th>
                                <th style="width: 10%;" class="text-end">
                                    จ่ายส่วนของ รพ.สต. (บาท)
                                    <i class="bi bi-info-circle ms-report-summary__th-info"
                                       data-bs-toggle="tooltip"
                                       title="คลังย่อยที่กำหนดใน params: inventoryV2.disburseSubWarehouseIds"></i>
                                </th>
                                <th style="width: 12%;" class="text-end">จ่ายส่วนของโรงพยาบาล (บาท)</th>
                                <th style="width: 10%;" class="text-end">รวมจ่าย (บาท)</th>
                                <th style="width: 12%;" class="text-end">ยอดยกไป (บาท)</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php
                            $totOpening = $totIn = $totOutSub = $totOutHosp = $totOut = $totClosing = 0;
                            foreach ($rows as $i => $r):
                                $totOpening += $r['opening_value'];
                                $totIn += $r['in_value'];
                                $totOutSub += $r['out_sub_value'];
                                $totOutHosp += $r['out_hosp_value'];
                                $totOut += $r['total_out_value'];
                                $totClosing += $r['closing_value'];
                                $totalAvail = $r['opening_value'] + $r['in_value'];
                                $catCode = $r['category_code'];
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $i + 1 ?></td>
                                    <td><?= Html::encode($r['category_label']) ?></td>
                                    <td class="text-end"><?= $renderCell($r['opening_value'], 'opening', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['in_value'], 'in', $catCode) ?></td>
                                    <td class="text-end"><?= $fmtSigned($totalAvail) ?></td>
                                    <td class="text-end"><?= $renderCell($r['out_sub_value'], 'out_sub', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['out_hosp_value'], 'out_hosp', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['total_out_value'], 'total_out', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['closing_value'], 'closing', $catCode) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="ms-report-summary__total-row">
                                <td class="text-center"></td>
                                <td>รวม</td>
                                <td class="text-end"><?= $fmtSigned($totOpening) ?></td>
                                <td class="text-end"><?= $fmtSigned($totIn) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOpening + $totIn) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOutSub) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOutHosp) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOut) ?></td>
                                <td class="text-end"><?= $fmtSigned($totClosing) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal ปิดเดือน — wizard 3 สเต็ป (เลือกงวด → ตรวจสอบ → ยืนยัน) -->
<div class="modal fade" id="modal-close-month" tabindex="-1" aria-labelledby="modal-close-month-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content cm-modal" data-step="1">
            <header class="cm-modal__head">
                <div class="cm-modal__head-row">
                    <h5 class="cm-modal__title" id="modal-close-month-title">
                        <i class="bi bi-calendar-check" aria-hidden="true"></i> ปิดเดือน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <ol class="cm-stepper" aria-label="ขั้นตอนการปิดเดือน">
                    <li class="cm-step is-active" data-step="1">
                        <span class="cm-step__indicator"><span class="cm-step__num">1</span><i class="cm-step__check bi bi-check-lg" aria-hidden="true"></i></span>
                        <span class="cm-step__label">เลือกงวด</span>
                    </li>
                    <li class="cm-step" data-step="2">
                        <span class="cm-step__indicator"><span class="cm-step__num">2</span><i class="cm-step__check bi bi-check-lg" aria-hidden="true"></i></span>
                        <span class="cm-step__label">ตรวจสอบข้อมูล</span>
                    </li>
                    <li class="cm-step" data-step="3">
                        <span class="cm-step__indicator"><span class="cm-step__num">3</span><i class="cm-step__check bi bi-check-lg" aria-hidden="true"></i></span>
                        <span class="cm-step__label">ยืนยัน</span>
                    </li>
                </ol>
            </header>

            <div class="modal-body cm-modal__body">
                <!-- ── สเต็ป 1: เลือกงวด ── -->
                <section class="cm-panel" data-step="1" aria-label="เลือกงวด">
                    <p class="cm-panel__lead">เลือกคลังและเดือนที่ต้องการปิด ระบบจะคำนวณยอดจากธุรกรรมรับ-จ่ายในงวดนั้นให้ตรวจสอบก่อนบันทึก</p>
                    <div class="cm-field-grid">
                        <div class="cm-field">
                            <label class="cm-field__label" for="close-warehouse-id">คลัง</label>
                            <select id="close-warehouse-id" class="form-select cm-select">
                                <option value="">เลือกคลัง</option>
                                <option value="all">ปิดรวมทุกคลังหลัก</option>
                                <?php foreach ($warehouses as $wid => $wname): ?>
                                    <?php if ($wid !== ''): ?>
                                        <option value="<?= (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <span class="cm-field__hint" id="cm-warehouse-hint" hidden>กรุณาเลือกคลังหรือเลือกปิดรวมทุกคลัง</span>
                        </div>
                        <div class="cm-field">
                            <label class="cm-field__label" for="close-month">เดือน / ปีงบประมาณ (พ.ศ.)</label>
                            <div class="cm-field__pair">
                                <select id="close-month" class="form-select cm-select" aria-label="เดือน">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select id="close-year" class="form-select cm-select" aria-label="ปี พ.ศ.">
                                    <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                                        <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?> (พ.ศ.)</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── สเต็ป 2: ตรวจสอบข้อมูล ── -->
                <section class="cm-panel" data-step="2" aria-label="ตรวจสอบข้อมูล" hidden>
                    <div class="cm-preview" id="cm-preview" aria-live="polite" aria-busy="true">
                        <!-- injected by JS: skeleton → summary + warnings + table -->
                    </div>
                </section>

                <!-- ── สเต็ป 3: ยืนยัน / ผลลัพธ์ ── -->
                <section class="cm-panel cm-panel--result" data-step="3" aria-label="ผลลัพธ์" hidden>
                    <div class="cm-result" id="cm-result" aria-live="polite">
                        <!-- injected by JS -->
                    </div>
                </section>
            </div>

            <footer class="cm-modal__foot">
                <!-- สเต็ป 1 -->
                <div class="cm-foot-group" data-step="1">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary cm-btn-next" id="cm-to-preview">
                        ถัดไป: ดูตัวอย่าง <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
                <!-- สเต็ป 2 -->
                <div class="cm-foot-group" data-step="2" hidden>
                    <button type="button" class="btn btn-outline-secondary" id="cm-back">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> ย้อนกลับ
                    </button>
                    <button type="button" class="btn btn-primary cm-btn-confirm" id="cm-confirm" disabled>
                        <span class="cm-btn-confirm__label"><i class="bi bi-calendar-check" aria-hidden="true"></i> ยืนยันปิดเดือน</span>
                        <span class="cm-btn-confirm__spinner spinner-border spinner-border-sm" role="status" aria-hidden="true" hidden></span>
                    </button>
                </div>
                <!-- สเต็ป 3 -->
                <div class="cm-foot-group" data-step="3" hidden>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </footer>
        </div>
    </div>
</div>

<!-- Modal Doctor: ตรวจ & ซ่อมปิดเดือน (วิเคราะห์ทั้งช่วงตั้งแต่ต้นจนถึงงวดที่เลือก) -->
<div class="modal fade" id="modal-cm-doctor" tabindex="-1" aria-labelledby="modal-cm-doctor-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content cm-modal">
            <header class="cm-modal__head">
                <div class="cm-modal__head-row">
                    <h5 class="cm-modal__title" id="modal-cm-doctor-title">
                        <i class="bi bi-clipboard2-pulse" aria-hidden="true"></i> ตรวจ &amp; ซ่อมปิดเดือน
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
            </header>
            <div class="modal-body cm-modal__body">
                <p class="cm-panel__lead">
                    ไล่คำนวณทุกงวดตั้งแต่งวดแรกที่มีเอกสารจนถึงงวดที่เลือก (ไม่แก้ข้อมูลจนกว่าจะกดซ่อม) แล้วจำแนกพัสดุที่มีปัญหา
                    — <strong>มูลค่าติดลบเทียม</strong> ซ่อมอัตโนมัติได้ด้วยการปิดเดือนใหม่แบบต้นทุนถัวเฉลี่ย ส่วน
                    <strong>จำนวนติดลบ</strong> ต้องตรวจ order_date / ปรับยอดเอง
                </p>
                <div class="cm-field-grid">
                    <div class="cm-field">
                        <label class="cm-field__label" for="cmd-warehouse-id">คลัง</label>
                        <select id="cmd-warehouse-id" class="form-select cm-select">
                            <option value="">เลือกคลัง</option>
                            <option value="all">ทุกคลังหลัก</option>
                            <?php foreach ($warehouses as $wid => $wname): ?>
                                <?php if ($wid !== ''): ?>
                                    <option value="<?= (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cm-field">
                        <label class="cm-field__label" for="cmd-month">ถึงงวด / ปีงบประมาณ (พ.ศ.)</label>
                        <div class="cm-field__pair">
                            <select id="cmd-month" class="form-select cm-select" aria-label="เดือน">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="cmd-year" class="form-select cm-select" aria-label="ปี พ.ศ.">
                                <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                                    <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?> (พ.ศ.)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="cm-field" style="align-self:end">
                        <button type="button" class="btn btn-primary" id="cmd-analyze">
                            <i class="bi bi-search" aria-hidden="true"></i> วิเคราะห์
                        </button>
                    </div>
                </div>
                <div id="cmd-result" class="mt-3" aria-live="polite"><!-- injected by JS --></div>
            </div>
            <footer class="cm-modal__foot" style="justify-content:space-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-success" id="cmd-autofix" disabled>
                    <span class="cmd-autofix__label"><i class="bi bi-wrench-adjustable" aria-hidden="true"></i> ซ่อมมูลค่าอัตโนมัติ (ปิดใหม่ทั้งช่วง)</span>
                    <span class="cmd-autofix__spinner spinner-border spinner-border-sm" role="status" aria-hidden="true" hidden></span>
                </button>
            </footer>
        </div>
    </div>
</div>

<!-- Modal ยกเลิกปิดเดือน (destructive — ต้องยืนยัน) -->
<div class="modal fade" id="modal-cancel-close" tabindex="-1" aria-labelledby="modal-cancel-close-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cx-modal">
            <header class="cx-modal__head">
                <span class="cx-modal__icon"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i></span>
                <div>
                    <h5 class="cx-modal__title" id="modal-cancel-close-title">ยกเลิกการปิดเดือน</h5>
                    <p class="cx-modal__caption" id="cx-context">—</p>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </header>
            <div class="modal-body cx-modal__body">
                <div class="cx-load" id="cx-load">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังตรวจสอบ…
                </div>
                <div id="cx-detail" hidden>
                    <p class="cx-lead">
                        การกระทำนี้จะ<strong>ลบข้อมูลรายงานที่ปิดไว้</strong> <span id="cx-rowcount" class="cx-strong">0</span> แถว
                        แล้วงวดนี้จะกลับไปเป็น "ยังไม่ปิด" <span class="cx-irrev">กู้คืนไม่ได้ ต้องปิดเดือนใหม่</span>
                    </p>
                    <div class="cm-alert cm-alert--warning" id="cx-later-warn" role="alert" hidden>
                        <i class="cm-alert__icon bi bi-diagram-3" aria-hidden="true"></i>
                        <div class="cm-alert__body">
                            <div class="cm-alert__title" id="cx-later-title">มีงวดถัดไปที่ปิดไปแล้ว</div>
                            <div>ยอดยกมาของงวดเหล่านี้ผูกกับยอดยกไปของงวดที่กำลังยกเลิก หลังยกเลิกต้อง<strong>ปิดงวดเหล่านี้ใหม่</strong>ให้ยอดตรง</div>
                            <ul class="cm-alert__list" id="cx-later-list"></ul>
                        </div>
                    </div>
                </div>
                <div class="cx-error" id="cx-error" hidden></div>
            </div>
            <footer class="cx-modal__foot">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ไม่ยกเลิก</button>
                <button type="button" class="btn cx-btn-danger" id="cx-confirm" disabled>
                    <span class="cx-btn-danger__label"><i class="bi bi-x-circle" aria-hidden="true"></i> ยืนยันยกเลิกการปิดเดือน</span>
                    <span class="cx-btn-danger__spinner spinner-border spinner-border-sm" role="status" aria-hidden="true" hidden></span>
                </button>
            </footer>
        </div>
    </div>
</div>

<?php // Modal: ประวัติการเคลื่อนไหววัสดุ — partial ร่วมกับหน้า balance (ดูประวัติ + ปรับยอด/แก้ไข) เปิดจากรายการติดลบใน preview ?>
<?= $this->render('@app/modules/inventoryV2/views/report/_item_history_modal', [
    'historyUrl' => Url::to(['/inventory-v2/report/item-history']),
    'exportHistoryUrl' => Url::to(['/inventory-v2/report/export-item-history']),
]) ?>

<!-- Modal: ดูรายการ item ที่ประกอบเป็นยอดของ cell ที่คลิก -->
<div class="modal fade" id="categoryDrillModal" tabindex="-1" aria-labelledby="categoryDrillModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content cd-modal">
            <header class="cd-modal__head">
                <div class="cd-modal__head-row">
                    <span class="cd-modal__kind-badge" id="cd-kind-badge">
                        <i class="bi bi-arrow-down-left-circle" aria-hidden="true"></i>
                        <span id="cd-kind-label">—</span>
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <h5 id="categoryDrillModalTitle" class="cd-modal__title">
                    <span id="cd-category-label">ประเภทวัสดุ</span>
                </h5>
                <p class="cd-modal__caption">
                    <span id="cd-period">—</span>
                    <span class="cd-modal__caption-sep" aria-hidden="true">·</span>
                    <span id="cd-warehouse">—</span>
                </p>
                <div class="cd-modal__stat-strip" id="cd-stat-strip" hidden>
                    <span class="cd-modal__stat">
                        <span class="cd-modal__stat-label">รายการ</span>
                        <span class="cd-modal__stat-value" id="cd-count">0</span>
                        <span class="cd-modal__stat-unit">ชนิด</span>
                    </span>
                    <span class="cd-modal__stat-sep" aria-hidden="true"></span>
                    <span class="cd-modal__stat cd-modal__stat--total">
                        <span class="cd-modal__stat-label">รวมมูลค่า</span>
                        <span class="cd-modal__stat-value" id="cd-total">0.00</span>
                        <span class="cd-modal__stat-unit">บาท</span>
                    </span>
                </div>
            </header>

            <div class="cd-modal__filter" id="cd-filter" hidden>
                <label for="cd-search" class="visually-hidden">ค้นหา</label>
                <div class="cd-modal__search-wrap">
                    <i class="bi bi-search cd-modal__search-icon" aria-hidden="true"></i>
                    <input type="search"
                           id="cd-search"
                           class="cd-modal__search"
                           placeholder="ค้นหารหัส / ชื่อวัสดุ"
                           autocomplete="off"
                           aria-controls="cd-body">
                    <button type="button" class="cd-modal__search-clear" id="cd-search-clear" aria-label="ล้างค้นหา" hidden>
                        <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                    </button>
                </div>
                <span class="cd-modal__filter-meta" id="cd-filter-meta" aria-live="polite"></span>
            </div>

            <div class="cd-modal__body" id="cd-body" aria-live="polite" aria-busy="false">
                <!-- injected by JS -->
            </div>

            <footer class="cd-modal__foot">
                <span class="cd-modal__foot-hint">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    มูลค่าจ่ายออกอ้างราคาทุนตามล็อตที่บันทึกตอนปิดเดือน
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">ปิด</button>
            </footer>
        </div>
    </div>
</div>

<style>
/* Tokens — ใช้ร่วมกับ modal ที่อยู่นอก .ms-report-summary scope (Bootstrap modal positioned fixed) */
.ms-report-summary,
#categoryDrillModal,
#modal-close-month,
#modal-cancel-close {
    --ms-ink-1: #1a202c;
    --ms-ink-2: #4a5568;
    --ms-ink-3: #718096;
    --ms-ink-4: #a0aec0;
    --ms-surface: #ffffff;
    --ms-surface-2: #f7f9fc;
    --ms-surface-3: #eef2f7;
    --ms-line: rgba(15, 23, 42, 0.08);
    --ms-line-strong: rgba(15, 23, 42, 0.18);
    --ms-primary: #0d6efd;
    --ms-primary-ink: #0a58ca;
    --ms-primary-soft: rgba(13, 110, 253, 0.08);
    --ms-success: #15803d;
    --ms-success-soft: rgba(21, 128, 61, 0.10);
    --ms-warning: #b45309;
    --ms-warning-soft: rgba(180, 83, 9, 0.10);
    --ms-danger: #b91c1c;
    --ms-danger-soft: rgba(185, 28, 28, 0.10);
    --ms-radius: 10px;
    --ms-radius-sm: 8px;
    --ms-ease: cubic-bezier(0.16, 1, 0.3, 1);
}

.ms-report-summary__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1.1rem;
    background: var(--ms-surface-2);
    border-bottom: 1px solid var(--ms-line);
    flex-wrap: wrap;
}
.ms-report-summary__head-titles { display: flex; flex-direction: column; gap: 0.15rem; }
.ms-report-summary__head-hint { font-size: 0.78rem; color: var(--ms-ink-3); }

.ms-report-summary__closed-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    background: var(--ms-success-soft);
    color: var(--ms-success);
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.2;
}
.ms-report-summary__closed-badge i { font-size: 0.85rem; }
.ms-report-summary__closed-badge-meta {
    color: var(--ms-ink-2);
    font-weight: 500;
    padding-left: 0.4rem;
    border-left: 1px solid rgba(21, 128, 61, 0.25);
}

.ms-report-summary__th-info { color: var(--ms-ink-4); cursor: help; margin-left: 0.2rem; }

.ms-report-summary__table thead th {
    background: var(--ms-surface-2);
    color: var(--ms-ink-2);
    font-weight: 600;
    font-size: 0.85rem;
    border-bottom: 1px solid var(--ms-line);
}
.ms-report-summary__table tbody td { font-size: 0.9rem; color: var(--ms-ink-1); }
.ms-report-summary__table tbody tr:hover td { background: var(--ms-surface-3); }
.ms-report-summary__table .text-end { font-variant-numeric: tabular-nums; }

.ms-report-summary__total-row td {
    background: var(--ms-surface-2);
    color: var(--ms-ink-1);
    font-weight: 700;
    border-top: 2px solid var(--ms-line);
}

/* Clickable cell button */
.ms-report-summary .cell-drill {
    appearance: none;
    border: none;
    background: transparent;
    padding: 0.1rem 0.35rem;
    margin: -0.1rem -0.35rem;
    border-radius: var(--ms-radius-sm);
    font: inherit;
    color: var(--ms-primary-ink);
    cursor: pointer;
    font-variant-numeric: tabular-nums;
    transition: background-color 120ms var(--ms-ease), color 120ms var(--ms-ease);
}
.ms-report-summary .cell-drill:hover { background: var(--ms-primary-soft); text-decoration: underline; }
.ms-report-summary .cell-drill:focus-visible { outline: 2px solid var(--ms-primary); outline-offset: 1px; }
.ms-report-summary .cell-zero { color: var(--ms-ink-4); }

/* Negative numbers — สีแดงทั้งในตารางสรุปและใน drill-down modal */
.ms-report-summary .is-negative,
.cd-modal .is-negative,
.cd-modal__num.is-negative { color: #b91c1c; }
.ms-report-summary .cell-drill.is-negative { color: #b91c1c; }
.ms-report-summary .cell-drill.is-negative:hover { background: rgba(185, 28, 28, 0.08); }

@media (prefers-reduced-motion: reduce) {
    .ms-report-summary .cell-drill { transition: none; }
}

/* Drill-down modal — head + summary + filter sticky, body scroll, foot sticky */
.cd-modal {
    border-radius: var(--ms-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 100%;
}
.cd-modal__head { padding: 1rem 1.25rem 0.5rem; flex: 0 0 auto; }
.cd-modal__head-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; }
.cd-modal__kind-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    background: var(--ms-primary-soft);
    color: var(--ms-primary-ink);
    font-size: 0.78rem;
    font-weight: 600;
}
.cd-modal__title { margin: 0; font-size: 1.05rem; font-weight: 600; color: var(--ms-ink-1); }
.cd-modal__caption { margin: 0.25rem 0 0; color: var(--ms-ink-3); font-size: 0.85rem; }
.cd-modal__caption-sep { margin: 0 0.4rem; color: var(--ms-ink-4); }

/* Inline stat strip (replaces nested summary card) */
.cd-modal__stat-strip {
    display: flex; align-items: baseline; gap: 0.75rem;
    margin-top: 0.6rem;
    padding-top: 0.55rem;
    border-top: 1px dashed var(--ms-line);
}
.cd-modal__stat { display: inline-flex; align-items: baseline; gap: 0.35rem; }
.cd-modal__stat-label { color: var(--ms-ink-3); font-size: 0.78rem; }
.cd-modal__stat-value { color: var(--ms-ink-1); font-weight: 700; font-size: 1rem; font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }
.cd-modal__stat-unit { color: var(--ms-ink-3); font-size: 0.72rem; }
.cd-modal__stat--total { margin-left: auto; }
.cd-modal__stat--total .cd-modal__stat-value { font-size: 1.15rem; }
.cd-modal__stat--total .cd-modal__stat-value.is-negative { color: #b91c1c; }
.cd-modal__stat-sep { width: 1px; align-self: stretch; background: var(--ms-line); }

.cd-modal__body {
    padding: 0.5rem 1.25rem 1rem;
    flex: 1 1 auto;
    overflow-y: auto;
    min-height: 0; /* allow flex child to shrink under content */
}
/* Sticky thead — ทึบจริง + เงาด้านล่างเพื่อบอก boundary ตอน content เลื่อนใต้ */
.cd-modal__body thead th {
    position: sticky;
    top: 0;
    background-color: #ffffff;
    z-index: 2;
    color: var(--ms-ink-2);
    border-bottom: 1px solid var(--ms-line-strong);
    box-shadow: 0 2px 4px -2px rgba(15, 23, 42, 0.08);
}
.cd-modal__body td.cd-modal__num { font-variant-numeric: tabular-nums; white-space: nowrap; }
.cd-modal__body tbody tr.is-hit-row mark { background: rgba(13, 110, 253, 0.18); color: var(--ms-ink-1); padding: 0 1px; border-radius: 2px; }

/* Image cell */
.cd-modal__img-cell { width: 56px; padding: 0.5rem 0.4rem !important; }
.cd-modal__img {
    width: 44px; height: 44px;
    object-fit: cover; object-position: center;
    border-radius: var(--ms-radius-sm);
    border: 1px solid var(--ms-line);
    background: var(--ms-surface-2);
    display: block;
}

/* Item meta cell */
.cd-modal__item-meta { display: flex; flex-direction: column; gap: 0.1rem; min-width: 0; }
.cd-modal__item-code { color: var(--ms-ink-3); font-size: 0.74rem; font-variant-numeric: tabular-nums; letter-spacing: 0.01em; }
.cd-modal__item-name { color: var(--ms-ink-1); font-size: 0.9rem; font-weight: 500; line-height: 1.3; }

/* Percent bar under value */
.cd-modal__value-stack { display: inline-flex; flex-direction: column; align-items: flex-end; gap: 0.25rem; min-width: 7rem; }
.cd-modal__value-num { font-variant-numeric: tabular-nums; }
.cd-modal__pct-bar {
    width: 100%;
    height: 3px;
    border-radius: 999px;
    background: var(--ms-line);
    position: relative;
    overflow: hidden;
}
.cd-modal__pct-fill {
    position: absolute; top: 0; bottom: 0; right: 0;
    width: var(--pct, 0%);
    background: var(--ms-primary);
    border-radius: 999px;
    transition: width 240ms var(--ms-ease);
}
.cd-modal__pct-fill.is-negative { background: #b91c1c; }
@media (prefers-reduced-motion: reduce) { .cd-modal__pct-fill { transition: none; } }

/* Row stagger reveal (only on initial load — flagged via .is-stagger) */
@keyframes cd-row-in {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: none; }
}
.cd-modal__body tbody tr.is-stagger {
    animation: cd-row-in 220ms var(--ms-ease) backwards;
    animation-delay: calc(var(--i, 0) * 14ms);
}
@media (prefers-reduced-motion: reduce) {
    .cd-modal__body tbody tr.is-stagger { animation: none; }
}

/* Filter crossfade for tbody (when re-rendering after typing) */
.cd-modal__body tbody.is-filter-fade { animation: cd-row-fade 140ms var(--ms-ease) backwards; }
@keyframes cd-row-fade { from { opacity: 0.35; } to { opacity: 1; } }
@media (prefers-reduced-motion: reduce) { .cd-modal__body tbody.is-filter-fade { animation: none; } }

/* Filter / search */
.cd-modal__filter {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0.75rem 1.25rem 0;
    flex: 0 0 auto;
}
.cd-modal__search-wrap { position: relative; flex: 1 1 auto; min-width: 0; }
.cd-modal__search-icon {
    position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
    color: var(--ms-ink-3); font-size: 0.9rem; pointer-events: none;
}
.cd-modal__search {
    width: 100%;
    height: 40px;
    padding: 0.4rem 2.4rem 0.4rem 2.3rem;
    border: 1px solid var(--ms-line-strong);
    border-radius: var(--ms-radius-sm);
    background-color: #ffffff;
    color: var(--ms-ink-1);
    font-size: 0.92rem;
    transition: border-color 120ms var(--ms-ease), box-shadow 120ms var(--ms-ease);
}
.cd-modal__search::placeholder { color: var(--ms-ink-3); opacity: 1; }
.cd-modal__search:hover { border-color: var(--ms-ink-3); }
.cd-modal__search:focus,
.cd-modal__search:focus-visible {
    outline: none;
    border-color: var(--ms-primary);
    box-shadow: 0 0 0 3px var(--ms-primary-soft);
}
.cd-modal__search:focus + .cd-modal__search-clear,
.cd-modal__search-wrap:focus-within .cd-modal__search-icon { color: var(--ms-primary); }
.cd-modal__search-clear {
    position: absolute; right: 0.4rem; top: 50%; transform: translateY(-50%);
    border: 0; background: transparent; color: var(--ms-ink-4);
    padding: 0.2rem; line-height: 1; cursor: pointer; border-radius: 999px;
    transition: color 120ms var(--ms-ease);
}
.cd-modal__search-clear:hover { color: var(--ms-ink-2); }
.cd-modal__filter-meta { color: var(--ms-ink-3); font-size: 0.78rem; white-space: nowrap; flex: 0 0 auto; }

@media (prefers-reduced-motion: reduce) {
    .cd-modal__search, .cd-modal__search-clear { transition: none; }
}

/* Skeleton — match real row shape: square thumb + two stacked lines + two number cells */
.cd-modal__skeleton { display: flex; flex-direction: column; gap: 0.5rem; padding: 0.5rem 0; }
.cd-modal__skeleton-row {
    display: grid;
    grid-template-columns: 44px 1fr 5rem 7rem;
    gap: 0.75rem;
    align-items: center;
    padding: 0.4rem 0.25rem;
}
.cd-modal__skeleton-thumb { width: 44px; height: 44px; border-radius: var(--ms-radius-sm); }
.cd-modal__skeleton-lines { display: flex; flex-direction: column; gap: 0.35rem; }
.cd-modal__skeleton-line { height: 0.7rem; border-radius: 999px; }
.cd-modal__skeleton-line--sm { height: 0.55rem; width: 35%; }
.cd-modal__skeleton-line--md { width: 75%; }
.cd-modal__skeleton-num { height: 0.7rem; border-radius: 999px; justify-self: end; width: 80%; }
.cd-modal__skeleton-thumb,
.cd-modal__skeleton-line,
.cd-modal__skeleton-num {
    background: linear-gradient(90deg, var(--ms-surface-2), var(--ms-surface-3), var(--ms-surface-2));
    background-size: 200% 100%;
    animation: cd-shimmer 1.2s linear infinite;
}
@keyframes cd-shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
@media (prefers-reduced-motion: reduce) {
    .cd-modal__skeleton-thumb,
    .cd-modal__skeleton-line,
    .cd-modal__skeleton-num { animation: none; background: var(--ms-surface-3); }
}

/* Empty / error — iconified */
.cd-modal__empty, .cd-modal__error {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--ms-ink-3);
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
}
.cd-modal__empty i, .cd-modal__error i { font-size: 1.75rem; color: var(--ms-ink-4); }
.cd-modal__empty p, .cd-modal__error p { margin: 0; color: var(--ms-ink-2); }
.cd-modal__empty small, .cd-modal__error small { color: var(--ms-ink-3); }
.cd-modal__error { color: #b91c1c; }
.cd-modal__error i { color: #b91c1c; }
.cd-modal__error p { color: #b91c1c; }
.cd-modal__error button { margin-top: 0.5rem; }

.cd-modal__foot {
    display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid var(--ms-line);
    background: var(--ms-surface-2);
    flex: 0 0 auto;
}
.cd-modal__foot-hint { color: var(--ms-ink-3); font-size: 0.78rem; }

/* ══════════════════════════════════════════════════════════
   Close-month wizard (#modal-close-month)
   ══════════════════════════════════════════════════════════ */
.cm-modal {
    border-radius: var(--ms-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 100%;
}
.cm-modal__head { padding: 1rem 1.25rem 0; flex: 0 0 auto; }
.cm-modal__head-row { display: flex; justify-content: space-between; align-items: center; }
.cm-modal__title {
    margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--ms-ink-1);
    display: inline-flex; align-items: center; gap: 0.45rem;
}
.cm-modal__title i { color: var(--ms-primary); font-size: 1.05rem; }

/* Stepper */
.cm-stepper {
    display: flex; align-items: center; gap: 0.5rem;
    list-style: none; margin: 0.9rem 0 0; padding: 0 0 1rem;
    counter-reset: none;
}
.cm-step {
    display: inline-flex; align-items: center; gap: 0.5rem;
    color: var(--ms-ink-3); font-size: 0.85rem; font-weight: 500;
    flex: 0 0 auto;
}
.cm-step:not(:last-child)::after {
    content: ""; width: 1.75rem; height: 2px; margin-left: 0.25rem;
    background: var(--ms-line-strong); border-radius: 999px;
    transition: background-color var(--ms-t-mid, 180ms) var(--ms-ease);
}
.cm-step__indicator {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; border-radius: 999px;
    background: var(--ms-surface-3); color: var(--ms-ink-3);
    font-size: 0.82rem; font-weight: 700; font-variant-numeric: tabular-nums;
    border: 1.5px solid transparent;
    transition: background-color 180ms var(--ms-ease), color 180ms var(--ms-ease), border-color 180ms var(--ms-ease);
}
.cm-step__check { display: none; font-size: 0.9rem; }
.cm-step.is-active { color: var(--ms-ink-1); font-weight: 600; }
.cm-step.is-active .cm-step__indicator {
    background: var(--ms-primary); color: #fff;
    border-color: var(--ms-primary);
    box-shadow: 0 0 0 3px var(--ms-primary-soft);
}
.cm-step.is-done { color: var(--ms-ink-2); }
.cm-step.is-done .cm-step__indicator { background: var(--ms-success); color: #fff; border-color: var(--ms-success); }
.cm-step.is-done .cm-step__num { display: none; }
.cm-step.is-done .cm-step__check { display: inline; }
.cm-step.is-done:not(:last-child)::after { background: var(--ms-success); }

#modal-close-month .is-negative { color: var(--ms-danger); }
.cm-modal__body { padding: 1.1rem 1.25rem; flex: 1 1 auto; overflow-y: auto; min-height: 0; }
.cm-panel__lead { color: var(--ms-ink-2); font-size: 0.9rem; margin: 0 0 1.1rem; line-height: 1.5; max-width: 60ch; }

/* Step 1 fields */
.cm-field-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; max-width: 640px; }
.cm-field { display: flex; flex-direction: column; gap: 0.4rem; }
.cm-field__label { font-size: 0.8rem; font-weight: 600; color: var(--ms-ink-2); }
.cm-field__pair { display: flex; gap: 0.5rem; }
.cm-select {
    min-height: 42px; border-radius: var(--ms-radius-sm);
    border: 1px solid var(--ms-line-strong); color: var(--ms-ink-1);
    font-size: 0.92rem;
    transition: border-color 120ms var(--ms-ease), box-shadow 120ms var(--ms-ease);
}
.cm-select:focus { border-color: var(--ms-primary); box-shadow: 0 0 0 3px var(--ms-primary-soft); }
.cm-field.is-invalid .cm-select { border-color: var(--ms-danger); }
.cm-field__hint { font-size: 0.78rem; color: var(--ms-danger); font-weight: 500; }

/* Summary strip (step 2) */
.cm-summary {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 0.6rem; margin-bottom: 1rem;
}
.cm-summary__cell {
    background: var(--ms-surface-2); border: 1px solid var(--ms-line);
    border-radius: var(--ms-radius-sm); padding: 0.6rem 0.75rem;
    display: flex; flex-direction: column; gap: 0.2rem;
}
.cm-summary__cell--emphasis { background: var(--ms-primary-soft); border-color: var(--ms-primary-line); }
.cm-summary__label { font-size: 0.74rem; color: var(--ms-ink-3); }
.cm-summary__value {
    font-size: 1.05rem; font-weight: 700; color: var(--ms-ink-1);
    font-variant-numeric: tabular-nums; letter-spacing: -0.01em; line-height: 1.2;
}
.cm-summary__value.is-negative { color: var(--ms-danger); }
.cm-summary__unit { font-size: 0.7rem; color: var(--ms-ink-3); font-weight: 500; }

/* Context line */
.cm-ctx { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.85rem; color: var(--ms-ink-2); font-size: 0.88rem; }
.cm-ctx__chip {
    display: inline-flex; align-items: center; gap: 0.35rem;
    background: var(--ms-surface-3); color: var(--ms-ink-2);
    padding: 0.25rem 0.65rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600;
}
.cm-ctx__chip i { color: var(--ms-ink-3); font-size: 0.82rem; }

/* Alert banners (already-closed / negatives / zero-cost / clean) */
.cm-alert {
    display: flex; gap: 0.6rem; align-items: flex-start;
    padding: 0.7rem 0.85rem; border-radius: var(--ms-radius-sm);
    margin-bottom: 0.75rem; font-size: 0.86rem; line-height: 1.45;
}
.cm-alert__icon { font-size: 1rem; flex: 0 0 auto; margin-top: 0.05rem; }
.cm-alert__body { min-width: 0; }
.cm-alert__title { font-weight: 600; }
.cm-alert__list { margin: 0.4rem 0 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: 0.35rem; }
.cm-alert__item {
    display: inline-flex; align-items: baseline; gap: 0.3rem;
    background: rgba(255, 255, 255, 0.65); border: 1px solid currentColor;
    border-radius: 999px; padding: 0.15rem 0.55rem; font-size: 0.78rem; font-weight: 500;
}
.cm-alert__item-code { font-variant-numeric: tabular-nums; opacity: 0.75; }
.cm-alert__item-val { font-variant-numeric: tabular-nums; font-weight: 700; }
.cm-alert__more { align-self: center; font-size: 0.78rem; font-weight: 600; opacity: 0.8; }
.cm-alert--danger { background: var(--ms-danger-soft); color: var(--ms-danger); }
.cm-alert--warning { background: var(--ms-warning-soft); color: var(--ms-warning); }
.cm-alert--success { background: var(--ms-success-soft); color: var(--ms-success); }
.cm-alert--info { background: var(--ms-primary-soft); color: var(--ms-primary-ink); }
.cm-alert--danger .cm-alert__item,
.cm-alert--warning .cm-alert__item { border-color: rgba(0, 0, 0, 0.12); }

/* Legend */
.cm-legend { display: flex; flex-wrap: wrap; gap: 0.9rem; margin: 0.6rem 0 0.85rem; font-size: 0.76rem; color: var(--ms-ink-3); }
.cm-legend__item { display: inline-flex; align-items: center; gap: 0.35rem; }
.cm-legend__swatch { width: 0.7rem; height: 0.7rem; border-radius: 3px; display: inline-block; }
.cm-legend__swatch--neg { background: var(--ms-danger); }
.cm-legend__swatch--zero { background: var(--ms-ink-4); }

/* Preview table */
.cm-table-wrap { border: 1px solid var(--ms-line); border-radius: var(--ms-radius-sm); overflow: hidden; }
.cm-table { width: 100%; margin: 0; border-collapse: collapse; }
.cm-table thead th {
    position: sticky; top: 0; z-index: 1;
    background: var(--ms-surface-2); color: var(--ms-ink-2);
    font-size: 0.78rem; font-weight: 600; text-align: right;
    padding: 0.55rem 0.7rem; border-bottom: 1px solid var(--ms-line-strong);
    white-space: nowrap;
}
.cm-table thead th:first-child, .cm-table tbody td:first-child { text-align: left; }
.cm-table tbody td {
    font-size: 0.85rem; color: var(--ms-ink-1); text-align: right;
    padding: 0.5rem 0.7rem; border-bottom: 1px solid var(--ms-line);
    font-variant-numeric: tabular-nums; white-space: nowrap;
}
.cm-table tbody tr:last-child td { border-bottom: none; }
.cm-table tbody tr:hover td { background: var(--ms-surface-hover); }
.cm-table td.is-negative { color: var(--ms-danger); font-weight: 600; }
.cm-table td .cm-zero { color: var(--ms-ink-4); }
.cm-table tfoot td {
    background: var(--ms-surface-2); color: var(--ms-ink-1);
    font-weight: 700; font-size: 0.86rem; text-align: right;
    padding: 0.55rem 0.7rem; border-top: 2px solid var(--ms-line-strong);
    font-variant-numeric: tabular-nums; white-space: nowrap;
    position: sticky; bottom: 0;
}
.cm-table tfoot td:first-child { text-align: left; }
.cm-table tfoot td.is-negative { color: var(--ms-danger); }

/* Preview skeleton */
.cm-skeleton { display: flex; flex-direction: column; gap: 0.6rem; }
.cm-skeleton__strip { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.6rem; }
.cm-skeleton__box, .cm-skeleton__line {
    background: linear-gradient(90deg, var(--ms-surface-2), var(--ms-surface-3), var(--ms-surface-2));
    background-size: 200% 100%; animation: cd-shimmer 1.2s linear infinite; border-radius: var(--ms-radius-sm);
}
.cm-skeleton__box { height: 58px; }
.cm-skeleton__line { height: 34px; border-radius: 6px; }
.cm-skeleton__rows { display: flex; flex-direction: column; gap: 0.4rem; margin-top: 0.4rem; }
@media (prefers-reduced-motion: reduce) {
    .cm-skeleton__box, .cm-skeleton__line { animation: none; background: var(--ms-surface-3); }
}

/* Preview error */
.cm-error { text-align: center; padding: 2rem 1rem; color: var(--ms-danger); display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.cm-error i { font-size: 1.75rem; }
.cm-error p { margin: 0; }

/* Step 3 result */
.cm-panel--result { display: flex; align-items: center; justify-content: center; min-height: 240px; }
.cm-result { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.6rem; }
.cm-result__icon {
    width: 64px; height: 64px; border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--ms-success-soft); color: var(--ms-success); font-size: 2rem;
}
.cm-result__icon.is-error { background: var(--ms-danger-soft); color: var(--ms-danger); }
.cm-result__title { font-size: 1.1rem; font-weight: 700; color: var(--ms-ink-1); margin: 0; }
.cm-result__caption { color: var(--ms-ink-3); font-size: 0.88rem; margin: 0; }
@keyframes cm-pop { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.cm-result__icon { animation: cm-pop 240ms var(--ms-ease); }
@media (prefers-reduced-motion: reduce) { .cm-result__icon { animation: none; } }

/* Footer */
.cm-modal__foot {
    display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;
    padding: 0.85rem 1.25rem; border-top: 1px solid var(--ms-line);
    background: var(--ms-surface-2); flex: 0 0 auto;
}
.cm-foot-group { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; width: 100%; }
.cm-btn-confirm { position: relative; }
.cm-btn-confirm.is-danger { background: var(--ms-danger); border-color: var(--ms-danger); }
.cm-btn-confirm.is-danger:hover { background: #a01818; border-color: #a01818; }
.cm-btn-confirm__spinner { margin-left: 0.15rem; }

/* Panel enter transition */
.cm-panel[hidden] { display: none; }
.cm-panel:not([hidden]) { animation: cm-panel-in var(--ms-t-mid, 180ms) var(--ms-ease); }
@keyframes cm-panel-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) { .cm-panel:not([hidden]) { animation: none; } }

/* ══════════════════════════════════════════════════════════
   Cancel-close confirm modal (#modal-cancel-close)
   ══════════════════════════════════════════════════════════ */
.cx-modal { border-radius: var(--ms-radius); overflow: hidden; }
.cx-modal__head { display: flex; align-items: flex-start; gap: 0.75rem; padding: 1.1rem 1.25rem 0.75rem; }
.cx-modal__icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 999px; flex: 0 0 auto;
    background: var(--ms-danger-soft); color: var(--ms-danger); font-size: 1.15rem;
}
.cx-modal__title { margin: 0; font-size: 1.05rem; font-weight: 600; color: var(--ms-ink-1); }
.cx-modal__caption { margin: 0.15rem 0 0; font-size: 0.82rem; color: var(--ms-ink-3); }
.cx-modal__body { padding: 0.5rem 1.25rem 1rem; }
.cx-load { display: flex; align-items: center; gap: 0.5rem; color: var(--ms-ink-3); font-size: 0.88rem; padding: 0.75rem 0; }
.cx-lead { color: var(--ms-ink-2); font-size: 0.9rem; line-height: 1.55; margin: 0 0 0.75rem; }
.cx-strong { font-weight: 700; color: var(--ms-ink-1); font-variant-numeric: tabular-nums; }
.cx-irrev { color: var(--ms-danger); font-weight: 600; }
.cx-modal__foot {
    display: flex; justify-content: flex-end; gap: 0.6rem;
    padding: 0.85rem 1.25rem; border-top: 1px solid var(--ms-line); background: var(--ms-surface-2);
}
.cx-btn-danger {
    background: var(--ms-danger); border: 1px solid var(--ms-danger); color: #fff;
    font-weight: 600; border-radius: var(--ms-radius-sm); position: relative;
    transition: background-color 120ms var(--ms-ease);
}
.cx-btn-danger:hover:not(:disabled) { background: #a01818; border-color: #a01818; color: #fff; }
.cx-btn-danger:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--ms-danger-soft); }
.cx-btn-danger:disabled { opacity: 0.55; cursor: not-allowed; }
.cx-btn-danger__spinner { margin-left: 0.15rem; }
.cx-error { color: var(--ms-danger); font-size: 0.88rem; padding: 0.5rem 0; display: flex; align-items: center; gap: 0.4rem; }

/* Clickable negative chip (in preview warning list) */
.cm-alert__list li { list-style: none; }
.cm-alert__item--btn {
    appearance: none; cursor: pointer; font: inherit; text-align: left;
    display: inline-flex; align-items: baseline; gap: 0.3rem;
    background: rgba(255, 255, 255, 0.75); border: 1px solid var(--ms-danger);
    color: var(--ms-danger); border-radius: 999px; padding: 0.15rem 0.55rem;
    font-size: 0.78rem; font-weight: 500;
    transition: background-color 120ms var(--ms-ease), box-shadow 120ms var(--ms-ease);
}
.cm-alert__item--btn:hover { background: #fff; box-shadow: 0 0 0 2px var(--ms-danger-soft); }
.cm-alert__item--btn:focus-visible { outline: none; box-shadow: 0 0 0 3px var(--ms-danger-soft); }
.cm-alert__item-wh { opacity: 0.7; font-weight: 400; }
.cm-alert__item-ico { font-size: 0.72rem; opacity: 0.7; align-self: center; }
@media (prefers-reduced-motion: reduce) { .cm-alert__item--btn { transition: none; } }

@media (max-width: 768px) {
    .ms-report-summary__head { flex-direction: column; align-items: flex-start; }
    .cd-modal__summary { margin: 0.5rem 0.75rem 0; }
    .cd-modal__head, .cd-modal__body, .cd-modal__foot { padding-left: 0.75rem; padding-right: 0.75rem; }
    .cm-modal__head, .cm-modal__body, .cm-modal__foot { padding-left: 0.85rem; padding-right: 0.85rem; }
    .cm-step__label { display: none; }
    .cm-step:not(:last-child)::after { width: 1.25rem; }
    .cm-skeleton__strip { grid-template-columns: repeat(2, 1fr); }
}
</style>

<?php
$closeUrl = Url::to(['/inventory-v2/report/close-month']);
$previewUrl = Url::to(['/inventory-v2/report/close-month-preview']);
$doctorUrl = Url::to(['/inventory-v2/report/close-month-doctor']);
$autofixUrl = Url::to(['/inventory-v2/report/close-month-autofix']);
$cancelUrl = Url::to(['/inventory-v2/report/cancel-close']);
$reportUrl = Url::to(['/inventory-v2/report/material-summary']);
$drillUrl = Url::to(['/inventory-v2/report/category-drilldown']);
$placeholderUrl = Yii::getAlias('@web') . '/img/placeholder-img.jpg';
$ctxYear = (int) $year;
$ctxMonth = (int) $month;
$ctxWarehouse = $warehouseId === null ? 'null' : (int) $warehouseId;
$this->registerJs(<<<JS
(function(){
    // ══ Close-month wizard (3 steps) ══
    var closeUrl = '{$closeUrl}';
    var previewUrl = '{$previewUrl}';
    var reportUrl = '{$reportUrl}';
    var MONTH_NAMES = ['','มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    var MAX_WARN_ITEMS = 12;

    var cmEl = document.getElementById('modal-close-month');
    var cmContent = cmEl ? cmEl.querySelector('.cm-modal') : null;
    var cmPreviewXhr = null;
    var cmPreviewLoaded = false;

    function cmFmt(n){ return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function cmFmtQty(n){ return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }); }
    function cmEsc(s){
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
            return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c];
        });
    }
    // number cell: 0 → em-dash, <0 → red
    function cmCell(v){
        v = Number(v || 0);
        if (Math.abs(v) < 0.005) return '<span class="cm-zero" aria-label="ไม่มียอด">—</span>';
        return v < 0 ? '<span class="is-negative">' + cmFmt(v) + '</span>' : cmFmt(v);
    }

    function cmGoStep(step){
        if (!cmContent) return;
        cmContent.setAttribute('data-step', String(step));
        cmContent.querySelectorAll('.cm-panel').forEach(function(p){
            p.hidden = (Number(p.getAttribute('data-step')) !== step);
        });
        cmContent.querySelectorAll('.cm-foot-group').forEach(function(g){
            g.hidden = (Number(g.getAttribute('data-step')) !== step);
        });
        cmContent.querySelectorAll('.cm-step').forEach(function(s){
            var sStep = Number(s.getAttribute('data-step'));
            s.classList.toggle('is-active', sStep === step);
            s.classList.toggle('is-done', sStep < step);
            if (sStep === step) { s.setAttribute('aria-current', 'step'); }
            else { s.removeAttribute('aria-current'); }
        });
    }

    function cmReset(){
        cmPreviewLoaded = false;
        cmGoStep(1);
        $('#cm-warehouse-hint').prop('hidden', true).closest('.cm-field').removeClass('is-invalid');
        $('#cm-confirm').prop('disabled', true).removeClass('is-danger');
        $('#cm-preview').empty().attr('aria-busy', 'true');
    }

    function cmPreviewSkeleton(){
        var rows = '';
        for (var i = 0; i < 6; i++){ rows += '<div class="cm-skeleton__line"></div>'; }
        return '<div class="cm-skeleton" aria-hidden="true">'
            + '<div class="cm-skeleton__strip">'
            +   '<div class="cm-skeleton__box"></div><div class="cm-skeleton__box"></div><div class="cm-skeleton__box"></div><div class="cm-skeleton__box"></div><div class="cm-skeleton__box"></div>'
            + '</div>'
            + '<div class="cm-skeleton__rows">' + rows + '</div>'
            + '</div>';
    }

    function cmWarnItems(items, kind){
        var shown = items.slice(0, MAX_WARN_ITEMS);
        var html = shown.map(function(it){
            if (kind === 'neg'){
                // คลิกได้ → เปิดประวัติเคลื่อนไหวของ item+คลังนั้น
                var wh = it.warehouse_name ? '<span class="cm-alert__item-wh">· ' + cmEsc(it.warehouse_name) + '</span>' : '';
                return '<li>'
                    + '<button type="button" class="cm-alert__item cm-alert__item--btn js-neg-history"'
                    +   ' data-item-code="' + cmEsc(it.item_code) + '"'
                    +   ' data-item-name="' + cmEsc(it.item_name) + '"'
                    +   ' data-warehouse-id="' + cmEsc(it.warehouse_id) + '"'
                    +   ' data-warehouse-name="' + cmEsc(it.warehouse_name || '') + '"'
                    +   ' title="ดูประวัติการเคลื่อนไหว">'
                    +   '<span class="cm-alert__item-code">' + cmEsc(it.item_code) + '</span>'
                    +   '<span>' + cmEsc(it.item_name) + '</span>' + wh
                    +   '<span class="cm-alert__item-val">' + cmFmt(it.value) + '</span>'
                    +   '<i class="bi bi-clock-history cm-alert__item-ico" aria-hidden="true"></i>'
                    + '</button></li>';
            }
            return '<li class="cm-alert__item">'
                + '<span class="cm-alert__item-code">' + cmEsc(it.item_code) + '</span>'
                + '<span>' + cmEsc(it.item_name) + '</span>'
                + '<span class="cm-alert__item-val">' + cmFmtQty(it.qty) + ' หน่วย</span>'
                + '</li>';
        }).join('');
        if (items.length > MAX_WARN_ITEMS){
            html += '<li class="cm-alert__more">และอีก ' + (items.length - MAX_WARN_ITEMS) + ' รายการ</li>';
        }
        return '<ul class="cm-alert__list">' + html + '</ul>';
    }

    function cmRenderPreview(res){
        var m = res.meta, s = res.summary, w = res.warnings;
        var negCls = s.closing_value < -0.005 ? ' is-negative' : '';
        var html = '';

        // context
        html += '<div class="cm-ctx">'
            + '<span class="cm-ctx__chip"><i class="bi bi-building" aria-hidden="true"></i>' + cmEsc(m.warehouse_label) + '</span>'
            + '<span class="cm-ctx__chip"><i class="bi bi-calendar3" aria-hidden="true"></i>' + cmEsc(m.period_label) + ' (พ.ศ.)</span>'
            + '</div>';

        // summary strip
        html += '<div class="cm-summary">'
            + cmSumCell('รายการที่จะบันทึก', s.row_count.toLocaleString('en-US'), 'แถว', false)
            + cmSumCell('ยอดยกมา', cmFmt(s.opening_value), 'บาท', false)
            + cmSumCell('รับเข้า', cmFmt(s.in_value), 'บาท', false)
            + cmSumCell('จ่ายออก', cmFmt(s.total_out_value), 'บาท', false)
            + cmSumCell('ยอดยกไป', '<span class="' + (negCls ? 'is-negative' : '') + '">' + cmFmt(s.closing_value) + '</span>', 'บาท', true)
            + '</div>';

        // warnings
        var hasWarn = false;
        if (m.chained_months && m.chained_months > 0){
            html += cmAlert('info', 'bi-calculator', 'ยังไม่เคยปิดงวดก่อนหน้า (' + m.chained_months + ' งวด)',
                'ยอดยกมาในตัวอย่างนี้คำนวณสะสมจากต้นจนถึงงวดนี้ให้แล้ว เมื่อกดยืนยันจะบันทึกเฉพาะงวดนี้งวดเดียว (ไม่สร้างข้อมูลงวดกลาง) — งวดถัดไปจะยกยอดมาจากงวดนี้');
        }
        if (m.already_closed){
            html += cmAlert('warning', 'bi-arrow-repeat', 'งวดนี้เคยปิดไปแล้ว', 'การกดยืนยันจะคำนวณใหม่และเขียนทับข้อมูลเดิมของงวดนี้');
        }
        if (w.negatives && w.negatives.length){
            hasWarn = true;
            html += cmAlert('danger', 'bi-exclamation-octagon-fill',
                'ยอดยกไปติดลบ ' + w.negatives.length + ' รายการ',
                'ตรวจสอบการรับ-จ่ายของรายการเหล่านี้ก่อนปิด (ยอดคงเหลือไม่ควรติดลบ) — คลิกที่รายการเพื่อดูประวัติการเคลื่อนไหว',
                cmWarnItems(w.negatives, 'neg'));
        }
        if (w.zero_cost && w.zero_cost.length){
            hasWarn = true;
            html += cmAlert('warning', 'bi-cash-stack',
                'จ่ายออกแต่ไม่มีราคาทุน ' + w.zero_cost.length + ' รายการ',
                'รายการเหล่านี้มีการจ่ายออกแต่คิดมูลค่าเป็น 0 (ยังไม่เคยรับเข้าในคลังนี้) มูลค่าจ่ายจริงอาจสูงกว่าที่แสดง',
                cmWarnItems(w.zero_cost, 'zero'));
        }
        if (!hasWarn && !m.already_closed){
            html += cmAlert('success', 'bi-check-circle-fill', 'ตรวจแล้วไม่พบความผิดปกติ', 'ทุกรายการมียอดคงเหลือปกติ พร้อมปิดเดือน');
        }

        // legend + table
        html += '<div class="cm-legend">'
            + '<span class="cm-legend__item"><span class="cm-legend__swatch cm-legend__swatch--neg"></span> ตัวเลขสีแดง = ติดลบ ต้องตรวจสอบ</span>'
            + '<span class="cm-legend__item"><span class="cm-legend__swatch cm-legend__swatch--zero"></span> — = ไม่มียอด</span>'
            + '</div>';
        html += cmRenderTable(res.rows);

        $('#cm-preview').attr('aria-busy', 'false').html(html);

        // confirm button state
        var \$confirm = $('#cm-confirm').prop('disabled', false);
        \$confirm.toggleClass('is-danger', !!m.already_closed);
        \$confirm.find('.cm-btn-confirm__label').html(m.already_closed
            ? '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> ยืนยันเขียนทับ'
            : '<i class="bi bi-calendar-check" aria-hidden="true"></i> ยืนยันปิดเดือน');
        cmPreviewLoaded = true;
    }

    function cmSumCell(label, valueHtml, unit, emphasis){
        return '<div class="cm-summary__cell' + (emphasis ? ' cm-summary__cell--emphasis' : '') + '">'
            + '<span class="cm-summary__label">' + label + '</span>'
            + '<span class="cm-summary__value">' + valueHtml + ' <span class="cm-summary__unit">' + unit + '</span></span>'
            + '</div>';
    }
    function cmAlert(variant, icon, title, body, extra){
        return '<div class="cm-alert cm-alert--' + variant + '" role="' + (variant === 'danger' ? 'alert' : 'status') + '">'
            + '<i class="cm-alert__icon bi ' + icon + '" aria-hidden="true"></i>'
            + '<div class="cm-alert__body">'
            +   '<div class="cm-alert__title">' + cmEsc(title) + '</div>'
            +   '<div>' + cmEsc(body) + '</div>'
            +   (extra || '')
            + '</div></div>';
    }
    function cmRenderTable(rows){
        if (!rows || !rows.length){
            return '<div class="cm-error" style="color:var(--ms-ink-3)"><i class="bi bi-inbox" style="color:var(--ms-ink-4)"></i><p>ไม่มีรายการเคลื่อนไหวในงวดนี้</p></div>';
        }
        var tOpen=0,tIn=0,tAvail=0,tSub=0,tHosp=0,tOut=0,tClose=0;
        var body = rows.map(function(r){
            var avail = Number(r.opening_value) + Number(r.in_value);
            tOpen+=Number(r.opening_value); tIn+=Number(r.in_value); tAvail+=avail;
            tSub+=Number(r.out_sub_value); tHosp+=Number(r.out_hosp_value);
            tOut+=Number(r.total_out_value); tClose+=Number(r.closing_value);
            function td(v){ var neg = Number(v) < -0.005; return '<td' + (neg ? ' class="is-negative"' : '') + '>' + cmCell(v) + '</td>'; }
            return '<tr>'
                + '<td>' + cmEsc(r.category_label) + '</td>'
                + td(r.opening_value) + td(r.in_value) + td(avail)
                + td(r.out_sub_value) + td(r.out_hosp_value) + td(r.total_out_value) + td(r.closing_value)
                + '</tr>';
        }).join('');
        function ft(v){ var neg = Number(v) < -0.005; return '<td' + (neg ? ' class="is-negative"' : '') + '>' + cmCell(v) + '</td>'; }
        return '<div class="cm-table-wrap"><table class="cm-table"><thead><tr>'
            + '<th>ประเภทวัสดุ</th><th>ยกมา</th><th>ซื้อ</th><th>รวม</th>'
            + '<th>จ่าย รพ.สต.</th><th>จ่าย รพ.</th><th>รวมจ่าย</th><th>ยกไป</th>'
            + '</tr></thead><tbody>' + body + '</tbody>'
            + '<tfoot><tr><td>รวมทั้งหมด</td>'
            + ft(tOpen) + ft(tIn) + ft(tAvail) + ft(tSub) + ft(tHosp) + ft(tOut) + ft(tClose)
            + '</tr></tfoot></table></div>';
    }

    function cmLoadPreview(){
        var wh = $('#close-warehouse-id').val();
        var month = $('#close-month').val();
        var year = $('#close-year').val();
        $('#cm-preview').attr('aria-busy', 'true').html(cmPreviewSkeleton());
        $('#cm-confirm').prop('disabled', true);
        if (cmPreviewXhr && cmPreviewXhr.readyState !== 4){ cmPreviewXhr.abort(); }
        cmPreviewXhr = $.ajax({ url: previewUrl, method: 'POST', dataType: 'json',
            data: { warehouse_id: wh, month: month, year: year } })
            .done(function(res){
                if (!res || !res.success){
                    cmPreviewError((res && res.message) || 'คำนวณข้อมูลไม่สำเร็จ');
                    return;
                }
                cmRenderPreview(res);
            })
            .fail(function(xhr, status){
                if (status === 'abort') return;
                cmPreviewError('คำนวณข้อมูลไม่สำเร็จ');
            });
    }
    function cmPreviewError(msg){
        $('#cm-preview').attr('aria-busy', 'false').html(
            '<div class="cm-error"><i class="bi bi-exclamation-triangle"></i><p>' + cmEsc(msg) + '</p>'
            + '<button type="button" class="btn btn-outline-secondary btn-sm" id="cm-retry">ลองอีกครั้ง</button></div>');
    }

    // ── Events ──
    // reset to step 1 each time modal opens
    if (cmEl){
        cmEl.addEventListener('show.bs.modal', cmReset);
        cmEl.addEventListener('shown.bs.modal', function(){ $('#close-warehouse-id').trigger('focus'); });
    }

    // step 1 → 2
    $(document).on('click', '#cm-to-preview', function(){
        var wh = $('#close-warehouse-id').val();
        if (!wh){
            $('#cm-warehouse-hint').prop('hidden', false).closest('.cm-field').addClass('is-invalid');
            $('#close-warehouse-id').trigger('focus');
            return;
        }
        $('#cm-warehouse-hint').prop('hidden', true).closest('.cm-field').removeClass('is-invalid');
        cmGoStep(2);
        cmLoadPreview();
    });
    $(document).on('change', '#close-warehouse-id', function(){
        if (this.value){ $('#cm-warehouse-hint').prop('hidden', true).closest('.cm-field').removeClass('is-invalid'); }
    });
    // retry preview
    $(document).on('click', '#cm-retry', cmLoadPreview);
    // step 2 → 1
    $(document).on('click', '#cm-back', function(){ cmGoStep(1); });

    // step 2 → confirm (commit)
    $(document).on('click', '#cm-confirm', function(){
        var \$btn = $(this).prop('disabled', true);
        \$btn.find('.cm-btn-confirm__label').css('opacity', 0.6);
        \$btn.find('.cm-btn-confirm__spinner').prop('hidden', false);
        var wh = $('#close-warehouse-id').val();
        var month = $('#close-month').val();
        var year = $('#close-year').val();
        $.post(closeUrl, { warehouse_id: wh, month: month, year: year })
            .done(function(res){
                if (res && res.success){
                    var caption = 'บันทึก ' + (res.count || 0) + ' รายการ';
                    if (res.warehouses_count > 1) caption += ' · ' + res.warehouses_count + ' คลัง';
                    $('#cm-result').html(
                        '<span class="cm-result__icon"><i class="bi bi-check-lg"></i></span>'
                        + '<p class="cm-result__title">ปิดเดือนเรียบร้อย</p>'
                        + '<p class="cm-result__caption">' + caption + ' · กำลังพาไปยังรายงาน…</p>');
                    cmGoStep(3);
                    var qs = 'year=' + year + '&month=' + month;
                    if (wh !== 'all') qs += '&warehouse_id=' + wh;
                    setTimeout(function(){ window.location.href = reportUrl + '?' + qs; }, 1100);
                } else {
                    cmConfirmFail(\$btn, (res && res.message) || 'เกิดข้อผิดพลาด');
                }
            })
            .fail(function(){ cmConfirmFail(\$btn, 'เกิดข้อผิดพลาด'); });
    });
    // on commit failure: stay on step 2, show inline alert, re-enable confirm to retry
    function cmConfirmFail(\$btn, msg){
        $('#cm-preview .cm-commit-error').remove();
        $('#cm-preview').prepend(cmAlert('danger', 'bi-exclamation-triangle-fill', 'ปิดเดือนไม่สำเร็จ', msg).replace('cm-alert cm-alert--danger', 'cm-alert cm-alert--danger cm-commit-error'));
        \$btn.prop('disabled', false);
        \$btn.find('.cm-btn-confirm__label').css('opacity', 1);
        \$btn.find('.cm-btn-confirm__spinner').prop('hidden', true);
    }

    // ══ Cancel-close (ยกเลิกปิดเดือน) — 2 phase: check → confirm delete ══
    var cancelUrl = '{$cancelUrl}';
    var cxEl = document.getElementById('modal-cancel-close');
    var cxModal = (cxEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) ? new bootstrap.Modal(cxEl) : null;
    var cxCtx = null;

    $(document).on('click', '#btn-cancel-close', function(){
        cxCtx = {
            year: this.getAttribute('data-year'),
            month: this.getAttribute('data-month'),
            warehouse: this.getAttribute('data-warehouse') || ''
        };
        $('#cx-load').show();
        $('#cx-detail').prop('hidden', true);
        $('#cx-error').prop('hidden', true).empty();
        $('#cx-later-warn').prop('hidden', true);
        $('#cx-later-list').empty();
        $('#cx-confirm').prop('disabled', true);
        if (cxModal) cxModal.show();
        $.post(cancelUrl, { year: cxCtx.year, month: cxCtx.month, warehouse_id: cxCtx.warehouse })
            .done(function(res){
                $('#cx-load').hide();
                if (!res || !res.success){
                    $('#cx-error').prop('hidden', false).html('<i class="bi bi-exclamation-triangle"></i> ' + cmEsc((res && res.message) || 'ตรวจสอบไม่สำเร็จ'));
                    return;
                }
                $('#cx-context').text(res.warehouse_label + ' · ' + res.period_label + ' (พ.ศ.)');
                $('#cx-rowcount').text(Number(res.row_count || 0).toLocaleString('en-US'));
                if (res.later_closed && res.later_closed.length){
                    $('#cx-later-title').text('มีงวดถัดไปที่ปิดไปแล้ว ' + res.later_closed.length + ' งวด');
                    $('#cx-later-list').html(res.later_closed.map(function(p){
                        return '<li class="cm-alert__item">' + cmEsc(p.label) + '</li>';
                    }).join(''));
                    $('#cx-later-warn').prop('hidden', false);
                }
                $('#cx-detail').prop('hidden', false);
                $('#cx-confirm').prop('disabled', false);
            })
            .fail(function(){
                $('#cx-load').hide();
                $('#cx-error').prop('hidden', false).html('<i class="bi bi-exclamation-triangle"></i> ตรวจสอบไม่สำเร็จ');
            });
    });

    $(document).on('click', '#cx-confirm', function(){
        if (!cxCtx) return;
        var \$btn = $(this).prop('disabled', true);
        \$btn.find('.cx-btn-danger__label').css('opacity', 0.6);
        \$btn.find('.cx-btn-danger__spinner').prop('hidden', false);
        $.post(cancelUrl, { year: cxCtx.year, month: cxCtx.month, warehouse_id: cxCtx.warehouse, confirmed: 1 })
            .done(function(res){
                if (res && res.success && res.confirmed){
                    var qs = 'year=' + cxCtx.year + '&month=' + cxCtx.month;
                    if (cxCtx.warehouse) qs += '&warehouse_id=' + cxCtx.warehouse;
                    window.location.href = reportUrl + '?' + qs;
                } else {
                    cxFail(\$btn, (res && res.message) || 'ยกเลิกไม่สำเร็จ');
                }
            })
            .fail(function(){ cxFail(\$btn, 'ยกเลิกไม่สำเร็จ'); });
    });
    function cxFail(\$btn, msg){
        $('#cx-error').prop('hidden', false).html('<i class="bi bi-exclamation-triangle"></i> ' + cmEsc(msg));
        \$btn.prop('disabled', false);
        \$btn.find('.cx-btn-danger__label').css('opacity', 1);
        \$btn.find('.cx-btn-danger__spinner').prop('hidden', true);
    }

    // ══ ประวัติการเคลื่อนไหววัสดุ — เปิดจากรายการติดลบใน preview ══
    // reuse โมดัลเต็ม #itemHistoryModal (partial ร่วมกับหน้า balance) เพื่อดูประวัติ + ปรับยอด/แก้ไขได้
    var histEl = document.getElementById('itemHistoryModal');

    $(document).on('click', '.js-neg-history', function(){
        if (!histEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
        // สร้าง element trigger ชั่วคราวพร้อม data-* ให้ show.bs.modal ของโมดัลอ่านไปตั้งค่า
        var trigger = document.createElement('span');
        trigger.setAttribute('data-item-code', this.getAttribute('data-item-code') || '');
        trigger.setAttribute('data-item-name', this.getAttribute('data-item-name') || '');
        trigger.setAttribute('data-warehouse-id', this.getAttribute('data-warehouse-id') || '');
        trigger.setAttribute('data-warehouse-name', this.getAttribute('data-warehouse-name') || '');
        trigger.setAttribute('data-unit-name', this.getAttribute('data-unit-name') || '');
        trigger.setAttribute('data-item-image', this.getAttribute('data-item-image') || '');
        bootstrap.Modal.getOrCreateInstance(histEl).show(trigger);
    });

    // history modal ซ้อนบน wizard modal — กัน scroll-lock หลุดตอนปิด history ขณะ wizard ยังเปิด
    if (histEl){
        histEl.addEventListener('hidden.bs.modal', function(){
            var wiz = document.getElementById('modal-close-month');
            if (wiz && wiz.classList.contains('show')){ document.body.classList.add('modal-open'); }
        });
    }

    // ── Bootstrap tooltip for tooltip-info icons ──
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){
            new bootstrap.Tooltip(el);
        });
    }

    // ── Cell drill-down ──
    var ctxYear = {$ctxYear};
    var ctxMonth = {$ctxMonth};
    var ctxWarehouse = {$ctxWarehouse};
    var drillUrl = '{$drillUrl}';
    var modalEl = document.getElementById('categoryDrillModal');
    var modal = (typeof bootstrap !== 'undefined' && bootstrap.Modal) ? new bootstrap.Modal(modalEl) : null;
    var lastTrigger = null;
    var currentXhr = null;

    var dirIconByKind = {
        opening: 'bi-archive',
        in: 'bi-arrow-down-left-circle',
        out_sub: 'bi-arrow-up-right-circle',
        out_hosp: 'bi-arrow-up-right-circle',
        total_out: 'bi-arrow-up-right-circle',
        closing: 'bi-archive-fill'
    };

    var currentItems = [];
    var currentQuery = '';

    function fmt(n){
        return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function fmtQty(n){
        return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }
    function escapeHtml(s){
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderSkeleton(){
        var row = '<div class="cd-modal__skeleton-row">'
            + '<div class="cd-modal__skeleton-thumb"></div>'
            + '<div class="cd-modal__skeleton-lines">'
            +   '<div class="cd-modal__skeleton-line cd-modal__skeleton-line--sm"></div>'
            +   '<div class="cd-modal__skeleton-line cd-modal__skeleton-line--md"></div>'
            + '</div>'
            + '<div class="cd-modal__skeleton-num"></div>'
            + '<div class="cd-modal__skeleton-num"></div>'
            + '</div>';
        return '<div class="cd-modal__skeleton" aria-hidden="true">' + row + row + row + row + '</div>';
    }

    function highlight(text, query){
        if (!query) return escapeHtml(text);
        var safe = escapeHtml(text);
        var safeQ = escapeHtml(query).replace(/[.*+?^\${}()|[\]\\\\]/g, '\\\\\$&');
        try {
            return safe.replace(new RegExp('(' + safeQ + ')', 'gi'), '<mark>\$1</mark>');
        } catch (e) {
            return safe;
        }
    }

    function renderTable(items, query, animate){
        if (!items.length) {
            if (query) {
                return '<div class="cd-modal__empty">'
                    + '<i class="bi bi-search" aria-hidden="true"></i>'
                    + '<p>ไม่พบรายการที่ตรงกับ "' + escapeHtml(query) + '"</p>'
                    + '<small>ลองค้นด้วยรหัสหรือคำในชื่อ</small>'
                    + '</div>';
            }
            return '<div class="cd-modal__empty">'
                + '<i class="bi bi-inbox" aria-hidden="true"></i>'
                + '<p>ไม่พบรายการที่ประกอบเป็นยอดนี้</p>'
                + '</div>';
        }
        var hasQuery = !!query;
        var placeholderUrl = '{$placeholderUrl}';
        var rows = items.map(function(it, idx){
            var classes = [];
            if (hasQuery) classes.push('is-hit-row');
            if (animate && idx < 25) classes.push('is-stagger');
            var classAttr = classes.length ? ' class="' + classes.join(' ') + '"' : '';
            var styleAttr = (animate && idx < 25) ? ' style="--i:' + idx + '"' : '';
            var code = hasQuery ? highlight(it.item_code, query) : escapeHtml(it.item_code);
            var name = hasQuery ? highlight(it.item_name, query) : escapeHtml(it.item_name);
            var qty = Number(it.qty || 0);
            var val = Number(it.value || 0);
            var pct = Number(it.percent_of_total || 0);
            var qtyCls = 'cd-modal__num text-end' + (qty < 0 ? ' is-negative' : '');
            var valCls = 'cd-modal__num text-end' + (val < 0 ? ' is-negative' : '');
            var fillCls = 'cd-modal__pct-fill' + (val < 0 ? ' is-negative' : '');
            var img = escapeHtml(it.image_url || placeholderUrl);
            var imgFallback = escapeHtml(placeholderUrl);
            return '<tr' + classAttr + styleAttr + '>'
                + '<td class="cd-modal__img-cell">'
                +   '<img src="' + img + '" alt="" loading="lazy" class="cd-modal__img"'
                +   ' onerror="this.onerror=null;this.src=\\'' + imgFallback + '\\';">'
                + '</td>'
                + '<td>'
                +   '<div class="cd-modal__item-meta">'
                +     '<span class="cd-modal__item-code">' + code + '</span>'
                +     '<span class="cd-modal__item-name">' + name + '</span>'
                +   '</div>'
                + '</td>'
                + '<td class="' + qtyCls + '">' + fmtQty(qty) + ' <span class="cd-modal__unit">' + escapeHtml(it.unit_name || '') + '</span></td>'
                + '<td class="' + valCls + '">'
                +   '<span class="cd-modal__value-stack">'
                +     '<span class="cd-modal__value-num">' + fmt(val) + '</span>'
                +     '<span class="cd-modal__pct-bar" title="' + pct.toFixed(1) + '% ของยอดรวม">'
                +       '<span class="' + fillCls + '" style="--pct:' + pct + '%"></span>'
                +     '</span>'
                +   '</span>'
                + '</td>'
                + '</tr>';
        }).join('');
        var tbodyCls = animate ? '' : ' class="is-filter-fade"';
        return '<table class="table table-hover table-sm align-middle mb-0">'
            + '<thead><tr>'
            + '<th class="cd-modal__img-cell"></th>'
            + '<th>รายการวัสดุ</th>'
            + '<th class="cd-modal__num text-end">จำนวน</th>'
            + '<th class="cd-modal__num text-end">มูลค่า (บาท)</th>'
            + '</tr></thead>'
            + '<tbody' + tbodyCls + '>' + rows + '</tbody>'
            + '</table>';
    }

    function applyFilter(animate){
        var q = currentQuery.trim().toLowerCase();
        var filtered;
        if (!q) {
            filtered = currentItems;
        } else {
            filtered = currentItems.filter(function(it){
                return (it.item_code || '').toLowerCase().indexOf(q) !== -1
                    || (it.item_name || '').toLowerCase().indexOf(q) !== -1;
            });
        }
        $('#cd-body').scrollTop(0).html(renderTable(filtered, q, !!animate));
        var meta = '';
        if (q) {
            meta = 'พบ ' + filtered.length.toLocaleString('en-US') + ' / ' + currentItems.length.toLocaleString('en-US') + ' รายการ';
        }
        $('#cd-filter-meta').text(meta);
        $('#cd-search-clear').prop('hidden', !q);
    }

    function renderError(message){
        return '<div class="cd-modal__error">'
            + '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i>'
            + '<p>' + escapeHtml(message || 'โหลดข้อมูลไม่สำเร็จ') + '</p>'
            + '<button type="button" class="btn btn-outline-secondary btn-sm" id="cd-retry">ลองอีกครั้ง</button>'
            + '</div>';
    }

    function load(kind, category, trigger){
        lastTrigger = trigger || null;
        currentItems = [];
        currentQuery = '';
        $('#cd-search').val('');
        $('#cd-search-clear').prop('hidden', true);
        $('#cd-filter').attr('hidden', true);
        $('#cd-filter-meta').text('');
        var \$body = $('#cd-body').attr('aria-busy', 'true').scrollTop(0).html(renderSkeleton());
        $('#cd-stat-strip').attr('hidden', true);
        $('#cd-kind-label').text('กำลังโหลด...');
        $('#cd-category-label').text('—');
        $('#cd-period').text('—');
        $('#cd-warehouse').text('—');
        $('#cd-kind-badge').find('i').attr('class', 'bi ' + (dirIconByKind[kind] || 'bi-list-ul'));

        if (currentXhr && currentXhr.readyState !== 4) {
            currentXhr.abort();
        }
        currentXhr = $.ajax({
            url: drillUrl,
            method: 'GET',
            dataType: 'json',
            data: {
                year: ctxYear,
                month: ctxMonth,
                warehouse_id: ctxWarehouse === null ? '' : ctxWarehouse,
                category: category,
                kind: kind
            }
        }).done(function(res){
            if (!res || !res.success) {
                \$body.attr('aria-busy', 'false').html(renderError((res && res.message) || 'โหลดข้อมูลไม่สำเร็จ'));
                return;
            }
            currentItems = Array.isArray(res.items) ? res.items : [];
            currentQuery = '';
            $('#cd-kind-label').text(res.meta.kind_label);
            $('#cd-category-label').text(res.meta.category_label);
            $('#cd-period').text(res.meta.period_label + ' (พ.ศ.)');
            $('#cd-warehouse').text(res.meta.warehouse_label);
            $('#cd-count').text(res.summary.count.toLocaleString('en-US'));
            var totalVal = Number(res.summary.total_value || 0);
            $('#cd-total').text(fmt(totalVal)).toggleClass('is-negative', totalVal < 0);
            $('#cd-stat-strip').removeAttr('hidden');
            \$body.attr('aria-busy', 'false');
            applyFilter(true);  // stagger reveal on initial load only
            if (currentItems.length > 0) {
                $('#cd-filter').removeAttr('hidden');
            }
        }).fail(function(xhr, status){
            if (status === 'abort') return;
            \$body.attr('aria-busy', 'false').html(renderError('โหลดข้อมูลไม่สำเร็จ'));
        });
    }

    // Search input — debounced
    var searchTimer = null;
    $(document).on('input', '#cd-search', function(){
        var val = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function(){
            currentQuery = val;
            applyFilter();
        }, 120);
    });
    $(document).on('click', '#cd-search-clear', function(){
        $('#cd-search').val('').focus();
        currentQuery = '';
        applyFilter();
    });
    // Esc inside search → clear query first (don't close modal)
    $(document).on('keydown', '#cd-search', function(e){
        if (e.key === 'Escape' && this.value !== '') {
            e.stopPropagation();
            this.value = '';
            currentQuery = '';
            applyFilter();
        }
    });

    $(document).on('click', '.ms-report-summary .cell-drill', function(){
        var kind = this.getAttribute('data-kind');
        var category = this.getAttribute('data-category');
        if (!kind || !category) return;
        load(kind, category, this);
        if (modal) modal.show();
    });

    $(document).on('click', '#cd-retry', function(){
        if (lastTrigger) {
            var kind = lastTrigger.getAttribute('data-kind');
            var category = lastTrigger.getAttribute('data-category');
            if (kind && category) load(kind, category, lastTrigger);
        }
    });

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function(){
            if (lastTrigger && typeof lastTrigger.focus === 'function') {
                lastTrigger.focus();
            }
        });
    }

    // ══ Doctor: ตรวจ & ซ่อมปิดเดือน ══
    var doctorUrl = '{$doctorUrl}';
    var autofixUrl = '{$autofixUrl}';
    var cmdCtx = null;

    var CMD_REASON = {
        negative_qty:      { cls: 'danger',  label: 'จำนวนติดลบ' },
        value_only_desync: { cls: 'warning', label: 'มูลค่าติดลบเทียม (ซ่อมอัตโนมัติได้)' },
        zero_cost:         { cls: 'secondary', label: 'จ่ายออกไม่มีต้นทุน' }
    };

    function cmdAnalyze(){
        var wh = $('#cmd-warehouse-id').val();
        if (!wh){ $('#cmd-result').html(cmAlert('warning','bi-exclamation-triangle','กรุณาเลือกคลัง','')); return; }
        cmdCtx = { warehouse_id: wh, month: $('#cmd-month').val(), year: $('#cmd-year').val() };
        $('#cmd-result').html('<div class="cm-load"><span class="spinner-border spinner-border-sm"></span> กำลังไล่คำนวณทุกงวด…</div>');
        $('#cmd-autofix').prop('disabled', true);
        $.post(doctorUrl, cmdCtx).done(function(res){
            if (!res || !res.success){ $('#cmd-result').html(cmAlert('danger','bi-exclamation-triangle','วิเคราะห์ไม่สำเร็จ',(res&&res.message)||'')); return; }
            cmdRender(res);
        }).fail(function(){ $('#cmd-result').html(cmAlert('danger','bi-exclamation-triangle','วิเคราะห์ไม่สำเร็จ','')); });
    }

    function cmdRender(res){
        var s = res.summary || {};
        var items = res.items || [];
        var html = '';
        html += '<div class="cm-summary">'
            + cmSumCell('ไล่คำนวณ', (res.months_scanned||0), 'งวด', false)
            + cmSumCell('มูลค่าติดลบเทียม', (s.value_only_desync||0), 'รายการ', (s.value_only_desync||0)>0)
            + cmSumCell('จำนวนติดลบ', (s.negative_qty||0), 'รายการ', (s.negative_qty||0)>0)
            + cmSumCell('จ่ายไม่มีต้นทุน', (s.zero_cost||0), 'รายการ', false)
            + '</div>';

        if (!items.length){
            html += cmAlert('success','bi-check-circle-fill','ไม่พบความผิดปกติ','ทุกพัสดุมียอดคงเหลือและมูลค่าปกติตลอดช่วงที่ตรวจ');
            $('#cmd-result').html(html);
            $('#cmd-autofix').prop('disabled', true);
            return;
        }

        if ((s.value_only_desync||0) > 0){
            html += cmAlert('info','bi-wrench-adjustable','ซ่อมมูลค่าติดลบเทียมได้อัตโนมัติ',
                'กดปุ่ม "ซ่อมมูลค่าอัตโนมัติ" ด้านล่าง ระบบจะปิดเดือนใหม่ทุกงวดด้วยต้นทุนถัวเฉลี่ย มูลค่าจะไม่ติดลบเมื่อจำนวนไม่ติดลบ');
        }
        if ((s.negative_qty||0) > 0){
            html += cmAlert('danger','bi-exclamation-octagon-fill','จำนวนติดลบต้องตรวจเอง',
                'เกิดจาก order_date ผกผัน (จ่ายก่อนรับ) หรือจ่ายเกินจริง — คลิกรายการเพื่อดูประวัติ แล้วแก้ที่เอกสาร/ปรับยอด');
        }

        var multi = items.some(function(i){ return i.warehouse_name; });
        var rows = items.map(function(i){
            var r = CMD_REASON[i.primary_reason] || { cls:'secondary', label:i.primary_reason };
            return '<tr class="js-neg-history" style="cursor:pointer" '
                + 'data-item-code="' + cmEsc(i.item_code) + '" data-item-name="' + cmEsc(i.item_name) + '" '
                + 'data-warehouse-id="' + cmEsc(i.warehouse_id) + '" data-warehouse-name="' + cmEsc(i.warehouse_name||'') + '">'
                + (multi ? '<td>' + cmEsc(i.warehouse_name||'') + '</td>' : '')
                + '<td>' + cmEsc(i.item_code) + '</td>'
                + '<td>' + cmEsc(i.item_name) + '</td>'
                + '<td>' + cmEsc(i.first_bad_month) + '</td>'
                + '<td class="' + (Number(i.worst_qty)<-0.005?'is-negative':'') + '">' + Number(i.worst_qty).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}) + '</td>'
                + '<td class="' + (Number(i.worst_value)<-0.005?'is-negative':'') + '">' + cmFmt(i.worst_value) + '</td>'
                + '<td><span class="badge bg-' + r.cls + '">' + cmEsc(r.label) + '</span></td>'
                + '<td class="text-muted small">' + cmEsc(i.fix_hint) + '</td>'
                + '</tr>';
        }).join('');
        html += '<div class="cm-table-wrap"><table class="cm-table"><thead><tr>'
            + (multi ? '<th>คลัง</th>' : '')
            + '<th>รหัส</th><th>ชื่อพัสดุ</th><th>งวดแรกที่เพี้ยน</th><th>จำนวนต่ำสุด</th><th>มูลค่าต่ำสุด</th><th>เหตุ</th><th>วิธีซ่อม</th>'
            + '</tr></thead><tbody>' + rows + '</tbody></table></div>';

        $('#cmd-result').html(html);
        $('#cmd-autofix').prop('disabled', false);
    }

    $(document).on('click', '#cmd-analyze', cmdAnalyze);
    $(document).on('change', '#cmd-warehouse-id, #cmd-month, #cmd-year', function(){
        $('#cmd-result').empty(); $('#cmd-autofix').prop('disabled', true);
    });

    $(document).on('click', '#cmd-autofix', function(){
        if (!cmdCtx){ return; }
        if (!window.confirm('ซ่อมอัตโนมัติจะดำเนินการ 3 อย่าง:\\n'
            + '1) ย้ายจำนวนรับข้ามเดือน (สร้างรายการปรับยอดคู่ +/− สุทธิเป็นศูนย์) เพื่อลบยอดติดลบชั่วคราว\\n'
            + '2) เติมราคาทุนบนแถวรับเข้าที่ราคา 0 ด้วยราคาซื้อล่าสุด\\n'
            + '3) ปิดเดือนใหม่ทุกงวดด้วยต้นทุนถัวเฉลี่ย\\n\\n'
            + 'ไม่แก้ยอดคงเหลือปลายทาง/ไม่แก้เอกสารที่ใช้ร่วมหลายพัสดุ และทุกรายการปรับยอดติดแท็กย้อนกลับได้ — ดำเนินการต่อ?')){ return; }
        var \$btn = $(this).prop('disabled', true);
        \$btn.find('.cmd-autofix__label').css('opacity', 0.6);
        \$btn.find('.cmd-autofix__spinner').prop('hidden', false);
        $.post(autofixUrl, cmdCtx).done(function(res){
            \$btn.find('.cmd-autofix__label').css('opacity', 1);
            \$btn.find('.cmd-autofix__spinner').prop('hidden', true);
            if (!res || !res.success){
                $('#cmd-result').prepend(cmAlert('danger','bi-exclamation-triangle','ซ่อมไม่สำเร็จ',(res&&res.message)||''));
                \$btn.prop('disabled', false);
                return;
            }
            var parts = [];
            if (res.skipped_shortage && res.skipped_shortage.length){
                parts.push('จำนวนติดลบจริง (จ่ายเกิน ต้องตรวจนับ): ' + res.skipped_shortage.join(', '));
            }
            if (res.zero_cost_no_price && res.zero_cost_no_price.length){
                parts.push('จ่ายไม่มีต้นทุน + ไม่เคยมีราคาซื้อ (ต้องกรอกราคาเอง): ' + res.zero_cost_no_price.join(', '));
            }
            var extra = parts.length ? ('ยังต้องซ่อมมือ — ' + parts.join(' · ')) : 'ไม่มีรายการที่ต้องซ่อมมือเพิ่ม';
            $('#cmd-result').prepend(cmAlert(parts.length ? 'warning' : 'success','bi-check-circle-fill', res.message || 'ซ่อมเรียบร้อย', extra));
            cmdAnalyze(); // วิเคราะห์ซ้ำให้เห็นผลหลังซ่อม
        }).fail(function(){
            \$btn.find('.cmd-autofix__label').css('opacity', 1);
            \$btn.find('.cmd-autofix__spinner').prop('hidden', true);
            \$btn.prop('disabled', false);
            $('#cmd-result').prepend(cmAlert('danger','bi-exclamation-triangle','ซ่อมไม่สำเร็จ',''));
        });
    });
})();
JS
);
?>
