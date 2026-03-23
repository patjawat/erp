<?php

use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\HelpdeskSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนงานซ่อม';

$badgeClass = static function (string $color): string {
    return 'badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1';
};

$priorityMap = [
    '1' => ['label' => 'ต่ำ', 'color' => 'primary'],
    '2' => ['label' => 'ปานกลาง', 'color' => 'info'],
    '3' => ['label' => 'สูง', 'color' => 'warning'],
    '4' => ['label' => 'วิกฤต', 'color' => 'danger'],
    'low' => ['label' => 'ต่ำ', 'color' => 'primary'],
    'medium' => ['label' => 'ปานกลาง', 'color' => 'info'],
    'high' => ['label' => 'สูง', 'color' => 'warning'],
    'critical' => ['label' => 'วิกฤต', 'color' => 'danger'],
];

$statusMeta = [
    'pending' => ['label' => 'เปิดงาน', 'color' => 'warning'],
    'receive' => ['label' => 'รับเรื่อง', 'color' => 'info'],
    'in_progress' => ['label' => 'กำลังดำเนินการ', 'color' => 'info'],
    'success' => ['label' => 'เสร็จสิ้น', 'color' => 'success'],
    'cancel' => ['label' => 'ยกเลิก', 'color' => 'danger'],
];

/** ลำดับขั้นตอนสำหรับแสดง workflow (อิงค่า status เดิมในระบบ) */
$workflowSteps = [
    ['code' => 'pending', 'label' => 'เปิดงาน'],
    ['code' => 'receive', 'label' => 'รับเรื่อง'],
    ['code' => 'in_progress', 'label' => 'ดำเนินการ'],
    ['code' => 'success', 'label' => 'เสร็จสิ้น'],
];

?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">

        <?= $icon ?> <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/helpdesk2/menu', ['active' => $active]) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/helpdesk2/views/service/_search', ['model' => $searchModel]) ?>
    </div>
</div>
<div class="d-flex justify-content-between">
    <h6 class="mt-2">
        <i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม
            <?php echo number_format($dataProvider->getTotalCount(), 0) ?> รายการ
    </h6>
    <div class="d-flex justify-content-between gap-3">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/me/repair-v2/create', 'title' => '<i class="fa-solid fa-screwdriver-wrench"></i> แจ้งซ่อม'], ['class' => 'btn btn-sm btn-light shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    </div>
</div>

