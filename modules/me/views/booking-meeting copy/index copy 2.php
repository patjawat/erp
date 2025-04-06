<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\components\UserHelper;

$this->title = 'ระบบขอใช้ห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://unpkg.com/popper.js/dist/umd/popper.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$me = UserHelper::getEmployee();

$dateLast = new DateTime($searchModel->date_start ? $searchModel->date_start : date('Y-m-d'));
$dateNext = new DateTime($searchModel->date_start ? $searchModel->date_start : date('Y-m-d'));
$dateLast->modify('-1 day');
$dateNext->modify('+1 day');

$days = [
    "Sunday" => "อาทิตย์",
    "Monday" => "จันทร์",
    "Tuesday" => "อังคาร",
    "Wednesday" => "พุธ",
    "Thursday" => "พฤหัสบดี",
    "Friday" => "ศุกร์",
    "Saturday" => "เสาร์"
];

// ตรวจสอบว่ามีค่า date_start หรือไม่ ถ้าไม่มีให้ใช้วันที่ปัจจุบัน
$date = $searchModel->date_start ? new DateTime($searchModel->date_start) : new DateTime();

$dayInEnglish = $date->format("l"); // ดึงชื่อวันเป็นภาษาอังกฤษ
$dayInThai = $days[$dayInEnglish]; // แปลงเป็นภาษาไทย


?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-person-chalkboard fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
ระบบห้องประชุม
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>

<style>
/* .fc .fc-toolbar>*> :first-child {
    margin-left: 0;
    font-size: medium;
} */

.fc .fc-button {
    background-color: var(--bs-primary) !important;
    /* ใช้สี primary ของ Bootstrap */
    border-color: var(--bs-primary) !important;
    color: white !important;
}

.fc .fc-button:hover {
    background-color: var(--bs-dark) !important;
    /* เมื่อ hover */
}
</style>
<?php // echo $this->render('list_room') ?>


 <!-- Sidebar -->
 <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="#">📅 Meeting Room Booking</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#">🏠 Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#">📆 ตารางจอง</a></li>
                <li class="nav-item"><a class="nav-link" href="#">🏢 ห้องประชุม</a></li>
                <li class="nav-item"><a class="nav-link" href="#">⚙️ การตั้งค่า</a></li>
            </ul>
        </div>
    </nav>

    <div class="mt-4">
        <!-- Welcome Message -->
        <div class="alert alert-primary text-center">
            👋 ยินดีต้อนรับ! จัดการการจองห้องประชุมของคุณได้ที่นี่
        </div>

        <div class="row">
            <!-- ตารางการจองวันนี้ -->
            <div class="col-md-6">
                <h5>📆 การจองวันนี้</h5>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>09:00 - 10:00 | ห้อง A | คุณสมชาย</span>
                        <span class="badge bg-success">กำลังใช้งาน</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>11:00 - 12:00 | ห้อง B | คุณวราภรณ์</span>
                        <span class="badge bg-secondary">รอเริ่ม</span>
                    </li>
                </ul>
            </div>

            <!-- รายการห้องประชุม -->
            <div class="col-md-6">
                <h5>🏢 สถานะห้องประชุม</h5>
                
                <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <?php echo Html::a('<i class="fa-solid fa-angle-left"></i> วันก่อน', ['/me/booking-meeting/index', 'date_start' => $dateLast->format('Y-m-d')],['class' => 'fs-6 fw-bolder']) ?>
                    <div class="d-flex gap-2 align-self-center">
                        <div>
                            
                            <span class="badge rounded-pill badge-soft-primary text-primary fs-3 p-3">
                                
                                <?php 

                            $dayOnly = date('j', strtotime($searchModel->date_start));
                            echo $dayOnly; // ผลลัพธ์: 04
                        ?>
                            </span>
                        </div>
                        <div class="d-flex flex-column align-self-center">
                          
                            <span class="fw-bolder fs-6">  <?php echo $dayInThai;?></span>
                            <span class="fw-bolder fs-6">
                                <?php
                                $dayM = date('n', strtotime($searchModel->date_start));
                                $month = AppHelper::getMonthName($dayM);
                                echo $month .' '.(date('Y')+543)
                                ?>
                                <!-- กุมภาพันธฺ 2569 -->
                            </span>
                        </div>
                    </div>

                    <!-- <h6><i class="fa-regular fa-calendar-plus"></i>
                        <?php $time = time();
                        echo Yii::$app->thaiFormatter->asDate($searchModel->date_start, 'full') . '<br>'; ?>
                    </h6> -->
                    <?php echo Html::a('วันถัดไป <i class="fa-solid fa-angle-right"></i> ', ['/me/booking-meeting/index', 'date_start' => $dateNext->format('Y-m-d')],['class' => 'fs-6 fw-bolder']) ?>
                </div>
            </div>
        </div>
        <?php echo $this->render('list_room', ['model' => $searchModel]) ?>
            </div>
        </div>

        <!-- ปุ่มจองห้องประชุม -->
        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg">➕ จองห้องประชุม</button>
        </div>
    </div>





<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6><i class="fa-regular fa-calendar-plus"></i> ปฏิทินรวม </h6>
                <div id='calendar'></div>
            </div>
        </div>

    </div>

    <div class="col-12">
        <?php //  $this->render('list_room') ?>
    </div>
</div>


<?php // echo $this->render('grid_room') ?>

<?php
$js = <<<JS



          
    \$(".open-canvas").click(function(e){
                e.preventDefault();
                var offcanvasElement = new bootstrap.Offcanvas(\$("#MyOffcanvas")[0]);
                offcanvasElement.show();
                \$.ajax({
                    type: "get",
                    url: \$(this).attr('href'),
                    dataType: "json",
                    success: function (res) {
                        \$('.offcanvas-body').html(res.content)
                    }
                });
                \$('.offcanvas-title').text('ทดสอบ');
            });

      document.addEventListener('DOMContentLoaded', function(info) {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'th',
                themeSystem: 'bootstrap5', 
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                headerToolbar: {
                    left: 'prev,next,today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,dayGridDay'
                },
                buttonText: {
                    today: 'วันนี้',
                    month: 'เดือน',
                    week: 'สัปดาห์',
                    day: 'วัน'
                },
                eventDidMount: function(info) {
                    var tooltip = new Tooltip(info.el, {
                    title: info.event.extendedProps.description,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                    });
                },

            events: function(fetchInfo, successCallback, failureCallback ) { 
            $.ajax({
                        url: '/me/booking-meeting/events',
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            successCallback(data);
                        },
                        error: function () {
                            // alert('Failed to load events.');
                        }
                    });

                    
                },
                eventClick: function(info) {
                // alert('Event: ' + info.event.title);
                // alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
                // alert('View: ' + info.view.type);
                var title = info.event.title;
                var description = info.event.extendedProps.description;
                var item = info.event.extendedProps.data;
                var view = info.event.extendedProps.view;
                var topic = info.event.extendedProps.topic;
                console.log(info.event.extendedProps.data);
                

                // change the border color just for fun
                info.el.style.borderColor = 'red';
                $("#main-modal").modal("show");
                $("#main-modal-label").html('แสดงรายละเอียดการขอใช้ห้องประชุม');
                $(".modal-body").html(view);
                // $(".modal-footer").html(response.footer);
                $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                $(".modal-dialog").addClass('modal-lg');
                $(".modal-content").addClass("card-outline card-primary");
      
            },
        });

        calendar.render();
      });


    JS;
$this->registerJS($js, View::POS_END);
?>