<?php
use yii\helpers\Html;
use yii\helpers\Url;

// context: 'approve' = กล่องอนุมัติของหัวหน้า (/approve-v2), 'attendance' = หน้าผู้ดูแลระบบลงเวลา (/attendance/checkin/confirm)
$inAttendance = ($context ?? 'approve') === 'attendance';
$listRoute = $inAttendance ? '/attendance/checkin/confirm' : '/approve-v2/checkin/index';
$this->title = $inAttendance ? 'ตรวจสอบลงเวลา' : 'ยืนยันการลงเวลา';
$this->params['breadcrumbs'][] = $inAttendance
    ? ['label' => 'ระบบลงเวลา', 'url' => ['/attendance/default/index']]
    : ['label' => 'ระบบการอนุมัติ', 'url' => ['/approve-v2']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php if ($inAttendance): ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'confirm']) ?>
<?php $this->endBlock(); ?>
<?php endif; ?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock-history fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php if (!$inAttendance): ?>
<?= $this->render('@app/modules/approveV2/tab_menu', ['menu' => 'checkin']) ?>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="p-3 border-bottom">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label small mb-1">ค้นหาพนักงาน</label>
                    <input type="text" name="q" value="<?= Html::encode($q ?? '') ?>" class="form-control form-control-sm" placeholder="ชื่อ หรือ นามสกุล">
                </div>
                <div class="col-8 col-sm-4 col-md-3">
                    <label class="form-label small mb-1">บริเวณ</label>
                    <?= Html::dropDownList('loc', $loc ?? '', ['' => 'ทั้งหมด', 'in' => 'อยู่ในบริเวณ', 'out' => 'นอกบริเวณ'], ['class' => 'form-select form-select-sm']) ?>
                </div>
                <div class="col-4 col-sm-2 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> ค้นหา</button>
                    <?= Html::a('ล้าง', [$listRoute], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                </div>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h6 class="mb-0">รอยืนยัน <?= $dataProvider->getTotalCount() ?> รายการ</h6>
                <div id="bulk-bar" class="ms-auto d-none align-items-center gap-2">
                    <span class="text-body-secondary small">เลือก <strong id="bulk-count">0</strong> รายการ</span>
                    <button type="button" class="btn btn-success btn-sm btn-bulk" data-status="Pass"><i class="bi bi-check-lg"></i> ยืนยันที่เลือก</button>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-bulk" data-status="Reject"><i class="bi bi-x-lg"></i> ไม่ยืนยันที่เลือก</button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <input type="checkbox" id="check-all" class="form-check-input" aria-label="เลือกทั้งหมด" title="เลือกทั้งหมด">
                        </th>
                        <th class="text-center" style="width: 50px;">ลำดับ</th>
                        <th>พนักงาน</th>
                        <th>ตำแหน่ง</th>
                        <th>หน่วยงาน</th>
                        <th>วันเวลาที่ลงเวลา</th>
                        <th>วิธีลงเวลา</th>
                        <th>บริเวณ</th>
                        <th>สาเหตุ</th>
                        <th class="text-center" style="width: 200px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider align-middle">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <?php $record = $item->checkinRecord; if (!$record) continue; ?>
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input row-check" value="<?= $item->id ?>" aria-label="เลือกรายการ">
                        </td>
                        <td class="text-center"><?= ($dataProvider->pagination->offset + $key + 1) ?></td>
                        <td class="fw-semibold"><?= $record->employee ? Html::encode($record->employee->fname . ' ' . $record->employee->lname) : '-' ?></td>
                        <td><?= $record->employee ? Html::encode($record->employee->positionName()) : '-' ?></td>
                        <td><?= $record->employee ? Html::encode($record->employee->departmentName()) : '-' ?></td>
                        <td><?= Yii::$app->formatter->asDatetime($record->checkin_at, 'php:d/m/Y H:i') ?></td>
                        <td><?= Html::encode($record->getMethodLabel()) ?></td>
                        <td><?= $record->is_in_location ? 'อยู่ในบริเวณ' : 'นอกบริเวณ' ?><div class="small text-body-secondary"><?= Html::encode($record->out_of_location_reason ?? '') ?></div>
                            <?php if ($record->photo_path): ?><a href="<?= Url::to(['/attendance/checkin/photo', 'id' => $record->id]) ?>" target="_blank" rel="noopener" class="small"><i class="bi bi-person-bounding-box" aria-hidden="true"></i> ดูรูป</a><?php endif; ?></td>
                        <td>
                            <?php $td = $record->timeDetail(); ?>
                            <?php if ($td['expected'] !== ''): ?><div class="small text-body-secondary" style="font-variant-numeric:tabular-nums">ตามเวร <?= Html::encode($td['expected']) ?></div><?php endif; ?>
                            <?php foreach ($td['badges'] as $b): $bs = $b[0] === 'ok' ? 'success' : ($b[0] === 'no' ? 'danger' : 'warning'); ?>
                                <span class="badge bg-<?= $bs ?>-subtle text-<?= $bs ?>-emphasis me-1 mb-1"><?= Html::encode($b[1]) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                <a href="<?= Url::to(['/approve-v2/checkin/view', 'id' => $item->id]) ?>" class="btn btn-outline-primary btn-sm">ดู</a>
                                <button type="button" class="btn btn-success btn-sm btn-approve" data-id="<?= $item->id ?>" data-status="Pass">ยืนยัน</button>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-approve" data-id="<?= $item->id ?>" data-status="Reject">ไม่ยืนยัน</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="p-4 text-center text-muted">ไม่มีรายการรอยืนยัน</div>
        <?php endif; ?>
        <nav class="p-3" aria-label="หน้ารายการรอยืนยัน">
            <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
        </nav>
    </div>
</div>

<?php
$updateUrl = Url::to(['/approve-v2/checkin/update']);
$bulkUrl = Url::to(['/approve-v2/checkin/bulk-update']);
$this->registerJs(<<<JS
function refreshBulkBar() {
    var n = $('.row-check:checked').length;
    $('#bulk-count').text(n);
    $('#bulk-bar').toggleClass('d-none', n === 0).toggleClass('d-flex', n > 0);
    var total = $('.row-check').length;
    $('#check-all').prop('checked', total > 0 && n === total).prop('indeterminate', n > 0 && n < total);
}
$('#check-all').on('change', function() { $('.row-check').prop('checked', $(this).prop('checked')); refreshBulkBar(); });
$('.row-check').on('change', refreshBulkBar);

$('.btn-approve').on('click', function() {
    var id = $(this).data('id');
    var status = $(this).data('status');
    var comment = '';
    if (status === 'Reject') {
        comment = prompt('เหตุผล (ถ้ามี):');
        if (comment === null) return;
    }
    var \$btn = $(this);
    \$btn.prop('disabled', true);
    $.post('$updateUrl', { id: id, status: status, comment: comment }).then(function(r) {
        if (r.status === 'success') location.reload();
        else alert(r.message || 'เกิดข้อผิดพลาด');
    }).fail(function() { alert('บันทึกผลไม่สำเร็จ กรุณาลองใหม่'); }).always(function() { \$btn.prop('disabled', false); });
});

$('.btn-bulk').on('click', function() {
    var ids = $('.row-check:checked').map(function() { return $(this).val(); }).get();
    if (!ids.length) return;
    var status = $(this).data('status');
    var verb = status === 'Pass' ? 'ยืนยัน' : 'ไม่ยืนยัน';
    var comment = '';
    if (status === 'Reject') {
        comment = prompt('เหตุผล (ถ้ามี) สำหรับ ' + ids.length + ' รายการ:');
        if (comment === null) return;
    } else if (!confirm(verb + ' ' + ids.length + ' รายการที่เลือก?')) {
        return;
    }
    var \$btns = $('.btn-bulk');
    \$btns.prop('disabled', true);
    $.post('$bulkUrl', { ids: ids, status: status, comment: comment }).then(function(r) {
        if (r.status === 'success') {
            if (r.failed > 0) alert('สำเร็จ ' + r.done + ' รายการ, ไม่สำเร็จ ' + r.failed + ' รายการ');
            location.reload();
        } else {
            alert(r.message || 'เกิดข้อผิดพลาด');
        }
    }).fail(function() { alert('บันทึกผลไม่สำเร็จ กรุณาลองใหม่'); }).always(function() { \$btns.prop('disabled', false); });
});
JS
);
?>
