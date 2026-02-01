<?php

use yii\helpers\Url;
use app\components\MyCalendar\MyCalendar;
use yii\web\View;
?>


<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12" id="calender-container">

        <?php
        $myUrl = Url::to(['/booking/vehicle/get-events', 'start' => '2026-01-01', 'end' => '2026-01-01']);
        echo MyCalendar::widget([
            'apiUrl' => $myUrl // ชื่อ Controller/Action ที่คุณสร้างไว้

        ]);
        ?>
    </div>
    <div class="col-lg-4 col-md-12 col-sm-12" id="manual-container">
        <div id="showEventToDays"></div>
        <div id="showEventTomorrow"></div>
    </div>
</div>


<?php
$urlEventToDays = Url::to(['/booking/vehicle/list-event-todays', 'vehicle_type' => $vehicle_type]);
$urlEventTomorrow = Url::to(['/booking/vehicle/list-event-tomorrow', 'vehicle_type' => $vehicle_type]);

$js = <<<JS
            listEventTomorrow()
            listEventToDays()
            async function listEventToDays()
            {
                await $.ajax({
                    type: "get",
                    url: "$urlEventToDays",
                    dataType: "json",
                    success: function (response) {
                        $('#showEventToDays').html(response.content)
                    }
                });
            }

            async function listEventTomorrow()
            {
                await $.ajax({
                    type: "get",
                    url: "$urlEventTomorrow",
                    dataType: "json",
                    success: function (response) {
                        $('#showEventTomorrow').html(response.content)
                    }
                });
            }
    JS;

$this->registerJS($js, View::POS_END);
?>