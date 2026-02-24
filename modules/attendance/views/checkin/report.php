<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = 'รายงานการเข้างาน';
$this->params['breadcrumbs'][] = ['label' => 'ลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-graph-up fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">กรองตามช่วงวันที่ พนักงาน สถานะ — ส่งออก Excel ได้</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> ประวัติลงเวลา', ['/attendance/checkin/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<a href="<?= Url::to(array_merge(['/attendance/checkin/export-excel'], Yii::$app->request->queryParams)) ?>" class="btn btn-success btn-sm" id="btn-export-attendance">
    <i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel
</a>
<?php $this->endBlock(); ?>

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
            <?= $form->field($searchModel, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                'options' => ['class' => 'form-control', 'placeholder' => 'ตั้งแต่วันที่'],
            ])->label('ตั้งแต่') ?>
        </div>
        <div class="col-12 col-md-6 col-lg-2">
            <?= $form->field($searchModel, 'date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                'options' => ['class' => 'form-control', 'placeholder' => 'ถึงวันที่'],
            ])->label('ถึง') ?>
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
                        $statusCls = $m->status === 'approved' ? 'text-bg-success' : ($m->status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning text-dark');
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
                        <td><?= Html::tag('span', $m->getStatusLabel(), ['class' => 'badge ' . $statusCls]) ?></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">ดำเนินการ</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><?= Html::a('<i class="bi bi-eye me-1"></i> ดู', ['/attendance/checkin/view', 'id' => $m->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
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
