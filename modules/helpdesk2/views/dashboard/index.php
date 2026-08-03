<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\helpers\HelpdeskSlaHelper;

/** @var yii\web\View $this */
/** @var int $totalTickets */
/** @var int $openTickets */
/** @var int $pendingTickets */
/** @var int $inProgressTickets */
/** @var int $resolvedToday */
/** @var array $statusSummary */
/** @var Helpdesk[] $recentTickets */
/** @var array $topCategories */
/** @var array $staffWorkload */
/** @var int $slaNear */
/** @var int $slaBreached */
/** @var string|null $pageTitle */
/** @var bool $skipDashboardBreadcrumbs */

$pageTitle = $pageTitle ?? 'แดชบอร์ดงานซ่อม';
$pendingTickets = isset($pendingTickets) ? (int) $pendingTickets : (int) $openTickets;
$this->title = $pageTitle;
if (empty($skipDashboardBreadcrumbs)) {
    $this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
    $this->params['breadcrumbs'][] = $this->title;
}

$statusLabels = array_fill_keys(array_keys(Helpdesk::repairStatusMeta()), 0);
foreach ($statusSummary as $row) {
    $code = Helpdesk::normalizeRepairStatus($row['status'] ?? null);
    if (array_key_exists($code, $statusLabels)) {
        $statusLabels[$code] += (int) $row['cnt'];
    }
}

$chartLabels = json_encode(array_values(Helpdesk::repairStatusOptions()), JSON_UNESCAPED_UNICODE);
$chartData = json_encode(array_values($statusLabels));

$this->registerJsFile('@web/libs/chartjs/chart.umd.min.js', ['depends' => [\yii\web\JqueryAsset::class]]); // self-hosted chart.js@4 (เดิม jsdelivr)

