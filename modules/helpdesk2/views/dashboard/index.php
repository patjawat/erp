<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;

/** @var yii\web\View $this */
/** @var int $totalTickets */
/** @var int $openTickets */
/** @var int $inProgressTickets */
/** @var int $resolvedToday */
/** @var array $statusSummary */
/** @var Helpdesk[] $recentTickets */
/** @var array $topCategories */
/** @var array $staffWorkload */
/** @var int $slaNear */
/** @var int $slaBreached */

$this->title = 'แดชบอร์ดงานซ่อม';
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $this->title;

$statusLabels = ['open' => 0, 'in_progress' => 0, 'success' => 0, 'cancel' => 0];
foreach ($statusSummary as $row) {
    $code = $row['status'];
    if ($code === 'pending' || $code === 'receive') {
        $statusLabels['open'] += (int) $row['cnt'];
    } elseif ($code === 'in_progress') {
        $statusLabels['in_progress'] += (int) $row['cnt'];
    } elseif ($code === 'success') {
        $statusLabels['success'] += (int) $row['cnt'];
    } elseif ($code === 'cancel') {
        $statusLabels['cancel'] += (int) $row['cnt'];
    }
}

$chartLabels = json_encode(['เปิดอยู่', 'กำลังดำเนินการ', 'เสร็จสิ้น', 'ยกเลิก']);
$chartData = json_encode(array_values($statusLabels));

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$js = <<<JS
const ctx = document.getElementById('statusChart');
if (ctx) {
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: {$chartLabels},
      datasets: [{
        data: {$chartData},
        backgroundColor: ['#0d6efd', '#fd7e14', '#198754', '#6c757d'],
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'bottom',
        }
      }
    }
  });
}
JS;

$this->registerJs($js);
?>

<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="card-header border-bottom d-flex align-items-center gap-2 px-0 pb-2 mb-2">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">ทั้งหมด</h6>
                </div>
                <h3 class="fw-bold mb-0"><?= number_format($totalTickets) ?></h3>
                <small class="text-muted">รายการทั้งหมด</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="card-header border-bottom d-flex align-items-center gap-2 px-0 pb-2 mb-2">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i class="bi bi-envelope-open"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">เปิดอยู่</h6>
                </div>
                <h3 class="fw-bold mb-0 text-primary"><?= number_format($openTickets) ?></h3>
                <small class="text-muted">งานที่เปิดอยู่</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="card-header border-bottom d-flex align-items-center gap-2 px-0 pb-2 mb-2">
                    <div class="erp-icon-box bg-warning bg-opacity-10">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">กำลังดำเนินการ</h6>
                </div>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format($inProgressTickets) ?></h3>
                <small class="text-muted">กำลังดำเนินการ</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="card-header border-bottom d-flex align-items-center gap-2 px-0 pb-2 mb-2">
                    <div class="erp-icon-box bg-success bg-opacity-10">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">ปิดวันนี้</h6>
                </div>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($resolvedToday) ?></h3>
                <small class="text-muted">ปิดงานวันนี้</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-pie-chart"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">สถานะงานซ่อม</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-danger bg-opacity-10">
                    <i class="bi bi-alarm"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">ติดตาม SLA</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1 me-2">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> เกิน SLA
                    </span>
                    <span class="fw-bold"><?= number_format($slaBreached) ?></span> รายการ
                </p>
                <p class="mb-0">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1 me-2">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> ใกล้ครบกำหนด
                    </span>
                    <span class="fw-bold"><?= number_format($slaNear) ?></span> รายการ
                </p>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-info bg-opacity-10">
                    <i class="bi bi-people"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">สรุปงานซ่อมของช่าง</h6>
            </div>
            <div class="card-body">
                <?php if (empty($staffWorkload)): ?>
                    <p class="text-muted mb-0">ยังไม่มีข้อมูลภาระงาน</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($staffWorkload as $row): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div class="d-flex flex-column">
                                    <span class="fw-medium"><?= Html::encode($row['fullname']) ?></span>
                                    <div class="small text-muted">
                                        เปิดอยู่: <?= number_format($row['open_total']) ?> |
                                        กำลังดำเนินการ: <?= number_format($row['in_progress_total']) ?> |
                                        ปิดงานแล้ว: <?= number_format($row['success_total']) ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                                        รวม <?= number_format($row['total']) ?> งาน
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h6 class="text-uppercase text-secondary m-0">รายการแจ้งซ่อมล่าสุด</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">รหัสงานซ่อม</th>
                                <th scope="col">รายการ</th>
                                <th scope="col">ผู้แจ้ง</th>
                                <th scope="col">หน่วยงาน</th>
                                <th scope="col">ความเร่งด่วน</th>
                                <th scope="col">สถานะ</th>
                                <th scope="col">วันที่สร้าง</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php if (empty($recentTickets)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">ยังไม่มีข้อมูลแจ้งซ่อม</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTickets as $ticket): ?>
                                    <?php
                                    $empInfo = $ticket->getUserReq();
                                    $detailUrl = Url::to(['/helpdesk/service/view-v2', 'id' => $ticket->id]);
                                    ?>
                                    <tr class="cursor-pointer" onclick="window.location.href='<?= $detailUrl ?>'">
                                        <td class="fw-medium text-primary"><?= Html::encode($ticket->repair_number) ?></td>
                                        <td><?= Html::encode($ticket->title) ?></td>
                                        <td><?= Html::encode($empInfo['fullname']) ?></td>
                                        <td><?= Html::encode($empInfo['department']) ?></td>
                                        <td><?= $ticket->viewUrgent()['view'] ?? '' ?></td>
                                        <td><?= $ticket->viewStatus() ?></td>
                                        <td><?= Html::encode($ticket->viewCreated()['date']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm mb-3">
                <div class="card-header border-bottom d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-secondary bg-opacity-10">
                        <i class="bi bi-tags"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">หมวดปัญหายอดนิยม (ตามประเภทอุปกรณ์)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($topCategories)): ?>
                        <p class="text-muted mb-0">ยังไม่มีข้อมูลหมวดปัญหา</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($topCategories as $row): ?>
                                <?php
                                $typeTitle = $row['device_type_id'] ? (Helpdesk::find()->where(['device_type_id' => $row['device_type_id']])->limit(1)->one()?->deviceType->title ?? 'ไม่ระบุ') : 'ไม่ระบุ';
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span><?= Html::encode($typeTitle) ?></span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                                        <?= number_format($row['cnt']) ?> งาน
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

