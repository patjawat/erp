<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\booking\models\Room;

?>

<div class="row">
        <?php foreach(Room::find()->where(['name' => 'meeting_room'])->all() as $item):?>
            <!-- <a href="<?php echo Url::to(['/booking/meeting-room/view','id' => 1])?>" class="open-modal" data-size="modal-lg"> -->
           <div class="col-6">
          
            <div class="card shadow-lg border rounded p-3">
    <div class="bg-primary rounded-4 shadow"style="background-image:url(<?php echo $item->showImg()?>); height: 170px; object-fit: cover;" >
        <?php  // echo Html::img($item->showImg(),['class' => '']);?>
        
    </div>
    <div class="card-body bg-white text-dark">
        <h1 class="d-inline-flex align-items-center fs-5 fw-semibold">
            <?php echo $item->title?> &nbsp;
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                <line x1="7" y1="17" x2="17" y2="7"></line>
                <polyline points="7 7 17 7 17 17"></polyline>
            </svg>
        </h1>
        <p class="mt-3 text-muted small">
            Lorem ipsum 
        </p>
        <!-- <div class="mt-4">
            <span class="badge bg-light text-dark fw-semibold me-2">#Macbook</span>
            <span class="badge bg-light text-dark fw-semibold me-2">#Apple</span>
            <span class="badge bg-light text-dark fw-semibold">#Laptop</span>
        </div> -->
        <button
        type="button"
        class="btn btn-dark w-100 mt-4"
      >
        ขอให้ห้องประชุม
      </button>
    </div>
</div>
  
</div>
        <?php endforeach;?>
    </div>
