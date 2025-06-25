<?php

use app\components\AppHelper;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use app\models\Categorise;
use app\components\UserHelper;
use app\modules\hr\models\Organization;

$thaiYear = AppHelper::YearBudget();

$listLeaveType = Categorise::find()->where(['name' => 'leave_type'])->all();
$listLeaveStatus = Categorise::find()->where(['name' => 'leave_status'])->all();
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
<?php echo $this->render('@app/modules/me/menu', ['active' => 'vehicle']) ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/views/leave/_sub_menu', ['active' => 'calendar']) ?>
<?php $this->endBlock(); ?>




<style>
    .fc-theme-standard .fc-scrollgrid {
        border-radius: 5px;
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
</style>

<div class="row">
    <div class="col-9" id="calender-container">
        <div class="card" id="fullscreen-container">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>ปฏิทินการลาของบุคลากร ประจำปี <?= $thaiYear ?></span>
                <div class="d-flex align-items-center gap-2">
                    <div class="">
                        <label for="eventLimitSelector" class="form-label">แารแสดงผล:</label>
                        <select id="eventLimitSelector" class="form-select" style="width: auto; display: inline-block;">
                            <option value="2">2 รายการ</option>
                            <option value="3" selected>3 รายการ</option>
                            <option value="5">5 รายการ</option>
                            <option value="all">แสดงทั้งหมด</option>
                        </select>
                    </div>

                    <div style="width: 400px;">

                        <?php
                        $me = UserHelper::GetEmployee();
                        echo \kartik\tree\TreeViewInput::widget([
                            'query' => Organization::find()->addOrderBy('root, lft'),
                            'value' => $me->department,
                            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                            'fontAwesome' => true,
                            'multiple' => false,
                            'name' => 'department',
                            'options' => [
                                'disabled' => false,
                                'class' => 'close',
                                'id' => 'departmentFilter',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]);
                        ?>
                    </div>



                    <button class="btn btn-sm btn-light" id="leave-manual"><i class="fa-solid fa-book"></i>
                        แสดงคู่มือ</button>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
                <!-- <div class="leave-summary">
                    <h5 class="leave-summary-title">สรุปการลาวันนี้</h5>
                    <div id="todayLeaveCount"></div>
                </div> -->
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
                    <li class="mb-2">คลิกเลือกวันที่ที่ต้องการลา เพื่อสร้างคำขอลาใหม่</li>
                    <li class="mb-2">กรอกรายละเอียดการลาให้ครบถ้วน</li>
                    <li class="mb-2">คลิกที่รายการลาในปฏิทินเพื่อดูรายละเอียด</li>
                </ol>

            </div>
        </div>
        <div class="card">
            <div class="card-header  bg-primary-gradient">
                <div class="d-flex justify-content-between align-items-center align-self-center">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> ประเภทการลา</h5>
                    <?= html::a('<i class="fa-solid fa-gear"></i>', ['/hr/leave-type/index'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">
                    <?php foreach ($listLeaveType as $leaveType): ?>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="leave-type-indicator <?= $leaveType->code ?>" style="background-color:<?= $leaveType->data_json['color'] ?? 'var(--bs-primary)' ?>"></span><?= $leaveType->title ?>
                            </div>
                            <div>
                                <!-- <span class="badge text-bg-light">0</span> -->
                            </div>


                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card text-start">
            <div class="card-header bg-primary-gradient">
                <div class="d-flex justify-content-between align-items-center align-self-center">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> สถานะการลา</h5>
                    <?= html::a('<i class="fa-solid fa-gear"></i>', ['/hr/leave-status/index'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($listLeaveStatus as $leaveStatus): ?>
                        <div class="d-flex align-items-center">
                            <div class="<?= $leaveType->code ?>" style="width:4px; height:16px; background-color:<?= $leaveStatus->data_json['color'] ?? 'var(--bs-primary)' ?>; margin-right:8px;"></div>
                            <?= $leaveStatus->title ?>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

    </div>
</div>


<!-- https://www.canva.com/ai/code/thread/9dc074ce-cf81-420d-9c78-bc0fa333e10e -->
<?php
$url = Url::to(['/me/leave/']);
$js = <<<JS
        $(document).ready(function() {

            var calendarEl = $('#calendar')[0];
            var containerEl = $('#external-events')[0];
            var checkbox = $('#drop-remove')[0];
            const allEvents = []; // เก็บ events ทั้งหมด


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


                        // กรองข้อมูลแผนกฝ่าย
            function renderFilteredEvents(department) {
                const filtered = department
                    ? allEvents.filter(e => e.extendedProps.department === department)
                    : allEvents;

                calendar.removeAllEvents();
                calendar.addEventSource(filtered);
            }


            
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
                    right: 'fullscreenToggle dayGridMonth,timeGridWeek,timeGridDay'
                },
                customButtons: {
                    fullscreenToggle: {
                        text: 'ขยาย/ย่อ',
                        click: function () {
                        $('#fullscreen-container').toggleClass('fullscreen');
                        calendar.updateSize();

                    }
                    }
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
                            end: fetchInfo.endStr,
                            department: $('#departmentFilter').val()
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

                      // ดึงสี border-left จาก extendedProps
                if(info.event.extendedProps.borderLeftColor){
                info.el.style.borderLeft = '5px solid ' + info.event.extendedProps.color;
                }

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
                    container.innerHTML = `<div class="mb-0 d-flex flex-column justify-conten-start gap-1">\${avatar}</div>`;

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
                    let source = info.event.extendedProps.source;
                    var url = '$url/'+'view?id=' + info.event.id;
                    console.log(source);
                    
                    if(source !== 'holiday'){
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
                    }
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
            // คลิกเพื่อเลือกแผนก/ฝ่าย
                $('#departmentFilter').on('change', function () {
                        $('body').find('.kv-tree-input').removeClass('show')
                        $('body').find('.kv-tree-dropdown').removeClass('show')
                        calendar.refetchEvents();
            });
        });

    JS;

$this->registerJS($js, View::POS_END);
?>