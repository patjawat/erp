<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = 'รายงานการลงเวลา (ทั้งหน่วยงาน)';
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = $this->title;

/** status → [class,label] ด้วย token semantic */
$statusBadge = function ($status, $label) {
    $cls = $status === 'approved' ? 'is-ok' : ($status === 'rejected' ? 'is-no' : 'is-wait');
    return '<span class="att-badge ' . $cls . '">' . Html::encode($label) . '</span>';
};
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-1 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-people"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">สำหรับผู้ดูแลระบบ · กรองตามช่วงวันที่ พนักงาน สถานะ แล้วส่งออก Excel</p>
</div>
<?php $this->endBlock(); ?>

<div class="att-report">
<div class="d-flex justify-content-end mb-3">
    <a href="<?= Url::to(array_merge(['/attendance/checkin/export-excel'], Yii::$app->request->queryParams)) ?>" class="btn btn-success btn-sm" id="btn-export-attendance">
        <i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel
    </a>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-light py-2 px-3">
        <h6 class="mb-0 small fw-normal">ตัวกรอง</h6>
    </div>
    <div class="card-body p-3">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['report'],
            'options' => ['class' => 'row g-2 align-items-end'],
        ]); ?>
        <div class="col-12 col-md-6 col-lg-2">
            <label class="form-label small fw-semibold text-muted mb-1">ช่วงเวลา</label>
            <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <label class="form-label small fw-semibold text-muted mb-1">ตั้งแต่</label>
            <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <label class="form-label small fw-semibold text-muted mb-1">ถึง</label>
            <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <?= $this->render('@app/components/ui/input_emp', [
                'form' => $form,
                'model' => $searchModel,
                'label' => false,
                'placeholder' => 'ค้นหาพนักงาน',
            ]) ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <?= $form->field($searchModel, 'status')->dropdownList([
                '' => 'ทุกสถานะ',
                'pending' => 'รออนุมัติ',
                'approved' => 'อนุมัติแล้ว',
                'rejected' => 'ไม่อนุมัติ',
            ], ['class' => 'form-select'])->label('สถานะ') ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <?= $form->field($searchModel, 'check_type')->dropdownList([
                '' => 'ทุกประเภท',
                'in' => 'บันทึกเข้า',
                'out' => 'บันทึกออก',
            ], ['class' => 'form-select'])->label('ประเภทการลง') ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <?= $form->field($searchModel, 'method')->dropdownList([
                '' => 'ทุกวิธี',
                'manual' => 'กดลงเวลา',
                'qrcode' => 'สแกน QR',
                'photo' => 'ถ่ายรูป',
            ], ['class' => 'form-select'])->label('วิธีลงเวลา') ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2 d-flex gap-2 flex-wrap">
            <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="bi bi-arrow-counterclockwise me-1"></i> ล้างตัวกรอง', ['/attendance/checkin/report'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$defaultShiftStart = '08:30'; // ใช้คำนวณ "สาย"
