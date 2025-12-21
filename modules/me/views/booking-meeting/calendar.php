<?php

use yii\web\View;


$this->title = 'ระบบจองห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
        <?= $this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?= $this->render('menu', ['active' => 'calendar']) ?>
    <?= $this->render('@app/components/ui/btnReturn')?>
</div>
<?php $this->endBlock(); ?>

<div class="row">
    <div class="col-lg-8 col-md-8 col-sm-12">
        <?= $this->render('@app/modules/booking/views/meeting/calendar_item') ?>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <div id="showToDays"></div>
        <div id="showTomorrow"></div>

    </div>
</div>

<?php
$js = <<< JS

listTomorrow()

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