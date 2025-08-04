<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use app\models\Categorise;
use app\modules\booking\models\Vehicle;


$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'ระบบขอใช้ยานพาหนะ/ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้รถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('menu', ['active' => $vehicle_type]) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu', ['active' => $vehicle_type]) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        <?= $this->render('@app/modules/booking/views/vehicle/carlendar_item', ['vehicle_type' => $vehicle_type]); ?>
    </div>
    <div class="col-lg-4 col-md-12 col-sm-12">
        <div id="showEventToDays"></div>
        <div id="showEventTomorrow"></div>
    </div>
</div>


<?php
$urlEventToDays = Url::to(['/booking/vehicle/list-event-todays','vehicle_type' => $vehicle_type]);
$urlEventTomorrow = Url::to(['/booking/vehicle/list-event-tomorrow','vehicle_type' => $vehicle_type]);
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