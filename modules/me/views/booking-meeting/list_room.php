<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\booking\models\Room;
?>



        <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
        <div class="border-bottom pb-3 mb-3">
            <div class="d-flex">
                <div class="flex-shrink-0 rounded p-5"
                    style="background-image:url(<?php echo $item->showImg()?>);background-size:cover;background-repeat:no-repeat;background-position:center;">

                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="fw-semibold mb-0">ห้องประชุมใหญ่</h6>
                            <p class="text-muted mb-1">ความจุ: 30 คน</p>
                            <p class="text-muted mb-0">เวลา: 13:00 - 17:00 น.</p>
                        </div>
                        <div>
                            <?=Html::a('จองห้องประชุม',['/me/booking-meeting/create','date_start' => $model->date_start,'title' => '<i class="fa-solid fa-calendar-plus"></i> ขอให้ห้องประชุม'],['class' => 'btn btn-primary shadow rounded-pill open-modal-xx','data' => ['size' => 'modal-xl']])?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach?>

