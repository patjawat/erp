<?php
use yii\web\View;
use yii\helpers\Html;
$this->title = 'Meetings';
$this->params['breadcrumbs'][] = $this->title;
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<div class="container">
    <?=$this->render('navbar')?>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>


    <div class="meeting-table table-responsive">
        <table class="table table-borderless mb-0">
            <thead>
                <tr>
                    <th>ผู้จอง</th>
                    <th>ห้องประชุม</th>
                    <th>วันที่</th>
                    <th>เวลา</th>
                    <th>สถานะ</th>
                    <th class="text-end">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <!-- Row 1 -->
                <?php foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td>
                        <?=$item->getUserReq()['avatar']?>
                    </td>
                    <td><?=$item->room->title?></td>
                    <td><?=$item->viewMeetingDate()?></td>
                    <td><?=$item->viewMeetingTime()?></td>
                    <td><?=$item->viewStatus()?></td>
                    <td class="text-end">

                        <?=Html::a('<div class="action-icon view"><i class="bi bi-eye"></i></div>',['/me/booking-meeting/view','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-md']])?>
                        <?php if($item->status == 'Pending'):?>
                        <div class="action-icon approve d-inline-flex confirm-meeting" data-id="<?=$item->id?>"
                            data-status="Pass" data-text="อนุมัติการจอง">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <?php endif;?>
                        <?php if($item->status == 'Pending'):?>
                        <div class="action-icon reject d-inline-flex confirm-meeting" data-id="<?=$item->id?>"
                            data-status="Cancel" data-text="ปฏิเสธการจอง">
                            <i class="bi bi-x-lg"></i>
                        </div>
                        <?php endif;?>
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