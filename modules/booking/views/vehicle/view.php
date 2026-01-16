<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehicles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<!-- 
 <h4 class="h3 fw-bold text-dark mb-1"> <?php echo $model->locationOrg?->title ?? '-' ?> </h4>
        <h6 class="text-muted mb-0"><?= $model->reason ?></h6> -->

<div class="p-4 bg-light bg-opacity-25 d-flex justify-content-between align-items-center">
    <div>
        <label class="small text-muted text-uppercase fw-bold mb-1 d-block" style="letter-spacing: 1px;">สถานที่ปลายทาง</label>
        <h4 class="fw-bold text-dark mb-1"> <?php echo $model->locationOrg?->title ?? '-' ?></h4>
        <div class="text-muted small mt-2">Ref: <?= $model->code ?></div>
    </div>
    <div class="d-flex flex-column justify-content-center">
        <label class="small text-muted text-uppercase fw-bold mb-1 d-block" style="letter-spacing: 1px;">สถานะคำขอ</label>
        <?= $model->viewStatus()['view'] ?>
    </div>
</div>

<div class="p-4">
    <h6 class="fw-bold text-dark mb-3"><i data-lucide="calendar-days" class="me-2 text-primary size-18"></i>กำหนดการเดินทาง</h6>
    <?= $model->viewGoType() ?>
    <div class="row g-3">
        <div class="col-6">
            <div class="p-3 rounded-3 bg-white border border-light shadow-sm">
                <small class="text-muted d-block mb-1">วัน/เวลาไป</small>
                <div class="fw-bold text-dark"><?= AppHelper::convertToThai($model->date_start) ?></div>
                <div class="text-primary fw-bold"><?= $model->time_start ?> น.</div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 rounded-3 bg-white border border-light shadow-sm">
                <small class="text-muted d-block mb-1">วัน/เวลาคืน</small>
                <div class="fw-bold text-dark"><?= AppHelper::convertToThai($model->date_end) ?></div>
                <div class="text-primary fw-bold"><?= $model->time_end ?> น.</div>
            </div>
        </div>
    </div>
</div>

<div class="p-4">
    <h6 class="fw-bold text-dark mb-3"><i data-lucide="user" class="me-2 text-primary size-18"></i>ข้อมูลผู้ขอใช้บริการ</h6>
    <div class="d-flex align-items-center mb-3">
        <?php echo $model->userRequest()['avatar']; ?>
    </div>
    <a href="<?php echo $model->data_json['phone'] ?? '-'; ?>" class="btn btn-outline-primary w-100 rounded-3 py-2 shadow-sm d-flex align-items-center justify-content-center">
        <i data-lucide="phone" class="size-18 me-2"></i> โทรติดต่อ: <?php echo $model->data_json['phone'] ?? '-'; ?>
    </a>
</div>



<div class="row g-4">
    <div class="col-12 mt-4">
        <div class="p-3 bg-light rounded-4 border-0">
            <label class="small fw-bold text-muted text-uppercase mb-1 d-block">วัตถุประสงค์การใช้รถ</label>
            <p class="text-dark mb-0 small leading-relaxed">
                <?= $model->reason ?>
            </p>
        </div>
    </div>
</div>

<?php foreach ($model->vehicleDetails ?? [] as $index => $detail): ?>
    <div class="row mt-4">
        <div class="col-6">
            <div class="d-flex align-items-center">
                <div class="badge bg-success bg-opacity-10 text-success p-1 rounded-3 me-3">
                    <?= Html::img($detail->driver?->showAvatar(), ['style' => 'max-width:50px']) ?>
                </div>
                <div>
                    <div class="d-flex flex-row gap-2 align-items-center">
                        <small class="text-muted d-block">พนักงานขับรถที่ได้รับจัดสรร</small> <?= $detail->viewStatus()['view'] ?>
                    </div>
                    <div class="d-flex flex-column">

                        <span class="fw-bold"><?= $detail->driver?->fullname ?? 'ยังไม่ได้จัดสรร พขร.' ?></span> <span class="mx-2 text-light"></span>
                        <small class="text-muted">โทร : <?= $detail->driver?->phone ?? '-' ?></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="d-flex">
                <?php
                try {
                    echo $detail->car ? Html::img($detail->car?->ShowImg()['image'], ['class' => 'avatar rounded border-secondary']) : '';
                } catch (\Throwable $th) {
                    //     //throw $th;
                }
                ?>
                <div class="avatar-detail">
                    <div class="d-flex flex-column">
                        <p class="mb-0"><?= $detail->car?->data_json['brand']; ?></p>
                        <p class="mb-0 text-primary"><?= $detail->license_plate ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-3">
            <?php if(Yii::$app->user->can('driver')):?>
            <?= Html::a('<i class="fa-solid fa-key"></i> บันทึกภารกิจ', ['/booking/vehicle/work-update', 'id' => $detail->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์'], ['class' => 'btn btn-outline-warning btn-sm rounded-pill px-4 fw-bold shadow-sm open-modal', 'data' => ['size' => 'modal-lg']]) ?>
            <?php else:?>
                <button type="button" class="btn btn-outline-secondary" disabled><i class="fa-solid fa-key"></i> บันทึกภารกิจ</button>

        <?php endif;?>
        </div>
    </div>
<?php endforeach; ?>
<!-- ######## -->
<div class="alert alert-light mt-3" r
ole="alert">
    <strong>หมายเหตุ</strong> ***
    <p><?= isset($model->data_json['coment']) ? $model->data_json['coment'] : '-' ?></p>
</div>

<div class="form-group">
    <?php echo $model->data_json['remarks'] ?? '-'; ?>
</div>
<?php //echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/booking/vehicle/approve', 'id' => $model->id, 'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'], ['class' => 'btn btn-warning rounded-pill shadow me-1 open-modal', 'data' => ['size' => 'modal-lg']]) 
?>
<div class="d-flex justify-content-center gap-3">
    <!-- ถ้าเป็นเจ้าของใบจอง หรือ เป็นสิทธิ Driver ให้สามารถแก้ไขยกเลิกได้ -->
    <?php if (($model->created_by == Yii::$app->user->id) || Yii::$app->user->can('driver')): ?>
        <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/booking/vehicle/update', 'id' => $model->id, 'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'], ['class' => 'btn btn-warning rounded-pill shadow me-1 open-modal', 'data' => ['size' => 'modal-xl']]) ?>
        <?php echo Html::a('<i class="fa-regular fa-circle-xmark"></i> ยกเลิกการจอง', ['/booking/vehicle/cancel', 'id' => $model->id], ['class' => 'btn btn-danger rounded-pill shadow me-1 btn-cancel-booking']) ?>
    <?php endif; ?>
    <?= Html::button('<i class="fa-regular fa-circle-xmark"></i> ปิด', [
        'class' => 'btn btn-secondary rounded-pill',
        'data-bs-dismiss' => 'modal'
    ]) ?>
</div>
<?php
$js = <<<JS

$('.btn-cancel-booking').click(function (e) { 
    e.preventDefault();
    var url = $(this).attr('href');
    console.log($(this).attr('href'));
    
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
            // --- เริ่มต้นแสดงสถานะ Loading ---
            closeModal()
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
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
                        }).then(() => {
                            window.location.reload();
                        });
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