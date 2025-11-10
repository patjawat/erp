<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
$this->title = 'อนุมัติการลา ';
$msg = 'ขอ';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu', ['active' => 'approve']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="text-white"><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title ?> <span class="badge rounded-pill text-bg-primary"><?= number_format($dataProvider->getTotalCount()) ?> </span> รายการ</h6>
            <?php // echo Html::a('อนุมัติทั้งหมด', ['/approve/leave/approve-all'], ['class' => 'btn btn-light shadow approve-all']); 
            ?>

            <div>
                <!-- ปุ่มดำเนินการ -->
                <div class="mt-3 d-flex justify-content-center gap-2">
                    <?= Html::button('<i class="fa-solid fa-check"></i> อนุมัติที่เลือก', [
                        'class' => 'btn btn-success',
                        'id' => 'btn-approve-selected',
                        'type' => 'button'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <!-- Checkbox เลือกทั้งหมด -->
                    <th class="text-center fw-semibold" style="width:30px">
                        <input type="checkbox" id="check-all">
                    </th>
                    <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                    <th class="fw-semibold text-center" scope="col" style="width:30px">ปีงบประมาณ</th>
                    <th class="fw-semibold" scope="col">ผู้ขออนุมัติการลา</th>
                    <th class="fw-semibold" scope="col" style="width:100px">ประเภทเวร</th>
                    <th class="fw-semibold">ประเภทการลา</th>
                    <th class="fw-semibold">ระหว่างวันที่</th>
                    <th class="fw-semibold text-start" scope="col">หน่วยงาน</th>
                    <th class="fw-semibold" scope="col" style="width: 127px;">ผู้อนุมัติ</th>
                    <th class="fw-semibold text-start">สถานะ/ความคืบหน้า</th>
                    <th class="fw-semibold text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr class="">
                        <td class="text-center">
                            <input type="checkbox" class="check-item" name="selected[]" value="<?= $item->id ?>">
                        </td>
                        <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td class="text-center fw-semibold "><?php echo $item->leave->thai_year ?></td>
                        <td class="text-truncate" style="max-width: 230px;">
                            <a href="<?php echo Url::to(['/me/leave/view', 'id' => $item->leave->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>"
                                class="open-modal" data-size="modal-xl">
                                <?php echo  $item->leave->employee->getAvatar(false) ?>
                            </a>
                        </td>
                        <td><?= $item->leave->work_shift_name ?></td>
                        <td>
                            <?= $item->leave->data_json['reason'] ?>
                            <div class="d-flex flex-column justofy-content-start align-items-start">
                                <span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i
                                        class="bi bi-exclamation-circle-fill"></i>
                                    <?php echo $item->leave->leaveType?->title ?? '-' ?>
                                    <code><?php echo $item->leave->total_days ?> </code> วัน</span>
                            </div>
                        </td>
                        <td><?php echo $item->leave->showLeaveDate() ?></td>
                        <td class="text-start text-truncate" style="max-width:150px;"><?php echo $item->leave->employee->departmentName() ?></td>
                        <td><?php echo $item->leave->stackChecker() ?></td>
                        <td class="fw-light align-middle text-start" style="width:150px;"><?php echo $item->leave->showStatus(); ?></td>

                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">

                                <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>', ['/approve/leave/update', 'id' => $item->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
</div>
<?= $this->render('calendar') ?>
<?php
$calendarUrl = Url::to(['/approve/leave/get-events']);
$currentDate = $date;

$js = <<< JS

let currentDate = new Date('$currentDate');
    
    // ฟังก์ชันแสดงปฏิทิน
    function renderCalendar(date) {
        // สร้างวันที่สำหรับสัปดาห์ (7 วัน)
        let weekDates = [];
        let startOfWeek = new Date(date);
        
        // ปรับวันเริ่มต้นตามวันที่เลือก (center the view around the selected date)
        startOfWeek.setDate(startOfWeek.getDate() - 3); // ย้อนหลัง 3 วัน เพื่อให้วันที่เลือกอยู่ตรงกลาง
        
        for (let i = 0; i < 7; i++) {
            let day = new Date(startOfWeek);
            day.setDate(startOfWeek.getDate() + i);
            weekDates.push(day);
        }
        
        // สร้างส่วนหัวของปฏิทิน
        let headerHtml = '<th width="50">เวลา</th>';
        for (let i = 0; i < weekDates.length; i++) {
            let date = weekDates[i];
            let dayName = new Intl.DateTimeFormat('th-TH', { weekday: 'short' }).format(date);
            let dateStr = new Intl.DateTimeFormat('th-TH', { day: 'numeric', month: 'short' }).format(date);
            let isSelectedDay = isSameDay(date, currentDate) ? 'bg-primary text-white' : '';
            let isToday = isSameDay(date, new Date()) ? 'bg-success text-white' : '';
            let bgClass = isSelectedDay ? 'bg-primary text-white' : (isToday ? 'bg-success text-white' : '');
            headerHtml += `<th class="\${bgClass}">\${dayName}<br>\${dateStr}</th>`;
        }
        $('#calendar-header').html(headerHtml);
        
        // สร้างเซลล์เวลาในปฏิทิน (ตั้งแต่ 8:00 ถึง 20:00)
        let bodyHtml = '';
        for (let hour = 8; hour <= 20; hour++) {
            let timeStr = `\${hour}:00`;
            bodyHtml += `<tr><th class="text-center">\${timeStr}</th>`;
            
            for (let date of weekDates) {
                let dateStr = formatDate(date);
                let isSelectedDay = isSameDay(date, currentDate) ? 'bg-light' : '';
                bodyHtml += `<td class="calendar-cell \${isSelectedDay}" data-date="\${dateStr}" data-time="\${hour}:00"></td>`;
            }
            
            bodyHtml += '</tr>';
        }
        $('#calendar-body').html(bodyHtml);
        
        // แสดงช่วงวันที่กำลังดู
        let startDateStr = new Intl.DateTimeFormat('th-TH', { day: 'numeric', month: 'long', year: 'numeric' }).format(weekDates[0]);
        let endDateStr = new Intl.DateTimeFormat('th-TH', { day: 'numeric', month: 'long', year: 'numeric' }).format(weekDates[6]);
        let currentDateStr = new Intl.DateTimeFormat('th-TH', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(currentDate);
        $('#date-range').html(`วันที่เลือก: <strong>\${currentDateStr}</strong><br>ช่วงที่แสดง: \${startDateStr} - \${endDateStr}`);
        
        // ดึงข้อมูลกิจกรรม
        let startDate = formatDate(weekDates[0]);
        let endDate = formatDate(weekDates[6]);
        loadEvents(startDate, endDate);
    }
    
    // ฟังก์ชันโหลดกิจกรรมจาก API
    function loadEvents(start, end) {
        $.ajax({
            url: '$calendarUrl',
            data: {
                start: start,
                end: end
            },
            dataType: 'json',
            success: function(events) {
                renderEvents(events);
                console.log('load event');
                
            }
        });
    }
    
    // แสดงกิจกรรมบนปฏิทิน
    function renderEvents(events) {
        // ล้างกิจกรรมเก่า
        $('.event-item').remove();
        
        for (let event of events) {
            let startDate = new Date(event.start);
            let eventDate = formatDate(startDate);
            let eventHour = startDate.getHours();
            console.log(eventDate);
            
            
            let cell = $(`.calendar-cell[data-date="\${eventDate}"][data-time="\${eventHour}:00"]`);
            
            let eventHtml = `
                <a class="event-item p-1 mb-1 rounded badge-soft-success" 
                     data-id="\${event.id}"
                     data-title="\${event.title}"
                     data-description="\${event.description || ''}"
                     data-start="\${event.start}"
                     data-end="\${event.end || ''}">
                    \${event.title}
                </a>
            `;
            
            cell.append(eventHtml);
        }
        
        // เพิ่ม event click เพื่อแสดงรายละเอียด
        $('.event-item').on('click', function() {
            let id = $(this).data('id');
            let title = $(this).data('title');
            let description = $(this).data('description');
            let start = new Date($(this).data('start'));
            let end = $(this).data('end') ? new Date($(this).data('end')) : null;
            
            let startStr = new Intl.DateTimeFormat('th-TH', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(start);
            
            let endStr = end ? new Intl.DateTimeFormat('th-TH', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(end) : '';
            
            let detailsHtml = `
                <p><strong>เริ่ม:</strong> \${startStr}</p>
                \${end ? `<p><strong>สิ้นสุด:</strong> \${endStr}</p>` : ''}
                \${description ? `<p><strong>รายละเอียด:</strong><br>\${description}</p>` : ''}
            `;
            

                $.ajax({
                    type: "get",
                    url: '/approve/leave/update',
                    data:{id:id},
                    dataType: "json",
                    success: function (response) {
                    $("#main-modal").modal("show");
                    $("#main-modal-label").html(response.title);
                    $(".modal-body").html(response.content);
                    $(".modal-footer").html(response.footer);
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                    $(".modal-dialog").addClass('modal-xl');
                    $(".modal-content").addClass("card-outline card-primary");
                    },
                    error: function (xhr) {
                    $("#main-modal-label").html("เกิดข้อผิดพลาด");
                    $(".modal-body").html(
                        '<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>'
                    );
                    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
                    $(".modal-dialog").addClass("modal-md");
                    console.log(xhr);

                    }
                })
            // $('#event-title').text(title);
            // $('#event-details').html(detailsHtml);
            
            // let modal = new bootstrap.Modal(document.getElementById('eventModal'));
            // modal.show();
        });
    }
    
    // ฟังก์ชันช่วยจัดการวันที่
    function formatDate(date) {
        let year = date.getFullYear();
        let month = (date.getMonth() + 1).toString().padStart(2, '0');
        let day = date.getDate().toString().padStart(2, '0');
        return `\${year}-\${month}-\${day}`;
    }
    
    function isSameDay(date1, date2) {
        return date1.getFullYear() === date2.getFullYear() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getDate() === date2.getDate();
    }
    
    // แสดงปฏิทินเริ่มต้น
    renderCalendar(currentDate);
    
    // กำหนด event handlers สำหรับปุ่มนำทาง
    $('#prev-day').on('click', function() {
        currentDate.setDate(currentDate.getDate() - 1);
        renderCalendar(currentDate);
        updateUrlParam();
    });
    
    $('#next-day').on('click', function() {
        currentDate.setDate(currentDate.getDate() + 1);
        renderCalendar(currentDate);
        updateUrlParam();
    });
    
    $('#prev-week').on('click', function() {
        currentDate.setDate(currentDate.getDate() - 7);
        renderCalendar(currentDate);
        updateUrlParam();
    });
    
    $('#next-week').on('click', function() {
        currentDate.setDate(currentDate.getDate() + 7);
        renderCalendar(currentDate);
        updateUrlParam();
    });
    
    $('#today').on('click', function() {
        currentDate = new Date();
        renderCalendar(currentDate);
        updateUrlParam();
    });
    
    // อัปเดต URL parameter เมื่อเปลี่ยนวันที่
    function updateUrlParam() {
        let dateParam = formatDate(currentDate);
        let url = new URL(window.location.href);
        url.searchParams.set('date', dateParam);
        window.history.replaceState({}, '', url);
    }




$('.approve-all').click(function (e) { 
    e.preventDefault();
    
    let url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการอนุมัติ?',
        text: "คุณแน่ใจหรือไม่ว่าต้องการอนุมัติทั้งหมด?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, อนุมัติ!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                type: "get",
                url: url,
                dataType: "json",
                success: function (res) {
                    console.log(res);
                    
                    if (res.status == 'success') {
                        Swal.fire({
                        title: 'กำลังบันทึกข้อมูล...',
                        text: 'โปรดรอสักครู่',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: 1000,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    }).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ',
                            showConfirmButton: false,
                            timer: 1000
                        }).then(() => {
                            window.location.reload();
                        });  
                    });
                    
                    } else {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: res.message || 'ไม่สามารถอนุมัติได้',
                            icon: 'error'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด!',
                        text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้',
                        icon: 'error'
                    });
                }
            });
        }
    });
});



