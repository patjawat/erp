<?php
use yii\helpers\Html;
use app\modules\helpdesk2\helpers\HelpdeskSlaHelper;
use app\modules\helpdesk2\models\HelpdeskDetail;

/** @var app\modules\helpdesk2\models\Helpdesk $model */

$ticketId = $model->id ?? '-';
$titleText = 'Ticket #' . $ticketId;

$dataJson = is_array($model->data_json ?? null) ? $model->data_json : [];
$statusCode = (string) ($model->status ?? 'pending');
$urgencyCode = $dataJson['urgency'] ?? null;
$createdAt = $model->created_at ?? null;

$badgeClass = static function (string $color): string {
    return 'badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1';
};

$statusMeta = [
    'pending' => ['label' => 'เปิดงาน', 'color' => 'warning', 'icon' => 'fa-solid fa-circle-info'],
    'receive' => ['label' => 'รับเรื่อง', 'color' => 'info', 'icon' => 'fa-solid fa-inbox'],
    'in_progress' => ['label' => 'กำลังดำเนินการ', 'color' => 'info', 'icon' => 'fa-solid fa-gears'],
    'success' => ['label' => 'เสร็จสิ้น', 'color' => 'success', 'icon' => 'fa-regular fa-circle-check'],
    'cancel' => ['label' => 'ยกเลิก', 'color' => 'danger', 'icon' => 'fa-solid fa-ban'],
];

$statusInfo = $statusMeta[$statusCode] ?? ['label' => 'ไม่ทราบสถานะ', 'color' => 'secondary', 'icon' => 'fa-solid fa-circle'];
$statusBadge = Html::tag('span', '<i class="' . $statusInfo['icon'] . ' me-1"></i>' . Html::encode($statusInfo['label']), [
    'class' => $badgeClass($statusInfo['color']),
]);

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

$priorityInfo = is_scalar($urgencyCode) ? ($priorityMap[(string) $urgencyCode] ?? ['label' => 'ไม่ระบุ', 'color' => 'secondary']) : ['label' => 'ไม่ระบุ', 'color' => 'secondary'];
$priorityBadge = Html::tag('span', Html::encode('ความสำคัญ: ' . $priorityInfo['label']), [
    'class' => $badgeClass($priorityInfo['color']),
]);

$slaBadgeHtml = '';
try {
    $slaBadgeHtml = HelpdeskSlaHelper::renderBadge($model);
} catch (\Throwable $e) {
    $slaBadgeHtml = '';
}
if ($slaBadgeHtml === '') {
    $slaBadgeHtml = Html::tag('span', '<i class="fa-regular fa-clock me-1"></i>ไม่มี SLA', [
        'class' => $badgeClass('secondary'),
    ]);
}

$descriptionSummary = Html::encode($model->title ?: '—');
$descriptionExtra = '';
if (!empty($dataJson['repair_note']) && is_string($dataJson['repair_note'])) {
    $descriptionExtra = Html::encode($dataJson['repair_note']);
} elseif (!empty($dataJson['description']) && is_string($dataJson['description'])) {
    $descriptionExtra = Html::encode($dataJson['description']);
}

$locationLabel = '-';
if (!empty($dataJson['location']) && is_string($dataJson['location'])) {
    $locationLabel = Html::encode($dataJson['location']);
}

$assetCodeLabel = Html::encode($model->asset_number ?: ($dataJson['asset_code'] ?? '-'));
$requestRepairDateLabel = '-';
if (!empty($model->request_repair_date)) {
    try {
        $requestRepairDateLabel = Html::encode((string) \Yii::$app->thaiFormatter->asDate($model->request_repair_date, 'long'));
    } catch (\Throwable $e) {
        $requestRepairDateLabel = Html::encode((string) \Yii::$app->formatter->asDate($model->request_repair_date));
    }
}

