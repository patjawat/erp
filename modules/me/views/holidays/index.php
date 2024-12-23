<?php
use yii\web\View;
use yii\helpers\Url;
use yii\web\JsExpression;
use app\modules\hr\models\Calendar;
$this->title = 'วันหยุดของฉัน';
$this->params['breadcrumbs'][] = $this->title;
/** @var yii\web\View $this */
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<!-- <div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> วันหยุดของฉัน <span class="badge rounded-pill text-bg-primary"></span> รายการ</h6>
        </div>
    </div>
</div> -->
<!-- <div class="card">
    <div class="card-body"> -->
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
                'eventResize' => new JsExpression("
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
<!-- </div>
</div> -->

<?php
$addEvent = Url::to(['/me/holidays/create']);
$updateEvent = Url::to(['/me/holidays/update']);
$viewEventUrl = Url::to(['/me/holidays/view']);
$js = <<< JS
function addEvent(start,end){
    console.log('Add Event'+start+' '+end);

    Swal.fire({
        title: "ยืนยัน?",
        text: "เพิ่มวัน Off!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: "$addEvent",
                data:{'start':start,'end':end},
                dataType: "json",
                success: function (res) {
                    if(res.status == 'error'){
                        warning(res.massages)
                    }else{
                        $('#calendar').fullCalendar('renderEvent', {
                    id:res.data.id,
                    title: 'OFF',
                    start: start,
                    end: end,
                    allDay: true
                }, true);
                success(res.massages)
                    }
                }
            });
         
            }
        });
}

function updateEvent(id,start,end)
{
    $.ajax({
                type: "post",
                url: "$updateEvent",
                data:{'id':id,'start':start,'end':end},
                dataType: "json",
                success: function (res) {
                    console.log(res)
                    success();
                }
            });
}

function viewEvent(id)
{
    $.ajax({
                type: "get",
                url: "$viewEventUrl",
                data:{id:id,title:'แสดงรายละเอียด'},
                dataType: "json",
                success: function (res) {
                    $("#main-modal").modal("show");
                    $("#main-modal-label").html(res.title);
                    $(".modal-body").html(res.content);
                    $(".modal-footer").html(res.footer);
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                }
            });
}
JS;
$this->registerJS($js,View::POS_END)
?>