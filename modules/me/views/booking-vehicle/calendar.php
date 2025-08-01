<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\bootstrap5\Html;
use app\modules\booking\models\Vehicle;
$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

?>
<?php

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
<?=$this->render('menu',['active' => $vehicle_type])?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/me/menu',['active' => 'vehicle']) ?>
<?php $this->endBlock(); ?>



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




<div class="row">
    <div class="col-lg-8 col-md-12 col-sm-12">
        
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

    <div class="card">
        <div class="card-body">
            <div id="calendar-loading" style="display: none; text-align: center; margin-bottom: 10px;">
                <span class="spinner-border text-primary" role="status"></span> กำลังโหลดกิจกรรม...
            </div>
            <div id="calendar"></div>
        </div>
    </div>
</div>
    </div>
    <div class="col-lg-4 col-md-12 col-sm-12">

        <div id="showEventToDays"></div>
        <div id="showEventTomorrow"></div>
    </div>
</div>


<?php
$url = Url::to(['/booking/vehicle/']);
$vehicleType = Json::encode($vehicle_type);
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
                selectMirror: true,
                moreLinkClick: 'popover',
                longPressDelay: 500, // สำหรับอุปกรณ์ touch
                dayMaxEvents: 5, // จำกัดให้แสดงสูงสุด 3 event ต่อวัน
                    moreLinkContent: function(args) {
                        return '+' + args.num + ' more';
                    },
                    loading: function(isLoading) {
                        if (isLoading) {
                            // กำลังโหลดข้อมูลจาก AJAX
                            $('#calendar-loading').show();  // แสดง loading spinner
                        } else {
                            // โหลดเสร็จแล้ว
                            $('#calendar-loading').hide();
                        }
                    },
                events: async function(fetchInfo, successCallback, failureCallback) {
                    await $.ajax({
                        url: '$url'+'/events',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr,
                            vehicle_type:$vehicleType 
                        },
                        success: function(data) {
                            successCallback(data.events);
                           const summary = data.summary_status;
                            if (summary) {
                                $('body').find('.status_summary').text(0)
                                $.each(summary, function (indexInArray, value) { 
                                         $('#status' + value.status).text(value.count);
                                     
                                });
                            }
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
                    const endDateObj = new Date(info.endStr);
                    endDateObj.setDate(endDateObj.getDate() - 1);
                    const dateEnd = endDateObj.toISOString().split('T')[0];
                    beforLoadModal();
                    $.ajax({
                        type: "get",
                        url: '/me/booking-vehicle/create',
                        data: {
                            date_start: dateStart,
                            date_end: dateEnd,
                            title:'<i class="fa-regular fa-file-lines"></i> แบบฟอร์มบันทึกการลา',
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

            listEventTomorrow()
            listEventToDays()
            async function listEventToDays()
            {
                await $.ajax({
                    type: "get",
                    url: "/booking/vehicle/list-event-todays",
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
                    url: "/booking/vehicle/list-event-tomorrow",
                    dataType: "json",
                    success: function (response) {
                        $('#showEventTomorrow').html(response.content)
                    }
                });
            }




    JS;

$this->registerJS($js, View::POS_END);
?>