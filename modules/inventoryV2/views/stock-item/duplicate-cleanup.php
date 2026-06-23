<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'จัดการรายการวัสดุชื่อซ้ำ';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2']];
$this->params['breadcrumbs'][] = ['label' => 'รายการวัสดุ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="duplicate-cleanup">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
        <div>
            <h4 class="mb-1 fw-medium text-body d-flex align-items-center gap-2">
                <i class="bi bi-shield-check"></i> <?= Html::encode($this->title) ?>
            </h4>
            <p class="text-muted small mb-0">
                ปิดใช้งานรายการวัสดุที่ชื่อซ้ำกัน <strong>เฉพาะตัวที่ไม่มีการเคลื่อนไหวเลย</strong>
                — เปิดกลับได้ภายหลัง (เก็บ audit ใน data_json)
            </p>
        </div>
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับรายการวัสดุ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">กลุ่มชื่อซ้ำที่จะจัดการ</div>
                    <div class="h2 mb-0 fw-bold text-primary"><?= number_format($totalGroups) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">รายการที่จะถูกปิด</div>
                    <div class="h2 mb-0 fw-bold text-warning"><?= number_format($totalCandidates) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">กลุ่มข้าม (ไม่มีตัวที่ใช้งาน)</div>
                    <div class="h2 mb-0 fw-bold text-secondary"><?= number_format($skippedGroups) ?></div>
                    <div class="text-muted" style="font-size:11px">กันการ wipe ทั้งกลุ่ม</div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($totalCandidates === 0): ?>
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            ไม่มีรายการที่ปลอดภัยจะปิดในตอนนี้ — ทุกกลุ่มซ้ำมีกิจกรรมหรือ balance อยู่ ต้อง merge ใน Stage 2
        </div>
    <?php else: ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning-subtle py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 small fw-semibold">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    รายการที่จะถูกปิดใช้งาน (active = 0)
                </h6>
                <button type="button" class="btn btn-warning btn-sm fw-semibold" id="btn-confirm-deactivate">
                    <i class="bi bi-shield-fill-check me-1"></i> ยืนยันปิด <?= number_format($totalCandidates) ?> รายการ
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">ชื่อรายการ (ที่ซ้ำ)</th>
                                <th class="text-center" style="width:13%">เก็บไว้ (KEEP)</th>
                                <th class="text-center" style="width:13%">ปิด (DEACTIVATE)</th>
                                <th class="text-start" style="width:48%">รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($groups as $group): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= Html::encode($group['title']) ?></div>
                                    <div class="text-muted small">รวม <?= $group['total_codes'] ?> รหัส</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success"><?= count($group['keepers']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning-emphasis"><?= count($group['candidates']) ?></span>
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="text-success mb-1">
                                            <i class="bi bi-check-circle me-1"></i><strong>เก็บ:</strong>
                                            <?php foreach ($group['keepers'] as $k): ?>
                                                <span class="badge bg-light text-dark border me-1 font-monospace"
                                                      title="balance: <?= $k['total_balance'] ?> / IN: <?= $k['has_in'] ? 'มี' : 'ไม่มี' ?> / OUT: <?= $k['has_out'] ? 'มี' : 'ไม่มี' ?>">
                                                    <?= Html::encode($k['code']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="text-muted">
                                            <i class="bi bi-x-circle me-1"></i><strong>ปิด:</strong>
                                            <?php foreach ($group['candidates'] as $c): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border me-1 font-monospace">
                                                    <?= Html::encode($c['code']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php
$executeUrl = Url::to(['bulk-deactivate-duplicates']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$total = (int) $totalCandidates;
$script = <<<JS
$('#btn-confirm-deactivate').on('click', function() {
    Swal.fire({
        title: 'ยืนยันปิดใช้งาน?',
        html: '<div class="text-start small">'
            + '<p>จะปิด <strong>$total รายการ</strong> ที่ชื่อซ้ำกันและไม่มีการเคลื่อนไหว</p>'
            + '<ul class="ps-3 mb-2">'
            + '<li><strong>ปลอดภัย</strong> — รายการที่มี balance หรือกิจกรรมจะ<strong>ไม่ถูกแตะ</strong></li>'
            + '<li><strong>เปิดกลับได้</strong> — บันทึก audit ใน data_json (active=0 → กลับมา 1 ได้)</li>'
            + '<li>dropdown ค้นวัสดุจะสะอาดขึ้นทันที</li>'
            + '</ul></div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ปิดทั้งหมด',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
        customClass: { confirmButton: 'btn btn-warning', cancelButton: 'btn btn-light' },
        showLoaderOnConfirm: true,
        preConfirm: function() {
            return \$.post('$executeUrl', { '$csrfParam': '$csrfToken' })
                .then(function(res) { return res; })
                .catch(function() { return { success: false, message: 'เชื่อมต่อระบบไม่ได้' }; });
        }
    }).then(function(result) {
        if (!result.isConfirmed) return;
        var res = result.value || {};
        if (res.success) {
            Swal.fire({
                title: 'สำเร็จ',
                text: 'ปิดใช้งาน ' + (res.deactivated || 0) + ' รายการเรียบร้อย' + (res.failed ? ' (พลาด ' + res.failed + ' รายการ)' : ''),
                icon: 'success'
            }).then(function() { window.location.reload(); });
        } else {
            Swal.fire('ผิดพลาด', res.message || 'ไม่สามารถดำเนินการได้', 'error');
        }
    });
});
JS;
$this->registerJs($script);
?>
