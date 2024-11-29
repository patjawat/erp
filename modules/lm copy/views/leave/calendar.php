<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\JsExpression;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
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
<div id='external-events'>
    <p>
        <strong>Draggable Events</strong>
    </p>

    <div class='fc-event bg-warning fc-daygrid-event fc-daygrid-block-event p-2'>
        <div class='fc-event-main'>วัน OFF</div>
    </div>


    <p>
        <input type='checkbox' id='drop-remove' />
        <label for='drop-remove'>remove after drop</label>
    </p>
</div>

<div id='calendar-container'>
    <div id='calendar'></div>
</div>


<?php
// $calDaysUrl = Url::to(['/lm/leave/cal-days']);
$js = <<< JS


JS;
$this->registerJS($js, \yii\web\View::POS_END);

?>