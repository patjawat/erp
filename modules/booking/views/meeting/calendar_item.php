<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
use app\models\Categorise;

$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>

<style>
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    /* #calendar.fullscreen { */
    #fullscreen-container.fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 1050 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: white !important;
        padding: 20px !important;
        overflow: auto !important;
    }

    #calendar {
        touch-action: manipulation;
    }
</style>

<div class="card" id="fullscreen-container">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>ปฏิทินการขอใช้รถยนต์</span>
        <div class="d-flex align-items-center gap-2">
            <div class="">
                <label for="eventLimitSelector" class="form-label">การแสดงผล:</label>
                <select id="eventLimitSelector" class="form-select" style="width: auto; display: inline-block;">
                    <option value="2">2 รายการ</option>
                    <option value="3" selected>3 รายการ</option>
                    <option value="5">5 รายการ</option>
                    <option value="all">แสดงทั้งหมด</option>
                </select>
            </div>

            <button class="btn btn-sm btn-light" id="resizeCalendar">
                <i class="fa-solid fa-expand me-2 fs-6"></i> ขยายเต็มจอ</button>
        </div>
    </div>

    <div class="card-body">
        <div id="calendar-loading" style="display: none; text-align: center; margin-bottom: 10px;">
            <span class="spinner-border text-primary" role="status"></span> กำลังโหลดกิจกรรม...
        </div>
        <div id="calendar"></div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-center mt-3">
            <ul class="d-flex  felx-column gap-5">
                <?php foreach (Categorise::find()->where(['name' => 'meeting_status'])->all() as $statusItem): ?>
                    <li class="d-flex gap- align-items-center">
                        <span class="badge rounded-pill me-1" style="background-color:<?= isset($statusItem->data_json['color']) ? $statusItem->data_json['color'] : '' ?>">&nbsp;</span>
                        <?= $statusItem->title ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>



<?php
$url = Url::to(['/booking/meeting/']);
// $eventUrl = Url::to(['/booking/vehicle/events']);  // Replace with your actual endpoint URL
$js = <<<JS

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
                            successCallback(data.events);
                        },
                        error: function() {
                            failureCallback();
                        }
                    });
                },
                eventDidMount: function(info) {
                    if (info.event.extendedProps.color) {
                        info.el.style.backgroundColor = info.event.extendedProps.color;
                    }
                    },
                eventContent: function(arg) {
                        // ดึงข้อมูลจาก extendedProps
                        const title = arg.event.extendedProps.title || '';
                        // สร้าง custom DOM element
                        const container = document.createElement('div');
                        container.style.textAlign = 'left';
                        // ใช้ innerHTML ได้ตามใจ
                        container.innerHTML = `<div class="mb-0 p-1 d-flex flex-column justify-conten-start gap-1">\${title}</div>`;
                        return { domNodes: [container] };
                    },

                select: function(info) {

                        const dateStart = info.startStr;
                        // แปลง dateEnd เป็น Date แล้วลบ 1 วัน
                            const endDateObj = new Date(info.endStr);
                            endDateObj.setDate(endDateObj.getDate() - 1);
                            
                            // แปลงกลับเป็นรูปแบบ YYYY-MM-DD
                            const dateEnd = endDateObj.toISOString().split('T')[0];
                            beforLoadModal();
                                $.ajax({
                                    type: "get",
                                    url: '/me/booking-meeting/create',
                                    data: {
                                        date_start: dateStart,
                                        date_end: dateEnd,
                                    },
                                    dataType: "json",
                                    success: function (res) {
                                        $("#main-modal").modal("show");
                                        $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                                        $(".modal-dialog").addClass("modal-xl");
                                            $("#main-modal-label").html(res.title);
                                            $(".modal-body").html(res.content);
                                            $(".modal-footer").html(res.footer);
                                    }
                                });
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
                       var code = info.event.extendedProps.code || '';
                        var url = 'view?id=' + info.event.id;
                            $.ajax({
                                type: "get",
                                url: url,
                                dataType: "json",
                                success: function (res) {
                                      \$('#main-modal').modal('show')
                                        \$("#main-modal-label").html(res.title);
                                        \$(".modal-body").html(res.content);
                                        $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                                        $(".modal-dialog").addClass("modal-lg");
                                }
                            });             
                    },
            });

            calendar.render();

                        
            $('#eventLimitSelector').on('change', function() {
                const value = $(this).val();
                if (value === 'all') {
                    calendar.setOption('dayMaxEvents', false); // แสดงทั้งหมด
                } else {
                    calendar.setOption('dayMaxEvents', parseInt(value));
                }
            });

            $('#resizeCalendar').click(function (e) { 
                e.preventDefault();
                $('#fullscreen-container').toggleClass('fullscreen');
                calendar.updateSize();

                // ตรวจสอบว่าตอนนี้อยู่ในโหมด fullscreen หรือไม่
                if ($('#fullscreen-container').hasClass('fullscreen')) {
                    $('#resizeCalendar').html('<i class="fa-solid fa-compress me-2 fs-6"></i> ย่อลง');
                } else {
                    $('#resizeCalendar').html('<i class="fa-solid fa-expand me-2 fs-6"></i> ขยายเต็มจอ');
                }
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