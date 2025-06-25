<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;

$listLeaveType = Categorise::find()->where(['name' => 'leave_type'])->all();
$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'ปฏิทินการลา';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้รถยนต์
<?php $this->endBlock(); ?>



<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/me/menu',['active' => 'vehicle']) ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('action'); ?>
<?=$this->render('@app/modules/me/views/leave/_sub_menu',['active' => 'calendar'])?>
<?php $this->endBlock(); ?>




<style>
.fc-theme-standard .fc-scrollgrid {
    border-radius: 10px;
}

.fc .fc-toolbar-title {
    font-size: 1.5rem;
    font-weight: 500;
    color: var(--dark-color);
}

.fc .fc-button-primary {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    font-weight: 500;
    border-radius: 8px;
    padding: 8px 16px;
    transition: all 0.3s ease;
}

.fc .fc-button-primary:hover {
    background-color: var(--bs-secondary);
    border-color: var(--bs-secondary);
}

.fc-day-today {
    background-color: rgba(67, 97, 238, 0.1) !important;

}

.fc-event {
    border-radius: 6px;
    padding: 3px;
    margin-bottom: 2px;
    border: none;
}

.leave-item {
    display: flex;
    align-items: center;
    padding: 2px 4px;
    border-radius: 4px;
    margin-bottom: 2px;
    font-size: 0.8rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.leave-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    margin-right: 5px;
    object-fit: cover;
    border: 1px solid #fff;
    flex-shrink: 0;
}




.detail-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.detail-info {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 10px;
    margin-top: 15px;
}

.leave-count-badge {
    position: absolute;
    top: 0;
    right: 0;
    background-color: var(--primary-color);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    z-index: 5;
}

.day-leave-list {
    max-height: 400px;
    overflow-y: auto;
}



@media (max-width: 768px) {
    .fc .fc-toolbar {
        flex-direction: column;
        gap: 10px;
    }

    .fc-header-toolbar {
        margin-bottom: 1.5em !important;
    }

    .leave-item {
        font-size: 0.7rem;
    }

    .leave-avatar {
        width: 20px;
        height: 20px;
    }
}

.leave-badge {
    font-size: 0.7rem;
    padding: 2px 5px;
    border-radius: 4px;
    color: white;
    margin-right: 4px;
    white-space: nowrap;
}

.sick-leave {
    background-color: var(--bs-primary);
}

.vacation-leave {
    background-color: var(--bs-secondary);
}

.personal-leave {
    background-color: var(--bs-info);
}

.training-leave {
    background-color: var(--leave-training);
}

.maternity-leave {
    background-color: var(--leave-maternity);
}

.other-leave {
    background-color: var(--leave-other);
}

.status-pending {
    border-left: 4px solid #f59e0b !important;
}

.status-approved {
    border-left: 4px solid #10b981 !important;
}

.status-rejected {
    border-left: 4px solid #ef4444 !important;
}


.leave-detail-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    border: 2px solid white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.leave-type-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

.status-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 500;
}

.status-badge-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-badge-approved {
    background-color: #d1fae5;
    color: #065f46;
}

.status-badge-rejected {
    background-color: #fee2e2;
    color: #b91c1c;
}

.leave-request-form label {
    font-weight: 500;
    margin-bottom: 4px;
}

.tooltip-inner {
    max-width: 300px;
}

</style>

<div class="row">
    <div class="col-9" id="calender-container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>ปฏิทินการลาของบุคลากร ประจำปี 2025</span>
                <div class="d-flex align-items-center gap-2">
                    <div class="">
                        <label for="eventLimitSelector" class="form-label">จำนวนรายการที่แสดงต่อวัน:</label>
                        <select id="eventLimitSelector" class="form-select" style="width: auto; display: inline-block;">
                            <option value="2">2 รายการ</option>
                            <option value="3" selected>3 รายการ</option>
                            <option value="5">5 รายการ</option>
                            <option value="all">แสดงทั้งหมด</option>
                        </select>
                    </div>

                    <button class="btn btn-sm btn-light" id="filterBtn">
                        <i class="fas fa-filter"></i> กรองข้อมูล
                    </button>
                    <button class="btn btn-sm btn-light" id="leave-manual"><i class="fa-solid fa-book"></i>
                        แสดงคู่มือ</button>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar"></div>

                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color leave-sick"></div>
                        <span>ลาป่วย</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color leave-vacation"></div>
                        <span>ลาพักผ่อน</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color leave-personal"></div>
                        <span>ลากิจ</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color leave-maternity"></div>
                        <span>ลาคลอด</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color leave-training"></div>
                        <span>ลาฝึกอบรม</span>
                    </div>
                </div>

                <div class="leave-summary">
                    <h5 class="leave-summary-title">สรุปการลาวันนี้</h5>
                    <div id="todayLeaveCount"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3" id="manual-container">
        <div class="card shadow-sm mb-4 guide-card">
            <div class="card-header bg-primary-gradient">
                <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> คู่มือการใช้งาน</h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">วิธีการใช้งานปฏิทินการลา</h6>
                <ol class="ps-3">
                    <li class="mb-2">คลิกที่ปุ่ม "ขอลา" เพื่อสร้างคำขอลาใหม่</li>
                    <li class="mb-2">เลือกวันที่ต้องการลาในแบบฟอร์ม</li>
                    <li class="mb-2">กรอกรายละเอียดการลาให้ครบถ้วน</li>
                    <li class="mb-2">คลิกที่รายการลาในปฏิทินเพื่อดูรายละเอียด</li>
                </ol>

            </div>
        </div>
        <div class="card">
            <div class="card-header  bg-primary-gradient">
                <div class="d-flex justify-content-between align-items-center align-self-center">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> ประเภทการลา</h5>
                    <?=html::a('<i class="fa-solid fa-gear"></i>',['/hr/leave-type/index'],['class'=> 'btn btn-sm btn-light open-modal','data' => ['size' => 'modal-lg']])?>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">
                    <?php foreach($listLeaveType as $leaveType):?>
                    <div><span class="leave-type-indicator <?=$leaveType->code?>"
                            style="background-color:<?=$leaveType->data_json['color'] ?? 'var(--bs-primary)'?>"></span><?=$leaveType->title?>
                    </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>

        <div class="card text-start">
            <div class="card-header bg-primary-gradient">
                <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> สถานะการลา</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center">
                        <div style="width:4px; height:16px; background-color:#f59e0b; margin-right:8px;"></div>
                        รออนุมัติ
                    </div>
                    <div class="d-flex align-items-center">
                        <div style="width:4px; height:16px; background-color:#10b981; margin-right:8px;"></div>
                        อนุมัติแล้ว
                    </div>
                    <div class="d-flex align-items-center">
                        <div style="width:4px; height:16px; background-color:#ef4444; margin-right:8px;"></div>
                        ไม่อนุมัติ
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>