// Mock data (used when controller doesn't provide $logs/$comments).
$mockTimeline = [
    (object) ['created_at' => date('Y-m-d H:i:s', strtotime('-22 minutes')), 'message' => 'อัปเดตสถานะงานซ่อม'],
    (object) ['created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'message' => 'รับเรื่องเรียบร้อยแล้ว รอช่างดำเนินการตรวจสอบ'],
    (object) ['created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')), 'message' => 'สร้างงานซ่อมใหม่'],
];

$mockComments = [
    (object) [
        'is_staff' => false,
        'user' => (object) ['name' => 'ผู้แจ้ง (ตัวอย่าง)'],
        'created_at' => date('Y-m-d H:i:s', strtotime('-18 minutes')),
        'message' => 'ช่วยตรวจอาการให้ละเอียดด้วยครับ/ค่ะ',
    ],
    (object) [
        'is_staff' => true,
        'user' => (object) ['name' => 'เจ้าหน้าที่ (ตัวอย่าง)'],
        'created_at' => date('Y-m-d H:i:s', strtotime('-12 minutes')),
        'message' => 'รับเรื่องแล้วครับ/ค่ะ กำลังประสานช่างตรวจสอบ',
    ],
];

$timelineItems = isset($logs) ? $logs : $mockTimeline;
$commentsItems = isset($comments) ? $comments : $mockComments;

$mockAttachments = [
    ['type' => 'pdf', 'label' => 'ไฟล์ใบส่งซ่อม (PDF)', 'url' => '#'],
    ['type' => 'image', 'label' => 'รูปอาการเสีย (JPG)', 'url' => '#'],
    ['type' => 'image', 'label' => 'รูปหลังการซ่อม (JPG)', 'url' => '#'],
];

$detailRows = HelpdeskDetail::find()
    ->where(['helpdesk_id' => $model->id])
    ->orderBy(['id' => SORT_ASC])
    ->all();
$serviceRecordCount = 0;
$partCount = 0;
$expenseCount = 0;
$totalExpense = 0.0;
foreach ($detailRows as $d) {
    $nameCode = strtolower((string) ($d->name ?? ''));
    $titleTextRaw = strtolower((string) ($d->title ?? ''));
    $dj = is_array($d->data_json ?? null) ? $d->data_json : [];
    if ($nameCode === 'service_record') {
        $serviceRecordCount++;
    }
    if (str_contains($nameCode, 'part') || str_contains($titleTextRaw, 'อะไหล่')) {
        $partCount++;
    }
    if (
        str_contains($nameCode, 'expense')
        || str_contains($titleTextRaw, 'ค่าใช้จ่าย')
        || isset($dj['amount'])
        || isset($dj['price'])
        || isset($dj['total'])
    ) {
        $expenseCount++;
    }
    $amount = 0.0;
    foreach (['total', 'amount', 'price', 'cost'] as $k) {
        if (isset($dj[$k]) && is_numeric($dj[$k])) {
            $amount = (float) $dj[$k];
            break;
        }
    }
    if ($amount <= 0 && is_numeric($d->code ?? null)) {
        $amount = (float) $d->code;
    }
    $totalExpense += $amount;
}
$isReceived = in_array($statusCode, ['receive', 'in_progress', 'success', 'cancel'], true);
$isStarted = in_array($statusCode, ['in_progress', 'success'], true);
$isClosed = in_array($statusCode, ['success', 'cancel'], true);
$hasExternal = $model->isExternalRepair();

?>

