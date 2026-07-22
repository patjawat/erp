<?php

use yii\web\View;
use yii\helpers\Url;
use app\models\Categorise;
use app\modules\booking\models\Meeting;

// FullCalendar โหลดจาก AppAsset (self-hosted) แล้ว

$this->title = 'ปฏิทินการใช้ห้องประชุม';
$this->params['breadcrumbs'][] = ['label' => 'จองห้องประชุม', 'url' => ['/booking/meeting/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 2v4"></path>
            <path d="M16 2v4"></path>
            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
            <path d="M3 10h18"></path>
            <path d="M8 14h.01"></path>
            <path d="M12 14h.01"></path>
            <path d="M16 14h.01"></path>
            <path d="M8 18h.01"></path>
            <path d="M12 18h.01"></path>
            <path d="M16 18h.01"></path>
        </svg>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/meeting_menu', ['active' => 'calendar']) ?>
<?php $this->endBlock(); ?>



<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('menu', ['active' => 'calendar']) ?>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-9 col-md-9 col-sm-12">
        <?= $this->render('calendar_item') ?>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
        <div id="showToDays"></div>
        <div id="showTomorrow"></div>
    </div>
</div>




<?php
$js = <<< JS

listTomorrow()
listToDays()
async function listTomorrow()
{
    await $.ajax({
        type: "get",
        url: "/me/booking-meeting/event-tomorrow",
        dataType: "json",
        success: function (response) {
            $('#showTomorrow').html(response.content)
        }
    });
}

async function listToDays()
{
    await $.ajax({
        type: "get",
        url: "/me/booking-meeting/event-todays",
        dataType: "json",
        success: function (response) {
            $('#showToDays').html(response.content)
        }
    });
}

JS;
$this->registerJS($js, View::POS_END);
?>