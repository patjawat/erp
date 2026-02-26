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
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 w-100">
    <h4 class="fw-bold text-body mb-0">การลางาน</h4>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-light border rounded-3 dropdown-toggle" type="button" id="dropdownYear" data-bs-toggle="dropdown" aria-expanded="false">
                ปี <?= (int) $thaiYear ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownYear">
                <?php foreach ((array) $listThaiYear as $y => $label): ?>
                <li><a class="dropdown-item" href="<?= Url::to(array_merge(['/leave/default/index'], $_GET, ['thai_year' => $y])) ?>">ปี <?= Html::encode($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-light border rounded-3 dropdown-toggle" type="button" id="dropdownRound" data-bs-toggle="dropdown" aria-expanded="false">
                รอบที่ <?= (int) $round ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownRound">
                <li><a class="dropdown-item" href="<?= Url::to(array_merge(['/leave/default/index'], $_GET, ['round' => 1])) ?>">รอบที่ 1</a></li>
                <li><a class="dropdown-item" href="<?= Url::to(array_merge(['/leave/default/index'], $_GET, ['round' => 2])) ?>">รอบที่ 2</a></li>
            </ul>
        </div>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i> สร้างใบลา', ['/leave/leave/create'], ['class' => 'btn btn-primary rounded-3']) ?>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <div class="row g-4">
        <!-- คอลัมน์ซ้าย: ข้อมูลปีงบประมาณ + การ์ดประเภทลา + เกณฑ์การลา -->
        <div class="col-12 col-lg-4">
            <p class="text-muted small mb-3">ข้อมูลปีงบประมาณ <?= Html::encode($fiscalLabel) ?></p>

            <?php foreach ($typeSummaries as $s): ?>
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-center g-2">
                        <div class="col-auto">
                            <div class="rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 48px; height: 48px; background: linear-gradient(135deg, <?= Html::encode($s['color']) ?> 0%, <?= Html::encode($s['color']) ?>99 100%);">
                                <?php if (!empty($s['icon'])): ?>
                                    <i class="bi bi-<?= Html::encode($s['icon']) ?> text-white fs-5"></i>
                                <?php else: ?>
                                    <i class="bi bi-calendar-check text-white fs-5"></i>
                                <?php endif; ?>
                            </div>
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
                        <i class="bi bi-pin-angle-fill text-primary"></i>
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
                        <i class="bi bi-info-circle text-primary opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- คอลัมน์ขวา: ประวัติการลาล่าสุด -->
        <div class="col-12 col-lg-8">
            <h5 class="fw-bold text-body mb-3">ประวัติการลาล่าสุด</h5>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small fw-semibold">วันที่ส่ง</th>
                                <th class="small fw-semibold">ประเภท</th>
                                <th class="small fw-semibold">ช่วงเวลา</th>
                                <th class="small fw-semibold text-center">จำนวน</th>
                                <th class="small fw-semibold text-center">สถานะ</th>
                                <th class="small fw-semibold text-end">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider align-middle">
                            <?php foreach ($dataProvider->getModels() as $item): ?>
                            <tr>
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
                                    <span class="badge rounded-pill bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> border border-<?= $statusClass ?>-subtle"><?= Html::encode($statusLabel) ?></span>
                                </td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-eye"></i>', ['/me/leave/view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill open-modal', 'data' => ['size' => 'modal-xl'], 'title' => 'ดู']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($dataProvider->getCount() === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4 small">ยังไม่มีประวัติการลา</td>
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
