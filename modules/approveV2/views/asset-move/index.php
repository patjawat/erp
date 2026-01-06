<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
$this->title = 'อนุมัติเคลื่อนย้ายครุภัณฑ์';
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
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>


<?= $this->render('@app/modules/approveV2/tab_menu', [
    'menu' => 'asset-move'
]) ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title ?> <span
                    class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
            <?php echo $this->render(
                '@app/modules/approveV2/views/default/_search',
                [
                    'model' => $searchModel,
                    'emp_label' => 'ผู้ขอ',
                    'approveAllUrl' => Url::to(['/approve/asset-move/approve-all'])
                ]
            ) ?>

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
                        <th class="text-start" style="width: 160px;">สถานะ</th>
                        <th>ครุภัณฑ์</th>
                        <th>เหตุผลการเคลื่อนย้าย</th>
                        <th>วันที่ต้องการย้าย</th>
                        <th>ผู้ขออนุมัติ</th>
                        <th class="text-center" style="width:115px">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
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
                            <td>
                                <div class="asset-preview-box mb-4">
                                    <div class="row align-items-center">
                                        <div class="col-auto text-primary">
                                            <?= Html::img($item->assetMove->asset->showImg()['image'], ['class' => 'w-100 h-100 object-fit-cover', 'style' => 'max-width: 76px;']) ?>
                                        </div>
                                        <div class="col">
                                            <h6 class="fw-bold mb-1"><?= $item->assetMove->asset->asset_name ?? '-' ?></h6>
                                            <p class="text-muted mb-0">หมายเลขครุภัณฑ์: <?= $item->assetMove->asset->code ?? '' ?> | ยี่ห้อ: <?= $item->assetMove->asset->data_json['brand'] ?? '' ?></p>
                                            <p class="text-muted mb-0">สถานะปัจจุบัน: <?= $item->assetMove->asset->viewstatus() ?></p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= $item->assetMove->getReasonLabel()  ?? '-' ?></td>
                            <td><?php
                                try {
                                    echo Yii::$app->thaiDate->toThaiDate($item->assetMove->date_start, false, false);
                                } catch (\Throwable $th) {
                                }

                                ?></td>
                            <td><?= $item->assetMove->employee->fullname ?? '-' ?></td>
                            <td class="text-center py-2">
                                <div class="d-flex justify-content-center">
                                    <a href="<?= Url::to(['update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-sm btn-outline-primary rounded-pill open-modal" data-size="modal-xl" title="ดูรายละเอียด">
                                        <i class="fa-regular fa-circle-check"></i> ตรวจสอบ
                                    </a>

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