// ปุ่มเลือกทั้งหมด
  // เลือก checkbox ทั้งหมด
    $('#check-all').on('change', function() {
        $('.check-item').prop('checked', this.checked);
    });

    // อัปเดต checkbox ส่วนหัวตาม checkbox รายตัว
    $('.check-item').on('change', function() {
        $('#check-all').prop('checked', $('.check-item').length === $('.check-item:checked').length);
    });


      // ฟังก์ชันอนุมัติที่เลือก
    $('#btn-approve-selected').on('click', function() {
        // เก็บ id ของรายการที่ถูกเลือก
        var selectedIds = $('.check-item:checked').map(function() {
            return $(this).val();
        }).get();

        if(selectedIds.length === 0) {
            alert('กรุณาเลือกอย่างน้อย 1 รายการ');
            return;
        }

        if(!confirm('ยืนยันการอนุมัติรายการที่เลือก?')) {
            return;
        }

        $.ajax({
            url: '/approve/leave/bulk-action', // เปลี่ยน URL ตาม Controller ของคุณ
            type: 'POST',
            data: {
                selected: selectedIds,
                action: 'approve',
                _csrf: yii.getCsrfToken() // สำหรับ Yii2
            },
            success: function(response) {
                // ตัวอย่าง: รีเฟรชตาราง หรือโชว์ข้อความ
                alert('อนุมัติเรียบร้อย!');
                location.reload(); // หรือทำการอัปเดตตารางด้วย Ajax
            },
            error: function(xhr) {
                alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
            }
        });
    });


JS;
$this->registerJS($js, View::POS_END);
$this->registerCss('
.calendar-table th, .calendar-table td {
    min-width: 120px;
    height: 50px;
    vertical-align: top;
}
.calendar-table th {
    text-align: center;
}
.event-item {
    font-size: 0.85rem;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
');
?>