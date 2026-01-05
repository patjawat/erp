<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ApproveHelper;

/** @var yii\web\View $this */
$me = \app\components\UserHelper::GetEmployee();
$this->title = "การอนุมัติวันลา";
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;

$msg = 'ขอ';
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check">
      <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
      <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
      <path d="m9 14 2 2 4-4" />
    </svg>
    <?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php // $this->render('@app/components/ui/btnReturn') 
?>
<?php //  $this->render('@app/modules/approveV2/menu', ['active' => 'leave']) 
?>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/approveV2/tab_menu', [
  'menu' => 'leave'
]) ?>



<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between">
      <h6>เห็นชอบการลา <?= $dataProvider->getTotalCount() ?> รายการ</h6>

      <?php echo $this->render('@app/modules/approveV2/views/default/_search', ['model' => $searchModel, 'emp_label' => 'ผู้ขออนุมัติการลา']) ?>

    </div>
    <div class="table-responsive" style="max-height: 600px;max-height: 600px;min-height:300px; overflow: auto;">
      <table class="table table-striped table-hover mb-0">
        <thead style="position: sticky; top: 0; z-index: 10;">
          <tr>
            <!-- Checkbox เลือกทั้งหมด -->
            <th class="text-center" style="width:30px">
              <input type="checkbox" id="check-all">
            </th>
            <th class="text-center" style="width:30px">ลำดับ</th>
            <th class="text-start" style="width: 165px;">สถานะ</th>
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th scope="col" style="width:100px">ประเภทเวร</th>
            <th>ประเภทการลา</th>
            <th style="width: 150px;">ระหว่างวันที่</th>
            <th class="text-start" scope="col">หน่วยงาน</th>
            <th scope="col" style="width: 127px;">ผู้อนุมัติ</th>
            <th class="text-start" style="width: 165px;">ความคืบหน้า</th>
            <th class="text-center">ดำเนินการ</th>
          </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
          <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr class="">
              <td class="text-center">
                <input
                  type="checkbox"
                  class="check-item"
                  name="selected[]"
                  value="<?= $item->id ?>"
                  <?= ($item->status == 'Pending'  ? '' : 'disabled') ?>>
              </td>
              <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
              <td>
                <?= $item->viewApproveStatus() ?>
              </td>
              <td class="text-truncate" style="max-width: 230px;">
                <a href="<?php echo Url::to(['/me/leave/view', 'id' => $item->leave->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>"
                  class="open-modal" data-size="modal-xl">
                  <?php echo  $item->leave->employee->getAvatar(false) ?>
                </a>
              </td>
              <td><?= $item->leave->work_shift_name ?></td>
              <td>
                <div class="fw-medium text-dark mb-1">
                  <?= $item->leave->leaveType?->title ?? '-' ?>
                  <span class="badge bg-primary-subtle text-primary border-0 ms-1 fw-normal">
                    <?= $item->leave->total_days ?> วัน
                  </span>
                </div>
                <div class="text-muted small text-truncate" style="max-width: 200px;" title="<?= $item->leave->data_json['reason'] ?>">
                  <?= $item->leave->data_json['reason'] ?>
                </div>
              </td>
              <td>
                <span class="text-dark fw-semibold small">
                  <?php echo $item->leave->showLeaveDate() ?>
              </td>
              </span>
              <td class="text-start text-truncate" style="max-width:150px;"><?php echo $item->leave->employee->departmentName() ?></td>
              <td><?php echo $item->leave->stackChecker() ?></td>
              <td class="fw-light align-middle text-start" style="width:150px;">
                <?php echo $item->leave->viewStatus(); ?>
                <?php echo ApproveHelper::viewStep('leave', $item->leave->id); ?>
              </td>

              <td class="text-center">
                <?= Html::a(
                  '<i class="fa-regular fa-circle-check"></i> ตรวจสอบ',
                  ['/approve/leave/update', 'id' => $item->id],
                  [
                    'class' => 'btn btn-sm btn-outline-primary rounded-pill open-modal',
                    'data' => ['size' => 'modal-xl']
                  ]
                ) ?>
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
</div>

<?php
$calendarUrl = Url::to(['/approve/leave/get-events']);
$currentDate = $date;

$js = <<< JS

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
    // ติ๊กเฉพาะ checkbox ที่ไม่ได้ disabled
    $('.check-item:not(:disabled)').prop('checked', this.checked);
    
    // แสดงปุ่ม approve
    $('#btn-approve-selected').show();
});

    // อัปเดต checkbox ส่วนหัวตาม checkbox รายตัว
    $('.check-item').on('change', function() {
    $('#check-all').prop('checked', $('.check-item').length === $('.check-item:checked').length);
    $('#btn-approve-selected').show();
});


$('.btn-approve-reject').on('click', function() {
    // เก็บ id ของรายการที่ถูกเลือก (ข้าม disabled)
    var selectedIds = $('.check-item:checked:not(:disabled)').map(function() {
        return $(this).val();
    }).get();

    if(selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'กรุณาเลือกอย่างน้อย 1 รายการ',
        });
        return;
    }

    // ดึง status จากปุ่ม
    var status = $(this).data('status');
    var actionText = status === 'Pass' ? 'อนุมัติ' : 'ไม่อนุมัติ';

    Swal.fire({
        title: 'ยืนยันการ ' + actionText + ' รายการที่เลือก?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: false
    }).then((result) => {
        if (result.isConfirmed) {

            // แสดง loading ระหว่างรอ Ajax
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/approve/leave/approve-all', // URL ของ controller updateAll
                type: 'POST',
                data: {
                    ids: selectedIds,
                    status: status,
                    _csrf: yii.getCsrfToken() // สำหรับ Yii2
                },
                success: function(response) {
                    Swal.close(); // ปิด loading
                    if(response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: actionText + ' เรียบร้อย!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // หรืออัปเดตตารางด้วย Ajax
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.message || 'กรุณาลองใหม่'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close(); // ปิด loading
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'กรุณาลองใหม่'
                    });
                }
            });
        }
    });
});





      // ฟังก์ชันอนุมัติที่เลือก
    // $('#btn-approve-selected').on('click', function() {
    //     // เก็บ id ของรายการที่ถูกเลือก
    //     var selectedIds = $('.check-item:checked').map(function() {
    //         return $(this).val();
    //     }).get();

    //     if(selectedIds.length === 0) {
    //         alert('กรุณาเลือกอย่างน้อย 1 รายการ');
    //         return;
    //     }

    //     if(!confirm('ยืนยันการอนุมัติรายการที่เลือก?')) {
    //         return;
    //     }

    //     $.ajax({
    //         url: '/approve/leave/bulk-action', // เปลี่ยน URL ตาม Controller ของคุณ
    //         type: 'POST',
    //         data: {
    //             selected: selectedIds,
    //             action: 'approve',
    //             _csrf: yii.getCsrfToken() // สำหรับ Yii2
    //         },
    //         success: function(response) {
    //             // ตัวอย่าง: รีเฟรชตาราง หรือโชว์ข้อความ
    //             alert('อนุมัติเรียบร้อย!');
    //             location.reload(); // หรือทำการอัปเดตตารางด้วย Ajax
    //         },
    //         error: function(xhr) {
    //             alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
    //         }
    //     });
    // });


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