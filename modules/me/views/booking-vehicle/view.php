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
    <label class="form-label fw-bold">เลขที่คำขอ: <?php echo $model->code?></label> <span><?=$model->viewStatus()['view']?></span>
    <p><?php echo $model->userRequest()['fullname'];?> ขอใช้<?php echo $model->carType->title;?>ไป<?php echo $model->locationOrg?->title ?? '-'?> วันที่ <?php echo $model->showDateRange()?></p>
    
       

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
                    <?php if($detail->car):?>
                            <div class="d-flex">
                                <?=Html::img($detail->car->ShowImg(),['class' => 'avatar rounded border-secondary'])?>
                                <div class="avatar-detail">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0"><?=$detail->car->data_json['brand'];?></p>
                                        <p class="mb-0 fw-semibold text-primary"><?=$detail->license_plate?></p>
                                    </div>
                                </div>
                            </div>
                            <?php else:?>
                            <?=$detail->license_plate;?>
                            <?php endif;?>
                        </td>
                <td>
                    <?=$detail->driver?->getAvatar(false,($detail->driver->phone))?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="form-group">
    <?php echo $model->data_json['remarks'] ?? '-'; ?>
</div>
<?php if($model->status == 'Pending'):?>
<div class="d-flex justify-content-center gap-2">
        <?= Html::a('<i class="bi bi-pencil"></i> แก้ไข', ['/me/booking-vehicle/update','id' => $model->id,'title' => '<i class="bi bi-pencil"></i> แก้ไขแบบขอใช้รถยนต์'],['class' => 'btn btn-warning rounded-pill open-modal','data' => ['size' => 'modal-lg']]) ?>
        <?= Html::a('<i class="fa-solid fa-xmark"></i> ยกเลิกการจอง', ['/me/booking-vehicle/cancel', 'id' => $model->id], ['class' => 'btn btn-secondary rounded-pill cancel-booking']) ?>
</div>
<?php endif;?>


<?php
$js = <<<JS

$('.cancel-booking').click(function (e) { 
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