<?php
$kpiTotal = (int) $dataProvider->getTotalCount();
$statusKpi = [
    'pending' => 0,
    'receive' => 0,
    'in_progress' => 0,
    'success' => 0,
    'cancel' => 0,
];
if (isset($dataProvider->query)) {
    try {
        foreach (array_keys($statusKpi) as $code) {
            $q = clone $dataProvider->query;
            $statusKpi[$code] = (int) $q
                ->limit(-1)
                ->offset(-1)
                ->orderBy([])
                ->andWhere(['status' => $code])
                ->count();
        }
    } catch (\Throwable $e) {
        // fallback: ถ้านับจาก query ไม่ได้ จะใช้ค่าเริ่มต้น 0
    }
}
$kpiOpen = $statusKpi['pending'] + $statusKpi['receive'] + $statusKpi['in_progress'];
$kpiDone = $statusKpi['success'];
$kpiCompletionRate = $kpiTotal > 0 ? (int) round(($kpiDone / $kpiTotal) * 100) : 0;
?>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h6 class="mb-0"><i class="bi bi-speedometer2 me-1"></i> สรุป KPI งานซ่อม</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                งานทั้งหมด <?= number_format($kpiTotal) ?> รายการ
            </span>
        </div>

        <div class="row g-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                    <div class="small text-muted">เสร็จสิ้น</div>
                    <div class="h5 mb-0 text-success"><?= number_format($kpiDone) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                    <div class="small text-muted">ค้างดำเนินการ</div>
                    <div class="h5 mb-0 text-warning"><?= number_format($kpiOpen) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                    <div class="small text-muted">เปิดงาน</div>
                    <div class="h5 mb-0"><?= number_format($statusKpi['pending']) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                    <div class="small text-muted">รับเรื่อง/กำลังทำ</div>
                    <div class="h5 mb-0 text-info"><?= number_format($statusKpi['receive'] + $statusKpi['in_progress']) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                    <div class="small text-muted">ยกเลิก</div>
                    <div class="h5 mb-0 text-danger"><?= number_format($statusKpi['cancel']) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                    <div class="small text-muted">อัตราปิดงาน</div>
                    <div class="h5 mb-1 text-primary"><?= $kpiCompletionRate ?>%</div>
                    <div class="progress" role="progressbar" aria-label="อัตราปิดงาน" aria-valuenow="<?= $kpiCompletionRate ?>" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: <?= $kpiCompletionRate ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <?php $models = $dataProvider->getModels(); ?>
        <div class="d-flex flex-column gap-3" role="list">
            <?php foreach ($models as $key => $item): ?>
                <?php
                $req = $item->getUserReq();
                $urgencyCode = is_array($item->data_json ?? null) ? ($item->data_json['urgency'] ?? null) : null;
                $pInfo = is_scalar($urgencyCode) ? ($priorityMap[(string) $urgencyCode] ?? ['label' => 'ไม่ระบุ', 'color' => 'secondary']) : ['label' => 'ไม่ระบุ', 'color' => 'secondary'];
                $priorityBadge = Html::tag('span', Html::encode($pInfo['label']), ['class' => $badgeClass($pInfo['color'])]);

                $statusCode = (string) ($item->status ?? 'pending');
                $sInfo = $statusMeta[$statusCode] ?? ['label' => ($item->repairStatus?->title ?? 'ไม่ทราบสถานะ'), 'color' => 'secondary'];
                $statusBadge = Html::tag('span', Html::encode($sInfo['label']), ['class' => $badgeClass($sInfo['color'])]);

                $location = '-';
                if (is_array($item->data_json ?? null)) {
                    $loc = $item->data_json['location'] ?? null;
                    $location = $loc !== null && $loc !== '' ? (string) $loc : '-';
                }
                $createdLabel = $item->viewCreated()['full'] ?? '-';
                /** จำนวนวันนับจากวันที่แจ้ง (created_at) — ใช้เฉพาะคำนวณใน view */
                $daysSinceReport = null;
                if (!empty($item->created_at)) {
                    try {
                        $dtCreated = new \DateTimeImmutable((string) $item->created_at);
                        $d0 = $dtCreated->setTime(0, 0, 0);
                        $d1 = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
                        if ($d0 > $d1) {
                            $daysSinceReport = 0;
                        } else {
                            $daysSinceReport = (int) $d0->diff($d1)->days;
                        }
                    } catch (\Throwable $e) {
                        $daysSinceReport = null;
                    }
                }
                $deviceLabel = $item->deviceType->title ?? '-';
                $titleText = (string) ($item->title ?? '');
                $summaryTitle = mb_strlen($titleText) > 72 ? mb_substr($titleText, 0, 72) . '…' : $titleText;

                $channelLabel = '-';
                try {
                    $channelLabel = $item->viewRepairChannelLabel();
                } catch (\Throwable $e) {
                    $channelLabel = '-';
                }

                $flowIndex = array_search($statusCode, array_column($workflowSteps, 'code'), true);
                $knownInFlow = $flowIndex !== false;
                if ($flowIndex === false) {
                    $flowIndex = -1;
                }
                $isCancel = ($statusCode === 'cancel');
                ?>
                <div role="listitem">
                    <div class="min-w-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                            <?php /* —— Summary: อ่านเร็วในไม่กี่วินาที —— */ ?>
                            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2 gap-lg-3 pb-3">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                                            #<?= (($dataProvider->pagination->offset + 1) + $key) ?>
                                        </span>
                                        <span class="font-monospace small fw-semibold text-primary"><?= Html::encode($item->repair_number ?? '-') ?></span>
                                        <span class="text-muted small">
                                            <i class="bi bi-clock me-1" aria-hidden="true"></i><?= Html::encode($createdLabel) ?>
                                        </span>
                                        <span class="text-muted" aria-hidden="true">·</span>
                                        <span class="small fw-medium text-body"><?= Html::encode($deviceLabel) ?></span>
                                        <?php if (!empty($item->asset_number)): ?>
                                            <span class="text-muted small font-monospace"><?= Html::encode($item->asset_number) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-0 text-body lh-sm"><?= Html::encode($summaryTitle !== '' ? $summaryTitle : '-') ?></p>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
                                    <?php if ($daysSinceReport !== null): ?>
                                        <?php
                                        $agingColor = $daysSinceReport <= 3 ? 'secondary' : ($daysSinceReport <= 7 ? 'warning' : 'danger');
                                        ?>
                                        <?= Html::tag('span', 'ผ่านมาแล้ว ' . $daysSinceReport . ' วัน', ['class' => $badgeClass($agingColor), 'title' => 'นับจากวันที่แจ้งซ่อม']) ?>
                                    <?php endif; ?>
                                    <?= $priorityBadge ?>
                                    <?= $statusBadge ?>
                                </div>
                            </div>

                            <?php /* —— รายละเอียดเสริม (ไม่ซ้ำกับ Summary) —— */ ?>
                            <div class="row g-3 pt-3">
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="small text-muted mb-1">ผู้แจ้ง</div>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php
                                            $avatarHtml = trim((string) ($req['avatar'] ?? ''));
                                            $reqName = trim((string) ($req['fullname'] ?? ''));
                                            $initialsStr = '';
                                            if ($reqName !== '') {
                                                $parts = preg_split('/\s+/u', $reqName, -1, PREG_SPLIT_NO_EMPTY);
                                                if (count($parts) >= 2) {
                                                    $initialsStr = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
                                                } elseif (count($parts) === 1) {
                                                    $initialsStr = mb_strtoupper(mb_substr($parts[0], 0, 1));
                                                }
                                            }
                                            if ($initialsStr === '') {
                                                $initialsStr = '?';
                                            }
                                        ?>
                                        <?php if ($avatarHtml !== ''): ?>
                                            <div class="flex-shrink-0"><?= $req['avatar'] ?></div>
                                        <?php else: ?>
                                            <div class="avatar avatar-md m-0 flex-shrink-0 rounded-circle border border-primary border-opacity-25 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-semibold user-select-none"
                                                 role="img"
                                                 aria-label="<?= Html::encode($reqName !== '' ? $reqName : 'ผู้แจ้ง') ?>">
                                                <?= Html::encode($initialsStr) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <div class="fw-medium text-truncate" title="<?= Html::encode($req['fullname'] ?? '') ?>"><?= Html::encode($req['fullname'] ?? '-') ?></div>
                                            <div class="text-muted small text-truncate" title="<?= Html::encode($req['department'] ?? '') ?>"><?= Html::encode($req['department'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="small text-muted mb-1">เลขครุภัณฑ์/ทรัพย์สิน</div>
                                    <div class="font-monospace"><?= Html::encode($item->asset_number ?: '-') ?></div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="small text-muted mb-1">สถานที่</div>
                                    <div class="text-break"><?= Html::encode($location) ?></div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="small text-muted mb-1">ช่องทางแจ้งซ่อม</div>
                                    <div class="mb-1"><?= Html::encode($channelLabel) ?></div>
                                </div>
                            </div>

                            <?php /* —— Workflow (จาก status เดิม) —— */ ?>
                            <div class="pt-3 mt-3">
                                <div class="small text-muted mb-2">ขั้นตอนงาน</div>
                                <?php
                                $totalSteps = count($workflowSteps);
                                $progressPercent = 0;
                                $progressColor = 'secondary';
                                if ($isCancel) {
                                    $progressPercent = 0;
                                    $progressColor = 'danger';
                                } elseif ($flowIndex >= 0 && $totalSteps > 0) {
                                    $progressPercent = (int) round((($flowIndex + 1) / $totalSteps) * 100);
                                    if ($statusCode === 'success') {
                                        $progressColor = 'success';
                                    } elseif ($statusCode === 'in_progress') {
                                        $progressColor = 'info';
                                    } else {
                                        $progressColor = 'primary';
                                    }
                                }
                                ?>
                                <div class="mb-2">
                                    <div class="progress" role="progressbar" aria-label="ความคืบหน้างานซ่อม" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                                        <div class="progress-bar bg-<?= Html::encode($progressColor) ?>" style="width: <?= $progressPercent ?>%"></div>
                                    </div>
                                    <div class="small text-muted mt-1">ความคืบหน้า <?= $progressPercent ?>%</div>
                                </div>
                                <?php if ($isCancel): ?>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <?php foreach ($workflowSteps as $i => $step): ?>
                                            <?php if ($i > 0): ?>
                                                <span class="text-muted small" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                                            <?php endif; ?>
                                            <?= Html::tag(
                                                'span',
                                                Html::tag('i', '', ['class' => 'bi bi-hourglass-split me-1', 'aria-hidden' => 'true']) . Html::encode($step['label']),
                                                ['class' => $badgeClass('secondary') . ' text-decoration-line-through opacity-50']
                                            ) ?>
                                        <?php endforeach; ?>
                                        <span class="text-muted small" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                                        <?= Html::tag('span', Html::encode($statusMeta['cancel']['label']), ['class' => $badgeClass('danger')]) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap align-items-center gap-1 gap-sm-2">
                                        <?php foreach ($workflowSteps as $si => $step): ?>
                                            <?php
                                            $isDone = $flowIndex >= 0 && $si < $flowIndex;
                                            $isCurrent = $flowIndex >= 0 && $si === $flowIndex;
                                            if ($isDone) {
                                                $stepClass = $badgeClass('success');
                                            } elseif ($isCurrent) {
                                                $stepClass = $badgeClass('primary');
                                            } else {
                                                $stepClass = $badgeClass('secondary') . ' opacity-75';
                                            }
                                            ?>
                                            <?php if ($si > 0): ?>
                                                <span class="text-muted small px-0"><i class="bi bi-chevron-right"></i></span>
                                            <?php endif; ?>
                                            <?php
                                            $stepIcon = $isDone ? 'bi-check-circle-fill' : 'bi-hourglass-split';
                                            $stepLabelHtml = Html::tag('i', '', ['class' => 'bi ' . $stepIcon . ' me-1', 'aria-hidden' => 'true']) . Html::encode($step['label']);
                                            ?>
                                            <?= Html::tag('span', $stepLabelHtml, ['class' => $stepClass]) ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="small text-muted mb-0 mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        สถานะปัจจุบัน: <span class="fw-medium text-body"><?= Html::encode($sInfo['label']) ?></span>
                                        <?php if (!$knownInFlow): ?>
                                            <span class="text-muted">(สถานะนี้ไม่อยู่ในลำดับมาตรฐาน — แสดงตามข้อมูลจริง)</span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-wrap justify-content-end gap-2 pt-3 mt-3">
                                <?php if ($item->status == 'pending'): ?>
                                    <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> รับงานซ่อม', ['/helpdesk/service/receive', 'id' => $item->id], ['class' => 'receive-order btn btn-sm btn-outline-primary']); ?>
                                <?php else: ?>
                                    <?= $this->render('@app/modules/helpdesk2/views/service/action', ['item' => $item]); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="d-flex justify-content-center mt-3">
            <div class="text-muted">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>


<?php
$js = <<< JS
$('body').on('click', '.receive-order', function (e) {
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการรับงาน?',
        text: "คุณแน่ใจหรือไม่ว่าจะรับงานนี้?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'ใช่, รับงาน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "get",
                url: url,
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'รับงานสำเร็จ!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload(); // โหลดหน้าใหม่หลังจากแจ้งเตือน
                        });
                    } else {
                        Swal.fire('ผิดพลาด', response.message || 'ไม่สามารถรับงานได้', 'error');
                    }
                },
                error: function () {
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                }
            });
        }
    });
});


$("body").on("click", ".delete-repair-item", async function (e) {
  e.preventDefault();
  var url = $(this).attr("href");
  // console.log('delete',url);
  // $('#main-modal').modal('show');

  await Swal.fire({
    title: "คุณแน่ใจไหม?",
    text: "ลบรายการที่เลือก!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ลบเลย!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    console.log("result", result.value);
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: url,
        dataType: "json",
        success: function (response) {
          if (response.status == "success") {
             location.reload();
            // $.pjax.reload({
            //   container: response.container,
            //   history: false,
            //   url: response.url,
            // });

            success("ดำเนินการลบสำเร็จ!.");
            if (response.close) {
              $("#main-modal").modal("hide");
            }
          }
        },
      });
    }
  });
});



JS;
$this->registerJS($js, View::POS_END);
?>