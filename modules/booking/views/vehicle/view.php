<?php

use yii\helpers\Html;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehicles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$viewCreated = $model->viewCreated();
$daysPassed = 0;
try {
    $createdAt = new \DateTime((string) $model->created_at);
    $now = new \DateTime();
    $daysPassed = (int) $createdAt->diff($now)->days;
} catch (\Throwable $th) {
    $daysPassed = 0;
}

$phoneRaw = $model->data_json['phone'] ?? '';
$phoneDigits = preg_replace('/[^0-9+]/', '', (string) $phoneRaw);
$remarksRaw = $model->data_json['remarks'] ?? '';
$comment = $model->data_json['coment'] ?? '';

// ผู้ร่วมเดินทาง: resolve จาก single source บน model (Vehicle::companions())
$companions = $model->companions();
$companionCount = $companions['count'];
?>

<div class="vehicle-view">

    <section class="vehicle-view-section px-3 pt-3 pb-3">
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="vv-label">สถานะคำขอ</div>
                <div class="mt-1"><?= $model->viewStatus()['view'] ?></div>
                <div class="mt-2">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">
                        <i class="fa-regular fa-clock me-1"></i>ผ่านมาแล้ว <?= (int) $daysPassed ?> วัน
                    </span>
                </div>
                <div class="vv-label mt-3">วันเวลาที่จอง</div>
                <div class="fw-semibold text-body-emphasis"><?= Html::encode($viewCreated['full'] ?? '-') ?></div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="vv-label">ผู้ขอใช้บริการ</div>
                <div class="mt-1 mb-3"><?= $model->userRequest()['avatar']; ?></div>
                <?php if ($phoneDigits !== ''): ?>
                    <a href="tel:<?= Html::encode($phoneDigits) ?>"
                       class="btn btn-outline-primary w-100 rounded-3 py-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-phone me-2"></i>โทรติดต่อ: <?= Html::encode($phoneRaw) ?>
                    </a>
                <?php else: ?>
                    <div class="small text-muted">ไม่มีหมายเลขโทรศัพท์</div>
                <?php endif; ?>
            </div>

            <div class="col-12 col-lg-4">
                <div class="vv-label">สถานที่ปลายทาง</div>
                <h5 class="fw-semibold text-body-emphasis mb-1"><?= Html::encode($model->locationOrg?->title ?? '-') ?></h5>
                <div class="small text-muted">Ref: <?= Html::encode($model->code) ?></div>

                <div class="vv-label mt-3">วัตถุประสงค์การใช้รถ</div>
                <p class="text-body-emphasis mb-0 small"><?= Html::encode($model->reason) ?></p>
            </div>
        </div>
    </section>

    <hr class="vv-divider">

    <section class="vehicle-view-section px-3 py-3">
        <h6 class="mb-3 fw-semibold text-body-emphasis">
            <i class="fa-regular fa-calendar me-2 text-primary"></i>กำหนดการเดินทาง
        </h6>
        <?= $model->viewGoType() ?>
        <div class="row g-3 mt-1">
            <div class="col-12 col-sm-6">
                <div class="vv-label">วัน/เวลาไป</div>
                <div class="fw-semibold text-body-emphasis mt-1"><?= AppHelper::convertToThai($model->date_start) ?></div>
                <div class="text-primary fw-semibold"><?= Html::encode($model->time_start) ?> น.</div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="vv-label">วัน/เวลาคืน</div>
                <div class="fw-semibold text-body-emphasis mt-1"><?= AppHelper::convertToThai($model->date_end) ?></div>
                <div class="text-primary fw-semibold"><?= Html::encode($model->time_end) ?> น.</div>
            </div>
        </div>
    </section>

    <hr class="vv-divider">

    <section class="vehicle-view-section px-3 py-3">
        <h6 class="mb-3 fw-semibold text-body-emphasis">
            <i class="fa-solid fa-car me-2 text-primary"></i>การจัดสรรรถและพนักงานขับ
        </h6>

        <?php if (empty($model->vehicleDetails)): ?>
            <div class="small text-muted fst-italic">ยังไม่ได้จัดสรรรถและพนักงานขับ</div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($model->vehicleDetails ?? [] as $index => $detail): ?>
                    <div class="row g-3 align-items-center vv-allocation-row">
                        <div class="col-12 col-lg-6">
                            <div class="d-flex align-items-center gap-3">
                                <?= Html::img($detail->driver?->showAvatar(), [
                                    'class' => 'rounded-3 vv-driver-avatar',
                                    'style' => 'max-width:50px',
                                    'alt' => 'พนักงานขับรถ',
                                ]) ?>
                                <div class="min-w-0">
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <span class="small text-muted">พนักงานขับรถ</span>
                                        <?= $detail->viewStatus()['view'] ?>
                                    </div>
                                    <div class="fw-semibold text-body-emphasis"><?= Html::encode($detail->driver?->fullname ?? 'ยังไม่ได้จัดสรร พขร.') ?></div>
                                    <?php if (!empty($detail->driver?->phone)): ?>
                                        <a href="tel:<?= Html::encode(preg_replace('/[^0-9+]/', '', (string) $detail->driver->phone)) ?>"
                                           class="small text-muted text-decoration-none">
                                            <i class="fa-solid fa-phone me-1"></i><?= Html::encode($detail->driver->phone) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-3">
                            <div class="d-flex align-items-center gap-2">
                                <?php
                                try {
                                    if ($detail->car) {
                                        echo Html::img($detail->car?->ShowImg()['image'], ['class' => 'avatar rounded border', 'alt' => 'รถ']);
                                    }
                                } catch (\Throwable $th) {
                                    // ignore
                                }
                                ?>
                                <div class="d-flex flex-column min-w-0">
                                    <span class="small text-body-emphasis text-truncate"><?= Html::encode($detail->car?->data_json['brand'] ?? '-') ?></span>
                                    <span class="small fw-semibold text-primary"><?= Html::encode($detail->license_plate ?? '-') ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-3 text-lg-end">
                            <?php if (Yii::$app->user->can('driver')): ?>
                                <?= Html::a('<i class="fa-solid fa-key me-1"></i>บันทึกภารกิจ',
                                    ['/booking/vehicle/work-update', 'id' => $detail->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์'],
                                    ['class' => 'btn btn-outline-warning btn-sm rounded-pill px-3 fw-semibold open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled>
                                    <i class="fa-solid fa-key me-1"></i>บันทึกภารกิจ
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($companionCount > 0): ?>
        <hr class="vv-divider">
        <section class="vehicle-view-section px-3 py-3">
            <h6 class="mb-3 fw-semibold text-body-emphasis d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-group text-primary"></i>ผู้ร่วมเดินทาง
                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-medium"><?= (int) $companionCount ?> คน</span>
            </h6>
            <div class="vv-companions">
                <?php foreach ($companions['items'] as $companion): ?>
                    <?php $emp = $companion['employee']; ?>
                    <div class="vv-companion">
                        <?php if ($emp): ?>
                            <img src="<?= Html::encode($emp->showAvatar()) ?>" class="vv-companion__avatar" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="vv-companion__avatar vv-companion__avatar--guest"><i class="fa-solid fa-user"></i></span>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <div class="fw-semibold text-body-emphasis text-truncate vv-companion__name"><?= Html::encode($companion['name']) ?></div>
                            <div class="text-muted text-truncate vv-companion__meta"><?= Html::encode($emp ? $emp->positionName() : 'บุคคลภายนอก') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (trim((string) $comment) !== '' || trim((string) $remarksRaw) !== ''): ?>
        <hr class="vv-divider">
        <section class="vehicle-view-section px-3 py-3">
            <h6 class="mb-2 fw-semibold text-body-emphasis">
                <i class="fa-regular fa-note-sticky me-2 text-secondary"></i>หมายเหตุ
            </h6>
            <?php if (trim((string) $comment) !== ''): ?>
                <p class="text-body-emphasis mb-2 small"><?= Html::encode($comment) ?></p>
            <?php endif; ?>
            <?php if (trim((string) $remarksRaw) !== ''): ?>
                <div class="small text-muted"><?= $remarksRaw ?></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <div class="vehicle-view-actions border-top px-3 py-3">
        <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-2">
            <?= Html::button('<i class="fa-regular fa-circle-xmark me-1"></i>ปิด', [
                'class' => 'btn btn-light',
                'data-bs-dismiss' => 'modal',
            ]) ?>
            <?= Html::a('<i class="fa-solid fa-print me-1"></i>พิมพ์ใบขอใช้รถ',
                ['/booking/vehicle/print', 'id' => $model->id, 'title' => 'ใบขอใช้รถยนต์'],
                [
                    'class' => 'btn btn-secondary',
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                    'data-pjax' => '0',
                    'title' => 'พิมพ์ใบขอใช้รถยนต์',
                ]) ?>
            <?php if (($model->created_by == Yii::$app->user->id) || Yii::$app->user->can('driver')): ?>
                <?= Html::a('<i class="fa-regular fa-circle-xmark me-1"></i>ยกเลิกการจอง',
                    ['/booking/vehicle/cancel', 'id' => $model->id],
                    ['class' => 'btn btn-danger btn-cancel-booking']) ?>
                <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข',
                    ['/booking/vehicle/update', 'id' => $model->id, 'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'],
                    ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.vehicle-view { background: var(--bs-body-bg); color: var(--bs-body-color); }
.vehicle-view .vv-label {
    font-size: .8125rem;
    font-weight: 500;
    color: var(--bs-secondary-color, #6c757d);
    margin-bottom: .15rem;
}
.vehicle-view .vv-divider {
    margin: 0;
    border: 0;
    border-top: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.08));
}
.vehicle-view .vv-driver-avatar { object-fit: cover; aspect-ratio: 1 / 1; }
.vehicle-view .vv-allocation-row {
    padding: .65rem 0;
    border-radius: .5rem;
    transition: background-color 150ms cubic-bezier(.16,1,.3,1);
}
.vehicle-view .vv-allocation-row:hover {
    background-color: var(--bs-tertiary-bg, #f8f9fa);
}
.vehicle-view .min-w-0 { min-width: 0; }
.vehicle-view .vv-companions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: .625rem;
}
.vehicle-view .vv-companion {
    display: flex;
    align-items: center;
    gap: .625rem;
    padding: .5rem .625rem;
    border: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.08));
    border-radius: .625rem;
    background: var(--bs-body-bg, #fff);
}
.vehicle-view .vv-companion__avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.08));
}
.vehicle-view .vv-companion__avatar--guest {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--bs-tertiary-bg, #f1f3f5);
    color: var(--bs-secondary-color, #6c757d);
}
.vehicle-view .vv-companion__name { font-size: .875rem; line-height: 1.25; }
.vehicle-view .vv-companion__meta { font-size: .75rem; line-height: 1.2; }
@media (prefers-reduced-motion: reduce) {
    .vehicle-view .vv-allocation-row { transition: none; }
}
</style>

<?php
$js = <<<JS
$('.btn-cancel-booking').click(function (e) {
    e.preventDefault();
    var url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการยกเลิกการจอง?',
        text: "คุณต้องการยกเลิกการจองหรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof closeModal === 'function') { closeModal(); }
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: response.message,
                            icon: 'success',
                            timer: 1000
                        }).then(() => { window.location.reload(); });
                    } else {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: response.message,
                            icon: 'error'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถยกเลิกการจองได้',
                        icon: 'error'
                    });
                }
            });
        }
    });
});
JS;
$this->registerJs($js);
?>
