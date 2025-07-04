<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'ขออนุมัติเบิกวัสดุ ';
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

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'approve']) ?>
<?php $this->endBlock(); ?>


<?php if($dataProvider->getTotalCount() > 0):?>
<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
            <?php echo Html::a('อนุมัติทั้งหมด',['/approve/leave/approve-all'],['class' => 'btn btn-primary rounded-pill shadow approve-all']);?>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ปีงบประมาณ</th>
                    <th scope="col">ผู้ขอเบิก</th>
                    <th scope="col">วันที่</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr class="">
                    <td class="text-center fw-semibold"><?php echo $item->stock->thai_year ?? '-'?></td>
                    <td class="text-truncate" style="max-width: 230px;">
                        <a href="<?php echo Url::to(['/hr/leave/view','id' => '','title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'])?>"
                            class="open-modal" data-size="modal-xl">
                            <?php echo isset($item->stock) ? $item->stock->CreateBy()['avatar'] : ''?>
                        </a>
                    </td>
                    <td><?php echo $item->stock?->viewCreatedAt()?></td>
                    <td class="text-center">
                        <div class="d-flex gap-2 justify-content-center">

                            <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/approve/main-stock/update', 'id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                        </div>

                    </td>
                </tr>
                <?php endforeach;?>
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
<?php else:?>
    <h5 class="text-center">ไม่มีรายการ</h5>
<?php endif?>
<?php // Pjax::end(); ?>

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
$this->registerJS($js,View::POS_END);
?>