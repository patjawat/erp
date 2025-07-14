<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */

$this->title = 'ขอใช้'.$model->room->title;
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="row mb-0 align-items-center">
    <label class="col-sm-4 col-form-label text-end fw-medium">หัวข้อการประชุม:</label>
    <div class="col-sm-8"><?=$model->title;?></div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-4 col-form-label text-end fw-medium">ห้องประชุม:</label>
    <div class="col-sm-8"><?=$model->room->title;?></div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-4 col-form-label text-end fw-medium">วันที่:</label>
    <div class="col-sm-8 d-flex align-items-center gap-2">
    <i class="fa-solid fa-calendar-day"></i>
        <?=$model->viewMeetingDate()?>
    </div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-4 col-form-label text-end fw-medium">เวลา:</label>
    <div class="col-sm-8 d-flex align-items-center gap-2">
    <i class="fa-regular fa-clock"></i>
    <?=$model->viewMeetingTime()?>
    </div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-4 col-form-label text-end fw-medium">จำนวนผู้เข้าร่วม:</label>
    <div class="col-sm-8">20 คน</div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-4 col-form-label text-end fw-medium">สถานะ:</label>
    <div class="col-sm-8">
    <?=$model->viewStatus()['view']?>
    </div>
</div>


<div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-center gap-2 mt-3">
<?php if($model->status == 'Pending'):?>
    <button type="button" class="btn btn-primary confirm-meeting  rounded-pill" data-id="<?=$model->id?>" data-status="Pass" data-text="อนุมัติการจอง" data-icon="success">
    <i class="fa-regular fa-circle-check"></i> อนุมัติ
    </button>
    <?php endif;?>

  <button type="button" class="btn btn-danger confirm-meeting  rounded-pill" data-id="<?=$model->id?>" data-status="Cancel" data-text="ปฏิเสธการจอง" data-icon="warning">
  <i class="fa-solid fa-xmark"></i> ยกเลิก
  </button>

  <button type="button" class="btn btn-secondary  rounded-pill" data-bs-dismiss="modal"><i
  class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>
