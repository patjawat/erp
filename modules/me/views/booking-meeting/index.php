<?php

use yii\web\View;
use yii\helpers\Html;

$this->title = 'ระบบขอใช้ห้องประชุม/ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
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
<div class="d-flex gap-2">
    <?= $this->render('menu', ['active' => 'meeting']) ?>
    <?= $this->render('@app/components/ui/btnReturn') ?>
</div>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/booking/views/meeting/_search', ['model' => $searchModel, 'action' => ['/me/booking-meeting/index']]); ?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <h6 class="">
                <i class="bi bi-ui-checks"></i> ทะเบียนขอใช้ห้องประชุม
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width:30px">ลำดับ</th>
                    <th>ผู้ขอ</th>
                    <th>หัวข้อการประชุม</th>
                    <th>ห้องประชุม</th>
                    <th>สถานะ</th>
                    <th class="fw-semibold text-end">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <!-- Row 1 -->
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr>
                        <td class="text-center">
                            <?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                        <td><?= $item->getUserReq()['avatar'] ?></td>
                        <td>
                            <div class="avatar-detail">
                                <h6 class="mb-0 fs-13"><?= $item->title ?></h6>
                                <p class="text-muted mb-0 fs-13">
                                    <?= $item->viewMeetingDate() ?> เวลา <?= $item->viewTime()['full'] ?>
                                </p>
                            </div>
                        </td>
                        <td><?= $item->room->title ?></td>
                        <td><?= $item->viewStatus()['view'] ?></td>

                        <td class="fw-light text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><?= Html::a('<i class="fa-solid fa-pen-to-square me-1"></i> แก้ไข', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-trash me-1"></i> ลบทิ้ง', ['delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item']) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงข้อมูล', ['/me/booking-meeting/view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                    <li><?= Html::a('<i class="fa-solid fa-circle-xmark me-1"></i> ยกเลิก', ['/me/booking-meeting/cancel', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> ลบ'], ['class' => 'dropdown-item cancel-order', 'data' => ['size' => 'modal-lg']]) ?>
                                </ul>
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

  $('body').on('click', '.confirm-meeting', function (e) {
    e.preventDefault();

    var status = $(this).data('status');
    var id = $(this).data('id');
    var text = $(this).data('text');
    Swal.fire({
      title: "ยืนยัน!",
      text:text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: 'ยกเลิก',
      confirmButtonText: 'ใช่, ยืนยัน!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          type: "post",
          url: '/me/booking-meeting/confirm',
          data: {
            id: id,
            status: status
          },
          dataType: "json",
          success: function (res) {
            if (res.status == 'success') {
              $('.modal').modal('hide');
              Swal.fire({
              icon: 'success',
              title: 'Confirmed!',
              text: res.message || 'ดำเนินการเรียบร้อยแล้ว',
              timer: 1000,
              showConfirmButton: false
              }).then(() => {
              location.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: res.message || 'Something went wrong.',
              });
            }
          }
        });
      }
    });
  });
  JS;
$this->registerJS($js, View::POS_END);
?>