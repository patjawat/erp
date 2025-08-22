<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\sm\models\Order;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => $title, 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ทะเบียนงานซ่อม';

?>

<?php $this->beginBlock('page-title'); ?>
<?=$icon?> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/helpdesk2/menu',['active' => $active]) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('@app/modules/helpdesk2/views/service/_search', ['model' => $searchModel])?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนงานซ่อม
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between gap-3">
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/me/repair-v2/create', 'title' => '<i class="fa-solid fa-screwdriver-wrench"></i> แจ้งซ่อม'],['class' => 'btn btn-light shadow open-modal','data' => ['size' => 'modal-lg']])?>
            </div>
        </div>
    </div>

    <div class="card-body">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th class="text-center" scope="col" style="width: 5%">#ลำดับ</th>
                        <th scope="col" class="text-start fw-semibold" style="width: 161px;">รหัสงานซ่อม</th>
                        <th scope="col">อุปกรณ์</th>
                        <th scope="col">ปัญหา</th>
                        <th scope="col">สถานที่</th>
                        <th scope="col">ผู้แจ้ง</th>
                        <th scope="col"  style="width: 250px;">หน่วยงาน</th>
                        <th scope="col" style="width: 100px;">ความเร่งด่วน</th>
                        <th scope="col" class="text-center"  style="width: 150px;">สถานะ</th>
                        <th scope="col">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                     <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                          <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                       <td class="text-start fw-semibold"><?php echo $item->repair_number?></td>
                        <td><?=$item->deviceType->title ?? '-'?></td>
                        <td><?=$item->title?></td>
                        <td><?=$item->data_json['location']?></td>
                        <td><?=$item->getUserReq()['avatar']?></td>
                        <td><?=$item->getUserReq()['department']?></td>
                        <td><?=$item->viewUrgent()['view']?></td>
                        <td class="text-center"><?=$item->repairStatus?->title ?? '-'?></td>
                        <td>
                            <?php if($item->status == 'pending'):?>
                            <?=Html::a('<i class="fa-solid fa-circle-exclamation"></i> รับงานซ่อม',['/helpdesk/service/receive','id' => $item->id],['class' => 'receive-order']);?>
                            <?php else:?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="">
                                    <li><?=Html::a('<i class="bi bi-eye me-2"></i> บันทึกงานซ่อม',['/helpdesk/service/view','id' => $item->id,'title' => 'รายละเอียดการแจ้งซ่อม #'.$item->repair_number],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-xl']])?></li>
                                    <li><?=Html::a('<i class="fa-regular fa-file-lines me-2"></i>เบิกอะไหล่',['/helpdesk/repair-parts/create','helpdesk_id' => $item->id,'title' => 'รายละเอียดการแจ้งซ่อม #'.$item->repair_number],['class' => 'dropdown-item','data' => ['size' => 'modal-xl']])?></li>
                                    <li><?=Html::a('<i class="bi bi-pencil me-2"></i>แก้ไข',['/helpdesk/service/update','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?></li>
                                    <li><?=Html::a('<i class="fa-solid fa-ban me-2"></i>ยกเลิก',['/helpdesk/service/cancel','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item cancel-order'])?></li>
                                    <li><?=Html::a('<i class="fa-solid fa-trash me-2"></i>ลบ',['/helpdesk/service/delete','id' => $item->id,'title' => '<i class="bi bi-pencil me-2"></i>แก้ไข'],['class' => 'dropdown-item delete-repair-item'])?></li>
                                </ul>
                            </div>
                            <?php endif;?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>

        <div class="d-flex justify-content-center">
            <div class="text-muted">
                <?= LinkPager::widget([
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
$js = <<< JS
$('body').on('click', '.receive-order', function (e) {
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการรับงาน?',
        text: "คุณแน่ใจหรือไม่ว่าจะรับงานนี้?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'ใช่, รับงาน',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "get",
                url: url,
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'รับงานสำเร็จ!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload(); // โหลดหน้าใหม่หลังจากแจ้งเตือน
                        });
                    } else {
                        Swal.fire('ผิดพลาด', response.message || 'ไม่สามารถรับงานได้', 'error');
                    }
                },
                error: function () {
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                }
            });
        }
    });
});


$("body").on("click", ".delete-repair-item", async function (e) {
  e.preventDefault();
  var url = $(this).attr("href");
  // console.log('delete',url);
  // $('#main-modal').modal('show');

  await Swal.fire({
    title: "คุณแน่ใจไหม?",
    text: "ลบรายการที่เลือก!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ลบเลย!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    console.log("result", result.value);
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: url,
        dataType: "json",
        success: function (response) {
          if (response.status == "success") {
             location.reload();
            // $.pjax.reload({
            //   container: response.container,
            //   history: false,
            //   url: response.url,
            // });

            success("ดำเนินการลบสำเร็จ!.");
            if (response.close) {
              $("#main-modal").modal("hide");
            }
          }
        },
      });
    }
  });
});



JS;
$this->registerJS($js,View::POS_END);
?>