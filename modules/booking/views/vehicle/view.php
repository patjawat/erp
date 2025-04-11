<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehicles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="mb-3 badge-soft-primary p-3 rounded">
    <label class="form-label fw-bold">เลขที่คำขอ: <?php echo $model->code?></label>
    <p class="mb-0"><?php echo $model->userRequest()['fullname'];?>
        ขอใช้<?php echo $model->carType->title;?>ไป<?php echo $model->locationOrg?->title ?? '-'?> วันที่
        <?php echo $model->showDateRange()?></p>
        <p class="text-muted mb-0 fs-13">เวลา <?=$model->viewTime()?></p>
       

</div>

<div class="booking-details">
    <label class="form-label">จัดสรรรถ</label>
    <table class="table table-bordered" id="details-table">
        <thead class="table-light">
            <tr>
                <th>วันที่</th>
                <th>รถ</th>
                <th>พนักงานขับรถ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->vehicleDetails ?? [] as $index => $detail): ?>
            <tr class="detail-row">
                <td>
                    <?=$detail->showDate()?>
                    <input type="hidden" name="vehicleDetails[<?= $index ?>][id]" value="<?= $detail->id ?>">
                </td>
                <td class="">
                            <div class="d-flex">
                                <?=$detail->car ? Html::img($detail->car?->ShowImg(),['class' => 'avatar rounded border-secondary']) : ''?>
                                <div class="avatar-detail">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0"><?=$detail->car?->data_json['brand'];?></p>
                                        <p class="mb-0 fw-semibold text-primary"><?=$detail->license_plate?></p>
                                    </div>
                                </div>
                            </div>
                        </td>
                <td>
                    <?=$detail->driver?->getAvatar(false,($detail->driver?->phone))?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="form-group">
    <?php echo $model->data_json['remarks'] ?? '-'; ?>
</div>
<div class="d-flex justify-content-center gap-2">
    <?php if($model->status == 'Pending'):?>
        <?php echo Html::a('<i class="bi bi-check-circle me-1"></i> จัดสรร', ['/booking/vehicle/approve', 'id' => $model->id,'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'], ['class' => 'btn btn-sm btn-success rounded-pill shadow me-1 open-modal', 'data' => [ 'size' => 'modal-lg']])?>
        <?php echo Html::a('<i class="bi bi-x-circle me-1"></i> ปฏิเสธ', ['/booking/vehicle/reject', 'id' => $model->id], ['class' => 'btn btn-sm btn-danger rounded-pill shadow me-1'])?>
        <?php else:?>
            <?php echo Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/booking/vehicle/approve', 'id' => $model->id,'title' => '<i class="bi bi-check-circle me-1"></i> อนุมัติการจัดสรรรถ'], ['class' => 'btn btn-sm btn-warning rounded-pill shadow me-1 open-modal', 'data' => [ 'size' => 'modal-lg']])?>
            <?php echo Html::a('<i class="fa-regular fa-circle-xmark"></i> ยกเลิกการจอง', ['/booking/vehicle/cancel', 'id' => $model->id], ['class' => 'btn btn-sm btn-danger rounded-pill shadow me-1 btn-cancel'])?>
            <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
            <?php endif;?>
</div>


<?php
$js = <<<JS

$('.btn-cancel').click(function (e) { 
    e.preventDefault();
    const url = $(this).attr('href');
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