$formatLate = function ($checkinAt) use ($defaultShiftStart) {
    if (!$checkinAt) return '-';
    $t = is_string($checkinAt) ? strtotime($checkinAt) : $checkinAt;
    $start = date('Y-m-d', $t) . ' ' . $defaultShiftStart . ':00';
    $startTs = strtotime($start);
    if ($t <= $startTs) return '-';
    $diff = $t - $startTs;
    $h = floor($diff / 3600);
    $m = (int)(($diff % 3600) / 60);
    if ($h > 0 && $m > 0) return $h . ' ชม. ' . $m . ' น.';
    if ($h > 0) return $h . ' ชม.';
    return $m . ' น.';
};
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="p-3 border-bottom bg-light">
            <span class="text-muted small">พบ <?= $dataProvider->getTotalCount() ?> รายการ</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="small">ลำดับ</th>
                        <th class="small">วันที่</th>
                        <th class="small">เวลา</th>
                        <th class="small">ชื่อ-นามสกุล</th>
                        <th class="small">หน่วยงาน</th>
                        <th class="small">ประเภทการลง</th>
                        <th class="small">ประเภทเวร</th>
                        <th class="small">ชื่อเวร</th>
                        <th class="small">เวลาเวร</th>
                        <th class="small">สาย</th>
                        <th class="small">ออกก่อน</th>
                        <th class="small">รูปภาพ</th>
                        <th class="small">สถานะ</th>
                        <th class="small">คำสั่ง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($models)): ?>
                    <tr><td colspan="14" class="text-center text-muted py-4">ไม่พบรายการ</td></tr>
                    <?php else: ?>
                    <?php foreach ($models as $idx => $m): ?>
                    <?php
                        $no = $pagination ? ($pagination->offset + $idx + 1) : ($idx + 1);
                        $emp = $m->employee;
                        $dateStr = $m->checkin_at ? Yii::$app->formatter->asDate($m->checkin_at, 'php:d/m/Y') : '-';
                        $timeStr = $m->checkin_at ? Yii::$app->formatter->asTime($m->checkin_at, 'php:H:i') : '-';
                        $nameStr = $emp ? ($emp->fname . ' ' . $emp->lname) : '-';
                        $deptStr = $emp ? $emp->departmentName() : '-';
                        $workTypeStr = $emp && method_exists($emp, 'viewWorkType') ? ($emp->viewWorkType() ?: '-') : '-';
                        $shiftNameStr = $emp && !empty($emp->work_shift) ? ($emp->work_shift === 'normal' ? 'ปกติ' : 'เวร') : '-';
                    ?>
                    <tr>
                        <td><?= (int)$no ?></td>
                        <td><?= Html::encode($dateStr) ?></td>
                        <td><?= Html::encode($timeStr) ?></td>
                        <td><?= Html::encode($nameStr) ?></td>
                        <td><?= Html::encode($deptStr) ?></td>
                        <td><?= Html::encode($m->getCheckTypeLabel()) ?></td>
                        <td><?= Html::encode($workTypeStr) ?></td>
                        <td><?= Html::encode($shiftNameStr) ?></td>
                        <td>08:30-16:30</td>
                        <td><?= Html::encode($formatLate($m->checkin_at)) ?></td>
                        <td>-</td>
                        <td>
                            <?php if (empty($m->photo_path)): ?>
                                <span class="text-muted" title="ไม่มีรูป"><i class="bi bi-image"></i></span>
                            <?php else: ?>
                                <?= Html::a('<i class="bi bi-image-fill text-primary"></i>', Url::to('@web/' . $m->photo_path), ['target' => '_blank', 'rel' => 'noopener', 'title' => 'ดูรูป']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= $statusBadge($m->status, $m->getStatusLabel()) ?></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">ดำเนินการ</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><?= Html::a('<i class="bi bi-eye me-1"></i> ดู', ['/attendance/checkin/view', 'id' => $m->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                    <li><?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['/attendance/checkin/update', 'id' => $m->id], ['class' => 'dropdown-item']) ?></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <?= Html::beginForm(['/attendance/checkin/delete', 'id' => $m->id], 'post', ['class' => 'att-del-form d-inline', 'data' => ['name' => ($emp ? $emp->fname . ' ' . $emp->lname : 'รายการนี้')]]) ?>
                                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-1"></i> ลบ</button>
                                        <?= Html::endForm() ?>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pagination && $pagination->getPageCount() > 1): ?>
        <div class="d-flex justify-content-center py-3">
            <?= LinkPager::widget([
                'pagination' => $pagination,
                'options' => ['class' => 'pagination mb-0'],
                'maxButtonCount' => 5,
            ]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div><!-- /.att-report -->

<style>
.att-report{--success:#15803d;--success-soft:rgba(21,128,61,.1);--warning:#b45309;--warning-soft:rgba(180,83,9,.1);--danger:#b91c1c;--danger-soft:rgba(185,28,28,.1)}
.att-report .att-badge{display:inline-flex;align-items:center;padding:.15rem .55rem;border-radius:999px;font-size:.76rem;font-weight:600;white-space:nowrap}
.att-report .att-badge.is-ok{background:var(--success-soft);color:var(--success)}
.att-report .att-badge.is-wait{background:var(--warning-soft);color:var(--warning)}
.att-report .att-badge.is-no{background:var(--danger-soft);color:var(--danger)}
.att-swal{border-radius:12px!important}
.att-swal .swal2-confirm,.att-swal .swal2-cancel{border-radius:8px!important}
</style>

<?php
$this->registerJs(<<<JS
$(document).on('submit', '.att-del-form', function(e){
    var form = this;
    if (form.dataset.confirmed === '1') return true;
    e.preventDefault();
    var name = form.getAttribute('data-name') || 'รายการนี้';
    if (!window.Swal) {
        if (confirm('ต้องการลบรายการลงเวลาของ ' + name + ' ใช่หรือไม่?')) { form.dataset.confirmed = '1'; form.submit(); }
        return;
    }
    Swal.fire({
        icon: 'warning',
        title: 'ลบรายการลงเวลา',
        html: 'ต้องการลบรายการของ <strong>' + $('<div>').text(name).html() + '</strong> ใช่หรือไม่?<br>การลบนี้ย้อนกลับไม่ได้',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash"></i> ลบรายการ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#b91c1c',
        reverseButtons: true,
        customClass: { popup: 'att-swal' }
    }).then(function(res){ if (res.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); } });
});
JS
);
?>
