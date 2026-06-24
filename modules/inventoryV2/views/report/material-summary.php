<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'สรุปรายงานวัสดุคงคลัง';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$monthNames = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];
$periodLabel = isset($monthNames[$month]) ? $monthNames[$month] . ' ' . ($year + 543) : '';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-journal-text fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">รายงานสรุปตามประเภทวัสดุ สำหรับส่งบัญชี — เลือกเดือนและคลัง แล้วกดปิดเดือนหากยังไม่มีข้อมูล</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['/inventory-v2/main-stock/dashboard'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">
    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 px-4">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/material-summary']) ?>" id="form-material-summary">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-auto">
                        <label class="form-label small text-muted fw-semibold text-uppercase mb-1">ช่วงเวลา</label>
                        <div class="d-flex gap-2 flex-nowrap">
                        <select name="month" class="form-select" style="min-width: 130px;">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="year" class="form-select" style="min-width: 95px;">
                                <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                                    <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <label class="form-label small text-muted fw-semibold text-uppercase mb-1">คลัง</label>
                        <select name="warehouse_id" class="form-select" style="min-width: 200px;">
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
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!$hasData): ?>
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            ยังไม่มีข้อมูลปิดเดือนสำหรับเดือน<?= isset($monthNames[$month]) ? ' ' . $monthNames[$month] . ' ' . ($year + 543) : '' ?> — กรุณาเลือกคลังด้านบน แล้วกด <strong>ปิดเดือน</strong> เพื่อคำนวณและบันทึกข้อมูล
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary bg-opacity-10 py-2 px-3">
                <h6 class="mb-0 fw-normal">สรุปงานวัสดุคงคลัง — <?= Html::encode($periodLabel) ?></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 4%;">#</th>
                                <th style="width: 22%;">รายการ</th>
                                <th style="width: 10%;" class="text-end">สินค้าคงเหลือ (บาท)</th>
                                <th style="width: 10%;" class="text-end">ซื้อระหว่างเดือน (บาท)</th>
                                <th style="width: 10%;" class="text-end">รวม (บาท)</th>
                                <th style="width: 10%;" class="text-end">จ่ายส่วนของ รพ.aq (บาท)</th>
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
                            ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td><?= Html::encode($r['category_label']) ?></td>
                                    <td class="text-end"><?= number_format($r['opening_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['in_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format($totalAvail, 2) ?></td>
                                    <td class="text-end"><?= number_format($r['out_sub_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['out_hosp_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['total_out_value'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['closing_value'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-warning fw-bold">
                                <td class="text-center"></td>
                                <td>รวม</td>
                                <td class="text-end"><?= number_format($totOpening, 2) ?></td>
                                <td class="text-end"><?= number_format($totIn, 2) ?></td>
                                <td class="text-end"><?= number_format($totOpening + $totIn, 2) ?></td>
                                <td class="text-end"><?= number_format($totOutSub, 2) ?></td>
                                <td class="text-end"><?= number_format($totOutHosp, 2) ?></td>
                                <td class="text-end"><?= number_format($totOut, 2) ?></td>
                                <td class="text-end"><?= number_format($totClosing, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal ปิดเดือน -->
<div class="modal fade" id="modal-close-month" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ปิดเดือน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">ระบบจะคำนวณยอดจากธุรกรรมรับ-จ่ายในเดือนที่เลือก แล้วบันทึกลงรายงานประจำเดือน</p>
                <div class="mb-3">
                    <label class="form-label">คลัง</label>
                    <select id="close-warehouse-id" class="form-select">
                        <option value="">-- เลือกคลัง --</option>
                        <option value="all">ปิดรวมทุกคลังหลัก</option>
                        <?php foreach ($warehouses as $wid => $wname): ?>
                            <?php if ($wid !== ''): ?>
                                <option value="<?= (int)$wid ?>"><?= Html::encode($wname) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">เดือน / ปี</label>
                    <div class="d-flex gap-2">
                        <select id="close-month" class="form-select">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                            <?php endfor; ?>
                        </select>
                        <select id="close-year" class="form-select">
                            <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                                <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div id="close-month-result" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary me-2" id="btn-do-close-month">
                    <i class="bi bi-calendar-check"></i> ปิดเดือน
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<?php
$closeUrl = Url::to(['/inventory-v2/report/close-month']);
$reportUrl = Url::to(['/inventory-v2/report/material-summary']);
$this->registerJs(<<<JS
(function(){
    $('#btn-do-close-month').on('click', function(){
        var \$btn = $(this).prop('disabled', true);
        var wh = $('#close-warehouse-id').val();
        var month = $('#close-month').val();
        var year = $('#close-year').val();
        var \$result = $('#close-month-result').addClass('d-none');
        if (!wh) {
            \$result.removeClass('d-none alert-success alert-danger').addClass('alert-warning').text('กรุณาเลือกคลังหรือปิดรวมทุกคลัง').show();
            \$btn.prop('disabled', false);
            return;
        }
        $.post('{$closeUrl}', { warehouse_id: wh, month: month, year: year })
            .done(function(res){
                if (res.success) {
                    var msg = 'ปิดเดือนเรียบร้อย — รายการ ' + (res.count || 0) + ' รายการ';
                    if (res.warehouses_count > 1) msg += ' (' + res.warehouses_count + ' คลัง)';
                    \$result.removeClass('alert-danger alert-warning').addClass('alert-success').html(msg).removeClass('d-none');
                    var qs = 'year=' + year + '&month=' + month;
                    if (wh !== 'all') qs += '&warehouse_id=' + wh;
                    setTimeout(function(){ window.location.href = '{$reportUrl}?' + qs; }, 1200);
                } else {
                    \$result.removeClass('alert-success alert-warning').addClass('alert-danger').text(res.message || 'เกิดข้อผิดพลาด').removeClass('d-none');
                    \$btn.prop('disabled', false);
                }
            })
            .fail(function(){
                \$result.removeClass('alert-success alert-warning').addClass('alert-danger').text('เกิดข้อผิดพลาด').removeClass('d-none');
                \$btn.prop('disabled', false);
            });
    });
})();
JS
);
?>
