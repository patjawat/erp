<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\booking\models\Meeting;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\MeetingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ระบบจัดการห้องประชุม(ผู้ดูแลระบบ)';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
Dashboard
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>



<div class="container-fluid">
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?=$this->render('summary', ['searchModel' => $searchModel])?>

    <div class="row">
        <div class="col-7">

            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-1">การจองที่รอการอนุมัติ</h3>
                        <p class="card-subtitle text-muted">รายการจองห้องประชุมที่รอการอนุมัติ</p>
                    </div>
                    <?=Html::a('ดูทั้งหมด',['/booking/meeting/index'],['class' => 'btn btn-primary rounded-pill'])?>
                </div>

                <ul class="list-group list-group-flush">
                    <!-- รายการที่ 1 -->
                     <?php foreach($dataProvider->getModels() as $key => $item):?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-0"><?=$item->getUserReq()['fullname']?></h6>
                                <small class="text-muted"><?=$item->room->title?></small>
                               
                            </div>
                            <div class="text-muted small mt-1">
                                    <i class="bi bi-calendar-event me-1"></i><?=$item->viewMeetingDate()?><br />
                                    <i class="bi bi-clock me-1"></i><?=$item->viewMeetingTime()?>
                                </div>
                            <div class="d-flex align-items-start gap-2">
                        <div class="action-icon approve d-inline-flex confirm-meeting" data-id="<?=$item->id?>"
                            data-status="Pass" data-text="อนุมัติการจอง">
                            <i class="fa-solid fa-circle-check fa-2x"></i>
                        </div>
                        <div class="action-icon reject d-inline-flex confirm-meeting" data-id="<?=$item->id?>"
                            data-status="Cancel" data-text="ยกเลิกการจอง">
                            <i class="fa-solid fa-circle-xmark fa-2x"></i>
                        </div>
                            </div>
                        </div>
                    </li>
            <?php endforeach;?>
                   
                    <!-- เพิ่มรายการอื่น ๆ ตามแบบเดียวกัน -->
                </ul>
            </div>
        </div>
        <div class="col-5">


            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">สถิติการใช้ห้องประชุม</h3>
                    <p class="card-subtitle text-muted mb-4">สถิติการใช้ห้องประชุมในเดือนนี้</p>
<?php foreach($searchModel->getUsageStatistics() as $key => $room):?>
                    <!-- ห้องประชุมใหญ่ -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <strong class="fw-semibold"><?=$room['title']?></strong>
                            <span class="text-muted small"><?=$room['percentage']?>%</span>
                        </div>
                        <div class="progress mb-1" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?=$room['percentage']?>%;"
                                aria-valuenow="<?=$room['percentage']?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>จำนวนการจอง: <?=$room['total']?> ครั้ง</span>
                            <!-- <span>จำนวนชั่วโมง: 48 ชั่วโมง</span> -->
                        </div>
                    </div>
<?php endforeach;?>
                </div>
            </div>

        </div>
    </div>

    <?php Pjax::end(); ?>

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
  