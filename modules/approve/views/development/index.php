<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'อนุมัติอบรม/ประชุม/ดูงาน';
$msg = 'ขอ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
        <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
        <?php echo Html::a('อนุมัติทั้งหมด',['/approve/leave/approve-all'],['class' => 'btn btn-primary rounded-pill shadow approve-all']);?>
    </div>
        <div class="d-flex justify-content-between  align-top align-items-center">
            <div class="d-flex flex-column">
                <div class="d-flex justify-content-between">
                </div>
                <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
        
        <div class="table-responsive pb-5">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="text-center fw-semibold" style="width:30px">ปีงบประมาณ</th>

                        <th class="fw-semibold" scope="col">เรื่อง/วัน/สถานที่</th>
                        <th class="fw-semibold"  scope="col">คณะเดินทาง</th>
                        <th class="fw-semibold"  scope="col">สถานะ</th>
                        <th class="fw-semibold text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold">
                            <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                        </td>
                        <td><?=$item->development->thai_year;?></td>
                        <td>
                            <div>
                                <p class="fw-semibold mb-0"><?=$item->development->topic?></p>
                                สถานที่ <?=$item->development->data_json['location'] ?? '-'?>  <?=$item->development->showDateRange()?>
                            </div>
                        </td>

                        <td>  <?=$item->development->StackMember()?></td>
                        <td><?=$item->viewStatus()['view'] ?>
                    </td>
                        
                        <td class="text-center" style="width:120px">
                            <div class="btn-group">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update','id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal','data' => ['size' => 'modal-xl']]) ?>
                                <!-- <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด', ['view','id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                    </ui> -->
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
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
</div>

<?php // Pjax::end(); ?>



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


JS;
$this->registerJS($js,View::POS_END);
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