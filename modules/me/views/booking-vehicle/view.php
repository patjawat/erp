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

<div class="mb-3 p-3 rounded">
    <div class="d-flex justify-content-between align-items-center mb-2">

        <h5 class="mb-0">
            ขอใช้<?php echo $model->carType?->title;?>ไป<?php echo $model->locationOrg?->title ?? '-'?> </h5>
        <p class="text-muted mb-0 fs-13"></p>
       
    </div>
    <p>วันที่ <?php echo $model->showDateRange()?> เวลา <?=$model->viewTime()?></p>
</div>
    
    <div class="row">
        <div class="col-6">
            <?php echo $model->userRequest()['avatar'];?>
        </div>
        <div class="col-6">โทร : <?php echo $model->userRequest()['phone'];?></div>
        <div class="col-12"></div>
    </div>

    <div
        class="alert alert-light mt-3"
        role="alert"
    >
        <strong>หมายเหตุ</strong> ***
        <p><?=isset($model->data_json['coment']) ? $model->data_json['coment'] : '-'?></p>
    </div>
    

<div class="booking-details mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <label class="form-label">ข้อมูลการจัดสรรจัดสรรรถ</label>
         <span><?=$model->viewStatus()['view']?></span>
    </div>
    <table class="table table-bordered" id="details-table">
        <thead class="table-light">
            <tr>
                <th>วันที่</th>
                <th>รถทะเบียน</th>
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
                        <?php
                        try {
                            echo $detail->car ? Html::img($detail->car?->ShowImg(),['class' => 'avatar rounded border-secondary']) : '';
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
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



<?php if($model->status == 'Pending' && Yii::$app->user->id == $model->created_by):?>
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
            $('#main-modal').hide()
            // แสดง loading
            Swal.fire({
                title: 'กำลังยกเลิกการจอง...',
                html: 'กรุณารอสักครู่',
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
                            timer: 1000,
                            showConfirmButton: false
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