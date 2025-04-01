<?php
/* @var $this yii\web\View */
/* @var $year int */
/* @var $month int */
/* @var $events array */

use app\components\EventCalendar;

$this->title = 'ปฏิทินกิจกรรม';
$this->params['breadcrumbs'][] = $this->title;
echo "<pre>";
// print_r($events);
echo "</pre>";
?>

<div class="calendar-index">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h1><?= $this->title ?></h1>
                </div>
                <div class="card-body">
                <?= EventCalendar::widget([
                    'year' => $year,
                    'month' => $month,
                    'events' => $events,
                    'calendarUrl' => ['calendar/ajax-load'], // กำหนด URL สำหรับโหลดปฏิทิน
                    'eventUrl' => ['event/ajax-view'],      // กำหนด URL สำหรับดูรายละเอียดอีเวนต์
                ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalLabel">รายละเอียดกิจกรรม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>