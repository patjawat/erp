<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
$this->title = 'ขออนุมัติเบิกวัสดุ ';
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
            'menu' => 'main-stock'
        ]) ?>


<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title ?> <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
            <?php echo $this->render('@app/modules/approveV2/views/default/_search', ['model' => $searchModel,'emp_label' => 'ผู้ขอเบิก']) ?>

            <?php // echo Html::a('อนุมัติทั้งหมด',['/approve/leave/approve-all'],['class' => 'btn btn-primary rounded-pill shadow approve-all']);
            ?>
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
                        <th scope="col">ผู้ขอเบิก</th>
                        <th scope="col">วันที่</th>
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
                                <?php //  $item->stock->id?>
                                <?= $item->viewApproveStatus() ?>
                            </td>
                            <td class="text-truncate" style="max-width: 230px;">
                                <a href="<?php echo Url::to(['/hr/leave/view', 'id' => '', 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>"
                                    class="open-modal" data-size="modal-xl">
                                    <?php 
                                    try {
                                        echo $item->stock->employee->getAvatar(false);
                                    } catch (\Throwable $th) {

                                    }
                                     ?>
                                    <?php // echo isset($item->stock) ? $item->stock->CreateBy()['avatar'] : '' ?>
                                </a>
                            </td>
                            <td><?php echo isset($item->stock?->created_at) ? ThaiDateHelper::formatThaiDate($item->stock?->created_at) : '' ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">

                                    <?php echo Html::a('<i class="fa-regular fa-circle-check"></i> ตรวจสอบ', ['/approve/main-stock/update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary rounded-pill open-modal', 'data' => ['size' => 'modal-xl']]) ?>
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
            Swal.fire({
                title: 'กำลังดำเนินการ...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                type: "get",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'อนุมัติทั้งหมดเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 2000, // ตั้งเวลา 2 วินาที
                            showConfirmButton: false,
                            willClose: () => {
                                location.reload(true); // รีโหลดหน้าหลังจากปิด Swal
                            }
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
    $this->registerJS($js, View::POS_END);
    ?>