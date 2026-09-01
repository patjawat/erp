<?php
use yii\helpers\Html;
use app\widgets\datepicker\DatepickerThai;
$this->title = 'รายการเงินเดือน';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
$this->beginBlock('page-title'); ?><div class="d-flex align-items-center gap-2"><i class="bi bi-calculator fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div><?php $this->endBlock();
$this->beginBlock('sub-title'); ?>สร้างรอบ คำนวณรายรับ–รายจ่าย และพิมพ์สลิปรายบุคคล<?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'payroll']); $this->endBlock();
$thaiMonths = [1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'];
$currentBuddhistYear = (int) date('Y') + 543;
$buddhistYears = []; for ($year = $currentBuddhistYear - 2; $year <= $currentBuddhistYear + 2; $year++) $buddhistYears[$year] = (string) $year;
$formatPeriod = static function (string $periodCode) use ($thaiMonths): string { $parts = explode('-', $periodCode); return ($thaiMonths[(int) ($parts[1] ?? 0)] ?? $periodCode) . ' ' . ((int) ($parts[0] ?? 0) + 543); };
$formatThaiDate = static function ($value): string { $timestamp = $value ? strtotime((string) $value) : false; return $timestamp ? date('d/m/', $timestamp) . ((int) date('Y', $timestamp) + 543) : '—'; };
?>
<?= $this->render('_menu', ['active' => 'reports']) ?>
<div aria-live="polite"><?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $class): if (!Yii::$app->session->hasFlash($flash)) continue; ?><div class="alert alert-<?= $class ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?><button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="ปิด"></button></div><?php endforeach; ?></div>

