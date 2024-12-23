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
                        
                            // id: event.id, // ระบุ ID ของ event
                            // start: event.start.format(), // วันเริ่มต้นที่แก้ไข
                            // end: event.end ? event.end.format() : null // วันสิ้นสุดที่แก้ไข (ถ้ามี)
                            // console.log('eventDrop',event.end.format());
                
               }
               "),
               'select'=> new \yii\web\JsExpression("function(start, end, jsEvent, view) {
               addEvent(moment(start).format(),moment(end).format())
                    // กำหนดปฏิทิน
                var calendar = $('#calendar').fullCalendar('getCalendar');
                
                // เพิ่ม Event แบบ dynamic
        

                }"),
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
                    console.log(res)
                }
            });
            $('#calendar').fullCalendar('renderEvent', {
                    title: 'OFF',
                    start: start,
                    end: end,
                    allDay: true
                }, true);
            }
        });
}

function updateEvent(id,start,end)
{
    $.ajax({
                type: "post",
                url: "$addEvent",
                data:{'start':start,'end':end},
                dataType: "json",
                success: function (res) {
                    console.log(res)
                }
            });
}
JS;
$this->registerJS($js,View::POS_END)
?>