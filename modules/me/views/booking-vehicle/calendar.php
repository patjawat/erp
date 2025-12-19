<?php

use yii\web\View;
use yii\helpers\Url;


$this->title = 'ระบบจองรถ/ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
        <?= $this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?= $this->render('menu', ['active' => $vehicle_type]) ?>
    <?= $this->render('@app/components/ui/btnReturn')?>
</div>
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