<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\UserHelper;
use app\modules\booking\models\Room;
use app\modules\booking\models\RoomLayout;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */

$this->title = 'ขอใช้' . $model->room->title;
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$me = UserHelper::GetEmployee();
$room = Room::findOne(['name' => 'meeting_room', 'code' => $model->room_id]);
$roomLayout = RoomLayout::findOne(['name' => 'room_layout', 'code' => $model->room_layout_id]);
$labels = $model::equipmentItems();
$requester = $model->getUserReq();
$canEditMeeting = $me && $model->status !== 'Cancel' && (Yii::$app->user->can('meeting') || $me->id == $model->emp_id);
$meetingData = is_array($model->data_json ?? null)
    ? $model->data_json
    : (is_string($model->data_json ?? null) ? (json_decode($model->data_json, true) ?: []) : []);
$equipments = $meetingData['equipment'] ?? [];
if (!is_array($equipments)) {
    $equipments = [$equipments];
}

?>

<style>
    .room-img,
    .room-layout-img {
        object-fit: cover;
        max-width: 100%;
        max-height: 200px;
    }
</style>
<div class="row">
    <div class="col-8">


        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">เลขที่ :</label>
            <div class="col-sm-8"><?= $model->code; ?></div>
        </div>


        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">วันที่:</label>
            <div class="col-sm-8 d-flex align-items-center gap-2">
                <i class="fa-solid fa-calendar-day"></i>
                <?= $model->viewMeetingDate() ?> เวลา <?= $model->viewTime()['full'] ?>
            </div>
        </div>
        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">ขอใช้ห้องประชุม:</label>
            <div class="col-sm-8"><?= $model->room->title; ?></div>
        </div>
        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">ผู้ขอใช้:</label>
            <div class="col-sm-8">
                <?= $requester['avatar'] ?: '-' ?>
            </div>
        </div>
        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">หัวข้อการประชุม:</label>
            <div class="col-sm-8"><?= $model->title; ?></div>
        </div>
        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">จำนวนผู้เข้าร่วม:</label>
            <div class="col-sm-8"><?= $model->emp_number ?? 0 ?> คน</div>
        </div>

        <div class="row mb-0 align-items-top">
            <label class="col-sm-3 col-form-label text-end fw-medium">รายการอุปกรณ์:</label>
            <div class="col-sm-8">

                <ul>
                    <?php foreach ($equipments as $item): ?>
                        <li><?= $labels[$item] ?? $item ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">เบอร์ติดต่อ:</label>
            <div class="col-sm-8">
                <?= $meetingData['phone'] ?? '-' ?>
            </div>
        </div>
        <div class="row mb-0 align-items-center">
            <label class="col-sm-3 col-form-label text-end fw-medium">สถานะ:</label>
            <div class="col-sm-8">
                <?= $model->viewStatus()['view'] ?>
            </div>
        </div>
    </div>
    <div class="col-4">
        <p>รูปแบบการจัดห้องประชุม</p>
        <div class="rounded-md d-flex mb-3">
            <?php if ($roomLayout && $roomLayout->showImg()['isFile']): ?>
                <?= Html::img($roomLayout->showImg()['image'], ['class' => 'room-layout-img']) ?>
            <?php else: ?>
                <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-layout-img']) ?>
            <?php endif ?>
        </div>
    </div>
</div>

<div class="d-flex flex-column-reverse flex-sm-row justify-content-sm-center gap-2 mt-3">

    <!-- แก้ไขได้เฉพาะเจ้าของหรือสิทธิ์ meeting และต้องไม่อยู่สถานะ Cancel -->
    <?php if ($canEditMeeting): ?>
        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i> แก้ไข', ['/me/booking-meeting/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning rounded-pill shadow open-modal', 'data' => ['size' => 'modal-xl']]) ?>
    <?php endif; ?>

    <?php if ($model->status == 'Pending'): ?>
        <button type="button" class="btn btn-primary confirm-meeting  rounded-pill" data-id="<?= $model->id ?>" data-status="Pass" data-text="อนุมัติการจอง" data-icon="success">
            <i class="fa-regular fa-circle-check"></i> อนุมัติ
        </button>
    <?php endif; ?>

    <?php if ($model->status !== 'Cancel'): ?>
        <button type="button" class="btn btn-danger confirm-meeting  rounded-pill" data-id="<?= $model->id ?>" data-status="Cancel" data-text="ปฏิเสธการจอง" data-icon="warning">
            <i class="fa-solid fa-xmark"></i> ยกเลิกการจอง
        </button>
    <?php endif; ?>

    <button type="button" class="btn btn-secondary  rounded-pill" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>
