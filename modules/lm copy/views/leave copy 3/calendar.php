<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\JsExpression;
?>
<style>
#external-events {
    padding: 0 10px;
    border: 1px solid #ccc;
    background: #eee;
    text-align: left;
}

#external-events h4 {
    font-size: 16px;
    margin-top: 0;
    padding-top: 1em;
}

#external-events .fc-event {
    margin: 10px 0;
    cursor: pointer;
}

#external-events p {
    margin: 1.5em 0;
    font-size: 11px;
    color: #666;
}

#external-events p input {
    margin: 0;
    vertical-align: middle;
}


</style>

<div class="">
<span id="testbtn" class="btn btn-primary">Test</span>
</div>
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
                    // console.log('eventReceive', event);
                }
            "),
            'eventDrop'         => new \yii\web\JsExpression("
               function(event, delta, revertFunc, jsEvent, ui, view,info) {
                
               }
               "),
               'select'         => new \yii\web\JsExpression("
               function(start, end, jsEvent, view) { // called when an event (already on the calendar) is moved
                 var allDay = !start.hasTime() && !end.hasTime();
                alert(['Event Start date: ' + moment(start).format(),
                'Event End date: ' + moment(end).format(),
               'AllDay: ' + allDay].join('\\n'));
                
            }
        "),
   
       
                
    
            ],
            
            'events'        => new JsExpression('[
                {
                    "id":null,
                    "title":"Appointment #776",
                    "allDay":false,
                    "start":"2016-03-18T14:00:00",
                    "end":null,
                    "url":null,
                    "className":null,
                    "editable":false,
                    "startEditable":false,
                    "durationEditable":false,
                    "rendering":null,
                    "overlap":true,
                    "constraint":null,
                    "source":null,
                    "color":null,
                    "backgroundColor":"grey",
                    "borderColor":"black",
                    "textColor":null
               },
        ]'),
        ]);
    ?>


<?php

/**
 * IMPORTANT
 *
 * For the draggables to work make sure you include the 'yii\jui\JuiAsset' asset to your AppAsset depends configuration
 *
 * IMPORTANT
 */

 $js = <<< JS
$('#external-events .fc-event').each(function() {

// store data so the calendar knows to render an event upon drop
$(this).data('event', {
    title: $.trim($(this).text()), // use the element's text as the event title
    stick: true // maintain when user navigates (see docs on the renderEvent method)
});

$(this).draggable({
    zIndex: 999,
    revert: true,      // will cause the event to go back to its
    revertDuration: 0  //  original position after the drag
});
});

// var calendar = $('#calendar').full
// $("#testbtn").on("click", function(){
//     var calendarEl = $("#calendar");
//     // console.log(calendarEl);
    
//     calendar.Calendar(calendarEl, {addEvent({
//       title: 'Third Event',
//       start: '2024-09-01',
//       end: '2020-04-05'
//     });
//   }); 

JS;
$this->registerJS($js,\yii\web\View::POS_END);

?>