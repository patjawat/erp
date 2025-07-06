

<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;


$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'ปฏิทินการใช้ห้องประชุม';
$this->params['breadcrumbs'][] = ['label' => 'ระบบห้องประชุม', 'url' => ['/booking/meeting/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-regular fa-calendar fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้ห้องประชุม
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu',['active' => 'calendar'])?>
<?php $this->endBlock(); ?>


<style>
.fc-daygrid-event-harness{
    width: 220px !important;
}
.fc-daygrid-event-harness{
    margin-bottom: 4px;
}
    /* ปรับสีพื้นหลังของ header */
.fc .fc-toolbar-title {
    color: #007bff; /* สีฟ้า */
    font-size: 20px;
}

/* ปรับสีปุ่มใน header */
.fc-button {
    background-color: #28a745; /* สีเขียว */
    color: white;
}

.fc-button:hover {
    background-color: #218838; /* สีเขียวเข้มเมื่อ hover */
}

/* ปรับสีของ event */
.fc-event {
    background-color: #f0f8ff;
    color: black;  /* ตัวอักษรสีดำ */
}

/* ปรับขนาดฟอนต์ในปฏิทิน */
.fc-daygrid-day-number {
    font-size: 14px;
}
/* ปรับขนาด event ให้แสดงเต็มพื้นที่แนวนอน */
.fc-event {
   
    white-space: normal; /* เพื่อให้ข้อความแสดงได้เต็มบรรทัด */
    text-align: center; /* จัดข้อความให้อยู่ตรงกลาง */
    font-size: 14px; /* ปรับขนาดฟอนต์ให้เหมาะสม */
}

</style>
<div class="card">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<?php
$url = Url::to(['/booking/meeting/']);
// $eventUrl = Url::to(['/booking/vehicle/events']);  // Replace with your actual endpoint URL
$js = <<<JS
        \$(document).ready(function() {

            
            var calendarEl = \$('#calendar')[0];
            var containerEl = \$('#external-events')[0];
            var checkbox = \$('#drop-remove')[0];

            // initialize the external events
            // -----------------------------------------------------------------

            if (containerEl) {
                new FullCalendar.Draggable(containerEl, {
                    itemSelector: '.fc-event',
                    eventData: function(eventEl) {
                        return {
                            title: \$(eventEl).text()
                        };
                    }
                });
            }

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'th',
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap5',  // เลือกธีมของ Bootstrap5 หรือใช้ตัวอื่น ๆ
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'วันนี้',           // ปุ่ม "วันนี้"
                    month: 'เดือน',            // ปุ่ม "เดือน"
                    week: 'สัปดาห์',           // ปุ่ม "สัปดาห์"
                    day: 'วัน'                 // ปุ่ม "วัน"
                },
                editable: true,
                selectable: true,
                droppable: true,
                events: async function(fetchInfo, successCallback, failureCallback) {
                    await $.ajax({
                        url: '$url'+'/events',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        },
                        success: function(data) {
                            successCallback(data);
                        },
                        error: function() {
                            failureCallback();
                        }
                    });
                },
                eventDidMount: function(info) {
                            info.el.style.borderLeft = '5px solid red';
                        },
                eventContent: function(arg) {
                        // ดึงข้อมูลจาก extendedProps
                        const title = arg.event.title || '';
                        const dateTime = arg.event.extendedProps.dateTime || '';
                        const status = arg.event.extendedProps.status || '';
                        const viewGoType = arg.event.extendedProps.viewGoType || '';
                        const showDateRange = arg.event.extendedProps.showDateRange || '';;

                        // สร้าง custom DOM element
                        const container = document.createElement('div');
                        container.style.textAlign = 'left';
                        // ใช้ innerHTML ได้ตามใจ
                        container.innerHTML = title;
                        return { domNodes: [container] };
                    },
                    eventDidMount: function(info) {
                        info.el.addEventListener('dblclick', function() {
                        document.getElementById('modalEventContent').innerHTML =
                            `<strong>Title:</strong> \${title}<br>
                            <strong>Description:</strong> \${title}`;
                        $('#main-modal').modal('show');
                        $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                        $(".modal-dialog").addClass('modal-lg');
                        });
                },
                select: function(info) {

                    },
                drop: function(info) {
                    console.log('drop: ' + info.dateStr);
                    if (\$(checkbox).is(':checked')) {
                        \$(info.draggedEl).remove();
                    }
                },
                eventDrop: function(info) {
                    if (info.event.title != 'วัน OFF') {
                        var dateStart = formatDateThai(info.event.start);
                        var dateEnd = formatDateThai(info.event.end);
                        \$('#leave-date_start').val(dateStart);
                        \$('#leave-date_end').val(dateEnd);
                        console.log(dateStart, ' ถึง ' + dateEnd);
                    }
                },
                eventResize: function(info) {
                    console.log('New Start: ' + formatDate(info.event.start));
                    console.log('New End: ' + formatDate(info.event.end));
                },
                  
                eventClick: function(info) {
                        info.jsEvent.preventDefault(); // ป้องกันการเปลี่ยนลิงก์
                        let viewHtml = info.event.extendedProps.view;
                        // กำหนด URL ไปยัง action ที่ใช้แสดงรายละเอียด
                        var url = '$url'+'view?id=' + info.event.id;
                        // โหลดเนื้อหามาแสดงใน Modal
                            \$('#main-modal').modal('show')
                            \$("#main-modal-label").html('รายละเอียดการจอง');
                            \$(".modal-body").html(viewHtml);
                            $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                            $(".modal-dialog").addClass("modal-lg");
                            console.log(JSON.stringify(viewHtml));
                            
                    },
            });
            // render the calendar});

            calendar.render();
        });


  $('body').on('click', '.confirm-meeting', function (e) {
    e.preventDefault();

    var status = $(this).data('status');
    var id = $(this).data('id');
    var text = $(this).data('text');
    Swal.fire({
      title: "ยืนยัน!",
      text:text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'ยกเลิก',
      confirmButtonText: 'ใช่, ยืนยัน!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "post",
          url: '/me/booking-meeting/confirm',
          data: {
            id: id,
            status: status
          },
          dataType: "json",
          success: function (res) {
            if (res.status == 'success') {
              $('.modal').modal('hide');
              Swal.fire({
              icon: 'success',
              title: 'Confirmed!',
              text: res.message || 'ดำเนินการเรียบร้อยแล้ว',
              timer: 1000,
              showConfirmButton: false
              }).then(() => {
              location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: res.message || 'Something went wrong.',
              });
            }
          }
        });
      }
    });
  });
  JS;

$this->registerJS($js, View::POS_END);
?>

