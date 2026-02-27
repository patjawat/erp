<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use app\components\ThaiDateHelper;

$roleChartLabels = [];
$roleChartSeries = [];
if (!empty($roleDetails)) {
    foreach ($roleDetails as $roleName => $detail) {
        $roleChartLabels[] = $detail['description'] . ' (' . $roleName . ')';
        $roleChartSeries[] = (int) $detail['count'];
    }
}

/** @var yii\web\View $this */
/** @var int $totalUsers */
/** @var int $activeUsers */
/** @var int $inactiveUsers */
/** @var int $rolesCount */
/** @var int $activeSessions */
/** @var array $usersPerRole ชื่อบทบาท => จำนวนคน */
/** @var array $roleDetails ชื่อบทบาท => ['description' => string, 'count' => int] */
/** @var yii\data\ActiveDataProvider $recentProvider */

$this->title = 'ภาพรวมระบบจัดการผู้ใช้งาน';
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 w-100">
    <h4 class="fw-bold text-body mb-0 d-flex align-items-center gap-2">
        <span class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
            <i class="bi bi-person-gear fs-4"></i>
        </span>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?= Html::a('<i class="bi bi-grid-1x2 me-1"></i> ภาพรวม', ['/usermanager/default/dashboard'], ['class' => 'btn btn-success rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-signpost-2 me-1"></i> เส้นทาง', ['/usermanager/router'], ['class' => 'btn btn-outline-secondary rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-person-badge me-1"></i> บทบาท', ['/usermanager/role'], ['class' => 'btn btn-outline-danger rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-people me-1"></i> ผู้ใช้งานระบบ', ['/usermanager/user'], ['class' => 'btn btn-outline-primary rounded-3 link-loading']) ?>
    <?= Html::a('<i class="bi bi-box-arrow-in-right me-1"></i> เซสชัน', ['/usermanager/session'], ['class' => 'btn btn-outline-info rounded-3 link-loading']) ?>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-primary rounded-start">
                    <div>
                        <p class="text-muted small mb-1">ผู้ใช้งานทั้งหมด</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($totalUsers) ?></h3>
                    </div>
                    <div class="text-primary opacity-75">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-success rounded-start">
                    <div>
                        <p class="text-muted small mb-1">กำลังใช้งาน</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($activeUsers) ?></h3>
                    </div>
                    <div class="text-success opacity-75">
                        <i class="bi bi-person-check fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-secondary rounded-start">
                    <div>
                        <p class="text-muted small mb-1">ปิดใช้งาน</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($inactiveUsers) ?></h3>
                    </div>
                    <div class="text-secondary opacity-75">
                        <i class="bi bi-person-x fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-danger rounded-start">
                    <div>
                        <p class="text-muted small mb-1">บทบาท (Roles)</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($rolesCount) ?></h3>
                    </div>
                    <div class="text-danger opacity-75">
                        <i class="bi bi-person-badge fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-info rounded-start">
                    <div>
                        <p class="text-muted small mb-1">กำลังออนไลน์</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($activeSessions) ?></h3>
                    </div>
                    <div class="text-info opacity-75">
                        <i class="bi bi-broadcast fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- จำนวนผู้ใช้แยกตามบทบาท -->
    <?php if (!empty($roleDetails)): ?>
    <div class="row g-4 mb-4">
        <!-- Chart จำนวนผู้ใช้แยกตามบทบาท -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="fw-bold text-body mb-0"><i class="bi bi-pie-chart me-2"></i> จำนวนผู้ใช้แยกตามบทบาท</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($roleChartSeries)): ?>
                    <div id="usersByRoleChart" style="min-height: 320px;"></div>
                    <?php else: ?>
                    <div class="text-center text-muted py-5">ยังไม่มีข้อมูล</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- ตาราง -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold text-body mb-0"><i class="bi bi-person-badge me-2"></i> รายละเอียดบทบาท</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?= Html::a('<i class="bi bi-people me-1"></i> ดูรายชื่อทั้งหมด', ['/usermanager/default/users-by-role'], ['class' => 'btn btn-outline-primary rounded-3 link-loading']) ?>
                        <?= Html::a('จัดการบทบาท', ['/usermanager/role'], ['class' => 'btn btn-outline-danger rounded-3 link-loading']) ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small fw-semibold text-start">รายละเอียด (ชื่อ role)</th>
                                    <th class="small fw-semibold text-end">จำนวนผู้ใช้ (คน)</th>
                                    <th class="small fw-semibold text-center">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle table-group-divider">
                                <?php foreach ($roleDetails as $roleName => $detail): ?>
                                <tr>
                                    <td class="text-start"><?= Html::encode($detail['description']) ?> <span class="text-muted">(<?= Html::encode($roleName) ?>)</span></td>
                                    <td class="text-end fw-medium"><?= number_format($detail['count']) ?></td>
                                    <td class="text-center">
                                        <?= Html::a('<i class="bi bi-people me-1"></i> ดูรายชื่อ', ['/usermanager/default/users-by-role', 'role' => $roleName], ['class' => 'btn btn-sm btn-outline-primary rounded-pill link-loading']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick actions + Recent users -->
    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 py-3">
                    <h5 class="fw-bold text-body mb-0">เมนูจัดการ</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-column gap-2">
                        <?= Html::a('<i class="bi bi-signpost-2 me-2"></i> จัดการเส้นทาง (Router)', ['/usermanager/router'], ['class' => 'btn btn-light border rounded-3 text-start link-loading']) ?>
                        <?= Html::a('<i class="bi bi-person-badge me-2"></i> จัดการบทบาท (Role)', ['/usermanager/role'], ['class' => 'btn btn-light border rounded-3 text-start link-loading']) ?>
                        <?= Html::a('<i class="bi bi-people me-2"></i> รายชื่อผู้ใช้แยกตามบทบาท', ['/usermanager/default/users-by-role'], ['class' => 'btn btn-light border rounded-3 text-start link-loading']) ?>
                        <?= Html::a('<i class="bi bi-shield-lock me-2"></i> สิทธิ์ (Permission)', ['/usermanager/permission'], ['class' => 'btn btn-light border rounded-3 text-start link-loading']) ?>
                        <?= Html::a('<i class="bi bi-people me-2"></i> ผู้ใช้งานระบบ', ['/usermanager/user'], ['class' => 'btn btn-light border rounded-3 text-start link-loading']) ?>
                        <?= Html::a('<i class="bi bi-box-arrow-in-right me-2"></i> เซสชันที่เข้าสู่ระบบ', ['/usermanager/session'], ['class' => 'btn btn-light border rounded-3 text-start link-loading']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold text-body mb-0">ผู้ใช้งานล่าสุด</h5>
                    <?= Html::a('ดูทั้งหมด', ['/usermanager/user'], ['class' => 'btn btn-outline-primary rounded-3 link-loading']) ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small fw-semibold text-start">ชื่อเข้าใช้งาน</th>
                                    <th class="small fw-semibold text-start">ชื่อ-นามสกุล</th>
                                    <th class="small fw-semibold text-center">สถานะ</th>
                                    <th class="small fw-semibold text-end">อัปเดตเมื่อ</th>
                                    <th class="small fw-semibold text-center">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle table-group-divider">
                                <?php foreach ($recentProvider->getModels() as $model): ?>
                                <tr>
                                    <td class="text-start"><?= Html::encode($model->username) ?></td>
                                    <td class="text-start"><?= Html::encode($model->employee ? $model->employee->fullname : '-') ?></td>
                                    <td class="text-center">
                                        <?php
                                        $isActive = $model->status == \app\modules\usermanager\models\User::STATUS_ACTIVE;
                                        $badgeClass = $isActive
                                            ? 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1'
                                            : 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1';
                                        ?>
                                        <span class="<?= $badgeClass ?>"><?= Html::encode($model->statusName) ?></span>
                                    </td>
                                    <td class="text-end small text-muted"><?= $model->updated_at ? ThaiDateHelper::formatThaiDate($model->updated_at, 'short') : '-' ?></td>
                                    <td class="text-center">
                                        <?= Html::a('<i class="bi bi-eye"></i>', ['/usermanager/user/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary rounded-pill', 'title' => 'ดู']) ?>
                                        <?= Html::a('<i class="bi bi-pencil"></i>', ['/usermanager/user/update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill link-loading', 'title' => 'แก้ไข']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if ($recentProvider->getCount() === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">ยังไม่มีผู้ใช้งานในระบบ</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($recentProvider->getCount() > 0 && $recentProvider->pagination && $recentProvider->pagination->pageCount > 1): ?>
                    <div class="d-flex justify-content-center border-top p-2">
                        <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $recentProvider->pagination]) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($roleChartSeries)): ?>
<?php
$roleChartLabelsJson = Json::encode($roleChartLabels);
$roleChartSeriesJson = Json::encode($roleChartSeries);
$chartHeight = max(320, count($roleChartLabels) * 36);
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ApexCharts === 'undefined') return;
    var el = document.querySelector('#usersByRoleChart');
    if (!el) return;
    var labels = <?= $roleChartLabelsJson ?>;
    var seriesData = <?= $roleChartSeriesJson ?>;
    if (!seriesData.length) return;
    var options = {
        series: [{ name: 'จำนวน (คน)', data: seriesData }],
        chart: { type: 'bar', height: <?= (int) $chartHeight ?>, fontFamily: 'Sarabun, sans-serif', toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, barHeight: '75%', borderRadius: 4, dataLabels: { position: 'top' } } },
        dataLabels: { enabled: true, formatter: function(v) { return v != null ? Number(v).toLocaleString() + ' คน' : ''; } },
        xaxis: { categories: labels, title: { text: 'จำนวนผู้ใช้ (คน)' }, labels: { formatter: function(v) { return Number(v).toLocaleString(); } } },
        yaxis: { labels: { maxWidth: 240 } },
        colors: ['#0d6efd'],
        grid: { xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } }
    };
    new ApexCharts(el, options).render();
});
</script>
<?php endif; ?>
