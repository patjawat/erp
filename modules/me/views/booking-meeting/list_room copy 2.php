<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\booking\models\Room;
?>


<div class="card h-100">
          <div class="card-body">
            <h5 class="fw-semibold mb-3">ห้องประชุมที่ว่าง</h5>
            <p class="text-muted">ห้องประชุมที่ว่างในวันนี้</p>
            <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
            <div class="border-bottom pb-3 mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <h6 class="fw-semibold mb-0">ห้องประชุมใหญ่</h6>
                  <p class="text-muted mb-1">ความจุ: 30 คน</p>
                  <p class="text-muted mb-0">เวลา: 13:00 - 17:00 น.</p>
                </div>
                <div>
                  <button class="btn btn-reserve">จองเลย</button>
                </div>
              </div>
            </div>
<?php endforeach?>

          </div>
        </div>
        
<div class="overflow-auto p-2" style="max-height: 690px;">
    <div class="row">

    <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
        <div class="col-lg-6 col-md-12 col-sm-12 col-12">
        <?php $checkRoom = $item->checkRoom($model->date_start)?>
        
    <div class="card hover-card mb-2">
        <div class="card-body p-1">
            <div class="d-flex">
                <div class="flex-shrink-0 rounded p-5" style="background-image:url(<?php echo $item->showImg()?>);background-size:cover;background-repeat:no-repeat;background-position:center;">

                </div>

                <?php if($checkRoom):?>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="d-flex flex-column">
                        <p class="fw-bolder fs-6"><i class="bi bi-clock text-primary"></i> <?php echo $checkRoom->time_start.' - '. $checkRoom->time_end?></p>
                            <p class=""> <?php echo $checkRoom ? $checkRoom->reason :  '<span class="text-black bg-success-subtle badge rounded-pill fw-ligh fs-13 me-3 mt-2">ว่าง</span>'?></p>
                            <!-- <span>ที่นั่ง <?php echo $item->data_json['seat_capacity'] ?? 0?> </span> -->
                        </div>
                            </h1>
                       
                    </div>
                    <?php echo $checkRoom ? 'สถานะ : '.$checkRoom->viewStatus() :  '<span></span>'?>
                    
                
                </div>
                <?php else:?>

                    
                <?php endif;?>

                
            </div>
        </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
            <span class="fw-bolder"><?php  echo $item->title;?></span>
             
            <?php  // echo $item->showOwner();?>
        <?php if($model->date_start >= date('Y-m-d')):?>
            <?php echo Html::a('<i class="fa-solid fa-thumbtack"></i> จอง',['/me/booking-meeting/create','date_start' => $model->date_start,'room_id' => $item->code,'title' => '<i class="fa-solid fa-calendar-plus"></i> ขอให้'.$item->title],['class' => 'btn btn-sm btn-primary shadow rounded-pill float-end open-modal','data' => ['size' => 'modal-lg']])?>
            <?php endif;?>
            </div>
    </div>
</div>
    <?php endforeach;?>
    </div>
</div>

