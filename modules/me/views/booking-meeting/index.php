<?php
use yii\web\View;
use yii\helpers\Html;

$this->title = 'ระบบขอใช้ห้องประชุม/ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-handshake fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ทะเบียนขอใช้ห้องประชุม
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('menu',['active' => 'index'])?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/me/menu',['active' => 'meeting'])?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/booking/views/meeting/_search', ['model' => $searchModel,'action' => ['/me/booking-meeting/index']]); ?>
    </div>
</div>

    

    <div class="card">
        <div class="card-header bg-primary-gradient text-white">
            <div class="d-flex justify-content-between">
                <h6 class="text-white">
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
                            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                            <th class="fw-semibold">หัวข้อการประชุม</th>
                            <th class="fw-semibold">ผู้ขอ</th>
                            <th class="fw-semibold">ห้องประชุม</th>
                            <th class="fw-semibold">สถานะ</th>
                            <th class="fw-semibold text-center">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <!-- Row 1 -->
                        <?php foreach($dataProvider->getModels() as $key => $item):?>
                        <tr>
                            <td class="text-center fw-semibold">
                                <?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                            <td>
                                <div class="avatar-detail">
                                    <h6 class="mb-0 fs-13"><?=$item->title?></h6>
                                    <p class="text-muted mb-0 fs-13">
                                        <?=$item->viewMeetingDate()?> เวลา <?=$item->viewMeetingTime()?>
                                    </p>
                                </div>
                            </td>
                            <td><?=$item->getUserReq()['avatar']?></td>
                            <td><?=$item->room->title?></td>
                            <td><?=$item->viewStatus()['view']?></td>
                            <td class="fw-light text-center">
                                <div class="btn-group">
                                    <?= Html::a('<i class="fa-solid fa-pen-to-square"></i>', ['/me/booking-meeting/update', 'id' => $item->id,'title' => 'แก้ไข'], ['class' => 'btn btn-light w-100 open-modal','data' => ['size' => 'modal-xl']]) ?>
                                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                        <i class="bi bi-caret-down-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><?php echo Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงข้อมูล',['/me/booking-meeting/view','id' => $item->id],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>
                                        </li>
                                        <li><?php echo Html::a('<i class="fa-solid fa-circle-xmark me-1"></i> ยกเลิก',['/me/booking-meeting/cancel','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> ลบ'],['class' => 'dropdown-item cancel-order','data' => ['size' => 'modal-lg']])?>
                                        </li>
                                    </ul>
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
  $this->registerJS($js,View::POS_END);
  ?>