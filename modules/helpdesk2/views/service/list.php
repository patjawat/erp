<?php

use app\modules\helpdesk2\models\Helpdesk;
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

/** ข้อความความเร่งด่วนเดียวกับ Select2 (listUrgency) */
$urgencyList = Helpdesk::listUrgency();

$statusMeta = [
    'pending' => ['label' => 'เปิดงาน', 'color' => 'warning'],
    'receive' => ['label' => 'รับเรื่อง', 'color' => 'info'],
    'in_progress' => ['label' => 'กำลังดำเนินการ', 'color' => 'info'],
    'success' => ['label' => 'เสร็จสิ้น', 'color' => 'success'],
    'cancel' => ['label' => 'ยกเลิก', 'color' => 'danger'],
];

$statusCounts = [];
$currentStatusFilter = trim((string) ($searchModel->status ?? ''));
if (isset($dataProvider->query)) {
    try {
        foreach ($statusMeta as $statusCode => $meta) {
            $q = clone $dataProvider->query;
            $statusCounts[$statusCode] = (int) $q
                ->limit(-1)
                ->offset(-1)
                ->orderBy([])
                ->andWhere(['helpdesk.status' => $statusCode])
                ->count();
        }
    } catch (\Throwable $e) {
        foreach (array_keys($statusMeta) as $statusCode) {
            $statusCounts[$statusCode] = 0;
        }
    }
}

$buildStatusUrl = static function (?string $statusCode) use ($searchModel): array {
    $params = Yii::$app->request->queryParams;
    $formName = $searchModel->formName();
    $params[$formName] = is_array($params[$formName] ?? null) ? $params[$formName] : [];
    if ($statusCode === null || $statusCode === '') {
        unset($params[$formName]['status']);
    } else {
        $params[$formName]['status'] = $statusCode;
    }
    return array_merge(['/' . Yii::$app->controller->route], $params);
};

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

