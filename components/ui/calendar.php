<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>


<style>
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

$eventUrl = Url::to([$url.'events']);  // Replace with your actual endpoint URL
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
                events: function(fetchInfo, successCallback, failureCallback) {
                    \$.ajax({
                        url: '$eventUrl',
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
                        const room = arg.event.title;
                        const title = arg.event.extendedProps.title || '';
                        const dateTime = arg.event.extendedProps.dateTime || '';
                        const status = arg.event.extendedProps.status || '';

                        // สร้าง custom DOM element
                        const container = document.createElement('div');
                        container.style.textAlign = 'left';
                        // ใช้ innerHTML ได้ตามใจ
                        container.innerHTML = `
                            <div class="mb-0 p-1 d-flex flex-column justify-conten-start gap-1">
                                <span>\${room}</span>
                                <small style="color:#555;">\${dateTime}</small>
                                <span>\${status}</span>
                            </div>
                        `;

                        return { domNodes: [container] };
                    },
                    eventDidMount: function(info) {
                        info.el.addEventListener('dblclick', function() {
                        document.getElementById('modalEventContent').innerHTML =
                            `<strong>Title:</strong> \${info.event.title}<br>
                            <strong>Description:</strong> \${info.event.extendedProps.description}`;
                        $('#main-modal').modal('show');
                        $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                        $(".modal-dialog").addClass('modal-lg');
                        });
                },
                select: function(info) {
                        console.log('Selected from', info.startStr, 'to', info.endStr);

                        const dateStart = info.startStr;
                        const dateEnd = info.endStr;
                                $.ajax({
                                    type: "get",
                                    url: '$url'+'create',
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
                        // หรือถ้าใช้ SweetAlert2:
                        // Swal.fire('Selected!', `From \${info.startStr} to \${info.endStr}`, 'info');
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
                  // ดับเบิลคลิกที่วันที่เพื่อเปิด modal
                            // dateClick: function(info) {
                                
                            // // const dateParts = info.dateStr.split('-');
                            // const dateParts = info.dateStr;
                            //     console.log(dateParts);
                            //     $.ajax({
                            //         type: "get",
                            //         url: '$url'+'create',
                            //         data: {
                            //             date_start: dateParts,
                            //         },
                            //         dataType: "json",
                            //         success: function (res) {
                            //             $("#main-modal").modal("show");
                            //             $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                            //             $(".modal-dialog").addClass("modal-xl");
                            //                 $("#main-modal-label").html(res.title);
                            //                 $(".modal-body").html(res.content);
                            //                 $(".modal-footer").html(res.footer);
                            //         }
                            //     });
                                
                            // },
                eventClick: function(info) {
                        info.jsEvent.preventDefault(); // ป้องกันการเปลี่ยนลิงก์
                        let viewHtml = info.event.extendedProps.view;
                        // กำหนด URL ไปยัง action ที่ใช้แสดงรายละเอียด
                        var url = '$url'+'view?id=' + info.event.id;
                        // โหลดเนื้อหามาแสดงใน Modal
                            \$('#main-modal').modal('show')
                            \$("#main-modal-label").html('รายละเอียดการจอง');
                            \$(".modal-body").html(viewHtml);
                            \$(".modal-body")
                            console.log(JSON.stringify(viewHtml));
                            
                    },
            });
            // render the calendar});

            calendar.render();
        });
    JS;

$this->registerJS($js, View::POS_END);
?>
