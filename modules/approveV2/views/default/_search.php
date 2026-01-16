<?php
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\approve\models\ApproveSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="approve-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="d-flex flex-row gap-2">

        <?= $this->render('@app/components/ui/_date_start',['form' => $form,'model' => $model,'label' => false])?>

        <?= $this->render('@app/components/ui/_date_end',['form' => $form,'model' => $model,'label' => false])?>
        <div style="width:200px">
            <?=$this->render('@app/components/ui/input_emp',['form' => $form,'model' => $model,'label' => false,'placeholder' => $emp_label])?>
        </div>
        <?= $form->field($model, 'status')->dropDownList(
            [
                'Pending' => 'รอเห็นชอบ',
                'Pass' => 'เห็นชอบ',
                'Reject' => 'ไม่เห็นชอบ',
            ],
            [
                'prompt' => '--- เลือกสถานะ ---', // ข้อความเริ่มต้น
                'class' => 'form-select shadow-sm', // ใช้คลาส Bootstrap 5
            ]
        )->label(false) ?>
         <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']);?>
          <?= Html::button('<i class="fa-solid fa-check"></i> อนุมัติที่เลือก', [
                'class' => 'btn btn-success btn-approve-reject',
                'type' => 'button',
                'data-status' => 'Pass' // สำหรับส่งไป controller
            ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php

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
                url: '$approveAllUrl', // URL ของ controller updateAll
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

JS;
$this->registerJS($js, View::POS_END);

?>