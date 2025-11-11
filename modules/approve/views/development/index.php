<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
$this->title = 'อนุมัติอบรม/ประชุม/ดูงาน';
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
        <div class="d-flex justify-content-between">
            <h6 class="text-white"><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title ?> <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
            <?php // echo Html::a('อนุมัติทั้งหมด', ['/approve/development/approve-all'], ['class' => 'btn btn-light shadow approve-all']); ?>

                    <?= Html::button('<i class="fa-solid fa-check"></i> อนุมัติที่เลือก', [
                        'class' => 'btn btn-success btn-approve-reject',
                        'type' => 'button',
                        'data-status' => 'Pass' // สำหรับส่งไป controller
                    ]) ?>
        </div>
        
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between">
        </div>
        <div class="d-flex justify-content-between  align-top align-items-center">
            <div class="d-flex flex-column">
                <div class="d-flex justify-content-between">
                </div>
            </div>
        </div>

        <div class="table-responsive pb-5">
            <table class="table">
                <thead>
                    <tr>
                        <!-- Checkbox เลือกทั้งหมด -->
                        <th class="text-center fw-semibold" style="width:30px">
                            <input type="checkbox" id="check-all">
                        </th>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="fw-semibold">เรื่อง</th>
                        <th class="fw-semibold">ประเภท</th>
                        <th class="fw-semibold">วันที่</th>
                        <th class="fw-semibold" scope="col">ผู้ขอ</th>
                        <th class="fw-semibold" scope="col" style="width: 200px;">ผู้อนุมัติ</th>
                        <th class="fw-semibold" scope="col">สถานะ</th>
                        <th class="fw-semibold text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider align-middle" id="list-data">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr data-id="<?= $item->id ?>">
                            <td class="text-center">
                            <input
                                type="checkbox"
                                class="check-item"
                                name="selected[]"
                                value="<?= $item->id ?>"
                                <?= $item->status == 'Pass' ? 'disabled' : '' ?>>
                        </td>
                            
                            <td class="text-center fw-semibold">
                                <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                            </td>
                            <td>
                                <p class="mb-0"><?= $item->development->topic ?></p>
                                <p class="mb-0">สถานที่ <span class="fw-semibold"><?= $item->development->data_json['location'] ?? 'ไม่ระบุ' ?><span></p>
                            </td>
                            <td><?= $item->developmentType?->title ?? '-' ?></td>
                            <td>
                                <p class="mb-0 fw-semibold"> <?php echo  $item->development->showDateRange() ?></p>
                            </td>
                            <td>
                                <?php

                                try {
                                     // echo $item->development->userRequest()['avatar'] ?? '';
                                } catch (\Throwable $th) {
                                    //throw $th;
                                } ?>
                            </td>

                            <!-- <td> <?php //  $item->development->StackMember() ?></td> -->
                              <td><?php //echo $item->development->stackChecker() ?></td>
                            <td>
                                <?= $item->development->status; ?>
                                <?= $item->development->getStatus($item->development->status)['view'] ?? '-' ?>
                            </td>

                            <td class="text-center" style="width:120px">
                                <div class="btn-group">
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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

<?php
$js = <<< JS



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





JS;
$this->registerJS($js, View::POS_END);
?>