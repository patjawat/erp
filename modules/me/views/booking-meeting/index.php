<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
$this->title = 'จองห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;
$me = UserHelper::getEmployee();
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-person-chalkboard fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<style>
.fc .fc-toolbar>*> :first-child {
    margin-left: 0;
    font-size: medium;
}
</style>
<?php // echo $this->render('list_room')?>
<div class="alert alert-primary" role="alert">
    <strong>Alert Heading</strong> Some Word
</div>

<div class="row">
    <div class="col-5">

    <?php echo $this->render('list_room')?>

        <!-- <div class="card" style="height: 340px;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <?php echo Html::a('วันก่อน',['/me/booking-metting'])?>
                    <h6><i class="fa-regular fa-calendar-plus"></i>
                        <?php $time = time(); echo Yii::$app->thaiFormatter->asDate($time, 'full')."<br>";?>
                    </h6>
                    <?php echo Html::a('วันถัดไป',['/me/booking-metting'])?>
                </div>
                <table class="table table-striped table-bordered">
                    <tbody class="align-middle table-group-divider">
                        <tr class="text-center" style="background:#dcdcdc;">
                            <th class="text-start">สถานที่</th>
                            <th width="150px">วันที่ และเวลา</th>
                            <th>หัวข้อการใช้ห้องปรุชุม</th>
                            <th width="80px">จำนวน</th>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                                14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>

                            <td class="text-center"><span class="badge badge-success">10</span></td>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                                14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>

                            <td class="text-center"><span class="badge badge-success">8</span></td>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                                14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>

                            <td class="text-center"><span class="badge badge-success">13</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-2" style="height: 340px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6><i class="fa-regular fa-calendar-plus"></i> รายการของ<?php echo $me->fullname?></h6>
                    <?php // echo Html::a('จองห้องประชุม',['/me/booking-meeting/list-room'],['class' => 'btn btn-primary rounded-pill shadow open-canvas'])?>
                </div>
                <table class="table table-striped table-bordered">
                    <tbody class="align-middle table-group-divider">
                        <tr class="text-center" style="background:#dcdcdc;">
                            <th class="text-start">สถานที่</th>
                            <th width="150px">วันที่ และเวลา</th>
                            <th>หัวข้อการใช้ห้องปรุชุม</th>
                            <th width="80px">จำนวน</th>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                                14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>

                            <td class="text-center"><span class="badge badge-success">10</span></td>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                                14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>

                            <td class="text-center"><span class="badge badge-success">8</span></td>
                        </tr>
                        <tr>
                            <td>ห้องหน่อไม้หวาน</td>
                            <td class="text-center p-1">
                                14:00-14:00 น.</td>
                            <td>ประชุม กกบ.รพร.</td>

                            <td class="text-center"><span class="badge badge-success">13</span></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div> -->

    </div>
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

    <div class="col-4">
        <?php //  $this->render('list_room')?>
    </div>
</div>


<?php  echo $this->render('grid_room')?>

<?php
$js = <<< JS

$(".open-canvas").click(function(e){
            e.preventDefault();
            var offcanvasElement = new bootstrap.Offcanvas($("#MyOffcanvas")[0]);
            offcanvasElement.show();
            $.ajax({
                type: "get",
                url: $(this).attr('href'),
                dataType: "json",
                success: function (res) {
                    $('.offcanvas-body').html(res.content)
                }
            });
            $('.offcanvas-title').text('ssss');
        });
        

JS;
$this->registerJS($js,View::POS_END);
?>