<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard ลงเวลา';
$this->params['breadcrumbs'][] = ['label' => 'ของฉัน', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
$name = $employee->fullname ?? trim($employee->fname . ' ' . $employee->lname);
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'checkin']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock-history fs-4 text-primary"></i>
        ระบบลงเวลาเข้างาน
    </h4>
    <p class="text-muted mb-0">สวัสดี <?= Html::encode($name) ?> — <?= (isset($isAdminOrHr) && $isAdminOrHr) ? 'Dashboard สรุปของฉันและทั้งหน่วยงาน' : 'Dashboard สรุปการลงเวลาและทางลัดการใช้งาน' ?></p>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">
    <!-- Quick action: ลงเวลา -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-4 bg-primary bg-opacity-10">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-8">
                            <h5 class="fw-bold text-body mb-1">ลงเวลาเข้างาน</h5>
                            <p class="text-muted small mb-0">กดลงเวลาวันนี้ — ระบบบันทึกพิกัดอัตโนมัติ</p>
                        </div>
                        <div class="col-12 col-md-4 text-md-end">
                            <a href="<?= Url::to(['/attendance/default/checkin']) ?>" class="btn btn-primary px-4 py-2 rounded-3">
                                <i class="bi bi-plus-circle me-2"></i>ลงเวลา
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- สถิติของฉัน -->
    <div class="mb-2">
        <h6 class="text-muted small fw-bold text-uppercase">ของฉัน</h6>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal">วันนี้</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-primary mb-0"><?= (int)$todayCount ?></p>
                    <p class="text-muted small mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal">สัปดาห์นี้</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-body mb-0"><?= (int)$weekCount ?></p>
                    <p class="text-muted small mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal">เดือนนี้</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-body mb-0"><?= (int)$monthCount ?></p>
                    <p class="text-muted small mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-warning text-dark py-2 px-3">
                    <h6 class="mb-0 small fw-normal">รออนุมัติ</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-warning mb-0"><?= (int)$pendingCount ?></p>
                    <p class="text-muted small mb-0">รายการ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- ลงเวลาล่าสุด -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 small fw-normal">ลงเวลาล่าสุด</h6>
                    <a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="text-white small text-decoration-none">ดูทั้งหมด</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($lastCheckin): ?>
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-medium"><?= Yii::$app->formatter->asDatetime($lastCheckin->checkin_at, 'php:d/m/Y H:i') ?></span>
                            <span class="badge <?= $lastCheckin->status === 'approved' ? 'text-bg-success' : ($lastCheckin->status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning text-dark') ?>"><?= $lastCheckin->getStatusLabel() ?></span>
                        </div>
                        <p class="text-muted small mb-0 mt-1"><?= $lastCheckin->getCheckTypeLabel() ?> · <?= $lastCheckin->getMethodLabel() ?></p>
                    </div>
                    <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-clock-history display-6 d-block mb-2"></i>
                        <p class="mb-0">ยังไม่มีรายการลงเวลา</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ทางลัด -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal">ทางลัด</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="<?= Url::to(['/attendance/default/checkin']) ?>" class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-1">
                                <i class="bi bi-plus-circle fs-4"></i>
                                <span class="small">ลงเวลา</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= Url::to(['/attendance/checkin/index']) ?>" class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-1">
                                <i class="bi bi-list-ul fs-4"></i>
                                <span class="small">ประวัติ</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-1">
                                <i class="bi bi-graph-up fs-4"></i>
                                <span class="small">รายงาน</span>
                            </a>
                        </div>
                        <?php if (isset($isAdminOrHr) && $isAdminOrHr): ?>
                        <div class="col-6">
                            <a href="<?= Url::to(['/attendance/location/index']) ?>" class="btn btn-outline-secondary w-100 py-3 rounded-3 d-flex flex-column align-items-center gap-1">
                                <i class="bi bi-geo-alt fs-4"></i>
                                <span class="small">จุดลงเวลา</span>
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="col-6"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($isAdminOrHr) && $statsAll !== null): ?>
    <!-- ผู้ดูแลระบบ: สรุปทั้งหน่วยงาน -->
    <hr class="my-4">
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <h6 class="text-muted small fw-bold text-uppercase mb-0">สรุปทั้งหน่วยงาน (ผู้ดูแลระบบ)</h6>
        <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="btn btn-outline-primary btn-sm">รายงาน</a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-secondary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal">วันนี้ (ทั้งหน่วย)</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-body mb-0"><?= (int)$statsAll['todayCount'] ?></p>
                    <p class="text-muted small mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-secondary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal">สัปดาห์นี้ (ทั้งหน่วย)</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-body mb-0"><?= (int)$statsAll['weekCount'] ?></p>
                    <p class="text-muted small mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-secondary text-white py-2 px-3">
                    <h6 class="mb-0 small fw-normal">เดือนนี้ (ทั้งหน่วย)</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-body mb-0"><?= (int)$statsAll['monthCount'] ?></p>
                    <p class="text-muted small mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-warning text-dark py-2 px-3">
                    <h6 class="mb-0 small fw-normal">รออนุมัติ (ทั้งหน่วย)</h6>
                </div>
                <div class="card-body p-3 text-center">
                    <p class="display-6 fw-bold text-warning mb-0"><?= (int)$statsAll['pendingCount'] ?></p>
                    <p class="text-muted small mb-0">รายการ</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-secondary text-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 small fw-normal">ลงเวลาล่าสุด (ทั้งหน่วยงาน)</h6>
                    <a href="<?= Url::to(['/attendance/checkin/report']) ?>" class="text-white small text-decoration-none">ดูรายงาน</a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentCheckinsAll)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">พนักงาน</th>
                                    <th class="small">วันเวลา</th>
                                    <th class="small">ประเภท</th>
                                    <th class="small">วิธี</th>
                                    <th class="small">สถานะ</th>
                                    <th class="small text-end">คำสั่ง</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider align-middle">
                                <?php foreach ($recentCheckinsAll as $r): ?>
                                <tr>
                                    <td><?= Html::encode($r->employee ? $r->employee->fname . ' ' . $r->employee->lname : '-') ?></td>
                                    <td class="small"><?= Yii::$app->formatter->asDatetime($r->checkin_at, 'php:d/m/Y H:i') ?></td>
                                    <td class="small"><?= $r->getCheckTypeLabel() ?></td>
                                    <td class="small"><?= $r->getMethodLabel() ?></td>
                                    <td><span class="badge <?= $r->status === 'approved' ? 'text-bg-success' : ($r->status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning text-dark') ?>"><?= $r->getStatusLabel() ?></span></td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">ดำเนินการ</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><?= Html::a('<i class="bi bi-eye me-1"></i> ดู', ['/attendance/checkin/view', 'id' => $r->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                                <li><?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['/attendance/checkin/update', 'id' => $r->id], ['class' => 'dropdown-item']) ?></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <?= Html::beginForm(['/attendance/checkin/delete', 'id' => $r->id], 'post', ['class' => 'd-inline']) ?>
                                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('ต้องการลบรายการนี้ใช่หรือไม่?');"><i class="bi bi-trash me-1"></i> ลบ</button>
                                                    <?= Html::endForm() ?>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="p-4 text-center text-muted small">ยังไม่มีรายการลงเวลา</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
