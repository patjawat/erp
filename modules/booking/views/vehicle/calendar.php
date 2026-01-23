<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
$vehicleStatus = Categorise::find()->where(['name' => 'vehicle_status'])->all();
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <?= $icon ?>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu',['active' => $vehicle_type]) ?>
<?php $this->endBlock(); ?>

<style>
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
</style>

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12" id="calender-container">
        <?= $this->render('carlendar_item', ['vehicle_type' => $vehicle_type]) ?>
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