<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\booking\models\Room;

?>
<div class="overflow-auto p-2" style="max-height: 640px;">
    <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
    <div class="card hover-card mb-2">
        <div class="card-body p-1">
            <div class="d-flex">
                <div class="flex-shrink-0 rounded p-5" style="background-image:url(<?php echo $item->showImg()?>);background-size:cover;background-repeat:no-repeat;background-position:center;">

                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="d-flex flex-column">
                            <span><?php  echo $item->title;?></span>
                            <span>ที่นั่ง <?php echo $item->data_json['seat_capacity'] ?? 0?> </span>

                        </div>

                        <span class="text-black bg-success-subtle badge rounded-pill fw-ligh fs-13 me-3 mt-2">ว่าง</span>
                        
                    </div>
                    
                    
                
                </div>
            </div>
        </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
            <?php  echo $item->showOwner();?>
           <?php echo Html::a('<i class="fa-solid fa-thumbtack"></i> จอง',['/me/booking-meeting/create','date_start' => date('Y-m-d'),'room_id' => $item->code,'title' => 'ขอให้'.$item->title],['class' => 'btn btn-sm btn-primary shadow rounded-pill float-end open-modal','data' => ['size' => 'modal-lg']])?>
            </div>
    </div>
    <?php endforeach;?>
</div>