<section class="card border shadow-sm mb-4">
<div class="card-header bg-body"><h5 class="mb-1">สร้างรอบใหม่</h5><p class="small text-body-secondary mb-0">ใช้เฉพาะรายการรับและรายการจ่ายที่เปิดอยู่ ณ วันที่สร้างรอบ</p></div>
<div class="card-body"><?= Html::beginForm(['create-payroll-run'], 'post', ['class' => 'row g-3 align-items-end']) ?>
<div class="col-lg-3 col-md-6"><label class="form-label" for="run-type">ประเภทรอบ</label><?= Html::dropDownList('period_type', 'salary', $types, ['id' => 'run-type', 'class' => 'form-select', 'required' => true]) ?></div>
<div class="col-lg-2 col-md-3"><label class="form-label" for="run-month">เดือน</label><?= Html::dropDownList('month_number', (int) date('n'), $thaiMonths, ['id' => 'run-month', 'class' => 'form-select', 'required' => true]) ?></div>
<div class="col-lg-2 col-md-3"><label class="form-label" for="run-year">ปี พ.ศ.</label><?= Html::dropDownList('buddhist_year', $currentBuddhistYear, $buddhistYears, ['id' => 'run-year', 'class' => 'form-select', 'required' => true]) ?></div>
<div class="col-lg-3 col-md-6"><label class="form-label" for="pay-date">วันที่จ่าย</label><?= DatepickerThai::widget(['name' => 'pay_date', 'options' => ['id' => 'pay-date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?></div>
<div class="col-lg-2 col-md-6"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-play-fill me-1" aria-hidden="true"></i>สร้างรอบ</button></div>
<?= Html::endForm() ?></div></section>

<section class="card border shadow-sm">
<div class="card-header bg-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3"><div><h5 class="mb-1"><?= $period ? 'รายละเอียดรอบ' : 'รอบที่สร้างแล้ว' ?></h5><p class="small text-body-secondary mb-0"><?= $period ? Html::encode(($types[$period['period_type']] ?? $period['period_type']) . ' · ' . $formatPeriod($period['period_code'])) : number_format(count($periods)) . ' รอบ' ?></p></div><?php if ($period): ?><div class="d-flex gap-2"><?= Html::a('<i class="bi bi-file-earmark-excel me-1" aria-hidden="true"></i>ส่งออก Excel', ['export-payroll-run', 'period_id' => $period['id']], ['class' => 'btn btn-success']) ?><?= Html::a('<i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กลับรายการรอบ', ['payroll-runs'], ['class' => 'btn btn-outline-secondary']) ?></div><?php endif; ?></div>
<?php if (!$period && $periods): ?>
<div class="table-responsive"><table class="table table-hover align-middle mb-0 payroll-period-table"><thead><tr><th>รอบ</th><th>ประเภท</th><th class="text-end">บุคลากร</th><th class="text-end">รายรับ</th><th class="text-end">รายจ่าย</th><th class="text-end">คงเหลือ</th><th class="text-end">จัดการ</th></tr></thead><tbody><?php foreach ($periods as $item): ?><tr><td><strong><?= Html::encode($formatPeriod($item['period_code'])) ?></strong><?php if (!empty($item['pay_date'])): ?><div class="small text-body-secondary">จ่าย <?= Html::encode($formatThaiDate($item['pay_date'])) ?></div><?php endif; ?></td><td><span class="badge text-bg-light border"><?= Html::encode($types[$item['period_type']] ?? $item['period_type']) ?></span></td><td class="text-end"><?= number_format((int) $item['employee_count']) ?> คน</td><td class="text-end"><?= number_format((float) $item['gross_total'], 2) ?></td><td class="text-end"><?= number_format((float) $item['deduction_total'], 2) ?></td><td class="text-end fw-semibold"><?= number_format((float) $item['net_total'], 2) ?></td><td class="text-end"><?= Html::a('<i class="bi bi-eye me-1" aria-hidden="true"></i>ดูรายละเอียด', ['payroll-runs', 'period_id' => $item['id']], ['class' => 'btn btn-sm btn-outline-primary']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php elseif ($period): $gross = array_sum(array_column($rows, 'gross_amount')); $deduction = array_sum(array_column($rows, 'deduction_amount')); $net = array_sum(array_column($rows, 'net_amount')); ?>
<div class="card-body border-bottom"><div class="d-flex flex-wrap gap-4 mb-3"><div><span class="text-body-secondary small d-block">บุคลากรที่พบ</span><strong><?= number_format(count($rows)) ?> คน</strong></div><div class="ms-lg-auto text-end"><span class="text-body-secondary small d-block">รับสุทธิรวม</span><strong class="fs-5"><?= number_format($net, 2) ?> บาท</strong></div></div><?= Html::beginForm(['payroll-runs'], 'get', ['class' => 'd-flex gap-2']) ?><?= Html::hiddenInput('period_id', $period['id']) ?><?= Html::textInput('q', $query, ['class' => 'form-control', 'placeholder' => 'ค้นหาชื่อหรือเลขประชาชน', 'aria-label' => 'ค้นหารายชื่อ']) ?><button class="btn btn-outline-primary px-4" type="submit"><i class="bi bi-search me-1" aria-hidden="true"></i>ค้นหา</button><?php if ($query !== ''): ?><?= Html::a('ล้าง', ['payroll-runs', 'period_id' => $period['id']], ['class' => 'btn btn-outline-secondary']) ?><?php endif; ?><?= Html::endForm() ?></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0 payroll-run-table"><thead><tr><th>บุคลากร</th><th class="text-end">รายรับ</th><th class="text-end">รายจ่าย</th><th class="text-end">คงเหลือ</th><th class="text-end">สลิป</th></tr></thead><tbody><?php foreach ($rows as $row): $employee = $row['employee_snapshot']; ?><tr><td><strong><?= Html::encode($employee['full_name'] ?? 'ไม่ระบุชื่อ') ?></strong><div class="small text-body-secondary font-monospace"><?= Html::encode($employee['cid'] ?? '—') ?></div></td><td class="text-end"><?= number_format($row['gross_amount'], 2) ?></td><td class="text-end"><?= number_format($row['deduction_amount'], 2) ?></td><td class="text-end fw-semibold"><?= number_format($row['net_amount'], 2) ?></td><td class="text-end"><?= Html::a('<i class="bi bi-receipt me-1" aria-hidden="true"></i>ดูสลิป', ['payslip', 'id' => $row['id']], ['class' => 'btn btn-sm btn-outline-primary js-payslip-preview', 'data-name' => $employee['full_name'] ?? '']) ?></td></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="5" class="text-center text-body-secondary py-5">ไม่พบรายชื่อตามคำค้นหา</td></tr><?php endif; ?></tbody><?php if ($rows): ?><tfoot><tr class="fw-semibold"><td>รวม</td><td class="text-end"><?= number_format($gross, 2) ?></td><td class="text-end"><?= number_format($deduction, 2) ?></td><td class="text-end"><?= number_format($net, 2) ?></td><td></td></tr></tfoot><?php endif; ?></table></div>
<?php else: ?><div class="card-body text-center py-5"><i class="bi bi-calendar2-plus fs-1 text-body-secondary" aria-hidden="true"></i><strong class="d-block mt-2 mb-1">ยังไม่มีรอบเงินเดือน</strong><span class="text-body-secondary">เลือกประเภทและเดือนด้านบนเพื่อสร้างรอบแรก</span></div><?php endif; ?>
</section>
<div class="modal fade" id="payslip-preview-modal" tabindex="-1" aria-labelledby="payslip-preview-title" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
<div class="modal-header"><div><h5 class="modal-title" id="payslip-preview-title"><i class="bi bi-receipt me-2" aria-hidden="true"></i>ตัวอย่างสลิป</h5><div class="small text-body-secondary" id="payslip-preview-person"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button></div>
<div class="modal-body bg-body-tertiary p-4" id="payslip-preview-body"><div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">กำลังโหลด</span></div><div class="mt-2 text-body-secondary">กำลังโหลดสลิป...</div></div></div>
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button><button type="button" class="btn btn-primary" id="print-payslip-button"><i class="bi bi-printer me-1" aria-hidden="true"></i>พิมพ์</button></div>
</div></div></div>
<?php
$this->registerCss('.payroll-run-table,.payroll-period-table{min-width:760px}.payroll-run-table td:not(:first-child),.payroll-run-table th:not(:first-child){width:10rem;font-variant-numeric:tabular-nums}.payroll-period-table td:nth-child(n+3){font-variant-numeric:tabular-nums}.payslip-preview-sheet{background:#fff;max-width:960px;margin:auto;border-radius:.75rem!important}.payslip-preview-sheet .table{--bs-table-bg:transparent}.payslip-preview-sheet td,.payslip-preview-sheet th{padding:.65rem .75rem;font-variant-numeric:tabular-nums}.payslip-preview-sheet .payslip-lines tbody tr{height:2.65rem}.payslip-preview-sheet tfoot td{border-top-width:2px}.payslip-signature-line{max-width:240px;margin-inline:auto}@media(max-width:575.98px){#payslip-preview-body{padding:.75rem!important}.payslip-preview-sheet .card-body{padding:1rem!important}}');
$this->registerJs(<<<'JS'
document.addEventListener('click', async function (event) {
    const link = event.target.closest('.js-payslip-preview');
    if (!link) return;
    event.preventDefault();
    const modalElement = document.getElementById('payslip-preview-modal');
    const body = document.getElementById('payslip-preview-body');
    document.getElementById('payslip-preview-person').textContent = link.dataset.name || '';
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">กำลังโหลด</span></div><div class="mt-2 text-body-secondary">กำลังโหลดสลิป...</div></div>';
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
    try {
        const response = await fetch(link.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
        if (!response.ok) throw new Error('HTTP ' + response.status);
        body.innerHTML = await response.text();
    } catch (error) {
        body.innerHTML = '<div class="alert alert-danger mb-0" role="alert">ไม่สามารถโหลดสลิปได้ กรุณาลองใหม่</div>';
    }
});
document.getElementById('print-payslip-button')?.addEventListener('click', function () {
    const slip = document.querySelector('#payslip-preview-body .payslip-preview-sheet');
    if (!slip) return;
    const frame = document.createElement('iframe');
    frame.setAttribute('title', 'พิมพ์สลิป');
    frame.style.cssText = 'position:fixed;width:0;height:0;border:0;right:0;bottom:0';
    document.body.appendChild(frame);
    const styles = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(node => node.outerHTML).join('');
    frame.contentDocument.open();
    frame.contentDocument.write('<!doctype html><html lang="th"><head><meta charset="utf-8"><title>สลิปเงินเดือน</title>' + styles + '<style>@page{size:A4 portrait;margin:14mm}html,body{background:#fff!important;font-size:14px}body{margin:0}.payslip-preview-sheet{width:100%;max-width:none!important;border:1px solid #d9dee5!important;border-radius:0!important;box-shadow:none!important;page-break-inside:avoid}.payslip-preview-sheet .card-body{padding:18mm 16mm!important}.payslip-preview-sheet table{page-break-inside:avoid}</style></head><body>' + slip.outerHTML + '</body></html>');
    frame.contentDocument.close();
    setTimeout(function () {
        frame.contentWindow.focus();
        frame.contentWindow.print();
        setTimeout(() => frame.remove(), 1000);
    }, 500);
});
JS);
?>