<div class="container-fluid py-3">
    <div class="row g-3">
        <!-- RIGHT -->
        <div class="col-lg-4">
            <?= $this->render('_sidebar', [
                'model' => $model,
                'statusInfo' => $statusInfo,
                'priorityInfo' => $priorityInfo,
                'statusBadge' => $statusBadge,
                'priorityBadge' => $priorityBadge,
                'slaBadgeHtml' => $slaBadgeHtml,
            ]); ?>
        </div>
        <!-- LEFT -->
        <div class="col-lg-8">
            <?= $this->render('_header', [
                'model' => $model,
                'titleText' => $titleText,
                'statusBadge' => $statusBadge,
                'priorityBadge' => $priorityBadge,
                'slaBadgeHtml' => $slaBadgeHtml,
            ]); ?>

                <div class="card shadow-sm mt-3">
                <div class="card-header fw-bold d-flex flex-wrap align-items-center gap-2">
                    <span class="flex-grow-1 min-w-0"><i class="bi bi-journal-check me-1"></i> มาตรฐานการบันทึกงานซ่อม</span>
                    <div class="d-flex flex-wrap gap-2 ms-auto">
                        <?= Html::a(
                            '<i class="fa-solid fa-pen-to-square me-1"></i> บันทึกวิธีดำเนินการ',
                            ['/helpdesk/service-record/create', 'helpdesk_id' => $model->id, 'title' => 'บันทึกวิธีดำเนินการซ่อม #' . $model->repair_number],
                            ['class' => 'btn btn-sm btn-outline-dark btn-open-repair-method']
                        ) ?>
                        <?php if ((string) $model->status === 'pending'): ?>
                            <?= Html::a('<i class="fa-solid fa-circle-exclamation me-1"></i> รับงาน', ['/helpdesk/service/receive', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary receive-order']) ?>
                        <?php endif; ?>
                        <?= Html::a('<i class="fa-solid fa-truck-fast me-1"></i> ส่งซ่อม/เริ่มงาน', ['/helpdesk/service/send-repair', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-info btn-send-repair']) ?>
                        <?= Html::a('<i class="fa-regular fa-file-lines me-1"></i> เบิกอะไหล่', ['/helpdesk/repair-parts/create', 'helpdesk_id' => $model->id, 'title' => 'เบิกอะไหล่งานซ่อม #' . $model->repair_number], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= Html::a('<i class="fa-solid fa-money-bill-wave me-1"></i> ลงค่าใช้จ่าย', ['/helpdesk/expenses/create', 'helpdesk_id' => $model->id, 'title' => 'ลงค่าใช้จ่ายงานซ่อม #' . $model->repair_number], ['class' => 'btn btn-sm btn-outline-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?= Html::tag('span', ($isReceived ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'รับเรื่อง', ['class' => $badgeClass($isReceived ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($isStarted ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'เริ่มดำเนินการ', ['class' => $badgeClass($isStarted ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($serviceRecordCount > 0 ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'บันทึกงานซ่อม', ['class' => $badgeClass($serviceRecordCount > 0 ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($partCount > 0 ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'บันทึกอะไหล่', ['class' => $badgeClass($partCount > 0 ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($expenseCount > 0 ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'บันทึกค่าใช้จ่าย', ['class' => $badgeClass($expenseCount > 0 ? 'success' : 'secondary')]) ?>
                        <?= Html::tag('span', ($isClosed ? '<i class="bi bi-check-circle-fill me-1"></i>' : '<i class="bi bi-hourglass-split me-1"></i>') . 'ปิดงาน', ['class' => $badgeClass($isClosed ? 'success' : 'secondary')]) ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                                <div class="small text-muted mb-2">วิธีดำเนินการซ่อม</div>
                                <div class="fw-medium mb-2">ช่องทางซ่อม: <?= Html::encode($model->viewRepairChannelLabel()) ?></div>
                                <?php if ($hasExternal): ?>
                                    <div class="small text-muted mb-1">รายละเอียดส่งซ่อมภายนอก</div>
                                    <?= $model->getExternalRepairDetailHtml() ?>
                                <?php else: ?>
                                    <div class="small text-muted">งานนี้เป็นซ่อมภายใน ให้บันทึกขั้นตอนใน Timeline และแนบรูปงานซ่อม</div>
                                    <div class="mt-2"><?= $model->getRepairWorkPhotosHtml() ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border border-secondary border-opacity-25 rounded-3 p-3 h-100">
                                <div class="small text-muted mb-2">สรุปอะไหล่และค่าใช้จ่าย</div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">รายการบันทึกซ่อม</span>
                                    <span class="fw-medium"><?= number_format($serviceRecordCount) ?> รายการ</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">รายการอะไหล่</span>
                                    <span class="fw-medium"><?= number_format($partCount) ?> รายการ</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">รายการค่าใช้จ่าย</span>
                                    <span class="fw-medium"><?= number_format($expenseCount) ?> รายการ</span>
                                </div>
                                <div class="d-flex justify-content-between py-1">
                                    <span class="text-muted">ค่าใช้จ่ายรวม</span>
                                    <span class="fw-bold text-danger"><?= number_format($totalExpense, 2) ?> บาท</span>
                                </div>
                                <?php if ($hasExternal): ?>
                                    <div class="mt-2 pt-2 border-top border-secondary border-opacity-25">
                                        <div class="small text-muted mb-1">บิล/หลักฐานส่งซ่อมภายนอก</div>
                                        <?= $model->getExternalRepairBillsHtml() ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card shadow-sm mt-3">
                <div class="card-header fw-bold">รายละเอียดงานซ่อม</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="d-flex align-items-start gap-2">
                                <div class="erp-icon-box bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-3 p-2">
                                    <i class="bi bi-clipboard2-check"></i>
                                </div>
                                <div>
                                    <div class="fw-bold mb-1"><?= $descriptionSummary ?></div>
                                    <?php if ($descriptionExtra !== ''): ?>
                                        <div class="text-muted"><?= nl2br($descriptionExtra) ?></div>
                                    <?php else: ?>
                                        <div class="text-muted">ไม่มีรายละเอียดเพิ่มเติม</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">รหัสครุภัณฑ์</span>
                                <span class="fw-medium"><?= $assetCodeLabel ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">สถานที่</span>
                                <span class="fw-medium"><?= $locationLabel ?></span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">วันที่ต้องการให้ซ่อม</span>
                                <span class="fw-medium"><?= $requestRepairDateLabel ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">อัปเดต</span>
                                <span class="fw-medium"><?= Html::encode($createdAt ? \Yii::$app->formatter->asDatetime($createdAt) : '-') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <?= $this->render('_timeline', ['items' => $timelineItems]); ?>

            <!-- Comments -->
            <?= $this->render('_comments', ['comments' => $commentsItems]); ?>

            <!-- Attachments -->
            <div class="card shadow-sm mt-3">
                <div class="card-header fw-bold d-flex align-items-center justify-content-between gap-2">
                    <span>ไฟล์แนบ</span>
                    <span class="text-muted small">ตัวอย่าง UI พร้อมแนบไฟล์</span>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($mockAttachments as $a): ?>
                            <?php
                            $iconClass = 'fa-regular fa-file';
                            if (($a['type'] ?? '') === 'pdf') {
                                $iconClass = 'fa-solid fa-file-pdf';
                            } elseif (($a['type'] ?? '') === 'image') {
                                $iconClass = 'fa-regular fa-file-image';
                            }
                            ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 border border-secondary-subtle bg-secondary bg-opacity-10 p-2 text-secondary">
                                            <i class="<?= $iconClass ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium"><?= Html::encode($a['label']) ?></div>
                                            <div class="text-muted small">ขนาดไฟล์: —</div>
                                        </div>
                                    </div>
                                    <div>
                                        <?= Html::a(
                                            'เปิดดู',
                                            $a['url'],
                                            ['class' => 'btn btn-outline-primary']
                                        ) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>


    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="repair-method-offcanvas" aria-labelledby="repair-method-offcanvas-label">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="repair-method-offcanvas-label">บันทึกวิธีดำเนินการซ่อม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="repair-method-offcanvas-content" class="text-muted small">กำลังโหลดฟอร์ม...</div>
    </div>
</div>

<?php
$js = <<<JS
// Support legacy callbacks from team form (view.php)
window.loadFormTeam = function () { window.location.reload(); };
window.loadListTeam = function () { window.location.reload(); };
window.loadFormServiceRecord = function () { window.location.reload(); };
window.loadTimeline = function () { window.location.reload(); };

$('body').off('click.repairMethodClose').on('click.repairMethodClose', '#repair-method-offcanvas [data-bs-dismiss="offcanvas"]', function () {
  // รีเซ็ตข้อความโหลด และเรียก hide ซ้ำเพื่อกันกรณี bootstrap ไม่ซ่อนจริง
  try {
    var offcanvasEl = document.getElementById('repair-method-offcanvas');
    if (offcanvasEl && offcanvasEl.__repairOffcanvasInstance) {
      offcanvasEl.__repairOffcanvasInstance.hide();
    }
  } catch (e) {}

  $('#repair-method-offcanvas-content').html('<div class="text-muted small">กำลังโหลดฟอร์ม...</div>');
});

$('body').on('click', 'a.btn-open-repair-method', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var offcanvasEl = document.getElementById('repair-method-offcanvas');
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
  offcanvasEl.__repairOffcanvasInstance = offcanvas;
  $('#repair-method-offcanvas-content').html('<div class="text-muted small">กำลังโหลดฟอร์ม...</div>');
  offcanvas.show();

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      $('#repair-method-offcanvas-label').html(response.title || 'บันทึกวิธีดำเนินการซ่อม');
      $('#repair-method-offcanvas-content').html(response.content || '<div class="text-danger small">ไม่พบฟอร์ม</div>');
    },
    error: function () {
      $('#repair-method-offcanvas-content').html('<div class="text-danger small">ไม่สามารถโหลดฟอร์มได้</div>');
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดฟอร์มบันทึกวิธีดำเนินการซ่อมได้', icon: 'error' });
    }
  });
});

// Open "assign team" form in main modal
$('body').on('click', 'a.btn-assign-team', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');
  var title = $(this).data('title') || 'มอบหมายช่าง';
  var size = $(this).data('size') || 'modal-md';

  if (typeof beforLoadModal === 'function') { beforLoadModal(); }

  $.ajax({
    type: 'get',
    url: url,
    dataType: 'json',
    success: function (response) {
      var modal = $('#main-modal');
      modal.find('#main-modal-label').html(response.title || title);
      modal.find('.modal-body').html(response.content || '');
      modal.find('.modal-footer').html(response.footer || '');
      modal.find('.modal-dialog')
        .removeClass('modal-sm modal-md modal-lg modal-xl modal-xxl')
        .addClass(size);
      modal.modal('show');
    },
    error: function () {
      Swal.fire({ title: 'ไม่สำเร็จ', text: 'ไม่สามารถเปิดฟอร์มมอบหมายช่างได้', icon: 'error' });
    }
  });
});

$('body').on('click', 'a.receive-order', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');

  Swal.fire({
    title: 'ยืนยันการรับงาน',
    text: 'ต้องการรับงานซ่อมรายการนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยันรับงาน',
    cancelButtonText: 'ยกเลิก',
    reverseButtons: false
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }
    $.ajax({
      type: 'get',
      url: url,
      dataType: 'json',
      success: function () {
        Swal.fire({
          title: 'รับงานแล้ว',
          icon: 'success',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          window.location.reload();
        });
      },
      error: function () {
        Swal.fire({
          title: 'ไม่สำเร็จ',
          text: 'ไม่สามารถรับงานได้ กรุณาลองใหม่อีกครั้ง',
          icon: 'error'
        });
      }
    });
  });
});

$('body').on('click', 'a.btn-send-repair', function (e) {
  e.preventDefault();
  var url = $(this).attr('href');

  Swal.fire({
    title: 'ยืนยันการส่งซ่อม',
    text: 'ต้องการส่งซ่อม/เริ่มดำเนินการรายการนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยันส่งซ่อม',
    cancelButtonText: 'ยกเลิก',
    reverseButtons: false
  }).then(function (result) {
    if (!result.isConfirmed) {
      return;
    }
    $.ajax({
      type: 'get',
      url: url,
      dataType: 'json',
      success: function () {
        Swal.fire({
          title: 'ส่งซ่อมแล้ว',
          icon: 'success',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          window.location.reload();
        });
      },
      error: function () {
        Swal.fire({
          title: 'ไม่สำเร็จ',
          text: 'ไม่สามารถส่งซ่อมได้ กรุณาลองใหม่อีกครั้ง',
          icon: 'error'
        });
      }
    });
  });
});
JS;
$this->registerJs($js);
?>

