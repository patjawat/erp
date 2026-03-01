<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

$this->title = 'การลางาน';
$this->params['breadcrumbs'][] = $this->title;

$name = $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : 'ผู้ใช้';
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('../menu', ['active' => 'index']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
        <i data-lucide="calendar-check" style="width:1.25rem;height:1.25rem"></i>
    </span>
    <h4 class="fw-bold text-body mb-0">การลางาน</h4>
</div>
<?php $this->endBlock(); ?>

<?php
$lucideIconMap = [
    'calendar-check' => 'calendar-check', 'heart' => 'heart', 'droplet' => 'droplet', 'baby' => 'baby',
    'sun' => 'sun', 'stethoscope' => 'stethoscope', 'palm' => 'palmtree', 'palmtree' => 'palmtree',
    'calendar' => 'calendar', 'briefcase' => 'briefcase', 'coffee' => 'coffee', 'umbrella' => 'umbrella',
    'heart-pulse' => 'heart-pulse', 'syringe' => 'syringe', 'graduation-cap' => 'graduation-cap',
    'book-open' => 'book-open', 'church' => 'church', 'scale' => 'scale',
];
$typeTheme = [
    'LT1' => ['lucide' => 'palmtree', 'color' => '#0d9488'],
    'LT2' => ['lucide' => 'baby', 'color' => '#db2777'],
    'LT3' => ['lucide' => 'stethoscope', 'color' => '#dc2626'],
    'LT4' => ['lucide' => 'briefcase', 'color' => '#2563eb'],
    'LT5' => ['lucide' => 'heart-pulse', 'color' => '#ea580c'],
    'LT6' => ['lucide' => 'graduation-cap', 'color' => '#7c3aed'],
    'LT7' => ['lucide' => 'baby', 'color' => '#059669'],
    'LT8' => ['lucide' => 'church', 'color' => '#4b5563'],
    'LT9' => ['lucide' => 'scale', 'color' => '#0d9488'],
];
?>
<div class="container-fluid py-3">
    <!-- Card ภาพรวม 4 คอลัมน์ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="erp-icon-box bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i data-lucide="inbox" style="width:1.125rem;height:1.125rem"></i>
                        </span>
                        <span class="small fw-medium text-muted">ที่ต้องอนุมัติ</span>
                    </div>
                    <div class="fw-bold fs-4 text-body"><?= (int) ($totalLeavePending ?? 0) ?></div>
                    <div class="small text-muted">ใบลารอฉันดำเนินการ</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i data-lucide="calendar-heart" style="width:1.125rem;height:1.125rem"></i>
                        </span>
                        <span class="small fw-medium text-muted">วันลาพักผ่อนคงเหลือ</span>
                    </div>
                    <div class="fw-bold fs-4 text-body"><?= (int) ($remainingAnnualLeave ?? 0) ?></div>
                    <div class="small text-muted">วัน (ปีนี้)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="erp-icon-box bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i data-lucide="clock" style="width:1.125rem;height:1.125rem"></i>
                        </span>
                        <span class="small fw-medium text-muted">ลาของฉันที่รออนุมัติ</span>
                    </div>
                    <div class="fw-bold fs-4 text-body"><?= (int) ($myPendingLeaveCount ?? 0) ?></div>
                    <div class="small text-muted">ใบ</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="erp-icon-box bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i data-lucide="calendar-check" style="width:1.125rem;height:1.125rem"></i>
                        </span>
                        <span class="small fw-medium text-muted">ใช้ไปแล้วในปี</span>
                    </div>
                    <div class="fw-bold fs-4 text-body"><?= number_format((float) ($totalDaysUsedThisYear ?? 0), 1) ?></div>
                    <div class="small text-muted">วัน</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- คอลัมน์ซ้าย: ข้อมูลปีงบประมาณ + การ์ดประเภทลา + เกณฑ์การลา -->
        <div class="col-12 col-lg-4">
            <p class="text-muted small mb-3">ข้อมูลปีงบประมาณ <?= Html::encode($fiscalLabel) ?> — ข้อมูลด้านล่างและประวัติการลาถูกกรองตามปีที่เลือก</p>

            <?php foreach ($typeSummaries as $s):
                $code = $s['code'] ?? '';
                $theme = $typeTheme[$code] ?? null;
                if ($theme) {
                    $typeIcon = $theme['lucide'];
                    $typeColor = $theme['color'];
                } else {
                    $icon = $s['icon'] ?? 'calendar-check';
                    $typeIcon = $lucideIconMap[$icon] ?? 'calendar-check';
                    $typeColor = $s['color'] ?? '#0d6efd';
                }
                $typeColorSafe = Html::encode($typeColor);
            ?>
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-center g-2">
                        <div class="col-auto">
                            <span class="erp-icon-box rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: <?= $typeColorSafe ?>20; color: <?= $typeColorSafe ?>">
                                <i data-lucide="<?= Html::encode($typeIcon) ?>" style="width:1.25rem;height:1.25rem"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="small fw-medium text-body"><?= Html::encode($s['title']) ?></div>
                            <div class="small text-secondary"><?= (int) $s['days_used'] ?> วัน | <?= (int) $s['times_used'] ?> ครั้ง</div>
                        </div>
                        <div class="col-auto text-end">
                            <span class="small text-muted">สิทธิ์ <?= (int) $s['entitlement_days'] ?> วัน</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="card border-0 shadow-sm rounded-3 bg-primary bg-opacity-10 border-primary border-opacity-25">
                <div class="card-body p-3">
                    <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-2">
                        <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
                            <i data-lucide="pin" style="width:1.125rem;height:1.125rem"></i>
                        </span>
                        เกณฑ์การลาราชการ (ครู)
                    </h6>
                    <ul class="list-unstyled mb-0 small text-body">
                        <?php foreach ($criteriaRules as $rule): ?>
                        <li class="d-flex gap-2 mb-2">
                            <span class="rounded-circle bg-primary bg-opacity-25" style="width: 6px; height: 6px; min-width: 6px; margin-top: 0.4rem;"></span>
                            <?= Html::encode($rule) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="text-end mt-2">
                        <i data-lucide="info" class="text-primary opacity-75" style="width:1rem;height:1rem"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- คอลัมน์ขวา: ประวัติการลาล่าสุด -->
        <div class="col-12 col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <h5 class="d-flex align-items-center gap-2 fw-bold text-body mb-0">
                    <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
                        <i data-lucide="list" style="width:1.125rem;height:1.125rem"></i>
                    </span>
                    ประวัติการลาล่าสุด
                </h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-3 dropdown-toggle" type="button" id="dropdownYear" data-bs-toggle="dropdown" aria-expanded="false">
                            ปีงบประมาณ <?= (int) $thaiYear ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownYear">
                            <?php foreach ((array) $listThaiYear as $y => $label): ?>
                            <li><a class="dropdown-item" href="<?= Url::to(array_merge(['/leave/default/index'], $_GET, ['thai_year' => $y])) ?>">ปี <?= Html::encode($label) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?= Html::a('<i data-lucide="plus" style="width:1rem;height:1rem" class="me-1 align-middle"></i> สร้างใบลา', ['/leave/leave/create'], ['class' => 'btn btn-primary rounded-3']) ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small fw-semibold text-center" style="width: 3rem;">ลำดับ</th>
                                <th class="small fw-semibold">วันที่ส่ง</th>
                                <th class="small fw-semibold">ประเภท</th>
                                <th class="small fw-semibold">ช่วงเวลา</th>
                                <th class="small fw-semibold text-center">จำนวน</th>
                                <th class="small fw-semibold text-center">สถานะ</th>
                                <th class="small fw-semibold text-end">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider align-middle">
                            <?php
                            $offset = (int) ($dataProvider->pagination->offset ?? 0);
                            foreach ($dataProvider->getModels() as $index => $item):
                                $no = $offset + $index + 1;
                            ?>
                            <tr>
                                <td class="small text-center text-muted"><?= $no ?></td>
                                <td class="small">
                                    <div><?= ThaiDateHelper::formatThaiDate($item->created_at, 'short') ?></div>
                                    <div class="text-muted"><?= date('H:i', strtotime($item->created_at)) ?> น.</div>
                                </td>
                                <td class="small">
                                    <div><?= Html::encode($item->leaveType ? $item->leaveType->title : '-') ?></div>
                                    <div class="text-muted"><?= Html::encode($item->data_json['reason'] ?? '-') ?></div>
                                </td>
                                <td class="small"><?= $item->showLeaveDate() ?></td>
                                <td class="small text-center"><?= (int) $item->total_days ?></td>
                                <td class="text-center">
                                    <?php
                                    $statusLabel = $item->leaveStatus ? $item->leaveStatus->title : $item->status;
                                    $statusClass = $item->status === 'Approve' ? 'success' : ($item->status === 'Reject' ? 'danger' : 'warning');
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> border border-<?= $statusClass ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($statusLabel) ?></span>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-eye"></i>', ['/leave/leave/view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill open-modal', 'data' => ['size' => 'modal-xl'], 'title' => 'ดู']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($dataProvider->getCount() === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4 small">ยังไม่มีประวัติการลา</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent border-0 py-2">
                    <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs("if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();", \yii\web\View::POS_END);
?>
