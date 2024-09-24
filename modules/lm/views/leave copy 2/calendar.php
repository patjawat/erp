
<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\JsExpression;
?>
<?= moreamazingnick\fullcalendar\Fullcalendar::widget([
    'options'       => [
        'id'       => 'calendar',
    ],
    // 'clientOptions' => [
    //     'locale' => 'th',
    //     'timeZone'=> 'local',

    //     //'initialView'=>'timeGridWeek',
    //     'firstDay' => 1,
    //     'weekNumbers' => true,
    //     'selectable'  => true,
    //     'eventTimeFormat' => [
    //         'hour' => '2-digit',
    //         'minute' => '2-digit',
    //         'omitZeroMinute' => false,
    //     ],
    //     'editable' => true,

    // ],

    'clientOptions' => [
         'height' =>  '450px',
        'locale' => 'th',
        'timeZone' => 'local',
        //modify eventcontainer
        'eventDidMount' => new JsExpression("
            function (info) {
                    let p = document.createElement('span');
                    p.innerHTML='mytest';
                    p.className = 'mytest';
                    info.el.append(p)
            }
        "),
        'eventAdd' => new JsExpression("
            function (info) {
                console.log(info);
            }
        "),
        'loading' => new JsExpression("
            function(bool) {
                 if(bool){
                    //   doSomething();
                 }else{
                    //  doSomethingElse();
                 }
            }
        "),
        // select an empty time slot and do something, like create an event
        'select' => new JsExpression("
             function(arg) {
                 Event = {
                     title: 'Slot',
                     start: arg.start,
                     end: arg.end,
                     editable: true,
                     startEditable: true,
                     durationEditable: true,
                     allDay: arg.allDay,
                 };
                 postevent=JSON.parse(JSON.stringify({Event}));
                     jQuery.ajax({
                         type: 'POST',
                         url: '" . Url::to(['yourcontroller/create-event']) . "',
                         data: postevent,
                         success: function(response){
                             console.log('create event select');
                             if(response){
                                 calendar.addEvent(response)
                             }else{
                                 alert('Could not create event!');
                             }
                             calendar.unselect() 
                        },
                        error: function(){
                            alert('Could not create event!');
                            calendar.unselect()
                        },
                    });
             }
        "),
        // moves event from one timeslot to another
        'eventDrop' => new JsExpression(" 
            function(eventDropInfo) {
            Event=eventDropInfo.event;
            postdata=JSON.parse(JSON.stringify({Event}));
            jQuery.ajax({
                type: 'POST',
                url: '" . Url::to(['yourcontroller/update-event']) . "',
                data: postdata,
                success: function(response){
                console.log('create event drop');
                    if(response){

                    }else{
                        alert('Could not alter event!');
                        eventDropInfo.revert();
                    }
                    console.log(response); 
                },
                error: function(){
                    alert('Could not alter event!')
                    eventDropInfo.revert();
                },

            });

        }"),
        // You can use the same implementation as in eventDrop
        'eventResize' => new JsExpression("
            function(eventResizeInfo) {
         console.log('re')
            }
        "),
        //OnClick event, for example you can open a modal window or fetch more details
        'eventClick' => new JsExpression("
            function (e) {
            }
        "),
    ],
    'events'        => new JsExpression('[
            {
                "id":null,
                "title":"Appointment #776",
                "allDay":false,
                "start":"2024-09-18T14:00:00",
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
            {
                "id":"56e74da126014",
                "title":"Appointment #928",
                "allDay":false,
                "start":"2024-09-17T12:30:00",
                "end":"2024-09-17T13:30:00",
                "url":null,
                "className":null,
                "editable":true,
                "startEditable":true,
                "durationEditable":true,
                "rendering":null,
                "overlap":true,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
            {
                "id":"56e74da126050",
                "title":"Appointment #197",
                "allDay":false,
                "start":"2024-09-17T15:30:00",
                "end":"2024-09-17T19:30:00",
                "url":null,
                "className":null,
                "editable":true,
                "startEditable":true,
                "durationEditable":true,
                "rendering":null,
                "overlap":false,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
            {
                "id":"56e74da126080",
                "title":"Appointment #537",
                "allDay":false,
                "start":"2024-09-16T11:00:00",
                "end":"2024-09-16T11:30:00",
                "url":null,
                "className":null,
                "editable":false,
                "startEditable":false,
                "durationEditable":true,
                "rendering":null,
                "overlap":true,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
            {
                "id":"56e74da1260a7",
                "title":"Appointment #465",
                "allDay":false,
                "start":"2024-09-15T14:00:00",
                "end":"2024-09-15T15:30:00",
                "url":null,
                "className":null,
                "editable":false,
                "startEditable":true,
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

