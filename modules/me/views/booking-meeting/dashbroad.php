<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\components\UserHelper;

$this->title = 'ระบบขอใช้ห้องประชุม';
$this->params['breadcrumbs'][] = $this->title;

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





<style>
/* .card {
      border-radius: 15px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    } */
.success-text {
    color: #28a745;
}

.warning-text {
    color: #ffc107;
}

.btn-reserve {
    background-color: #212529;
    color: white;
    border-radius: 8px;
    padding: 6px 15px;
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.05);
}
</style>
</head>

<body>
    <div class="container">
        <?=$this->render('navbar')?>


        <p class="text-muted mb-4">ยินดีต้อนรับกลับมา, คุณสามารถจัดการการจองห้องประชุมได้ที่นี่</p>

        <?=$this->render('@app/modules/booking/views/meeting/summary', ['searchModel' => $searchModel])?>

        <!-- Upcoming Bookings and Available Rooms -->
        <div class="row">
            <!-- Upcoming Bookings -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">การจองที่กำลังจะถึง</h5>
                        <p class="text-muted">รายการจองห้องประชุมที่กำลังจะถึงใน 7 วันข้างหน้า</p>
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-semibold mb-0"><?=$item->room->title?></h6>
                                <?=$item->viewStatus();?>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-calendar me-2"></i>
                                <span><?=$item->viewMeetingDate()?></span>
                            </div>
                            <div class="d-flex align-items-center text-muted mt-1">
                                <i class="bi bi-clock me-2"></i>
                                <span><?=$item->viewMeetingTime()?></span>
                            </div>
                        </div>
                    <?php endforeach;?>
                    </div>
                </div>

                <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
        </div>
        
            </div>

            <!-- Available Rooms -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-semibold mb-3">ห้องประชุมที่ว่าง</h5>
                            <div class="d-flex gap-2 align-self-center">
                                <?php // echo Html::a('<i class="fa-solid fa-angle-left"></i> วันก่อน', ['/me/booking-meeting/index', 'date_start' => $dateLast->format('Y-m-d')],['class' => 'fs-6 fw-semibolder']) ?>
                                <?php // echo Html::a('วันถัดไป <i class="fa-solid fa-angle-right"></i> ', ['/me/booking-meeting/index', 'date_start' => $dateNext->format('Y-m-d')],['class' => 'fs-6 fw-semibolder']) ?>

                            </div>

                        </div>
                        <p class="text-muted">ห้องประชุมที่ว่างในวันนี้</p>
                        <?php echo $this->render('list_room', ['model' => $searchModel]) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center align-items-center">
        <?=Html::a('จองห้องประชุม',['/me/booking-meeting/create','date_start' => $searchModel->date_start,'title' => '<i class="fa-solid fa-calendar-plus"></i> ขอให้ห้องประชุม'],['class' => 'btn btn-primary shadow rounded-pill open-modal','data' => ['size' => 'modal-xl']])?>
    </div>



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