<div class="helpdesk2-service-list">
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header">
        <h6 class="mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/helpdesk2/views/service/_search', ['model' => $searchModel]) ?>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0">
                <i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between gap-2 flex-wrap align-items-center">
                <?php foreach ($statusMeta as $statusCode => $meta): ?>
                    <?php
                    $count = (int) ($statusCounts[$statusCode] ?? 0);
                    $isActiveStatus = $currentStatusFilter === $statusCode;
                    $badgeClasses = $isActiveStatus
                        ? $badgeClass($meta['color'])
                        : $badgeClass('secondary') . ' opacity-75';
                    ?>
                    <?= Html::a(
                        Html::encode($meta['label'] . ' ' . number_format($count)),
                        $buildStatusUrl($statusCode),
                        [
                            'class' => $badgeClasses . ' text-decoration-none',
                            'title' => 'คลิกเพื่อกรองสถานะ ' . $meta['label'],
                        ]
                    ) ?>
                <?php endforeach; ?>
                <?php if ($currentStatusFilter !== ''): ?>
                    <?= Html::a(
                        'ล้างตัวกรอง',
                        $buildStatusUrl(null),
                        ['class' => 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1 text-decoration-none']
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $models = $dataProvider->getModels(); ?>
<div class="overflow-auto" style="max-height: 68vh;">
        <div class="row g-2" role="list">
            <?php foreach ($models as $key => $item): ?>
                <?php
                $req = $item->getUserReq();
                $urgencyCode = is_array($item->data_json ?? null) ? ($item->data_json['urgency'] ?? null) : null;
                $urgencyLabel = 'ไม่ระบุ';
                if ($urgencyCode !== null && $urgencyCode !== '') {
                    $urgencyLabel = $urgencyList[(string) $urgencyCode] ?? $urgencyList[$urgencyCode] ?? 'ไม่ระบุ';
                }
                $vu = $item->viewUrgent();
                $urgencyColor = 'secondary';
                if (!empty($vu['color']) && is_string($vu['color'])) {
                    $c = preg_replace('/[^a-z]/', '', strtolower($vu['color']));
                    $allowed = ['primary', 'secondary', 'success', 'danger', 'warning', 'info'];
                    $urgencyColor = in_array($c, $allowed, true) ? $c : 'secondary';
                }
                $priorityBadge = Html::tag('span', Html::encode($urgencyLabel), [
                    'class' => $badgeClass($urgencyColor) . ' text-wrap text-start',
                    'title' => 'ความเร่งด่วน',
                ]);

                $statusCode = (string) ($item->status ?? 'pending');
                $sInfo = $statusMeta[$statusCode] ?? ['label' => ($item->repairStatus?->title ?? 'ไม่ทราบสถานะ'), 'color' => 'secondary'];
                $statusBadge = Html::tag('span', Html::encode($sInfo['label']), ['class' => $badgeClass($sInfo['color'])]);

                $location = '-';
                if (is_array($item->data_json ?? null)) {
                    $loc = $item->data_json['location'] ?? null;
                    $location = $loc !== null && $loc !== '' ? (string) $loc : '-';
                }
                $problemDetail = '-';
                if (is_array($item->data_json ?? null)) {
                    $pd = $item->data_json['problem_detail'] ?? null;
                    $problemDetail = $pd !== null && $pd !== '' ? (string) $pd : ((string) ($item->title ?? '-'));
                } else {
                    $problemDetail = (string) ($item->title ?? '-');
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
                <div class="col-12" role="listitem">
                    <div class="min-w-0">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                            <?php /* —— Summary: อ่านเร็วในไม่กี่วินาที —— */ ?>
                            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2 gap-lg-3 pb-2">
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
                                    <?php /* ยกเลิกข้อความรายละเอียดปัญหาในส่วน Summary เพื่อไม่ให้ซ้ำกับคอลัมน์ "รายละเอียดของปัญหา" */ ?>
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
                            <div class="row g-2 pt-2">
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
                                    <div class="small text-muted mb-1">รายละเอียดของปัญหา</div>
                                    <div class="text-break"><?= Html::encode($problemDetail) ?></div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3">
                                    <div class="small text-muted mb-1">ช่องทางแจ้งซ่อม</div>
                                    <div class="mb-1"><?= Html::encode($channelLabel) ?></div>
                                </div>
                            </div>

                            <?php /* —— Workflow (จาก status เดิม) —— */ ?>
                            <div class="pt-2 mt-2 border-top">
                                <div class="small text-muted mb-1">ขั้นตอนงาน</div>
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
                                <div class="mb-1">
                                    <div class="progress" role="progressbar" aria-label="ความคืบหน้างานซ่อม" aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                                        <div class="progress-bar bg-<?= Html::encode($progressColor) ?>" style="width: <?= $progressPercent ?>%"></div>
                                    </div>
                                    <div class="small mt-1 mb-0 <?= $statusCode === 'success' ? 'text-success fw-medium' : 'text-muted' ?>">ความคืบหน้า <?= $progressPercent ?>%</div>
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
                                                $stepClass = ($statusCode === 'success')
                                                    ? $badgeClass('success')
                                                    : 'badge bg-primary text-primary text-white border border-primary-subtle rounded-pill fw-medium px-2 py-1';
                                            } else {
                                                $stepClass = $badgeClass('secondary') . ' opacity-75';
                                            }
                                            ?>
                                            <?php if ($si > 0): ?>
                                                <span class="text-muted small px-0"><i class="bi bi-chevron-right"></i></span>
                                            <?php endif; ?>
                                            <?php
                                            $stepIcon = ($isDone || ($isCurrent && $statusCode === 'success'))
                                                ? 'bi-check-circle-fill'
                                                : 'bi-hourglass-split';
                                            $stepLabelHtml = Html::tag('i', '', ['class' => 'bi ' . $stepIcon . ' me-1', 'aria-hidden' => 'true']) . Html::encode($step['label']);
                                            ?>
                                            <?= Html::tag('span', $stepLabelHtml, ['class' => $stepClass]) ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="small text-muted mb-0 mt-1">
                                        <i class="bi bi-info-circle me-1"></i>
                                        สถานะปัจจุบัน: <span class="fw-medium text-body"><?= Html::encode($sInfo['label']) ?></span>
                                        <?php if (!$knownInFlow): ?>
                                            <span class="text-muted">(สถานะนี้ไม่อยู่ในลำดับมาตรฐาน — แสดงตามข้อมูลจริง)</span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-wrap justify-content-end gap-1 pt-2 mt-2">
                                <?php if ($item->status == 'pending'): ?>
                                    <?= Html::a('<i class="fa-solid fa-circle-exclamation"></i> รับเรื่อง', ['/helpdesk/service/receive', 'id' => $item->id], ['class' => 'receive-order btn btn-sm btn-outline-primary']); ?>
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
</div>

        <div class="body-footer mt-2">
            <div class="d-flex justify-content-center">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'class' => 'pagination pagination-sm',
                    ],
                ]); ?>
            </div>
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
                            window.location.href = response.url || window.location.href;
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