<!-- https://www.canva.com/ai/code/thread/9dc074ce-cf81-420d-9c78-bc0fa333e10e -->
<?php
$url = Url::to(['/me/leave/']);
// $eventUrl = Url::to(['/booking/vehicle/events']);  // Replace with your actual endpoint URL
$js = <<<JS



        $(document).ready(function() {
            // อ่านสถานะ manualVisible จาก localStorage ถ้ามี
            let manualVisible = localStorage.getItem('leaveManualVisible') !== 'false';
            if (!manualVisible) {
                $('#manual-container').hide();
                $('#calender-container').removeClass('col-9').addClass('col-12');
            }

            $('#leave-manual').click(function (e) { 
                e.preventDefault();
                manualVisible = !manualVisible;
                localStorage.setItem('leaveManualVisible', manualVisible);
                if (manualVisible) {
                    $('#manual-container').fadeIn(200, function() {
                        $('#calender-container').removeClass('col-12').addClass('col-9');
                        calendar.updateSize();
                    });
                } else {
                    $('#manual-container').fadeOut(200, function() {
                        $('#calender-container').removeClass('col-9').addClass('col-12');
                        calendar.updateSize();
                    });
                }
            });

            $('#eventLimitSelector').on('change', function() {
                const value = $(this).val();
                if (value === 'all') {
                    calendar.setOption('dayMaxEvents', false); // แสดงทั้งหมด
                } else {
                    calendar.setOption('dayMaxEvents', parseInt(value));
                }
            });

            var calendarEl = $('#calendar')[0];
            var containerEl = $('#external-events')[0];
            var checkbox = $('#drop-remove')[0];
            
            // สร้างตัวแปรสำหรับ loading indicator
            var \$loadingIndicator = $('<div id="calendar-loading" style="position:absolute;top:50%;left:40%;transform:translate(-50%,-50%);z-index:9999;display:none;"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-primary">กำลังโหลด...</div></div>');
            $('body').append(\$loadingIndicator);
            
            // initialize the external events
            if (containerEl) {
                new FullCalendar.Draggable(containerEl, {
                    itemSelector: '.fc-event',
                    eventData: function(eventEl) {
                        return {
                            title: $(eventEl).text()
                        };
                    }
                });
            }
            
            let initialDayMaxEvents = 3;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'th',
                initialView: 'dayGridMonth',
                themeSystem: 'bootstrap5',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'วันนี้',
                    month: 'เดือน',
                    week: 'สัปดาห์',
                    day: 'วัน'
                },
                editable: true,
                selectable: true,
                droppable: true,
               dayMaxEvents: initialDayMaxEvents,
                moreLinkText: function(n) {
                    return `+อีก \${n} รายการ`;
                },
                events: async function(fetchInfo, successCallback, failureCallback) {
                    $('#calendar-loading').show();
                    await $.ajax({
                        url: '$url/events',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        },
                        success: function(data) {
                            $('#calendar-loading').hide();
                            successCallback(data);
                        },
                        error: function() {
                            $('#calendar-loading').hide();
                            failureCallback();
                        }
                    });
                },
                eventDidMount: function(info) {
                    // Add tooltip
                    const title = getStaffById(info.event.extendedProps.title);
                    $(info.el).attr('data-bs-toggle', 'tooltip');
                    $(info.el).attr('data-bs-title', `
                        \${title} - \${leaveTypeTranslations[leaveType]}
                        // วันที่: \${formatThaiDate(info.event.start)} - \${formatThaiDate(new Date(info.event.end - 86400000))}
                        // สถานะ: \${statusTranslations[status]}
                    `);
                },
                eventContent: function(arg) {
                    const title = arg.event.extendedProps.title || '';
                    const avatar = arg.event.extendedProps.avatar || '';
                    const dateTime = arg.event.extendedProps.dateTime || '';
                    const status = arg.event.extendedProps.status || '';
                    const viewGoType = arg.event.extendedProps.viewGoType || '';
                    const showDateRange = arg.event.extendedProps.showDateRange || '';

                    const container = document.createElement('div');
                    container.style.textAlign = 'left';
                    container.innerHTML = `<div class="mb-0 px-2 d-flex flex-column justify-conten-start gap-1">\${avatar}</div>`;

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
                    const dateStart = info.startStr;
                    const endDateObj = new Date(info.endStr);
                    endDateObj.setDate(endDateObj.getDate() - 1);
                    const dateEnd = endDateObj.toISOString().split('T')[0];
                    beforLoadModal();
                    $.ajax({
                        type: "get",
                        url: '$url'+'/create',
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
                    if ($(checkbox).is(':checked')) {
                        $(info.draggedEl).remove();
                    }
                },
                eventDrop: function(info) {
                    if (info.event.title != 'วัน OFF') {
                        var dateStart = formatDateThai(info.event.start);
                        var dateEnd = formatDateThai(info.event.end);
                        $('#leave-date_start').val(dateStart);
                        $('#leave-date_end').val(dateEnd);
                        console.log(dateStart, ' ถึง ' + dateEnd);
                    }
                },
                eventResize: function(info) {
                    console.log('New Start: ' + formatDate(info.event.start));
                    console.log('New End: ' + formatDate(info.event.end));
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    let viewHtml = info.event.extendedProps.view;
                    let avatar = info.event.extendedProps.avatar;
                    var url = '$url/'+'view?id=' + info.event.id;
                    beforLoadModal()
                    $.ajax({
                        type: "get",
                        url: "$url"+"/view?id="+ info.event.id,
                        dataType: "json",
                        boforeSubmit: function(){},
                        success: function (response) {
                            $('#main-modal').modal('show')
                            $("#main-modal-label").html(response.title);
                            $(".modal-body").html(response.content);
                            $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                            $(".modal-dialog").addClass("modal-xl");
                        }
                    });             
                },
                loading: function(isLoading) {
                    if(isLoading) {
                        $('#calendar-loading').show();
                    } else {
                        $('#calendar-loading').hide();
                    }
                }
            });
            calendar.render();
        });

    // document.addEventListener('DOMContentLoaded', function() {
    //         // ข้อมูลพนักงาน
    //         const employees = [
    //             { id: 1, name: 'สมชาย ใจดี', avatar: createAvatar('สช', '#4361ee'), department: 'IT' },
    //             { id: 2, name: 'สมหญิง รักเรียน', avatar: createAvatar('สญ', '#f72585'), department: 'HR' },
    //             { id: 3, name: 'วิชัย สุขสันต์', avatar: createAvatar('วช', '#4cc9f0'), department: 'Finance' },
    //             { id: 4, name: 'นภา ดาวเด่น', avatar: createAvatar('นภ', '#7209b7'), department: 'Marketing' },
    //             { id: 5, name: 'ประสิทธิ์ มั่นคง', avatar: createAvatar('ปส', '#4895ef'), department: 'Operations' },
    //             { id: 6, name: 'กมลา พรหมเทพ', avatar: createAvatar('กม', '#560bad'), department: 'IT' },
    //             { id: 7, name: 'ธนา รุ่งเรือง', avatar: createAvatar('ธน', '#3a0ca3'), department: 'HR' },
    //             { id: 8, name: 'พิมพ์ใจ งามพริ้ง', avatar: createAvatar('พจ', '#f72585'), department: 'Finance' },
    //             { id: 9, name: 'อนันต์ วิเศษ', avatar: createAvatar('อน', '#4361ee'), department: 'Marketing' },
    //             { id: 10, name: 'มณีรัตน์ แก้วใส', avatar: createAvatar('มร', '#7209b7'), department: 'Operations' }
    //         ];
            
    //         // ข้อมูลการลา
    //         const leaveData = [
    //             {
    //                 id: 1,
    //                 employeeId: 1,
    //                 title: 'ลาป่วย',
    //                 start: '2025-01-05',
    //                 end: '2025-01-07',
    //                 type: 'sick',
    //                 reason: 'ไข้หวัดใหญ่',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 2,
    //                 employeeId: 2,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-01-10',
    //                 end: '2025-01-15',
    //                 type: 'vacation',
    //                 reason: 'ท่องเที่ยวต่างจังหวัด',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 3,
    //                 employeeId: 3,
    //                 title: 'ลากิจ',
    //                 start: '2025-01-20',
    //                 end: '2025-01-21',
    //                 type: 'personal',
    //                 reason: 'ธุระส่วนตัว',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 4,
    //                 employeeId: 4,
    //                 title: 'ลาคลอด',
    //                 start: '2025-02-01',
    //                 end: '2025-04-30',
    //                 type: 'maternity',
    //                 reason: 'คลอดบุตร',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-maternity'
    //             },
    //             {
    //                 id: 5,
    //                 employeeId: 5,
    //                 title: 'ลาฝึกอบรม',
    //                 start: '2025-02-15',
    //                 end: '2025-02-18',
    //                 type: 'training',
    //                 reason: 'อบรมหลักสูตรการบริหารจัดการ',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-training'
    //             },
    //             {
    //                 id: 6,
    //                 employeeId: 6,
    //                 title: 'ลาป่วย',
    //                 start: '2025-03-05',
    //                 end: '2025-03-08',
    //                 type: 'sick',
    //                 reason: 'ผ่าตัดไส้ติ่ง',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 7,
    //                 employeeId: 7,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-03-20',
    //                 end: '2025-03-25',
    //                 type: 'vacation',
    //                 reason: 'พักผ่อนประจำปี',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 8,
    //                 employeeId: 8,
    //                 title: 'ลากิจ',
    //                 start: '2025-04-10',
    //                 end: '2025-04-12',
    //                 type: 'personal',
    //                 reason: 'งานแต่งงานญาติ',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 9,
    //                 employeeId: 9,
    //                 title: 'ลาฝึกอบรม',
    //                 start: '2025-04-20',
    //                 end: '2025-04-22',
    //                 type: 'training',
    //                 reason: 'อบรมการตลาดดิจิทัล',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-training'
    //             },
    //             {
    //                 id: 10,
    //                 employeeId: 10,
    //                 title: 'ลาป่วย',
    //                 start: '2025-05-05',
    //                 end: '2025-05-06',
    //                 type: 'sick',
    //                 reason: 'ปวดฟัน',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 11,
    //                 employeeId: 1,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-05-15',
    //                 end: '2025-05-20',
    //                 type: 'vacation',
    //                 reason: 'ท่องเที่ยวต่างประเทศ',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 12,
    //                 employeeId: 2,
    //                 title: 'ลากิจ',
    //                 start: '2025-06-01',
    //                 end: '2025-06-02',
    //                 type: 'personal',
    //                 reason: 'ธุระครอบครัว',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 13,
    //                 employeeId: 3,
    //                 title: 'ลาฝึกอบรม',
    //                 start: '2025-06-15',
    //                 end: '2025-06-18',
    //                 type: 'training',
    //                 reason: 'อบรมระบบบัญชีใหม่',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-training'
    //             },
    //             {
    //                 id: 14,
    //                 employeeId: 5,
    //                 title: 'ลาป่วย',
    //                 start: '2025-07-05',
    //                 end: '2025-07-10',
    //                 type: 'sick',
    //                 reason: 'ไข้เลือดออก',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 15,
    //                 employeeId: 6,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-07-20',
    //                 end: '2025-07-25',
    //                 type: 'vacation',
    //                 reason: 'พักผ่อนประจำปี',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 16,
    //                 employeeId: 7,
    //                 title: 'ลากิจ',
    //                 start: '2025-08-01',
    //                 end: '2025-08-03',
    //                 type: 'personal',
    //                 reason: 'ย้ายบ้าน',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 17,
    //                 employeeId: 8,
    //                 title: 'ลาฝึกอบรม',
    //                 start: '2025-08-15',
    //                 end: '2025-08-18',
    //                 type: 'training',
    //                 reason: 'อบรมภาษีใหม่',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-training'
    //             },
    //             {
    //                 id: 18,
    //                 employeeId: 9,
    //                 title: 'ลาป่วย',
    //                 start: '2025-09-05',
    //                 end: '2025-09-07',
    //                 type: 'sick',
    //                 reason: 'ท้องเสีย',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 19,
    //                 employeeId: 10,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-09-20',
    //                 end: '2025-09-25',
    //                 type: 'vacation',
    //                 reason: 'พักผ่อนประจำปี',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 20,
    //                 employeeId: 1,
    //                 title: 'ลากิจ',
    //                 start: '2025-10-01',
    //                 end: '2025-10-02',
    //                 type: 'personal',
    //                 reason: 'ธุระส่วนตัว',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 21,
    //                 employeeId: 2,
    //                 title: 'ลาฝึกอบรม',
    //                 start: '2025-10-15',
    //                 end: '2025-10-18',
    //                 type: 'training',
    //                 reason: 'อบรมการบริหารบุคคล',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-training'
    //             },
    //             {
    //                 id: 22,
    //                 employeeId: 3,
    //                 title: 'ลาป่วย',
    //                 start: '2025-11-05',
    //                 end: '2025-11-08',
    //                 type: 'sick',
    //                 reason: 'ไข้หวัดใหญ่',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 23,
    //                 employeeId: 4,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-11-20',
    //                 end: '2025-11-25',
    //                 type: 'vacation',
    //                 reason: 'พักผ่อนประจำปี',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 24,
    //                 employeeId: 5,
    //                 title: 'ลากิจ',
    //                 start: '2025-12-01',
    //                 end: '2025-12-03',
    //                 type: 'personal',
    //                 reason: 'งานแต่งงาน',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 25,
    //                 employeeId: 6,
    //                 title: 'ลาฝึกอบรม',
    //                 start: '2025-12-15',
    //                 end: '2025-12-18',
    //                 type: 'training',
    //                 reason: 'อบรมเทคโนโลยีใหม่',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-training'
    //             },
    //             // เพิ่มข้อมูลการลาในช่วงเวลาเดียวกัน
    //             {
    //                 id: 26,
    //                 employeeId: 7,
    //                 title: 'ลาป่วย',
    //                 start: '2025-01-05',
    //                 end: '2025-01-06',
    //                 type: 'sick',
    //                 reason: 'ไข้หวัด',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 27,
    //                 employeeId: 8,
    //                 title: 'ลาป่วย',
    //                 start: '2025-01-05',
    //                 end: '2025-01-08',
    //                 type: 'sick',
    //                 reason: 'ไข้หวัดใหญ่',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 28,
    //                 employeeId: 9,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-01-10',
    //                 end: '2025-01-12',
    //                 type: 'vacation',
    //                 reason: 'ท่องเที่ยว',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 29,
    //                 employeeId: 10,
    //                 title: 'ลาพักผ่อน',
    //                 start: '2025-02-15',
    //                 end: '2025-02-20',
    //                 type: 'vacation',
    //                 reason: 'พักผ่อนประจำปี',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-vacation'
    //             },
    //             {
    //                 id: 30,
    //                 employeeId: 1,
    //                 title: 'ลาฝึกอบรม',
    //                 start: '2025-02-15',
    //                 end: '2025-02-17',
    //                 type: 'training',
    //                 reason: 'อบรมเทคโนโลยีใหม่',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-training'
    //             },
    //             {
    //                 id: 31,
    //                 employeeId: 2,
    //                 title: 'ลากิจ',
    //                 start: '2025-03-05',
    //                 end: '2025-03-06',
    //                 type: 'personal',
    //                 reason: 'ธุระส่วนตัว',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 32,
    //                 employeeId: 3,
    //                 title: 'ลากิจ',
    //                 start: '2025-03-05',
    //                 end: '2025-03-07',
    //                 type: 'personal',
    //                 reason: 'ธุระครอบครัว',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             {
    //                 id: 33,
    //                 employeeId: 4,
    //                 title: 'ลากิจ',
    //                 start: '2025-03-05',
    //                 end: '2025-03-05',
    //                 type: 'personal',
    //                 reason: 'ธุระส่วนตัว',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-personal'
    //             },
    //             // เพิ่มข้อมูลการลาในวันเดียวกันมากกว่า 3 คน
    //             {
    //                 id: 34,
    //                 employeeId: 5,
    //                 title: 'ลาป่วย',
    //                 start: '2025-03-05',
    //                 end: '2025-03-05',
    //                 type: 'sick',
    //                 reason: 'ไข้หวัด',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             },
    //             {
    //                 id: 35,
    //                 employeeId: 6,
    //                 title: 'ลาป่วย',
    //                 start: '2025-03-05',
    //                 end: '2025-03-06',
    //                 type: 'sick',
    //                 reason: 'ปวดท้อง',
    //                 status: 'อนุมัติ',
    //                 className: 'leave-sick'
    //             }
    //         ];
            
    //         // เพิ่มข้อมูลพนักงานลงในข้อมูลการลา
    //         const events = leaveData.map(leave => {
    //             const employee = employees.find(emp => emp.id === leave.employeeId);
    //             return {
    //                 ...leave,
    //                 employee: employee,
    //                 extendedProps: {
    //                     employeeName: employee.name,
    //                     employeeAvatar: employee.avatar,
    //                     department: employee.department,
    //                     leaveType: leave.type,
    //                     reason: leave.reason,
    //                     status: leave.status
    //                 }
    //             };
    //         });
            
    //         // กรองข้อมูล
    //         let filteredEvents = [...events];
    //         let activeFilters = {
    //             types: ['sick', 'vacation', 'personal', 'maternity', 'training'],
    //             departments: ['IT', 'HR', 'Finance', 'Marketing', 'Operations']
    //         };
            
    //         // สร้าง Calendar
    //         const calendarEl = document.getElementById('calendar');
    //         const calendar = new FullCalendar.Calendar(calendarEl, {
    //             initialView: 'dayGridMonth',
    //             initialDate: '2025-01-01',
    //             headerToolbar: {
    //                 left: 'prev,next today',
    //                 center: 'title',
    //                 right: 'dayGridMonth,timeGridWeek,listMonth'
    //             },
    //             locale: 'th',
    //             buttonText: {
    //                 today: 'วันนี้',
    //                 month: 'เดือน',
    //                 week: 'สัปดาห์',
    //                 list: 'รายการ'
    //             },
    //             firstDay: 0,
    //             height: 'auto',
    //             events: filteredEvents,
    //             eventContent: function(arg) {
    //                 return null; // ไม่แสดงข้อมูลการลาที่นี่ เพราะเราจะจัดการเองใน dayCellDidMount
    //             },
    //             eventClick: function(info) {
    //                 const event = info.event;
    //                 const employee = event.extendedProps;
    //                 const startDate = new Date(event.start);
    //                 const endDate = new Date(event.end || event.start);
    //                 const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                    
    //                 // แปลงประเภทการลาเป็นภาษาไทย
    //                 let leaveTypeText = '';
    //                 switch(employee.leaveType) {
    //                     case 'sick': leaveTypeText = 'ลาป่วย'; break;
    //                     case 'vacation': leaveTypeText = 'ลาพักผ่อน'; break;
    //                     case 'personal': leaveTypeText = 'ลากิจ'; break;
    //                     case 'maternity': leaveTypeText = 'ลาคลอด'; break;
    //                     case 'training': leaveTypeText = 'ลาฝึกอบรม'; break;
    //                 }
                    
    //                 // แสดง Modal รายละเอียดการลา
    //                 $('#detailAvatar').attr('src', employee.employeeAvatar);
    //                 $('#detailName').text(employee.employeeName);
    //                 $('#detailDepartment').text('แผนก' + getDepartmentName(employee.department));
    //                 $('#detailType').text(leaveTypeText);
    //                 $('#detailStart').text(formatThaiDate(startDate));
    //                 $('#detailEnd').text(formatThaiDate(endDate));
    //                 $('#detailDays').text(days + ' วัน');
    //                 $('#detailReason').text(employee.reason);
    //                 $('#detailStatus').html(`<span class="badge bg-success">\${employee.status}</span>`);
                    
    //                 const leaveDetailModal = new bootstrap.Modal(document.getElementById('leaveDetailModal'));
    //                 leaveDetailModal.show();
    //             },
    //             dayCellDidMount: function(info) {
    //                 // ตรวจสอบว่ามีการลาในวันนี้หรือไม่
    //                 const date = info.date;
    //                 const dateStr = formatYMD(date);
                    
    //                 // หาการลาในวันนี้
    //                 const leavesOnThisDay = filteredEvents.filter(event => {
    //                     const eventStart = new Date(event.start);
    //                     const eventEnd = new Date(event.end || event.start);
    //                     return date >= eventStart && date <= eventEnd;
    //                 });
                    
    //                 // ถ้ามีการลา ให้แสดงข้อมูลการลา
    //                 if (leavesOnThisDay.length > 0) {
    //                     // สร้าง badge แสดงจำนวนการลา
    //                     const badge = document.createElement('div');
    //                     badge.className = 'leave-count-badge';
    //                     badge.textContent = leavesOnThisDay.length;
    //                     info.el.appendChild(badge);
                        
    //                     // สร้าง container สำหรับแสดงข้อมูลการลา
    //                     const leaveContainer = document.createElement('div');
    //                     leaveContainer.style.position = 'relative';
    //                     leaveContainer.style.marginTop = '15px';
                        
    //                     // แสดงข้อมูลการลาไม่เกิน 3 รายการ
    //                     const maxDisplay = 3;
    //                     const displayCount = Math.min(leavesOnThisDay.length, maxDisplay);
                        
    //                     for (let i = 0; i < displayCount; i++) {
    //                         const leave = leavesOnThisDay[i];
    //                         const employee = leave.employee;
                            
    //                         const leaveItem = document.createElement('div');
    //                         leaveItem.className = `leave-item open-modal \${leave.className}`;
    //                         leaveItem.innerHTML = `
    //                             <img src="\${employee.avatar}" class="leave-avatar" alt="\${employee.name}">
    //                             <div>
    //                                 <div>\${employee.name}</div>
    //                                 <div>\${leave.title}</div>
    //                             </div>
    //                         `;
                            
    //                         // เพิ่ม event listener เมื่อคลิกที่รายการลา
    //                         leaveItem.style.cursor = 'pointer';
    //                         leaveItem.addEventListener('click', function(e) {
    //                             e.stopPropagation();
    //                             showLeaveDetail(leave);
    //                         });
                            
    //                         leaveContainer.appendChild(leaveItem);
    //                     }
                        
    //                     // ถ้ามีการลามากกว่า 3 รายการ ให้แสดงปุ่ม "more +"
    //                     if (leavesOnThisDay.length > maxDisplay) {
    //                         const moreBtn = document.createElement('div');
    //                         moreBtn.className = 'more-leaves-btn';
    //                         moreBtn.innerHTML = `more + <span class="ms-1">\${leavesOnThisDay.length - maxDisplay}</span> เพิ่มเติม`;
                            
    //                         // เพิ่ม event listener เมื่อคลิกที่ปุ่ม "more +"
    //                         moreBtn.addEventListener('click', function(e) {
    //                             e.stopPropagation();
    //                             showDayLeaveModal(date, leavesOnThisDay);
    //                         });
                            
    //                         leaveContainer.appendChild(moreBtn);
    //                     }
                        
    //                     info.el.appendChild(leaveContainer);
                        
    //                     // เพิ่ม event listener เมื่อคลิกที่วันที่
    //                     info.el.style.cursor = 'pointer';
    //                     info.el.addEventListener('click', function() {
    //                         showDayLeaveModal(date, leavesOnThisDay);
    //                     });
    //                 }
    //             },
    //             datesSet: function(info) {
    //                 updateTodayLeaveCount();
    //             }
    //         });
            
    //         calendar.render();
            
    //         // แสดงรายละเอียดการลา
    //         function showLeaveDetail(leave) {
    //             const employee = leave.employee;
    //             const startDate = new Date(leave.start);
    //             const endDate = new Date(leave.end || leave.start);
    //             const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                
    //             // แปลงประเภทการลาเป็นภาษาไทย
    //             let leaveTypeText = '';
    //             switch(leave.type) {
    //                 case 'sick': leaveTypeText = 'ลาป่วย'; break;
    //                 case 'vacation': leaveTypeText = 'ลาพักผ่อน'; break;
    //                 case 'personal': leaveTypeText = 'ลากิจ'; break;
    //                 case 'maternity': leaveTypeText = 'ลาคลอด'; break;
    //                 case 'training': leaveTypeText = 'ลาฝึกอบรม'; break;
    //             }
                
    //             // แสดง Modal รายละเอียดการลา
    //             $('#detailAvatar').attr('src', employee.avatar);
    //             $('#detailName').text(employee.name);
    //             $('#detailDepartment').text('แผนก' + getDepartmentName(employee.department));
    //             $('#detailType').text(leaveTypeText);
    //             $('#detailStart').text(formatThaiDate(startDate));
    //             $('#detailEnd').text(formatThaiDate(endDate));
    //             $('#detailDays').text(days + ' วัน');
    //             $('#detailReason').text(leave.reason);
    //             $('#detailStatus').html(`<span class="badge bg-success">\${leave.status}</span>`);
                
    //             const leaveDetailModal = new bootstrap.Modal(document.getElementById('leaveDetailModal'));
    //             leaveDetailModal.show();
    //         }
            
    //         // อัพเดทข้อมูลการลาวันนี้
    //         function updateTodayLeaveCount() {
    //             const today = new Date();
    //             const todayStr = formatYMD(today);
                
    //             // หาการลาในวันนี้
    //             const leavesToday = filteredEvents.filter(event => {
    //                 const eventStart = new Date(event.start);
    //                 const eventEnd = new Date(event.end || event.start);
    //                 return today >= eventStart && today <= eventEnd;
    //             });
                
    //             // จัดกลุ่มตามประเภทการลา
    //             const leavesByType = {
    //                 sick: 0,
    //                 vacation: 0,
    //                 personal: 0,
    //                 maternity: 0,
    //                 training: 0
    //             };
                
    //             leavesToday.forEach(leave => {
    //                 leavesByType[leave.type]++;
    //             });
                
    //             // แสดงข้อมูลการลาวันนี้
    //             const todayLeaveCountEl = document.getElementById('todayLeaveCount');
    //             todayLeaveCountEl.innerHTML = '';
                
    //             if (leavesToday.length === 0) {
    //                 todayLeaveCountEl.innerHTML = '<p class="text-muted">ไม่มีการลาในวันนี้</p>';
    //             } else {
    //                 if (leavesByType.sick > 0) {
    //                     todayLeaveCountEl.innerHTML += `
    //                         <div class="leave-summary-count">
    //                             <div class="leave-summary-color" style="background-color: #4cc9f0;"></div>
    //                             <span>ลาป่วย: \${leavesByType.sick} คน</span>
    //                         </div>
    //                     `;
    //                 }
                    
    //                 if (leavesByType.vacation > 0) {
    //                     todayLeaveCountEl.innerHTML += `
    //                         <div class="leave-summary-count">
    //                             <div class="leave-summary-color" style="background-color: #4361ee;"></div>
    //                             <span>ลาพักผ่อน: \${leavesByType.vacation} คน</span>
    //                         </div>
    //                     `;
    //                 }
                    
    //                 if (leavesByType.personal > 0) {
    //                     todayLeaveCountEl.innerHTML += `
    //                         <div class="leave-summary-count">
    //                             <div class="leave-summary-color" style="background-color: #7209b7;"></div>
    //                             <span>ลากิจ: \${leavesByType.personal} คน</span>
    //                         </div>
    //                     `;
    //                 }
                    
    //                 if (leavesByType.maternity > 0) {
    //                     todayLeaveCountEl.innerHTML += `
    //                         <div class="leave-summary-count">
    //                             <div class="leave-summary-color" style="background-color: #f72585;"></div>
    //                             <span>ลาคลอด: \${leavesByType.maternity} คน</span>
    //                         </div>
    //                     `;
    //                 }
                    
    //                 if (leavesByType.training > 0) {
    //                     todayLeaveCountEl.innerHTML += `
    //                         <div class="leave-summary-count">
    //                             <div class="leave-summary-color" style="background-color: #4895ef;"></div>
    //                             <span>ลาฝึกอบรม: \${leavesByType.training} คน</span>
    //                         </div>
    //                     `;
    //                 }
                    
    //                 todayLeaveCountEl.innerHTML += `
    //                     <div class="mt-2">
    //                         <strong>รวมทั้งหมด: \${leavesToday.length} คน</strong>
    //                     </div>
    //                 `;
    //             }
    //         }
            
    //         // แสดง Modal รายการลาในวันที่เลือก
    //         function showDayLeaveModal(date, leaves) {
    //             const formattedDate = formatThaiDate(date);
    //             $('#selectedDate').text(formattedDate);
                
    //             const dayLeaveList = document.getElementById('dayLeaveList');
    //             dayLeaveList.innerHTML = '';
                
    //             if (leaves.length === 0) {
    //                 dayLeaveList.innerHTML = '<p class="text-muted">ไม่มีการลาในวันนี้</p>';
    //             } else {
    //                 leaves.forEach(leave => {
    //                     const employee = leave.employee;
    //                     const startDate = new Date(leave.start);
    //                     const endDate = new Date(leave.end || leave.start);
    //                     const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                        
    //                     // แปลงประเภทการลาเป็นภาษาไทย
    //                     let leaveTypeText = '';
    //                     switch(leave.type) {
    //                         case 'sick': leaveTypeText = 'ลาป่วย'; break;
    //                         case 'vacation': leaveTypeText = 'ลาพักผ่อน'; break;
    //                         case 'personal': leaveTypeText = 'ลากิจ'; break;
    //                         case 'maternity': leaveTypeText = 'ลาคลอด'; break;
    //                         case 'training': leaveTypeText = 'ลาฝึกอบรม'; break;
    //                     }
                        
    //                     dayLeaveList.innerHTML += `
    //                         <div class="day-leave-item \${leave.type}">
    //                             <div class="d-flex align-items-center mb-2">
    //                                 <img src="\${employee.avatar}" class="leave-avatar me-2" alt="\${employee.name}" style="width: 32px; height: 32px;">
    //                                 <div>
    //                                     <strong>\${employee.name}</strong>
    //                                     <div class="text-muted small">แผนก\${getDepartmentName(employee.department)}</div>
    //                                 </div>
    //                             </div>
    //                             <div class="row mb-1">
    //                                 <div class="col-4">ประเภทการลา:</div>
    //                                 <div class="col-8"><strong>\${leaveTypeText}</strong></div>
    //                             </div>
    //                             <div class="row mb-1">
    //                                 <div class="col-4">ระยะเวลา:</div>
    //                                 <div class="col-8">
    //                                     <strong>\${formatThaiDate(startDate)} - \${formatThaiDate(endDate)}</strong>
    //                                     <div class="small">(\${days} วัน)</div>
    //                                 </div>
    //                             </div>
    //                             <div class="row mb-1">
    //                                 <div class="col-4">เหตุผล:</div>
    //                                 <div class="col-8">\${leave.reason}</div>
    //                             </div>
    //                             <div class="row">
    //                                 <div class="col-4">สถานะ:</div>
    //                                 <div class="col-8"><span class="badge bg-success">\${leave.status}</span></div>
    //                             </div>
    //                         </div>
    //                     `;
    //                 });
    //             }
                
    //             const dayLeaveModal = new bootstrap.Modal(document.getElementById('dayLeaveModal'));
    //             dayLeaveModal.show();
    //         }
            
    //         // แสดง Modal กรองข้อมูล
    //         $('#filterBtn').click(function() {
    //             const filterModal = new bootstrap.Modal(document.getElementById('filterModal'));
    //             filterModal.show();
    //         });
            
    //         // นำกรองข้อมูลไปใช้
    //         $('#applyFilter').click(function() {
    //             // เก็บค่าประเภทการลาที่เลือก
    //             activeFilters.types = [];
    //             $('.filter-type:checked').each(function() {
    //                 activeFilters.types.push($(this).val());
    //             });
                
    //             // เก็บค่าแผนกที่เลือก
    //             activeFilters.departments = [];
    //             $('.filter-dept:checked').each(function() {
    //                 activeFilters.departments.push($(this).val());
    //             });
                
    //             // กรองข้อมูล
    //             filteredEvents = events.filter(event => {
    //                 return activeFilters.types.includes(event.type) && 
    //                        activeFilters.departments.includes(event.employee.department);
    //             });
                
    //             // อัพเดทปฏิทิน
    //             calendar.removeAllEvents();
    //             calendar.addEventSource(filteredEvents);
    //             calendar.refetchEvents();
                
    //             // อัพเดทข้อมูลการลาวันนี้
    //             updateTodayLeaveCount();
                
    //             // ปิด Modal
    //             const filterModal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
    //             filterModal.hide();
    //         });
            
    //         // ฟังก์ชันสร้าง Avatar จากตัวอักษร
    //         function createAvatar(initials, bgColor) {
    //             const canvas = document.createElement('canvas');
    //             const context = canvas.getContext('2d');
    //             canvas.width = 200;
    //             canvas.height = 200;
                
    //             // วาดพื้นหลัง
    //             context.fillStyle = bgColor;
    //             context.beginPath();
    //             context.arc(100, 100, 100, 0, Math.PI * 2, true);
    //             context.closePath();
    //             context.fill();
                
    //             // วาดตัวอักษร
    //             context.font = 'bold 90px Prompt, sans-serif';
    //             context.fillStyle = '#ffffff';
    //             context.textAlign = 'center';
    //             context.textBaseline = 'middle';
    //             context.fillText(initials, 100, 100);
                
    //             return canvas.toDataURL('image/png');
    //         }
            
    //         // ฟังก์ชันแปลงวันที่เป็นรูปแบบไทย
    //         function formatThaiDate(date) {
    //             const options = { year: 'numeric', month: 'long', day: 'numeric' };
    //             return date.toLocaleDateString('th-TH', options);
    //         }
            
    //         // ฟังก์ชันแปลงวันที่เป็นรูปแบบ YYYY-MM-DD
    //         function formatYMD(date) {
    //             const year = date.getFullYear();
    //             const month = String(date.getMonth() + 1).padStart(2, '0');
    //             const day = String(date.getDate()).padStart(2, '0');
    //             return `\${year}-\${month}-\${day}`;
    //         }
            
    //         // ฟังก์ชันแปลงชื่อแผนกเป็นภาษาไทย
    //         function getDepartmentName(deptCode) {
    //             switch(deptCode) {
    //                 case 'IT': return 'ไอที';
    //                 case 'HR': return 'ทรัพยากรบุคคล';
    //                 case 'Finance': return 'การเงิน';
    //                 case 'Marketing': return 'การตลาด';
    //                 case 'Operations': return 'ปฏิบัติการ';
    //                 default: return deptCode;
    //             }
    //         }
            
    //         // อัพเดทข้อมูลการลาวันนี้เมื่อโหลดหน้า
    //         updateTodayLeaveCount();
    //     });


    JS;

$this->registerJS($js, View::POS_END);
?>