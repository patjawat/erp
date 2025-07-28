<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use app\components\UserHelper;
use app\modules\hr\models\Organization;


$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
$vehicleStatus = Categorise::find()->where(['name' => 'vehicle_status'])->all();
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar fx-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้รถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('menu', ['active' => 'calendar']) ?>
<?php $this->endBlock(); ?>

<style>
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
</style>

<div class="row">
    <div class="col-9" id="calender-container">
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
    <div class="col-3" id="manual-container">
        <div class="card">
            <div class="card-header  bg-primary-gradient">
                <div class="d-flex justify-content-between align-items-center align-self-center">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> สถานะการขอใช้รถ</h5>
                    <?= html::a('<i class="fa-solid fa-gear"></i>', ['/booking/vehicle-status/index'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">
                    <?php foreach ($vehicleStatus as $_vehicleStatus): ?>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="status-indicator <?= $_vehicleStatus->code ?>" style="background-color:<?= $_vehicleStatus->data_json['color'] ?? 'var(--bs-primary)' ?>"></span><?= $_vehicleStatus->title ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<?php
$url = Url::to(['/booking/vehicle/']);
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
                moreLinkClick: 'popover',
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
                        // กำหนด URL ไปยัง action ที่ใช้แสดงรายละเอียด
                       var code = info.event.extendedProps.code || '';
                        var url = '$url/'+'view?id=' + info.event.id;
                        // โหลดเนื้อหามาแสดงใน Modal
                            $.ajax({
                                type: "get",
                                url: url,
                                dataType: "json",
                                success: function (res) {
                                      \$('#main-modal').modal('show')
                                        \$("#main-modal-label").html('<label class="form-label">ขอใช้ยานพาหนะเลขที่ : <span class="badge rounded-pill bg-primary text-white fw-bold">CAR250703-028            </span></label>');
                                        \$(".modal-body").html(res.content);
                                        $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                                        $(".modal-dialog").addClass("modal-lg");
                                }
                            });

                            
                    },
            });
            calendar.render();
        });
    JS;

$this->registerJS($js, View::POS_END);
?>