<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\booking\models\Booking;
/** @var yii\web\View $this */
if($searchModel->car_type == 'general'){
    $this->title = ' ทะเบียนขอใช้รถทั่วไป'; 
}

if($searchModel->car_type == 'ambulance'){
    $this->title = 'ทะเบียนขอใช้รถพยาบาล'; 
}
?>


<?php $this->beginBlock('icon'); ?>
<?php if($searchModel->car_type == 'general'):?>
    <i class="fa-solid fa-car fs-1"></i>
    <?php endif;?>
    <?php if($searchModel->car_type == 'ambulance'):?>
        <i class="fa-solid fa-truck-medical fs-1"></i>
    <?php endif;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
ระบบยานพาหนะ
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-3">
        <a href="<?php echo Url::to(['/booking/driver','car_type'=>'general','status' => 'RECERVE'])?>">

            <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold">0</span>
                        <div class="relative">
                        <i class="fa-solid fa-circle-exclamation text-warning fs-1"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <h6>ร้องขอ</h6>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-3">
        <a href="<?php echo Url::to(['/booking/driver','car_type'=>'general','status' => 'SUCCESS'])?>">
            <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <span class="h2 fw-semibold"></span>
                        <div class="relative">
                            <i class="fa-solid fa-share-nodes fs-1"></i>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between gap-1 mb-0">
                        <h6>จัดสรร</h6>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-3">
        <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h2 fw-semibold">1,417</span>
                    <div class="relative">
                        <i class="fa-solid fa-truck fs-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <h6>EMS</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card hover-card-under border-4 border-start-0 border-end-0 border-top-0 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <span class="h2 fw-semibold">445</span>
                    <div class="relative">
                        <i class="fa-solid fa-car-side fs-1"></i>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-1 mb-0">
                    <h6>รับ-ส่ง</h6>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="row">
<div class="col-7">
<div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> ปฏิทินรวม </h6>
                <?= edofre\fullcalendar\Fullcalendar::widget([
                        'options'       => [
                                    'id'       => 'calendar',
                                    'language' => 'th',
                                ],
                                'clientOptions' => [
                                    'weekNumbers' => true,
                                    'selectable'  => true,
                                    'droppable' => true,
                                    'defaultView' => 'month',
                                    'eventResize' => new  \yii\web\JsExpression("
                                        function(event, delta, revertFunc, jsEvent, ui, view) {
                                            console.log(event);
                                        }
                                    "),
                                
                                    'droppable' => true,
                                    'drop'              => new \yii\web\JsExpression("
                                        function(date, jsEvent, ui, resourceId) {
                                        console.log('drop', date.format(), resourceId);
                        
                                        if ($('#drop-remove').is(':checked')) {
                                            // if so, remove the element from the \"Draggable Events\" list
                                            // $(this).remove();
                                        }
                                    }
                                "),
                                'eventReceive'      => new \yii\web\JsExpression("
                                    function(event) { // called when a proper external event is dropped
                                        console.log('eventReceive', event);
                                        }
                                        "),
                                        'eventDrop'         => new \yii\web\JsExpression("
                                        function(event, delta, revertFunc, jsEvent, ui, view,info) {
                                            var id =  event.id;
                                            var start = event.start.format();
                                            var end = event.end ? event.end.format() : null;
                                            // console.log('eventDrop',start);
                                            updateEvent(id,start,end)
                                    
                                }
                                "),
                                'select'=> new \yii\web\JsExpression("function(start, end, jsEvent, view) {
                                            addEvent(moment(start).format(),moment(end).format())
                                                // กำหนดปฏิทิน
                                            var calendar = $('#calendar').fullCalendar('getCalendar');
                                    
                                    // เพิ่ม Event แบบ dynamic
                            

                                }"),
                                'eventClick' => new \yii\web\JsExpression("
                                    function(calEvent, jsEvent, view) {
                                    var id =  calEvent.id;
                                    viewEvent(id)

                                }
                                "),
                                    
                                    'eventRender' => new \yii\web\JsExpression("
                                    function(event, element) {
                                        if (event.imageUrl) {
                                            element.find('.fc-title').before('<img src=\"' + event.imageUrl + '\" style=\"width:20px;height:20px;margin-right:5px;\">');
                                        }
                                            
                                    }
                                "),
                                ],
                                'events'        => Url::to(['/me/holidays/events']),
                            ]);
                        ?>


            </div>
        </div>
</div>
<div class="col-5">


<div class="overflow-auto p-2" style="max-height: 700px;">
    <?php foreach(Booking::find()->where(['name' => 'driver_service'])->all() as $item):?>
    <div class="card hover-card mb-2">
        <div class="card-body p-1">
            <div class="d-flex">
                <div class="flex-shrink-0 rounded p-5" style="background-image:url(<?php // echo $item->showImg()?>);background-size:cover;background-repeat:no-repeat;background-position:center;">

                </div>
                <div class="flex-grow-1 ms-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="d-flex flex-column">
                            <span><?php //  echo $item->title;?></span>
                            <span>ที่นั่ง <?php // echo $item->data_json['seat_capacity'] ?? 0?> </span>

                        </div>

                        <span class="text-black bg-success-subtle badge rounded-pill fw-ligh fs-13 me-3 mt-2">ว่าง</span>
                        
                    </div>
                    
                    
                
                </div>
            </div>
        </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
            <?php // echo $item->showOwner();?>
           <?php // echo Html::a('<i class="fa-solid fa-thumbtack"></i> จอง',['/me/booking-meeting/create','room_id' => $item->code,'title' => 'ขอให้'.$item->title],['class' => 'btn btn-sm btn-primary shadow rounded-pill float-end open-modal','data' => ['size' => 'modal-lg']])?>
            </div>
    </div>
    <?php endforeach;?>
</div>



</div>
</div>



<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
                <h6><i class="bi bi-ui-checks"></i> ทะเบียนการ<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
        </div>

        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ลำดับ</th>
                    <th scope="col">สถานะ</th>
                    <th scope="col">ความเร่งด่วน</th>
                    <th scope="col">ความพึงพอใจ</th>
                    <th scope="col">ทะเบียน</th>
                    <th class="text-start" scope="col">วัน/เวลา</th>
                    <th class="text-start" scope="col">สถานที่ไป</th>
                    <th scope="col">เหตุผลการขอรถ</th>
                    <th scope="col">ผู้ร้องขอ</th>
                    <th scope="col">พขร.ที่จัดสรร</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $key => $item):?>
               <tr>
                <td><?php echo $key+1;?></td>
                <td><?php echo $item->status;?></td>
                <td><?php echo $item->reason;?></td>
                <td><?php // echo $item->car->Avatar();?></td>
                <td><?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?></td>
                <td><?php echo $item->showStartTime()?></td>
                <td><?=Yii::$app->thaiFormatter->asDate($item->date_end, 'medium')?></td>
                <td><?php echo $item->showEndTime()?></td>
                <td><?php echo $item->leader_id?></td>
                <td></td>
                <td class="text-center">
                <?php // echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/booking/driver/update','id' => $item->id,'title' => '<i class="fa-solid fa-briefcase"></i> จัดสรร'],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/booking/driver/update','id' => $item->id,'title' => '<i class="fa-solid fa-briefcase"></i> จัดสรร'],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                </td>
               </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>

    </div>
</div>