$js = <<<JS
const ctx = document.getElementById('statusChart');
if (ctx) {
  const rootStyles = getComputedStyle(document.documentElement);
  const statusColors = ['warning', 'info', 'primary', 'success', 'danger'].map(function (name) {
    return rootStyles.getPropertyValue('--bs-' + name).trim();
  });
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: {$chartLabels},
      datasets: [{
        data: {$chartData},
        backgroundColor: statusColors,
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

$receiveJs = <<<JS
$(document).off('click.helpdeskDashReceive', 'a.helpdesk-dashboard-receive').on('click.helpdeskDashReceive', 'a.helpdesk-dashboard-receive', function (e) {
  e.preventDefault();
  var action = $(this);
  if (action.data('request-pending')) {
    return;
  }
  var url = $(this).attr('href');
  Swal.fire({
    title: 'ยืนยันการรับเรื่อง',
    text: 'รับเรื่องนี้แล้วระบบจะบันทึกสถานะเป็น «รับเรื่องแล้ว» และแสดงในทะเบียนงานซ่อม',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'รับเรื่อง',
    cancelButtonText: 'ยกเลิก'
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }
    action
      .data('request-pending', true)
      .addClass('disabled')
      .attr({ 'aria-disabled': 'true', 'aria-busy': 'true' });

    $.ajax({
      type: 'post',
      url: url,
      dataType: 'json',
      data: (typeof yii !== 'undefined' && typeof yii.getCsrfParam === 'function')
        ? { [yii.getCsrfParam()]: yii.getCsrfToken() }
        : {},
      success: function (response) {
        if (response && response.status === 'success') {
          Swal.fire({
            title: 'รับเรื่องแล้ว',
            icon: 'success',
            timer: 900,
            showConfirmButton: false
          }).then(function () {
            window.location.reload();
          });
        } else {
          Swal.fire('ไม่สำเร็จ', (response && response.message) ? response.message : 'ไม่สามารถรับเรื่องได้', 'error');
        }
      },
      error: function () {
        Swal.fire({
          title: 'ไม่สำเร็จ',
          text: 'ไม่สามารถรับเรื่องได้ กรุณาลองใหม่อีกครั้ง',
          icon: 'error'
        });
      },
      complete: function () {
        action
          .removeData('request-pending')
          .removeClass('disabled')
          .removeAttr('aria-disabled aria-busy');
      }
    });
  });
});
JS;
$this->registerJs($receiveJs);
?>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="small text-uppercase text-secondary fw-semibold">ทั้งหมด</div>
                    <i class="fa-solid fa-list-check text-secondary opacity-50 fs-2"></i>
                </div>
                <div class="d-flex align-items-end gap-2">
                    <span class="fw-bold text-dark lh-1 fs-2"><?= number_format($totalTickets) ?></span>
                    <span class="small text-muted mb-1">รายการ</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="small text-uppercase text-secondary fw-semibold">รอรับเรื่อง</div>
                    <i class="fa-solid fa-inbox text-secondary opacity-50 fs-2"></i>
                </div>
                <div class="d-flex align-items-end gap-2">
                    <span class="fw-bold text-dark lh-1 fs-2"><?= number_format($pendingTickets) ?></span>
                    <span class="small text-muted mb-1">รายการ</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="small text-uppercase text-secondary fw-semibold">กำลังดำเนินการ</div>
                    <i class="fa-solid fa-screwdriver-wrench text-secondary opacity-50 fs-2"></i>
                </div>
                <div class="d-flex align-items-end gap-2">
                    <span class="fw-bold text-dark lh-1 fs-2"><?= number_format($inProgressTickets) ?></span>
                    <span class="small text-muted mb-1">รายการ</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="small text-uppercase text-secondary fw-semibold">ปิดวันนี้</div>
                    <i class="fa-solid fa-circle-check text-secondary opacity-50 fs-2"></i>
                </div>
                <div class="d-flex align-items-end gap-2">
                    <span class="fw-bold text-dark lh-1 fs-2"><?= number_format($resolvedToday) ?></span>
                    <span class="small text-muted mb-1">รายการ</span>
                </div>
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
                            <?php
                            $openTotal = (int) ($row['open_total'] ?? 0);
                            $inProgressTotal = (int) ($row['in_progress_total'] ?? 0);
                            $successTotal = (int) ($row['success_total'] ?? 0);
                            $total = max(1, (int) ($row['total'] ?? ($openTotal + $inProgressTotal + $successTotal)));

                            $openPct = (int) round(($openTotal / $total) * 100);
                            $inProgressPct = (int) round(($inProgressTotal / $total) * 100);
                            $successPct = (int) round(($successTotal / $total) * 100);
                            $sumPct = $openPct + $inProgressPct + $successPct;
                            if ($sumPct !== 100) {
                                // Adjust to avoid overflow due to rounding
                                $diff = 100 - $sumPct;
                                $successPct = max(0, $successPct + $diff);
                            }
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div class="d-flex flex-column">
                                    <span class="fw-medium"><?= Html::encode($row['fullname']) ?></span>
                                    <div class="small text-muted">
                                        เปิดอยู่: <?= number_format($row['open_total']) ?> |
                                        กำลังดำเนินการ: <?= number_format($row['in_progress_total']) ?> |
                                        ปิดงานแล้ว: <?= number_format($row['success_total']) ?>
                                    </div>
                                    <div class="progress mt-2 mb-0" style="height:6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $openPct ?>%"></div>
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $inProgressPct ?>%"></div>
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $successPct ?>%"></div>
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
            <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h6 class="text-uppercase text-secondary m-0">รายการแจ้งซ่อมล่าสุด</h6>
                        <p class="small text-muted mb-0 d-none d-md-block">งานสถานะ «รอรับเรื่อง» สามารถกดรับเรื่องได้ที่คอลัมน์จัดการ — หลังรับแล้วจะไปอยู่ในทะเบียนงานซ่อมของศูนย์</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-md-none small text-muted mb-3 p-3 rounded-3 border border-secondary-subtle bg-secondary bg-opacity-10">
                    งาน «รอรับเรื่อง» ใช้ปุ่ม <strong>รับเรื่อง</strong> ในคอลัมน์จัดการ — รายการจะเข้าทะเบียนงานซ่อมหลังรับแล้ว
                </div>
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
                                <th scope="col">วันที่-เวลาที่ซ่อม</th>
                                <th scope="col">ผ่านมาแล้วกี่วัน</th>
                                <th scope="col">SLA</th>
                                <th scope="col" class="text-end text-nowrap">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php if (empty($recentTickets)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">ยังไม่มีข้อมูลแจ้งซ่อม</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTickets as $ticket): ?>
                                    <?php
                                    $empInfo = $ticket->getUserReq();
                                    $detailUrl = Url::to(['/helpdesk/service/view-v2', 'id' => $ticket->id]);
                                    $receiveUrl = Url::to(['/helpdesk/service/receive', 'id' => $ticket->id]);
                                    $canReceive = ($ticket->status === 'pending');
                                    $createdBase = !empty($ticket->receive_date) ? (string) $ticket->receive_date : (string) $ticket->created_at;
                                    $daysSinceReport = null;
                                    try {
                                        if (!empty($createdBase)) {
                                            $dtCreated = new \DateTimeImmutable((string) $createdBase);
                                            $d0 = $dtCreated->setTime(0, 0, 0);
                                            $d1 = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
                                            $daysSinceReport = $d0 > $d1 ? 0 : (int) $d0->diff($d1)->days;
                                        }
                                    } catch (\Throwable $e) {
                                        $daysSinceReport = null;
                                    }
                                    ?>
                                    <tr>
                                        <td class="fw-medium">
                                            <?= Html::a(Html::encode($ticket->repair_number), $detailUrl, ['class' => 'text-primary text-decoration-none']) ?>
                                        </td>
                                        <td class="text-break"><?= Html::encode($ticket->title) ?></td>
                                        <td><?= Html::encode($empInfo['fullname'] ?? '') ?></td>
                                        <td class="text-break small"><?= Html::encode($empInfo['department'] ?? '') ?></td>
                                        <td><?= $ticket->viewUrgent()['view'] ?? '' ?></td>
                                        <td><?= $ticket->viewStatus() ?></td>
                                        <td class="text-nowrap small"><?= Html::encode($ticket->viewCreated()['full']) ?></td>
                                        <td class="text-nowrap small">
                                            <?php if ($daysSinceReport !== null): ?>
                                                <?= Html::encode((string) $daysSinceReport) ?> วัน
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $slaBadgeHtml = HelpdeskSlaHelper::renderBadge($ticket); ?>
                                            <?php if ($slaBadgeHtml !== ''): ?>
                                                <?= $slaBadgeHtml ?>
                                            <?php else: ?>
                                                <span class="text-muted">ไม่มี SLA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex flex-wrap justify-content-end gap-1">
                                                <?= Html::a(
                                                    '<i class="bi bi-eye me-1"></i>ดู',
                                                    $detailUrl,
                                                    ['class' => 'btn btn-sm btn-outline-secondary', 'encode' => false]
                                                ) ?>
                                                <?php if ($canReceive): ?>
                                                    <?= Html::a(
                                                        '<i class="fa-solid fa-circle-exclamation me-1"></i>รับเรื่อง',
                                                        $receiveUrl,
                                                        ['class' => 'btn btn-sm btn-outline-primary helpdesk-dashboard-receive', 'encode' => false]
                                                    ) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
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
                        <?php
                        $maxCnt = 1;
                        foreach ($topCategories as $row) {
                            $maxCnt = max($maxCnt, (int) ($row['cnt'] ?? 0));
                        }
                        ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($topCategories as $row): ?>
                                <?php
                                $typeTitle = $row['device_type_id'] ? (Helpdesk::find()->where(['device_type_id' => $row['device_type_id']])->limit(1)->one()?->deviceType->title ?? 'ไม่ระบุ') : 'ไม่ระบุ';
                                $cnt = (int) ($row['cnt'] ?? 0);
                                $pct = (int) round(($cnt / $maxCnt) * 100);
                                ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span class="text-break"><?= Html::encode($typeTitle) ?></span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                                            <?= number_format($cnt) ?> งาน
                                        </span>
                                    </div>
                                    <div class="progress mt-2 mb-0" style="height:6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
